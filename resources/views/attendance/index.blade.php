@extends('layout.app')
@section('title', 'Attendance')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Attendance Monitor</h1>
            <div class="text-muted small">Track lates, absences, leave follow-ups, and memo thresholds for HR monitoring.</div>
        </div>

        <form class="row g-2 align-items-end" method="GET" action="{{ route('attendance.index') }}">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
            </div>
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100 bg-light">
                <div class="text-muted small">Lates today</div>
                <div class="fs-3 fw-bold">{{ $summary['late_today'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100 bg-light">
                <div class="text-muted small">Absences today</div>
                <div class="fs-3 fw-bold">{{ $summary['absent_today'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100 bg-light">
                <div class="text-muted small">Employees at 7+ lates</div>
                <div class="fs-3 fw-bold">{{ $summary['employees_with_7_or_more_lates'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100 bg-light">
                <div class="text-muted small">Employees at 10+ lates</div>
                <div class="fs-3 fw-bold">{{ $summary['employees_with_10_or_more_lates'] }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Record Attendance</div>
                <div class="text-muted small">Log a present, late, absent, or leave status for a specific employee.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('attendance.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->emp_no }}" @selected(old('employee_id') == $employee->emp_no)>
                                {{ $employee->full_name }} @if($employee->Role) - {{ $employee->Role }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="{{ old('attendance_date', $selectedDate) }}" required>
                    @error('attendance_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'leave' => 'Leave'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'present') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Check-in Time</label>
                    <input type="time" name="check_in_at" class="form-control" value="{{ old('check_in_at') }}">
                    @error('check_in_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Minutes Late</label>
                    <input type="number" min="0" name="minutes_late" class="form-control" value="{{ old('minutes_late') }}">
                    @error('minutes_late')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                    @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-save"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white p-0">
            <button class="btn w-100 d-flex align-items-center justify-content-between gap-2 text-start px-3 py-3 border-0 bg-transparent"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#dailyRecordsCollapse"
                    aria-expanded="true"
                    aria-controls="dailyRecordsCollapse">
                <span>
                    <span class="fw-semibold">Records for {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</span>
                    <span class="text-muted small d-block">{{ $recordsForDate->count() }} record(s)</span>
                </span>
                <span class="d-inline-flex align-items-center gap-2">
                    <span class="badge text-bg-secondary rounded-pill">{{ $recordsForDate->count() }} record(s)</span>
                    <i class="bi bi-chevron-down records-chevron"></i>
                </span>
            </button>
        </div>
        <div class="collapse show" id="dailyRecordsCollapse">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Minutes Late</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordsForDate as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->employee?->full_name ?? $record->employee_id }}</td>
                                <td>{{ $record->employee?->Role ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $record->status === 'late' ? 'bg-warning text-dark' : ($record->status === 'absent' ? 'bg-danger' : ($record->status === 'leave' ? 'bg-info text-dark' : 'bg-success')) }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td>{{ $record->check_in_at?->format('h:i A') ?? 'N/A' }}</td>
                                <td>{{ $record->minutes_late !== null ? $record->minutes_late : '—' }}</td>
                                <td>{{ $record->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No attendance records found for this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white p-0">
            <button class="btn w-100 d-flex align-items-center justify-content-between gap-2 text-start px-3 py-3 border-0 bg-transparent"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#monthlyTrackerCollapse"
                    aria-expanded="true"
                    aria-controls="monthlyTrackerCollapse">
                <span>
                    <span class="fw-semibold">Monthly Late and Absence Tracker</span>
                    <span class="text-muted small d-block">Month range: {{ $monthStart->format('M d, Y') }} to {{ $monthEnd->format('M d, Y') }}</span>
                </span>
                <span class="d-inline-flex align-items-center gap-2">
                    <span class="badge text-bg-secondary rounded-pill">{{ count($employeeRows) }} employee(s)</span>
                    <i class="bi bi-chevron-down monthly-tracker-chevron"></i>
                </span>
            </button>
        </div>
        <div class="collapse show" id="monthlyTrackerCollapse">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th class="text-center">Lates</th>
                            <th class="text-center">Absences</th>
                            <th>Warning</th>
                            <th>Memo Flag</th>
                            <th>Leave Follow-up</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeeRows as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row['employee']->full_name }}</td>
                                <td>{{ $row['employee']->Role ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $row['late_count'] >= 10 ? 'bg-danger' : ($row['late_count'] >= 7 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ $row['late_count'] }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $row['absence_count'] }}</td>
                                <td>
                                    <span class="badge {{ $row['warning_sent'] ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                        {{ $row['warning_sent'] ? 'Sent' : 'Pending' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $row['memo_flagged'] ? 'bg-danger' : 'bg-light text-dark' }}">
                                        {{ $row['memo_flagged'] ? 'Flagged' : 'Pending' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $row['absence_follow_up_sent'] ? 'bg-info text-dark' : 'bg-light text-dark' }}">
                                        {{ $row['absence_follow_up_sent'] ? 'Sent' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No employees available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .monthly-tracker-chevron,
        .records-chevron {
            transition: transform 0.2s ease;
        }
        [data-bs-toggle="collapse"][aria-expanded="false"] .monthly-tracker-chevron,
        [data-bs-toggle="collapse"][aria-expanded="false"] .records-chevron {
            transform: rotate(-90deg);
        }
    </style>
</div>
@endsection
