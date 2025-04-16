<?php
session_start();
require_once '../includes/auth_check.php';

// Initialize sessions if they don't exist
if (!isset($_SESSION['temp_uploaded_images'])) {
    $_SESSION['temp_uploaded_images'] = [];
}
if (!isset($_SESSION['original_images'])) {
    $_SESSION['original_images'] = [];
}

// Get JSON input
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// Check if we're handling POST data or JSON data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['images']) && isset($data['action']) && $data['action'] === 'init') {
        // Handle initialization of existing images (for edit.php)
        $_SESSION['original_images'] = array_map('basename', $data['images']);
        $_SESSION['temp_uploaded_images'] = array_map('basename', $data['images']);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Existing images initialized',
            'tracked_images' => $_SESSION['temp_uploaded_images']
        ]);
        exit;
    }
    elseif (isset($data['image'])) {
        // Handle newly uploaded image
        $image_filename = basename($data['image']);
        
        // Add to temporary uploaded images if not already tracked
        if (!in_array($image_filename, $_SESSION['temp_uploaded_images'])) {
            $_SESSION['temp_uploaded_images'][] = $image_filename;
        }
        
        echo json_encode([
            'status' => 'success',
            'tracked_images' => $_SESSION['temp_uploaded_images']
        ]);
        exit;
    }
    elseif (isset($_POST['image'])) {
        // Handle traditional POST upload
        $image_filename = basename($_POST['image']);
        
        if (!in_array($image_filename, $_SESSION['temp_uploaded_images'])) {
            $_SESSION['temp_uploaded_images'][] = $image_filename;
        }
        
        echo json_encode([
            'status' => 'success',
            'tracked_images' => $_SESSION['temp_uploaded_images']
        ]);
        exit;
    }
}

// If we get here, something went wrong
http_response_code(400);
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request',
    'received_data' => $data ?? null
]);
exit;