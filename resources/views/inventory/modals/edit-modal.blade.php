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
            <div class="price-input-wrapper">
                <input type="number" step="0.01" class="form-control" id="unit_price-{{ $item->no }}" name="unit_price" placeholder="Enter amount" value="{{ old('unit_price', $item->unit_price) }}" {{ !$item->unit_price && old('unit_price_type') == 'na' ? 'disabled' : '' }}>
                <div class="form-check form-check-inline ms-2">
                    <input type="checkbox" class="form-check-input unit-price-na-checkbox" id="unit_price_na-{{ $item->no }}" data-item-no="{{ $item->no }}" {{ !$item->unit_price && old('unit_price_type') == 'na' ? 'checked' : '' }}>
                    <label class="form-check-label" for="unit_price_na-{{ $item->no }}">NA</label>
                </div>
            </div>
            <input type="hidden" id="unit_price_type_hidden-{{ $item->no }}" name="unit_price_type" value="{{ $item->unit_price ? 'value' : 'na' }}">
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
            <div class="date-input-wrapper">
                <div class="input-group">
                    <span class="input-group-text cursor-pointer date-picker-icon" data-item-no="{{ $item->no }}" style="cursor: pointer;"><i class="bi bi-calendar-date"></i></span>
                    <input type="date" class="form-control date-acquired-input" id="date_acquired-{{ $item->no }}" name="date_acquired" value="{{ old('date_acquired', $item->date_acquired ? $item->date_acquired->format('Y-m-d') : '') }}" data-item-no="{{ $item->no }}" {{ !$item->date_acquired && old('date_acquired_type') == 'na' ? 'disabled' : '' }}>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input date-acquired-na-checkbox" id="date_acquired_na-{{ $item->no }}" data-item-no="{{ $item->no }}" {{ !$item->date_acquired && old('date_acquired_type') == 'na' ? 'checked' : '' }}>
                    <label class="form-check-label" for="date_acquired_na-{{ $item->no }}">NA</label>
                </div>
            </div>
            <input type="hidden" id="date_acquired_type_hidden-{{ $item->no }}" name="date_acquired_type" value="{{ $item->date_acquired ? 'date' : 'na' }}">
        </div>
    </div>
    <div class="mb-3">
        <label for="remarks-{{ $item->no }}" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks-{{ $item->no }}" name="remarks" rows="2">{{ old('remarks', $item->remarks) }}</textarea>
    </div>
    <div class="mb-3">
        <label for="serviceability-{{ $item->no }}" class="form-label">Serviceability</label>
        <select class="form-select" id="serviceability-{{ $item->no }}" name="serviceability">
            <option value="" disabled>Select Serviceability</option>
            <option value="Beyond Economic Repair" {{ old('serviceability', $item->serviceability) == 'Beyond Economic Repair' ? 'selected' : '' }}>Beyond Economic Repair</option>
            <option value="Good Condition" {{ old('serviceability', $item->serviceability) == 'Good Condition' ? 'selected' : '' }}>Good Condition</option>
            <option value="For Replacement" {{ old('serviceability', $item->serviceability) == 'For Replacement' ? 'selected' : '' }}>For Replacement</option>
        </select>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Item</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeSearchInput = document.getElementById('employee_search-{{ $item->no }}');
    const suggestionsDiv = document.getElementById('employee_suggestions-{{ $item->no }}');
    const empNoInput = document.getElementById('emp_no-{{ $item->no }}');
    const enduserInput = document.getElementById('enduser-{{ $item->no }}');

    if (!empNoInput) return;

    employeeSearchInput.addEventListener('input', function() {
        const query = this.value.trim();
        suggestionsDiv.innerHTML = '';
        if (query.length < 2) { // Only search after 2 characters
            suggestionsDiv.style.display = 'none';
            return;
        }

// 🔍 ULTIMATE DEBUG - RED GLOWING BOX FOR EDIT MODAL TOO!
console.log('🔍 EDIT: Searching employees for:', query);
fetch(`/api/search-employees?query=${encodeURIComponent(query)}`, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
    }
})
.then(response => {
    console.log('📡 EDIT Response:', response.status);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
})
.then(data => {
    console.log('✅ EDIT Employees:', data.length, data);
    suggestionsDiv.innerHTML = '';
    
    if (data && data.length > 0) {
        // 🔥 MASSIVE RED GLOW - IMPOSSIBLE TO MISS!
        suggestionsDiv.style.border = '5px solid red !important';
        suggestionsDiv.style.background = 'linear-gradient(45deg, #ff0000, #ff4444) !important';
        suggestionsDiv.style.boxShadow = '0 0 0 3px orange, 0 8px 32px rgba(255,0,0,0.6) !important';
        suggestionsDiv.style.display = 'block';
        
        data.forEach(emp => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'suggestion-item';
            suggestionItem.innerHTML = `<strong>${emp.fullname || emp.name || 'N/A'}</strong><br><small>(${emp.emp_no}) ${emp.department_name || 'N/A'}</small>`;
            suggestionItem.addEventListener('click', function() {
                employeeSearchInput.value = emp.fullname || emp.name;
                empNoInput.value = emp.emp_no;
                enduserInput.value = emp.fullname || emp.name;
                suggestionsDiv.style.display = 'none';
            });
            suggestionsDiv.appendChild(suggestionItem);
        });
    } else {
        suggestionsDiv.style.display = 'none';
    }
})
.catch(error => {
    console.error('💥 EDIT AJAX ERROR:', error);
    suggestionsDiv.innerHTML = '<div class=\"p-3 text-white\"><strong>ERROR</strong><br>Error: ' + error.message + '</div>';
    suggestionsDiv.style.border = '5px solid #ff0000 !important';
    suggestionsDiv.style.background = '#ff4444';
    suggestionsDiv.style.display = 'block';
});
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

    // Set initial value from the form data
    if (enduserInput.value) {
        employeeSearchInput.value = enduserInput.value;
    }

    // Handle Unit Price NA Checkbox Toggle for edit modal
    const unitPriceNaCheckboxes = document.querySelectorAll('.unit-price-na-checkbox');
    unitPriceNaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const itemNo = this.dataset.itemNo;
            const unitPriceInput = document.getElementById(`unit_price-${itemNo}`);
            const unitPriceTypeHidden = document.getElementById(`unit_price_type_hidden-${itemNo}`);
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
    });

    // Handle Date Acquired NA Checkbox Toggle for edit modal
    const dateAcquiredNaCheckboxes = document.querySelectorAll('.date-acquired-na-checkbox');
    dateAcquiredNaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const itemNo = this.dataset.itemNo;
            const dateAcquiredInput = document.getElementById(`date_acquired-${itemNo}`);
            const dateAcquiredTypeHidden = document.getElementById(`date_acquired_type_hidden-${itemNo}`);
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
    });

    // Handle Date Picker Icon Click for edit modal
    const datePickerIcons = document.querySelectorAll('.date-picker-icon');
    datePickerIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const itemNo = this.dataset.itemNo;
            const dateInput = document.getElementById(`date_acquired-${itemNo}`);
            if (dateInput && !dateInput.disabled) {
                dateInput.showPicker();
            }
        });
    });

    // Handle form submission via AJAX
    const form = document.getElementById('edit-inventory-form-{{ $item->no }}');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const itemNo = '{{ $item->no }}';
            const url = "{{ route('inventory.update', $item->no) }}";

            fetch(url, {
                method: 'PUT',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Item updated successfully!');
                    
                    const modalElement = document.getElementById('editModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    
                    setTimeout(() => location.reload(), 500);
                } else {
                    alert(data.message || 'An error occurred while updating the item.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        });
    }

});
</script>
