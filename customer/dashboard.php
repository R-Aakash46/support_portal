<?php
require_once '../php/session_check.php';
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .ticket {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .status {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }
        .status.open { background: #f39c12; }
        .status.in-progress { background: #3498db; }
        .status.resolved { background: #2ecc71; }
        .status.closed { background: #95a5a6; }
        .update {
            background: #f9f9f9;
            padding: 10px;
            margin-top: 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <a href="../php/logout.php" class="btn logout">Logout</a>
        </header>

        <section class="ticket-form">
            <h2>Create New Ticket</h2>
            <form id="ticketForm">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <button type="submit" class="btn">Submit Ticket</button>
            </form>
        </section>

        <section class="ticket-list">
            <h2>Your Tickets</h2>
            <div id="ticketsContainer">
                <p>Loading your tickets...</p>
            </div>
        </section>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Submit new ticket
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../php/submit_ticket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Ticket submitted successfully!');
                    this.reset();
                    loadTickets();
                } else {
                    throw new Error(data.error || 'Failed to submit ticket');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
        });

        // Load customer tickets with updates
        function loadTickets() {
            fetch('../php/get_customer_tickets.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(tickets => {
                    const container = document.getElementById('ticketsContainer');
                    container.innerHTML = '';
                    
                    if (!tickets || tickets.length === 0) {
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
                            <p><strong>Created:</strong> ${new Date(ticket.created_at).toLocaleString()}</p>
                            <p>${ticket.description}</p>
                            
                            ${ticket.updates && ticket.updates.length > 0 ? `
                                <div class="updates">
                                    <h4>Updates</h4>
                                    ${ticket.updates.map(update => `
                                        <div class="update">
                                            <p><strong>${update.agent_name || 'System'}</strong> - 
                                            ${new Date(update.updated_at).toLocaleString()}</p>
                                            <p>Status changed to: <span class="status ${update.status_change.toLowerCase().replace(' ', '-')}">
                                                ${update.status_change}
                                            </span></p>
                                            <p>${update.update_text}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p>No updates yet.</p>'}
                        `;
                        container.appendChild(ticketEl);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('ticketsContainer').innerHTML = 
                        '<p>Error loading tickets. Please try again.</p>';
                });
        }
        
        // Initial load and refresh every 30 seconds
        loadTickets();
        setInterval(loadTickets, 30000);
    });
    </script>
</body>
</html>