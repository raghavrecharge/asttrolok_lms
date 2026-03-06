<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .stats-card { border-radius: 12px; padding: 20px; color: #fff; }
        .stats-card.pending { background: linear-gradient(135deg, #f6d365, #fda085); }
        .stats-card.verified { background: linear-gradient(135deg, #89f7fe, #66a6ff); }
        .stats-card.executed { background: linear-gradient(135deg, #a8e063, #56ab2f); }
        .stats-card.rejected { background: linear-gradient(135deg, #ff6b6b, #ee5a24); }
        .stats-card h3 { font-size: 2rem; margin: 0; }
        .stats-card p { margin: 0; opacity: 0.9; }
        .filter-card { border-radius: 12px; background: #fff; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .badge-status { font-size: 0.8rem; padding: 5px 10px; border-radius: 20px; }
        .ticket-row:hover { background: #f0f4ff; cursor: pointer; }
        .top-bar { background: #fff; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="top-bar d-flex justify-content-between align-items-center">
    <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Support Log Viewer</h4>
    <div>
        <a href="{{ route('admin.support.logs.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
            <i class="fas fa-chart-bar me-1"></i>Dashboard
        </a>
        <a href="{{ route('admin.support.logs.export', request()->all()) }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-download me-1"></i>Export CSV
        </a>
    </div>
</div>

<div class="container-fluid px-4">
    {{-- Status Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="stats-card pending">
                <h3>{{ $statusCounts['pending'] ?? 0 }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="stats-card verified">
                <h3>{{ ($statusCounts['verified'] ?? 0) + ($statusCounts['in_review'] ?? 0) }}</h3>
                <p>Verified / In Review</p>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="stats-card executed">
                <h3>{{ $statusCounts['executed'] ?? 0 }}</h3>
                <p>Executed</p>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="stats-card rejected">
                <h3>{{ $statusCounts['rejected'] ?? 0 }}</h3>
                <p>Rejected</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.support.logs') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Ticket #</label>
                    <input type="text" name="ticket" class="form-control form-control-sm" 
                           value="{{ request('ticket') }}" placeholder="AST-...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['pending','verified','in_review','executed','rejected','closed'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Scenario</label>
                    <select name="scenario" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($scenarios as $sc)
                            <option value="{{ $sc }}" {{ request('scenario') == $sc ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $sc)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">User Search</label>
                    <input type="text" name="user_search" class="form-control form-control-sm" 
                           value="{{ request('user_search') }}" placeholder="Name, email, phone">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.support.logs') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tickets Table --}}
    <div id="ticketsTableContainer">
        @include('admin.support-logs.partials.tickets-table', ['tickets' => $tickets])
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
