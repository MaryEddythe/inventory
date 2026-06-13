@extends('layout.app')
@section('title', 'Edit Employee')

@section('content')
<style>
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        z-index: 9999;
    }
    .modal-dialog {
        width: 100%;
        max-width: 700px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
        overflow: hidden;
    }
    .modal-header {
        padding: 2rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        background: #fafbfc;
    }
    .modal-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
        letter-spacing: -0.3px;
    }
    .modal-subtitle {
        margin-top: 0.4rem;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .modal-close {
        background: transparent;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        border-radius: 5px;
        padding: 0.45rem 0.65rem;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.9rem;
        line-height: 1;
        transition: all 0.2s ease;
    }
    .modal-close:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }
    .modal-body { padding: 2rem; }
    .modal-actions {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .required-asterisk { color: #dc2626; }
    .error-text { color: #dc2626; font-size: 0.85rem; margin-top: 0.25rem; }
</style>

<div class="modal-backdrop" role="dialog" aria-modal="true" aria-label="Edit Employee">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <div class="modal-title">Edit Employee</div>
                <div class="modal-subtitle">Update employee details (drive folder not affected)</div>
            </div>
            <button type="button" class="modal-close" onclick="window.location='{{ route('employees.show', $employee) }}'">
                ✕
            </button>
        </div>

        <div class="modal-body">
            <form method="POST" action="{{ route('employees.update', $employee) }}">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name <span class="required-asterisk">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" placeholder="e.g. Maria" required>
                        @error('first_name') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required-asterisk">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" placeholder="e.g. Santos" required>
                        @error('last_name') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address <span class="required-asterisk">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="e.g. maria.santos@company.com" required>
                    @error('email') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Division <span class="required-asterisk">*</span></label>
                        <select name="division_id" required>
                            <option value="">-- Select Division --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ (int) old('division_id', $employee->division_id) === (int) $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                    @if($division->code) ({{ $division->code }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('division_id') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Position <span class="required-asterisk">*</span></label>
                        <input type="text" name="position" value="{{ old('position', $employee->position) }}" placeholder="e.g. Software Developer" required>
                        @error('position') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Employment Type <span class="required-asterisk">*</span></label>
                    <select name="employment_type" required>
                        <option value="PERMANENT" {{ old('employment_type', $employee->employment_type) === 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                        <option value="COS" {{ old('employment_type', $employee->employment_type) === 'COS' ? 'selected' : '' }}>Contract of Service (COS)</option>
                    </select>
                    @error('employment_type') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Date Hired <span class="required-asterisk">*</span></label>
                    <input type="date" name="hired_at" value="{{ old('hired_at', optional($employee->hired_at)->format('Y-m-d')) }}" required>
                    @error('hired_at') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

