<?php
require_once 'db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$customer_id = $_SESSION['user_id'];
$subject = $conn->real_escape_string($_POST['subject']);
$description = $conn->real_escape_string($_POST['description']);

$stmt = $conn->prepare("INSERT INTO tickets (customer_id, subject, description) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $customer_id, $subject, $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>