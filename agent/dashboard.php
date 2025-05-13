<?php
require_once '../php/session_check.php';
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .status { padding: 3px 8px; border-radius: 3px; font-weight: bold; color: white; }
        .status.open { background: #f39c12; }
        .status.in-progress { background: #3498db; }
        .status.resolved { background: #2ecc71; }
        .status.closed { background: #95a5a6; }
        .ticket { margin-bottom: 20px; padding: 15px; background: white; border-radius: 5px; }
        #updateForm { display: none; margin-top: 20px; padding: 20px; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Agent Dashboard - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <a href="../php/logout.php" class="btn logout">Logout</a>
        </header>

        <section class="ticket-list">
            <h2>All Customer Tickets</h2>
            <div id="ticketsContainer">
                <p>Loading tickets...</p>
            </div>
        </section>

        <section class="update-form" id="updateForm">
            <h2>Update Ticket</h2>
            <form id="updateTicketForm">
                <input type="hidden" id="ticketId" name="ticket_id">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status_change" required>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="update_text">Update Message</label>
                    <textarea id="update_text" name="update_text" required></textarea>
                </div>
                <button type="submit" class="btn">Submit Update</button>
                <button type="button" class="btn cancel" onclick="document.getElementById('updateForm').style.display='none'">Cancel</button>
            </form>
        </section>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateForm = document.getElementById('updateForm');
        const updateTicketForm = document.getElementById('updateTicketForm');
        let currentTicketId = null;
        
        // Load all tickets
        function loadTickets() {
            fetch('../php/get_all_tickets.php')
                .then(response => response.json())
                .then(tickets => {
                    const container = document.getElementById('ticketsContainer');
                    container.innerHTML = '';
                    
                    if (tickets.length === 0) {
                        container.innerHTML = '<p>No tickets found.</p>';
                        return;
                    }
                    
                    tickets.forEach(ticket => {
                        const ticketEl = document.createElement('div');
                        ticketEl.className = 'ticket';
                        ticketEl.innerHTML = `
                            <h3>${ticket.subject} 
                                <span class="status ${ticket.status.toLowerCase().replace(' ', '-')}">
                                    ${ticket.status}
                                </span>
                            </h3>
                            <p><strong>From:</strong> ${ticket.customer_name}</p>
                            <p><strong>Created:</strong> ${new Date(ticket.created_at).toLocaleString()}</p>
                            <p>${ticket.description}</p>
                            <button class="btn update-btn" data-id="${ticket.ticket_id}">Update Ticket</button>
                        `;
                        container.appendChild(ticketEl);
                    });
                    
                    // Add event listeners to update buttons
                    document.querySelectorAll('.update-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            currentTicketId = this.getAttribute('data-id');
                            document.getElementById('ticketId').value = currentTicketId;
                            updateForm.style.display = 'block';
                            window.scrollTo(0, document.body.scrollHeight);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('ticketsContainer').innerHTML = '<p>Error loading tickets.</p>';
                });
        }
        
        // Handle ticket update submission
        updateTicketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../php/update_ticket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ticket updated successfully!');
                    this.reset();
                    updateForm.style.display = 'none';
                    loadTickets();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update ticket'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the ticket');
            });
        });
        
        // Initial load and refresh every 30 seconds
        loadTickets();
        setInterval(loadTickets, 30000);
    });
    </script>
</body>
</html>