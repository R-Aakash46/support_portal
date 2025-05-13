<?php
session_start();
// Only redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'agent' ? 'agent/dashboard.php' : 'customer/dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Customer Support Portal</h1>
        <div class="role-selection">
            <a href="customer/login.php" class="btn">Customer Login</a>
            <a href="agent/login.php" class="btn">Agent Login</a>
        </div>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>