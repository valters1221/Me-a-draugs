<?php
session_start();

// If already logged in, redirect to admin dashboard
if(isset($_SESSION['admin_id'])) {
    header("Location: ../blog/list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <title>admin</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-6 text-center">Admin Login</h1>
            <form method="POST" action="authenticate.php" class="space-y-4">
                <input type="hidden" name="test_submit" value="1">
                <div>
                    <label class="block text-gray-700">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:border-blue-500">
                </div>

                <?php 
                // Display error message if any
                if(isset($_SESSION['login_error'])) {
                    echo '<div class="text-red-500 text-center">' . $_SESSION['login_error'] . '</div>';
                    unset($_SESSION['login_error']);
                }
                ?>

                <button type="submit"
                    class="w-full py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>

</html>