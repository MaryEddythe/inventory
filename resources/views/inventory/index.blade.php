@extends('layout.app')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Inventory</h1>
        <div class="d-flex gap-2 align-items-center">
            <form class="d-flex align-items-center" style="min-width: 220px;">
                <input type="text" class="form-control form-control-sm" placeholder="Search anything here">
            </form>
            <a href="#" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"><i class="bi bi-funnel"></i> Filter</a>
            <a href="#" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1"><i class="bi bi-box-arrow-up"></i> Export</a>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addInventoryModal"><i class="bi bi-plus-circle"></i> Add Inventory</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0" style="font-size: 0.97rem;">
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr data-item-id="{{ $item->no }}">
                    <td class="fw-semibold text-muted">{{ $item->no }}</td>
                    <td>
                        <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                            {{ $item->division }}
                        </span>
                    </td>
                    <td class="item-enduser">{{ $item->enduser }}</td>
                    <td><span class="badge bg-secondary-subtle text-dark fw-normal item-classification">{{ $item->classification }}</span></td>
                    <td class="item-description">{{ Str::limit($item->description, 40) }}</td>
                    <td class="item-serial">{{ $item->serial_number ?? 'N/A' }}</td>
                    <td class="item-property">{{ $item->property_number }}</td>
                    <td class="item-price">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="item-comooe">{{ $item->co_mooe }}</td>
                    <td class="item-date">{{ $item->date_acquired->format('M d, Y') }}</td>
                    <td class="item-remarks">{{ Str::limit($item->remarks, 20) ?? 'N/A' }}</td>
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
                                @include('inventory.edit-modal', ['item' => $item])
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-4">No items found.</td>
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
                @include('inventory.create-modal')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    document.querySelector('#addInventoryModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;
        let formData = new FormData(form);

        $.ajax({
            url: form.action,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });

    // Handle edit form submissions
    $('.edit-inventory-form').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let itemId = form.attr('id').split('-').pop();
        let formData = new FormData(form[0]);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Response:', response); // Debug log
                
                if (response.success && response.item) {
                    let item = response.item;
                    let row = $(`tr[data-item-id="${itemId}"]`);
                    
                    if (row.length) {
                        row.find('.badge-division').text(item.division);
                        row.find('.item-enduser').text(item.enduser);
                        row.find('.item-classification').text(item.classification);
                        row.find('.item-description').text(
                            item.description.length > 40 ? item.description.substring(0, 40) + '...' : item.description
                        );
                        row.find('.item-serial').text(item.serial_number || 'N/A');
                        row.find('.item-property').text(item.property_number);
                        row.find('.item-price').text('₱' + parseFloat(item.unit_price).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        row.find('.item-comooe').text(item.co_mooe);
                        row.find('.item-date').text(new Date(item.date_acquired).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        }));
                        row.find('.item-remarks').text(
                            item.remarks ? (item.remarks.length > 20 ? item.remarks.substring(0, 20) + '...' : item.remarks) : 'N/A'
                        );

                        // Close modal and show success message
                        $(`#editInventoryModal${itemId}`).modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                    }
                } else {
                    throw new Error('Invalid response format');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr); // Debug log
                let errorMessage = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });

    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This item will be deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: data.message,
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to delete item.',
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred: ' + error.message,
                        });
                    });
                }
            });
        });
    });
});
</script>
@endpush