<?php
session_start();
require_once '../includes/auth_check.php';

// Initialize sessions if they don't exist
if (!isset($_SESSION['temp_uploaded_images'])) {
    $_SESSION['temp_uploaded_images'] = [];
}
if (!isset($_SESSION['deleted_images'])) {
    $_SESSION['deleted_images'] = [];
}
if (!isset($_SESSION['original_images'])) {
    $_SESSION['original_images'] = [];
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['currentImages'])) {
    $currentImages = array_map('basename', $data['currentImages']);
    $isEditMode = isset($data['postId']);
    
    // If we're in edit mode and this is a save action, compare with original images
    if ($isEditMode && isset($data['action']) && $data['action'] === 'save') {
        $deletedImages = array_diff($_SESSION['original_images'], $currentImages);
        $_SESSION['deleted_images'] = array_unique(array_merge($_SESSION['deleted_images'], $deletedImages));
    } else {
        // Regular tracking of deleted images
        foreach ($_SESSION['temp_uploaded_images'] as $uploadedImage) {
            $isImageInUse = in_array($uploadedImage, $currentImages);
            
            if (!$isImageInUse) {
                // Add to deleted images if not already there and it's not an original image
                if (!in_array($uploadedImage, $_SESSION['deleted_images']) && 
                    (!$isEditMode || !in_array($uploadedImage, $_SESSION['original_images']))) {
                    $_SESSION['deleted_images'][] = $uploadedImage;
                }
            } else {
                // If image is back in use, remove from deleted images
                $key = array_search($uploadedImage, $_SESSION['deleted_images']);
                if ($key !== false) {
                    unset($_SESSION['deleted_images'][$key]);
                    $_SESSION['deleted_images'] = array_values($_SESSION['deleted_images']);
                }
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'tracking_status' => [
            'temp_uploaded' => $_SESSION['temp_uploaded_images'],
            'deleted' => $_SESSION['deleted_images'],
            'original' => $_SESSION['original_images'],
            'current' => $currentImages
        ]
    ]);
    exit;
}

// If we get here, something went wrong
http_response_code(400);
echo json_encode([
    'status' => 'error', 
    'message' => 'Invalid request',
    'received_data' => $data
]);
exit;