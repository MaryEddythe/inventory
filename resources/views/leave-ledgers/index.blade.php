@extends('layout.app')
@section('title', 'Leave Ledgers')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Permanent Employee Leave Ledgers</h1>
            <div class="text-muted small">Vacation Leave and Sick Leave monthly accruals for permanent employees.</div>
        </div>

        <form class="d-flex gap-2" method="GET" action="{{ route('leave-ledgers.index') }}">
            <input type="text" name="search" value="{{ $search }}" class="form-control" style="min-width: 260px;" placeholder="Search employees">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Division/Office</th>
                    <th>Position</th>
                    <th>Employment Type</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td class="fw-semibold">{{ $employee->lastname }}, {{ $employee->firstname }}</td>
                        <td>{{ $employee->department_code ?? $employee->department_description ?? 'N/A' }}</td>
                        <td>{{ $employee->Role ?? 'N/A' }}</td>
                        <td><span class="badge bg-success">PERMANENT</span></td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('leave-ledgers.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('leave-ledgers.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No permanent employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} entries</div>
        {{ $employees->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection
