@extends('layout.app')

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0">{{ $totalItems }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-box text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Value</h6>
                            <h3 class="mb-0">₱{{ number_format($totalValue, 2) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-dollar text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Items Added This Month</h6>
                            <h3 class="mb-0">{{ $itemsThisMonth }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-graph-up text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Divisions</h6>
                            <h3 class="mb-0">{{ $totalDivisions }}</h3>
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
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Items by Division</h5>
                    <canvas id="divisionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Monthly Acquisitions</h5>
                    <canvas id="acquisitionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row of Charts -->
    <div class="row g-3 mt-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Value Distribution by Classification</h5>
                    <canvas id="classificationChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Status Distribution</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Division Chart
    new Chart(document.getElementById('divisionChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($divisionData->pluck('division')) !!},
            datasets: [{
                label: 'Number of Items',
                data: {!! json_encode($divisionData->pluck('count')) !!},
                backgroundColor: ['#4DA9FF', '#FF6B6B', '#7BC950', '#FFC145', '#8E7CC3', '#007B83'],
            }]
        },
        options: {
            responsive: true,
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

    // Monthly Acquisitions Chart
    new Chart(document.getElementById('acquisitionChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyAcquisitions->pluck('month')) !!},
            datasets: [{
                label: 'Items Acquired',
                data: {!! json_encode($monthlyAcquisitions->pluck('count')) !!},
                borderColor: '#0d6efd',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
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
            scales: {
                y: {
                    beginAtZero: true
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
                }
            }
        }
    });
});
</script>
@endpush
@endsection
