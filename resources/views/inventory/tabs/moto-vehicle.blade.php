@extends('layout.app')

@section('content')
<style>
    .motor-vehicle-table {
        font-size: 0.68rem;
    }

    .motor-vehicle-table th {
        font-size: 0.86rem;
    }

    .motor-vehicle-table th,
    .motor-vehicle-table td {
        padding: 0.28rem 0.4rem;
        line-height: 1.15;
    }

    .motor-vehicle-table .btn {
        --bs-btn-padding-y: 0.08rem;
        --bs-btn-padding-x: 0.28rem;
        --bs-btn-font-size: 0.62rem;
        line-height: 1.1;
    }

    /* Dropdown Submenu Styling */
    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu .submenu {
        top: 0;
        left: 100%;
        margin-top: -1px;
        display: none;
    }

    .dropdown-submenu .submenu.show {
        display: block !important;
    }
</style>

<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Motor Vehicle</h1>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div class="d-flex align-items-center flex-column" style="min-width: 240px;">
                <input id="motoVehicleSearch" type="text" class="form-control form-control-sm" placeholder="Live search..." value="{{ request('search') }}">
            </div>

            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li class="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle d-flex align-items-center justify-content-between" href="#" aria-expanded="false">
                            <span>
                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                Export as PDF
                            </span>
                            <i class="bi bi-chevron-right ms-3 small"></i>
                        </a>
                        <ul class="dropdown-menu submenu">
                            <li><a class="dropdown-item export-moto-option" href="#" data-type="pdf" data-subtype="rpcsp"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>RPCSP</a></li>
                            <li><a class="dropdown-item export-moto-option" href="#" data-type="pdf" data-subtype="ppe"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>PPE</a></li>
                        </ul>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item export-moto-option" href="#" data-type="csv"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Export as CSV</a></li>
                </ul>
            </div>

            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMotorVehicleModal">
                <i class="bi bi-plus-circle"></i>Add Motor Vehicle
            </button>
        </div>
    </div>

