<?php
session_start();
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';

// Get category ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: list.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $language = $_POST['language'];
    
    // Create slug from name
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    
    // Validate inputs
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($language)) {
        $errors[] = "Language is required";
    }
    
    if (empty($errors)) {
        // Check if slug exists for another category in this language
        $check_stmt = $conn->prepare("SELECT id FROM blog_categories WHERE slug = ? AND language = ? AND id != ?");
        $check_stmt->bind_param("ssi", $slug, $language, $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "A category with this name already exists in this language";
        } else {
            // Update category
            $stmt = $conn->prepare("UPDATE blog_categories SET name = ?, description = ?, language = ?, slug = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $description, $language, $slug, $id);
            
            if ($stmt->execute()) {
                header("Location: list.php?updated=1");
                exit();
            } else {
                $errors[] = "Error updating category: " . $conn->error;
            }
        }
    }
} else {
    // Fetch existing category data
    $stmt = $conn->prepare("SELECT * FROM blog_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: list.php');
        exit();
    }
    
    $category = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Edit Category - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4">
                    <h1 class="text-2xl font-semibold">Edit Category</h1>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <form method="POST" class="p-6 space-y-6">
                            <?php if (!empty($errors)): ?>
                            <div class="bg-red-50 text-red-500 p-4 rounded-lg">
                                <ul class="list-disc list-inside">
                                    <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category Name</label>
                                <input type="text" name="name"
                                    value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Language</label>
                                <select name="language"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>
                                    <option value="lv"
                                        <?php echo (($category['language'] ?? '') == 'lv') ? 'selected' : ''; ?>>Latvian
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center space-x-4">
                                <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Update Category
                                </button>
                                <a href="list.php" class="text-gray-500 hover:text-gray-700">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>