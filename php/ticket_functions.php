<?php
require_once 'db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_all_tickets':
                if ($_SESSION['role'] !== 'agent') {
                    throw new Exception('Unauthorized access');
                }
                
                $stmt = $conn->prepare("
                    SELECT t.ticket_id, t.subject, t.description, t.status, t.created_at, 
                           u.username AS customer_name
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
                exit();

            case 'get_ticket_updates':
                if ($_SESSION['role'] !== 'agent') {
                    throw new Exception('Unauthorized access');
                }
                
                $ticket_id = (int)$_GET['ticket_id'];
                $stmt = $conn->prepare("
                    SELECT tu.update_text, tu.status_change, tu.updated_at, u.username AS agent_name
                    FROM ticket_updates tu
                    JOIN users u ON tu.agent_id = u.user_id
                    WHERE tu.ticket_id = ?
                    ORDER BY tu.updated_at DESC
                ");
                $stmt->bind_param("i", $ticket_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $updates = [];
                while ($row = $result->fetch_assoc()) {
                    $updates[] = $row;
                }
                
                echo json_encode($updates);
                exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_ticket':
                if ($_SESSION['role'] !== 'agent') {
                    throw new Exception('Unauthorized access');
                }
                
                $ticket_id = (int)($_POST['ticket_id'] ?? 0);
                $status_change = $conn->real_escape_string($_POST['status_change'] ?? '');
                $update_text = $conn->real_escape_string($_POST['update_text'] ?? '');
                
                if ($ticket_id <= 0) {
                    throw new Exception('Invalid ticket ID');
                }
                
                if (empty($status_change) || empty($update_text)) {
                    throw new Exception('Status and update text are required');
                }
                
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
                    $stmt2->bind_param("iiss", $ticket_id, $_SESSION['user_id'], $update_text, $status_change);
                    $stmt2->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    $conn->rollback();
                    throw new Exception('Database error: ' . $e->getMessage());
                }
                exit();
        }
    }
    
    throw new Exception('Invalid request');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit();
}
?>