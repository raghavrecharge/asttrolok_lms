<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ticket #</th>
                        <th>User</th>
                        <th>Scenario</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Flow</th>
                        <th>Handler</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="ticket-row">
                            <td>
                                <code class="text-primary">{{ $ticket->ticket_number }}</code>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $ticket->getRequesterName() }}</div>
                                <small class="text-muted">{{ $ticket->getRequesterEmail() }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $ticket->getScenarioLabel() }}</span>
                            </td>
                            <td>
                                @if($ticket->webinar)
                                    <span title="{{ $ticket->webinar->title }}">
                                        {{ \Illuminate\Support\Str::limit($ticket->webinar->title, 25) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-status bg-{{ $ticket->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->flow_type)
                                    <small>{{ $ticket->getFlowTypeLabel() }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->supportHandler)
                                    <small>{{ $ticket->supportHandler->full_name }}</small>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $ticket->created_at->format('d M Y') }}</small><br>
                                <small class="text-muted">{{ $ticket->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.support.logs.timeline', $ticket->ticket_number) }}" 
                                   class="btn btn-outline-primary btn-sm" title="View Timeline">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                No tickets found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tickets->hasPages())
        <div class="card-footer bg-white border-top-0 d-flex justify-content-center py-3">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
