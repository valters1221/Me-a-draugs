<?php
session_start();
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';

// Get language filter (default to showing all)
$language_filter = isset($_GET['lang']) ? $_GET['lang'] : 'all';

// Build query based on language filter
$query = "SELECT * FROM blog_categories";
if ($language_filter !== 'all') {
    $query .= " WHERE language = ?";
}
$query .= " ORDER BY created_at DESC";

// Prepare and execute query
if ($language_filter !== 'all') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $language_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Categories - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-semibold">Categories</h1>
                        <!-- Language Filter -->
                        <select onchange="window.location.href=`list.php?lang=${this.value}`"
                            class="border rounded px-2 py-1">
                            <option value="lv" <?php echo $language_filter === 'lv' ? 'selected' : ''; ?>>Latvian
                            </option>
                        </select>
                    </div>
                    <a href="create.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add New
                        Category</a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-50 text-green-500 p-4 rounded-lg mb-6">
                        Category created successfully!
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['deleted'])): ?>
                    <div class="bg-green-50 text-green-500 p-4 rounded-lg mb-6">
                        Category was successfully deleted.
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6">
                        <?php 
                            switch($_GET['error']) {
                                case 'invalid_id':
                                    echo 'Invalid category selected.';
                                    break;
                                case 'delete_failed':
                                    echo 'Failed to delete category. Please try again.';
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Language
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php 
                                                if (!empty($row['description'])) {
                                                    echo strlen($row['description']) > 100 
                                                        ? htmlspecialchars(substr($row['description'], 0, 100)) . '...' 
                                                        : htmlspecialchars($row['description']);
                                                } else {
                                                    echo '<span class="text-gray-400">No description</span>';
                                                }
                                                ?>
                                    </td>
                                    <td class="px-6 py-4 uppercase"><?php echo $row['language']; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['slug']); ?></td>
                                    <td class="px-6 py-4">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>"
                                            class="text-blue-500 hover:text-blue-700 mr-4">Edit</a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>"
                                            class="text-red-500 hover:text-red-700"
                                            onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No categories found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>