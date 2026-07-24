@extends('layout.app')
@section('title', 'Leave Applications')

@section('content')
@php
    $statusLabels = [
        'pending_hr' => ['label' => 'Pending HR', 'class' => 'bg-warning text-dark'],
        'pending_division_chief' => ['label' => 'Pending Division Chief', 'class' => 'bg-info text-dark'],
        'pending_regional_director' => ['label' => 'Pending Regional Director', 'class' => 'bg-primary'],
        'approved' => ['label' => 'Approved', 'class' => 'bg-success'],
        'rejected' => ['label' => 'Rejected', 'class' => 'bg-danger'],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="page-title">Leave Applications</div>
        <div class="page-subtitle">{{ $employee->full_name }} | {{ $employee->employee_id }}</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Apply Leave</h5>
                <p class="text-muted mb-4">
                    Submit a leave request here. HR can later route it to the Division Chief and Regional Director for signing.
                </p>

                <form method="POST" action="{{ route('leave-applications.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Leave Type</label>
                        <select name="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                            <option value="">-- Select Leave Type --</option>
                            @foreach($leaveTypes as $leaveType)
                                <option value="{{ $leaveType }}" @selected(old('leave_type') === $leaveType)>{{ $leaveType }}</option>
                            @endforeach
                        </select>
                        @error('leave_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Date From</label>
                            <input type="date" name="date_from" value="{{ old('date_from') }}" class="form-control @error('date_from') is-invalid @enderror" required>
                            @error('date_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Date To</label>
                            <input type="date" name="date_to" value="{{ old('date_to') }}" class="form-control @error('date_to') is-invalid @enderror">
                            @error('date_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3 mb-4">
                        <label class="form-label fw-semibold">Reason</label>
                        <textarea name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Brief reason for leave">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Submit Leave Application
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="fw-bold mb-0">
                        {{ auth()->user()?->isSuperAdmin() || auth()->user()?->role?->slug === 'hr' ? 'All Leave Applications' : 'My Leave Applications' }}
                    </h5>
                    <span class="badge bg-secondary">{{ $applications->count() }} record(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Date Range</th>
                                <th>Status</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                @php
                                    $statusKey = (string) $application->status;
                                    $status = $statusLabels[$statusKey] ?? [
                                        'label' => strtoupper(str_replace('_', ' ', $statusKey)),
                                        'class' => 'bg-secondary',
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $application->employee?->full_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $application->employee?->employee_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $application->leave_type }}</td>
                                    <td>
                                        <div>{{ $application->date_from?->format('M d, Y') }}</div>
                                        <small class="text-muted">
                                            to {{ $application->date_to?->format('M d, Y') ?? 'Open ended' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </td>
                                    <td>{{ $application->created_at?->format('M d, Y h:i A') }}</td>
                                </tr>
                                @if($application->reason)
                                    <tr class="table-light">
                                        <td colspan="5">
                                            <div class="small text-muted fw-semibold mb-1">Reason</div>
                                            <div>{{ $application->reason }}</div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        No leave applications yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
