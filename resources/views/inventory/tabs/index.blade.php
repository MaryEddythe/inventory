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
            
            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item export-option" href="#" data-type="pdf"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export as PDF</a></li>
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

@include('inventory.modals.filter-modal')
@endsection

@push('scripts')
<script>
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

    const filterForm = document.getElementById('filterForm');
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        updateResults();
        $('#filterModal').modal('hide');
    });

    document.getElementById('clearFilters').addEventListener('click', function() {
        filterForm.reset();
        updateResults();
    });

    document.querySelectorAll('.export-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const exportType = this.getAttribute('data-type');
            exportData(exportType);
        });
    });

    function attachFormListeners() {
        // Client-side validation for add inventory form
        const addInventoryForm = document.getElementById('add-inventory-form');
        if (addInventoryForm) {
            addInventoryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const empNoInput = this.querySelector('input[name="emp_no"]');
                if (!empNoInput) {
                    console.error('emp_no input not found in add form');
                    return;
                }
                const empNo = empNoInput.value;
                if (!empNo) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Please select a valid employee from the search results.'
                    });
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#addInventoryModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An error occurred while adding the item'
                        });
                    }
                })
                .catch(error => {
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
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const empNoInput = this.querySelector('input[name="emp_no"]');
                if (!empNoInput) {
                    console.error('emp_no input not found in edit form');
                    return;
                }
                const empNo = empNoInput.value;
                if (!empNo) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Please select a valid employee from the search results.'
                    });
                    return;
                }

                const itemId = this.id.split('-').pop();
                const formData = new FormData(this);

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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An error occurred while updating the item'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while updating the item. Check console for details.'
                    });
                });
            });
        });

        document.querySelectorAll('.delete-form').forEach(form => {
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

        const formData = new FormData(filterForm);
        for (let pair of formData.entries()) {
            if (pair[1]) {
                searchParams.append(pair[0], pair[1]);
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

    function exportData(type) {
        const searchParams = new URLSearchParams(window.location.search);

        const formData = new FormData(filterForm);
        for (let pair of formData.entries()) {
            if (pair[1]) {
                searchParams.append(pair[0], pair[1]);
            }
        }

        if (perPageSelect && perPageSelect.value) {
            searchParams.set('per_page', perPageSelect.value);
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
});
</script>
@endpush