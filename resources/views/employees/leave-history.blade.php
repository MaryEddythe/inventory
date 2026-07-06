@extends('layout.app')
@section('title', 'Leave History - ' . $employee->full_name)

@section('content')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .credits-header {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }
    th, td {
        padding: 0.75rem 0.75rem;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        vertical-align: top;
        font-size: 0.9rem;
    }
    thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #475569;
        background: #f8fafc;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .empty-state {
        padding: 2rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .badge badge-blue {
        background: #3b82f6;
    }
</style>

<div class="page-header">
    <div class="credits-header">
        <div class="page-title">Leave History</div>
        <div class="page-subtitle">{{ $employee->full_name }} · {{ $employee->employee_id }} · {{ optional($employee->division)->code ?? 'N/A' }}</div>
    </div>
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline">← Back</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>employee id</th>
            <th>name</th>
            <th>division</th>
            <th>position</th>
            <th>start date</th>
            <th>end date</th>
            <th>credit_type</th>
            <th>credit_hours (net)</th>
            <th>remarks</th>
        </tr>
        </thead>
        <tbody>
        @forelse($benefits as $benefit)
            @php($leaveBenefit = $benefit->leaveBenefit)
            <tr>
                <td><span class="badge badge-blue">{{ $employee->employee_id }}</span></td>
                <td>{{ $leaveBenefit->name ?? $employee->full_name }}</td>
                <td>{{ $leaveBenefit->division ?? optional($employee->division)->code ?? 'N/A' }}</td>
                <td>{{ $leaveBenefit->position ?? $employee->position }}</td>
                <td>{{ $leaveBenefit?->start_date?->format('M d, Y') }}</td>
                <td>
                    @if($leaveBenefit?->end_date)
                        {{ $leaveBenefit->end_date->format('M d, Y') }}
                    @else
                        —
                    @endif
                </td>

                        <td>{{ $benefit->credit_type }}</td>

                        @php($netHours = (int)($benefit->credits_added ?? 0) - (int)($benefit->hours_used ?? 0))
                        <td>{{ $netHours }}</td>

                        <td>{{ $benefit->remarks ?? '—' }}</td>

            </tr>
        @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <div class="empty-state-icon">—</div>
                        <div class="empty-state-text">No leave history found</div>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