<div id="table-container">
    @php
        $q = trim((string) request('search'));
        $filteredMotorVehicles = $q === '' ? $motorVehicles : $motorVehicles->filter(function($v) use ($q) {
            return str_contains(strtolower((string)$v->article), strtolower($q))
                || str_contains(strtolower((string)$v->description), strtolower($q))
                || str_contains(strtolower((string)$v->property_number), strtolower($q))
                || str_contains(strtolower((string)($v->remarks ?? '')), strtolower($q));
        });
    @endphp

        @if($filteredMotorVehicles->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 motor-vehicle-table">
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
                        @foreach($filteredMotorVehicles as $vehicle)
                            <tr>

                                <td class="fw-semibold">{{ $vehicle->article }}</td>
                                <td title="{{ $vehicle->description }}">{{ Str::limit($vehicle->description, 10) }}</td>
                                <td>{{ $vehicle->property_number }}</td>
                                <td>{{ number_format($vehicle->unit_value, 2) }}</td>
                                <td>{{ $vehicle->date_acquired ? $vehicle->date_acquired->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $vehicle->remarks ?: 'N/A' }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMotorVehicleModal{{ $vehicle->id }}" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('motor-vehicle.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Delete this motor vehicle?');">
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
                <p class="text-muted mt-3">No motor vehicles added yet. Click the "+ Add Motor Vehicle" button to get started.</p>
            </div>
        @endif
    </div>
</div>

@foreach($motorVehicles as $vehicle)
    <div class="modal fade" id="editMotorVehicleModal{{ $vehicle->id }}" tabindex="-1" aria-labelledby="editMotorVehicleModalLabel{{ $vehicle->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('motor-vehicle.update', $vehicle) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMotorVehicleModalLabel{{ $vehicle->id }}">Edit Motor Vehicle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_article_{{ $vehicle->id }}" class="form-label">Article <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_article_{{ $vehicle->id }}" name="article" value="{{ old('article', $vehicle->article) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_property_number_{{ $vehicle->id }}" class="form-label">Property Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_property_number_{{ $vehicle->id }}" name="property_number" value="{{ old('property_number', $vehicle->property_number) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description_{{ $vehicle->id }}" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_description_{{ $vehicle->id }}" name="description" rows="3" required>{{ old('description', $vehicle->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_unit_value_{{ $vehicle->id }}" class="form-label">Unit Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="edit_unit_value_{{ $vehicle->id }}" name="unit_value" value="{{ old('unit_value', $vehicle->unit_value) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_date_acquired_{{ $vehicle->id }}" class="form-label">Date Acquired <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_date_acquired_{{ $vehicle->id }}" name="date_acquired" value="{{ old('date_acquired', $vehicle->date_acquired ? $vehicle->date_acquired->format('Y-m-d') : '') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_remarks_{{ $vehicle->id }}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="edit_remarks_{{ $vehicle->id }}" name="remarks" rows="2">{{ old('remarks', $vehicle->remarks) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Motor Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addMotorVehicleModal" tabindex="-1" aria-labelledby="addMotorVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMotorVehicleModalLabel">Add New Motor Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.modals.create-motor-vehicle-modal')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function debounce(fn, delay = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    }

    // Wrap everything inside DOMContentLoaded to ensure elements exist before binding scripts
    document.addEventListener('DOMContentLoaded', function() {
        const motoVehicleSearch = document.getElementById('motoVehicleSearch');

        console.log('moto-vehicle search input exists?', !!motoVehicleSearch);
        console.log('moto-vehicle scripts executed');

        let lastSearch = motoVehicleSearch ? (motoVehicleSearch.value || '').trim() : '';

        if (motoVehicleSearch) {
            const applySearch = debounce(() => {
                const q = (motoVehicleSearch.value || '').trim();
                console.log('moto-vehicle live search applySearch q=', q);

                if (q === lastSearch) return;
                lastSearch = q;

                // Reload with ?search=... so the controller can filter.
                const url = new URL(window.location.href);
                if (q) {
                    url.searchParams.set('search', q);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }, 300);

            motoVehicleSearch.addEventListener('input', () => {
                console.log('moto-vehicle input event fired, current=', motoVehicleSearch.value);
            });

            motoVehicleSearch.addEventListener('input', applySearch);
        }

        // Export dropdown options
        document.querySelectorAll('.export-moto-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();

                const type = this.getAttribute('data-type');
                const subtype = this.getAttribute('data-subtype');

                // Export as PDF: open rpcsp/ppe PDFs using existing exportCategoryPdf logic
                if (type === 'pdf') {
                    window.location.href = `{{ route('inventory.category.export.pdf', ['moto-vehicle', 'rpcsp']) }}`.replace('rpcsp', subtype);
                    return;
                }
            });
        });

        // Toggle the submenu visibility when clicking the "Export as PDF" parent item
        document.querySelectorAll('.dropdown-submenu > a.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // CRITICAL: Keeps Bootstrap from killing the parent dropdown menu frame

                const submenu = this.parentElement.querySelector('.submenu');
                if (!submenu) return;

                // Toggle visibility class used by your CSS
                submenu.classList.toggle('show');
            });
        });

        // Close nested submenu when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-submenu .submenu.show').forEach(s => s.classList.remove('show'));
        });

        // Reset form when modal is hidden
        const addMotorVehicleModal = document.getElementById('addMotorVehicleModal');
        if (addMotorVehicleModal) {
            addMotorVehicleModal.addEventListener('hidden.bs.modal', function(e) {
                const form = document.getElementById('add-motor-vehicle-form');
                if (form) {
                    form.reset();
                    form._isSubmitting = false;
                    form.querySelectorAll('input, textarea, select').forEach(field => {
                        field.value = '';
                    });
                }
            });
        }
    });
</script>
@endpush