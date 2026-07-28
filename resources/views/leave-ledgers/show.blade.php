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

    <div class="ledger-balance-highlights">
        <div class="ledger-highlight-card vacation">
            <span>Current Vacation Leave</span>
            <strong>{{ number_format($balanceCard['vacation_balance'], 3) }}</strong>
            <small>as of {{ $balanceCard['month'] }} {{ $balanceCard['year'] }} month-end</small>
        </div>
        <div class="ledger-highlight-card sick">
            <span>Current Sick Leave</span>
            <strong>{{ number_format($balanceCard['sick_balance'], 3) }}</strong>
            <small>as of {{ $balanceCard['month'] }} {{ $balanceCard['year'] }} month-end</small>
        </div>
        <div class="ledger-highlight-card spl">
            <span>SPL</span>
            <strong>{{ number_format($balanceCard['spl_balance'], 0) }}</strong>
            <small>remaining this year</small>
        </div>
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

@push('styles')
<style>
    .leave-ledger-workspace {
        color: #111827;
    }

    .ledger-balance-highlights {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .ledger-highlight-card {
        border: 1px solid #e5e7eb;
        border-left-width: 6px;
        border-radius: 8px;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }

    .ledger-highlight-card span,
    .ledger-highlight-card small {
        display: block;
        color: #64748b;
        font-size: 0.78rem;
    }

    .ledger-highlight-card strong {
        display: block;
        font-size: 2rem;
        line-height: 1.1;
        margin: 0.25rem 0;
    }

    .ledger-highlight-card.vacation {
        border-left-color: #2563eb;
    }

    .ledger-highlight-card.sick {
        border-left-color: #16a34a;
    }

    .ledger-highlight-card.spl {
        border-left-color: #f59e0b;
    }

    .ledger-folder-tabs {
        display: flex;
        align-items: flex-end;
        gap: 0.25rem;
        padding-left: 0.75rem;
        margin-bottom: -1px;
    }

    .ledger-folder-tab {
        border: 1px solid #d1d5db;
        border-bottom: 0;
        border-radius: 8px 8px 0 0;
        background: #eef2f7;
        color: #475569;
        padding: 0.75rem 1.25rem;
        font-weight: 700;
    }

    .ledger-folder-tab.active {
        background: #fff;
        color: #0f172a;
        position: relative;
        z-index: 2;
    }

    .ledger-folder-panel {
        display: none;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        padding: 1.25rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .ledger-folder-panel.active {
        display: block;
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
        .ledger-balance-highlights,
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
@endpush

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
