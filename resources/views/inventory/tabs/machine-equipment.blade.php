@extends('layout.app')

@section('content')
<style>
    .category-table { font-size: 0.68rem; }
    .category-table th { font-size: 0.86rem; }
    .category-table th,
    .category-table td {
        padding: 0.28rem 0.4rem;
        line-height: 1.15;
    }
</style>

<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Machine & Equipment</h1>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMachineEquipmentModal">
                <i class="bi bi-plus-circle"></i> Add Machine & Equipment
            </button>
        </div>
    </div>

    @if($machineEquipments->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 category-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Article</th>
                        <th scope="col">Description</th>
                        <th scope="col">Property Number</th>
                        <th scope="col">Unit Value</th>
                        <th scope="col">Date Acquired</th>
                        <th scope="col">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machineEquipments as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->article }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->property_number }}</td>
                            <td>{{ number_format($item->unit_value, 2) }}</td>
                            <td>{{ $item->date_acquired ? $item->date_acquired->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $item->remarks ?: 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No machine and equipment entries added yet. Click the "Add Machine & Equipment" button to get started.</p>
        </div>
    @endif
</div>

<div class="modal fade" id="addMachineEquipmentModal" tabindex="-1" aria-labelledby="addMachineEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMachineEquipmentModalLabel">Add Machine & Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.modals.create-machine-equipment-modal')
            </div>
        </div>
    </div>
</div>
@endsection
