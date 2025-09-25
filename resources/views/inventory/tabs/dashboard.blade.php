@extends('layout.app')

@section('content')
<div class="container-fluid">
    <!-- Header with Controls -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Inventory Dashboard</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="refresh-btn">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-1"></i>Filters
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="filter-today">Today</a></li>
                    <li><a class="dropdown-item" href="#" id="filter-week">This Week</a></li>
                    <li><a class="dropdown-item" href="#" id="filter-month">This Month</a></li>
                    <li><a class="dropdown-item" href="#" id="filter-year">This Year</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="custom-range">Custom Range</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card summary-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Total number of items in inventory">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0 count-up" data-target="{{ $totalItems }}">{{ $totalItems }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-box text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Total monetary value of all items">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Value</h6>
                            <h3 class="mb-0">₱<span class="count-up" data-target="{{ $totalValue }}">{{ number_format($totalValue, 2) }}</span></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-cash text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Items added in the current month">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Items Added This Month</h6>
                            <h3 class="mb-0 count-up" data-target="{{ $itemsThisMonth }}">{{ $itemsThisMonth }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-graph-up text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Number of active divisions">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Divisions</h6>
                            <h3 class="mb-0 count-up" data-target="{{ $totalDivisions }}">{{ $totalDivisions }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-building text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Items by Division</h5>
                </div>
                <div class="card-body">
                    <canvas id="divisionChart"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('divisionTable')">Show Data Table</button>
                        <div id="divisionTable" class="mt-2" style="display: none;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Division</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody id="divisionTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Monthly Acquisitions</h5>
                </div>
                <div class="card-body">
                    <canvas id="acquisitionChart"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('acquisitionTable')">Show Data Table</button>
                        <div id="acquisitionTable" class="mt-2" style="display: none;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody id="acquisitionTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row of Charts -->
    <div class="row g-3 mt-3">
        <div class="col-md-8">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Value Distribution by Classification</h5>
                </div>
                <div class="card-body">
                    <canvas id="classificationChart"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('classificationTable')">Show Data Table</button>
                        <div id="classificationTable" class="mt-2" style="display: none;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Classification</th>
                                        <th>Total Value</th>
                                    </tr>
                                </thead>
                                <tbody id="classificationTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('statusTable')">Show Data Table</button>
                        <div id="statusTable" class="mt-2" style="display: none;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody id="statusTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Indicators -->
    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Storage Utilization</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                            75% Used
                        </div>
                    </div>
                    <small class="text-muted">Total capacity: 1000 items</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monthly Goal Progress</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                            60% Complete
                        </div>
                    </div>
                    <small class="text-muted">Goal: 50 new items this month</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Count-up animation for summary cards
    function animateCountUp() {
        const counters = document.querySelectorAll('.count-up');
        counters.forEach(counter => {
            const target = parseFloat(counter.getAttribute('data-target'));
            const text = counter.textContent;
            const isCurrency = text.includes('₱');
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                if (isCurrency) {
                    counter.textContent = '₱' + current.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 16);
        });
    }

    // Call count-up animation
    animateCountUp();

    // Charts with enhanced options
    const divisionLabels = {!! json_encode($divisionData->pluck('division')) !!};
    const divisionData = {!! json_encode($divisionData->pluck('count')) !!};

    // Division Chart
    new Chart(document.getElementById('divisionChart'), {
        type: 'bar',
        data: {
            labels: divisionLabels,
            datasets: [{
                label: 'Number of Items',
                data: divisionData,
                backgroundColor: ['#4DA9FF', '#FF6B6B', '#7BC950', '#FFC145', '#8E7CC3', '#007B83'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    drillDown('division', divisionLabels[index]);
                }
            }
        }
    });

    // Monthly Acquisitions Chart
    new Chart(document.getElementById('acquisitionChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyAcquisitions->pluck('month')) !!},
            datasets: [{
                label: 'Items Acquired',
                data: {!! json_encode($monthlyAcquisitions->pluck('count')) !!},
                borderColor: '#0d6efd',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Classification Chart
    new Chart(document.getElementById('classificationChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($classificationData->pluck('classification')) !!},
            datasets: [{
                label: 'Total Value (₱)',
                data: {!! json_encode($classificationData->pluck('total_value')) !!},
                backgroundColor: '#800000',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusData->pluck('status')) !!},
            datasets: [{
                data: {!! json_encode($statusData->pluck('count')) !!},
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Populate data tables
    populateDataTable('divisionTableBody', divisionLabels, divisionData);
    populateDataTable('acquisitionTableBody', {!! json_encode($monthlyAcquisitions->pluck('month')) !!}, {!! json_encode($monthlyAcquisitions->pluck('count')) !!});
    populateDataTable('classificationTableBody', {!! json_encode($classificationData->pluck('classification')) !!}, {!! json_encode($classificationData->pluck('total_value')) !!});
    populateDataTable('statusTableBody', {!! json_encode($statusData->pluck('status')) !!}, {!! json_encode($statusData->pluck('count')) !!});

    // Event listeners
    document.getElementById('refresh-btn').addEventListener('click', function() {
        location.reload();
    });

    // Filter functionality
    document.getElementById('filter-today').addEventListener('click', function() {
        applyFilter('today');
    });

    document.getElementById('filter-week').addEventListener('click', function() {
        applyFilter('week');
    });

    document.getElementById('filter-month').addEventListener('click', function() {
        applyFilter('month');
    });

    document.getElementById('filter-year').addEventListener('click', function() {
        applyFilter('year');
    });

    document.getElementById('custom-range').addEventListener('click', function() {
        // Implement custom date range picker
        alert('Custom range picker not implemented yet');
    });

    // View options
    document.getElementById('view-compact').addEventListener('click', function() {
        document.body.classList.add('compact-view');
    });

    document.getElementById('view-detailed').addEventListener('click', function() {
        document.body.classList.remove('compact-view');
    });
});

// Helper functions
function populateDataTable(tableBodyId, labels, data) {
    const tableBody = document.getElementById(tableBodyId);
    tableBody.innerHTML = '';
    for (let i = 0; i < labels.length; i++) {
        const row = document.createElement('tr');
        row.innerHTML = `<td>${labels[i]}</td><td>${data[i]}</td>`;
        tableBody.appendChild(row);
    }
}

function toggleDataTable(tableId) {
    const table = document.getElementById(tableId);
    table.style.display = table.style.display === 'none' ? 'block' : 'none';
}

function drillDown(chartType, label) {
    // Implement drill-down functionality
    alert(`Drilling down into ${chartType}: ${label}`);
}

function applyFilter(filterType) {
    // Implement filter functionality
    console.log('Applying filter:', filterType);
}
</script>
@endpush
@endsection
