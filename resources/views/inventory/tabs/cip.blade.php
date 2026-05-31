@extends('layout.app')

@section('content')
<style>
    .cip-table {
        font-size: 0.68rem;
    }

    .cip-table th {
        font-size: 0.86rem;
    }

    .cip-table th,
    .cip-table td {
        padding: 0.28rem 0.4rem;
        line-height: 1.15;
    }

    .cip-table .btn {
        --bs-btn-padding-y: 0.08rem;
        --bs-btn-padding-x: 0.28rem;
        --bs-btn-font-size: 0.62rem;
        line-height: 1.1;
    }
</style>

<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">CIP</h1>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCipModal">
                <i class="bi bi-plus-circle"></i> Add CIP
            </button>
        </div>
    </div>

    @if($cips->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 cip-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Article</th>
                        <th scope="col">Description</th>
                        <th scope="col">Property Number</th>
                        <th scope="col">Unit Value</th>
                        <th scope="col">Date Acquired</th>
                        <th scope="col">Remarks</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cips as $cip)
                        <tr>
                            <td class="fw-semibold">{{ $cip->article }}</td>
                            <td>{{ $cip->description }}</td>
                            <td>{{ $cip->property_number }}</td>
                            <td>{{ number_format($cip->unit_value, 2) }}</td>
                            <td>{{ $cip->date_acquired ? $cip->date_acquired->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $cip->remarks ?: 'N/A' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCipModal{{ $cip->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('cip.destroy', $cip) }}" method="POST" onsubmit="return confirm('Delete this CIP entry?');">
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
            <p class="text-muted mt-3">No CIP entries added yet. Click the "Add CIP" button to get started.</p>
        </div>
    @endif
</div>

@foreach($cips as $cip)
    <div class="modal fade" id="editCipModal{{ $cip->id }}" tabindex="-1" aria-labelledby="editCipModalLabel{{ $cip->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('cip.update', $cip) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCipModalLabel{{ $cip->id }}">Edit CIP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cip_article_{{ $cip->id }}" class="form-label">Article <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_cip_article_{{ $cip->id }}" name="article" value="{{ old('article', $cip->article) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_cip_property_number_{{ $cip->id }}" class="form-label">Property Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_cip_property_number_{{ $cip->id }}" name="property_number" value="{{ old('property_number', $cip->property_number) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_cip_description_{{ $cip->id }}" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_cip_description_{{ $cip->id }}" name="description" rows="3" required>{{ old('description', $cip->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cip_unit_value_{{ $cip->id }}" class="form-label">Unit Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="edit_cip_unit_value_{{ $cip->id }}" name="unit_value" value="{{ old('unit_value', $cip->unit_value) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_cip_date_acquired_{{ $cip->id }}" class="form-label">Date Acquired <span class="text-danger">*</span></label>
                                <input type="date" class="form-control edit-cip-date-acquired" id="edit_cip_date_acquired_{{ $cip->id }}" name="date_acquired" value="{{ old('date_acquired', $cip->date_acquired ? $cip->date_acquired->format('Y-m-d') : '') }}" data-cip-id="{{ $cip->id }}" {{ $cip->date_acquired ? 'required' : 'disabled' }}>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input edit-cip-date-na" id="edit_cip_date_acquired_na_{{ $cip->id }}" data-cip-id="{{ $cip->id }}" {{ $cip->date_acquired ? '' : 'checked' }}>
                                    <label class="form-check-label" for="edit_cip_date_acquired_na_{{ $cip->id }}">NA</label>
                                </div>
                                <input type="hidden" id="edit_cip_date_acquired_type_{{ $cip->id }}" name="date_acquired_type" value="{{ $cip->date_acquired ? 'date' : 'na' }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_cip_remarks_{{ $cip->id }}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="edit_cip_remarks_{{ $cip->id }}" name="remarks" rows="2">{{ old('remarks', $cip->remarks) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update CIP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addCipModal" tabindex="-1" aria-labelledby="addCipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCipModalLabel">Add CIP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.modals.create-cip-modal')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addCipModal = document.getElementById('addCipModal');
        if (addCipModal) {
            addCipModal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('add-cip-form');
                if (form) {
                    form.reset();
                    const dateInput = document.getElementById('cip_date_acquired');
                    const typeInput = document.getElementById('cip_date_acquired_type');
                    if (dateInput) {
                        dateInput.disabled = false;
                        dateInput.setAttribute('required', 'required');
                    }
                    if (typeInput) {
                        typeInput.value = 'date';
                    }
                }
            });
        }

        document.querySelectorAll('.edit-cip-date-na').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const cipId = this.dataset.cipId;
                const dateInput = document.getElementById(`edit_cip_date_acquired_${cipId}`);
                const typeInput = document.getElementById(`edit_cip_date_acquired_type_${cipId}`);

                if (!dateInput || !typeInput) return;

                if (this.checked) {
                    dateInput.value = '';
                    dateInput.disabled = true;
                    dateInput.removeAttribute('required');
                    typeInput.value = 'na';
                } else {
                    dateInput.disabled = false;
                    dateInput.setAttribute('required', 'required');
                    typeInput.value = 'date';
                }
            });
        });
    });
</script>
@endpush
