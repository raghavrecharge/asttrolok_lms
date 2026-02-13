@extends('admin.layouts.app')

@section('title', 'Event Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Event Details</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Events
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-4">
                            @if($event->image)
                                <img src="{{ asset($event->image) }}" alt="{{ $event->title }}" class="rounded me-3" style="width: 120px; height: 120px; object-fit: cover;">
                            @endif
                            <div class="flex-grow-1">
                                <h4 class="mb-2">{{ $event->title }}</h4>
                                <p class="text-muted mb-2">{{ $event->description }}</p>
                                <div class="d-flex flex-wrap gap-2">
                                    @switch($event->status)
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                        @case('closed')
                                            <span class="badge bg-danger">Closed</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-secondary">Completed</span>
                                            @break
                                        @default
                                            <span class="badge bg-warning">{{ $event->status }}</span>
                                    @endswitch
                                    @if($event->category)
                                        <span class="badge bg-info">{{ $event->category }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Event Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Price:</strong></td>
                                        <td>₹{{ number_format($event->price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Max Participants:</strong></td>
                                        <td>{{ $event->max_participants }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registered:</strong></td>
                                        <td>{{ $event->registered_count }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Remaining Slots:</strong></td>
                                        <td>{{ $event->remaining_slots }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Event Date:</strong></td>
                                        <td>{{ $event->event_date->format('M j, Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registration Deadline:</strong></td>
                                        <td>{{ $event->registration_deadline->format('M j, Y h:i A') }}</td>
                                    </tr>
                                    @if($event->location)
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td>{{ $event->location }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Financial Summary</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Total Revenue:</strong></td>
                                        <td>₹{{ number_format($event->total_revenue, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Potential Revenue:</strong></td>
                                        <td>₹{{ number_format($event->price * $event->max_participants, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registration Rate:</strong></td>
                                        <td>{{ $event->max_participants > 0 ? round(($event->registered_count / $event->max_participants) * 100, 1) : 0 }}%</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Link Section -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Link</h5>
                    </div>
                    <div class="card-body">
                        @if($event->paymentLink)
                            <div class="mb-3">
                                <label class="form-label">Payment URL:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $event->paymentLink->payment_link }}" readonly id="paymentLink">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyPaymentLink()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-text">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-{{ $event->paymentLink->isActive() ? 'success' : 'danger' }}">
                                            {{ $event->paymentLink->formatted_status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-text">
                                        <strong>Clicks:</strong> {{ $event->paymentLink->click_count }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-text">
                                        <strong>Expires:</strong> {{ $event->paymentLink->expires_at->format('M j, Y h:i A') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('admin.events.regenerate-link', $event->id) }}" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure? This will generate a new payment link.')">
                                        <i class="fas fa-sync"></i> Regenerate
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No payment link generated. <a href="{{ route('admin.events.regenerate-link', $event->id) }}" class="alert-link">Generate Payment Link</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Event
                            </a>
                            <a href="{{ route('admin.events.toggle-status', $event->id) }}" class="btn btn-{{ $event->status === 'active' ? 'danger' : 'success' }}">
                                <i class="fas fa-power-off me-2"></i>{{ $event->status === 'active' ? 'Close Event' : 'Activate Event' }}
                            </a>
                            <a href="{{ route('admin.events.regenerate-link', $event->id) }}" class="btn btn-warning">
                                <i class="fas fa-link me-2"></i>Regenerate Link
                            </a>
                            <a href="{{ route('admin.events.delete', $event->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                                <i class="fas fa-trash me-2"></i>Delete Event
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary">{{ $event->registered_count }}</h4>
                                <small class="text-muted">Registered</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">₹{{ number_format($event->total_revenue, 0) }}</h4>
                                <small class="text-muted">Revenue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Payments</h5>
                    </div>
                    <div class="card-body">
                        @if($event->payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($event->payments->take(10) as $payment)
                                            <tr>
                                                <td>{{ $payment->id }}</td>
                                                <td>{{ $payment->name }}</td>
                                                <td>{{ $payment->email }}</td>
                                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $payment->status === 'paid' ? 'success' : 'warning' }}">
                                                        {{ $payment->formatted_status }}
                                                    </span>
                                                </td>
                                                <td>{{ $payment->created_at->format('M j, Y h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($event->payments->count() > 10)
                                <div class="text-center mt-3">
                                    <small class="text-muted">Showing 10 of {{ $event->payments->count() }} payments</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <h5>No Payments Yet</h5>
                                <p class="text-muted">Payments will appear here once participants start registering.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyPaymentLink() {
    const paymentLink = document.getElementById('paymentLink');
    paymentLink.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endpush
