

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('inventory.update', $item->no) }}" method="POST" id="edit-inventory-form-{{ $item->no }}" class="edit-inventory-form">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="division-{{ $item->no }}" class="form-label">Division <span class="text-danger">*</span></label>
            <select class="form-select" id="division-{{ $item->no }}" name="division" required>
                <option value="" disabled>Select Division</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department }}" {{ old('division', $item->division) == $dept->department ? 'selected' : '' }}>{{ $dept->department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3 position-relative">
            <label for="employee_search-{{ $item->no }}" class="form-label">Employee <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="employee_search-{{ $item->no }}" placeholder="Search Employee..." autocomplete="off" required>
            <div id="employee_suggestions-{{ $item->no }}" class="suggestions-list"></div>
            <input type="hidden" id="emp_no-{{ $item->no }}" name="emp_no" value="{{ old('emp_no', $item->emp_no) }}">
            <input type="hidden" id="enduser-{{ $item->no }}" name="enduser" value="{{ old('enduser', $item->enduser) }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="classification-{{ $item->no }}" class="form-label">Classification <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="classification-{{ $item->no }}" name="classification" value="{{ old('classification', $item->classification) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="property_number-{{ $item->no }}" class="form-label">Property Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="property_number-{{ $item->no }}" name="property_number" value="{{ old('property_number', $item->property_number) }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="description-{{ $item->no }}" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description-{{ $item->no }}" name="description" rows="3" required>{{ old('description', $item->description) }}</textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="serial_number-{{ $item->no }}" class="form-label">Serial Number/Device ID</label>
            <input type="text" class="form-control" id="serial_number-{{ $item->no }}" name="serial_number" value="{{ old('serial_number', $item->serial_number) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="unit_price-{{ $item->no }}" class="form-label">Unit Price <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="unit_price-{{ $item->no }}" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="co_mooe-{{ $item->no }}" class="form-label">CO/MOOE <span class="text-danger">*</span></label>
            <select class="form-select" id="co_mooe-{{ $item->no }}" name="co_mooe" required>
                <option value="" disabled>Select CO/MOOE</option>
                <option value="CO" {{ old('co_mooe', $item->co_mooe) == 'CO' ? 'selected' : '' }}>Capital Outlay</option>
                <option value="MOOE" {{ old('co_mooe', $item->co_mooe) == 'MOOE' ? 'selected' : '' }}>MOOE</option>
                <option value="NA" {{ old('co_mooe', $item->co_mooe) == 'NA' ? 'selected' : '' }}>NA</option>

            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="date_acquired-{{ $item->no }}" class="form-label">Date Acquired <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="date_acquired-{{ $item->no }}" name="date_acquired" value="{{ old('date_acquired', $item->date_acquired ? $item->date_acquired->format('Y-m-d') : '') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="remarks-{{ $item->no }}" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks-{{ $item->no }}" name="remarks" rows="2">{{ old('remarks', $item->remarks) }}</textarea>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Item</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employees = [
        @foreach($employees as $employee)
            { emp_no: '{{ $employee->emp_no }}', name: '{{ $employee->firstname }} {{ $employee->lastname }}', department: '{{ $employee->department }}', department_name: '{{ $employee->departmentInfo ? $employee->departmentInfo->department : '' }}' },
        @endforeach
    ];

    const employeeSearchInput = document.getElementById('employee_search-{{ $item->no }}');
    const suggestionsDiv = document.getElementById('employee_suggestions-{{ $item->no }}');
    const empNoInput = document.getElementById('emp_no-{{ $item->no }}');
    const enduserInput = document.getElementById('enduser-{{ $item->no }}');

    if (!empNoInput) return;

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

                    // Auto-select division based on employee's department name
                    const divisionSelect = document.getElementById('division-{{ $item->no }}');
                    if (divisionSelect && emp.department_name) {
                        const options = divisionSelect.options;
                        for (let i = 0; i < options.length; i++) {
                            if (options[i].value === emp.department_name) {
                                options[i].selected = true;
                                break;
                            }
                        }
                    }

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
                this.closest('form').submit();
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

    // Set initial values if emp_no is pre-filled
    if (empNoInput.value) {
        const selectedEmployee = employees.find(emp => emp.emp_no === empNoInput.value);
        if (selectedEmployee) {
            employeeSearchInput.value = `${selectedEmployee.name} (${selectedEmployee.emp_no})`;
            enduserInput.value = selectedEmployee.name;
        } else {
            // Employee not found in list, use stored enduser
            employeeSearchInput.value = enduserInput.value;
        }
    } else if (enduserInput.value) {
        // For items without emp_no (legacy items), populate search with enduser name
        employeeSearchInput.value = enduserInput.value;
    }


});
</script>
