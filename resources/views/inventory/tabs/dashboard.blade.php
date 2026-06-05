@extends('layout.app')

@section('content')
<div class="container-fluid inventory-dashboard">
    <!-- Header Section -->
    <div class="dashboard-header mb-5">
        <div class="dashboard-hero d-flex justify-content-between align-items-start mb-4">
            <div>
                <span class="dashboard-eyebrow">Inventory Overview</span>
                <h1 class="dashboard-title"><i class="bi bi-speedometer2 me-2"></i>Inventory Dashboard</h1>
                <p class="dashboard-subtitle">Monitor your inventory metrics and division performance</p>
            </div>
            <div class="dashboard-actions d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="refresh-btn">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="bi bi-funnel me-1"></i><span id="current-filter-text">Filters</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dashboard-filter-menu">
                        <li class="px-3 py-2">
                            <small class="dashboard-filter-label d-block mb-2">Date Range</small>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="start_date" class="form-label small text-muted mb-1">Start</label>
                                    <input type="date" class="form-control form-control-sm" id="start_date">
                                </div>
                                <div class="col-6">
                                    <label for="end_date" class="form-label small text-muted mb-1">End</label>
                                    <input type="date" class="form-control form-control-sm" id="end_date">
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100 mt-3" id="apply-custom-range">
                                <i class="bi bi-check2 me-1"></i>Apply date range
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="px-3 py-2 border-top">
                            <small class="dashboard-filter-label d-block mb-2">Item Classification</small>
                            <div class="form-check">
                                <input class="form-check-input classification-filter" type="checkbox" id="filter-rpcsp" value="rpcsp">
                                <label class="form-check-label" for="filter-rpcsp">
                                    <span class="badge bg-success">RPCSP</span> Regular Supplies (≤ ₱49,999)
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input classification-filter" type="checkbox" id="filter-ppe" value="ppe">
                                <label class="form-check-label" for="filter-ppe">
                                    <span class="badge bg-info">PPE</span> Equipment (≥ ₱50,000)
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Key Metrics Summary -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="metric-card metric-card-primary">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <div class="metric-info">
                            <span class="metric-label">Total Items</span>
                            <h2 class="metric-value"><span class="count-up" data-target="{{ $totalItems }}" id="totalItemsCount">{{ $totalItems }}</span></h2>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <small class="text-muted">All items in inventory</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card metric-card-success">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="metric-info">
                            <span class="metric-label">RPCSP Value</span>
                            <h2 class="metric-value">₱<span class="count-up" data-target="{{ $rpcspValue }}" id="rpcspValueCount">{{ number_format($rpcspValue, 2) }}</span></h2>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <small class="text-muted">Regular supplies value</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card metric-card-info">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="metric-info">
                            <span class="metric-label">PPE Value</span>
                            <h2 class="metric-value">₱<span class="count-up" data-target="{{ $ppeValue }}" id="ppeValueCount">{{ number_format($ppeValue, 2) }}</span></h2>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <small class="text-muted">Equipment value</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card metric-card-warning">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="metric-info">
                            <span class="metric-label">Active Divisions</span>
                            <h2 class="metric-value"><span class="count-up" data-target="{{ $totalDivisions }}" id="totalDivisionsCount">{{ $totalDivisions }}</span></h2>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <small class="text-muted">Operating divisions</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    <!-- Division Summary Section -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-diagram-3 me-2"></i>Division Summary</h3>
                    <p class="section-subtitle">Click any division to see item breakdown by classification</p>
                </div>
                <div class="section-body">
                    <div class="division-summary-row" id="division-summary-cards">
                        @foreach($divisionData as $division)
                        <div class="division-summary-card">
                            <div class="division-card division-card-{{ str_replace(' ', '-', strtolower(trim($division->division))) }} cursor-pointer division-summary-trigger" data-division="{{ $division->division }}" data-breakdown="{{ base64_encode(json_encode($divisionBreakdown[$division->division] ?? [])) }}">
                                <div class="division-card-body">
                                    <div class="division-icon">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="division-content">
                                        <h3 class="division-count">{{ $division->count }}</h3>
                                        <p class="division-name">{{ $division->division }}</p>
                                    </div>
                                </div>
                                <div class="division-footer">
                                    <small>items assigned</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status and Condition Section -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-bookmark-check me-2"></i>Item Status</h3>
                    <p class="section-subtitle">Items by status category</p>
                </div>
                <div class="section-body">
                    <div class="status-container" id="status-cards">
                        @foreach($statusData as $status)
                        <div class="status-item status-{{ strtolower(str_replace(' ', '-', $status->status)) }}">
                            <div class="status-icon">
                                <i class="bi {{ strpos($status->status, 'Less than') !== false ? 'bi-calendar-check' : 'bi-calendar-x' }}"></i>
                            </div>
                            <div class="status-info">
                                <p class="status-label">{{ $status->status }}</p>
                                <h3 class="status-count">{{ $status->count }}</h3>
                            </div>
                            <div class="status-bar">
                                <div class="status-bar-fill"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-tools me-2"></i>Item Condition</h3>
                    <p class="section-subtitle">Items by condition status</p>
                </div>
                <div class="section-body">
                    <div class="condition-container" id="condition-cards">
                        @foreach($conditionData as $condition)
                        <div class="condition-item condition-{{ strtolower(str_replace(' ', '-', $condition->condition)) }}">
                            <div class="condition-icon">
                                <i class="bi {{ $condition->condition == 'Functional' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                            </div>
                            <div class="condition-info">
                                <p class="condition-label">{{ $condition->condition }}</p>
                                <h3 class="condition-count">{{ $condition->count }}</h3>
                            </div>
                            <div class="condition-indicator">
                                <span class="condition-badge">{{ $condition->condition == 'Functional' ? '✓' : '⚠' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Division Breakdown Modal -->
<div class="modal fade" id="divisionBreakdownModal" tabindex="-1" aria-labelledby="divisionBreakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="divisionBreakdownModalLabel">Division Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalBreakdownContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Helper function to decode base64
    function decodeBase64(str) {
        try {
            return JSON.parse(atob(str));
        } catch (e) {
            console.error('Failed to decode base64:', e);
            return null;
        }
    }

    // Global variable to store division breakdown data
    let divisionBreakdownData = {};

    // Handle division summary modal clicks
    document.addEventListener('DOMContentLoaded', function() {
        // Set up click handlers for summary cards
        attachDivisionSummaryClickHandlers();
    });

    function attachDivisionSummaryClickHandlers() {
        document.querySelectorAll('.division-summary-trigger').forEach(card => {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                const division = this.getAttribute('data-division');
                const encodedBreakdown = this.getAttribute('data-breakdown');
                const breakdown = decodeBase64(encodedBreakdown);
                
                if (!breakdown || Object.keys(breakdown).length === 0) {
                    console.warn('No breakdown data for division:', division);
                    return;
                }
                
                // Populate modal
                const modalTitle = document.querySelector('#divisionBreakdownModalLabel');
                const modalContent = document.querySelector('#modalBreakdownContent');
                
                modalTitle.textContent = `${division} - Item Breakdown`;
                
                let contentHtml = `
                    <div class="breakdown-modal-content">
                        <div class="breakdown-modal-summary">
                            <div class="breakdown-modal-total">
                                <span class="breakdown-modal-number">${(breakdown.Desktop ?? 0) + (breakdown.Laptop ?? 0) + (breakdown.Monitor ?? 0) + (breakdown.Printer ?? 0) + (breakdown.Scanner ?? 0) + (breakdown.Others ?? 0)}</span>
                                <span class="breakdown-modal-text">Total Items</span>
                            </div>
                        </div>
                        <div class="breakdown-modal-details">
                            <div class="breakdown-modal-item">
                                <span class="breakdown-modal-label">Desktop</span>
                                <span class="breakdown-modal-value">${breakdown.Desktop ?? 0}</span>
                            </div>
                            <div class="breakdown-modal-item">
                                <span class="breakdown-modal-label">Laptop</span>
                                <span class="breakdown-modal-value">${breakdown.Laptop ?? 0}</span>
                            </div>
                            <div class="breakdown-modal-item">
                                <span class="breakdown-modal-label">Monitor</span>
                                <span class="breakdown-modal-value">${breakdown.Monitor ?? 0}</span>
                            </div>
                            <div class="breakdown-modal-item">
                                <span class="breakdown-modal-label">Printer</span>
                                <span class="breakdown-modal-value">${breakdown.Printer ?? 0}</span>
                            </div>
                            <div class="breakdown-modal-item">
                                <span class="breakdown-modal-label">Scanner</span>
                                <span class="breakdown-modal-value">${breakdown.Scanner ?? 0}</span>
                            </div>
                            <div class="breakdown-modal-item">
                                <span class="breakdown-modal-label">Others</span>
                                <span class="breakdown-modal-value">${breakdown.Others ?? 0}</span>
                            </div>
                        </div>
                    </div>
                `;
                
                modalContent.innerHTML = contentHtml;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('divisionBreakdownModal'));
                modal.show();
            });
        });
    }

    // ── Helper: apply a metrics object to the dashboard DOM ───────────────────
    function applyMetricsToDom(metrics) {
        if (!metrics) return;

        const map = {
            totalItemsCount:     { value: metrics.totalItems,     currency: false },
            rpcspValueCount:     { value: metrics.rpcspValue,     currency: true  },
            ppeValueCount:       { value: metrics.ppeValue,       currency: true  },
            totalDivisionsCount: { value: metrics.totalDivisions, currency: false },
        };


        Object.entries(map).forEach(([id, cfg]) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.setAttribute('data-target', cfg.value);

            // No count-up animation: update instantly.
            if (cfg.currency) {
                el.textContent = Number(cfg.value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                el.textContent = Math.round(Number(cfg.value)).toLocaleString();
            }
        });
    }


    // Track active filters
    let activeFilter = 'none';
    let selectedClassifications = [];

    function getSelectedClassifications() {
        const rpcspChecked = document.getElementById('filter-rpcsp').checked;
        const ppeChecked = document.getElementById('filter-ppe').checked;
        const classifications = [];
        if (rpcspChecked) classifications.push('rpcsp');
        if (ppeChecked) classifications.push('ppe');
        return classifications;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // If another tab (e.g. Inventory) wrote fresh metrics into sessionStorage
        // while this tab was open, apply them immediately so the dashboard
        // reflects the latest data without needing a page reload.
        try {
            const cached = sessionStorage.getItem('inventoryMetrics');
            if (cached) {
                applyMetricsToDom(JSON.parse(cached));
            }
        } catch (e) { /* ignore */ }


        // Also react whenever the tab regains focus (user switches back to this tab
        // in the browser after adding items on the Inventory tab).
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState !== 'visible') return;
            try {
                const cached = sessionStorage.getItem('inventoryMetrics');
                if (cached) applyMetricsToDom(JSON.parse(cached));
            } catch (e) { /* ignore */ }
        });

        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        document.querySelectorAll('.filter-option').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filterType = this.getAttribute('data-filter');
                const filterText = this.textContent;
                activeFilter = filterType;
                applyFilter(filterType, filterText);
            });
        });

        // Add event listeners for classification filters
        document.querySelectorAll('.classification-filter').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                selectedClassifications = getSelectedClassifications();
                applyFilter(activeFilter, null, selectedClassifications);
            });
        });

        document.getElementById('apply-custom-range').addEventListener('click', function() {
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }

            if (startDate > endDate) {
                alert('Start date cannot be after end date.');
                return;
            }

            var filterDropdown = bootstrap.Dropdown.getOrCreateInstance(document.getElementById('filterDropdown'));
            filterDropdown.hide();

            document.getElementById('current-filter-text').textContent = `Custom: ${startDate} to ${endDate}`;

            activeFilter = 'custom';
            selectedClassifications = getSelectedClassifications();
            const classParams = selectedClassifications.length > 0 ? `&classifications=${selectedClassifications.join(',')}` : '';

            fetch(`/dashboard?filter=custom&date_from=${startDate}&date_to=${endDate}${classParams}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const totalItemsEl = document.getElementById('totalItemsCount');
                if (totalItemsEl) {
                    totalItemsEl.setAttribute('data-target', data.totalItems);
                    animateCountUp(totalItemsEl, data.totalItems, 0);
                }

                const rpcspValueEl = document.getElementById('rpcspValueCount');
                if (rpcspValueEl) {
                    rpcspValueEl.setAttribute('data-target', data.rpcspValue);
                    animateCountUp(rpcspValueEl, data.rpcspValue, 0);
                }

                const ppeValueEl = document.getElementById('ppeValueCount');
                if (ppeValueEl) {
                    ppeValueEl.setAttribute('data-target', data.ppeValue);
                    animateCountUp(ppeValueEl, data.ppeValue, 0);
                }

                const totalDivisionsEl = document.getElementById('totalDivisionsCount');
                if (totalDivisionsEl) {
                    totalDivisionsEl.setAttribute('data-target', data.totalDivisions);
                    animateCountUp(totalDivisionsEl, data.totalDivisions, 0);
                }

                updateCards('division-summary-cards', data.divisionData, 'division-summary', data.divisionBreakdown);
                updateCards('status-cards', data.statusData, 'status');
                updateCards('condition-cards', data.conditionData, 'condition');
            })
            .catch(error => {
                console.error('Error fetching dashboard data:', error);
                alert('Failed to apply custom date range filter. Check console for details.');
            });
        });
    });

    function animateCountUp(el, targetValue, duration = 1000) {
        const target = targetValue !== undefined ? parseFloat(targetValue) : parseFloat(el.getAttribute('data-target'));
        if (isNaN(target)) return;

        // Detect currency elements by ID — rpcspValueCount and ppeValueCount hold peso values
        const currencyIds = ['rpcspValueCount', 'ppeValueCount', 'totalValueCount'];
        const isCurrency = currencyIds.includes(el.id);

        // Always animate FROM 0 so the count-up is always visible.
        // (Parsing the existing formatted text as a "start" value caused start===target
        // when the page first loads, resulting in no visible animation.)
        const start = 0;

        // If duration is 0, set value immediately
        if (duration === 0) {
            if (isCurrency) {
                el.textContent = target.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                el.textContent = Math.round(target).toLocaleString();
            }
            return;
        }

        let startTime;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = timestamp - startTime;
            const ratio = Math.min(progress / duration, 1);
            const currentValue = start + (target - start) * ratio;

            if (isCurrency) {
                el.textContent = currentValue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                el.textContent = Math.round(currentValue).toLocaleString();
            }

            if (ratio < 1) {
                window.requestAnimationFrame(step);
            }
        }

        window.requestAnimationFrame(step);
    }

    // Color function removed as charts are replaced with cards

    // Charts removed as replaced with cards

    // Table functions removed as charts are replaced with cards

    function applyFilter(filterType, filterText, classifications = []) {
        if (filterText) {
            document.getElementById('current-filter-text').textContent = filterText === 'All Time (Clear Filter)' ? 'Filters' : filterText;
        }

        const classParams = classifications.length > 0 ? `&classifications=${classifications.join(',')}` : '';

        fetch(`/dashboard?filter=${filterType}${classParams}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
            .then(data => {
            const totalItemsEl = document.getElementById('totalItemsCount');
            totalItemsEl?.setAttribute('data-target', data.totalItems);
            if (totalItemsEl) animateCountUp(totalItemsEl, data.totalItems, 0);


            const rpcspValueEl = document.getElementById('rpcspValueCount');
            if (rpcspValueEl) {
                rpcspValueEl.setAttribute('data-target', data.rpcspValue);
                animateCountUp(rpcspValueEl, data.rpcspValue, 0);
            }

            const ppeValueEl = document.getElementById('ppeValueCount');
            if (ppeValueEl) {
                ppeValueEl.setAttribute('data-target', data.ppeValue);
                animateCountUp(ppeValueEl, data.ppeValue, 0);
            }

            const itemsThisMonthEl = document.getElementById('itemsThisMonthCount');
            if (itemsThisMonthEl) {
                itemsThisMonthEl.setAttribute('data-target', data.itemsThisMonth);
                animateCountUp(itemsThisMonthEl, data.itemsThisMonth, 0);
            }

            const totalDivisionsEl = document.getElementById('totalDivisionsCount');
            if (totalDivisionsEl) {
                totalDivisionsEl.setAttribute('data-target', data.totalDivisions);
                animateCountUp(totalDivisionsEl, data.totalDivisions, 0);
            }

            updateCards('division-summary-cards', data.divisionData, 'division-summary', data.divisionBreakdown);
            updateCards('status-cards', data.statusData, 'status');
            updateCards('condition-cards', data.conditionData, 'condition');
            divisionBreakdownData = data.divisionBreakdown;
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            alert('Failed to apply filter. Check console for details.');
        });
    }

    function updateCards(containerId, data, type, divisionBreakdown = null) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        data.forEach((item, index) => {
            let cardHtml;

            if (type === 'division-summary') {
                const divisionClass = item.division.replace(/\s+/g, '-').toLowerCase();
                const breakdown = divisionBreakdown && divisionBreakdown[item.division] ? divisionBreakdown[item.division] : {};
                const encodedBreakdown = btoa(JSON.stringify(breakdown));
                cardHtml = `
                    <div class="col-lg-3 col-md-6">
                        <div class="division-card division-card-${divisionClass} cursor-pointer division-summary-trigger" data-division="${item.division}" data-breakdown="${encodedBreakdown}">
                            <div class="division-card-body">
                                <div class="division-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="division-content">
                                    <h3 class="division-count">${item.count}</h3>
                                    <p class="division-name">${item.division}</p>
                                </div>
                            </div>
                            <div class="division-footer">
                                <small>items assigned</small>
                            </div>
                        </div>
                    </div>
                `;
            } else if (type === 'status') {
                const statusClass = item.status.replace(/\s+/g, '-').toLowerCase();
                const iconClass = item.status.includes('Less than') ? 'bi-calendar-check' : 'bi-calendar-x';
                const statusLabel = item.status;
                cardHtml = `
                    <div class="status-item status-${statusClass}">
                        <div class="status-icon">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div class="status-info">
                            <p class="status-label">${statusLabel}</p>
                            <h3 class="status-count">${item.count}</h3>
                        </div>
                        <div class="status-bar">
                            <div class="status-bar-fill"></div>
                        </div>
                    </div>
                `;
            } else if (type === 'condition') {
                const conditionClass = item.condition === 'Functional' ? 'condition-functional' : 'condition-non-functional';
                const iconClass = item.condition === 'Functional' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
                const badge = item.condition === 'Functional' ? '✓' : '⚠';
                cardHtml = `
                    <div class="condition-item ${conditionClass}">
                        <div class="condition-icon">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div class="condition-info">
                            <p class="condition-label">${item.condition}</p>
                            <h3 class="condition-count">${item.count}</h3>
                        </div>
                        <div class="condition-indicator">
                            <span class="condition-badge">${badge}</span>
                        </div>
                    </div>
                `;
            }

            container.insertAdjacentHTML('beforeend', cardHtml);
        });

        // Re-attach click handlers for division-summary cards
        if (type === 'division-summary') {
            attachDivisionSummaryClickHandlers();
        }
    }

</script>
@endpush
@endsection
