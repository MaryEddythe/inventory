@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h2>Edit Inventory Item</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.update', $inventoryItem->no) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="division" class="form-label">Division</label>
                            <input type="text" class="form-control" id="division" name="division" value="{{ $inventoryItem->division }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="enduser" class="form-label">End User</label>
                            <input type="text" class="form-control" id="enduser" name="enduser" value="{{ $inventoryItem->enduser }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="classification" class="form-label">Classification</label>
                            <input type="text" class="form-control" id="classification" name="classification" value="{{ $inventoryItem->classification }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="property_number" class="form-label">Property Number</label>
                            <input type="text" class="form-control" id="property_number" name="property_number" value="{{ $inventoryItem->property_number }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required>{{ $inventoryItem->description }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ $inventoryItem->serial_number }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit_price" class="form-label">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" value="{{ $inventoryItem->unit_price }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="co_mooe" class="form-label">CO/MOOE</label>
                            <input type="text" class="form-control" id="co_mooe" name="co_mooe" value="{{ $inventoryItem->co_mooe }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_acquired" class="form-label">Date Acquired</label>
                            <input type="date" class="form-control" id="date_acquired" name="date_acquired" value="{{ $inventoryItem->date_acquired->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ $inventoryItem->remarks }}</textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection