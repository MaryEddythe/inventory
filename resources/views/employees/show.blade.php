@extends('layout.app')
@section('title', $employee->full_name)

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
    <div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-primary btn-sm"><- Back to List</a>
    </div>
</div>

<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        {{-- Avatar --}}
                        @php
                            $user = $employee->user;
                            $initials = strtoupper(
                                substr($employee->first_name ?? '', 0, 1) .
                                substr($employee->last_name ?? '', 0, 1)
                            );
                        @endphp

                        @if($user && $user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}"
                                 alt="{{ $employee->full_name }}"
                                 class="rounded-circle"
                                 style="width: 72px; height: 72px; object-fit: cover; border: 2px solid #dee2e6;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white fw-bold"
                                 style="width: 72px; height: 72px; font-size: 1.5rem; border: 2px solid #dee2e6;">
                                {{ $initials ?: '?' }}
                            </div>
                        @endif

                        <div>
                            <h3 class="card-title h5 fw-bold mb-0">{{ $employee->full_name }}</h3>
                            <small class="text-muted">{{ $employee->position }}</small>
                        </div>
                    </div>

                    <div class="row g-0">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Employee ID</div>
                                <span class="badge bg-primary">{{ $employee->employee_id }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Full Name</div>
                                <div class="fw-semibold">{{ $employee->full_name }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Division</div>
                                <div class="fw-semibold">{{ optional($employee->division)->department ?? optional($employee->division)->description ?? 'N/A' }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Employment Type</div>
                                <div class="fw-semibold">
                                    {{ $employee->employment_type === 'PERMANENT' ? 'Permanent' : (($employee->employment_type === 'COS') ? 'COS' : ($employee->employment_type ?? 'N/A')) }}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Position</div>
                                <div class="fw-semibold">{{ $employee->position }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Date of Birth (DOB)</div>
                                <div class="fw-semibold">{{ optional($employee->dob)->format('F d, Y') ?? '-' }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Added On</div>
                                <div class="fw-semibold">{{ optional($employee->created_at)->format('M d, Y h:i A') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top mt-3 pt-3">
                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="d-inline"
                              onsubmit="return confirm('Delete {{ $employee->full_name }}? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete Employee</button>
                        </form>

                        <button type="button" onclick="openLeaveModal()" class="btn btn-outline-primary btn-sm ms-2">
                            Apply Leave
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title h5 fw-bold mb-3">Drive Folder</h3>

                    @if($employee->drive_folder_url)
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                            <span class="badge bg-success">Ready</span>
                            <a href="{{ $employee->drive_folder_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                Open in Google Drive
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning mb-3" role="alert">
                            Drive folder is being created. Refresh in a few seconds.
                            <a href="{{ route('employees.show', $employee) }}" class="ms-2" style="text-decoration: underline; color: #92400e;">Refresh</a>
                        </div>
                    @endif

                    @if($employee->drive_folder_id)
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .05em;">File Upload</div>

                            <form method="POST" action="{{ route('employees.upload', $employee) }}" enctype="multipart/form-data" class="mt-3">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">File Type</label>
                                    @php
                                        $uploadedTypes = $employee->files ? $employee->files->pluck('file_type')->all() : [];
                                        $types = ['PDS', 'SALN', 'NBI Clearance', 'Medical Certificate', 'PAG-IBIG', 'PhilHealth'];
                                        $availableTypes = array_values(array_diff($types, $uploadedTypes));
                                    @endphp
                                    <select name="file_type" class="form-select form-select-sm" required {{ empty($availableTypes) ? 'disabled' : '' }}>
                                        <option value="">-- Select File Type --</option>
                                        @foreach($availableTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('file_type') <div class="text-danger mt-1 fw-semibold">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Upload File <span class="text-muted">(max 20MB)</span></label>
                                    <label class="btn btn-outline-secondary w-100 text-start">
                                        <i class="bi bi-upload me-2"></i> Choose file
                                        <input type="file" name="file" required class="d-none" />
                                    </label>
                                    @error('file') <div class="text-danger mt-1 fw-semibold">{{ $message }}</div> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    Upload to Google Drive
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            File upload will be available once the Drive folder is ready.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $leaveBenefits = [
            'PERMANENT' => [
                ['Vacation Leave', 15],
                ['Sick Leave', 15],
                ['Wellness Leave', 5],
                ['Special Privilege Leave', 3],
                ['Maternity Leave', 105],
                ['Paternity Leave', 7],
                ['Solo Parent Leave', 7],
                ['Special Leave Benefits for Women', null],
                ['Rehabilitation Leave', null],
                ['Special Emergency Leave', 5],
                ['Credited Time-off', null],
            ],
            'COS' => [
                ['Wellness Leave', 5],
                ['Credited Time-off', null],
            ],
        ];

        $employmentType = $employee->employment_type;
        $benefitRows = $leaveBenefits[$employmentType] ?? $leaveBenefits['PERMANENT'];

        $benefits = $employee->leaveBenefits()->orderBy('start_date', 'desc')->get();
        $benefitsByType = $benefits->groupBy('credit_type');

        $ctoCredits = $benefits->filter(function ($benefit) {
            $type = strtolower(trim((string) $benefit->credit_type));

            return $type === 'credited time-off'
                || $type === 'credited time off'
                || str_contains($type, 'cto');
        });

        $ctoTotalHours = (int) $ctoCredits->sum('credit_hours');
        $dayBasedCreditFactor = 10;
    @endphp

    @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isHr())
        <div class="card mt-4">
            <div class="card-body">
                <h3 class="card-title h5 fw-bold mb-3">Leave Benefits</h3>

                @foreach($benefitRows as $row)
                    @php
                        $label = $row[0];
                        $annualDays = $row[1];
                        $isCtoBenefit = in_array(strtolower($label), ['credited time-off', 'credited time off'], true);

                        if (is_int($annualDays)) {
                            $usedHours = (int) ($benefitsByType->get($label)?->sum('credit_hours') ?? 0);
                            $usedDays = intdiv($usedHours, $dayBasedCreditFactor);
                            $remainingDays = max(0, (int) $annualDays - $usedDays);
                        } else {
                            $remainingDays = $annualDays;
                        }
                    @endphp

                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">{{ $label }}</div>
                        <div class="fw-semibold">
                            @if(is_int($remainingDays))
                                {{ $remainingDays }} days annually
                            @elseif($isCtoBenefit)
                                {{ $ctoTotalHours }} hours
                            @else
                                {{ $remainingDays ?? 'As per policy' }}
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="mt-4 pt-3 border-top">
                    <div class="fw-bold" style="color: #0f172a;">Leave History</div>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>credit_type</th>
                                    <th>start date</th>
                                    <th>end date</th>
                                    <th>credit_hours</th>
                                    <th>remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($benefits as $benefit)
                                    <tr>
                                        <td>{{ $benefit->credit_type }}</td>
                                        <td>{{ $benefit->start_date?->format('M d, Y') }}</td>
                                        <td>{{ $benefit->end_date ? $benefit->end_date->format('M d, Y') : '-' }}</td>
                                        <td>{{ $benefit->credit_hours }}</td>
                                        <td>{{ $benefit->remarks ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No leave history found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-4 mb-0">
            Leave balances and leave credit history are visible to HR only.
        </div>
    @endif
</div>

{{-- Leave Application Modal --}}
<style>
    .leave-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .leave-modal-overlay.active {
        display: flex;
    }
    .leave-modal-content {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .leave-modal-header {
        padding: 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .leave-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .leave-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .leave-modal-close:hover {
        color: #0f172a;
    }
    .leave-modal-body {
        padding: 1.75rem;
    }
    .leave-modal-footer {
        padding: 1.75rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }
    .leave-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .leave-form-grid.full {
        grid-template-columns: 1fr;
    }
    .leave-form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .leave-form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        background: #fff;
        transition: all 0.2s ease;
    }
    .leave-form-control:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08);
    }
    .leave-form-control:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
</style>

<div class="leave-modal-overlay" id="leaveModal">
    <div class="leave-modal-content">
        <div class="leave-modal-header">
            <h2 class="leave-modal-title">Apply Leave</h2>
            <button class="leave-modal-close" onclick="closeLeaveModal()">×</button>
        </div>
        <form method="POST" action="{{ route('credits.store') }}">
            @csrf
            <input type="hidden" name="cto_action" id="leaveCtoAction" value="deduct">
            <div class="leave-modal-body">
                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Employee</label>
                        <input type="text" id="leaveEmployeeName" class="leave-form-control" disabled>
                        <input type="hidden" id="leaveEmployeeId" name="employee_id">
                    </div>
                </div>

                <div class="leave-form-grid">
                    <div>
                        <label class="leave-form-label">Division</label>
                        <input type="text" id="leaveDivision" class="leave-form-control" disabled>
                    </div>
                    <div>
                        <label class="leave-form-label">Position</label>
                        <input type="text" id="leavePosition" class="leave-form-control" disabled>
                    </div>
                </div>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Employment Type</label>
                        <input type="text" id="leaveEmploymentType" class="leave-form-control" disabled>
                    </div>
                </div>

                <div class="leave-form-grid">
                    <div>
                        <label class="leave-form-label">Start Date *</label>
                        <input type="date" name="start_date" class="leave-form-control" required>
                    </div>
                    <div>
                        <label class="leave-form-label">End Date</label>
                        <input type="date" name="end_date" class="leave-form-control">
                    </div>
                </div>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Leave Type *</label>
                        <select name="credit_type" id="leaveCreditType" class="leave-form-control" required>
                            <option value="">-- Select Leave Type --</option>
                        </select>
                    </div>
                </div>

                <div id="leaveCreditHoursWrapper" style="display:none;">
                    <div class="leave-form-grid full">
                        <div>
                            <label class="leave-form-label">Credit Hours *</label>
                            <input type="number" name="credit_hours" id="leaveCreditHours" class="leave-form-control" min="0" step="1" placeholder="Enter hours" />
                        </div>
                    </div>
                </div>

                <div class="leave-form-grid">
                    <div>
                        <label class="leave-form-label">Date Applied *</label>
                        <input type="date" name="date_applied" class="leave-form-control" required>
                    </div>
                </div>
            </div>

            <div class="leave-modal-footer">
                <button type="button" onclick="closeLeaveModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Leave</button>
            </div>
        </form>
    </div>
</div>

@php
    $leaveTypesForModal = [
        'PERMANENT' => [
            'Vacation Leave',
            'Sick Leave',
            'Wellness Leave',
            'Special Privilege Leave',
            'Maternity Leave',
            'Paternity Leave',
            'Solo Parent Leave',
            'Rehabilitation Leave',
            'Special Emergency Leave',
            'Credited Time-Off',
        ],
        'COS' => [
            'Wellness Leave',
            'Credited Time-Off',
        ],
    ];
@endphp

<script>
    const leaveTypesByEmploymentType = @json($leaveTypesForModal);

    const currentEmployee = {
        id: {{ $employee->emp_no }},
        full_name: '{{ $employee->full_name }}',
        division_code: '{{ optional($employee->division)->department ?? optional($employee->division)->description ?? 'N/A' }}',
        position: '{{ $employee->position }}',
        employment_type: '{{ $employee->employment_type }}',
    };

    function openLeaveModal() {
        const emp = currentEmployee;

        document.getElementById('leaveEmployeeName').value = emp.full_name;
        document.getElementById('leaveEmployeeId').value = emp.id;
        document.getElementById('leaveDivision').value = emp.division_code;
        document.getElementById('leavePosition').value = emp.position;
        document.getElementById('leaveEmploymentType').value = emp.employment_type;

        updateLeaveCreditTypeOptions(emp.employment_type);
        document.getElementById('leaveModal').classList.add('active');
    }

    function closeLeaveModal() {
        document.getElementById('leaveModal').classList.remove('active');
        const form = document.querySelector('#leaveModal form');
        if (form) form.reset();
        document.getElementById('leaveEmployeeId').value = '';
        document.getElementById('leaveEmployeeName').value = '';
        document.getElementById('leaveDivision').value = '';
        document.getElementById('leavePosition').value = '';
        document.getElementById('leaveEmploymentType').value = '';
        document.getElementById('leaveCreditHoursWrapper').style.display = 'none';
    }

    function updateLeaveCreditTypeOptions(employmentType) {
        const upper = (employmentType || '').toString().trim().toUpperCase();
        const key = upper.includes('PER') ? 'PERMANENT' : 'COS';
        const allowed = leaveTypesByEmploymentType[key] || [];
        const select = document.getElementById('leaveCreditType');

        select.innerHTML = '<option value="">-- Select Leave Type --</option>';
        allowed.forEach(type => {
            const opt = document.createElement('option');
            opt.value = type;
            opt.textContent = type;
            select.appendChild(opt);
        });

        updateLeaveCreditHoursVisibility();
    }

    document.getElementById('leaveCreditType')?.addEventListener('change', updateLeaveCreditHoursVisibility);

    function updateLeaveCreditHoursVisibility() {
        const wrapper = document.getElementById('leaveCreditHoursWrapper');
        const input = document.getElementById('leaveCreditHours');
        const creditType = (document.getElementById('leaveCreditType')?.value || '').toString().toLowerCase();
        const isCto = creditType.includes('cto') || creditType.includes('credited time-off') || creditType.includes('credited time off') || creditType.includes('credited');

        const ctoActionHidden = document.getElementById('leaveCtoAction');
        if (ctoActionHidden) {
            ctoActionHidden.value = isCto ? 'deduct' : '';
        }

        wrapper.style.display = isCto ? 'block' : 'none';
        if (isCto) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
            input.value = '';
        }
    }
</script>
@endsection
