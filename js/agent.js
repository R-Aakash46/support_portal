document.addEventListener('DOMContentLoaded', function() {
    const updateForm = document.getElementById('updateForm');
    const updateTicketForm = document.getElementById('updateTicketForm');
    let currentTicketId = null;
    
    // Load all tickets
    function loadTickets() {
        fetch('../php/ticket_functions.php?action=get_all_tickets')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(tickets => {
                const container = document.getElementById('ticketsContainer');
                if (!container) return;
                
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
                            <span class="status ${ticket.status.toLowerCase().replace(' ', '-')}">${ticket.status}</span>
                        </h3>
                        <p class="meta">From: ${ticket.customer_name} | Created: ${new Date(ticket.created_at).toLocaleString()}</p>
                        <p class="description">${ticket.description}</p>
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
                const container = document.getElementById('ticketsContainer');
                if (container) {
                    container.innerHTML = '<p>Error loading tickets. Please refresh the page.</p>';
                }
            });
    }
    
    // Handle ticket update submission
    if (updateTicketForm) {
        updateTicketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_ticket');
            
            fetch('../php/ticket_functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Ticket updated successfully!');
                    updateTicketForm.reset();
                    updateForm.style.display = 'none';
                    loadTickets(); // Refresh the ticket list
                } else {
                    throw new Error(data.error || 'Failed to update ticket');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating ticket: ' + error.message);
            });
        });
    }
    
    // Initial load
    loadTickets();
});