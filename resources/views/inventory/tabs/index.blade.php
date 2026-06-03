@extends('layout.app')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Inventory</h1>
        <div class="d-flex gap-2 align-items-center">
            <form id="searchForm" class="d-flex align-items-center" style="min-width: 220px;">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search anything here" value="{{ request('search') }}">
            </form>

            <!-- per-page dropdown -->
            <div class="ms-2">
                <select id="perPageSelect" name="per_page" class="form-select form-select-sm">
                    @php $currentPer = request('per_page', $perPage ?? 10); @endphp
                    <option value="10" {{ $currentPer == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $currentPer == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $currentPer == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $currentPer == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Filter Button with Dropdown -->
            <div style="position: relative;">
                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                    id="openFilterBtn">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @include('inventory.modals.filter-modal', ['departments' => $departments])
            </div>
                            
            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li class="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle d-flex align-items-center justify-content-between" href="#">
                            <span>
                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                Export as PDF
                            </span>
                            <i class="bi bi-chevron-right ms-3 small"></i>
                        </a>
                        <ul class="dropdown-menu submenu">
                            <li><a class="dropdown-item export-option" href="#" data-type="pdf" data-subtype="inventory"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Inventory</a></li>
                            <li><a class="dropdown-item export-option" href="#" data-type="pdf" data-subtype="rpcsp"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>RPCSP</a></li>
                            <li><a class="dropdown-item export-option" href="#" data-type="pdf" data-subtype="ppe"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>PPE</a></li>
                        </ul>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item export-option" href="#" data-type="csv"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Export as CSV</a></li>
                </ul>
            </div>
            
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="bi bi-plus-circle"></i> Add Inventory
            </button>
        </div>
    </div>

    <div id="table-container">
        <div class="table-responsive">
            @if(isset($items))
                @include('inventory.table-data', compact('items', 'groupedItems', 'departments', 'employees'))
            @else
                <div class="text-center py-4">No items found</div>
            @endif
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries</div>
            <div>
                {{ $items->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInventoryModalLabel">Add New Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.modals.create-modal', ['departments' => $departments, 'employees' => $employees])
            </div>
        </div>
    </div>
</div>

@include('inventory.modals.filter-modal', ['departments' => $departments])
@endsection

@push('scripts')
<script>
    // Function to update dashboard metrics if dashboard elements exist in the current DOM
    // and persist the latest values to sessionStorage so the Dashboard tab always
    // picks them up whenever it is activated — even if the user is on a different tab.
    function updateDashboardMetrics(metrics) {
        if (!metrics) return;

        // ── Persist to sessionStorage ──────────────────────────────────────────
        // The Dashboard tab reads this on DOMContentLoaded and on tab-show so it
        // always reflects the most recent data without a full page reload.
        try {
            sessionStorage.setItem('inventoryMetrics', JSON.stringify(metrics));
        } catch (e) { /* storage quota or private-mode – silently ignore */ }

        // ── Update DOM elements if they are currently visible ─────────────────
        const totalItemsEl     = document.getElementById('totalItemsCount');
        const rpcspValueEl     = document.getElementById('rpcspValueCount');
        const ppeValueEl       = document.getElementById('ppeValueCount');
        const itemsThisMonthEl = document.getElementById('itemsThisMonthCount');

        if (totalItemsEl) {
            totalItemsEl.setAttribute('data-target', metrics.totalItems);
            animateCountUp(totalItemsEl, metrics.totalItems);
        }
        if (rpcspValueEl) {
            rpcspValueEl.setAttribute('data-target', metrics.rpcspValue);
            animateCountUp(rpcspValueEl, metrics.rpcspValue);
        }
        if (ppeValueEl) {
            ppeValueEl.setAttribute('data-target', metrics.ppeValue);
            animateCountUp(ppeValueEl, metrics.ppeValue);
        }
        if (itemsThisMonthEl) {
            itemsThisMonthEl.setAttribute('data-target', metrics.itemsThisMonth);
            animateCountUp(itemsThisMonthEl, metrics.itemsThisMonth);
        }
    }

    // Function to animate count up (copied from dashboard - needed for metric updates)
    function animateCountUp(el, targetValue) {
        const target = targetValue !== undefined ? parseFloat(targetValue) : parseFloat(el.getAttribute('data-target'));
        const isCurrency = el.closest('#totalValueCount') !== null;
        let start = 0;
        const currentText = el.textContent.replace(/[^0-9.]/g, '');
        if (currentText && !isNaN(parseFloat(currentText))) {
            start = parseFloat(currentText);
        }

        const duration = 1000;
        let startTime;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = timestamp - startTime;
            const ratio = Math.min(progress / duration, 1);
            const currentValue = start + (target - start) * ratio;

            if (isCurrency) {
                el.textContent = currentValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                el.textContent = Math.round(currentValue);
            }

            if (ratio < 1) {
                window.requestAnimationFrame(step);
            }
        }

        window.requestAnimationFrame(step);
    }

document.addEventListener('DOMContentLoaded', function() {
    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');
    const perPageSelect = document.getElementById('perPageSelect');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            updateResults();
        }, 300);
    });

    // trigger update when per-page changes
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            updateResults();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Filter dropdown toggle
    // ─────────────────────────────────────────────────────────────────────────
    const openFilterBtn = document.getElementById('openFilterBtn');
    const filterDropdown = document.getElementById('filterDropdown');
    
    if (openFilterBtn && filterDropdown) {
        // Toggle dropdown on button click
        openFilterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isVisible = filterDropdown.style.display !== 'none';
            filterDropdown.style.display = isVisible ? 'none' : 'block';
            console.log('🔍 Filter dropdown toggled:', filterDropdown.style.display);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!filterDropdown.contains(e.target) && !openFilterBtn.contains(e.target)) {
                filterDropdown.style.display = 'none';
            }
        });
        
        // Position dropdown relative to button
        openFilterBtn.style.position = 'relative';
    }

    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // Handle form submission
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('📋 Filter form submitted');
            updateResults();
            
            // Close dropdown
            if (filterDropdown) {
                filterDropdown.style.display = 'none';
            }
        });

        // Handle Clear Filters button
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🔄 Clear filters clicked');
                filterForm.reset();
                updateResults();
                
                // Close dropdown after clearing
                if (filterDropdown) {
                    filterDropdown.style.display = 'none';
                }
            });
        }
    } else {
        console.warn('⚠️ Filter form not found - filter may not be working properly');
    }

    // Handle submenu toggle
    const pdfMenuItem = document.querySelector('.dropdown-submenu .dropdown-toggle');
    const submenu = document.querySelector('.dropdown-submenu .submenu');

    if (pdfMenuItem && submenu) {
        pdfMenuItem.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            submenu.classList.toggle('show');
        });

        // Close submenu when clicking outside
        document.addEventListener('click', function(e) {
            if (!pdfMenuItem.contains(e.target) && !submenu.contains(e.target)) {
                submenu.classList.remove('show');
            }
        });
    }

    document.querySelectorAll('.export-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const exportType = this.getAttribute('data-type');
            const subtype = this.getAttribute('data-subtype');
            exportData(exportType, subtype);
        });
    });

    function attachFormListeners() {
        // Client-side validation for add inventory form
        const addInventoryForm = document.getElementById('add-inventory-form');
        if (addInventoryForm && !addInventoryForm._listenerAttached) {
            // Only attach once per form instance
            addInventoryForm._listenerAttached = true;

            addInventoryForm.addEventListener('submit', function(e) {
                e.preventDefault();

                console.log('🔴 FORM SUBMIT EVENT FIRED');

                // Prevent duplicate submissions
                if (this._isSubmitting) {
                    console.warn('❌ BLOCKED: Form submission already in progress - preventing duplicate');
                    return;
                }
                this._isSubmitting = true;
                console.log('✅ Submission guard activated - _isSubmitting = true');

                const empNoInput = this.querySelector('input[name="emp_no"]');
                if (!empNoInput) {
                    console.error('emp_no input not found in add form');
                    this._isSubmitting = false;
                    return;
                }
                const empNo = empNoInput.value;
                if (!empNo) {
                    console.warn('Employee not selected');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Please select a valid employee from the search results.'
                    });
                    this._isSubmitting = false;
                    return;
                }

                const formData = new FormData(this);

                Swal.fire({
                    title: 'Adding Item...',
                    text: 'Please wait while we add the item',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'CSRF token not found. Please refresh the page.'
                    });
                    this._isSubmitting = false;
                    return;
                }

                console.log('📤 Sending FETCH request to /inventory.store');
                fetch('{{ route("inventory.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('📨 RESPONSE received:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('📦 RESPONSE JSON parsed:', data);
                    addInventoryForm._isSubmitting = false;
                    Swal.close();
                    if (data.success) {
                        console.log('✅ SUCCESS - Item created');
                        // Update dashboard metrics if available
                        if (data.metrics) {
                            updateDashboardMetrics(data.metrics);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            console.log('Closing modal and reloading page...');
                            $('#addInventoryModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        console.error('❌ ERROR - Server returned failure:', data.message);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An error occurred while adding the item'
                        });
                    }
                })
                .catch(error => {
                    console.error('💥 FETCH ERROR:', error);
                    addInventoryForm._isSubmitting = false;
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while adding the item. Check console for details.'
                    });
                });
            });
        }

        // Client-side validation for edit inventory forms
        document.querySelectorAll('.edit-inventory-form').forEach(form => {
            const employeeSearchInput = form.querySelector('input[id^="employee_search-"]');
            const empNoInput = form.querySelector('input[name="emp_no"]');
            const enduserInput = form.querySelector('input[name="enduser"]');

            if (employeeSearchInput && enduserInput && !employeeSearchInput.value.trim()) {
                employeeSearchInput.value = enduserInput.value || employeeSearchInput.dataset.originalEnduser || '';
            }

            if (enduserInput && !enduserInput.value.trim() && employeeSearchInput?.value.trim()) {
                enduserInput.value = employeeSearchInput.value.trim();
            }

            // Only attach once per form instance
            if (form._listenerAttached) return;
            form._listenerAttached = true;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                console.log('🔧 EDIT FORM SUBMIT:', {
                    formId: this.id,
                    formClass: this.className,
                    formTag: this.tagName,
                    formFieldCount: this.querySelectorAll('input, select, textarea').length
                });
                
                // Verify this is the correct form
                const empNoInput = this.querySelector('input[name="emp_no"]');
                const enduserInput = this.querySelector('input[name="enduser"]');
                const employeeSearchInput = this.querySelector('input[id^="employee_search-"]');
                const classificationInput = this.querySelector('input[name="classification"]');
                const descriptionInput = this.querySelector('textarea[name="description"]');
                
                if (!empNoInput || !classificationInput || !descriptionInput) {
                    console.error('Required form fields not found', {
                        empNo: empNoInput?.value,
                        classification: classificationInput?.value, 
                        description: descriptionInput?.value
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Form fields not properly initialized. Please refresh and try again.'
                    });
                    return;
                }
                
                const empNo = empNoInput.value;
                if (enduserInput && !enduserInput.value.trim() && employeeSearchInput?.value.trim()) {
                    enduserInput.value = employeeSearchInput.value.trim();
                }

                if (employeeSearchInput && !employeeSearchInput.value.trim() && enduserInput?.value.trim()) {
                    employeeSearchInput.value = enduserInput.value.trim();
                }

                if (!empNo) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Please select a valid employee from the search results.'
                    });
                    return;
                }

                // Temporarily enable all disabled fields so they're included in FormData
                const disabledFields = this.querySelectorAll(':disabled');
                console.log('🔧 Disabled fields found:', disabledFields.length);
                disabledFields.forEach(field => field.disabled = false);

                const itemId = this.id.split('-').pop();

                // Debug: Log all form fields BEFORE creating FormData
                const allFields = this.querySelectorAll('input, select, textarea');
                console.log('🔍 ALL FORM FIELDS:', allFields.length);
                allFields.forEach((field, index) => {
                    console.log(`Field ${index}:`, {
                        name: field.name,
                        type: field.type,
                        value: field.value,
                        id: field.id,
                        disabled: field.disabled
                    });
                });

                const formData = new FormData(this);

                // Re-disable the fields after FormData is created
                disabledFields.forEach(field => field.disabled = true);

                // Debug: Log FormData contents
                console.log('Form ID:', this.id);
                console.log('Item ID:', itemId);
                const formDataArray = Array.from(formData.entries());
                console.log('📦 FormData entries count:', formDataArray.length);
                console.log('📦 FormData entries:', formDataArray);

                Swal.fire({
                    title: 'Updating Item...',
                    text: 'Please wait while we update the item',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'CSRF token not found. Please refresh the page.'
                    });
                    return;
                }

                fetch(`{{ route("inventory.update", ":id") }}`.replace(':id', itemId), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        // Update dashboard metrics if available
                        if (data.metrics) {
                            updateDashboardMetrics(data.metrics);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $(`#editInventoryModal${itemId}`).modal('hide');
                            location.reload();
                        });
                    } else {
                        // Show validation errors if present
                        let errorMessage = data.message || 'An error occurred while updating the item';
                        if (data.errors) {
                            const errorList = Object.entries(data.errors)
                                .map(([field, errors]) => `${field}: ${errors.join(', ')}`)
                                .join('\n');
                            errorMessage += '\n\n' + errorList;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error!',
                            text: errorMessage
                        });
                        console.error('Validation errors:', data.errors);
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Request error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while updating the item. Check console for details.'
                    });
                });
            });
        });

        document.querySelectorAll('.delete-form').forEach(form => {
            // Only attach once per form instance
            if (form._listenerAttached) return;
            form._listenerAttached = true;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You won\'t be able to revert this!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData(this);

                        const csrfToken = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfToken) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'CSRF token not found. Please refresh the page.'
                            });
                            return;
                        }

                        fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update dashboard metrics if available
                                if (data.metrics) {
                                    updateDashboardMetrics(data.metrics);
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message || 'An error occurred while deleting the item'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while deleting the item'
                            });
                        });
                    }
                });
            });
        });
    }

    function bindPaginationLinks() {
        document.querySelectorAll('#table-container .pagination a').forEach(link => {
            link.removeEventListener('click', paginationClickHandler);
            link.addEventListener('click', paginationClickHandler);
        });
    }

    function paginationClickHandler(e) {
        e.preventDefault();
        const href = this.href;
        if (!href) return;

        const params = new URL(href);
        if (perPageSelect && perPageSelect.value) {
            params.searchParams.set('per_page', perPageSelect.value);
        }

        fetch(params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#table-container');
            
            if (newContent) {
                document.getElementById('table-container').innerHTML = newContent.innerHTML;
                history.pushState({}, '', params.toString());
                attachFormListeners();
                bindPaginationLinks();
            }
        })
        .catch(() => {
            window.location.href = params.toString();
        });
    }

    attachFormListeners();
    bindPaginationLinks();

    function updateResults() {
        const searchParams = new URLSearchParams();

        if (searchInput.value) {
            searchParams.append('search', searchInput.value);
        }

        // Only process filter form if it exists
        if (filterForm) {
            const formData = new FormData(filterForm);
            for (let pair of formData.entries()) {
                if (pair[1]) {
                    searchParams.append(pair[0], pair[1]);
                }
            }
        }

        // include per_page explicitly (from dropdown)
        if (perPageSelect && perPageSelect.value) {
            searchParams.set('per_page', perPageSelect.value);
        }

        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({}, '', newUrl);

        fetch(`${window.location.pathname}?${searchParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#table-container');
            
            if (newContent) {
                document.getElementById('table-container').innerHTML = newContent.innerHTML;
                attachFormListeners();
                bindPaginationLinks();
            }
        });
    }

    function exportData(type, subtype = null) {
        const searchParams = new URLSearchParams(window.location.search);

        // Only process filter form if it exists
        if (filterForm) {
            const formData = new FormData(filterForm);
            for (let pair of formData.entries()) {
                if (pair[1]) {
                    searchParams.append(pair[0], pair[1]);
                }
            }
        }

        if (perPageSelect && perPageSelect.value) {
            searchParams.set('per_page', perPageSelect.value);
        }

        if (subtype) {
            searchParams.set('subtype', subtype);
        }

        Swal.fire({
            title: 'Exporting...',
            text: 'Please wait while we prepare your file',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const exportUrl = `{{ route('inventory.export', ':type') }}?${searchParams.toString()}`.replace(':type', type);
        window.location.href = exportUrl;

        setTimeout(() => {
            Swal.close();
        }, 2000);
    }

    // Reset form when modal is hidden
    const addInventoryModal = document.getElementById('addInventoryModal');
    if (addInventoryModal) {
        addInventoryModal.addEventListener('hidden.bs.modal', function(e) {
            const form = document.getElementById('add-inventory-form');
            if (form) {
                // Reset form state
                form.reset();
                // Clear the submission flag so fresh submission is allowed
                form._isSubmitting = false;
                // Clear all input fields for fresh form
                form.querySelectorAll('input, textarea, select').forEach(field => {
                    field.value = '';
                });

                // Log for debugging
                console.log('Modal hidden - form reset and _isSubmitting cleared');
            }
        });
    }
});
</script>
@endpush
