@extends('layout.app')
@section('title', 'Edit Leave Credit')

@section('content')
<style>
    .credits-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .credits-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .form-grid.full {
        grid-template-columns: 1fr;
    }
    .form-group-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .form-control {
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
    .form-control:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08);
    }
    .form-control:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .form-card {
        background: #fff;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        max-width: 700px;
    }
    .form-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 2rem;
    }
</style>

<div class="page-header">
    <div class="credits-info">
        <div class="page-title">Edit Leave Credit</div>
        <div class="page-subtitle">Update employee leave credit information</div>
    </div>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('credits.update', $benefit->id) }}">
        @csrf
        @method('PUT')

        <div class="form-grid full">
            <div>
                <label class="form-group-label">Employee</label>
                <input type="text" class="form-control" value="{{ $benefit->employee->full_name ?? 'N/A' }}" disabled>
            </div>
        </div>

        <div class="form-grid">
            <div>
                <label class="form-group-label">Division</label>
                <input type="text" class="form-control" value="{{ $benefit->division ?? 'N/A' }}" disabled>
            </div>
            <div>
                <label class="form-group-label">Position</label>
                <input type="text" class="form-control" value="{{ $benefit->position ?? 'N/A' }}" disabled>
            </div>
        </div>

        <div class="form-grid full">
            <div>
                <label class="form-group-label">Employment Type</label>
                <input type="text" class="form-control" value="{{ $benefit->employment_type === 'PERMANENT' ? 'Permanent' : 'COS' }}" disabled>
            </div>
        </div>

        <div class="form-grid">
            <div>
                <label class="form-group-label">Start Date *</label>
                <input type="date" name="start_date" class="form-control" value="{{ optional($benefit->start_date)->format('Y-m-d') }}" required>

            </div>
            <div>
                <label class="form-group-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $benefit->end_date ? $benefit->end_date->format('Y-m-d') : '' }}">
            </div>
        </div>

        <div class="form-grid full">
            <div>
                <label class="form-group-label">Leave Type *</label>
                <select name="credit_type" class="form-control" required>
                    <option value="">-- Select Leave Type --</option>
                    @php
                        $allTypes = $benefit->employment_type === 'PERMANENT' ? $leaveTypesPermanent : $leaveTypesCos;
                    @endphp
                    @foreach($allTypes as $type)
                        <option value="{{ $type }}" {{ $benefit->credit_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="creditHoursWrapper" style="display:{{ strpos(strtolower($benefit->credit_type), 'cto') !== false || strpos(strtolower($benefit->credit_type), 'credited') !== false ? 'block' : 'none' }};">
            <div class="form-grid full">
                <div>
                    <label class="form-group-label">Credit Hours *</label>
                    <input type="number" name="credit_hours" id="creditHoursInput" class="form-control" min="0" step="1" value="{{ $benefit->credit_hours ?? '' }}" placeholder="Enter hours" />
                </div>
            </div>
        </div>

        <div class="form-grid">
            <div>
                <label class="form-group-label">Date Applied *</label>
                <input type="date" name="date_applied" class="form-control" value="{{ optional($benefit->date_applied)->format('Y-m-d') }}" required>

            </div>
            <div>
                <label class="form-group-label">Date Effective *</label>
                <input type="date" name="date_effective" class="form-control" value="{{ optional($benefit->date_effective)->format('Y-m-d') }}" required>


            </div>
        </div>

        <div class="form-grid full">
            <div>
                <label class="form-group-label">Status</label>
                <select name="status" class="form-control" required>
                    <option value="ACTIVE" {{ $benefit->status === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="INACTIVE" {{ $benefit->status === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('credits.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
    const creditTypeSelect = document.querySelector('select[name="credit_type"]');
    const creditHoursWrapper = document.getElementById('creditHoursWrapper');
    const creditHoursInput = document.getElementById('creditHoursInput');

    function updateCreditHoursVisibility() {
        const creditType = (creditTypeSelect?.value || '').toString().toLowerCase();
        const isCto = creditType.includes('cto') || creditType.includes('credited time-off') || creditType.includes('credited time off') || creditType.includes('credited');

        creditHoursWrapper.style.display = isCto ? 'block' : 'none';
        if (isCto) {
            creditHoursInput.setAttribute('required', 'required');
        } else {
            creditHoursInput.removeAttribute('required');
        }
    }

    creditTypeSelect.addEventListener('change', updateCreditHoursVisibility);
</script>

@endsection

