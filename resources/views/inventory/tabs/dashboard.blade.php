@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Inventory Dashboard</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="refresh-btn">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-1"></i><span id="current-filter-text">Filters</span>
                </button>
                <ul class="dropdown-menu">
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card summary-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Total number of items in inventory">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0"><span class="count-up" data-target="{{ $totalItems }}" id="totalItemsCount">{{ $totalItems }}</span></h3>
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
                            <h3 class="mb-0">₱<span class="count-up" data-target="{{ $totalValue }}" id="totalValueCount">{{ number_format($totalValue, 2) }}</span></h3>
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
                            <h3 class="mb-0"><span class="count-up" data-target="{{ $itemsThisMonth }}" id="itemsThisMonthCount">{{ $itemsThisMonth }}</span></h3>
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
                            <h3 class="mb-0"><span class="count-up" data-target="{{ $totalDivisions }}" id="totalDivisionsCount">{{ $totalDivisions }}</span></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-building text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    // Global variable to store chart instances
    const chartInstances = {};

    document.addEventListener('DOMContentLoaded', function() {
        // Initial Count-up animation
        document.querySelectorAll('.count-up').forEach(el => animateCountUp(el));

        // Initialize all charts
        initializeCharts();

        // Populate data tables
        populateDataTable('divisionTableBody', {!! json_encode($divisionData->pluck('division')) !!}, {!! json_encode($divisionData->pluck('count')) !!});
        populateDataTable('acquisitionTableBody', {!! json_encode($monthlyAcquisitions->pluck('month')) !!}, {!! json_encode($monthlyAcquisitions->pluck('count')) !!});
        populateDataTable('classificationTableBody', {!! json_encode($classificationData->pluck('classification')) !!}, {!! json_encode($classificationData->pluck('total_value')) !!}, 'currency');
        populateDataTable('statusTableBody', {!! json_encode($statusData->pluck('status')) !!}, {!! json_encode($statusData->pluck('count')) !!});

        // Event listeners
        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        // Filter functionality using event delegation for all filter options
        document.querySelectorAll('.filter-option').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filterType = this.getAttribute('data-filter');
                const filterText = this.textContent;
                applyFilter(filterType, filterText);
            });
        });

        document.getElementById('custom-range').addEventListener('click', function() {
            // Implement custom date range picker
            alert('Custom range picker not implemented yet');
        });
    });

    // Helper functions

    function animateCountUp(el, targetValue) {
        // Use the data-target attribute as a fallback if targetValue is not provided
        const target = targetValue !== undefined ? parseFloat(targetValue) : parseFloat(el.getAttribute('data-target'));
        const isCurrency = el.closest('#totalValueCount') !== null;
        let start = 0;
        
        // Try to get the current visible value to start the animation from
        const currentText = el.textContent.replace(/[^0-9.]/g, '');
        if (currentText && !isNaN(parseFloat(currentText))) {
            start = parseFloat(currentText);
        }

        const duration = 1000; // 1 second
        let startTime;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = timestamp - startTime;
            const ratio = Math.min(progress / duration, 1);
            const currentValue = start + (target - start) * ratio;

            if (isCurrency) {
                // For currency, format with 2 decimal places and commas
                el.textContent = currentValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                // For counts, round and format with commas
                el.textContent = Math.round(currentValue).toLocaleString();
            }

            if (ratio < 1) {
                window.requestAnimationFrame(step);
            }
        }

        window.requestAnimationFrame(step);
    }

    function getDivisionColor(label) {
        const colorMap = {
            'MMD': '#007B83',
            'MSESDD': '#FF6B6B',
            'GD': '#FFC145',
            'GSS': '#7BC950',
            'ORD': '#8E7CC3',
            'FAD': '#4DA9FF',
            'Unknown Division': '#6c757d'
        };
        return colorMap[label] || '#6c757d'; // Default to gray if not found
    }

    function initializeCharts() {
        // Division Chart (Pie)
        const divisionLabels = {!! json_encode($divisionData->pluck('division')) !!};
        const divisionDataCounts = {!! json_encode($divisionData->pluck('count')) !!};
        chartInstances['divisionChart'] = new Chart(document.getElementById('divisionChart'), {
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
        chartInstances['acquisitionChart'] = new Chart(document.getElementById('acquisitionChart'), {
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
                                return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value % 1 === 0) return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Classification Chart (Bar)
        chartInstances['classificationChart'] = new Chart(document.getElementById('classificationChart'), {
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
        const statusLabels = {!! json_encode($statusData->pluck('status')) !!};
        const statusDataCounts = {!! json_encode($statusData->pluck('count')) !!};
        chartInstances['statusChart'] = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusDataCounts,
                    backgroundColor: ['#28a745', '#ffc107'],
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
    }

    function populateDataTable(tableBodyId, labels, data, format = 'count') {
        const tableBody = document.getElementById(tableBodyId);
        tableBody.innerHTML = '';
        for (let i = 0; i < labels.length; i++) {
            let dataValue = data[i];
            if (format === 'currency') {
                dataValue = '₱' + parseFloat(data[i]).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                dataValue = parseInt(data[i]).toLocaleString();
            }
            const row = document.createElement('tr');
            row.innerHTML = `<td>${labels[i]}</td><td>${dataValue}</td>`;
            tableBody.appendChild(row);
        }
    }

    function toggleDataTable(tableId) {
        const table = document.getElementById(tableId);
        table.style.display = table.style.display === 'none' ? 'block' : 'none';
    }

    function applyFilter(filterType, filterText) {
        // Update the dropdown text to reflect the applied filter
        document.getElementById('current-filter-text').textContent = filterText === 'All Time (Clear Filter)' ? 'Filters' : filterText;
        
        // Start the fetch with the filter parameter
        fetch(`/dashboard?filter=${filterType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update summary cards
            // 1. Total Items
            const totalItemsEl = document.getElementById('totalItemsCount');
            totalItemsEl.setAttribute('data-target', data.totalItems);
            animateCountUp(totalItemsEl, data.totalItems);

            // 2. Total Value
            const totalValueEl = document.getElementById('totalValueCount');
            totalValueEl.setAttribute('data-target', data.totalValue);
            animateCountUp(totalValueEl, data.totalValue);

            // 3. Items Added This Month (Note: This card's data doesn't get filtered by time range in the controller, 
            // but we'll update it for completeness if the controller changes it)
            const itemsThisMonthEl = document.getElementById('itemsThisMonthCount');
            itemsThisMonthEl.setAttribute('data-target', data.itemsThisMonth);
            animateCountUp(itemsThisMonthEl, data.itemsThisMonth);

            // 4. Active Divisions
            const totalDivisionsEl = document.getElementById('totalDivisionsCount');
            totalDivisionsEl.setAttribute('data-target', data.totalDivisions);
            animateCountUp(totalDivisionsEl, data.totalDivisions);


            // Update charts
            updateChart('divisionChart', data.divisionData.labels, data.divisionData.counts);
            updateChart('acquisitionChart', data.monthlyAcquisitions.labels, data.monthlyAcquisitions.counts);
            updateChart('classificationChart', data.classificationData.labels, data.classificationData.values);
            // Note: Status chart data is currently *not* filtered in the controller, but updated for consistency
            updateChart('statusChart', data.statusData.labels, data.statusData.counts); 

            // Update data tables
            populateDataTable('divisionTableBody', data.divisionData.labels, data.divisionData.counts);
            populateDataTable('acquisitionTableBody', data.monthlyAcquisitions.labels, data.monthlyAcquisitions.counts);
            populateDataTable('classificationTableBody', data.classificationData.labels, data.classificationData.values, 'currency');
            populateDataTable('statusTableBody', data.statusData.labels, data.statusData.counts);

        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            alert('Failed to apply filter. Check console for details.');
        });
    }

    function updateChart(chartId, labels, data) {
        const chart = chartInstances[chartId];
        if (!chart) return; // Exit if chart is not initialized

        // Remove duplicates from labels
        labels = [...new Set(labels)];

        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        
        if (chartId === 'divisionChart') {
            chart.data.datasets[0].backgroundColor = labels.map(label => getDivisionColor(label));
        }

        // For the Acquisition Chart, the stepSize on the Y-axis might need re-evaluation
        if (chartId === 'acquisitionChart' && data.length > 0) {
            const maxCount = Math.max(...data);
            chart.options.scales.y.ticks.stepSize = maxCount <= 5 ? 1 : null; // Set stepSize to 1 if max is small
        }

        chart.update();
    }
</script>
@endpush
@endsection