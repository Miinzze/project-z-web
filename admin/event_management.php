<div class="admin-container">
    <h1 class="admin-title">Event Management</h1>
    
    <div class="admin-form">
        <h2>Neues Event erstellen</h2>
        <form id="event-form">
            <div class="form-group">
                <label>Titel</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Beschreibung</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Datum & Uhrzeit</label>
                <input type="datetime-local" name="event_date" required>
            </div>
            <div class="form-group">
                <label>Dauer (Minuten)</label>
                <input type="number" name="duration" value="120" required>
            </div>
            <div class="form-group">
                <label>Event-Typ</label>
                <select name="event_type" required>
                    <option value="rp">RP-Event</option>
                    <option value="party">Party</option>
                    <option value="action">Server-Aktion</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Event speichern</button>
        </form>
    </div>
    
    <div class="admin-list">
        <h2>Kommende Events</h2>
        <div class="event-list" id="admin-event-list">
            <!-- Wird via JavaScript befüllt -->
        </div>
    </div>
</div>

<script>
// JavaScript für Event-Management (AJAX etc.)
document.addEventListener('DOMContentLoaded', function() {
    const eventForm = document.getElementById('event-form');
    const eventList = document.getElementById('admin-event-list');
    
    // Event-Formular verarbeiten
    eventForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(eventForm);
        
        fetch('/api/save_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Event gespeichert!');
                eventForm.reset();
                loadEvents();
            } else {
                alert('Fehler: ' + data.error);
            }
        });
    });
    
    // Events laden
    function loadEvents() {
        fetch('/api/get_events.php?admin=1')
            .then(response => response.json())
            .then(events => {
                renderEventList(events);
            });
    }
    
    // Event-Liste rendern
    function renderEventList(events) {
        eventList.innerHTML = '';
        
        if (events.length === 0) {
            eventList.innerHTML = '<p>Keine bevorstehenden Events</p>';
            return;
        }
        
        events.forEach(event => {
            const eventDate = new Date(event.event_date);
            const eventElement = document.createElement('div');
            eventElement.className = 'admin-event-card';
            eventElement.innerHTML = `
                <div class="event-header">
                    <h3>${event.title}</h3>
                    <div class="event-actions">
                        <button class="btn btn-sm edit-event" data-id="${event.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-event" data-id="${event.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="event-date">
                    ${eventDate.toLocaleDateString('de-DE', { 
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    })}
                </div>
                <div class="event-description">${event.description}</div>
            `;
            eventList.appendChild(eventElement);
        });
    }
    
    // Initial laden
    loadEvents();
});
</script>