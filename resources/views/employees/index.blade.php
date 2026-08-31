@extends('layout.app')
@section('title', 'Employees')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4 mb-3" data-page="employees-index">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Employees</h1>

        <div class="d-flex flex-wrap gap-3 justify-content-end align-items-center w-100">
            <form class="d-flex gap-2 align-items-center" method="GET" action="{{ route('employees.index') }}">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    style="min-width: 280px;"
                    placeholder="Search employees (name, department, position, folder)"
                    aria-label="Search employees"
                >
                @if(request('search'))
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary" title="Clear">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>

            <a href="{{ route('employees.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle"></i> Add Employee
            </a>
        </div>
    </div>


    <div id="employeesResultsWrap">
        @include('employees.partials.employee-table', ['employees' => $employees])
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge-division {
        background-color: #6c757d;
        color: #fff;
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 0.375rem;
    }
    .badge-division-MMD    { background-color: #0d6efd; color: #fff; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }
    .badge-division-MSESDD { background-color: #6610f2; color: #fff; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }
    .badge-division-GD     { background-color: #198754; color: #fff; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }
    .badge-division-ORD    { background-color: #fd7e14; color: #fff; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }
    .badge-division-FAD    { background-color: #dc3545; color: #fff; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }
    .badge-division-COA    { background-color: #ffc107; color: #000; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }

</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        const resultsWrap = document.getElementById('employeesResultsWrap');

        if (searchInput && resultsWrap) {
            let debounceTimer = null;
            let activeRequest = null;

            const loadResults = (url) => {
                if (activeRequest) activeRequest.abort();
                activeRequest = new AbortController();

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    signal: activeRequest.signal,
                })
                    .then((res) => {
                        if (!res.ok) throw new Error('Request failed');
                        return res.json();
                    })
                    .then((data) => {
                        if (data.html) {
                            resultsWrap.innerHTML = data.html;
                            const clean = new URL(url, window.location.origin);
                            history.replaceState(null, '', clean.pathname + clean.search);
                        }
                    })
                    .catch(() => {
                        // Silently ignore aborted/errored requests.
                    });
            };

            const applySearch = () => {
                const q = searchInput.value.trim();
                const url = new URL(window.location.href);
                if (q) url.searchParams.set('search', q);
                else url.searchParams.delete('search');
                url.searchParams.delete('page');
                loadResults(url.toString());
            };

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(applySearch, 350);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    applySearch();
                }
            });

            // AJAX pagination inside the results region (no full reload).
            resultsWrap.addEventListener('click', function (e) {
                const link = e.target.closest('a[href]');
                if (!link) return;
                const href = link.getAttribute('href') || '';
                if (!/([?&])page=\d+/.test(href)) return;

                e.preventDefault();
                const url = new URL(href, window.location.origin);
                const q = searchInput.value.trim();
                if (q) url.searchParams.set('search', q);
                else url.searchParams.delete('search');
                loadResults(url.toString());
            });
        }

        // Delete buttons keep working after AJAX re-renders via delegation.
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.employee-delete-btn');
            if (!btn) return;
            e.preventDefault();

            const name = btn.getAttribute('data-employee-name') || 'this employee';
            const form = btn.closest('form.employee-delete-form');

            Swal.fire({
                title: 'Delete Employee?',
                text: `Are you sure you want to deactivate ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, deactivate',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed && form) form.submit();
            });
        });
    });
</script>
@endpush
