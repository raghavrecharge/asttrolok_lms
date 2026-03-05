<style>
    .available-times label{
        color: #161716 !important;
    font-size: 0.875rem !important;
    font-weight: 400 !important;
    }
.available-times:hover label {
    color: #ffffff !important;
}

.date-active{
    background-color: #43d477 !important;
    font-weight: 600 !important;
    color: white !important;
}
.date-active span{
    font-weight: 600 !important;
    color: white !important;
}
.date1:hover{
     background-color: #43d477;
    color: white !important;
    font-weight: 600;
}

.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

  .pick-a-time .available-times label, .pick-a-time .meeting-type-reserve label {
    cursor: pointer;
    padding: 8px  !important;
    border-radius: 25px !important;
    border: solid 1px #cfcece !important;
    color: var(--primary);
    font-size: 0.875rem;
}
.datepicker-plot-area .datepicker-day-view .table-days td.today span {

    color: #1000ff;
    border-radius: 50px;
    border: 0;
    text-shadow: none;
}
.inline-reservation-calender .datepicker-plot-area .datepicker-day-view .table-days td.disabled span {
    background-color: #ffffff;
}
.datepicker-plot-area .datepicker-day-view .table-days td span.other-month {
    color: #fff;
    border: none;
    text-shadow: none;
}
.inline-reservation-calender .datepicker-plot-area .datepicker-navigator .pwt-btn {
    color: #6a6a6a;
}
.pwt-btn-switch{
    color: #32ba7c !important;
    font-size: 1rem;
}
.datepicker-plot-area {
    box-shadow: 0 2px 4px rgb(0 0 0 / 6%);
    width: 455px;
}
.inline-reservation-calender .datepicker-plot-area {
    background-color: #ffffff00;
}
.inline-reservation-calender .datepicker-plot-area .datepicker-day-view .table-days td.selected span {
    background-color: var(--primary);
    border-radius: 50px;
    text-shadow: none;
    color: #ffffff !important;
    font-weight: bold;
}
.inline-reservation-calender .datepicker-plot-area .datepicker-day-view .table-days td {
    width: 62px;
    height: 30px;
    padding: 12px;
}
.inline-reservation-calender .datepicker-plot-area .datepicker-day-view .table-days td span {
    width: 100%;
    font-weight: 500;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: none;
}
.pick-a-time .available-times, .pick-a-time .meeting-type-reserve {
    margin-right: 5px !important;
}

