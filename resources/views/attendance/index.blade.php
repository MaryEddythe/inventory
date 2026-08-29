@extends('layout.app')
@section('title', 'Attendance')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Attendance Monitor</h1>
            <div class="text-muted small">Track lates, absences, leave follow-ups, memo thresholds, and the Philippine holiday calendar in one place.</div>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-end">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#attendanceHolidayModal">
                <i class="bi bi-calendar2-event"></i> Holiday Dates
            </button>

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
                <div class="fw-semibold">Schedule for Selected Date</div>
                <div class="text-muted small">{{ $selectedDate }} is using {{ $selectedScheduleLabel }}</div>
            </div>
            <span class="badge {{ $selectedScheduleType === 'holiday' ? 'bg-info text-dark' : 'bg-secondary' }}">
                {{ ucfirst($selectedScheduleType) }}
            </span>
        </div>
        <div class="card-body">
            <div class="text-muted small mb-2">
                Regular: 7:00 AM to 7:00 PM. Holiday: 8:00 AM to 5:00 PM. In both cases, 8:01 AM onward is late.
            </div>
            @if($selectedHoliday)
                <div class="alert alert-info mb-0">
                    Holiday day: <strong>{{ $selectedHoliday->title }}</strong>
                    @if($selectedHoliday->notes)
                        <div class="small mt-1">{{ $selectedHoliday->notes }}</div>
                    @endif
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No holiday is set for this date. Attendance will use the regular schedule.
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Record Attendance</div>
                <div class="text-muted small">Log attendance using the regular or holiday schedule.</div>
            </div>
            <div class="text-muted small">
                Late cutoff: 8:00 AM. Expected logout depends on the selected schedule.
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
                    <label class="form-label">Check-out Time</label>
                    <input type="time" name="check_out_at" class="form-control" value="{{ old('check_out_at') }}">
                    @error('check_out_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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

    @if(false)
    <div class="card shadow-sm mb-4 d-none">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Holiday Dates</div>
                <div class="text-muted small">HR can mark any date as holiday and the attendance schedule switches automatically.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('attendance.holidays.store') }}" class="row g-3 mb-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Holiday Date</label>
                    <input type="date" name="holiday_date" class="form-control" value="{{ $selectedDate }}" required>
                    @error('holiday_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Holiday Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Holiday name" required>
                    @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                    @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100" type="submit">Add</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Notes</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidaysForMonth as $holiday)
                            <tr>
                                <td class="fw-semibold">{{ $holiday->holiday_date->format('M d, Y') }}</td>
                                <td>{{ $holiday->title }}</td>
                                <td>{{ $holiday->notes ?: '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('attendance.holidays.destroy', $holiday) }}" onsubmit="return confirm('Remove this holiday?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No holidays found for this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif

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
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Expected Logout</th>
                            <th>Minutes Late</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordsForDate as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->employee?->full_name ?? $record->employee_id }}</td>
                                <td>{{ $record->employee?->Role ?? 'N/A' }}</td>
                                <td>{{ data_get($schedules, $record->schedule_type . '.label', ucfirst($record->schedule_type)) }}</td>
                                <td>
                                    <span class="badge {{ $record->status === 'late' ? 'bg-warning text-dark' : ($record->status === 'absent' ? 'bg-danger' : ($record->status === 'leave' ? 'bg-info text-dark' : 'bg-success')) }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td>{{ $record->check_in_at?->format('h:i A') ?? 'N/A' }}</td>
                                <td>{{ $record->check_out_at?->format('h:i A') ?? 'N/A' }}</td>
                                <td>
                                    @if($record->check_in_at)
                                        @php
                                            $schedule = $schedules[$record->schedule_type] ?? $schedules[config('attendance.default_schedule', 'regular')];
                                            $expectedLogout = $record->check_in_at->copy()->addMinutes((int) ($schedule['checkout_offset_minutes'] ?? 0));
                                        @endphp
                                        {{ $expectedLogout->format('h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $record->minutes_late !== null ? $record->minutes_late : '—' }}</td>
                                <td>{{ $record->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No attendance records found for this date.</td></tr>
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
                            <th>Schedule</th>
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
                                <td>{{ $selectedSchedule['label'] ?? 'Regular' }}</td>
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
                                <td colspan="8" class="text-center text-muted py-4">No employees available.</td>
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

@push('modals')
    @include('attendance.partials.holiday-modal')
@endpush
@endsection
