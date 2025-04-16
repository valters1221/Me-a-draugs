<?php
session_start();
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';

// Get category ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: list.php?error=invalid_id');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // First update any blog posts that use this category to set category_id to NULL
    $update_posts = $conn->prepare("UPDATE blog_posts SET category_id = NULL WHERE category_id = ?");
    $update_posts->bind_param("i", $id);
    $update_posts->execute();

    // Then delete the category
    $delete_category = $conn->prepare("DELETE FROM blog_categories WHERE id = ?");
    $delete_category->bind_param("i", $id);
    
    if ($delete_category->execute()) {
        // Commit transaction
        $conn->commit();
        header("Location: list.php?deleted=1");
        exit();
    } else {
        throw new Exception("Error deleting category");
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header("Location: list.php?error=delete_failed");
    exit();
}
?>