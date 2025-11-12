@extends('layout.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="dashboard-header mb-5">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="dashboard-title"><i class="bi bi-speedometer2 me-2"></i>Inventory Dashboard</h1>
                <p class="dashboard-subtitle">Monitor your inventory metrics and division performance</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="refresh-btn">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel me-1"></i><span id="current-filter-text">Filters</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item filter-option" href="#" data-filter="none">All Time (Clear Filter)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item filter-option" href="#" data-filter="today" id="filter-today">Today</a></li>
                        <li><a class="dropdown-item filter-option" href="#" data-filter="week" id="filter-week">This Week</a></li>
                        <li><a class="dropdown-item filter-option" href="#" data-filter="month" id="filter-month">This Month</a></li>
                        <li><a class="dropdown-item filter-option" href="#" data-filter="year" id="filter-year">This Year</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="custom-range">Custom Range</a></li>
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
                            <span class="metric-label">Total Value</span>
                            <h2 class="metric-value">₱<span class="count-up" data-target="{{ $totalValue }}" id="totalValueCount">{{ number_format($totalValue, 2) }}</span></h2>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <small class="text-muted">Total inventory value</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card metric-card-info">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="metric-info">
                            <span class="metric-label">Added Items</span>
                            <h2 class="metric-value"><span class="count-up" data-target="{{ $itemsThisMonth }}" id="itemsThisMonthCount">{{ $itemsThisMonth }}</span></h2>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <small class="text-muted">New items added</small>
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

    <!-- Division Summary Section -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-diagram-3 me-2"></i>Division Summary</h3>
                    <p class="section-subtitle">Item distribution across divisions</p>
                </div>
                <div class="section-body">
                    <div class="row g-3" id="division-summary-cards">
                        @foreach($divisionData as $division)
                        <div class="col-lg-3 col-md-6">
                            <div class="division-card division-card-{{ str_replace(' ', '-', strtolower(trim($division->division))) }}">
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
                        <div class="status-item status-{{ strtolower($status->status) }}">
                            <div class="status-icon">
                                <i class="bi {{ $status->status == 'NEW' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
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

    <!-- Division Breakdown Section -->
    <div class="row g-3">
        <div class="col-12">
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-list-columns-reverse me-2"></i>Division Breakdown by Classification</h3>
                    <p class="section-subtitle">Detailed item distribution across divisions and types</p>
                </div>
                <div class="section-body">
                    <div class="row g-3" id="division-breakdown-cards">
                        @foreach($divisionBreakdown as $division => $breakdown)
                        <div class="col-lg-4 col-md-6">
                            <div class="breakdown-card breakdown-card-{{ str_replace(' ', '-', strtolower(trim($division))) }}">
                                <div class="breakdown-header">
                                    <h4 class="breakdown-title">{{ $division }}</h4>
                                    <button class="btn-expand" type="button" data-bs-toggle="collapse" data-bs-target="#breakdown-{{ str_replace(' ', '-', $division) }}" aria-expanded="false">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="breakdown-summary">
                                    <div class="breakdown-total">
                                        <span class="breakdown-number">{{ array_sum($breakdown) }}</span>
                                        <span class="breakdown-text">Total Items</span>
                                    </div>
                                </div>
                                <div class="collapse" id="breakdown-{{ str_replace(' ', '-', $division) }}">
                                    <div class="breakdown-details">
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Desktop</span>
                                            <span class="breakdown-value">{{ $breakdown['Desktop'] ?? 0 }}</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Laptop</span>
                                            <span class="breakdown-value">{{ $breakdown['Laptop'] ?? 0 }}</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Monitor</span>
                                            <span class="breakdown-value">{{ $breakdown['Monitor'] ?? 0 }}</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Printer</span>
                                            <span class="breakdown-value">{{ $breakdown['Printer'] ?? 0 }}</span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span class="breakdown-label">Scanner</span>
                                            <span class="breakdown-value">{{ $breakdown['Scanner'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Range Modal -->
<div class="modal fade" id="customRangeModal" tabindex="-1" aria-labelledby="customRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customRangeModalLabel">Select Custom Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="apply-custom-range">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.count-up').forEach(el => animateCountUp(el));

        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        document.querySelectorAll('.filter-option').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filterType = this.getAttribute('data-filter');
                const filterText = this.textContent;
                applyFilter(filterType, filterText);
            });
        });

        document.getElementById('custom-range').addEventListener('click', function() {
            var customRangeModal = new bootstrap.Modal(document.getElementById('customRangeModal'));
            customRangeModal.show();
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

            var customRangeModalEl = document.getElementById('customRangeModal');
            var customRangeModal = bootstrap.Modal.getInstance(customRangeModalEl);
            customRangeModal.hide();

            document.getElementById('current-filter-text').textContent = `Custom: ${startDate} to ${endDate}`;

            fetch(`/dashboard?filter=custom&date_from=${startDate}&date_to=${endDate}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const totalItemsEl = document.getElementById('totalItemsCount');
                totalItemsEl.setAttribute('data-target', data.totalItems);
                animateCountUp(totalItemsEl, data.totalItems);

                const totalValueEl = document.getElementById('totalValueCount');
                totalValueEl.setAttribute('data-target', data.totalValue);
                animateCountUp(totalValueEl, data.totalValue);

                const itemsThisMonthEl = document.getElementById('itemsThisMonthCount');
                itemsThisMonthEl.setAttribute('data-target', data.itemsThisMonth);
                animateCountUp(itemsThisMonthEl, data.itemsThisMonth);

                const totalDivisionsEl = document.getElementById('totalDivisionsCount');
                totalDivisionsEl.setAttribute('data-target', data.totalDivisions);
                animateCountUp(totalDivisionsEl, data.totalDivisions);

                updateCards('division-summary-cards', data.divisionData, 'division-summary');
                updateCards('status-cards', data.statusData, 'status');
                updateCards('condition-cards', data.conditionData, 'condition');
                updateDivisionBreakdownCards(data.divisionBreakdown);
            })
            .catch(error => {
                console.error('Error fetching dashboard data:', error);
                alert('Failed to apply custom date range filter. Check console for details.');
            });
        });
    });

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

    function applyFilter(filterType, filterText) {
        document.getElementById('current-filter-text').textContent = filterText === 'All Time (Clear Filter)' ? 'Filters' : filterText;
        
        fetch(`/dashboard?filter=${filterType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const totalItemsEl = document.getElementById('totalItemsCount');
            totalItemsEl.setAttribute('data-target', data.totalItems);
            animateCountUp(totalItemsEl, data.totalItems);
            const totalValueEl = document.getElementById('totalValueCount');
            totalValueEl.setAttribute('data-target', data.totalValue);
            animateCountUp(totalValueEl, data.totalValue);

            const itemsThisMonthEl = document.getElementById('itemsThisMonthCount');
            itemsThisMonthEl.setAttribute('data-target', data.itemsThisMonth);
            animateCountUp(itemsThisMonthEl, data.itemsThisMonth);

            const totalDivisionsEl = document.getElementById('totalDivisionsCount');
            totalDivisionsEl.setAttribute('data-target', data.totalDivisions);
            animateCountUp(totalDivisionsEl, data.totalDivisions);

            updateCards('division-summary-cards', data.divisionData, 'division-summary');
            updateCards('status-cards', data.statusData, 'status');
            updateCards('condition-cards', data.conditionData, 'condition');
            updateDivisionBreakdownCards(data.divisionBreakdown);

            // Tables removed as charts are replaced with cards

        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            alert('Failed to apply filter. Check console for details.');
        });
    }

    function updateDivisionBreakdownCards(divisionBreakdown) {
        const container = document.getElementById('division-breakdown-cards');
        container.innerHTML = '';

        Object.keys(divisionBreakdown).forEach((division, index) => {
            const breakdown = divisionBreakdown[division];
            const total = Object.values(breakdown).reduce((sum, count) => sum + count, 0);
            const divisionId = division.replace(/\s+/g, '-');

            const divisionClass = division.replace(/\s+/g, '-').toLowerCase();
            const cardHtml = `
                <div class="col-lg-4 col-md-6">
                    <div class="breakdown-card breakdown-card-${divisionClass}">
                        <div class="breakdown-header">
                            <h4 class="breakdown-title">${division}</h4>
                            <button class="btn-expand" type="button" data-bs-toggle="collapse" data-bs-target="#breakdown-${divisionId}" aria-expanded="false">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                        <div class="breakdown-summary">
                            <div class="breakdown-total">
                                <span class="breakdown-number">${total}</span>
                                <span class="breakdown-text">Total Items</span>
                            </div>
                        </div>
                        <div class="collapse" id="breakdown-${divisionId}">
                            <div class="breakdown-details">
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Desktop</span>
                                    <span class="breakdown-value">${breakdown.Desktop ?? 0}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Laptop</span>
                                    <span class="breakdown-value">${breakdown.Laptop ?? 0}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Monitor</span>
                                    <span class="breakdown-value">${breakdown.Monitor ?? 0}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Printer</span>
                                    <span class="breakdown-value">${breakdown.Printer ?? 0}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Scanner</span>
                                    <span class="breakdown-value">${breakdown.Scanner ?? 0}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });
    }

    function updateCards(containerId, data, type) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        data.forEach((item, index) => {
            let cardHtml;

            if (type === 'division-summary') {
                const divisionClass = item.division.replace(/\s+/g, '-').toLowerCase();
                cardHtml = `
                    <div class="col-lg-3 col-md-6">
                        <div class="division-card division-card-${divisionClass}">
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
                const statusClass = item.status === 'NEW' ? 'status-new' : 'status-used';
                const iconClass = item.status === 'NEW' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
                cardHtml = `
                    <div class="status-item ${statusClass}">
                        <div class="status-icon">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div class="status-info">
                            <p class="status-label">${item.status}</p>
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
    }

    // Chart update functions removed as charts are replaced with cards
</script>
@endpush
@endsection
