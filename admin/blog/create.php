<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/config.php';

// Set secure headers
// header("X-Content-Type-Options: nosniff");
// header("X-Frame-Options: DENY");
// header("X-XSS-Protection: 1; mode=block");
// header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.tailwindcss.com https://cdn.tiny.cloud; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com;");

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

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Fetch categories with prepared statement
try {
    $stmt = $conn->prepare("SELECT id, name, language FROM blog_categories ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['language']][] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        // Validate required fields
        $required_fields = ['title', 'author', 'content', 'language', 'category_id'];
        $errors = [];
        $data = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst($field) . " is required";
            }
            $data[$field] = trim($_POST[$field]);
        }

        // Additional field validation
        if (strlen($data['title']) > 255) {
            $errors[] = "Title is too long (maximum 255 characters)";
        }
        if (strlen($data['author']) > 255) {
            $errors[] = "Author name is too long (maximum 255 characters)";
        }

        // Validate and process optional fields
        $data['meta_title'] = isset($_POST['meta_title']) ? substr(trim($_POST['meta_title']), 0, 60) : null;
        $data['meta_description'] = isset($_POST['meta_description']) ? substr(trim($_POST['meta_description']), 0, 160) : null;
        $data['status'] = in_array($_POST['status'], ['draft', 'published']) ? $_POST['status'] : 'draft';

        // Create slug from title
        $data['slug'] = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']));
        $data['slug'] = trim($data['slug'], '-');

        // Process featured image
        $data['featured_image'] = null;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            // Validate file size
            if ($_FILES['featured_image']['size'] > MAX_IMAGE_SIZE) {
                throw new Exception("File is too large. Maximum size is " . (MAX_IMAGE_SIZE / 1024 / 1024) . "MB");
            }

            // Validate file type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES['featured_image']['tmp_name']);
            if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
                throw new Exception("Invalid file type. Allowed types: " . implode(', ', IMAGE_EXTENSIONS));
            }

            // Generate safe filename
            $file_extension = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
            $new_filename = 'featured_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
            $upload_path = UPLOAD_PATH . $new_filename;

            // Move file
            if (!move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload image");
            }

            // Set image URL using configured base URL
            $data['featured_image'] = '/images/blog/' . $new_filename;
        }

        if (empty($errors)) {
            // Begin transaction
            $conn->begin_transaction();

            try {
                // Check for duplicate slug
                $check_stmt = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ? AND language = ?");
                $check_stmt->bind_param("ss", $data['slug'], $data['language']);
                $check_stmt->execute();
                if ($check_stmt->get_result()->num_rows > 0) {
                    $data['slug'] .= '-' . time();
                }

                // Insert post
                $stmt = $conn->prepare("
                    INSERT INTO blog_posts (
                        title, author, content, language, category_id,
                        meta_title, meta_description, featured_image,
                        slug, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param("ssssssssss",
                    $data['title'], $data['author'], $data['content'],
                    $data['language'], $data['category_id'],
                    $data['meta_title'], $data['meta_description'],
                    $data['featured_image'], $data['slug'], $data['status']
                );

                if (!$stmt->execute()) {
                    throw new Exception("Database error: " . $stmt->error);
                }

                // Clean up deleted images
                if (!empty($_SESSION['deleted_images'])) {
                    foreach ($_SESSION['deleted_images'] as $image) {
                        $image_path = UPLOAD_PATH . basename($image);
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                }

                // Clear sessions
                unset($_SESSION['temp_uploaded_images']);
                unset($_SESSION['deleted_images']);

                $conn->commit();
                header("Location: list.php?success=1");
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                // Clean up uploaded image if transaction failed
                if (isset($upload_path) && file_exists($upload_path)) {
                    unlink($upload_path);
                }
                throw $e;
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        error_log("Error creating blog post: " . $e->getMessage());
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Create Blog Post - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tiny.cloud/1/kxm5qlbv9qkji2ffwpmt8efpz2acsj04en9fq5vydzas9m11/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
    // TinyMCE configuration with security improvements
    tinymce.init({
        selector: '#content',
        height: 700,
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons',
            'image', 'link', 'lists', 'media', 'searchreplace', 'table',
            'visualblocks', 'wordcount',
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        images_upload_url: 'upload.php',
        images_upload_credentials: true,
        automatic_uploads: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        extended_valid_elements: 'img[class|src|border=0|alt|title|width|height|style]',
        valid_children: '+body[style]',
        verify_html: true,
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            '//www.tiny.cloud/css/codepen.min.css'
        ],
        setup: function(editor) {
            editor.on('NodeChange', function(e) {
                if (e.element.nodeName === 'IMG') {
                    const images = editor.getContent().match(/<img[^>]+src="([^">]+)"/g) || [];
                    const currentImages = images.map(img => {
                        const match = img.match(/src="([^">]+)"/);
                        return match ? match[1] : null;
                    }).filter(Boolean);

                    fetch('track_deleted_images.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                        },
                        body: JSON.stringify({
                            currentImages: currentImages
                        }),
                        credentials: 'same-origin'
                    });
                }
            });
        }
    });

    // Category handling with improved error handling
    document.addEventListener('DOMContentLoaded', function() {
        const languageSelect = document.querySelector('[name="language"]');
        const categorySelect = document.querySelector('[name="category_id"]');
        const categories = <?php echo json_encode($categories); ?>;

        function updateCategories(selectedLanguage) {
            categorySelect.innerHTML = '<option value="">Select Category</option>';

            if (selectedLanguage && categories[selectedLanguage]) {
                categories[selectedLanguage].forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            }
        }

        if (languageSelect.value) {
            updateCategories(languageSelect.value);
        }

        languageSelect.addEventListener('change', function() {
            updateCategories(this.value);
        });

        const previousCategory =
            '<?php echo isset($_POST["category_id"]) ? htmlspecialchars($_POST["category_id"]) : ""; ?>';
        if (previousCategory) {
            setTimeout(() => {
                categorySelect.value = previousCategory;
            }, 0);
        }
    });
    </script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/navbar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">

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
                                    value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Author -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                                <input type="text" name="author" required value="Meža Draugs"
                                    value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Featured Image -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                                <input type="file" name="featured_image" accept="image/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Recommended size: 1200x630 pixels (for social
                                    sharing)</p>
                            </div>

                            <!-- Language & Category -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                    <select name="language" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="lv"
                                            <?php echo (isset($_POST['language']) && $_POST['language'] == 'lv') ? 'selected' : ''; ?>>
                                            Latvian</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                    <select name="category_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Category</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                                <textarea id="content"
                                    name="content"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>

                            <!-- SEO Section -->
                            <div class="border-t pt-6 mt-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">SEO Details</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                                        <input type="text" name="meta_title" maxlength="60"
                                            value="<?php echo isset($_POST['meta_title']) ? htmlspecialchars($_POST['meta_title']) : ''; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-sm text-gray-500">Maximum 60 characters</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta
                                            Description</label>
                                        <textarea name="meta_description" maxlength="160" rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo isset($_POST['meta_description']) ? htmlspecialchars($_POST['meta_description']) : ''; ?></textarea>
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
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>