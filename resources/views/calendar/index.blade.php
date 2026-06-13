@extends('layout.app')
@section('title', 'Calendar')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

<div class="page-header">
    <div class="calendar-header">
        <div class="page-title">Calendar</div>
        <div class="page-subtitle">Monthly view for travel orders, events, birthdays, and tasks.</div>
    </div>
    <button onclick="openEventModal()" class="btn btn-primary">+ Add Event</button>
</div>

<div class="calendar-shell">
    <div class="calendar-panel">
        <div id="calendarAlert" class="calendar-alert"></div>
        <div id="calendar"></div>
    </div>

    <aside class="calendar-sidebar">
        <div class="sidebar-section">
            <div class="section-title">Event Types</div>
            <div class="legend-list">
                <div class="legend-item">
                    <span class="legend-dot" style="background:#2563eb;"></span>
                    <span>Travel Order</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background:#16a34a;"></span>
                    <span>Event</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background:#db2777;"></span>
                    <span>Birthday</span>
                </div>
            </div>
        </div>
    </aside>
</div>

<div class="modal-overlay" id="eventModalOverlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="eventModalTitle">Create New Event</h2>
            <button class="modal-close" onclick="closeEventModal()">&times;</button>
        </div>
        <form id="modalEventForm">
            @csrf
            <input type="hidden" id="modalEventId">
            <div class="modal-body">
                <div class="event-meta" id="eventMeta" style="display:none;">
                    <span class="event-meta-dot" id="eventMetaDot"></span>
                    <span id="eventMetaText"></span>
                </div>
                <div class="modal-form-group">
                    <label for="modalEventTitle">Title</label>
                    <input type="text" id="modalEventTitle" name="title" required placeholder="Event title">
                </div>
                <div class="modal-form-group">
                    <label for="modalEventType">Event Type</label>
                    <select id="modalEventType" name="type" required>
                        <option value="">Select event type...</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label for="modalEventStartDate">Start Date</label>
                    <input type="date" id="modalEventStartDate" name="start_date" required>
                </div>
                <div class="modal-form-group">
                    <label for="modalEventEndDate">End Date</label>
                    <input type="date" id="modalEventEndDate" name="end_date" required>
                </div>
                <div class="modal-form-group">
                    <label for="modalEventDescription">Description</label>
                    <textarea id="modalEventDescription" name="description" placeholder="Event description"></textarea>
                </div>
                <div class="modal-form-group">
                    <label for="modalEventRemarks">Remarks</label>
                    <textarea id="modalEventRemarks" name="remarks" placeholder="Additional remarks"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-danger" id="deleteEventButton" onclick="confirmDeleteEvent()" style="display:none;">Delete</button>
                <div class="modal-footer-actions">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeEventModal()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-primary" id="saveEventButton">Create Event</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    let calendar;
    let deleteArmed = false;

    const typeColors = {
        'Travel Order': '#2563eb',
        'Event': '#16a34a',
        'Birthday': '#db2777'
    };

    function getCsrfToken() {
        return document.querySelector('input[name="_token"]').value;
    }

    function showCalendarAlert(message, type = 'success') {
        const alert = document.getElementById('calendarAlert');
        alert.textContent = message;
        alert.className = `calendar-alert ${type} show`;

        window.clearTimeout(alert.dataset.timer);
        alert.dataset.timer = window.setTimeout(() => {
            alert.classList.remove('show');
        }, 3200);
    }

    function formatDisplayDate(dateValue) {
        if (!dateValue) {
            return '';
        }

        return new Date(`${dateValue}T00:00:00`).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function normalizeDateValue(dateValue) {
        if (!dateValue) {
            return '';
        }

        return String(dateValue).split('T')[0];
    }

    function setModalMode(mode, eventData = null) {
        const isEdit = mode === 'edit';
        const form = document.getElementById('modalEventForm');
        const deleteButton = document.getElementById('deleteEventButton');
        const eventMeta = document.getElementById('eventMeta');

        form.reset();
        deleteArmed = false;
        deleteButton.textContent = 'Delete';
        deleteButton.style.display = isEdit ? 'inline-flex' : 'none';
        eventMeta.style.display = isEdit ? 'flex' : 'none';

        document.getElementById('eventModalTitle').textContent = isEdit ? 'Edit Event' : 'Create New Event';
        document.getElementById('saveEventButton').textContent = isEdit ? 'Save Changes' : 'Create Event';
        document.getElementById('modalEventId').value = eventData?.id || '';

        if (eventData) {
            document.getElementById('modalEventTitle').value = eventData.title || '';
            document.getElementById('modalEventType').value = eventData.type || '';
            document.getElementById('modalEventStartDate').value = normalizeDateValue(eventData.start_date);
            document.getElementById('modalEventEndDate').value = normalizeDateValue(eventData.end_date);
            document.getElementById('modalEventDescription').value = eventData.description || '';
            document.getElementById('modalEventRemarks').value = eventData.remarks || '';
            document.getElementById('eventMetaDot').style.background = typeColors[eventData.type] || '#16a34a';
            document.getElementById('eventMetaText').textContent = `${eventData.type} | ${formatDisplayDate(eventData.start_date)} to ${formatDisplayDate(eventData.end_date)}`;
        }
    }

    function openEventModal(eventData = null) {
        setModalMode(eventData ? 'edit' : 'create', eventData);
        document.getElementById('eventModalOverlay').classList.add('active');
    }

    function closeEventModal() {
        document.getElementById('eventModalOverlay').classList.remove('active');
        setModalMode('create');
    }

    window.onclick = function (event) {
        const modal = document.getElementById('eventModalOverlay');
        if (event.target == modal) {
            closeEventModal();
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEventModal();
        }
    });

    async function fetchEventTypes() {
        try {
            const response = await fetch('/api/events/types');
            return await response.json();
        } catch (error) {
            console.error('Error fetching event types:', error);
            return ['Travel Order', 'Event', 'Birthday'];
        }
    }

    async function populateEventTypeDropdown() {
        const select = document.getElementById('modalEventType');
        const types = await fetchEventTypes();

        select.innerHTML = '<option value="">Select event type...</option>';
        types.forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            select.appendChild(option);
        });
    }

    function toCalendarEvent(event) {
        return {
            id: event.id,
            title: event.title,
            start: normalizeDateValue(event.start_date),
            end: new Date(new Date(normalizeDateValue(event.end_date)).getTime() + 24*60*60*1000).toISOString().split('T')[0],
            color: typeColors[event.type] || '#16a34a',
            extendedProps: {
                id: event.id,
                type: event.type,
                description: event.description,
                remarks: event.remarks,
                start_date: normalizeDateValue(event.start_date),
                end_date: normalizeDateValue(event.end_date)
            }
        };
    }

    async function fetchEvents() {
        try {
            const response = await fetch('/api/events');
            const events = await response.json();
            return events.map(toCalendarEvent);
        } catch (error) {
            console.error('Error fetching events:', error);
            return [];
        }
    }

    async function refreshCalendarEvents() {
        const events = await fetchEvents();
        calendar.removeAllEvents();
        events.forEach(event => calendar.addEvent(event));
    }

    document.addEventListener('DOMContentLoaded', async function () {
        await populateEventTypeDropdown();

        const today = new Date();
        const customEvents = await fetchEvents();
        const calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: today,
            height: 'auto',
            nowIndicator: true,
            dayMaxEvents: 3,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            events: customEvents,
            eventClick: function(info) {
                const event = info.event;
                openEventModal({
                    id: event.extendedProps.id || event.id,
                    title: event.title,
                    type: event.extendedProps.type,
                    start_date: event.extendedProps.start_date,
                    end_date: event.extendedProps.end_date,
                    description: event.extendedProps.description,
                    remarks: event.extendedProps.remarks
                });
            }
        });

        calendar.render();
    });

    document.getElementById('modalEventForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const eventId = document.getElementById('modalEventId').value;
        const formData = {
            title: document.getElementById('modalEventTitle').value,
            start_date: document.getElementById('modalEventStartDate').value,
            end_date: document.getElementById('modalEventEndDate').value,
            description: document.getElementById('modalEventDescription').value,
            remarks: document.getElementById('modalEventRemarks').value,
            type: document.getElementById('modalEventType').value,
            _token: getCsrfToken()
        };

        try {
            const response = await fetch(eventId ? `/api/events/${eventId}` : '/api/events', {
                method: eventId ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData._token
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                closeEventModal();
                await refreshCalendarEvents();
                showCalendarAlert(eventId ? 'Event updated successfully.' : 'Event created successfully.');
            } else {
                showCalendarAlert('Please check the event details and try again.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showCalendarAlert('Unable to save event right now.', 'error');
        }
    });

    async function confirmDeleteEvent() {
        const eventId = document.getElementById('modalEventId').value;
        const deleteButton = document.getElementById('deleteEventButton');

        if (!eventId) {
            return;
        }

        if (!deleteArmed) {
            deleteArmed = true;
            deleteButton.textContent = 'Confirm Delete';
            return;
        }

        try {
            const response = await fetch(`/api/events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            if (response.ok) {
                closeEventModal();
                await refreshCalendarEvents();
                showCalendarAlert('Event deleted successfully.');
            } else {
                showCalendarAlert('Unable to delete event right now.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showCalendarAlert('Unable to delete event right now.', 'error');
        }
    }
</script>
@endsection
