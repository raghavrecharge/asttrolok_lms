<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .dash-card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .big-stat { font-size: 2.5rem; font-weight: 700; }
        .stat-label { color: #6c757d; font-size: 0.9rem; }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Support Dashboard</h4>
        <a href="{{ route('admin.support.logs') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-list me-1"></i>Back to Logs
        </a>
    </div>

    {{-- Summary Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dash-card text-center">
                <div class="big-stat text-primary">{{ $totalTickets }}</div>
                <div class="stat-label">Total Tickets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dash-card text-center">
                <div class="big-stat text-warning">{{ $pendingTickets }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dash-card text-center">
                <div class="big-stat text-success">{{ $executedTickets }}</div>
                <div class="stat-label">Executed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dash-card text-center">
                <div class="big-stat text-danger">{{ $rejectedTickets }}</div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>

    {{-- Avg Resolution --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dash-card text-center">
                <div class="big-stat text-info">{{ $avgResolutionHours ? round($avgResolutionHours, 1) : 'N/A' }}</div>
                <div class="stat-label">Avg Resolution Time (hours)</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="dash-card">
                <h6 class="fw-bold mb-3">Top Handlers</h6>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Handler</th><th>Tickets Handled</th></tr></thead>
                    <tbody>
                        @forelse($topHandlers as $handler)
                            <tr>
                                <td>{{ $handler->supportHandler ? $handler->supportHandler->full_name : 'Unknown' }}</td>
                                <td><span class="badge bg-primary">{{ $handler->count }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted text-center">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row">
        <div class="col-md-4">
            <div class="dash-card">
                <h6 class="fw-bold mb-3">Status Distribution</h6>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dash-card">
                <h6 class="fw-bold mb-3">Scenario Distribution</h6>
                <div class="chart-container">
                    <canvas id="scenarioChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dash-card">
                <h6 class="fw-bold mb-3">Flow Type Distribution</h6>
                <div class="chart-container">
                    <canvas id="flowChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Tickets Chart --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="dash-card">
                <h6 class="fw-bold mb-3">Daily Tickets (Last 30 Days)</h6>
                <div style="height: 250px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const statusData = @json($statusDistribution);
    const scenarioData = @json($scenarioDistribution);
    const flowData = @json($flowDistribution);
    const dailyData = @json($dailyTickets);

    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];

    // Status Pie
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(s => s.replace('_',' ').toUpperCase()),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // Scenario Pie
    new Chart(document.getElementById('scenarioChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(scenarioData).map(s => s.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{
                data: Object.values(scenarioData),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } } }
    });

    // Flow Pie
    new Chart(document.getElementById('flowChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(flowData).map(s => s.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{
                data: Object.values(flowData),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // Daily Line
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: Object.keys(dailyData),
            datasets: [{
                label: 'Tickets Created',
                data: Object.values(dailyData),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78,115,223,0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
