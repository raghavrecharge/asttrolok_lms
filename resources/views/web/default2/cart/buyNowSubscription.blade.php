@extends('web.default2.layouts.app')

@section('content')
<section class="container mt-45">
    <h2 class="section-title">{{ $subscription->title }}</h2>

    <form id="paymentForm">
        @csrf
        <input type="text" name="name" id="customer_name"
               value="{{ auth()->check() ? auth()->user()->full_name : '' }}"
               placeholder="Name" class="form-control mt-25" required>

        <input type="email" name="email" id="customer_email"
               value="{{ auth()->check() ? auth()->user()->email : '' }}"
               placeholder="Email" class="form-control mt-25" required>

        <input type="number" name="number" id="customer_number"
               value="{{ auth()->check() ? auth()->user()->mobile : '' }}"
               placeholder="Mobile" class="form-control mt-25 mb-25" required>

        <button type="button" id="paymentSubmit" class="btn btn-primary">
            Pay {{ handlePrice($subscription->getPrice()) }}
        </button>
    </form>

    <center>
        <div class="loader mt-50" id="loader" style="display:none;">
            <img width="80px" height="80px" src="{{ asset('assets/default/img/loading.gif') }}">
            <h3>Processing payment...</h3>
        </div>
    </center>
</section>
@endsection

@push('scripts_bottom')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="/js/unified-payment.js"></script>
<script>
document.getElementById('paymentSubmit').addEventListener('click', function(e) {
    e.preventDefault();

    const userDetails = {
        name: document.getElementById('customer_name').value,
        email: document.getElementById('customer_email').value,
        number: document.getElementById('customer_number').value,
        discount_id: {{ session('discountCouponId') ?? 0 }}
    };

    initiatePayment('subscription', {{ $subscription->id }}, userDetails);
});
</script>
@endpush