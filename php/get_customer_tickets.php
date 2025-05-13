<?php
require_once 'db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'customer') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$customer_id = $_SESSION['user_id'];

// First get all tickets for this customer
$ticketStmt = $conn->prepare("
    SELECT * FROM tickets 
    WHERE customer_id = ?
    ORDER BY created_at DESC
");
$ticketStmt->bind_param("i", $customer_id);
$ticketStmt->execute();
$tickets = $ticketStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Then get updates for each ticket
$result = [];
foreach ($tickets as $ticket) {
    $updateStmt = $conn->prepare("
        SELECT tu.*, u.username as agent_name 
        FROM ticket_updates tu
        LEFT JOIN users u ON tu.agent_id = u.user_id
        WHERE tu.ticket_id = ?
        ORDER BY tu.updated_at DESC
    ");
    $updateStmt->bind_param("i", $ticket['ticket_id']);
    $updateStmt->execute();
    $updates = $updateStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $ticket['updates'] = $updates;
    $result[] = $ticket;
}

echo json_encode($result);
?>