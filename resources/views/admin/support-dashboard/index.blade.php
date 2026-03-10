@extends('admin.layouts.app')

@section('title')
    Dashboard Overview
@stop

@section('content')
<div class="dashboard-container">
    <!-- System Overview -->
    <div class="section-header">
        <h2>System Overview</h2>
    </div>

    <div class="system-overview">
        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fa fa-ticket"></i>
            </div>
            <div class="metric-content">
                <p class="metric-label">Open Tickets</p>
                <h3>{{ $metrics['open_tickets'] }}</h3>
                <span class="metric-change {{ $metrics['open_tickets_change'] > 0 ? 'positive' : 'negative' }}">
                    {{ $metrics['open_tickets_change'] > 0 ? '+' : '' }}{{ $metrics['open_tickets_change'] }}%
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="metric-content">
                <p class="metric-label">Resolved Today</p>
                <h3>{{ $metrics['resolved_today'] }}</h3>
                <span class="metric-change {{ $metrics['resolved_today_change'] > 0 ? 'positive' : 'negative' }}">
                    {{ $metrics['resolved_today_change'] > 0 ? '+' : '' }}{{ $metrics['resolved_today_change'] }}%
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fa fa-clock-o"></i>
            </div>
            <div class="metric-content">
                <p class="metric-label">Avg. Response Time</p>
                <h3>{{ $metrics['avg_response_time'] }}m</h3>
                <span class="metric-change {{ $metrics['avg_response_time_change'] > 0 ? 'negative' : 'positive' }}">
                    {{ $metrics['avg_response_time_change'] > 0 ? '+' : '' }}{{ $metrics['avg_response_time_change'] }}m
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fa fa-smile-o"></i>
            </div>
            <div class="metric-content">
                <p class="metric-label">Customer Satisfaction</p>
                <h3>{{ $metrics['customer_satisfaction'] }}/5</h3>
                <span class="metric-change positive">+{{ $metrics['customer_satisfaction_change'] }}</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Ticket Volume</h3>
                <p>Daily incoming vs resolved tickets</p>
            </div>
            <div class="chart-body">
                <canvas id="ticketVolumeChart" height="250"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Scenario Split</h3>
                <p>Ticket distribution by category</p>
            </div>
            <div class="chart-body">
                <canvas id="scenarioSplitChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Pending Tickets Table -->
    <div class="section-card">
        <div class="section-card-header d-flex justify-content-between align-items-center">
            <h3>Pending Tickets</h3>
            <div class="d-flex" style="gap:8px">
                <button class="btn btn-sm btn-primary" id="bulkAssignBtn">Bulk Assign</button>
                <select class="form-control input-sm" id="sortSelect">
                    <option value="newest">Time (Newest)</option>
                    <option value="oldest">Time (Oldest)</option>
                    <option value="priority">Priority</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Ticket ID</th>
                        <th>User</th>
                        <th>Issue</th>
                        <th>Scenario</th>
                        <th>Priority</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingTickets as $ticket)
                    <tr>
                        <td><input type="checkbox" class="ticket-cb" value="{{ $ticket->id }}"></td>
                        <td><span class="badge badge-info">#{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                        <td>{{ optional($ticket->user)->full_name ?? 'N/A' }}</td>
                        <td>{{ Str::limit($ticket->issue_description ?? '', 50) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $ticket->support_scenario ?? '')) }}</td>
                        <td>
                            <span class="badge badge-{{ ($ticket->priority ?? '') == 'high' ? 'danger' : (($ticket->priority ?? '') == 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($ticket->priority ?? 'N/A') }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::createFromTimestamp($ticket->created_at)->diffForHumans() }}</td>
                        <td>
                            <a href="{{ getAdminPanelUrl() }}/supports/{{ $ticket->id }}" class="btn btn-sm btn-success"><i class="fa fa-bolt"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center p-10">
            <small>Showing {{ $pendingTickets->count() }} of {{ $pendingTickets->total() }} pending tickets</small>
            {{ $pendingTickets->links() }}
        </div>
    </div>

    <!-- Ticket Workflow -->
    <div class="section-header mt-20">
        <h2>Ticket Workflow</h2>
    </div>

    <div class="workflow-columns">
        {{-- NEW --}}
        <div class="workflow-col">
            <div class="workflow-col-header">
                <span class="dot" style="background:#3498db"></span> NEW <span class="badge badge-secondary">{{ $ticketWorkflow['new']->count() }}</span>
            </div>
            @foreach($ticketWorkflow['new'] as $t)
            <div class="workflow-card">
                <div class="d-flex justify-content-between">
                    <strong>{{ Str::limit($t->issue_description ?? 'Ticket #'.$t->id, 30) }}</strong>
                    <span class="badge badge-{{ ($t->priority ?? '') == 'high' ? 'danger' : (($t->priority ?? '') == 'medium' ? 'warning' : 'success') }}">{{ ucfirst($t->priority ?? '') }}</span>
                </div>
                <small class="text-muted">{{ optional($t->user)->full_name ?? 'N/A' }} &middot; {{ \Carbon\Carbon::createFromTimestamp($t->created_at)->diffForHumans() }}</small>
            </div>
            @endforeach
        </div>

        {{-- IN PROGRESS --}}
        <div class="workflow-col">
            <div class="workflow-col-header">
                <span class="dot" style="background:#f39c12"></span> IN PROGRESS <span class="badge badge-secondary">{{ $ticketWorkflow['in_progress']->count() }}</span>
            </div>
            @foreach($ticketWorkflow['in_progress'] as $t)
            <div class="workflow-card">
                <div class="d-flex justify-content-between">
                    <strong>{{ Str::limit($t->issue_description ?? 'Ticket #'.$t->id, 30) }}</strong>
                    <span class="badge badge-{{ ($t->priority ?? '') == 'high' ? 'danger' : (($t->priority ?? '') == 'medium' ? 'warning' : 'success') }}">{{ ucfirst($t->priority ?? '') }}</span>
                </div>
                <small class="text-muted">{{ optional($t->user)->full_name ?? 'N/A' }} &middot; {{ \Carbon\Carbon::createFromTimestamp($t->updated_at)->diffForHumans() }}</small>
            </div>
            @endforeach
        </div>

        {{-- ON HOLD --}}
        <div class="workflow-col">
            <div class="workflow-col-header">
                <span class="dot" style="background:#95a5a6"></span> ON HOLD <span class="badge badge-secondary">{{ $ticketWorkflow['on_hold']->count() }}</span>
            </div>
            @foreach($ticketWorkflow['on_hold'] as $t)
            <div class="workflow-card">
                <div class="d-flex justify-content-between">
                    <strong>{{ Str::limit($t->issue_description ?? 'Ticket #'.$t->id, 30) }}</strong>
                    <span class="badge badge-{{ ($t->priority ?? '') == 'high' ? 'danger' : (($t->priority ?? '') == 'medium' ? 'warning' : 'success') }}">{{ ucfirst($t->priority ?? '') }}</span>
                </div>
                <small class="text-muted">{{ optional($t->user)->full_name ?? 'N/A' }} &middot; {{ \Carbon\Carbon::createFromTimestamp($t->updated_at)->diffForHumans() }}</small>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.system-overview { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.metric-card { background:#fff; border-radius:10px; padding:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 2px 6px rgba(0,0,0,.08); }
.metric-icon { width:50px; height:50px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.3rem; }
.metric-label { margin:0; color:#6c757d; font-size:.85rem; }
.metric-content h3 { margin:4px 0; font-size:1.6rem; font-weight:700; }
.metric-change { font-size:.8rem; font-weight:600; }
.metric-change.positive { color:#27ae60; }
.metric-change.negative { color:#e74c3c; }
.charts-section { display:grid; grid-template-columns:2fr 1fr; gap:1rem; margin-bottom:1.5rem; }
.chart-card { background:#fff; border-radius:10px; padding:1.25rem; box-shadow:0 2px 6px rgba(0,0,0,.08); }
.chart-header h3 { margin:0 0 .25rem; }
.chart-header p { margin:0 0 .75rem; color:#6c757d; font-size:.85rem; }
.section-card { background:#fff; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,.08); margin-bottom:1.5rem; overflow:hidden; }
.section-card-header { padding:1rem 1.25rem; border-bottom:1px solid #eee; }
.section-header h2 { font-size:1.35rem; font-weight:600; margin-bottom:.75rem; }
.workflow-columns { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
.workflow-col { background:#fff; border-radius:10px; padding:1rem; box-shadow:0 2px 6px rgba(0,0,0,.08); }
.workflow-col-header { font-weight:600; margin-bottom:.75rem; display:flex; align-items:center; gap:.5rem; }
.dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
.workflow-card { background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; padding:.75rem; margin-bottom:.5rem; }
.workflow-card:hover { border-color:#3498db; }
@media(max-width:768px){ .charts-section,.workflow-columns{ grid-template-columns:1fr; } }
</style>
@endsection

@push('scripts_bottom')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const volumeData = @json($ticketVolumeData);
const scenarioData = @json($scenarioSplitData);

new Chart(document.getElementById('ticketVolumeChart'), {
    type: 'line',
    data: {
        labels: volumeData.days,
        datasets: [
            { label:'Incoming', data:volumeData.incoming, borderColor:'#3498db', backgroundColor:'rgba(52,152,219,.1)', tension:.4, fill:true },
            { label:'Resolved', data:volumeData.resolved, borderColor:'#2ecc71', backgroundColor:'rgba(46,204,113,.1)', tension:.4, fill:true }
        ]
    },
    options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true } } }
});

new Chart(document.getElementById('scenarioSplitChart'), {
    type: 'bar',
    data: {
        labels: scenarioData.map(i=>i.scenario),
        datasets: [{ data:scenarioData.map(i=>i.percentage), backgroundColor:['#3498db','#e74c3c','#f39c12','#2ecc71','#9b59b6','#1abc9c','#34495e','#e67e22'] }]
    },
    options:{ responsive:true, maintainAspectRatio:false, indexAxis:'y', plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true, max:100 } } }
});

document.getElementById('selectAll')?.addEventListener('change', function(){ document.querySelectorAll('.ticket-cb').forEach(c=>c.checked=this.checked); });
</script>
@endpush
