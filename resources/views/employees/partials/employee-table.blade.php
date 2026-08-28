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
                            <div class="d-flex gap-2">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-link p-0 text-decoration-none" title="View">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition" aria-hidden="true">
                                        <i class="bi bi-eye" style="font-size: 1rem;"></i>
                                    </span>
                                </a>
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-link p-0 text-decoration-none" title="Edit">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition" aria-hidden="true">
                                        <i class="bi bi-pencil-square" style="font-size: 1rem;"></i>
                                    </span>
                                </a>

                                <form method="POST" action="{{ route('employees.destroy', $emp) }}" style="display: inline;" class="employee-delete-form">
                                    @csrf @method('DELETE')
                                    <button
                                        class="btn btn-link p-0 text-decoration-none text-danger employee-delete-btn"
                                        type="button"
                                        title="Delete"
                                        data-employee-name="{{ $emp->full_name }}"
                                    >
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition" aria-hidden="true">
                                            <i class="bi bi-trash" style="font-size: 1rem;"></i>
                                        </span>
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