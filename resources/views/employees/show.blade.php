@extends('layout.app')
@section('title', $employee->full_name)

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
    <div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-primary btn-sm">← Back to List</a>
    </div>
</div>

<div class="container-fluid px-0">
    <div class="row g-4">
        {{-- LEFT: Employee Details --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title h5 fw-bold mb-3">Details</h3>

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
                                <div class="fw-semibold">{{ optional($employee->dob)->format('F d, Y') ?? '—' }}</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .02em;">Added On</div>
                                <div class="fw-semibold">{{ optional($employee->created_at)->format('M d, Y h:i A') ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top mt-3 pt-3">
                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="d-inline"
                              onsubmit="return confirm('Delete {{ $employee->full_name }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete Employee</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Drive Folder + Upload --}}
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

                    {{-- File Upload Section --}}
                    @if($employee->drive_folder_id)
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="text-uppercase fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: .05em;">File Upload</div>

                            <form method="POST" action="{{ route('employees.upload', $employee) }}" enctype="multipart/form-data" class="mt-3">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">File Type</label>
                                    @php
                                        $uploadedTypes = $employee->files ? $employee->files->pluck('file_type')->all() : [];
                                        $types = ['PDS','SALN','NBI Clearance','Medical Certificate','PAG-IBIG','PhilHealth'];
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
        // Leave benefits shown depend on employee employment_type.
        // Business rule: COS employees are only entitled to Wellness Leave (+ Credited Time-off).
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
        $dayBasedCreditFactor = 10; // 1 day = 10 hours
    @endphp

    <div class="card mt-4">
        <div class="card-body">
            <h3 class="card-title h5 fw-bold mb-3">Leave Benefits</h3>

            @foreach($benefitRows as $row)
                @php
                    $label = $row[0];
                    $annualDays = $row[1];
                    $isCtoBenefit = in_array(strtolower($label), ['credited time-off', 'credited time off'], true);

                    $usedDays = 0;
                    if (is_int($annualDays)) {
                        $usedHours = (int) ($benefitsByType->get($label)?->sum('credit_hours') ?? 0);
                        $usedDays = intdiv($usedHours, $dayBasedCreditFactor);
                        $remainingDays = max(0, (int)$annualDays - $usedDays);
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
                                <td>
                                    {{ $benefit->end_date ? $benefit->end_date->format('M d, Y') : '—' }}
                                </td>
                                <td>{{ $benefit->credit_hours }}</td>
                                <td>{{ $benefit->remarks ?? '—' }}</td>
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
</div>

@endsection

