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

    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0" style="font-size: 1.1rem;">
            <thead style="background: #f3f4f6;">
                <tr class="text-secondary">
                    <th>No</th>
                    <th>Division</th>
                    <th>Enduser</th>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Serial Number</th>
                    <th>Property Number</th>
                    <th>Unit Price</th>
                    <th>CO/MOOE</th>
                    <th>Date Acquired</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                @php
                    // Calculate years since acquisition
                    $yearsSinceAcquisition = $item->date_acquired ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now()) : 10;

                    // Determine badge class based on age
                    $ageBadgeClass = $yearsSinceAcquisition <= 5 ? 'badge-age-new' : 'badge-age-old';
                @endphp
                <tr data-item-id="{{ $item->no }}">
                    <td class="fw-semibold text-muted">{{ $item->no }}</td>
                    <td>
                        <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                            {{ $item->division }}
                        </span>
                    </td>
                    <td class="item-enduser">
                        @if(request('search'))
                            {!! preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->enduser) !!}
                        @else
                            {{ $item->enduser }}
                        @endif
                    </td>
                    <td><span class="badge bg-secondary-subtle text-dark fw-normal item-classification">{{ $item->classification }}</span></td>
                    <td class="item-description">
                        @if(request('search'))
                            {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->description), 40) !!}
                        @else
                            {{ Str::limit($item->description, 40) }}
                        @endif
                    </td>
                    <td class="item-serial">{{ $item->serial_number ?? 'N/A' }}</td>
                    <td class="item-property">{{ $item->property_number }}</td>
                    <td class="item-price">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="item-comooe">{{ $item->co_mooe }}</td>
                    <td class="item-date">{{ $item->date_acquired ? $item->date_acquired->format('M d, Y') : 'N/A' }}</td>
                    <td class="item-remarks">{{ Str::limit($item->remarks, 20) ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $ageBadgeClass }} fw-normal" title="{{ $yearsSinceAcquisition }} years old">
                            {{ $yearsSinceAcquisition <= 5 ? 'NEW' : 'FOR REPLACEMENT' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#editInventoryModal{{ $item->no }}"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('inventory.destroy', $item->no) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <!-- Edit Inventory Modal -->
                <div class="modal fade" id="editInventoryModal{{ $item->no }}" tabindex="-1" aria-labelledby="editInventoryModalLabel{{ $item->no }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editInventoryModalLabel{{ $item->no }}">Edit Inventory Item</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @include('inventory.modals.edit-modal', ['item' => $item, 'departments' => $departments, 'employees' => $employees])
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="14" class="text-center py-4">No items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries</div>
        <div>
            {{ $items->links('vendor.pagination.bootstrap-5') }}
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

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            updateResults();
        }, 300); 
    });

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

    const addInventoryForm = document.getElementById('add-inventory-form');
    if (addInventoryForm) {
        addInventoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Add form submitted, preventing default');

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
                console.error('CSRF token not found!');
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'CSRF token not found. Please refresh the page.'
                });
                return;
            }
            console.log('CSRF token found:', csrfToken.getAttribute('content'));

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
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
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
                console.error('Fetch error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error!',
                    text: 'An error occurred while adding the item. Check console for details.'
                });
            });
        });
    }

    document.querySelectorAll('.edit-inventory-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Edit form submitted, preventing default for ID:', this.id);

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
                console.error('CSRF token not found!');
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'CSRF token not found. Please refresh the page.'
                });
                return;
            }
            console.log('CSRF token found:', csrfToken.getAttribute('content'));

            fetch(`{{ route("inventory.update", ":id") }}`.replace(':id', itemId), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Update response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Update response data:', data);
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
                console.error('Update fetch error:', error);
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
                        console.error('CSRF token not found!');
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'CSRF token not found. Please refresh the page.'
                        });
                        return;
                    }
                    console.log('CSRF token found:', csrfToken.getAttribute('content'));

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
                        console.error('Error:', error);
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

        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({}, '', newUrl);

        fetch(`${window.location.pathname}?${searchParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.querySelector('.table-responsive').innerHTML = html;
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