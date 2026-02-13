@extends('web.default2.layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <!-- Failed Icon -->
                    <div class="mb-4">
                        <div class="failed-icon">
                            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                    </div>

                    <!-- Failed Message -->
                    <h3 class="mb-3">Payment Failed</h3>
                    <p class="text-muted mb-4">
                        We couldn't process your payment for <strong>{{ $event->title }}</strong>. 
                        Please try again or contact support if the issue persists.
                    </p>

                    <!-- Event Details -->
                    <div class="bg-light rounded p-3 mb-4">
                        <h6>Event Details</h6>
                        <table class="table table-sm mb-0">
                            <tr>
                                <td><strong>Event:</strong></td>
                                <td>{{ $event->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Amount:</strong></td>
                                <td>₹{{ number_format($event->price, 2) }}</td>
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
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="history.back()">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                    </div>

                    <!-- Help Information -->
                    <div class="alert alert-warning mt-4">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Troubleshooting</h6>
                        <ul class="mb-0 small text-start">
                            <li>Check your internet connection</li>
                            <li>Ensure your card has sufficient balance</li>
                            <li>Verify your card details are correct</li>
                            <li>Try using a different payment method</li>
                            <li>Contact your bank if the issue persists</li>
                        </ul>
                    </div>

                    <!-- Contact Support -->
                    <div class="text-center mt-3">
                        <p class="text-muted small">
                            Need help? Contact our support team at<br>
                            <a href="mailto:support@example.com">support@example.com</a> or<br>
                            <a href="tel:+919876543210">+91 98765 43210</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.failed-icon {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}
</style>
@endsection
