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

            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel"></i> Filter
            </button>

            <!-- Export Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li class="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-bs-auto-close="outside">
                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export PDF
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item export-pdf" href="#" data-format="inventory"><i class="bi bi-list-ul me-2"></i>Export Inventory</a></li>
                            <li><a class="dropdown-item export-pdf" href="#" data-format="rpcsp"><i class="bi bi-file-text me-2"></i>Export RPCSP</a></li>
                            <li><a class="dropdown-item export-pdf" href="#" data-format="ppe"><i class="bi bi-building me-2"></i>Export PPE</a></li>
                        </ul>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item export-csv" href="#"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Export CSV</a></li>
                </ul>
            </div>

            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="bi bi-plus-circle"></i> Add Inventory
            </button>
        </div>
    </div>

    <div class="table-responsive">
        @if(isset($items))
            @include('inventory.table-data', compact('items', 'groupedItems', 'departments', 'employees'))
        @else
            <div class="text-center py-4">No items found</div>
        @endif
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

@include('inventory.modals.filter-modal')
@endsection

@push('styles')
<style>
    .dropdown-submenu {
        position: relative;
    }
    .dropdown-submenu .dropdown-menu {
        position: absolute;
        top: 0;
        left: 100%;
        margin-top: -1px;
        border-radius: 0.375rem;
        display: none;
    }
    .dropdown-submenu .dropdown-menu.show {
        display: block;
    }
    .dropdown-item.dropdown-toggle::after {
        margin-left: auto;
        border-top: 0.3em solid transparent;
        border-bottom: 0.3em solid transparent;
        border-left: 0.3em solid;
        border-right: 0;
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle nested dropdown for PDF export options
    const dropdownSubmenu = document.querySelector('.dropdown-submenu');
    if (dropdownSubmenu) {
        const pdfDropdownToggle = dropdownSubmenu.querySelector('.dropdown-toggle');
        const submenu = dropdownSubmenu.querySelector('.dropdown-menu');

        if (pdfDropdownToggle && submenu) {
            pdfDropdownToggle.addEventListener('mouseenter', () => submenu.classList.add('show'));
            dropdownSubmenu.addEventListener('mouseleave', () => submenu.classList.remove('show'));
            pdfDropdownToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                submenu.classList.toggle('show');
            });
        }

        const mainDropdown = dropdownSubmenu.closest('.dropdown');
        if (mainDropdown) {
            mainDropdown.addEventListener('hidden.bs.dropdown', () => submenu.classList.remove('show'));
        }
    }

    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');
    const perPageSelect = document.getElementById('perPageSelect');
    const filterForm = document.getElementById('filterForm');

    function getQueryParams() {
        const params = new URLSearchParams();
        
        if (searchInput.value) {
            params.set('search', searchInput.value);
        }

        if (filterForm) {
            const formData = new FormData(filterForm);
            for (let [key, value] of formData.entries()) {
                if (value) {
                    params.set(key, value);
                }
            }
        }

        if (perPageSelect?.value) {
            params.set('per_page', perPageSelect.value);
        }
        
        return params;
    }

    function updateResults() {
        const params = getQueryParams();
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        history.pushState({}, '', newUrl);

        fetch(newUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#table-container');
            
            const tableContainer = document.getElementById('table-container');
            if (newContent && tableContainer) {
                tableContainer.innerHTML = newContent.innerHTML;
            } else if (tableContainer) {
                tableContainer.innerHTML = html;
            } else {
                document.querySelector('.table-responsive').innerHTML = html;
            }

            attachFormListeners();
            bindPaginationLinks();
        })
        .catch(error => console.error('Error updating results:', error));
    }

    function exportData(type, format = null) {
        const params = getQueryParams();
        if (type === 'pdf' && format) {
            params.set('format', format);
        }
        
        const baseUrl = '{{ route("inventory.export", ["type" => ":type"]) }}'.replace(':type', type);
        const url = `${baseUrl}?${params.toString()}`;

        Swal.fire({
            title: 'Exporting...',
            text: 'Please wait while we generate your file',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        window.location.href = url;

        setTimeout(() => Swal.close(), 2500);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                updateResults();
            }, 300);
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener('change', updateResults);
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateResults();
            $('#filterModal').modal('hide');
        });

        const clearFiltersButton = document.getElementById('clearFilters');
        if (clearFiltersButton) {
            clearFiltersButton.addEventListener('click', function() {
                filterForm.reset();
                updateResults();
            });
        }
    }

    document.querySelectorAll('.export-pdf').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const format = this.getAttribute('data-format');
            exportData('pdf', format);
        });
    });

    document.querySelectorAll('.export-csv').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            exportData('csv');
        });
    });

    function attachFormListeners() {
        const addInventoryForm = document.getElementById('add-inventory-form');
        if (addInventoryForm) {
            addInventoryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const empNoInput = this.querySelector('input[name="emp_no"]');
                if (!empNoInput || !empNoInput.value) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Please select a valid employee.' });
                    return;
                }

                const formData = new FormData(this);
                Swal.fire({ title: 'Adding Item...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'CSRF token not found.' });
                    return;
                }

                fetch('{{ route("inventory.store") }}', {
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
                        Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000, showConfirmButton: false })
                           .then(() => {
                                $('#addInventoryModal').modal('hide');
                                location.reload();
                           });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: data.message || 'An error occurred.' });
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Network Error!', text: 'An error occurred.' });
                });
            });
        }

        document.querySelectorAll('.edit-inventory-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const empNoInput = this.querySelector('input[name="emp_no"]');
                if (!empNoInput || !empNoInput.value) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Please select a valid employee.' });
                    return;
                }

                const itemId = this.id.split('-').pop();
                const formData = new FormData(this);
                Swal.fire({ title: 'Updating Item...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'CSRF token not found.' });
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
                        Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000, showConfirmButton: false })
                            .then(() => {
                                $(`#editInventoryModal${itemId}`).modal('hide');
                                location.reload();
                            });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: data.message || 'An error occurred.' });
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Network Error!', text: 'An error occurred.' });
                });
            });
        });

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfToken) {
                            Swal.fire({ icon: 'error', title: 'Error!', text: 'CSRF token not found.' });
                            return;
                        }
                        fetch(this.action, {
                            method: 'POST',
                            body: new FormData(this),
                            headers: {
                                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 2000, showConfirmButton: false })
                                    .then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error!', text: data.message || 'An error occurred.' });
                            }
                        })
                        .catch(() => Swal.fire({ icon: 'error', title: 'Error!', text: 'An error occurred.' }));
                    }
                });
            });
        });
    }

    function bindPaginationLinks() {
        document.querySelectorAll('#table-container .pagination a').forEach(link => {
            link.addEventListener('click', paginationClickHandler);
        });
    }

    function paginationClickHandler(e) {
        e.preventDefault();
        const href = this.href;
        if (!href) return;

        fetch(href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#table-container');
            const tableContainer = document.getElementById('table-container');
            
            if (newContent && tableContainer) {
                tableContainer.innerHTML = newContent.innerHTML;
                history.pushState({}, '', href);
                attachFormListeners();
                bindPaginationLinks();
            } else {
                window.location.href = href;
            }
        })
        .catch(() => {
            window.location.href = href;
        });
    }

    attachFormListeners();
    bindPaginationLinks();
});
</script>
@endpush
