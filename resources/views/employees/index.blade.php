@extends('layout.app')
@section('title', 'Employees')

@section('content')
<div id="employees-page">
    {{-- Keep styling minimal: rely on global layout styles where possible --}}
    <style>
        .employees-info { display:flex; flex-direction:column; gap:.25rem; }
        .table-wrapper {
            background:#fff;
            border-radius:8px;
            border:1px solid #e2e8f0;
            overflow:hidden;
            box-shadow:0 1px 2px rgba(0,0,0,.04);
            margin-bottom: 1.5rem;
        }
        .actions-cell { display:flex; gap:.6rem; flex-wrap:wrap; }
        .employee-name { font-weight:700; color:#0f172a; font-size:.95rem; }
        .empty-state { text-align:center; padding:4rem 2rem; color:#64748b; }
        .empty-state-icon {
            width:50px; height:50px; margin:0 auto 1rem;
            background:linear-gradient(135deg,#e2e8f0 0%,#f1f5f9 100%);
            border-radius:6px; display:flex; align-items:center; justify-content:center;
            font-size:1rem; color:#94a3b8;
        }
        .empty-state-text { font-size:.95rem; margin-bottom:.85rem; font-weight:600; color:#475569; }
        .empty-state-link { color:#0066cc; font-weight:700; text-decoration:none; transition:color .2s ease; font-size:.9rem; }
        .empty-state-link:hover { color:#0052a3; }
        .employee-actions-form { margin:0; }
    </style>

    <div class="page-header">
        <div class="employees-info">
            <div class="page-title">Employees</div>
            <div class="page-subtitle">Total Records: <strong>{{ $employees->total() }}</strong></div>
        </div>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">+ Add Employee</a>
    </div>

    @if(session('status') || session('error') || $errors->any())
        <div style="margin:0.75rem 0;padding:0.75rem;border-radius:6px;background:#fff3f3;border:1px solid #ffd6d6;color:#922">
            @if(session('status')) {{ session('status') }} @endif
            @if(session('error')) {{ session('error') }} @endif
            @if($errors->any()) {{ $errors->first() }} @endif
        </div>
    @endif

    <div class="table-wrapper">
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Employment Type</th>
                        <th>Folder</th>
                        <th style="min-width:170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td>
                                <div class="employee-name">
                                    {{ $emp->lastname ?? $emp->last_name ?? 'N/A' }}, {{ $emp->firstname ?? $emp->first_name ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $dept = $emp->department ?? null;
                                    if ($dept === null || $dept === '') {
                                        $dept = optional($emp->division)->code ?? optional($emp->division)->name ?? null;
                                    }
                                @endphp
                                {{ $dept && $dept !== '' ? $dept : 'N/A' }}
                            </td>
                            <td>{{ $emp->position ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $et = $emp->employment_type ?? null;
                                @endphp
                                @if($et === 'Permanent' || $et === 'PERMANENT' || $et === 'Permanent ')
                                    Permanent
                                @else
                                    COS
                                @endif
                            </td>

                            <td>
                                @if($emp->drive_folder_url)
                                    <a href="{{ $emp->drive_folder_url }}" target="_blank" rel="noopener">Open Folder</a>
                                @else
                                    <span class="text-muted">Pending</span>
                                @endif
                            </td>

                            <td>
                                <div class="actions-cell">
                                    <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline btn-sm">View</a>
                                    <a href="{{ route('employees.edit', $emp) }}" class="btn btn-outline btn-sm">Edit</a>

                                    <form method="POST"
                                          class="employee-actions-form"
                                          action="{{ route('employees.destroy', $emp) }}"
                                          onsubmit="return confirm('Delete {{ $emp->full_name }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">–</div>
                                    <div class="empty-state-text">No employees found</div>
                                    <a href="{{ route('employees.create') }}" class="empty-state-link">Add your first employee</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $employees->links() }}</div>
</div>
@endsection
