@extends('web.default2.layouts.app')

@section('title', 'Invalid Link')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <!-- Invalid Icon -->
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Error Message -->
                    <h3 class="mb-3">{{ $message ?? 'Invalid Payment Link' }}</h3>
                    <p class="text-muted mb-4">
                        The payment link you've used is invalid or has expired. 
                        Please contact the event organizer for a valid payment link.
                    </p>

                    <!-- Possible Reasons -->
                    <div class="alert alert-info text-start">
                        <h6><i class="fas fa-info-circle me-2"></i>Possible Reasons:</h6>
                        <ul class="mb-0 small">
                            <li>The payment link has expired</li>
                            <li>The event registration is closed</li>
                            <li>The payment link has been disabled</li>
                            <li>The event has been cancelled</li>
                            <li>The link is malformed or incorrect</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                        <button class="btn btn-outline-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </button>
                    </div>

                    <!-- Contact Information -->
                    <div class="text-center mt-4">
                        <p class="text-muted small">
                            If you believe this is an error, please contact the event organizer<br>
                            or our support team for assistance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
