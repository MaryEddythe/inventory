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
            <div class="price-input-wrapper">
                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" placeholder="Enter amount" value="{{ old('unit_price') }}" {{ !old('unit_price_type') || old('unit_price_type') == 'value' ? '' : 'disabled' }}>
                <div class="form-check form-check-inline ms-2">
                    <input type="checkbox" class="form-check-input" id="unit_price_na" {{ old('unit_price_type') == 'na' ? 'checked' : '' }}>
                    <label class="form-check-label" for="unit_price_na">NA</label>
                </div>
            </div>
            <input type="hidden" id="unit_price_type_hidden" name="unit_price_type" value="{{ old('unit_price_type', 'value') }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="co_mooe" class="form-label">CO/MOOE <span class="text-danger">*</span></label>
            <select class="form-select" id="co_mooe" name="co_mooe" required>
                <option value="" disabled {{ old('co_mooe') ? '' : 'selected' }}>Select CO/MOOE</option>
                <option value="CO" {{ old('co_mooe') == 'CO' ? 'selected' : '' }}>Capital Outlay</option>
                <option value="MOOE" {{ old('co_mooe') == 'MOOE' ? 'selected' : '' }}>MOOE</option>
                <option value="NA" {{ old('co_mooe') == 'NA' ? 'selected' : '' }}>NA</option>

            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="date_acquired" class="form-label">Date Acquired <span class="text-danger">*</span></label>
            <div class="date-input-wrapper">
                <div class="input-group">
                    <span class="input-group-text cursor-pointer" id="date_picker_icon" style="cursor: pointer;"><i class="bi bi-calendar-date"></i></span>
                    <input type="date" class="form-control" id="date_acquired" name="date_acquired" value="{{ old('date_acquired', '') }}" {{ !old('date_acquired_type') || old('date_acquired_type') == 'date' ? '' : 'disabled' }}>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="date_acquired_na" {{ old('date_acquired_type') == 'na' ? 'checked' : '' }}>
                    <label class="form-check-label" for="date_acquired_na">NA</label>
                </div>
            </input>
            <input type="hidden" id="date_acquired_type_hidden" name="date_acquired_type" value="{{ old('date_acquired_type', 'date') }}">
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
    const employeeSearchInput = document.getElementById('employee_search');
    const suggestionsDiv = document.getElementById('employee_suggestions');
    const empNoInput = document.getElementById('emp_no');
    const enduserInput = document.getElementById('enduser');
    let currentIndex = -1;
    let suggestionItems = [];
    let currentEmployees = [];

    employeeSearchInput.addEventListener('input', function() {
        const query = this.value.trim();
        suggestionsDiv.innerHTML = '';
        if (query.length < 2) { // Only search after 2 characters
            suggestionsDiv.style.display = 'none';
            empNoInput.value = '';
            enduserInput.value = '';
            return;
        }

// Make AJAX call to search employees
console.log('Searching employees for:', query);
fetch(`/api/search-employees?query=${encodeURIComponent(query)}`, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})
.then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    return response.json();
})
.then(data => {
    console.log('✅ API Response received:', {
        type: typeof data,
        isArray: Array.isArray(data),
        length: data ? data.length : 0,
        data: data
    });
    currentEmployees = data;
    suggestionsDiv.innerHTML = '';

    if (currentEmployees && currentEmployees.length > 0) {
        console.log('Displaying dropdown with', currentEmployees.length, 'employees');
        suggestionsDiv.style.display = 'block';
        suggestionItems = [];

        currentEmployees.forEach((emp, index) => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'suggestion-item';
            suggestionItem.innerHTML = `
                <div><strong>${emp.fullname || 'N/A'}</strong></div>
                <small>(${emp.emp_no || 'N/A'}) - ${emp.department_name || emp.department || 'N/A'}</small>
            `;
            suggestionItem.dataset.index = index;
            suggestionItem.style.cursor = 'pointer';

            // Use closure to properly capture the employee object
            (function(employee) {
                suggestionItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Selected employee:', employee);
                    selectEmployee(employee);
                });
            })(emp);

            suggestionsDiv.appendChild(suggestionItem);
            suggestionItems.push(suggestionItem);
        });
        currentIndex = -1;
        console.log('🎉 Dropdown populated:', currentEmployees.length, 'items');
    } else {
        console.log('❌ No employees found');
        suggestionsDiv.style.display = 'none';
        suggestionItems = [];
        currentIndex = -1;
    }
})
.catch(error => {
    console.error('Error searching employees:', error);
    suggestionsDiv.innerHTML = '<div class="p-2 text-danger"><small>Error loading employees</small></div>';
    suggestionsDiv.style.display = 'block';
    
    // 🔔 User-friendly error
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Search Error',
            text: 'Failed to load employees: ' + error.message,
            toast: true,
            position: 'top-end',
            timer: 5000
        });
    }
});
    });

    // Function to select employee
    function selectEmployee(emp) {
        console.log('Selecting employee:', emp);

        if (emp && emp.fullname && emp.emp_no) {
            // Re-reference DOM elements to ensure we're using the current form
            const currentSearchInput = document.getElementById('employee_search');
            const currentEmpNoInput = document.getElementById('emp_no');
            const currentEnduserInput = document.getElementById('enduser');

            currentSearchInput.value = emp.fullname;
            currentEmpNoInput.value = emp.emp_no;
            currentEnduserInput.value = emp.fullname;

            console.log('Employee fields populated:', {
                searchInput: currentSearchInput.value,
                empNo: currentEmpNoInput.value,
                enduser: currentEnduserInput.value
            });

            // Auto-select division based on employee's department name
            const divisionSelect = document.getElementById('division');
            if (divisionSelect && emp.department_name) {
                const options = divisionSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === emp.department_name) {
                        options[i].selected = true;
                        console.log('Division auto-selected:', emp.department_name);
                        break;
                    }
                }
            }

            suggestionsDiv.style.display = 'none';
            currentIndex = -1;
            suggestionItems.forEach(item => item.classList.remove('highlighted'));
        } else {
            console.error('Invalid employee object:', emp);
            alert('Error: Invalid employee data');
        }
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
                if (currentIndex >= 0 && currentIndex < currentEmployees.length) {
                    selectEmployee(currentEmployees[currentIndex]);
                }
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

    // Handle Unit Price NA Checkbox Toggle
    const unitPriceNaCheckbox = document.getElementById('unit_price_na');
    const unitPriceInput = document.getElementById('unit_price');
    const unitPriceTypeHidden = document.getElementById('unit_price_type_hidden');

    if (unitPriceNaCheckbox) {
        unitPriceNaCheckbox.addEventListener('change', function() {
            if (this.checked) {
                unitPriceInput.disabled = true;
                unitPriceInput.removeAttribute('required');
                unitPriceInput.value = '';
                unitPriceTypeHidden.value = 'na';
            } else {
                unitPriceInput.disabled = false;
                unitPriceInput.setAttribute('required', 'required');
                unitPriceTypeHidden.value = 'value';
            }
        });
    }

    // Handle Date Acquired NA Checkbox Toggle
    const dateAcquiredNaCheckbox = document.getElementById('date_acquired_na');
    const dateAcquiredInput = document.getElementById('date_acquired');
    const dateAcquiredTypeHidden = document.getElementById('date_acquired_type_hidden');
    const datePickerIcon = document.getElementById('date_picker_icon');

    // Click calendar icon to open date picker
    if (datePickerIcon && dateAcquiredInput) {
        datePickerIcon.addEventListener('click', function() {
            if (!dateAcquiredInput.disabled) {
                dateAcquiredInput.showPicker();
            }
        });
    }

    if (dateAcquiredNaCheckbox) {
        dateAcquiredNaCheckbox.addEventListener('change', function() {
            if (this.checked) {
                dateAcquiredInput.disabled = true;
                dateAcquiredInput.removeAttribute('required');
                dateAcquiredInput.value = '';
                dateAcquiredTypeHidden.value = 'na';
            } else {
                dateAcquiredInput.disabled = false;
                dateAcquiredInput.setAttribute('required', 'required');
                dateAcquiredTypeHidden.value = 'date';
            }
        });
    }

});
</script>
