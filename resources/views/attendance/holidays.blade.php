@extends('layout.app')
@section('title', 'Holiday Dates')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Holiday Dates</h1>
            <div class="text-muted small">Philippine holidays are loaded automatically, and HR can add or override dates when needed.</div>
        </div>

        <form class="row g-2 align-items-end" method="GET" action="{{ route('attendance.holidays.index') }}">
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
            <div class="col-auto">
                <a href="{{ route('attendance.index', ['month' => $month, 'year' => $year]) }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Back to Attendance
                </a>
            </div>
        </form>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="alert alert-info">
        Official Philippine holidays are generated automatically for the selected month. HR-added holidays remain editable and can override the same date.
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="border rounded-4 p-3 bg-light h-100">
                <div class="fw-semibold mb-3">Add Holiday</div>
                <form method="POST" action="{{ route('attendance.holidays.store') }}" class="row g-3">
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
            <div class="border rounded-4 p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <div class="fw-semibold">Holiday Dates for {{ $monthStart->format('F Y') }}</div>
                        <div class="text-muted small">Official Philippine holidays appear together with HR-added holidays.</div>
                    </div>
                    <span class="badge text-bg-secondary">{{ $holidays->count() }} date(s)</span>
                </div>

                <div class="table-responsive holiday-table-wrapper">
                    <table class="table table-hover align-middle mb-0 holiday-table">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Title</th>
                                <th>Source</th>
                                <th>Notes</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    <td class="fw-semibold">{{ $holiday->holiday_date->format('M d, Y') }}</td>
                                    <td>{{ $holiday->holiday_date->format('l') }}</td>
                                    <td>{{ $holiday->title }}</td>
                                    <td>
                                        <span class="badge {{ ($holiday->is_custom ?? false) ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                            {{ $holiday->source ?? 'Philippine calendar' }}
                                        </span>
                                    </td>
                                    <td>{{ $holiday->notes ?: 'N/A' }}</td>
                                    <td class="text-end">
                                        @if($holiday->is_custom ?? false)
                                            <form method="POST" action="{{ route('attendance.holidays.destroy', $holiday->id) }}" onsubmit="return confirm('Remove this holiday?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Official holiday</span>
                                        @endif
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