@media (min-width: 658px) {
  .hide-mobile {
    display: none !important;
}
.inline-reservation-calender .datepicker-plot-area .datepicker-day-view .table-days td {
    width: 55px;
    height: 55px;
    padding: 12px;
}
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
@if(!empty($meeting) and !empty($meeting->meetingTimes) and $meeting->meetingTimes->count() > 0)
    @push('styles_top')
        <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    @endpush
<div class="col-12 col-lg-6">

    <div class="mt-40 " >
        <h3 class="mt-40 font-16 font-weight-bold text-dark-blue " style="">Select Time Slot</h3>
        <div id="slotsTime" class="d-flex flex-wrap  mt-25" style="">

            <div class="position-relative available-times1 text-center rounded-lg  font-14 text-gray border date-active" style="justify-content: center !important;">
                <input type="radio" name="time1" id="slotTime30" value="30" data-type="online" checked style="visibility: hidden;display: none;">
                <label for="slotTime30" class="mt-10 mx-20 font-weight-bold">30 Min</label>
                <input type="hidden" class="js-time-description" value="null">
            </div>
            <div class="position-relative available-times1 ml-20 text-center rounded-lg font-14 text-gray border">
                <input type="radio" name="time1" id="slotTime15" value="15" data-type="online" style="visibility: hidden;display: none;">
                <label for="slotTime15" class="mt-10 mx-20 font-weight-bold">15 Min</label>
            </div>
        </div>

        <h3 class="mt-40 font-16 font-weight-bold text-dark-blue " style="">Check available Date</h3>

        <div class="mt-35">
            <div class="">
                <input type="hidden" id="inlineCalender" class="form-control">
                <div class="inline-reservation-calender"></div>
            </div>
        </div>

    </div>
</div>

    <div class="col-12 col-lg-6 mt-20 mt-lg-0">

        <div class="pick-a-time d-none" id="PickTimeContainer" data-user-id="{{ $user['id'] }}">

                <form action="{{ (!$meeting->disabled) ? '/meetings/reserve' : '' }}" method="post" id="PickTimeBody" class="d-none">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="day" id="selectedDay" value="">

                        <div class="  form-group mt-30">
                            <label class="input-label">Name*</label>
                            <input name="full_name" id="full_name" type="text" maxlength="40" class="form-control"  placeholder="Name*" value="{{ old('full_name', (auth()->check()) ? auth()->user()->full_name : '') }}">
                        </div>

                        <div class="  form-group mt-30">
                            <label class="input-label">Email*</label>
                            <input name="email" id="email" type="email" maxlength="60" class="form-control"  placeholder="Email*" value="{{ old('email', (auth()->check()) ? auth()->user()->email : '') }}">
                        </div>
<div class="form-group mt-30">
    <label class="input-label">Create Password*</label>
    <input 
        name="password" 
        id="password" 
        type="password" 
        maxlength="60" 
        class="form-control"  
        placeholder="Create Password*" 
        required
    >
</div>

<div class="form-group mt-30">
    <label class="input-label">Confirm Password*</label>
    <input 
        name="password_confirmation" 
        id="password_confirmation" 
        type="password" 
        maxlength="60" 
        class="form-control"  
        placeholder="Confirm Password*" 
        required
    >
</div>
                        {{-- Wallet Payment Widget --}}
                        @include('web.default.includes.wallet_payment_widget', ['totalAmount' => $meeting->amount ?? 0])

                        <div class="  form-group mt-30">
                            <label class="input-label">Contact*</label>
                            <input name="mobile" id="mobile" type="number" class="form-control"  placeholder="Contact*" maxlength="10" value="{{ old('mobile', (auth()->check()) ? auth()->user()->mobile : '') }}">
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="input-label" for="birthdate">Birth Date*:</label>
                                    <input name="birthdate" type="date" required
                                        class="form-control @error('birthdate') is-invalid @enderror" id="birthdate"
                                        aria-describedby="passwordHelp">
                                    @error('birthdate')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="input-label" for="birthtime">Birth Time*:</label>
                                    <input name="birthtime" type="time" required
                                        class="form-control @error('birthtime') is-invalid @enderror" id="birthtime"
                                        aria-describedby="passwordHelp">
                                    @error('birthtime')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="input-label" for="amount">Birth Place*:</label>
                            <input name="birthplace" type="text" required
                                class="form-control @error('birthplace') is-invalid @enderror" id="birthplace"
                                aria-describedby="passwordHelp">
                            @error('birthplace')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <h3 class="font-16 font-weight-bold text-dark-blue d-none">

                        @if($meeting->disabled)
                            {{ trans('public.unavailable') }}
                        @else
                            {{ trans('site.pick_a_time') }}
                            @if(!empty($meeting) and !empty($meeting->discount) and !empty($meeting->amount) and $meeting->amount > 0)
                                <span class="badge badge-danger text-white font-12">{{ $meeting->discount }}% {{ trans('public.off') }} </span>
                            @endif
                        @endif
                    </h3>

                    <div class="d-flex flex-column mt-10">
                        @if($meeting->disabled)
                            <span class="font-14 text-gray">{{ trans('public.unavailable_description') }}</span>
                        @else
                            <span class="d-none font-14 text-gray font-weight-500">
                                {{ trans('site.instructor_hourly_charge') }}

                                @if(!empty($meeting->amount) and $meeting->amount > 0)
                                    @if(!empty($meeting->discount))
                                        <span class="text-decoration-line-through">{{ handlePrice($meeting->amount, true, true, false, null, true) }}</span>
                                        <span class="text-primary">{{ handlePrice($meeting->amount - (($meeting->amount * $meeting->discount) / 100), true, true, false, null, true) }}</span>
                                    @else
                                        <span class="text-primary">{{ handlePrice($meeting->amount, true, true, false, null, true) }}</span>
                                    @endif
                                @else
                                    <span class="text-primary">{{ trans('public.free') }}</span>
                                @endif
                            </span>

                            @if($meeting->in_person)
                            <span class="d-none font-14 text-gray font-weight-500">
                                {{ trans('update.instructor_hourly_charge_in_person_amount') }}

                                @if(!empty($meeting->in_person_amount) and $meeting->in_person_amount > 0)
                                    @if(!empty($meeting->discount))
                                        <span class="text-decoration-line-through">{{ handlePrice($meeting->in_person_amount, true, true, false, null, true) }}</span>
                                        <span class="text-primary">{{ handlePrice($meeting->in_person_amount - (($meeting->in_person_amount * $meeting->discount) / 100), true, true, false, null, true) }}</span>
                                    @else
                                        <span class="text-primary">{{ handlePrice($meeting->in_person_amount, true, true, false, null, true) }}</span>
                                    @endif
                                @else
                                    <span class="text-primary">{{ trans('public.free') }}</span>
                                @endif
                            </span>
                        @endif
                        @if($meeting->group_meeting)
                            <span class="d-none font-14 text-gray font-weight-500">{{ trans('update.instructor_conducts_group_meetings',['min' => $meeting->online_group_min_student,'max' => $meeting->online_group_max_student]) }}</span>
                        @endif

                        @endif

                        <span class="font-16 font-weight-bold text-dark-blue mt-5 selected_date" style="font-weight:900">{{ trans('site.selected_date') }}: <span></span></span>
                    </div>

                    @if(!$meeting->disabled)
                        <div id="availableTimes" class="d-flex flex-wrap align-items-center mt-25">

                        </div>

                        <div class="js-time-description-card d-none mt-25 rounded-sm border p-10">

                        </div>

                        <div class="mt-25 d-none js-finalize-reserve">
                            <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('update.finalize_your_meeting') }}</h3>
                            <span class="selected-date-time font-14 text-gray font-weight-500">{{ trans('update.meeting_time') }}: <span></span></span>

                            <div class="mt-15 d-none">
                                <span class="font-16 font-weight-500 text-dark-blue">{{ trans('update.meeting_type') }}</span>

                                <div class="d-flex align-items-center mt-5">
                                    <div class="meeting-type-reserve position-relative">
                                        <input type="radio" name="meeting_type" id="meetingTypeInPerson" value="in_person">
                                        <label for="meetingTypeInPerson">{{ trans('update.in_person') }}</label>
                                    </div>

                                    <div class="meeting-type-reserve position-relative">
                                        <input type="radio" name="meeting_type" id="meetingTypeOnline" value="online" selected>
                                        <label for="meetingTypeOnline">{{ trans('update.online') }}</label>
                                    </div>
                                </div>
                            </div>

                            @if($meeting->group_meeting)
                                <div class="js-group-meeting-switch d-none align-items-center mt-20">
                                    <label class="mb-0 mr-10 text-gray font-14 font-weight-500 cursor-pointer"
                                        for="withGroupMeetingSwitch">{{ trans('update.group_meeting') }}</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="with_group_meeting" class="custom-control-input"
                                            id="withGroupMeetingSwitch">
                                        <label class="custom-control-label" for="withGroupMeetingSwitch"></label>
                                    </div>
                                </div>

                                <div class="js-group-meeting-options d-none mt-15">
                                    <div class="row">
                                        <div class="col-12 col-lg-4">
                                            <div class="form-group">
                                                <input type="hidden" id="online_group_max_student" value="{{ $meeting->online_group_max_student }}">
                                                <input type="hidden" id="in_person_group_max_student" value="{{ $meeting->in_person_group_max_student }}">
                                                <label for="studentCountRange" class="form-label">{{ trans('update.participates') }}:</label>
                                                <div
                                                    class="range"
                                                    id="studentCountRange"
                                                    data-minLimit="1"
                                                >
                                                    <input type="hidden" name="student_count" id="student_count" value="1">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="js-online-group-amount d-none font-14 font-weight-500 mt-15">
                                        <span class="text-gray d-block">{{ trans('update.online') }} {{ trans('update.group_meeting_hourly_rate_per_student',['amount' => handlePrice($meeting->online_group_amount, true, true, false, null, true)]) }}</span>
                                        <span class="text-danger mt-5 d-block">{{ trans('update.group_meeting_student_count_hint',['min' => $meeting->online_group_min_student, 'max' => $meeting->online_group_max_student]) }}</span>
                                        <span class="text-danger mt-5 d-block">{{ trans('update.group_meeting_max_student_count_hint',['max' => $meeting->online_group_max_student]) }}</span>
                                    </div>

                                    @if($meeting->in_person)
                                    <div class="js-in-person-group-amount d-none font-14 font-weight-500 mt-15">
                                        <span class="text-gray d-block">{{ trans('update.in_person') }} {{ trans('update.group_meeting_hourly_rate_per_student',['amount' => handlePrice($meeting->in_person_group_amount, true, true, false, null, true)]) }}</span>
                                        <span class="text-danger mt-5 d-block">{{ trans('update.group_meeting_student_count_hint',['min' => $meeting->in_person_group_min_student, 'max' => $meeting->in_person_group_max_student]) }}</span>
                                        <span class="text-danger mt-5 d-block">{{ trans('update.group_meeting_max_student_count_hint',['max' => $meeting->in_person_group_max_student]) }}</span>
                                    </div>
                                    @endif

                                </div>
                            @endif
                        </div>

                        <div class=" align-items-center justify-content-end mt-30">
                            <button type="button" id="paymentSubmit" class=" btn btn-primary"  style="font-family: 'Inter', sans-serif !important;">{{ trans('meeting.reserve_appointment') }}</button>
                        </div>
                    @endif
                </form>


                  
          <h3 class="font-16 font-weight-bold text-dark-blue mt-20">Please validate any coupon code before use</h3>
