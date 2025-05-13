<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $expected_role = $_POST['role'];

    $stmt = $conn->prepare("SELECT user_id, username, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === $expected_role) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: ../" . $user['role'] . "/dashboard.php");
                exit();
            } else {
                header("Location: ../" . $expected_role . "/login.php?error=wrong_role");
                exit();
            }
        }
    }
    
    header("Location: ../" . $expected_role . "/login.php?error=invalid_credentials");
    exit();
}
?>