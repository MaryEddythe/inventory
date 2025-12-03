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
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="serviceability" class="form-label">Serviceability</label>
            <select class="form-select" id="serviceability" name="serviceability">
                <option value="" disabled {{ old('serviceability') ? '' : 'selected' }}>Select Serviceability</option>
                <option value="Good Condition" {{ old('serviceability') == 'Good Condition' ? 'selected' : '' }}>Good Condition</option>
                <option value="For Replacement" {{ old('serviceability') == 'For Replacement' ? 'selected' : '' }}>For Replacement</option>
                <option value="Beyond Economic Repair" {{ old('serviceability') == 'Beyond Economic Repair' ? 'selected' : '' }}>Beyond Economic Repair</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
        </div>
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
            { emp_no: '{{ $employee->emp_no }}', name: '{{ $employee->firstname }} {{ $employee->lastname }}', department: '{{ $employee->department }}', department_name: '{{ $employee->departmentInfo ? $employee->departmentInfo->department : '' }}' },
        @endforeach
    ];

    const employeeSearchInput = document.getElementById('employee_search');
    const suggestionsDiv = document.getElementById('employee_suggestions');
    const empNoInput = document.getElementById('emp_no');
    const enduserInput = document.getElementById('enduser');
    let currentIndex = -1;
    let suggestionItems = [];

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
            suggestionItems = [];
            filteredEmployees.forEach((emp, index) => {
                const suggestionItem = document.createElement('div');
                suggestionItem.className = 'suggestion-item';
                suggestionItem.textContent = emp.name;
                suggestionItem.dataset.index = index;
                suggestionItem.addEventListener('click', function() {
                    selectEmployee(emp);
                });
                suggestionsDiv.appendChild(suggestionItem);
                suggestionItems.push(suggestionItem);
            });
            currentIndex = -1;
        } else {
            suggestionsDiv.style.display = 'none';
            suggestionItems = [];
            currentIndex = -1;
        }
    });

    // Function to select employee
    function selectEmployee(emp) {
        employeeSearchInput.value = emp.name;
        empNoInput.value = emp.emp_no;
        enduserInput.value = emp.name;

        // Auto-select division based on employee's department name
        const divisionSelect = document.getElementById('division');
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
        currentIndex = -1;
        suggestionItems.forEach(item => item.classList.remove('highlighted'));
    }

    // Function to update highlight
    function updateHighlight() {
        suggestionItems.forEach((item, index) => {
            if (index === currentIndex) {
                item.classList.add('highlighted');
                item.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            } else {
                item.classList.remove('highlighted');
            }
        });
    }

    // Handle keyboard navigation
    employeeSearchInput.addEventListener('keydown', function(e) {
        if (suggestionsDiv.style.display === 'block' && suggestionItems.length > 0) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % suggestionItems.length;
                updateHighlight();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentIndex = currentIndex <= 0 ? suggestionItems.length - 1 : currentIndex - 1;
                updateHighlight();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentIndex >= 0 && currentIndex < filteredEmployees.length) {
                    selectEmployee(filteredEmployees[currentIndex]);
                } else if (empNoInput.value) {
                    form.submit();
                } else {
                    alert('Please select a valid employee from the search results.');
                    this.focus();
                }
            }
        } else if (e.key === 'Enter') {
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
            currentIndex = -1;
            suggestionItems.forEach(item => item.classList.remove('highlighted'));
        }
    });

    // Set initial values if emp_no is pre-filled (e.g., after form validation error)
    if (empNoInput.value) {
        const selectedEmployee = employees.find(emp => emp.emp_no === empNoInput.value);
        if (selectedEmployee) {
            employeeSearchInput.value = selectedEmployee.name;
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
