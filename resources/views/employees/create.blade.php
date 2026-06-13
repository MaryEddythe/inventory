@extends('layouts.app')
@section('title', 'Add Employee')

@section('content')
{{--
    Render the create employee form inside a modal popup.
    This view is still served at /employees/create.
--}}

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
    .modal-body {
        padding: 2rem;
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
    .modal-actions {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
</style>

<div class="modal-backdrop" role="dialog" aria-modal="true" aria-label="Add Employee">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <div class="modal-title">Employee Information</div>
                <div class="modal-subtitle">A Google Drive folder will be created automatically</div>
            </div>
            <button type="button" class="modal-close" onclick="window.location='{{ route('employees.index') }}'">
                ✕
            </button>
        </div>

        <div class="modal-body">
            <form method="POST" action="{{ route('employees.store') }}" id="employeeForm">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name <span class="required-asterisk">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                               placeholder="e.g. Maria" required>
                        @error('first_name') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required-asterisk">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               placeholder="e.g. Santos" required>
                        @error('last_name') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address <span class="required-asterisk">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="e.g. maria.santos@company.com" required>
                    @error('email') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Division <span class="required-asterisk">*</span></label>
                        <select name="division_id" required>
                            <option value="">-- Select Division --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                    @if($division->code) ({{ $division->code }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('division_id') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Position <span class="required-asterisk">*</span></label>
                        <input type="text" name="position" value="{{ old('position') }}"
                               placeholder="e.g. Software Developer" required>
                        @error('position') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Employment Type <span class="required-asterisk">*</span></label>
                    <select name="employment_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="PERMANENT" {{ old('employment_type') == 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                        <option value="COS" {{ old('employment_type') == 'COS' ? 'selected' : '' }}>Contract of Service (COS)</option>
                    </select>
                    @error('employment_type') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Date Hired <span class="required-asterisk">*</span></label>
                    <input type="date" name="hired_at" value="{{ old('hired_at') }}" required>
                    @error('hired_at') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Save Employee & Create Drive Folder
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline">Cancel</a>
                </div>

                <!-- small async notice -->
                <div style="margin-top:0.75rem;color:#64748b;font-size:0.9rem;">
                    Note: A Drive folder will be created automatically in the background. It may take a few seconds to appear.
                </div>
            </form>
        </div>
    </div>
</div>

<!-- simple disable-on-submit to prevent double posts -->
<script>
(function(){
    const form = document.getElementById('employeeForm');
    const submitBtn = document.getElementById('submitBtn');
    form.addEventListener('submit', function(){ submitBtn.disabled = true; });
})();
</script>

<style>
    .required-asterisk {
        color: #dc2626;
    }
</style>
@endsection
