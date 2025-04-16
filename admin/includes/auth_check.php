<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
    // Not logged in, redirect to login page
    header("Location: /index.php");
    exit();
}

// Set timeout for inactivity (e.g., 30 minutes)
$timeout = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // Session expired
    session_unset();     // Remove all session variables
    session_destroy();   // Destroy the session
    header("Location: index.php?msg=expired");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();