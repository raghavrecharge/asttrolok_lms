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

            <h2 class="section-title mt-45">Activate autopay to avoid any interruption in your learning journey.</h2>
            @php
                $payment_type = $_REQUEST['payment_type'];
                $subscription_id = $_REQUEST['subscription_id'];
                $name = $_REQUEST['name'];
                $email = $_REQUEST['email'];
                $mobile = $_REQUEST['mobile'];
            @endphp

                <form id="paymentForm">
                    @csrf
                    <input type="text" name="name" id="customer_name"
                           value="{{ $name }}"
                           placeholder="Name" class="form-control mt-25" required>

                    <input type="text" name="email" id="customer_email"
                           value="{{$email}}"
                           placeholder="Email" class="form-control mt-25" required>

                    <input type="number" name="number" id="customer_number"
                           value="{{$mobile}}"
                           placeholder="Mobile" class="form-control mt-25 mb-25" required>

                    <button type="button" id="paymentSubmit" class="btn btn-primary">
                        Setup Subscription Autopay
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
        discount_id: {{ session('discountCouponId') ?? 0 }}
    };
showPaymentLoader();
    // Call unified payment handler
    initiatePayment('subscription' , {{ $subscription_id }} , userDetails);
});
</script>
@endpush