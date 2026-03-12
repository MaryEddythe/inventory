<form id="add-icm-form" class="add-icm-form" enctype="multipart/form-data">
    @csrf
    
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
            <small class="text-muted">Start typing to search for personnel</small>
        </div>
    </div>

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
        <div class="col-md-6">
            <label for="hardware_software" class="form-label">Hardware or Software <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" id="hardware_software" name="hardware_software" required>
                <option value="">Select Type</option>
                <option value="Hardware">Hardware</option>
                <option value="Software">Software</option>
            </select>
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

    <div class="text-end">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Add ICM</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
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

                // Reset brand/model and item fields
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

    // Helper function to get first 5 words of description
    function getFirstFiveWords(text) {
        if (!text) return '';
        const words = text.trim().split(/\s+/).slice(0, 5);
        return words.join(' ');
    }

    // Handle classification selection
    classificationSelect.addEventListener('change', function() {
        const selectedClassification = this.value;
        const brandModelField = document.getElementById('brand_model');

        if (selectedClassification && itemsData[selectedClassification]) {
            const items = itemsData[selectedClassification];

            // Convert brand_model to a select dropdown for better UX
            if (brandModelField.tagName !== 'SELECT') {
                const newSelect = document.createElement('select');
                newSelect.className = 'form-select form-select-sm';
                newSelect.id = 'brand_model';
                newSelect.name = 'brand_model';
                newSelect.required = true;

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select item';
                newSelect.appendChild(placeholder);

                items.forEach((item, index) => {
                    const option = document.createElement('option');
                    option.value = item.no;
                    // Show description (first 5 words) or brand_model as fallback
                    const displayText = getFirstFiveWords(item.description) || item.brand_model || `Item ${index + 1}`;
                    option.textContent = displayText;
                    option.dataset.serialNumber = item.serial_number || '';
                    option.dataset.propertyNumber = item.property_number || '';
                    option.dataset.brandModel = item.brand_model || '';
                    newSelect.appendChild(option);
                });

                // Replace the input with the select
                brandModelField.parentNode.replaceChild(newSelect, brandModelField);

                // Re-assign the reference
                const newBrandModelField = document.getElementById('brand_model');
                newBrandModelField.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        serialNumberInput.value = selectedOption.dataset.serialNumber || '';
                        propertyNumberInput.value = selectedOption.dataset.propertyNumber || '';
                    } else {
                        serialNumberInput.value = '';
                        propertyNumberInput.value = '';
                    }
                });

                newBrandModelField.disabled = false;
                newBrandModelField.focus();
            }

            // Automatically populate with first item if only one exists
            if (items.length === 1) {
                const firstItem = items[0];
                const brandModelSelectField = document.getElementById('brand_model');
                brandModelSelectField.value = firstItem.no;
                serialNumberInput.value = firstItem.serial_number || '';
                propertyNumberInput.value = firstItem.property_number || '';
            }
        } else {
            // Reset if no classification selected
            document.getElementById('brand_model').disabled = true;
            serialNumberInput.value = '';
            propertyNumberInput.value = '';
            document.getElementById('brand_model').value = '';
        }
    });
});
</script>

<style>
.hover-effect:hover {
    background-color: #f0f0f0;
}
</style>
