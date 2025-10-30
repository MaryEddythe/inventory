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
        <h1 class="h4 fw-bold mb-0">IPM</h1>
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
=======
    <!-- per-page dropdown (new) -->
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
    
    <input type="hidden" name="tab" value="ipm" form="searchForm" />

            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item export-option" href="#" data-type="pdf"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export as PDF</a></li>
                    <li><a class="dropdown-item export-option" href="#" data-type="csv"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Export as CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    

    <style>
        .ipm-table {
            font-size: 1.1rem;
        }
        @media (max-width: 768px) {
            .ipm-table {
                font-size: 0.9rem;
            }
            .ipm-table th, .ipm-table td {
                padding: 0.5rem 0.25rem;
            }
        }
        @media (max-width: 576px) {
            .ipm-table {
                font-size: 0.8rem;
            }
            .ipm-table th, .ipm-table td {
                padding: 0.4rem 0.2rem;
            }
        }
    </style>
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0 ipm-table">
            <thead style="background: #f3f4f6;">
                <tr class="text-secondary">
                    <th title="Number">No</th>
                    <th title="Division">Div.</th>
                    <th title="User">User</th>
                    <th title="Type">Type</th>
                    <th title="Description">Desc</th>
                    <th title="Condition">Condition</th>
                    <th title="System Boot Up">Boot Up</th>
                    <th title="Hardware">Hardware</th>
                    <th title="Performance">Performance</th>
                    <th title="Cables and Connections">Cables/Conn</th>
                    <th title="Peripherals">Periph</th>
                    <th title="Recommendation">Recommendations</th>
                    <th title="Date Conducted">Date</th>
                    <th title="Time Started">Start</th>
                    <th title="Time Ended">End</th>
                    <th title="Action">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr data-item-id="{{ $item->no }}">
                    <td class="fw-semibold text-muted">{{ $item->no }}</td>
                    <td>
                        <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                            {{ $item->division }}
                        </span>
                    </td>
                    <td class="item-enduser">
                        @if(request('search'))
                            {!! preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->enduser) !!}
                        @else
                            {{ $item->enduser }}
                        @endif
                    </td>
                    <td><span class="badge bg-secondary-subtle text-dark fw-normal item-classification">{{ $item->classification }}</span></td>
                    <td class="item-description">
                        @if(request('search'))
                            {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->description), 40) !!}
                        @else
                            {{ Str::limit($item->description, 40) }}
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $item->condition === 'Functional' ? 'bg-success' : 'bg-warning text-dark' }} fw-normal">
                            {{ $item->condition === 'Functional' ? 'FUNC' : 'NONFUNC' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $item->system_boot_up ? '✓' : '✗' }}</td>
                    <td class="text-center">{{ $item->hardware ? '✓' : '✗' }}</td>
                    <td class="text-center">{{ $item->performance ? '✓' : '✗' }}</td>
                    <td class="text-center">{{ $item->cables_connections ? '✓' : '✗' }}</td>
                    <td class="text-center">{{ $item->peripherals ? '✓' : '✗' }}</td>
                    <td class="item-recommendation">
                        {!! request('search')
                            ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->recommendation ?? 'N/A')), 20)
                            : e(Str::limit($item->recommendation ?? 'N/A', 20)) !!}
                    </td>
                    <td class="item-date-conducted">{{ $item->date_conducted ? $item->date_conducted->format('M d, Y') : 'N/A' }}</td>
                    <td class="item-time-started">{{ $item->time_started ? \Carbon\Carbon::parse($item->time_started)->format('h:iA') : 'N/A' }}</td>
                    <td class="item-time-ended">{{ $item->time_ended ? \Carbon\Carbon::parse($item->time_ended)->format('h:iA') : 'N/A' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-primary btn-sm" title="Edit IPM" data-bs-toggle="modal" data-bs-target="#editIpmModal{{ $item->no }}"><i class="bi bi-pencil"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="text-center py-4">No items found.</td>
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
            <!-- Edit IPM Modal -->
            <div class="modal fade" id="editIpmModal{{ $item->no }}" tabindex="-1" aria-labelledby="editIpmModalLabel{{ $item->no }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editIpmModalLabel{{ $item->no }}">Edit IPM for Item {{ $item->no }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('inventory.modals.edit-ipm-modal', ['item' => $item])
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('inventory.modals.filter-ipm-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');

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

    document.querySelectorAll('.export-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const exportType = this.getAttribute('data-type');
            exportData(exportType);
        });
    });

    function attachEditFormListeners() {
        document.querySelectorAll('.edit-ipm-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Edit form submitted, preventing default for ID:', this.id);

                const itemId = this.id.split('-').pop();
                const formData = new FormData(this);

                Swal.fire({
                    title: 'Updating IPM...',
                    text: 'Please wait while we update the IPM',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'CSRF token not found. Please refresh the page.'
                    });
                    return;
                }
                console.log('CSRF token found:', csrfToken.getAttribute('content'));

                fetch(`{{ route("inventory.update", ":id") }}`.replace(':id', itemId), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Update response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Update response data:', data);
                    Swal.close();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $(`#editIpmModal${itemId}`).modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An error occurred while updating the IPM'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Update fetch error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while updating the IPM. Check console for details.'
                    });
                });
            });
        });
    }

    attachEditFormListeners();

    function updateResults() {
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

        // Add tab=ipm to ensure controller filters correctly
        searchParams.set('tab', 'ipm');

        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({}, '', newUrl);

        fetch(`${window.location.pathname}?${searchParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.querySelector('.table-responsive').innerHTML = html;
            attachEditFormListeners();
        });
    }

    function exportData(type) {
        const searchParams = new URLSearchParams(window.location.search);
        searchParams.append('tab', 'ipm');

        const formData = new FormData(filterForm);
        for (let pair of formData.entries()) {
            if (pair[1]) {
                searchParams.append(pair[0], pair[1]);
            }
        }

        Swal.fire({
            title: 'Exporting...',
            text: 'Please wait while we prepare your file',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const exportUrl = `{{ route('inventory.export', ':type') }}?${searchParams.toString()}`.replace(':type', type);
        window.location.href = exportUrl;

        setTimeout(() => {
            Swal.close();
        }, 2000);
    }
});
</script>
@endpush