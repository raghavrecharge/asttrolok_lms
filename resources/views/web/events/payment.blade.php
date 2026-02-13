@extends('web.default2.layouts.app')

@section('title', 'Event Registration - ' . $event->title)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Event Header -->
                    <div class="text-center mb-4">
                        @if($event->image)
                            <img src="{{ asset($event->image) }}" alt="{{ $event->title }}" class="rounded mb-3" style="max-width: 200px; height: auto;">
                        @endif
                        <h3 class="mb-2">{{ $event->title }}</h3>
                        <p class="text-muted">{{ $event->description }}</p>
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            @if($event->category)
                                <span class="badge bg-info">{{ $event->category }}</span>
                            @endif
                            <span class="badge bg-success">{{ $event->registered_count }}/{{ $event->max_participants }} Registered</span>
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-calendar-alt text-primary me-3"></i>
                                <div>
                                    <small class="text-muted">Event Date</small>
                                    <h6 class="mb-0">{{ $event->event_date->format('M j, Y h:i A') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-clock text-warning me-3"></i>
                                <div>
                                    <small class="text-muted">Registration Deadline</small>
                                    <h6 class="mb-0">{{ $event->registration_deadline->format('M j, Y h:i A') }}</h6>
                                </div>
                            </div>
                        </div>
                        @if($event->location)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-3"></i>
                                <div>
                                    <small class="text-muted">Location</small>
                                    <h6 class="mb-0">{{ $event->location }}</h6>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-users text-success me-3"></i>
                                <div>
                                    <small class="text-muted">Available Slots</small>
                                    <h6 class="mb-0">{{ $event->remaining_slots }} left</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <div class="border-top pt-4">
                        <h5 class="mb-4">Complete Your Registration</h5>
                        
                        <form id="paymentForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Summary -->
                            <div class="bg-light rounded p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Registration Fee</h6>
                                        <small class="text-muted">{{ $event->title }}</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="mb-0 text-primary">₹{{ number_format($event->price, 2) }}</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="payButton">
                                    <i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($event->price, 2) }}
                                </button>
                                <p class="text-muted small mt-2">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secure payment powered by Razorpay
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Important Information -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Important Information</h6>
                        <ul class="mb-0 small">
                            <li>Registration will be confirmed only after successful payment</li>
                            <li>You will receive a confirmation email with event details</li>
                            <li>Registration is non-refundable unless event is cancelled</li>
                            <li>Please arrive 15 minutes before the event start time</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    const eventId = '{{ $event->id }}';
    const token = '{{ $paymentLink->link_token }}';

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable button
        payButton.disabled = true;
        payButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        // Get form data
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`/events/pay/${eventId}/${token}/process`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    email: formData.get('email'),
                    phone: formData.get('phone')
                })
            });

            const data = await response.json();

            if (data.success) {
                // Initialize Razorpay
                const options = {
                    key: data.razorpay_key,
                    amount: data.amount,
                    currency: data.currency,
                    name: data.name,
                    description: data.description,
                    image: '{{ asset('images/logo.png') }}',
                    order_id: data.order_id,
                    handler: function (response) {
                        // Verify payment
                        verifyPayment(response);
                    },
                    prefill: {
                        name: data.name,
                        email: data.email,
                        contact: data.phone
                    },
                    theme: {
                        color: '#3399cc'
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
            payButton.disabled = false;
            payButton.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($event->price, 2) }}';
        }
    });

    async function verifyPayment(response) {
        try {
            const verifyResponse = await fetch(`/events/pay/${eventId}/${token}/verify`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                })
            });

            const data = await verifyResponse.json();

            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert('Payment verification failed: ' + error.message);
            window.location.href = `/events/pay/${eventId}/${token}/failed`;
        }
    }
});
</script>
@endsection
