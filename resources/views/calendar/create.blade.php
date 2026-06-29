@extends('layout.app')
@section('content')
    @push('styles')
        <link href="{{ asset('hr.css') }}" rel="stylesheet">
    @endpush

    {{-- Inline fallback styles – guarantee the modal is styled even if hr.css doesn't load --}}
    <style>
        /* Modal overlay – exactly as defined in hr.css */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            z-index: 9999;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            width: 100%;
            max-width: 700px;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
            background: #fafbfc;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
            letter-spacing: -0.3px;
        }

        .modal-subtitle {
            margin-top: 0.4rem;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .modal-close {
            background: transparent;
            border: 1.5px solid #cbd5e1;
            color: #475569;
            border-radius: 5px;
            padding: 0.45rem 0.65rem;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            line-height: 1;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0 2rem 2rem 2rem;
        }

        .btn-primary {
            background: #0d6efd;
            border: none;
            color: #fff;
            padding: 0.6rem 1.2rem;
            border-radius: 0.4rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #0b5ed7;
        }

        /* Bootstrap grid helpers (optional) */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0.5rem;
            box-sizing: border-box;
        }
        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0.5rem;
            box-sizing: border-box;
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid #ced4da;
            border-radius: 0.4rem;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .gap-2 {
            gap: 0.5rem;
        }
        .d-flex {
            display: flex;
        }
        .align-items-center {
            align-items: center;
        }
    </style>

    <div class="modal-overlay active" id="eventCreateModalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">Create Event</h2>
                    <div class="modal-subtitle">Monthly calendar events (travel orders, events, birthdays, tasks)</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('calendar.index') }}" class="modal-close" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">&larr; Back</a>
                    <button class="modal-close" type="button" onclick="closeCreateModal()" aria-label="Close">&times;</button>
                </div>
            </div>

            <form method="POST" action="{{ url('/calendar') }}" id="eventCreateForm">
                <div class="modal-body">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label">Event Type</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Select event type...</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-close" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeCreateModal() {
            window.location.href = "{{ route('calendar.index') }}";
        }

        // Populate event type dropdown (same as in the main calendar modal)
        async function fetchEventTypes() {
            try {
                const response = await fetch('/api/events/types');
                return await response.json();
            } catch (error) {
                console.error('Error fetching event types:', error);
                return ['Travel Order', 'Event', 'Birthday'];
            }
        }

        async function populateTypeDropdown() {
            const select = document.getElementById('type');
            const types = await fetchEventTypes();

            select.innerHTML = '<option value="">Select event type...</option>';
            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                select.appendChild(option);
            });
        }

        document.addEventListener('DOMContentLoaded', populateTypeDropdown);
    </script>
@endsection