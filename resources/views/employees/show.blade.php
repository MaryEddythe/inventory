@extends('layout.app')
@section('title', $employee->full_name)

@section('content')
<style>
    .employee-show-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .employee-header-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .status-alert {
        background: #fef9c3;
        border: 1px solid #fde68a;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        color: #78350f;
        font-weight: 600;
        line-height: 1.5;
    }
    .drive-ready {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .info-section {
        border-top: 1px solid #e2e8f0;
        padding-top: 1.5rem;
        margin-top: 1.5rem;
    }
    .info-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1.2rem;
    }
    .upload-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.75rem;
    }
    .delete-section {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    @media (max-width: 1024px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .employee-show-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="page-header">
    <div class="employee-header-info">
        <div class="page-title">{{ $employee->full_name }}</div>
        <div class="page-subtitle">{{ $employee->employee_id }} · {{ optional($employee->division)->code ?? 'N/A' }}</div>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline">← Back to List</a>
</div>

<div class="details-grid">

    {{-- LEFT: Employee Details --}}
    <div class="card">
        <div class="card-title">Details</div>
        <div class="info-row">
            <span class="info-label">Employee ID</span>
            <span class="info-value">
                <span class="badge badge-blue">{{ $employee->employee_id }}</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Full Name</span>
            <span class="info-value">{{ $employee->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ $employee->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Division</span>
            <span class="info-value">{{ optional($employee->division)->code ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Employment Type</span>
            <span class="info-value">
                {{ $employee->employment_type === 'PERMANENT' ? 'Permanent' : (($employee->employment_type === 'COS') ? 'COS' : ($employee->employment_type ?? 'N/A')) }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Position</span>
            <span class="info-value">{{ $employee->position }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date Hired</span>
            <span class="info-value">{{ $employee->hired_at->format('F d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Added On</span>
            <span class="info-value">{{ $employee->created_at->format('M d, Y h:i A') }}</span>
        </div>

        <div class="delete-section">
            <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                  onsubmit="return confirm('Delete {{ $employee->full_name }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete Employee</button>
            </form>
        </div>
    </div>

    {{-- RIGHT: Drive Folder + Upload --}}
    <div>
        <div class="card">
            <div class="card-title">Drive Folder</div>

            @if($employee->drive_folder_url)
                <div class="drive-ready">
                    <span class="badge badge-green">Ready</span>
                    <a href="{{ $employee->drive_folder_url }}" target="_blank" class="btn btn-outline btn-sm">
                        Open in Google Drive
                    </a>
                </div>
            @else
                <div class="status-alert">
                    Drive folder is being created. Refresh in a few seconds.
                    <a href="{{ route('employees.show', $employee) }}" style="color: #92400e; text-decoration: underline; margin-left: 0.5rem;">Refresh</a>
                </div>
            @endif

            {{-- File Upload Section --}}
            @if($employee->drive_folder_id)
                <div class="upload-section">
                    <div class="info-section-title">File Upload</div>
                    <form method="POST" action="{{ route('employees.upload', $employee) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>File Type</label>
                            @php
                                $uploadedTypes = $employee->files ? $employee->files->pluck('file_type')->all() : [];
                                $types = ['PDS','SALN','NBI Clearance','Medical Certificate','PAG-IBIG','PhilHealth'];
                                $availableTypes = array_values(array_diff($types, $uploadedTypes));
                            @endphp
                            <select name="file_type" required {{ empty($availableTypes) ? 'disabled' : '' }}>
                                <option value="">-- Select File Type --</option>
                                @foreach($availableTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('file_type') <div class="error-text mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Upload File <span class="text-slate-500">(max 20MB)</span>
                            </label>

                            <label
                                class="flex cursor-pointer items-center justify-center rounded-md border-2 border-dashed border-slate-300 bg-white px-4 py-4 text-sm font-semibold text-slate-600 hover:border-blue-400 hover:bg-slate-50"
                            >
                                <span>Choose file</span>
                                <input type="file" name="file" required class="sr-only" />
                            </label>

                            @error('file') <div class="error-text mt-1">{{ $message }}</div> @enderror

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 0;">
                                    Upload to Google Drive
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="upload-section">
                    <p style="font-size: 0.9rem; color: #64748b; font-weight: 500;">
                        File upload will be available once the Drive folder is ready.
                    </p>
                </div>
            @endif
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

    // Convert stored credit_hours back to credited days (1 day = 10 hours).
    // Then compute remaining days for annual day-based leaves.
    $benefits = $employee->leaveBenefits()
        ->orderBy('start_date', 'desc')
        ->get();

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


    <div class="card">
    <div class="card-title">Leave Benefits</div>


    @foreach($benefitRows as $row)
        @php
            $label = $row[0];
            $annualDays = $row[1];
            $isCtoBenefit = in_array(strtolower($label), ['credited time-off', 'credited time off'], true);

            // For annual day-based credits, compute remaining = annual - usedDays
            $usedDays = 0;
            if (is_int($annualDays)) {
                // We treat recorded credits (credit_hours) as the used portion.
                // Right now the app doesn't decrement hours_used, so credit_hours represents usage.
                $usedHours = (int) ($benefitsByType->get($label)?->sum('credit_hours') ?? 0);
                $usedDays = intdiv($usedHours, $dayBasedCreditFactor);


                // Remaining can't be negative
                $remainingDays = max(0, (int)$annualDays - $usedDays);
            } else {
                $remainingDays = $annualDays; // null/As per policy/etc.
            }
        @endphp

        <div class="info-row">
            <span class="info-label">{{ $label }}</span>
            <span class="info-value">
                @if(is_int($remainingDays))
                    {{ $remainingDays }} days annually
                @elseif($isCtoBenefit)
                    {{ $ctoTotalHours }} hours
                @else
                    {{ $remainingDays ?? 'As per policy' }}
                @endif
            </span>
        </div>
    @endforeach


    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
        <div style="display:flex; align-items:center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="font-weight: 700; color: #0f172a;">Leave History</div>
        </div>

        <div class="table-wrapper" style="margin-top: 0.75rem;">

            <table>
                <thead>
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
                            @if($benefit->end_date)
                                {{ $benefit->end_date->format('M d, Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $benefit->credit_hours }}</td>
                        <td>{{ $benefit->remarks ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No leave history found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