<form id="couponForm">
    <input type="text" id="couponCode" placeholder="Enter Coupon Code" class="form-control">
    <input type="hidden" id="coupon_hidden" name="coupon_id" value="">
    <button type="button" id="validateCoupon" class="btn btn-primary mt-10" style="font-family: 'Inter', sans-serif !important;">Validate</button>
    <div id="result" class="mt-10"></div>
</form>



                {{-- Cashback Alert --}}
                @include('web.default.includes.cashback_alert',['itemPrice' => $meeting->amount, 'classNames' => 'mt-0 mb-40', 'itemType' => 'meeting'])

                @include('web.default.includes.cashback_alert',['itemPrice' => $meeting->amount, 'classNames' => 'mt-0 mb-40', 'itemType' => 'meeting'])

                <div class="loading-img d-none text-center">
                    <img src="{{ config('app.js_css_url') }}/assets/default/img/loading.gif" width="80" height="80">
                </div>
        </div>
</div>
<div id="paymentLoader">
        <div class="spinner"></div>
        </div>
    @push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart.min.js"></script>
    <script>




        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>
        <script src="{{ config('app.js_css_url') }}/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
        <script>
$(document).ready(function() {
    // Attach change event listener to radio buttons
    $('#slotsTime input[type="radio"]').change(function() {
        // Remove 'date-active' class from all elements
        $('.available-times1').removeClass('date-active');

        // Add 'date-active' class to the selected radio button's parent element
        $(this).closest('.available-times1').addClass('date-active');
        $('#PickTimeBody').addClass('d-none');

    });
});
</script>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    const loaderEl = document.getElementById('paymentLoader');

    function showPaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'block';
    }

    function hidePaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'none';
    }

