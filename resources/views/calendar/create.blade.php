@extends('layout.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Create Event</h3>
        <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <form method="POST" action="{{ route('calendar.store') ?? url('/calendar') }}" id="eventCreateForm">
        @csrf

        <div class="card shadow-sm">
            <div class="card-body">
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

            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-primary">Create Event</button>
            </div>
        </div>
    </form>
</div>
@endsection

