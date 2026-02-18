@extends(getTemplate().'.layouts.app')

@push('styles_top')

<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-course-payment.css">
<style>
    .loader {
      //border: 16px solid #f3f3f3;
      //border-radius: 50%;
      //border-top: 16px solid #3498db;

      height: 80px;
      -webkit-animation: spin 2s linear infinite;
      animation: spin 2s linear infinite;
    }

    #loader {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

.disabled-page {
    pointer-events: none;
    opacity: 0.5;
}
    </style>
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
@endpush

@section('content')

    <section class="container mt-45">

        @php
            $payment_type = $_REQUEST['payment_type'];
            $subscription_id = $_REQUEST['subscription_id'];
            $name = $_REQUEST['name'];
            $email = $_REQUEST['email'];
            $mobile = $_REQUEST['mobile'];
        @endphp

        <h2 class="section-title">Activate autopay to avoid any interruption in your learning journey.</h2>
        <form action="/payments/payment-request" method="post" class=" mt-25"  id="razor-pay-request">
            {{ csrf_field() }}

           <input type="text" name="name" value="{{ $name }}" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="{{ $email }}" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" value="{{ $mobile }}" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >
             <h2 class="section-title d-none">Payment Option</h2>
             <br>

            <center><div class="loader mt-50" id="loader" style="dispay:none ">
    <img loading="lazy" width= '80px' height= '80px' src="{{ asset('public/assets/default/img/loading.gif')}}">
    <br>
    <h3>Please do not refresh or close the page while your payment is being processed...</h3>
    </div></center>

            <div class="d-flex align-items-center justify-content-between">

                <button type="button" id="paymentSubmit" class="btn btn-sm btn-primary" style="width:;">

                    Pay Now
                </button>
            </div>
        </form>
<div id="paymentLoader">
        <div class="spinner"></div>
        </div>
    </section>

@endsection

@push('scripts_bottom')
<script defer src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script defer src="/assets/design_1/js/unified-payment.js"></script>
<script defer>
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
    initiatePayment('subscription', {{ $subscription_id }}, userDetails);
});
</script>
@endpush
