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
    });
</script>
@endpush
