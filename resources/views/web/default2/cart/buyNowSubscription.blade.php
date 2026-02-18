@extends('web.default2.layouts.app')
<style>
  #paymentLoader {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
  }
  #paymentLoader .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    position: absolute;
    top: 50%;
    left: 44%;
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
</style>

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
    <div id="paymentLoader">
        <div class="spinner"></div>
        </div>
</section>
@endsection

@push('scripts_bottom')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="/assets/design_1/js/unified-payment.js"></script>
<script>

    const loaderEl = document.getElementById('paymentLoader');

    function showPaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'block';
    }

    function hidePaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'none';
    }
document.getElementById('paymentSubmit').addEventListener('click', function(e) {
    e.preventDefault();
    
    const userDetails = {
        name: document.getElementById('customer_name').value,
        email: document.getElementById('customer_email').value,
        number: document.getElementById('customer_number').value,
        discount_id: @json(session('discountCouponId'))
    };
     showPaymentLoader();

    // Call unified payment handler
    initiatePayment('subscription_one_time', {{ $subscription->id }}, userDetails);
});
</script>
@endpush