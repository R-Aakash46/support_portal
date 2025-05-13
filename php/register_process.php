<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php?error=Invalid request");
    exit();
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$role = $_POST['role'];

// Validate inputs
if (empty($username) || empty($email) || empty($password) || empty($role)) {
    header("Location: ../register.php?error=All fields are required");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../register.php?error=Invalid email format");
    exit();
}

if (strlen($password) < 6) {
    header("Location: ../register.php?error=Password must be at least 6 characters");
    exit();
}

if (!in_array($role, ['customer', 'agent'])) {
    header("Location: ../register.php?error=Invalid role selected");
    exit();
}

// Check if email exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: ../register.php?error=Email already registered");
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

if ($stmt->execute()) {
    header("Location: ../register.php?success=1");
} else {
    header("Location: ../register.php?error=Registration failed. Please try again.");
}
exit();
?>