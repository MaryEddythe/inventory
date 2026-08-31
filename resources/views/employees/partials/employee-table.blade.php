<div class="table-responsive mt-2">
    @if($employees->count() > 0)
        <table class="table table-borderless mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Employment Type</th>
                    <th>Folder</th>
                    <th style="min-width: 170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                    <tr>
                        <td class="fw-bold">
                            {{ $emp->lastname ?? 'N/A' }}, {{ $emp->firstname ?? 'N/A' }}
                        </td>
                        <td>
                            @php
                                $deptDesc = $emp->inv_dept_description ?? ($emp->department->description ?? $emp->description ?? 'N/A');
                                $deptCode = strtoupper(trim($emp->inv_dept_code ?? ''));
                                $badgeClassMap = [
                                    'MMD' => 'badge-division-MMD',
                                    'MSESDD' => 'badge-division-MSESDD',
                                    'GD' => 'badge-division-GD',
                                    'ORD' => 'badge-division-ORD',
                                    'FAD' => 'badge-division-FAD',
                                    'COA' => 'badge-division-COA',
                                ];
                                $badgeClass = $badgeClassMap[$deptCode] ?? 'badge-division';
                            @endphp

                            <span class="badge {{ $badgeClass }}">{{ $deptDesc }}</span>
                        </td>
                        <td>{{ $emp->Role ?? 'N/A' }}</td>
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
                            <div class="d-flex gap-1">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline-primary btn-sm" title="View" aria-label="View employee">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </a>
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-outline-secondary btn-sm" title="Edit" aria-label="Edit employee">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                </a>

                                <form method="POST" action="{{ route('employees.destroy', $emp) }}" class="d-inline employee-delete-form">
                                    @csrf @method('DELETE')
                                    <button
                                        class="btn btn-outline-danger btn-sm employee-delete-btn"
                                        type="button"
                                        title="Delete"
                                        aria-label="Delete employee"
                                        data-employee-name="{{ $emp->full_name }}"
                                    >
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-4">No employees found</div>
    @endif
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted small">Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} entries</div>
    <div>
        {{ $employees->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
