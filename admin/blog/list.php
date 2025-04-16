<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/config.php';

// Commenting out security headers during development
/*
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.tailwindcss.com;");
*/

// Validate and sanitize language filter input
$allowed_languages = ['all', 'en', 'lv'];
$language_filter = isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages) 
    ? $_GET['lang'] 
    : 'all';

try {
    // Build base query with JOIN
    $query = "SELECT 
        blog_posts.id,
        blog_posts.title,
        blog_posts.language,
        blog_posts.status,
        blog_posts.created_at,
        COALESCE(blog_categories.name, 'Uncategorized') as category_name 
    FROM blog_posts 
    LEFT JOIN blog_categories ON blog_posts.category_id = blog_categories.id";

    // Add language filter if needed
    if ($language_filter !== 'all') {
        $query .= " WHERE blog_posts.language = ?";
    }
    $query .= " ORDER BY blog_posts.created_at DESC";

    // Prepare and execute query
    $stmt = $conn->prepare($query);
    if ($language_filter !== 'all') {
        $stmt->bind_param("s", $language_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Database query failed");
    }

} catch (Exception $e) {
    error_log("Error in blog post list: " . $e->getMessage());
    $error_message = "An error occurred while fetching blog posts.";
    $result = null;
} finally {
    // Make sure to close the statement
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Generate CSRF token if it doesn't exist
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
    <title>Blog Posts - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/navbar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-semibold">Blog Posts</h1>
                        <!-- Language Filter -->
                        <select onchange="window.location.href=`list.php?lang=${this.value}`"
                            class="border rounded px-2 py-1">
                            <option value="lv" <?php echo $language_filter === 'lv' ? 'selected' : ''; ?>>Latvian
                            </option>
                        </select>
                    </div>
                    <a href="create.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add New
                        Post</a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <?php if (isset($_GET['deleted'])): ?>
                    <div class="bg-green-50 text-green-500 p-4 rounded-lg mb-6">
                        Blog post was successfully deleted.
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['updated'])): ?>
                    <div class="bg-green-50 text-green-500 p-4 rounded-lg mb-6">
                        Blog post was successfully updated.
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6">
                        <?php 
                            switch($_GET['error']) {
                                case 'invalid_id':
                                    echo 'Invalid post selected.';
                                    break;
                                case 'delete_failed':
                                    echo 'Failed to delete post. Please try again.';
                                    break;
                                default:
                                    echo 'An error occurred.';
                            }
                            ?>
                    </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Language
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="px-6 py-4 uppercase"><?php echo $row['language']; ?></td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $row['status'] === 'published' 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>"
                                            class="text-blue-500 hover:text-blue-700 mr-4">Edit</a>
                                        <a href="#" onclick="deletePost(<?php echo $row['id']; ?>); return false;"
                                            class="text-red-500 hover:text-red-700">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No blog posts found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" action="delete.php" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id" value="">
    </form>

    <!-- Delete Script -->
    <script>
    function deletePost(id) {
        if (confirm('Are you sure you want to delete this post?')) {
            document.querySelector('#deleteForm input[name="id"]').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    </script>
</body>

</html>