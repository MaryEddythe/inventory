@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2>Inventory Item Details</h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">No:</div>
                    <div class="col-md-8">{{ $inventoryItem->no }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Division:</div>
                    <div class="col-md-8">{{ $inventoryItem->division }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">End User:</div>
                    <div class="col-md-8">{{ $inventoryItem->enduser }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Classification:</div>
                    <div class="col-md-8">{{ $inventoryItem->classification }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Description:</div>
                    <div class="col-md-8">{{ $inventoryItem->description }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Serial Number:</div>
                    <div class="col-md-8">{{ $inventoryItem->serial_number ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Property Number:</div>
                    <div class="col-md-8">{{ $inventoryItem->property_number }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Unit Price:</div>
                    <div class="col-md-8">₱{{ number_format($inventoryItem->unit_price, 2) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">CO/MOOE:</div>
                    <div class="col-md-8">{{ $inventoryItem->co_mooe }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Date Acquired:</div>
                    <div class="col-md-8">{{ $inventoryItem->date_acquired->format('F d, Y') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Remarks:</div>
                    <div class="col-md-8">{{ $inventoryItem->remarks ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Created:</div>
                    <div class="col-md-8">{{ $inventoryItem->created_at->format('F d, Y H:i') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-4 fw-bold">Last Updated:</div>
                    <div class="col-md-8">{{ $inventoryItem->updated_at->format('F d, Y H:i') }}</div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.edit', $inventoryItem->no) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection