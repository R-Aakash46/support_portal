<?php
require_once 'db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'agent') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$stmt = $conn->prepare("
    SELECT t.*, u.username AS customer_name
    FROM tickets t
    JOIN users u ON t.customer_id = u.user_id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

echo json_encode($tickets);
?>