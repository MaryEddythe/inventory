<form id="add-icm-form" class="add-icm-form" enctype="multipart/form-data">
    @csrf
    
    <!-- Step Indicator -->
    <div class="mb-4">
        <div class="d-flex justify-content-between mb-3">
            <div class="step-indicator active" id="step1-indicator">
                <div class="step-number">1</div>
                <div class="step-label">Personnel Details</div>
            </div>
            <div class="step-line"></div>
            <div class="step-indicator" id="step2-indicator">
                <div class="step-number">2</div>
                <div class="step-label">Item Details</div>
            </div>
        </div>
    </div>

    <!-- STEP 1: PERSONNEL DETAILS -->
    <div id="step1" class="step-content active">
        <h6 class="mb-4">Step 1: Personnel Details</h6>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" id="division" name="division" required>
                    <option value="">Select Division</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="requesting_personnel" class="form-label">Requesting Personnel <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm" id="requesting_personnel" name="requesting_personnel" placeholder="Search personnel..." required autocomplete="off">
                    <div id="personnel_suggestions" class="position-absolute bg-white border border-light shadow-sm rounded mt-1" style="display:none; width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000;"></div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="icm_type" class="form-label">Type <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" id="icm_type" name="icm_type" required>
                    <option value="">Select Type</option>
                    <option value="Assistance">Assistance</option>
                    <option value="Troubleshoot">Troubleshoot</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" id="priority" name="priority" required>
                    <option value="">Select Priority</option>
                    <option value="P1-Critical">P1-Critical</option>
                    <option value="P2-Important">P2-Important</option>
                    <option value="P3-Normal">P3-Normal</option>
                    <option value="P4-Low">P4-Low</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="hardware_software" class="form-label">Hardware or Software <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" id="hardware_software" name="hardware_software" required>
                    <option value="">Select Type</option>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                </select>
            </div>
        </div>
    </div>

    <!-- STEP 2: ITEM DETAILS -->
    <div id="step2" class="step-content" style="display:none;">
        <h6 class="mb-4">Step 2: Item & Issue Details</h6>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="classification" class="form-label">Item Classification <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" id="classification" name="classification" required disabled>
                    <option value="">Select classification after choosing personnel</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="brand_model" class="form-label">Item <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="brand_model" name="brand_model" placeholder="Select classification first" required disabled>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="serial_number" name="serial_number" readonly required>
            </div>
            <div class="col-md-6">
                <label for="property_number" class="form-label">Property Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="property_number" name="property_number" readonly required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <label for="problem_description" class="form-label">Problem/Assistance Description <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm" id="problem_description" name="problem_description" rows="3" required></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="open_date" class="form-label">Open Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control form-control-sm" id="open_date" name="open_date" required>
            </div>
            <div class="col-md-6">
                <label for="open_time" class="form-label">Open Time <span class="text-danger">*</span></label>
                <input type="time" class="form-control form-control-sm" id="open_time" name="open_time" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="close_date" class="form-label">Close Date</label>
                <input type="date" class="form-control form-control-sm" id="close_date" name="close_date">
            </div>
            <div class="col-md-6">
                <label for="close_time" class="form-label">Close Time</label>
                <input type="time" class="form-control form-control-sm" id="close_time" name="close_time">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <label for="icm_findings" class="form-label">Findings</label>
                <textarea class="form-control form-control-sm" id="icm_findings" name="icm_findings" rows="3"></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <label for="actions_taken" class="form-label">Actions Taken</label>
                <textarea class="form-control form-control-sm" id="actions_taken" name="actions_taken" rows="3"></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <label for="icm_recommendations" class="form-label">Recommendations</label>
                <textarea class="form-control form-control-sm" id="icm_recommendations" name="icm_recommendations" rows="3"></textarea>
            </div>
        </div>
    </div>

    <!-- Form Buttons -->
    <div class="text-end mt-4">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="prevBtn" style="display:none;">Previous</button>
        <button type="button" class="btn btn-primary btn-sm" id="nextBtn">Next</button>
        <button type="submit" class="btn btn-success btn-sm" id="submitBtn" style="display:none;">Submit</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Step navigation
    let currentStep = 1;
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');

    // Step 1 required fields
    const step1Fields = ['division', 'requesting_personnel', 'icm_type', 'priority', 'hardware_software'];

    nextBtn.addEventListener('click', function() {
        if (currentStep === 1) {
            if (validateStep1()) {
                currentStep = 2;
                updateStepDisplay();
            }
        }
    });

    prevBtn.addEventListener('click', function() {
        if (currentStep === 2) {
            currentStep = 1;
            updateStepDisplay();
        }
    });

    function validateStep1() {
        let isValid = true;
        for (let fieldId of step1Fields) {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        }
        return isValid;
    }

    function updateStepDisplay() {
        if (currentStep === 1) {
            step1.style.display = 'block';
            step2.style.display = 'none';
            nextBtn.style.display = 'inline-block';
            prevBtn.style.display = 'none';
            submitBtn.style.display = 'none';
            step1Indicator.classList.add('active');
            step2Indicator.classList.remove('active');
        } else if (currentStep === 2) {
            step1.style.display = 'none';
            step2.style.display = 'block';
            nextBtn.style.display = 'none';
            prevBtn.style.display = 'inline-block';
            submitBtn.style.display = 'inline-block';
            step1Indicator.classList.remove('active');
            step2Indicator.classList.add('active');
        }
    }

    // Remove invalid class on input
    step1Fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', function() {
                this.classList.remove('is-invalid');
            });
        }
    });

    const personnelInput = document.getElementById('requesting_personnel');
    const suggestionContainer = document.getElementById('personnel_suggestions');
    const classificationSelect = document.getElementById('classification');
    const brandModelSelect = document.getElementById('brand_model');
    const serialNumberInput = document.getElementById('serial_number');
    const propertyNumberInput = document.getElementById('property_number');
    let itemsData = {};
    let selectedPersonnelEmpNo = null;

    // Live search for personnel
    let searchTimer;
    personnelInput.addEventListener('input', function(e) {
        clearTimeout(searchTimer);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            suggestionContainer.style.display = 'none';
            return;
        }

        searchTimer = setTimeout(() => {
            fetch(`{{ route('api.search-employees') }}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(employees => {
                    if (employees.length > 0) {
                        suggestionContainer.innerHTML = '';
                        employees.forEach(emp => {
                            const div = document.createElement('div');
                            div.className = 'p-2 cursor-pointer hover-effect';
                            div.style.cursor = 'pointer';
                            div.textContent = `${emp.firstname} ${emp.lastname} (${emp.emp_no})`;
                            div.addEventListener('click', () => selectPersonnel(emp));
                            suggestionContainer.appendChild(div);
                        });
                        suggestionContainer.style.display = 'block';
                    } else {
                        suggestionContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching employees:', error);
                    suggestionContainer.style.display = 'none';
                });
        }, 300);
    });

    // Handle clicking outside suggestions
    document.addEventListener('click', function(e) {
        if (e.target !== personnelInput) {
            suggestionContainer.style.display = 'none';
        }
    });

    function selectPersonnel(employee) {
    console.log('Selected employee:', employee);
    
    // Store the emp_no as string for consistency
    selectedPersonnelEmpNo = String(employee.emp_no);
    
    // Display the full name with emp_no
    personnelInput.value = `${employee.firstname} ${employee.lastname} (${employee.emp_no})`;
    suggestionContainer.style.display = 'none';

    // Auto-populate Division using department_name
    const divisionSelect = document.getElementById('division');
    if (divisionSelect) {
        const divisionValue = employee.department_name || employee.department;
        if (divisionValue) {
            divisionSelect.value = divisionValue;
            divisionSelect.classList.remove('is-invalid');
            console.log('Division set to:', divisionValue);
        } else {
            console.warn('No department found for employee');
        }
    }

    // Load items for this personnel
    if (selectedPersonnelEmpNo) {
        console.log('Loading items for emp_no:', selectedPersonnelEmpNo);
        loadItemsForPersonnel(selectedPersonnelEmpNo);
    } else {
        console.error('No emp_no found for employee');
        classificationSelect.disabled = true;
        classificationSelect.innerHTML = '<option value="">No emp_no found for this employee</option>';
    }
}

    function loadItemsForPersonnel(empNo) {
    console.log('Loading items for emp_no:', empNo);
    
    classificationSelect.innerHTML = '<option value="">Loading items...</option>';
    classificationSelect.disabled = true;
    
    const url = `/api/items-by-personnel?emp_no=${encodeURIComponent(empNo)}`;
    console.log('Fetching URL:', url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(items => {
        console.log('Items received:', items);
        
        itemsData = items;
        classificationSelect.innerHTML = '';
        
        const classifications = Object.keys(items);
        console.log('Classifications:', classifications);
        
        if (classifications.length > 0) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = 'Select item classification';
            classificationSelect.appendChild(placeholderOption);
            
            classifications.forEach(classification => {
                const option = document.createElement('option');
                option.value = classification;
                option.textContent = classification;
                const itemCount = items[classification].length;
                if (itemCount > 1) {
                    option.textContent += ` (${itemCount} items)`;
                }
                classificationSelect.appendChild(option);
            });
            
            classificationSelect.disabled = false;
        } else {
            classificationSelect.innerHTML = '<option value="">No items found for this personnel</option>';
            classificationSelect.disabled = true;
        }
        
        // Reset the item select field
        const brandModelField = document.getElementById('brand_model');
        if (brandModelField) {
            brandModelField.disabled = true;
            brandModelField.value = '';
        }
        serialNumberInput.value = '';
        propertyNumberInput.value = '';
    })
    .catch(error => {
        console.error('Error fetching items:', error);
        classificationSelect.innerHTML = `<option value="">Error: ${error.message.substring(0, 100)}</option>`;
        classificationSelect.disabled = true;
        
        Swal.fire({
            icon: 'error',
            title: 'Error Loading Items',
            text: error.message,
            toast: true,
            timer: 5000
        });
    });
}

    function getFirstFiveChars(text) {
        if (!text) return '';
        return text.trim().substring(0, 15);
    }

    classificationSelect.addEventListener('change', function() {
    const selectedClassification = this.value;
    console.log('Selected classification:', selectedClassification);

    if (selectedClassification && itemsData[selectedClassification]) {
        const items = itemsData[selectedClassification];
        console.log('Items for this classification:', items);

        // Create select element for items
        const newSelect = document.createElement('select');
        newSelect.className = 'form-select form-select-sm';
        newSelect.id = 'brand_model';
        newSelect.name = 'brand_model';
        newSelect.required = true;

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = items.length > 1 ? 'Select item' : '';
        newSelect.appendChild(placeholder);

        items.forEach((item, index) => {
            const option = document.createElement('option');
            option.value = item.no;
            // Use description instead of brand_model
            const displayText = getFirstFiveChars(item.description) || `Item ${index + 1}`;
            option.textContent = displayText;
            option.title = item.description || '';  // full description tooltip
            option.dataset.serialNumber = item.serial_number || '';
            option.dataset.propertyNumber = item.property_number || '';
            newSelect.appendChild(option);
        });

        // Replace the existing field
        const brandModelContainer = document.getElementById('brand_model');
        if (brandModelContainer) {
            brandModelContainer.parentNode.replaceChild(newSelect, brandModelContainer);
        }

        // Attach change listener
        newSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                serialNumberInput.value = selectedOption.dataset.serialNumber || '';
                propertyNumberInput.value = selectedOption.dataset.propertyNumber || '';
                console.log('Selected item:', {
                    id: selectedOption.value,
                    serial: serialNumberInput.value,
                    property: propertyNumberInput.value
                });
            } else {
                serialNumberInput.value = '';
                propertyNumberInput.value = '';
            }
        });

        newSelect.disabled = false;

        // If only one item exists, auto-select it
        if (items.length === 1) {
            const firstItem = items[0];
            newSelect.value = firstItem.no;
            serialNumberInput.value = firstItem.serial_number || '';
            propertyNumberInput.value = firstItem.property_number || '';
            console.log('Auto-selected single item:', firstItem);
        } else {
            // Reset serial/property when multiple items exist
            serialNumberInput.value = '';
            propertyNumberInput.value = '';
        }
    } else {
        // Reset if no classification selected
        const brandModelField = document.getElementById('brand_model');
        if (brandModelField) {
            brandModelField.disabled = true;
            brandModelField.value = '';
        }
        serialNumberInput.value = '';
        propertyNumberInput.value = '';
    }
});
});
</script>