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
@if(!empty($meeting) and !empty($meeting->meetingTimes) and $meeting->meetingTimes->count() > 0)
    @push('styles_top')
        <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    @endpush
 @php
    $selected = in_array($selectedTime ?? '', ['15', '30']) ? $selectedTime : 'both';
@endphp
    <div class="mt-20 align-items-center">
        @if($selected == 'both' )
             <h3 class="mt-40 font-16 font-weight-bold text-dark-blue align-items-center" >Select Time Slot</h3>
        @endif

        <div id="slotsTime" class="d-flex flex-wrap align-items-center mt-20" style="justify-content: center !important;">
            @if($selected == '30' )
            <div class="d-none position-relative available-times1 text-center rounded-lg  font-14 text-gray border date-active" style="justify-content: center !important;">
                <input type="radio" name="time1" id="slotTime30" value="30" data-type="online" checked style="visibility: hidden;display: none;">
                <label for="slotTime30" class="mt-10 mx-20 font-weight-bold">30 Min</label>
                <input type="hidden" class="js-time-description" value="null">
            </div>
            @endif
            @if( $selected == 'both')
            <div class=" position-relative available-times1 text-center rounded-lg  font-14 text-gray border date-active" style="justify-content: center !important;">
                <input type="radio" name="time1" id="slotTime30" value="30" data-type="online" checked style="visibility: hidden;display: none;">
                <label for="slotTime30" class="mt-10 mx-20 font-weight-bold">30 Min</label>
                <input type="hidden" class="js-time-description" value="null">
            </div>
            <div class=" position-relative available-times1 ml-20 text-center rounded-lg font-14 text-gray border">
                <input type="radio" name="time1" id="slotTime15" value="15" data-type="online" style="visibility: hidden;display: none;">
                <label for="slotTime15" class="mt-10 mx-20 font-weight-bold">15 Min</label>
            </div>
            @if( $selected == '15')
            <div class="d-none position-relative available-times1 ml-20 text-center rounded-lg font-14 text-gray border">
                <input type="radio" name="time1" id="slotTime15" value="15" data-type="online" style="visibility: hidden;display: none;">
                <label for="slotTime15" class="mt-10 mx-20 font-weight-bold">15 Min</label>
            </div>
            @endif
        </div>
        <h3 class="font-16 mt-20 font-weight-bold text-dark-blue align-items-center" style="padding-left:0px !important;">Select Date</h3>

        <div class="mt-20">
            <div class="row align-items-center justify-content-center">
                <input type="hidden" id="inlineCalender" class="form-control">
                <div class="inline-reservation-calender"></div>
            </div>
        </div>
    </div>
<h3 class="font-16 font-weight-bold text-dark-blue mt-20">Please validate any coupon code before use</h3>
        <form  id="cartForm15" method="Post">
                    {{ csrf_field() }}
                    <div class="row" style="display: flex;justify-content: space-evenly;align-items: flex-start;flex-wrap: nowrap;flex-direction: row;">
                    <div class="col-11 col-lg-9">
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-10 {{ session('discountCoupon') ? (session('discountCoupon')=='no' ? 'is-invalid' : 'is-valid') : '' }}" value="{{ session('discountCoupon') ? (session('discountCoupon')=='no' ? '' : session('discountCoupon')) : '' }}"
                         style="border-radius: 20px !important;" placeholder="{{ trans('cart.enter_your_code_here') }}">

                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                        <input type='hidden' name='user_id' value='{{ $user["id"] }}'>
                    </div>
                    </div>
                    <div class="col-5 col-lg-3 botton-1" style="margin-top: 3px;margin-right: -295px;position: absolute;">
                    <button type="submit" id="checkCoupon15" class="btn btn-sm btn-primary mt-10" style="height: 35px !important; border-radius: 20px !important; ">{{ trans('cart.validate') }}</button>
                    </div></div>
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
                <div class=" align-items-center justify-content-end mt-30" id="Confirm">
                    <button type="button" onclick="pop();" class="btn bookbtn btn-primary  bookb">Confirm</button>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                    <input name="full_name" type="text"  class="form-control" id="customer_name" value="{{ old('full_name') }}"  >
                     @if ($errors->has('full_name'))
                            <span class="text-danger">{{ $errors->first('full_name') }}</span>
                        @endif

                </div>

                <div class="  form-group mt-15">
                    <label class="input-label">Email*</label>
                    <input name="email" type="email" maxlength="60" class="form-control @error('email') is-invalid @enderror" id="customer_email" required>
                     @if($errors->has('email'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </div>
                                    @enderror
                </div>
  <div class="row">
              <div class="form-group col-md-6">
            <label class="input-label" for="mobile">{{ trans('auth.country') }}:</label>
            <select name="country_code" class="form-control select2">
                @foreach(getCountriesMobileCode() as $country => $code)

                    <option value="{{ $code }}" @if($code == '+91')) selected @endif>{{ $country }}</option>
                @endforeach
            </select>
                 </div>
                 <div class="form-group col-md-6 mt-3 mt-md-0">
                    <label class="input-label">Contact*</label>
                    <input name="mobile" type="number" class="form-control @error('mobile') is-invalid @enderror"  maxlength="10" id="customer_number">
                     @if($errors->has('mobile'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('mobile') }}
                                    </div>
                                    @enderror
                </div>
                </div>
<div class="  form-group mt-30">
                   <label class="input-label" for="amount">Birth Date:</label>
                   <input name="birthdate" type="date" required class="form-control @error('amount') is-invalid @enderror" id="amount"
                                   aria-describedby="passwordHelp">
                </div>
                <div class="form-group">
                            <label class="input-label" for="amount">Birth Time:</label>
                            <input name="birthtime" type="time" required
                                   class="form-control @error('amount') is-invalid @enderror" id="amount"
                                   aria-describedby="passwordHelp">
                </div>
                <label class="input-label" for="amount">Birth Place:</label>
                <input name="birthplace" type="text" required
                       class="form-control @error('amount') is-invalid @enderror" id="amount"
                       aria-describedby="passwordHelp">

                <div class=" align-items-center justify-content-end mt-15" style="text-align:center;">
                    <button type="button" onclick="validationform()" class="js-submit-form btn bookbtn btn-primary  bookb"><svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
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

    @push('scripts_bottom')
    <script defer src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart.min.js"></script>
    <script defer>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>
    <script defer>
       function pop(){

             $('#textpop').modal();

           }

         </script>

<script defer>

 function validationform(){

        var name = '';
        var email = '';
        var mobile = '';
         name = document.getElementById("customer_name").value ;
         email = document.getElementById("customer_email").value;
         mobile = document.getElementById("customer_number").value;

        $('.textdanger').remove();
        if(name ===''){

            var namevalidation ='Name field is required';
            $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');

        }
         if(email ===''){

            var emailvalidation ='Email field is required';
            $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
         if(mobile ===''){

            var mobilevalidation ='Mobile field is required';
            $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
        }else{
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(!regex.test(email)) {
                document.getElementById("customer_email").value =email;
                var emailvalidation ='Enter Valid Email Address';
                $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span><br>');
               return false;
            }
          return true;
        }
 }
         </script>
        <script defer src="{{ config('app.js_css_url') }}/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
        <script defer>
$(document).ready(function() {

    $('#slotsTime input[type="radio"]').change(function() {

        $('.available-times1').removeClass('date-active');

        $(this).closest('.available-times1').addClass('date-active');
        $('#PickTimeBody').addClass('d-none');

    });
});

</script>
    @endpush
@else

    @include(getTemplate() . '.includes.no-result',[
       'file_name' => 'meet.png',
       'title' => trans('site.instructor_not_available'),
       'hint' => '',
    ])

@endif
