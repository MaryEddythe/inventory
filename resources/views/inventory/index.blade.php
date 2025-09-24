@extends('layout.app')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
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
                <tr>
                    <td class="fw-semibold text-muted">{{ $item->no }}</td>
                    <td>
                        <span class="badge fw-normal badge-division-{{ $item->division }}">
                            {{ $item->division }}
                        </span>
                    </td>
                    <td>{{ $item->enduser }}</td>
                    <td><span class="badge bg-secondary-subtle text-dark fw-normal">{{ $item->classification }}</span></td>
                    <td>{{ Str::limit($item->description, 40) }}</td>
                    <td>{{ $item->serial_number ?? 'N/A' }}</td>
                    <td>{{ $item->property_number }}</td>
                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->co_mooe }}</td>
                    <td>{{ $item->date_acquired->format('M d, Y') }}</td>
                    <td>{{ Str::limit($item->remarks, 20) ?? 'N/A' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('inventory.edit', $item->no) }}" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('inventory.destroy', $item->no) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        timer: 1800,
        showConfirmButton: false
    });
@endif

// Intercept delete form submit
document.querySelectorAll('form[action*="inventory.destroy"]').forEach(function(form) {
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
                form.submit();
            }
        });
    });
});
</script>
@endpush