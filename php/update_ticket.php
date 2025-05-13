<?php
require_once 'db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'agent') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$ticket_id = (int)$_POST['ticket_id'];
$status_change = $conn->real_escape_string($_POST['status_change']);
$update_text = $conn->real_escape_string($_POST['update_text']);
$agent_id = $_SESSION['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Update ticket status
    $stmt1 = $conn->prepare("UPDATE tickets SET status = ? WHERE ticket_id = ?");
    $stmt1->bind_param("si", $status_change, $ticket_id);
    $stmt1->execute();
    
    // Add update record
    $stmt2 = $conn->prepare("
        INSERT INTO ticket_updates (ticket_id, agent_id, update_text, status_change)
        VALUES (?, ?, ?, ?)
    ");
    $stmt2->bind_param("iiss", $ticket_id, $agent_id, $update_text, $status_change);
    $stmt2->execute();
    
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>