@extends('web.default2.layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="success-icon">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <h3 class="mb-3">Payment Successful!</h3>
                    <p class="text-muted mb-4">
                        Your registration for <strong>{{ $event->title }}</strong> has been confirmed.
                    </p>

                    <!-- Payment Details -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Payment ID</small>
                                <h6 class="mb-0">{{ $payment->razorpay_payment_id }}</h6>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Amount Paid</small>
                                <h6 class="mb-0 text-success">₹{{ number_format($payment->amount, 2) }}</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="text-start mb-4">
                        <h5>Event Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Event:</strong></td>
                                <td>{{ $event->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>{{ $event->event_date->format('M j, Y h:i A') }}</td>
                            </tr>
                            @if($event->location)
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td>{{ $event->location }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Registered Name:</strong></td>
                                <td>{{ $payment->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $payment->email }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                    </div>

                    <!-- Important Information -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Next Steps</h6>
                        <ul class="mb-0 small text-start">
                            <li>A confirmation email has been sent to {{ $payment->email }}</li>
                            <li>Please save this page for your records</li>
                            <li>Arrive 15 minutes before the event start time</li>
                            <li>Bring a valid ID proof for verification</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: scaleIn 0.5s ease-in-out;
}

@keyframes scaleIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@media print {
    .btn, .alert {
        display: none !important;
    }
}
</style>
@endsection
