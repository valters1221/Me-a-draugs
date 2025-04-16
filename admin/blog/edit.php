<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/config.php';

// Security headers (commented out during development)
/*
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.tiny.cloud; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com; img-src 'self' data: blob:; connect-src 'self';");
*/

// Configuration
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', dirname(dirname(dirname(__FILE__))) . '/images/blog/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Initialize sessions with secure defaults
if (!isset($_SESSION['temp_uploaded_images'])) {
    $_SESSION['temp_uploaded_images'] = [];
}
if (!isset($_SESSION['deleted_images'])) {
    $_SESSION['deleted_images'] = [];
}
if (!isset($_SESSION['original_images'])) {
    $_SESSION['original_images'] = [];
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    // Validate post ID
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception("Invalid post ID");
    }

    // Fetch the blog post with prepared statement
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();

    if (!$post) {
        throw new Exception("Post not found");
    }

    // Track original images
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $post['content'], $matches);
    if (!empty($matches[1])) {
        $_SESSION['original_images'] = array_map('basename', array_filter($matches[1]));
    }

    // Fetch categories
    $stmt = $conn->prepare("SELECT id, name, language FROM blog_categories WHERE language = ? ORDER BY name ASC");
    $stmt->bind_param("s", $post['language']);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        // Validate and sanitize input
        $data = [
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'author' => filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING),
            'content' => $_POST['content'], // Will be handled by TinyMCE
            'category_id' => filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT),
            'meta_title' => filter_input(INPUT_POST, 'meta_title', FILTER_SANITIZE_STRING),
            'meta_description' => filter_input(INPUT_POST, 'meta_description', FILTER_SANITIZE_STRING),
            'status' => in_array($_POST['status'], ['draft', 'published']) ? $_POST['status'] : 'draft'
        ];

        // Validate required fields
        $errors = [];
        foreach (['title', 'author', 'content', 'category_id'] as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst($field) . " is required";
            }
        }

        // Handle featured image
        $data['featured_image'] = $post['featured_image']; // Default to existing

        if (isset($_POST['remove_featured_image']) && !empty($post['featured_image'])) {
            $old_image_path = UPLOAD_PATH . basename($post['featured_image']);
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
            $data['featured_image'] = null;
        }
        elseif (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            // Validate file
            if ($_FILES['featured_image']['size'] > MAX_IMAGE_SIZE) {
                throw new Exception("File is too large. Maximum size is " . (MAX_IMAGE_SIZE / 1024 / 1024) . "MB");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES['featured_image']['tmp_name']);
            if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
                throw new Exception("Invalid file type. Allowed types: " . implode(', ', IMAGE_EXTENSIONS));
            }

            // Generate safe filename
            $file_extension = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
            $new_filename = 'featured_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
            $upload_path = UPLOAD_PATH . $new_filename;

            // Delete old image if exists
            if (!empty($post['featured_image'])) {
                $old_image_path = UPLOAD_PATH . basename($post['featured_image']);
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }

            // Upload new image
            if (!move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload image");
            }

            $data['featured_image'] = '/images/blog/' . $new_filename;
        }

        if (empty($errors)) {
            $conn->begin_transaction();

            try {
                // Create slug
                $data['slug'] = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']));
                $data['slug'] = trim($data['slug'], '-');

                // Check for duplicate slug
                $check_stmt = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ? AND language = ?");
                $check_stmt->bind_param("sis", $data['slug'], $id, $post['language']);
                $check_stmt->execute();
                if ($check_stmt->get_result()->num_rows > 0) {
                    $data['slug'] .= '-' . time();
                }

                // Update post
                $stmt = $conn->prepare("
                    UPDATE blog_posts SET 
                        title = ?, author = ?, content = ?, category_id = ?,
                        meta_title = ?, meta_description = ?, featured_image = ?,
                        slug = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");

                $stmt->bind_param("sssisssssi",
                    $data['title'], $data['author'], $data['content'],
                    $data['category_id'], $data['meta_title'], $data['meta_description'],
                    $data['featured_image'], $data['slug'], $data['status'], $id
                );

                $stmt->execute();

                // Clean up deleted images
                if (!empty($_SESSION['deleted_images'])) {
                    foreach ($_SESSION['deleted_images'] as $image) {
                        $image_path = UPLOAD_PATH . basename($image);
                        if (file_exists($image_path) && !empty($image)) {
                            unlink($image_path);
                        }
                    }
                }

                // Clear sessions
                unset($_SESSION['temp_uploaded_images'], $_SESSION['deleted_images'], $_SESSION['original_images']);

                $conn->commit();
                header("Location: list.php?updated=1");
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "Error updating post: " . $e->getMessage();
            }
        }
    }

} catch (Exception $e) {
    error_log("Error in edit post: " . $e->getMessage());
    header("Location: list.php?error=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Edit Blog Post - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tiny.cloud/1/kxm5qlbv9qkji2ffwpmt8efpz2acsj04en9fq5vydzas9m11/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
    // Featured image handling
    function clearFeaturedImage(button) {
        const input = button.parentNode.querySelector('input[type="file"]');
        const preview = button.closest('.mb-6').querySelector('img');
        if (preview) {
            preview.parentNode.style.display = 'none';
        }
        input.value = '';
        // Add hidden input to mark image for deletion
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'remove_featured_image';
        hiddenInput.value = '1';
        button.parentNode.appendChild(hiddenInput);
        button.style.display = 'none';
    }

    // Initialize editor
    tinymce.init({
        selector: '#content',
        height: 700,
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons',
            'image', 'link', 'lists', 'media', 'searchreplace', 'table',
            'visualblocks', 'wordcount', 'checklist', 'mediaembed'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        images_upload_url: 'upload.php',
        automatic_uploads: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        setup: function(editor) {
            // On editor initialization, track existing images
            editor.on('init', function() {
                const content = editor.getContent();
                const images = content.match(/<img[^>]+src="([^">]+)"/g) || [];
                const existingImages = images.map(img => {
                    const match = img.match(/src="([^">]+)"/);
                    return match ? match[1] : null;
                }).filter(Boolean);

                // Track these as existing images
                fetch('track_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        images: existingImages,
                        action: 'init'
                    })
                });
            });

            // Track changes to images
            editor.on('NodeChange', function(e) {
                if (e.element.nodeName === 'IMG') {
                    const currentContent = editor.getContent();
                    const images = currentContent.match(/<img[^>]+src="([^">]+)"/g) || [];
                    const currentImages = images.map(img => {
                        const match = img.match(/src="([^">]+)"/);
                        return match ? match[1] : null;
                    }).filter(Boolean);

                    // Track current images state
                    fetch('track_deleted_images.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            currentImages: currentImages,
                            postId: <?php echo json_encode($id); ?>
                        })
                    });
                }
            });

            // Track when editor content is saved
            editor.on('submit', function() {
                const content = editor.getContent();
                const images = content.match(/<img[^>]+src="([^">]+)"/g) || [];
                const finalImages = images.map(img => {
                    const match = img.match(/src="([^">]+)"/);
                    return match ? match[1] : null;
                }).filter(Boolean);

                // Send final state of images
                fetch('track_deleted_images.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        currentImages: finalImages,
                        postId: <?php echo json_encode($id); ?>,
                        action: 'save'
                    })
                });
            });
        }
    });
    </script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/navbar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4">
                    <h1 class="text-2xl font-semibold">Edit Blog Post</h1>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <!-- Title -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                <input type="text" name="title" required
                                    value="<?php echo htmlspecialchars($post['title']); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Author -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                                <input type="text" name="author" required
                                    value="<?php echo htmlspecialchars($post['author']); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Featured Image -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                                <?php if ($post['featured_image']): ?>
                                <div class="mb-4">
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>"
                                        alt="Current featured image" class="w-40 h-auto rounded">
                                    <p class="mt-2 text-sm text-gray-500">Current featured image</p>
                                </div>
                                <?php endif; ?>
                                <div class="flex items-center space-x-4">
                                    <input type="file" name="featured_image" accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <?php if ($post['featured_image']): ?>
                                    <button type="button" onclick="clearFeaturedImage(this)"
                                        class="px-3 py-2 text-red-600 hover:text-red-700 focus:outline-none">
                                        Remove
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Upload new image to replace current one</p>
                            </div>

                            <!-- Category -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select name="category_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo $category['id'] == $post['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Content -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                                <textarea id="content"
                                    name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>

                            <!-- SEO Section -->
                            <div class="border-t pt-6 mt-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">SEO Details</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                                        <input type="text" name="meta_title" maxlength="60"
                                            value="<?php echo htmlspecialchars($post['meta_title']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-sm text-gray-500">Maximum 60 characters</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta
                                            Description</label>
                                        <textarea name="meta_description" maxlength="160" rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($post['meta_description']); ?></textarea>
                                        <p class="mt-1 text-sm text-gray-500">Maximum 160 characters</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex justify-end space-x-4 mt-6">
                                <button type="submit" name="status" value="draft"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Save as Draft
                                </button>
                                <button type="submit" name="status" value="published"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Publish
                                </button>
                                <a href="list.php" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>