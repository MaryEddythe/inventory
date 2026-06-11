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

    .category-table .btn {
        --bs-btn-padding-y: 0.08rem;
        --bs-btn-padding-x: 0.28rem;
        --bs-btn-font-size: 0.62rem;
        line-height: 1.1;
    }
</style>

<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Technical and Scientific Equipment</h1>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('inventory.category.export.pdf', 'technical-scientific-equipment') }}" class="btn btn-outline-danger d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </a>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addTechnicalScientificEquipmentModal">
                <i class="bi bi-plus-circle"></i> Add Technical and Scientific Equipment
            </button>
        </div>
    </div>

    @if($technicalScientificEquipments->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 category-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Article</th>
                        <th scope="col">Description</th>
                        <th scope="col">Property Number</th>
                        <th scope="col">Unit Value</th>
                        <th scope="col">CO/MOOE</th>
                        <th scope="col">Date Acquired</th>
                        <th scope="col">Remarks</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>

                </thead>
                <tbody>
                    @foreach($technicalScientificEquipments as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->article }}</td>
                            <td title="{{ $item->description }}">{{ Str::limit($item->description, 10) }}</td>
                            <td>{{ $item->property_number }}</td>
                            <td>{{ number_format($item->unit_value, 2) }}</td>
                            <td>{{ $item->co_mooe ?: 'N/A' }}</td>
                            <td>{{ $item->date_acquired ? $item->date_acquired->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $item->remarks ?: 'N/A' }}</td>

                            <td>
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTechnicalScientificEquipmentModal{{ $item->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('technical-scientific-equipment.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this technical and scientific equipment entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No technical and scientific equipment entries added yet. Click the "Add Technical and Scientific Equipment" button to get started.</p>
        </div>
    @endif
</div>

@foreach($technicalScientificEquipments as $item)
    <div class="modal fade" id="editTechnicalScientificEquipmentModal{{ $item->id }}" tabindex="-1" aria-labelledby="editTechnicalScientificEquipmentModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('technical-scientific-equipment.update', $item) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTechnicalScientificEquipmentModalLabel{{ $item->id }}">Edit Technical and Scientific Equipment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_technical_scientific_equipment_article_{{ $item->id }}" class="form-label">Article <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_technical_scientific_equipment_article_{{ $item->id }}" name="article" value="{{ old('article', $item->article) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_technical_scientific_equipment_property_number_{{ $item->id }}" class="form-label">Property Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_technical_scientific_equipment_property_number_{{ $item->id }}" name="property_number" value="{{ old('property_number', $item->property_number) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_technical_scientific_equipment_description_{{ $item->id }}" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_technical_scientific_equipment_description_{{ $item->id }}" name="description" rows="3" required>{{ old('description', $item->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_technical_scientific_equipment_unit_value_{{ $item->id }}" class="form-label">Unit Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="edit_technical_scientific_equipment_unit_value_{{ $item->id }}" name="unit_value" value="{{ old('unit_value', $item->unit_value) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_technical_scientific_equipment_date_acquired_{{ $item->id }}" class="form-label">Date Acquired <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_technical_scientific_equipment_date_acquired_{{ $item->id }}" name="date_acquired" value="{{ old('date_acquired', $item->date_acquired ? $item->date_acquired->format('Y-m-d') : '') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_technical_scientific_equipment_remarks_{{ $item->id }}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="edit_technical_scientific_equipment_remarks_{{ $item->id }}" name="remarks" rows="2">{{ old('remarks', $item->remarks) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Technical and Scientific Equipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addTechnicalScientificEquipmentModal" tabindex="-1" aria-labelledby="addTechnicalScientificEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTechnicalScientificEquipmentModalLabel">Add Technical and Scientific Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.modals.create-technical-scientific-equipment-modal')
            </div>
        </div>
    </div>
</div>
@endsection
