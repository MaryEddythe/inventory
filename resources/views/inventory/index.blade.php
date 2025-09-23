@extends('layout.app')

@section('content')
<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">Inventory Items</h1>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Item
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Division</th>
                    <th scope="col">Enduser</th>
                    <th scope="col">Classification</th>
                    <th scope="col">Description</th>
                    <th scope="col">Serial Number</th>
                    <th scope="col">Property Number</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">CO/MOOE</th>
                    <th scope="col">Date Acquired</th>
                    <th scope="col">Remarks</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $item->no }}</td>
                    <td>{{ $item->division }}</td>
                    <td>{{ $item->enduser }}</td>
                    <td>{{ $item->classification }}</td>
                    <td>{{ Str::limit($item->description, 50) }}</td>
                    <td>{{ $item->serial_number ?? 'N/A' }}</td>
                    <td>{{ $item->property_number }}</td>
                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->co_mooe }}</td>
                    <td>{{ $item->date_acquired->format('M d, Y') }}</td>
                    <td>{{ Str::limit($item->remarks, 30) ?? 'N/A' }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('inventory.show', $item->no) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('inventory.edit', $item->no) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('inventory.destroy', $item->no) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
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

    <div class ="popup-modal add-new-item

    <div class="d-flex justify-content-center mt-4">
        {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection