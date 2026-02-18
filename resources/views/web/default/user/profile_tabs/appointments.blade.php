<style>
    
   .available-times label {
    color: #161716 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
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

   <div class="mt-20 align-items-center px-0">
        <h3 class="mt-40 font-16 font-weight-bold text-dark-blue align-items-center" >Select Time Slot</h3>
        <div id="slotsTime" class="d-flex flex-wrap align-items-center mt-20" style="justify-content: center !important;">

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
        <h3 class="font-16 mt-20 font-weight-bold text-dark-blue align-items-center" style="padding-left:0px !important;">Select Date</h3>

        <div class="mt-20">
            <div class="row align-items-center justify-content-center">
                <input type="hidden" id="inlineCalender" class="form-control">
                <div class="inline-reservation-calender"></div>
            </div>
        </div>
    </div>
  <h3 class="font-15 font-weight-bold text-dark-blue mt-20">Please validate any coupon code before use</h3>
           
<form id="couponForm">
    <input type="text" id="couponCode" placeholder="Enter Coupon Code" class="form-control">
    <input type="hidden" id="coupon_hidden" name="coupon_id" value="">
    <button type="button" id="validateCoupon" class="btn btn-primary mt-10">Validate</button>
    <div id="result" class="mt-10"></div>
</form>
    <div class="pick-a-time d-none" id="PickTimeContainer" data-user-id="{{ $user["id"] }}">

        @include('web.default.includes.cashback_alert',['itemPrice' => $meeting->amount, 'classNames' => 'mt-0 mb-40', 'itemType' => 'meeting'])

        <div class="loading-img d-none text-center">
            <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/loading.gif" width="80" height="80">
        </div>

        <form action="{{ (!$meeting->disabled) ? '/meetings/reserve' : '' }}" method="post" id="PickTimeBody" class="d-none">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="day" id="selectedDay" value="">

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

                <span class="font-16 font-weight-bold text-dark-blue selected_date" style="font-weight:900">Select Available Time Slot</span>
            </div>

            @if(!$meeting->disabled)
                <div id="availableTimes" class="d-flex flex-wrap align-items-center mt-10 pt-10">

                </div>

                <div class="js-time-description-card d-none mt-25 rounded-sm border p-10">

                </div>

                <div class="mt-25 d-none js-finalize-reserve  align-items-center" style="text-align: center;">
               <div class="align-items-center justify-content-end mt-30" id="Confirm" style="margin-right: 45px;">
    <button type="button" onclick="pop();" class="btn bookbtn btn-primary bookb">Confirm</button>
</div>
                    <h3 class="font-16  d-none font-weight-bold text-dark-blue">{{ trans('update.finalize_your_meeting') }}</h3>
                    <span class="selected-date-time   d-none font-14 text-gray font-weight-500">{{ trans('update.meeting_time') }}: <span></span></span>

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
                                            <input type="hidden" name="student_count" value="1">
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
                <div class="modal fade" id="textpop" tabindex="-1" aria-labelledby="textpop" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mt-50">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class="mt-5 position-relative">

                <div class="modal-video-lists mt-5">

                                        <div class="accordion-content-wrapper mt-5" id="videosAccordion" role="tablist" aria-multiselectable="true">
                                     <div class="login-card1">

                                     <div class="mt-5 text-center">
                        <span class="Fill text-bold">Fill your Contact Details Now</span>

                    </div>

                <div class="  form-group mt-15">
                    <label class="input-label">Name*</label>
                    <input name="full_name" type="text"  class="form-control" id="full_name" value="{{ old('full_name') }}"  >
                     @if ($errors->has('full_name'))
                            <span class="text-danger">{{ $errors->first('full_name') }}</span>
                        @endif

                </div>

                <div class="  form-group mt-15">
                    <label class="input-label">Email*</label>
                    <input name="email" type="email" maxlength="60" class="form-control @error('email') is-invalid @enderror" id="email" required>
                     @if($errors->has('email'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </div>
                                    @enderror
                </div>
                <div class="form-group mt-15">
    <label class="input-label">Create Password*</label>
    <input name="password" 
           type="password" 
           maxlength="60" 
           class="form-control @error('password') is-invalid @enderror" 
           id="password" 
           required>

    @if($errors->has('password'))
        <div class="invalid-feedback">
            {{ $errors->first('password') }}
        </div>
    @endif
</div>

<div class="form-group mt-15">
    <label class="input-label">Confirm Password*</label>
    <input name="password_confirmation" 
           type="password" 
           maxlength="60" 
           class="form-control @error('password_confirmation') is-invalid @enderror" 
           id="password_confirmation" 
           required>

    @if($errors->has('password_confirmation'))
        <div class="invalid-feedback">
            {{ $errors->first('password_confirmation') }}
        </div>
    @endif
</div>


                <div class="  form-group mt-15">
                    <label class="input-label">Contact*</label>
                    <input name="mobile" type="number" class="form-control @error('mobile') is-invalid @enderror"  maxlength="10" id="mobile">
                     @if($errors->has('mobile'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('mobile') }}
                                    </div>
                                    @enderror
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

                <div class=" align-items-center justify-content-end mt-15" style="text-align:center;">
                    <button type="button" id="paymentSubmit" class=" btn bookbtn btn-primary  bookb"><svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.8933 0H11.5933V1.3C11.5933 1.56 11.3767 1.73333 11.16 1.73333C10.9433 1.73333 10.7267 1.56 10.7267 1.3V0H3.79333V1.3C3.79333 1.56 3.57667 1.73333 3.36 1.73333C3.14333 1.73333 2.92667 1.56 2.92667 1.3V0H1.62667C0.976667 0 0.5 0.563333 0.5 1.3V2.86H14.3667V1.3C14.3667 0.563333 13.5867 0 12.8933 0ZM0.5 3.77V11.7C0.5 12.48 0.976667 13 1.67 13H12.9367C13.63 13 14.41 12.4367 14.41 11.7V3.77H0.5ZM4.35667 11.05H3.31667C3.14333 11.05 2.97 10.92 2.97 10.7033V9.62C2.97 9.44667 3.1 9.27333 3.31667 9.27333H4.4C4.57333 9.27333 4.74667 9.40333 4.74667 9.62V10.7033C4.70333 10.92 4.57333 11.05 4.35667 11.05ZM4.35667 7.15H3.31667C3.14333 7.15 2.97 7.02 2.97 6.80333V5.72C2.97 5.54667 3.1 5.37333 3.31667 5.37333H4.4C4.57333 5.37333 4.74667 5.50333 4.74667 5.72V6.80333C4.70333 7.02 4.57333 7.15 4.35667 7.15ZM7.82333 11.05H6.74C6.56667 11.05 6.39333 10.92 6.39333 10.7033V9.62C6.39333 9.44667 6.52333 9.27333 6.74 9.27333H7.82333C7.99667 9.27333 8.17 9.40333 8.17 9.62V10.7033C8.17 10.92 8.04 11.05 7.82333 11.05ZM7.82333 7.15H6.74C6.56667 7.15 6.39333 7.02 6.39333 6.80333V5.72C6.39333 5.54667 6.52333 5.37333 6.74 5.37333H7.82333C7.99667 5.37333 8.17 5.50333 8.17 5.72V6.80333C8.17 7.02 8.04 7.15 7.82333 7.15ZM11.29 11.05H10.2067C10.0333 11.05 9.86 10.92 9.86 10.7033V9.62C9.86 9.44667 9.99 9.27333 10.2067 9.27333H11.29C11.4633 9.27333 11.6367 9.40333 11.6367 9.62V10.7033C11.6367 10.92 11.5067 11.05 11.29 11.05ZM11.29 7.15H10.2067C10.0333 7.15 9.86 7.02 9.86 6.80333V5.72C9.86 5.54667 9.99 5.37333 10.2067 5.37333H11.29C11.4633 5.37333 11.6367 5.50333 11.6367 5.72V6.80333C11.6367 7.02 11.5067 7.15 11.29 7.15Z" fill="white"/>
</svg>

                             <span class="ml-5 font-12"> Book a Consultation </span></button>
                </div>

                </div>
                                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

            @endif
        </form>
    </div>
    <div id="paymentLoader">
        <div class="spinner"></div>
        </div>

    @push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart.min.js"></script>
    <script  >
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>
    <script  >
       function pop(){

             $('#textpop').modal();

           }

         </script>

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

<script   src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script  >
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
            if (response.status === 200 && response.valid === true) {
                $('#result').html('<span class="valid-feedback d-block">Coupon is valid! Discount will be applied.</span>');
                $('#coupon_hidden').val(response.discount_id);
            } else {
                $('#result').html('<span class="invalid-feedback d-block">Invalid coupon code.</span>');
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


    const loaderEl = document.getElementById('paymentLoader');

    function showPaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'block';
    }

    function hidePaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'none';
    }
    
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
        birthdate: document.getElementById('birthdate').value,
        birthtime: document.getElementById('birthtime').value,
        birthplace: document.getElementById('birthplace').value,
        selectedDay: document.getElementById('selectedDay').value,
        password: document.getElementById('password').value,
        // selectedTime: selectedTime,
        discount_id: @json(session('meeting_discount_id'))
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
                        password: userDetails.password,
                        discount_id: @json(session('meeting_discount_id')) || null,
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
                        birthplace: userDetails.birthplace || null
                    })
                });

                // console.log(response);

                if (!response.ok) {
                    hidePaymentLoader();
                    throw new Error('Failed to initiate payment');
                }

                const data = await response.json();
                this.openRazorpayCheckout(data, userDetails);
                hidePaymentLoader();

            } catch (error) {
                console.error('Payment error:', error);
                alert('Payment failed. Please try again.');
                this.hideLoader();
                hidePaymentLoader();
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
                hidePaymentLoader();
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
