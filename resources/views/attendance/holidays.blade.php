@extends('layout.app')
@section('title', 'Attendance Holidays')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Attendance Holiday Schedule</h1>
            <div class="text-muted small">Set holiday dates here. Only holidays that fall on Friday switch the office to the 8:00 AM to 5:00 PM schedule.</div>
        </div>

        <form class="row g-2 align-items-end" method="GET" action="{{ route('attendance-holidays.index') }}">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Month</label>
                <input type="number" min="1" max="12" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Year</label>
                <input type="number" min="2020" max="2100" name="year" class="form-control" value="{{ $year }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4 align-items-stretch holiday-layout">
        <div class="col-12 col-xl-4">
            <div class="alert alert-info mb-4">
                Friday holiday = <strong>8:00 AM to 5:00 PM</strong>. Holiday on Monday to Thursday still uses the regular <strong>7:00 AM to 7:00 PM</strong> schedule.
            </div>

            <div class="holiday-panel h-100">
                <div class="holiday-panel-header d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Add Holiday</div>
                    <span class="badge text-bg-light text-muted">Schedule rules</span>
                </div>
                <form method="POST" action="{{ route('attendance-holidays.store') }}" class="row g-3 holiday-form-grid">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Holiday Date</label>
                        <input type="date" name="holiday_date" class="form-control" value="{{ old('holiday_date', now()->toDateString()) }}" required>
                        @error('holiday_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Holiday Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Holiday name" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional notes" value="{{ old('notes') }}">
                        @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100" type="submit">Save Holiday</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="holiday-panel h-100">
                <div class="holiday-panel-header">
                    <div class="fw-semibold">Holiday Dates for {{ $monthStart->format('F Y') }}</div>
                </div>
                <div class="table-responsive holiday-table-wrapper">
                    <table class="table table-hover align-middle mb-0 holiday-table">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Title</th>
                                <th>Schedule Effect</th>
                                <th>Notes</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                @php($isFriday = $holiday->holiday_date->isFriday())
                                <tr>
                                    <td class="fw-semibold">{{ $holiday->holiday_date->format('M d, Y') }}</td>
                                    <td>{{ $holiday->holiday_date->format('l') }}</td>
                                    <td>{{ $holiday->title }}</td>
                                    <td>
                                        <span class="badge {{ $isFriday ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $isFriday ? 'Holiday schedule' : 'Regular schedule' }}
                                        </span>
                                    </td>
                                    <td>{{ $holiday->notes ?: 'N/A' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('attendance-holidays.destroy', $holiday) }}" onsubmit="return confirm('Remove this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No holidays found for this month.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .holiday-layout {
        align-items: stretch;
    }

    .holiday-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
    }

    .holiday-panel-header {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .holiday-form-grid .form-control {
        min-height: 44px;
    }

    .holiday-table-wrapper {
        max-height: min(68vh, 720px);
    }

    .holiday-table th,
    .holiday-table td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>
@endsection
