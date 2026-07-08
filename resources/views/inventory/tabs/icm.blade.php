@extends('layout.app')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">ICM</h1>
        <div class="d-flex gap-2 align-items-center">
            <form id="searchForm" class="d-flex align-items-center" style="min-width: 220px;">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search anything here" value="{{ request('search') }}">
            </form>

            <!-- per-page dropdown -->
            <div class="ms-2">
                <select id="perPageSelect" name="per_page" class="form-select form-select-sm">
                    @php $currentPer = request('per_page', $perPage ?? 10); @endphp
                    <option value="10" {{ $currentPer == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $currentPer == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $currentPer == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $currentPer == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            
            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel"></i> Filter
            </button>

            <input type="hidden" name="tab" value="icm" form="searchForm" />

            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item export-icm-option" href="#" data-type="pdf">
                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>ICM as PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item export-icm-option" href="#" data-type="csv">
                            <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>ICM as CSV
                        </a>
                    </li>
                </ul>
            </div>

            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addIcmModal">
                <i class="bi bi-plus-circle"></i> Add ICM
            </button>
        </div>
    </div>

    <style>
        .icm-table {
            font-size: 0.85rem;
        }
        .icm-table th, .icm-table td {
            padding: 0.4rem 0.5rem;
        }
        .icm-table thead th {
            font-size: 0.8rem;
        }
        @media (max-width: 768px) {
            .icm-table {
                font-size: 0.8rem;
            }
            .icm-table th, .icm-table td {
                padding: 0.3rem 0.35rem;
            }
            .icm-table thead th {
                font-size: 0.75rem;
            }
        }
        @media (max-width: 576px) {
            .icm-table {
                font-size: 0.75rem;
            }
            .icm-table th, .icm-table td {
                padding: 0.25rem 0.3rem;
            }
            .icm-table thead th {
                font-size: 0.7rem;
            }
        }
    </style>
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0 icm-table table-compact">
            <thead style="background: #f3f4f6;">
                <tr class="text-secondary">
                    <th title="ICM Number">ICM No</th>
                    <th title="Division">Div.</th>
                    <th title="Requesting Personnel">Personnel</th>
                    <th title="Problem Description">Problem</th>
                    <th title="Type">Type</th>
                    <th title="Priority">Priority</th>
                    <th title="Hardware/Software">HW/SW</th>
                    <th title="Brand/Model">Brand/Model</th>
                    <th title="Serial Number">Serial No</th>
                    <th title="Property Number">Prop. No</th>
                    <th title="Open Date">Open Date</th>
                    <th title="Close Date">Close Date</th>
                    <th title="Findings">Findings</th>
                    <th title="Actions TakeP
                    n">Actions</th>
                    <th title="Recommendations">Recommendations</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr data-item-id="{{ $item->icm_no }}">
                    <td class="fw-semibold">{{ $item->icm_no }}</td>
                    <td>
                        <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                            {{ $item->division }}
                        </span>
                    </td>
                    <td class="item-personnel">{{ preg_replace('/\s*\(\d+\)$/', '', $item->requesting_personnel ?? 'N/A') }}</td>
                    <td class="item-problem">
                        @if(request('search'))
                            {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->problem_description ?? ''), 12) !!}
                        @else
                            {{ Str::limit($item->problem_description ?? '', 12) }}
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $item->icm_type === 'Assistance' ? 'bg-info' : 'bg-warning text-dark' }} fw-normal">
                            {{ $item->icm_type ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $item->priority === 'P1-Critical' ? 'bg-danger' : ($item->priority === 'P2-Important' ? 'bg-warning text-dark' : ($item->priority === 'P3-Normal' ? 'bg-info' : 'bg-secondary')) }} fw-normal">
                            {{ $item->priority ?? 'N/A' }}
                        </span>
                    </td>
                    <td>{{ $item->hardware_software ?? 'N/A' }}</td>
                    <td>{{ $item->brand_model ?? 'N/A' }}</td>
                    <td class="item-serial">
                        {{ Str::limit($item->serial_number ?? 'N/A', 12) }}
                    </td>
                    <td class="item-property">{{ $item->property_number ?? 'N/A' }}</td>
                    <td class="item-open-date">{{ $item->open_date ? $item->open_date->format('M d, Y') : 'N/A' }}</td>
                    <td class="item-close-date">{{ $item->close_date ? $item->close_date->format('M d, Y') : 'N/A' }}</td>
                    <td class="item-findings">
                        {!! request('search')
                            ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->icm_findings ?? 'N/A')), 15)
                            : e(Str::limit($item->icm_findings ?? 'N/A', 15)) !!}
                    </td>
                    <td class="item-actions">
                        {!! request('search')
                            ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->actions_taken ?? 'N/A')), 15)
                            : e(Str::limit($item->actions_taken ?? 'N/A', 15)) !!}
                    </td>
                    <td class="item-recommendations">
                        {!! request('search')
                            ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->icm_recommendations ?? 'N/A')), 15)
                            : e(Str::limit($item->icm_recommendations ?? 'N/A', 15)) !!}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-primary btn-sm" title="Edit ICM" data-bs-toggle="modal" data-bs-target="#editIcmModal{{ $item->id }}"><i class="bi bi-pencil"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" class="text-center py-4">No ICM items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries</div>
            <div>
                {{ $items->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
        @foreach($items as $item)
            <!-- Edit ICM Modal -->
            <div class="modal fade" id="editIcmModal{{ $item->id }}" tabindex="-1" aria-labelledby="editIcmModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editIcmModalLabel{{ $item->id }}">Edit ICM {{ $item->icm_no }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('inventory.modals.edit-icm-modal', ['item' => $item])
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Add ICM Modal -->
<div class="modal fade" id="addIcmModal" tabindex="-1" aria-labelledby="addIcmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIcmModalLabel">Add New ICM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('inventory.modals.create-icm-modal', ['departments' => $departments, 'employees' => $employees])
            </div>
        </div>
    </div>
</div>

@include('inventory.modals.filter-icm-modal')
@endsection

@push('scripts')
<script>
    // Function to update dashboard metrics if dashboard page exists/is open
    function updateDashboardMetrics(metrics) {
        if (!metrics) return;
        
        // Update dashboard if visible
        const totalItemsEl = document.getElementById('totalItemsCount');
        const rpcspValueEl = document.getElementById('rpcspValueCount');
        const ppeValueEl = document.getElementById('ppeValueCount');
        const itemsThisMonthEl = document.getElementById('itemsThisMonthCount');

        if (totalItemsEl) {
            totalItemsEl.setAttribute('data-target', metrics.totalItems);
            animateCountUp(totalItemsEl, metrics.totalItems);
        }
        if (rpcspValueEl) {
            rpcspValueEl.setAttribute('data-target', metrics.rpcspValue);
            animateCountUp(rpcspValueEl, metrics.rpcspValue);
        }
        if (ppeValueEl) {
            ppeValueEl.setAttribute('data-target', metrics.ppeValue);
            animateCountUp(ppeValueEl, metrics.ppeValue);
        }
        if (itemsThisMonthEl) {
            itemsThisMonthEl.setAttribute('data-target', metrics.itemsThisMonth);
            animateCountUp(itemsThisMonthEl, metrics.itemsThisMonth);
        }
    }

    // Function to animate count up (copied from dashboard - needed for metric updates)
    function animateCountUp(el, targetValue) {
        const target = targetValue !== undefined ? parseFloat(targetValue) : parseFloat(el.getAttribute('data-target'));
        const isCurrency = el.closest('#totalValueCount') !== null;
        let start = 0;
        const currentText = el.textContent.replace(/[^0-9.]/g, '');
        if (currentText && !isNaN(parseFloat(currentText))) {
            start = parseFloat(currentText);
        }

        const duration = 1000;
        let startTime;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = timestamp - startTime;
            const ratio = Math.min(progress / duration, 1);
            const currentValue = start + (target - start) * ratio;

            if (isCurrency) {
                el.textContent = currentValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                el.textContent = Math.round(currentValue);
            }

            if (ratio < 1) {
                window.requestAnimationFrame(step);
            }
        }

        window.requestAnimationFrame(step);
    }

document.addEventListener('DOMContentLoaded', function() {
    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');

    // Export ICM handler (PDF/CSV) - keeps current filter/search/per_page
    document.querySelectorAll('.export-icm-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();

            const type = this.getAttribute('data-type'); // pdf | csv
            const searchParams = new URLSearchParams(window.location.search);

            // Ensure tab is set so backend knows this is ICM
            searchParams.set('tab', 'icm');

            // For CSV/PDF exports, controller export() uses the same query params (search, filter, per_page, etc.)
            if (type) {
                const exportUrl = `{{ route('inventory.export', ':type') }}?${searchParams.toString()}`.replace(':type', type);
                window.location.href = exportUrl;
            }
        });
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            updateResults();
        }, 300); 
    });

    const filterForm = document.getElementById('filterForm');
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        updateResults();
        $('#filterModal').modal('hide');
    });

    document.getElementById('clearFilters').addEventListener('click', function() {
        filterForm.reset();
        updateResults();
    });

    function attachEditFormListeners() {
        document.querySelectorAll('.edit-icm-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const itemId = this.id.split('-').pop();
                const formData = new FormData(this);

                Swal.fire({
                    title: 'Updating ICM...',
                    text: 'Please wait while we update the ICM',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'CSRF token not found. Please refresh the page.'
                    });
                    return;
                }

                fetch(`{{ route("inventory.update", ":id") }}`.replace(':id', itemId), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    if (data.success) {
                        // Update dashboard metrics if available
                        if (data.metrics) {
                            updateDashboardMetrics(data.metrics);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $(`#editIcmModal${itemId}`).modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An error occurred while updating the ICM'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while updating the ICM. Check console for details.'
                    });
                });
            });
        });
    }

    function attachAddFormListener() {
        const addForm = document.getElementById('add-icm-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                Swal.fire({
                    title: 'Adding ICM...',
                    text: 'Please wait while we add the ICM',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'CSRF token not found. Please refresh the page.'
                    });
                    return;
                }

                fetch('{{ route("inventory.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        // Update dashboard metrics if available
                        if (data.metrics) {
                            updateDashboardMetrics(data.metrics);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#addIcmModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An error occurred while adding the ICM'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while adding the ICM. Check console for details.'
                    });
                });
            });
        }
    }

    attachEditFormListeners();
    attachAddFormListener();

    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            updateResults();
        });
    }

    function bindPaginationLinks() {
        document.querySelectorAll('.table-responsive .pagination a').forEach(link => {
            link.removeEventListener('click', icmPaginationClickHandler);
            link.addEventListener('click', icmPaginationClickHandler);
        });
    }

    function icmPaginationClickHandler(e) {
        e.preventDefault();
        const href = this.href;
        if (!href) return;

        const params = new URL(href);
        if (perPageSelect && perPageSelect.value) {
            params.searchParams.set('per_page', perPageSelect.value);
        }

        fetch(params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            document.querySelector('.table-responsive').innerHTML = html;
            history.pushState({}, '', params.toString());
            attachEditFormListeners();
            bindPaginationLinks();
        })
        .catch(() => {
            window.location.href = params.toString();
        });
    }

    const originalUpdateResults = window.updateResults;
    window.updateResults = function() {
        const searchParams = new URLSearchParams();

        if (searchInput.value) {
            searchParams.append('search', searchInput.value);
        }

        const formData = new FormData(filterForm);
        for (let pair of formData.entries()) {
            if (pair[1]) {
                searchParams.append(pair[0], pair[1]);
            }
        }

        if (perPageSelect && perPageSelect.value) {
            searchParams.set('per_page', perPageSelect.value);
        }

        searchParams.set('tab', 'icm');

        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({}, '', newUrl);

        return fetch(`${window.location.pathname}?${searchParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.querySelector('.table-responsive').innerHTML = html;
            attachEditFormListeners();
            bindPaginationLinks();
        });
    };

    bindPaginationLinks();
});
</script>
@endpush
