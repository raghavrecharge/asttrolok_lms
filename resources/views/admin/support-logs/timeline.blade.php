<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Timeline - {{ $ticket->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .timeline { position: relative; padding: 20px 0; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-left: 70px;
            margin-bottom: 25px;
        }
        .timeline-dot {
            position: absolute;
            left: 21px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 3px #dee2e6;
            z-index: 1;
        }
        .timeline-dot.audit { background: #4e73df; box-shadow: 0 0 0 3px #4e73df55; }
        .timeline-dot.activity { background: #1cc88a; box-shadow: 0 0 0 3px #1cc88a55; }
        .timeline-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .ticket-header { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .meta-label { font-weight: 600; color: #6c757d; font-size: 0.85rem; }
        .meta-value { font-size: 0.95rem; }
    </style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <a href="{{ route('admin.support.logs') }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>
            </a>
            Ticket Timeline
        </h4>
        <span class="badge bg-{{ $ticket->getStatusBadgeClass() }} fs-6">
            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
        </span>
    </div>

    {{-- Ticket Info Card --}}
    <div class="ticket-header">
        <div class="row">
            <div class="col-md-3">
                <div class="meta-label">Ticket Number</div>
                <div class="meta-value"><code class="text-primary fs-5">{{ $ticket->ticket_number }}</code></div>
            </div>
            <div class="col-md-3">
                <div class="meta-label">Requester</div>
                <div class="meta-value">{{ $ticket->getRequesterName() }}</div>
                <small class="text-muted">{{ $ticket->getRequesterEmail() }}</small>
            </div>
            <div class="col-md-3">
                <div class="meta-label">Scenario</div>
                <div class="meta-value">{{ $ticket->getScenarioLabel() }}</div>
            </div>
            <div class="col-md-3">
                <div class="meta-label">Created</div>
                <div class="meta-value">{{ $ticket->created_at->format('d M Y, H:i') }}</div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <div class="meta-label">Course</div>
                <div class="meta-value">{{ $ticket->webinar ? $ticket->webinar->title : 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <div class="meta-label">Flow Type</div>
                <div class="meta-value">{{ $ticket->flow_type ? $ticket->getFlowTypeLabel() : 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <div class="meta-label">Handler</div>
                <div class="meta-value">{{ $ticket->supportHandler ? $ticket->supportHandler->full_name : 'Unassigned' }}</div>
            </div>
            <div class="col-md-3">
                <div class="meta-label">Executed At</div>
                <div class="meta-value">{{ $ticket->executed_at ? $ticket->executed_at->format('d M Y, H:i') : 'Not yet' }}</div>
            </div>
        </div>
        @if($ticket->description)
            <hr>
            <div class="meta-label">Description</div>
            <div class="meta-value mt-1">{{ $ticket->description }}</div>
        @endif
    </div>

    {{-- Timeline --}}
    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Activity Timeline ({{ $timeline->count() }} events)</h5>

    @if($timeline->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fas fa-clock fa-3x mb-3"></i>
            <p>No activity recorded for this ticket yet.</p>
        </div>
    @else
        <div class="timeline">
            @foreach($timeline as $event)
                <div class="timeline-item">
                    <div class="timeline-dot {{ $event['type'] }}"></div>
                    <div class="timeline-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                @if($event['type'] === 'audit')
                                    <span class="badge bg-primary me-1">Status Change</span>
                                    <strong>{{ $event['old_status'] }}</strong>
                                    <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                    <strong>{{ $event['new_status'] }}</strong>
                                    <span class="text-muted ms-2">by {{ $event['user_name'] }} ({{ $event['role'] }})</span>
                                @else
                                    <span class="badge bg-success me-1">Activity</span>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $event['action'])) }}</strong>
                                    @if(!empty($event['remarks']))
                                        <span class="text-muted ms-2">— {{ $event['remarks'] }}</span>
                                    @endif
                                @endif
                            </div>
                            <small class="text-muted text-nowrap">
                                @if($event['timestamp'])
                                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('d M Y, H:i:s') }}
                                @endif
                            </small>
                        </div>

                        @if($event['type'] === 'audit' && !empty($event['metadata']))
                            <div class="mt-2 p-2 bg-light rounded" style="font-size: 0.85rem;">
                                @foreach($event['metadata'] as $key => $value)
                                    @if($value)
                                        <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                            @if(is_array($value))
                                                <code>{{ json_encode($value) }}</code>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($event['ip_address']))
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-globe me-1"></i>{{ $event['ip_address'] }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
