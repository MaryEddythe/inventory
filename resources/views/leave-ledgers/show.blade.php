@extends('layout.app')
@section('title', 'Leave Ledger')

@section('content')
<div class="leave-ledger-workspace">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <a href="{{ route('leave-ledgers.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
        <a href="{{ route('leave-ledgers.edit', $employee) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil-square"></i> Edit
        </a>
    </div>

    <div class="ledger-folder-tabs" role="tablist" aria-label="Leave ledger tabs">
        <button type="button" class="ledger-folder-tab active" data-ledger-tab="ledger-sheet" role="tab" aria-selected="true">
            Ledger Sheet
        </button>
        <button type="button" class="ledger-folder-tab" data-ledger-tab="leave-balance-card" role="tab" aria-selected="false">
            Leave Balance Card
        </button>
    </div>

    <div class="ledger-folder-panel active" id="ledger-sheet" role="tabpanel">
        @include('leave-ledgers.ledger-sheet')
    </div>

    <div class="ledger-folder-panel" id="leave-balance-card" role="tabpanel">
        @include('leave-ledgers.leave-balance-card')
    </div>
</div>
@endsection

<style>
    .leave-ledger-workspace {
        color: #111827;
    }

    .ledger-folder-panel {
        display: none !important;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        padding: 1.25rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .ledger-folder-panel.active {
        display: block !important;
    }

    .ledger-meta-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 0.75rem;
        font-size: 0.9rem;
    }

    .ledger-meta-grid span,
    .balance-card-meta span {
        font-size: 0.72rem;
        color: #6b7280;
        letter-spacing: 0.04em;
    }

    .ledger-table {
        min-width: 980px;
        font-size: 0.82rem;
    }

    .ledger-table th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        vertical-align: middle;
    }

    .ledger-table tr.current-month td {
        background: #fff8e1;
    }

    .balance-card {
        max-width: 980px;
        margin: 0 auto;
        border: 1px solid #cbd5e1;
        padding: 1.25rem;
    }

    .balance-card-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .balance-card-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem 2rem;
        margin: 1rem 0;
    }

    .balance-card-values {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        text-align: center;
        border: 1px solid #cbd5e1;
        margin: 1rem 0;
    }

    .balance-card-values > div {
        padding: 1rem;
        border-right: 1px solid #cbd5e1;
    }

    .balance-card-values > div:last-child {
        border-right: 0;
    }

    .balance-card-values span {
        display: block;
        color: #475569;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .balance-card-values strong {
        font-size: 1.8rem;
    }

    .balance-card-note {
        font-size: 0.85rem;
        line-height: 1.6;
        color: #334155;
    }

    @media (max-width: 768px) {
        .ledger-meta-grid,
        .balance-card-meta,
        .balance-card-values {
            grid-template-columns: 1fr;
        }

        .balance-card-values > div {
            border-right: 0;
            border-bottom: 1px solid #cbd5e1;
        }

        .balance-card-values > div:last-child {
            border-bottom: 0;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ledger-folder-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.getAttribute('data-ledger-tab');

            document.querySelectorAll('.ledger-folder-tab').forEach(function (item) {
                item.classList.toggle('active', item === tab);
                item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
            });

            document.querySelectorAll('.ledger-folder-panel').forEach(function (panel) {
                panel.classList.toggle('active', panel.id === target);
            });
        });
    });
});
</script>
@endpush
