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
        personnelInput.value = `${employee.firstname} ${employee.lastname} (${employee.emp_no})`;
        selectedPersonnelEmpNo = employee.emp_no;
        suggestionContainer.style.display = 'none';

        // Auto-populate Division using department_name (the resolved string from the
        // departments table JOIN). The raw employee.department is an integer FK and
        // would never match the <select> option values which are department name strings.
        const divisionSelect = document.getElementById('division');
        if (divisionSelect && employee.department_name) {
            divisionSelect.value = employee.department_name;
            divisionSelect.classList.remove('is-invalid');
        }

        // Load items for this personnel
        loadItemsForPersonnel(employee.emp_no);
    }

    function loadItemsForPersonnel(empNo) {
        fetch(`{{ route('api.items-by-personnel') }}?emp_no=${encodeURIComponent(empNo)}`)
            .then(response => response.json())
            .then(items => {
                itemsData = items;
                classificationSelect.innerHTML = '<option value="">Select item classification</option>';
                
                if (Object.keys(items).length > 0) {
                    Object.keys(items).forEach(classification => {
                        const option = document.createElement('option');
                        option.value = classification;
                        option.textContent = classification;
                        classificationSelect.appendChild(option);
                    });
                    classificationSelect.disabled = false;
                } else {
                    classificationSelect.disabled = true;
                    classificationSelect.innerHTML = '<option value="">No items found for this personnel</option>';
                }

                brandModelSelect.innerHTML = '<option value="">Select classification first</option>';
                brandModelSelect.disabled = true;
                serialNumberInput.value = '';
                propertyNumberInput.value = '';
            })
            .catch(error => {
                console.error('Error fetching items:', error);
                classificationSelect.disabled = true;
            });
    }

    function getFirstFiveChars(text) {
        if (!text) return '';
        return text.trim().substring(0, 15);
    }

    classificationSelect.addEventListener('change', function() {
        const selectedClassification = this.value;

        if (selectedClassification && itemsData[selectedClassification]) {
            const items = itemsData[selectedClassification];

            // Always rebuild the brand_model select (convert input→select on first run,
            // or repopulate options on subsequent classification changes)
            let brandModelField = document.getElementById('brand_model');

            // Create a fresh select element each time so options are always current
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
                // First 5 chars of description as the compact label; full text on hover
                const displayText = getFirstFiveChars(item.description) || `Item ${index + 1}`;
                option.textContent = displayText;
                option.title = item.description || '';  // full description tooltip
                option.dataset.serialNumber = item.serial_number || '';
                option.dataset.propertyNumber = item.property_number || '';
                newSelect.appendChild(option);
            });

            // Replace the existing field (input or old select) with the fresh select
            brandModelField.parentNode.replaceChild(newSelect, brandModelField);

            // Attach change listener so selecting an item populates serial/property
            newSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    serialNumberInput.value = selectedOption.dataset.serialNumber || '';
                    propertyNumberInput.value = selectedOption.dataset.propertyNumber || '';
                } else {
                    serialNumberInput.value = '';
                    propertyNumberInput.value = '';
                }
            });

            newSelect.disabled = false;

            // If only one item exists, auto-select it and populate serial/property immediately
            if (items.length === 1) {
                const firstItem = items[0];
                newSelect.value = firstItem.no;
                serialNumberInput.value = firstItem.serial_number || '';
                propertyNumberInput.value = firstItem.property_number || '';
            } else {
                // Reset serial/property when classification changes and multiple items exist
                serialNumberInput.value = '';
                propertyNumberInput.value = '';
                newSelect.focus();
            }
        } else {
            // Reset if no classification selected
            const brandModelField = document.getElementById('brand_model');
            brandModelField.disabled = true;
            brandModelField.value = '';
            serialNumberInput.value = '';
            propertyNumberInput.value = '';
        }
    });
});
</script>