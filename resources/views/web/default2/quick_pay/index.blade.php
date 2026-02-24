@extends('web.default2.layouts.app')

@push('styles_top')
<style>
    .quick-pay-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 30px;
    }
    .quick-pay-card h2 {
        font-size: 22px;
        font-weight: 700;
        color: #1f2937;
    }
    .course-info {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .course-info img {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }
    .schedule-preview {
        background: #f1f5f9;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .schedule-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }
    .schedule-row:last-child { border-bottom: none; }
    .schedule-row .paid { color: #16a34a; font-weight: 600; }
    .schedule-row .partial { color: #d97706; font-weight: 600; }
    .schedule-row .unpaid { color: #6b7280; }
    .amount-input-group {
        position: relative;
    }
    .amount-input-group .rupee-symbol {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        font-weight: 700;
        color: #374151;
    }
    .amount-input-group input {
        padding-left: 35px;
        font-size: 20px;
        font-weight: 700;
        height: 56px;
    }
    .payment-summary {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 12px 15px;
        margin-top: 15px;
        font-size: 13px;
        display: none;
    }
    .existing-progress {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div id="paymentLoader" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;text-align:center;padding-top:40vh;">
    <div class="spinner-border text-primary" role="status"></div>
    <h3 class="mt-10">Processing payment... Please do not close this page.</h3>
</div>

<div class="container pt-50 mt-10 pb-50">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="quick-pay-card">
                <h2 class="mb-20">Quick Pay</h2>

                {{-- Course Info --}}
                <div class="course-info">
                    @if($webinar->image_cover)
                        <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->image_cover }}" alt="{{ $webinar->title }}">
                    @endif
                    <div>
                        <div class="font-16 font-weight-bold text-dark-blue">{{ $webinar->title }}</div>
                        <div class="font-14 text-gray">Course Price: <span class="font-weight-bold text-dark-blue">{{ handlePrice($coursePrice) }}</span></div>
                    </div>
                </div>

                @if(!$existingPlan)
                    {{-- No existing installment plan — Quick Pay not available --}}
                    <div class="alert alert-warning text-center py-20">
                        <i class="fa fa-info-circle font-20 mb-10 d-block"></i>
                        <strong class="font-16">This course does not have an installment plan, therefore Quick Payment is not available.</strong>
                        <p class="mt-10 mb-0 font-13 text-gray">To use Quick Pay, you must first purchase this course through an installment plan.</p>
                    </div>
                @else
                    {{-- Existing installment plan progress --}}
                    <div class="existing-progress">
                        <div class="font-14 font-weight-bold text-dark-blue mb-10">
                            Your Installment Plan
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="font-12 text-gray">Total</div>
                                <div class="font-16 font-weight-bold">{{ handlePrice($existingPlan->total_amount) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray">Paid</div>
                                <div class="font-16 font-weight-bold text-primary">{{ handlePrice($totalPaid) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray">Remaining</div>
                                <div class="font-16 font-weight-bold text-danger">{{ handlePrice($remaining) }}</div>
                            </div>
                        </div>

                        @if($existingPlan->schedules)
                            <div class="mt-10">
                                @foreach($existingPlan->schedules->sortBy('sequence') as $schedule)
                                    <div class="schedule-row">
                                        <span>Installment {{ $schedule->sequence }}</span>
                                        <span>{{ handlePrice($schedule->amount_due) }}</span>
                                        <span class="{{ $schedule->status === 'paid' ? 'paid' : ($schedule->status === 'partial' ? 'partial' : 'unpaid') }}">
                                            @if($schedule->status === 'paid')
                                                Paid
                                            @elseif($schedule->status === 'partial')
                                                Partial ({{ handlePrice($schedule->amount_paid) }})
                                            @else
                                                {{ ucfirst($schedule->status) }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($remaining <= 0)
                        <div class="alert alert-success text-center">
                            <strong>All installments are fully paid!</strong>
                        </div>
                    @else
                        {{-- Amount Input --}}
                        <div class="form-group">
                            <label class="font-14 font-weight-bold text-dark-blue">Enter Amount to Pay</label>
                            <div class="amount-input-group">
                                <span class="rupee-symbol">&#8377;</span>
                                <input type="number" id="amount" class="form-control" min="1" max="{{ $remaining }}" placeholder="Enter amount" step="1">
                            </div>
                            <small class="text-gray">Remaining: {{ handlePrice($remaining) }}</small>
                        </div>

                        {{-- Payment Summary Preview --}}
                        <div class="payment-summary" id="paymentSummary">
                            <div id="summaryText"></div>
                        </div>

                        {{-- User Info + Pay --}}
                        <div id="Payment-Option" class="bg-gray200 mt-20 rounded-lg border p-15">
                            <div class="form-group">
                                <input name="name" type="text" id="customer_name" placeholder="Name"
                                       value="{{ auth()->check() ? auth()->user()->full_name : '' }}"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <input name="email" type="email" id="customer_email" placeholder="Email"
                                       value="{{ auth()->check() ? auth()->user()->email : '' }}"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <input name="number" type="text" id="customer_number" placeholder="Contact Number"
                                       value="{{ auth()->check() ? auth()->user()->mobile : '' }}"
                                       class="form-control">
                            </div>

                            <button type="button" id="paymentSubmit" class="btn btn-primary btn-block" disabled>
                                Pay Now
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="/assets/design_1/js/unified-payment.js"></script>
<script>
    var webinarId = {{ $webinar->id }};
    var remaining = {{ $remaining }};

    function showPaymentLoader() {
        var el = document.getElementById('paymentLoader');
        if (el) el.style.display = 'block';
    }

    // Amount input validation + summary
    var amountInput = document.getElementById('amount');
    var payBtn = document.getElementById('paymentSubmit');
    var summaryDiv = document.getElementById('paymentSummary');
    var summaryText = document.getElementById('summaryText');

    if (amountInput) {
        amountInput.addEventListener('input', function() {
            var val = parseFloat(this.value) || 0;

            if (val > 0 && val <= remaining) {
                payBtn.disabled = false;
                summaryDiv.style.display = 'block';
                summaryText.innerHTML = '<strong>You are paying ₹' + val.toLocaleString('en-IN') + '</strong>. The system will automatically adjust your installments based on this amount.';
            } else {
                payBtn.disabled = true;
                summaryDiv.style.display = 'none';
            }
        });
    }

    // Pay button
    if (payBtn) {
        payBtn.addEventListener('click', function(e) {
            e.preventDefault();

            var userDetails = {
                name: document.getElementById('customer_name').value,
                email: document.getElementById('customer_email').value,
                number: document.getElementById('customer_number').value,
                amount: document.getElementById('amount').value,
                installment_id: null
            };

            showPaymentLoader();
            initiatePayment('quick_pay', webinarId, userDetails);
        });
    }
</script>
@endpush
