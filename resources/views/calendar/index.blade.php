@extends('layout.app')
@section('title', 'Calendar')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

<div class="page-header">

    <a href="{{ route('calendar.create') ?? url('/calendar/create') }}" class="btn btn-primary">+ Add Event</a>
</div>

<div class="calendar-shell">
    <div class="calendar-panel">
        <div id="calendarAlert" class="calendar-alert"></div>
        <div id="calendar"></div>
    </div>
</div>

@push('styles')
<link href="{{ asset('hr.css') }}" rel="stylesheet">
@endpush

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    let calendar;

    const typeColors = {
        'Travel Order': '#2563eb',
        'Event': '#16a34a',
        'Birthday': '#db2777'
    };

    function getCsrfToken() {
        return document.querySelector('input[name="_token"]')?.value || '';
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

    function normalizeDateValue(dateValue) {
        if (!dateValue) return '';
        return String(dateValue).split('T')[0];
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
            // Clicking an event redirects to edit page? For now just alert (or you can open a separate edit page)
            eventClick: function(info) {
                const event = info.event;
                alert(`Event: ${event.title}\nType: ${event.extendedProps.type}\nStart: ${event.extendedProps.start_date}\nEnd: ${event.extendedProps.end_date}`);
            }
        });

        calendar.render();
    });
</script>
@endsection