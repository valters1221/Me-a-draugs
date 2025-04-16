<?php
session_start();
require_once '../includes/config.php';

// For development without HTTPS, remove or comment out these lines
// ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1);  // This was preventing login without HTTPS
// ini_set('session.cookie_samesite', 'Strict');
session_regenerate_id(true);

// Rate limiting
$attempt_timeout = 900; // 15 minutes
$max_attempts = 5;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = time();
}

// Check if max attempts reached
if ($_SESSION['login_attempts'] >= $max_attempts) {
    if (time() - $_SESSION['first_attempt_time'] < $attempt_timeout) {
        $_SESSION['login_error'] = "Too many failed attempts. Please try again later.";
        header("Location: login.php");
        exit();
    } else {
        // Reset attempts after timeout
        $_SESSION['login_attempts'] = 0;
        $_SESSION['first_attempt_time'] = time();
    }
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

// Temporarily remove CSRF check for testing
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     header("Location: login.php");
//     exit();
// }

try {
    // Get and sanitize input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($password)) {
        throw new Exception("Please fill in all fields");
    }

    // Modify query to remove active check for now
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database error");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify credentials
    if ($user && password_verify($password, $user['password'])) {
        // Reset login attempts
        $_SESSION['login_attempts'] = 0;
        
        // Set session variables
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        
        // Update last login time - only if the column exists
        // try {
        //     $update_stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        //     $update_stmt->bind_param("i", $user['id']);
        //     $update_stmt->execute();
        //     $update_stmt->close();
        // } catch (Exception $e) {
        //     // Ignore last_login update errors
        // }
        
        header("Location: ../blog/list.php");
        exit();
    } else {
        // Increment failed attempts
        $_SESSION['login_attempts']++;
        throw new Exception("Invalid username or password");
    }

} catch (Exception $e) {
    $_SESSION['login_error'] = $e->getMessage();
    header("Location: login.php");
    exit();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>