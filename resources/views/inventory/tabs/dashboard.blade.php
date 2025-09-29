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
                    <canvas id="divisionChart" style="height: 60px;"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('divisionTable')">Show Data Table</button>
                        <div id="divisionTable" class="mt-2" style="display: none;">
                            <table class="table table-sm table-bordered">
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
                    <canvas id="acquisitionChart" style="height: 200px;"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('acquisitionTable')">Show Data Table</button>
                        <div id="acquisitionTable" class="mt-2" style="display: none;">
                            <table class="table table-sm table-bordered">
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
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Value by Classification</h5>
                </div>
                <div class="card-body">
                    <canvas id="classificationChart" style="height: 200px;"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('classificationTable')">Show Data Table</button>
                        <div id="classificationTable" class="mt-2" style="display: none;">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Classification</th>
                                        <th>Value (₱)</th>
                                    </tr>
                                </thead>
                                <tbody id="classificationTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="height: 60px;"></canvas>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleDataTable('statusTable')">Show Data Table</button>
                        <div id="statusTable" class="mt-2" style="display: none;">
                            <table class="table table-sm table-bordered">
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
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Count-up animation
    function animateCountUp(el) {
        const target = parseFloat(el.getAttribute('data-target'));
        let count = 0;
        const increment = target / 100;
        const interval = setInterval(() => {
            count += increment;
            if (count >= target) {
                count = target;
                clearInterval(interval);
            }
            el.textContent = Math.round(count).toLocaleString();
        }, 10);
    }

    document.querySelectorAll('.count-up').forEach(el => animateCountUp(el));

    // Division Colors Map (based on division full names)
    function getDivisionColor(label) {
        const colorMap = {
            'Mine Management Division': '#007B83',
            'Mine Safety, Environment and Social Development Division': '#FF6B6B',
            'Geosciences Division': '#FFC145',
            'General Support Services': '#7BC950',
            'Office of the Regional Director': '#8E7CC3',
            'Finance and Administrative Division': '#4DA9FF',
            'Unknown Division': '#6c757d'
        };
        return colorMap[label] || '#6c757d'; // Default to gray if not found
    }

    // Division Chart (Pie)
    const divisionLabels = {!! json_encode($divisionData->pluck('division')) !!};
    const divisionDataCounts = {!! json_encode($divisionData->pluck('count')) !!};
    new Chart(document.getElementById('divisionChart'), {
        type: 'pie',
        data: {
            labels: divisionLabels,
            datasets: [{
                data: divisionDataCounts,
                backgroundColor: divisionLabels.map(label => getDivisionColor(label)),
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

    // Acquisition Chart (Line)
    new Chart(document.getElementById('acquisitionChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyAcquisitions->pluck('month')) !!},
            datasets: [{
                label: 'Items Acquired',
                data: {!! json_encode($monthlyAcquisitions->pluck('count')) !!},
                borderColor: '#007bff',
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

    // Classification Chart (Bar)
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

    // Status Chart (Doughnut)
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
    populateDataTable('divisionTableBody', divisionLabels, divisionDataCounts);
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

function applyFilter(filterType) {
    // Implement filter functionality via AJAX
    fetch(`/dashboard?filter=${filterType}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update summary cards
        document.querySelector('.count-up[data-target="{{ $totalItems }}"]').setAttribute('data-target', data.totalItems);
        document.querySelector('.count-up[data-target="{{ $totalValue }}"]').setAttribute('data-target', data.totalValue);
        document.querySelector('.count-up[data-target="{{ $itemsThisMonth }}"]').setAttribute('data-target', data.itemsThisMonth);
        document.querySelector('.count-up[data-target="{{ $totalDivisions }}"]').setAttribute('data-target', data.totalDivisions);
        document.querySelectorAll('.count-up').forEach(el => animateCountUp(el));

        // Update charts
        updateChart('divisionChart', data.divisionData.labels, data.divisionData.counts);
        updateChart('acquisitionChart', data.monthlyAcquisitions.labels, data.monthlyAcquisitions.counts);
        updateChart('classificationChart', data.classificationData.labels, data.classificationData.values);
        updateChart('statusChart', data.statusData.labels, data.statusData.counts);

        // Update data tables
        populateDataTable('divisionTableBody', data.divisionData.labels, data.divisionData.counts);
        populateDataTable('acquisitionTableBody', data.monthlyAcquisitions.labels, data.monthlyAcquisitions.counts);
        populateDataTable('classificationTableBody', data.classificationData.labels, data.classificationData.values);
        populateDataTable('statusTableBody', data.statusData.labels, data.statusData.counts);
    });
}

function updateChart(chartId, labels, data) {
    const chart = Chart.getChart(chartId);
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    if (chartId === 'divisionChart') {
        chart.data.datasets[0].backgroundColor = labels.map(label => getDivisionColor(label));
    }
    chart.update();
}
</script>
@endpush
@endsection