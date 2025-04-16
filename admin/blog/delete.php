<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/config.php';

// Configuration
define('UPLOAD_PATH', dirname(dirname(dirname(__FILE__))) . '/images/blog/');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php?error=invalid_request');
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: list.php?error=invalid_token');
    exit();
}

// Get post ID from POST
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$id) {
    header('Location: list.php?error=invalid_id');
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // First, get the post to extract images
    $stmt = $conn->prepare("SELECT content, featured_image FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();

    if (!$post) {
        throw new Exception("Post not found");
    }

    $images_to_delete = [];

    // Get all images from content
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $post['content'], $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $img_url) {
            $images_to_delete[] = basename($img_url);
        }
    }

    // Add featured image if exists
    if (!empty($post['featured_image'])) {
        $images_to_delete[] = basename($post['featured_image']);
    }

    // Delete the post from database
    $delete_stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete post from database");
    }

    // If post is deleted successfully, delete all associated images
    if (!empty($images_to_delete)) {
        $upload_dir = UPLOAD_PATH;
        
        foreach ($images_to_delete as $image) {
            if (empty($image)) continue;
            
            // Validate image filename
            if (!preg_match('/^[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/', $image)) {
                error_log("Suspicious image filename detected: " . $image);
                continue;
            }
            
            $image_path = $upload_dir . $image;
            if (file_exists($image_path) && is_file($image_path)) {
                unlink($image_path);
            }
        }
    }

    // Clear any related sessions
    unset($_SESSION['temp_uploaded_images']);
    unset($_SESSION['deleted_images']);
    unset($_SESSION['original_images']);

    // Commit transaction
    $conn->commit();
    header('Location: list.php?deleted=1');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    error_log("Error deleting post: " . $e->getMessage());
    header('Location: list.php?error=delete_failed');
    exit();
} finally {
    // Clean up resources
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($delete_stmt)) {
        $delete_stmt->close();
    }
}
?>