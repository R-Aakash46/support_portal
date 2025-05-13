document.addEventListener('DOMContentLoaded', function() {
    // Submit new ticket
    const ticketForm = document.getElementById('ticketForm');
    if (ticketForm) {
        ticketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'submit_ticket');
            
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
                    alert('Ticket submitted successfully!');
                    ticketForm.reset();
                    loadTickets();
                } else {
                    throw new Error(data.error || 'Failed to submit ticket');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting ticket: ' + error.message);
            });
        });
    }

    // Load customer tickets
    function loadTickets() {
        fetch('../php/ticket_functions.php?action=get_customer_tickets')
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
                
                // Group tickets by ticket_id (since updates are separate rows)
                const groupedTickets = {};
                tickets.forEach(ticket => {
                    if (!groupedTickets[ticket.ticket_id]) {
                        groupedTickets[ticket.ticket_id] = {
                            ...ticket,
                            updates: []
                        };
                    }
                    if (ticket.update_text) {
                        groupedTickets[ticket.ticket_id].updates.push({
                            text: ticket.update_text,
                            status: ticket.status_change,
                            date: ticket.updated_at,
                            agent: ticket.agent_name
                        });
                    }
                });
                
                // Render each ticket
                Object.values(groupedTickets).forEach(ticket => {
                    const ticketEl = document.createElement('div');
                    ticketEl.className = 'ticket';
                    ticketEl.innerHTML = `
                        <h3>${ticket.subject} <span class="status ${ticket.status.toLowerCase().replace(' ', '-')}">${ticket.status}</span></h3>
                        <p class="date">Created: ${new Date(ticket.created_at).toLocaleString()}</p>
                        <p class="description">${ticket.description}</p>
                        
                        ${ticket.updates.length > 0 ? `
                            <div class="updates">
                                <h4>Updates</h4>
                                ${ticket.updates.map(update => `
                                    <div class="update">
                                        <p class="update-meta">
                                            <strong>${update.agent || 'System'}</strong> 
                                            marked as <span class="status ${update.status.toLowerCase().replace(' ', '-')}">${update.status}</span>
                                            on ${new Date(update.date).toLocaleString()}
                                        </p>
                                        <p>${update.text}</p>
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
                const container = document.getElementById('ticketsContainer');
                if (container) {
                    container.innerHTML = '<p>Error loading tickets. Please try again.</p>';
                }
            });
    }
    
    // Initial load
    loadTickets();
});