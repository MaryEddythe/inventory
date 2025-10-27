@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('inventory.store') }}" method="POST" id="add-inventory-form">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
            <select class="form-select" id="division" name="division" required>
                <option value="" disabled {{ old('division') ? '' : 'selected' }}>Select Division</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department }}" {{ old('division') == $dept->department ? 'selected' : '' }}>{{ $dept->department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3 position-relative">
            <label for="employee_search" class="form-label">Employee <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="employee_search" placeholder="Search Employee..." autocomplete="off" required>
            <div id="employee_suggestions" class="suggestions-list"></div>
            <input type="hidden" id="emp_no" name="emp_no" value="{{ old('emp_no') }}">
            <input type="hidden" id="enduser" name="enduser" value="{{ old('enduser') }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="classification" class="form-label">Classification <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="classification" name="classification" value="{{ old('classification') }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="property_number" class="form-label">Property Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="property_number" name="property_number" value="{{ old('property_number') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="serial_number" class="form-label">Serial Number/Device ID</label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" value="{{ old('unit_price') }}" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="co_mooe" class="form-label">CO/MOOE <span class="text-danger">*</span></label>
            <select class="form-select" id="co_mooe" name="co_mooe" required>
                <option value="" disabled {{ old('co_mooe') ? '' : 'selected' }}>Select CO/MOOE</option>
                <option value="CO" {{ old('co_mooe') == 'CO' ? 'selected' : '' }}>Capital Outlay</option>
                <option value="MOOE" {{ old('co_mooe') == 'MOOE' ? 'selected' : '' }}>MOOE</option>
                <option value="MOOE" {{ old('co_mooe') == 'NA' ? 'selected' : '' }}>NA</option>

            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="date_acquired" class="form-label">Date Acquired <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="date_acquired" name="date_acquired" value="{{ old('date_acquired') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Item</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employees = [
        @foreach($employees as $employee)
            { emp_no: '{{ $employee->emp_no }}', name: '{{ $employee->firstname }} {{ $employee->lastname }}' },
        @endforeach
    ];

    const employeeSearchInput = document.getElementById('employee_search');
    const suggestionsDiv = document.getElementById('employee_suggestions');
    const empNoInput = document.getElementById('emp_no');
    const enduserInput = document.getElementById('enduser');

    employeeSearchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        suggestionsDiv.innerHTML = '';
        if (query.length === 0) {
            suggestionsDiv.style.display = 'none';
            empNoInput.value = '';
            enduserInput.value = '';
            return;
        }

        const filteredEmployees = employees.filter(emp =>
            emp.name.toLowerCase().includes(query) || emp.emp_no.toLowerCase().includes(query)
        );

        if (filteredEmployees.length > 0) {
            suggestionsDiv.style.display = 'block';
            filteredEmployees.forEach(emp => {
                const suggestionItem = document.createElement('div');
                suggestionItem.className = 'suggestion-item';
                suggestionItem.textContent = `${emp.name} (${emp.emp_no})`;
                suggestionItem.addEventListener('click', function() {
                    employeeSearchInput.value = `${emp.name} (${emp.emp_no})`;
                    empNoInput.value = emp.emp_no;
                    enduserInput.value = emp.name;
                    suggestionsDiv.style.display = 'none';
                });
                suggestionsDiv.appendChild(suggestionItem);
            });
        } else {
            suggestionsDiv.style.display = 'none';
        }
    });

    // Prevent form submission on Enter key if no employee selected
    employeeSearchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (empNoInput.value) {
                form.submit();
            } else {
                alert('Please select a valid employee from the search results.');
                this.focus();
            }
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!employeeSearchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.style.display = 'none';
        }
    });

    // Set initial values if emp_no is pre-filled (e.g., after form validation error)
    if (empNoInput.value) {
        const selectedEmployee = employees.find(emp => emp.emp_no === empNoInput.value);
        if (selectedEmployee) {
            employeeSearchInput.value = `${selectedEmployee.name} (${selectedEmployee.emp_no})`;
            enduserInput.value = selectedEmployee.name;
        }
    }

    // Form validation before submission
    const form = document.getElementById('add-inventory-form');
    form.addEventListener('submit', function(e) {
        if (!empNoInput.value) {
            e.preventDefault();
            alert('Please select a valid employee from the search results.');
            employeeSearchInput.focus();
            return false;
        }
    });
});
</script>
