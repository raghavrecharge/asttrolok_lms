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

        @if(!empty($subscription->razorpay_plan_id) && auth()->check())
        <div class="mt-15 mb-15 p-15" style="border: 1px solid #e0e0e0; border-radius: 8px; background: #f9f9f9;">
            <p class="font-14 font-weight-bold mb-10">Choose Payment Method:</p>
            <label style="display:block; padding:10px; margin-bottom:8px; border:1px solid #ddd; border-radius:6px; background:#fff; cursor:pointer;">
                <input type="radio" name="payment_mode" value="one_time" checked style="margin-right:8px;">
                <strong>Pay {{ handlePrice($subscription->getPrice()) }} for this month only</strong>
                <br><small style="color:#666; margin-left:24px;">Manual renewal each month</small>
            </label>
            <label style="display:block; padding:10px; border:1px solid #ddd; border-radius:6px; background:#fff; cursor:pointer;">
                <input type="radio" name="payment_mode" value="autopay" style="margin-right:8px;">
                <strong>Enable AutoPay — {{ handlePrice($subscription->getPrice()) }}/month</strong>
                <br><small style="color:#666; margin-left:24px;">Auto-deducted every month, cancel anytime</small>
            </label>
        </div>
        @endif

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
<script src="/js/subscription-payment.js"></script>
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

    // Check payment mode
    const paymentModeEl = document.querySelector('input[name="payment_mode"]:checked');
    const paymentMode = paymentModeEl ? paymentModeEl.value : 'one_time';

    if (paymentMode === 'autopay') {
        initiateAutoPaySubscription({{ $subscription->id }}, userDetails);
    } else {
        initiatePayment('subscription', {{ $subscription->id }}, userDetails);
    }
});
</script>
@endpush