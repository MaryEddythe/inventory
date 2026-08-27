@extends('layout.app')
@section('title', 'Edit Employee')

@section('content')
{{--
    Edit employee form inside a modal popup.
    Styling should match employees/create.blade.php
--}}

<div class="employees-create-modal-backdrop" role="dialog" aria-modal="true" aria-label="Edit Employee">
    <div class="employees-create-modal-dialog">
        <div class="employees-create-modal-header">
            <div>
                <div class="employees-create-modal-title">Employee Information</div>
                <div class="employees-create-modal-subtitle">Update employee details (drive folder not affected)</div>
            </div>
            <button type="button" class="employees-create-modal-close" onclick="window.location='{{ route('employees.show', $employee) }}'">
                ✕
            </button>
        </div>

        <div class="employees-create-modal-body">

            <form method="POST" action="{{ route('employees.update', $employee) }}" id="employeeForm">
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

                <div class="form-grid">
                    <div class="form-group">
                        <label>Division <span class="required-asterisk">*</span></label>
                        <select name="division_id" required>
                            <option value="">-- Select Division --</option>
                            @foreach($divisions as $dept)
                                @php
                                    $deptDesc = $dept->description ?? null;
                                    $deptText = $deptDesc ?? ($dept->department ?? $dept->dept_no ?? null);
                                @endphp
                                                            <option value="{{ $dept->dept_no }}" {{ (string) old('division_id', $employee->department) === (string) $dept->dept_no ? 'selected' : '' }}>
                                    {{ $deptText }}
                                </option>
                            @endforeach


                        </select>
                        @error('division_id') <div class="error-text">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label>Position <span class="required-asterisk">*</span></label>
                        <input type="text" name="position" value="{{ old('position', $employee->Role) }}" placeholder="e.g. Chief Finance and Administrative Division" required>
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
                    <input type="date" name="dob" value="{{ old('dob', optional($employee->dob)->format('Y-m-d')) }}" required>
                    @error('dob') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Changes</button>
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline">Cancel</a>
                </div>

                <div style="margin-top:0.75rem;color:#64748b;font-size:0.9rem;">
                    Note: Updating your details won’t affect the existing Google Drive folder.
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    const form = document.getElementById('employeeForm');
    const submitBtn = document.getElementById('submitBtn');
    if(form && submitBtn){
        form.addEventListener('submit', function(){ submitBtn.disabled = true; });
    }
})();
</script>

@endsection


