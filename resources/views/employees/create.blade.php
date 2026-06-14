@extends('layout.app')
@section('title', 'Add Employee')

@section('content')
{{--
    Render the create employee form inside a modal popup.
    This view is still served at /employees/create.
--}}

<div class="employees-create-modal-backdrop" role="dialog" aria-modal="true" aria-label="Add Employee">
    <div class="employees-create-modal-dialog">
        <div class="employees-create-modal-header">
            <div>
                <div class="employees-create-modal-title">Employee Information</div>
                <div class="employees-create-modal-subtitle">A Google Drive folder will be created automatically</div>
            </div>
            <button type="button" class="employees-create-modal-close" onclick="window.location='{{ route('employees.index') }}'">
                ✕
            </button>
        </div>

        <div class="employees-create-modal-body">

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
                    <label>Date of Birth (DOB) <span class="required-asterisk">*</span></label>
                    <input type="date" name="dob" value="{{ old('dob') }}" required>
                    @error('dob') <div class="error-text">{{ $message }}</div> @enderror
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

@endsection

