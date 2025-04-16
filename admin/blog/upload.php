<?php
session_start();
require_once '../includes/auth_check.php';

// Initialize tracking session if it doesn't exist
if (!isset($_SESSION['temp_uploaded_images'])) {
    $_SESSION['temp_uploaded_images'] = [];
}

// Define upload path - going up two levels to reach images/blog
$upload_dir = dirname(dirname(dirname(__FILE__))) . '/images/blog/';

// Make sure the upload directory exists
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Response array
$response = [];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Get file info
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Allowed file extensions
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Validate file type
    if (in_array($file_ext, $allowed)) {
        // Create unique filename
        $new_filename = 'blog_' . uniqid() . '.' . $file_ext;
        $destination = $upload_dir . $new_filename;

        // Maximum file size (5MB)
        $max_size = 5 * 1024 * 1024;

        if ($file_size <= $max_size) {
            // Try to move the uploaded file
            if (move_uploaded_file($file_tmp, $destination)) {
                // Dynamically construct full URL
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $full_url = $protocol . '://' . $host . '/images/blog/' . $new_filename;

                // Track the uploaded image
                if (!in_array($new_filename, $_SESSION['temp_uploaded_images'])) {
                    $_SESSION['temp_uploaded_images'][] = $new_filename;
                }

                // Success response with the full URL
                $response = [
                    'location' => $full_url
                ];
            } else {
                // Upload failed
                $response = [
                    'error' => 'Failed to upload file.'
                ];
                http_response_code(500);
            }
        } else {
            // File too large
            $response = [
                'error' => 'File size must be less than 5MB.'
            ];
            http_response_code(400);
        }
    } else {
        // Invalid file type
        $response = [
            'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed)
        ];
        http_response_code(400);
    }
} else {
    // Handle upload error
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_INI_SIZE:
            $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = 'The uploaded file was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = 'No file was uploaded';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $message = 'Missing a temporary folder';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $message = 'Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $message = 'A PHP extension stopped the file upload';
            break;
        default:
            $message = 'Unknown upload error';
    }
    
    $response = [
        'error' => $message
    ];
    http_response_code(400);
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);