document.getElementById('validateCoupon').addEventListener('click', function() {
    const couponCode = document.getElementById('couponCode').value;
    
    if (!couponCode) {
        $('#result').html('<span class="invalid-feedback d-block">Please enter a coupon code</span>');
        return;
    }

    $.ajax({
        url: '{{ url("/cart/coupon/validate") }}',  
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            coupon: couponCode
        },
     success: function(response) {
    if (response.status === 200) {
        $('#result').html('<span class="valid-feedback d-block">Coupon is valid! Discount will be applied.</span>');
        $('#result').find('.invalid-feedback').remove();
        // Hidden field में discount_id save करें
        $('#coupon_hidden').val(response.discount_id);
    } else {
        $('#result').html('<span class="invalid-feedback d-block">Invalid coupon code.</span>');
        $('#result').find('.valid-feedback').remove();
        $('#coupon_hidden').val('');
    }
},





        error: function(xhr) {
            $('#result').html('<span class="invalid-feedback d-block">Something went wrong.</span>');
            $('#result').find('.valid-feedback').remove();
            $('#coupon_hidden').val('');
        }
    });
});


    document.getElementById('paymentSubmit').addEventListener('click', function(e) {
        e.preventDefault();

        // const meetingTimeId = document.querySelector('input[name="meeting_time"]:checked')?.value;
        const selectedTime = document.querySelector('input[name="time"]:checked')?.value;

        if (!selectedTime) {
            alert('Please select a time slot');
            return;
        }
const userDetails = {
    name: document.getElementById('full_name').value,
    email: document.getElementById('email').value,
    number: document.getElementById('mobile').value,
    password: document.getElementById('password').value,
    birthdate: document.getElementById('birthdate').value,
    birthtime: document.getElementById('birthtime').value,
    birthplace: document.getElementById('birthplace').value,
    selectedDay: document.getElementById('selectedDay').value,
    discount_id: $('#coupon_hidden').val() || null,
    wallet_amount: (typeof getWalletPaymentAmount === 'function') ? getWalletPaymentAmount() : 0,
    slotDuration: parseInt(document.querySelector('input[name="time1"]:checked')?.value || '30')
};


    showPaymentLoader();
        initiatePayment('meeting' , selectedTime, userDetails);
    });

    // jscode

    class UnifiedPaymentHandler {
        constructor() {
            this.loader = document.getElementById('loader');
        }

        async initiatePayment(paymentType, itemId, userDetails) {
            try {
                if (!this.validateInputs(userDetails)) {
                    return false;
                }

                // this.showLoader();

                const response = await fetch('/payments/initiate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        payment_type: paymentType,
                        item_id: itemId,
                        name: userDetails.name,
                        email: userDetails.email,
                        number: userDetails.number,
                        password: userDetails?.password,
                        discount_id: userDetails.discount_id || null,
                        installment_id: userDetails.installment_id || null,
                        Country: userDetails.Country || null,
                        StateProvince: userDetails.StateProvince || null,
                        City: userDetails.City || null,
                        pin_code: userDetails.pin_code || null,
                        address: userDetails.address || null,
                        message: userDetails.message || null,
                        amount: userDetails.amount || null,
                        selectedDay: userDetails.selectedDay || null,
                        birthdate: userDetails.birthdate || null,
                        birthtime: userDetails.birthtime || null,
                        birthplace: userDetails.birthplace || null,
                        slot_duration: userDetails.slotDuration || 30
                    })
                });

                // console.log(response);

                if (!response.ok) {
                    throw new Error('Failed to initiate payment');
                }

                const data = await response.json();

                // Full wallet payment — booking already confirmed, no Razorpay needed
                if (data.wallet_paid) {
                    window.location.href = '/payment/success';
                    return;
                }

                this.openRazorpayCheckout(data, userDetails);

            } catch (error) {
                console.error('Payment error:', error);
                alert('Payment failed. Please try again.');
                this.hideLoader();
            }
        }

        openRazorpayCheckout(orderData, userDetails) {
            const options = {
                key: orderData.key,
                amount: orderData.amount,
                currency: orderData.currency,
                name: 'Asttrolok',
                order_id: orderData.razorpay_order_id,

                handler: (response) => {
                    this.handleSuccess(response, orderData.order_id);
                },

                prefill: {
                    name: userDetails.name,
                    email: userDetails.email,
                    contact: userDetails.number
                },

                theme: {
                    color: '#43d477'
                },

                modal: {
                    ondismiss: () => {
                        this.hideLoader();
                        alert('Payment cancelled');
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }

        handleSuccess(response, orderId) {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '/payments/callback';

            const fields = {
                'razorpay_payment_id': response.razorpay_payment_id,
                'razorpay_order_id': response.razorpay_order_id,
                'razorpay_signature': response.razorpay_signature,
                'order_id': orderId
            };

            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        validateInputs(userDetails) {
            if (!userDetails.name || !userDetails.email || !userDetails.number) {
                alert('Please fill all required fields');
                return false;
            }
            return true;
        }

        showLoader() {
            if (this.loader) this.loader.style.display = 'block';
            document.body.classList.add('disabled-page');
        }

        hideLoader() {
            if (this.loader) this.loader.style.display = 'none';
            document.body.classList.remove('disabled-page');
        }
    }

    window.paymentHandler = new UnifiedPaymentHandler();

    function initiatePayment(type, itemId, userDetails) {
        return window.paymentHandler.initiatePayment(type, itemId, userDetails);
    }
</script>
@endpush
@else

    @include(getTemplate() . '.includes.no-result',[
       'file_name' => 'meet.png',
       'title' => trans('site.instructor_not_available'),
       'hint' => '',
    ])

@endif
