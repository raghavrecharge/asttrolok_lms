<style>
    .available-times label{
        color: #161716 !important;
    font-size: 0.875rem !important;
    font-weight: 900 !important;
    }
/*    .datepicker-plot-area .datepicker-day-view .table-days td.today span {*/
/*    background-color: #fff !important;*/
/*    color: #000 !important;*/
/*    border: 0;*/
/*    text-shadow: none;*/
/*}*/
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
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
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
    /*background-color: #bbd6ff69;*/
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
    width: 550px;
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
@if(!empty($meeting) and !empty($meeting->meetingTimes) and $meeting->meetingTimes->count() > 0)
    @push('styles_top')
        <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    @endpush

    <div class="mt-40 align-items-center" >
        <h3 class="mt-40 font-16 font-weight-bold text-dark-blue align-items-center" style="text-align: center;">Select Time Slot</h3>
        <div id="slotsTime" class="d-flex flex-wrap align-items-center mt-25" style="justify-content: center !important;">
            
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

        <h3 class="mt-40 font-16 font-weight-bold text-dark-blue align-items-center" style="text-align: center;">Check available Date</h3>
        
        
        

        <div class="mt-35">
            <div class="row align-items-center justify-content-center">
                <input type="hidden" id="inlineCalender" class="form-control">
                <div class="inline-reservation-calender"></div>
            </div>
        </div>
        
        
        
        

        
        
<!--        <div class="slider-container p-10 mt-20 justify-content-center" style="display: flex; gap:5px; overflow: hidden;">-->
<!--    <div class="slider-button preview-button text-center rounded-lg py-15 px-20 font-14 text-gray  date1" style="display: flex;align-items: center;">-->
        <!-- Preview Button SVG -->
<!--        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"-->
<!--	 viewBox="0 0 492 492" style="enable-background:new 0 0 492 492; height: 20px;" xml:space="preserve">-->
<!--<g>-->
<!--	<g>-->
<!--		<path d="M198.608,246.104L382.664,62.04c5.068-5.056,7.856-11.816,7.856-19.024c0-7.212-2.788-13.968-7.856-19.032l-16.128-16.12-->
<!--			C361.476,2.792,354.712,0,347.504,0s-13.964,2.792-19.028,7.864L109.328,227.008c-5.084,5.08-7.868,11.868-7.848,19.084-->
<!--			c-0.02,7.248,2.76,14.028,7.848,19.112l218.944,218.932c5.064,5.072,11.82,7.864,19.032,7.864c7.208,0,13.964-2.792,19.032-7.864-->
<!--			l16.124-16.12c10.492-10.492,10.492-27.572,0-38.06L198.608,246.104z"/>-->
<!--	</g>-->
<!--	</g>-->
<!--</svg>-->
<!--    </div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1 pratul">1<br>SUN-->
<!--      <input type="hidden"  class="form-control " value="1714372200000">-->
<!--    </div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date-active date1 pratul">2<br>MON-->
<!--    <input type="hidden"  class="form-control " value="1714458600000"></div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">3<br>TUE</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">5<br>THU</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">6<br>FRI</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">8<br>SUN</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">9<br>MON</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">11<br>WED</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">12<br>THU</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">15<br>SUN</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">16<br>MON</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1">17<br>TUE</div>-->
<!--    <div class="slider-item text-center rounded-lg py-15 px-20 font-14 text-gray border date1" >18<br>WED</div>-->
    <!-- Add more slider items as needed -->
<!--    <div class="slider-button next-button text-center rounded-lg py-15 px-20 font-14 text-gray  date1" style="display: flex;align-items: center;">-->
        <!-- Next Button SVG -->
<!--        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"-->
<!--	 viewBox="0 0 492.004 492.004" style="enable-background:new 0 0 492.004 492.004; height: 20px;" xml:space="preserve">-->
<!--<g>-->
<!--	<g>-->
<!--		<path d="M382.678,226.804L163.73,7.86C158.666,2.792,151.906,0,144.698,0s-13.968,2.792-19.032,7.86l-16.124,16.12-->
<!--			c-10.492,10.504-10.492,27.576,0,38.064L293.398,245.9l-184.06,184.06c-5.064,5.068-7.86,11.824-7.86,19.028-->
<!--			c0,7.212,2.796,13.968,7.86,19.04l16.124,16.116c5.068,5.068,11.824,7.86,19.032,7.86s13.968-2.792,19.032-7.86L382.678,265-->
<!--			c5.076-5.084,7.864-11.872,7.848-19.088C390.542,238.668,387.754,231.884,382.678,226.804z"/>-->
<!--	</g>-->
<!--	</g>-->

<!--</svg>-->
        
<!--    </div>-->
<!--</div>-->

<script>
    // // JavaScript to handle slider functionality
    // const sliderItems = document.querySelectorAll('.slider-item');
    // const numItemsToShow = 7;
    // let currentIndex = 0;

    // // Function to show current slide
    // function showSlide(startIndex) {
    //     // Hide all slides
    //     sliderItems.forEach(item => item.style.display = 'none');
    //     // Show slides based on startIndex and numItemsToShow
    //     for (let i = startIndex; i < Math.min(startIndex + numItemsToShow, sliderItems.length); i++) {
    //         sliderItems[i].style.display = 'block';
    //     }
    // }

    // // Show initial slides
    // showSlide(currentIndex);

    // // Function to go to next slides
    // function nextSlides() {
    //     currentIndex = Math.min(currentIndex + numItemsToShow, sliderItems.length - numItemsToShow);
    //     showSlide(currentIndex);
    // }

    // // Function to go to previous slides
    // function prevSlides() {
    //     currentIndex = Math.max(currentIndex - numItemsToShow, 0);
    //     showSlide(currentIndex);
    // }

    // // Function to handle click on slider items
    // function handleSliderItemClick(event) {
    //     // Remove "date-active" class from all slider items
    //     sliderItems.forEach(item => item.classList.remove('date-active'));
    //     // Add "date-active" class to the clicked slider item
    //     event.target.classList.add('date-active');
    // }

    // // Event listeners for next and preview buttons
    // document.querySelector('.next-button').addEventListener('click', nextSlides);
    // document.querySelector('.preview-button').addEventListener('click', prevSlides);

    // // Event listener for slider item click
    // sliderItems.forEach(item => item.addEventListener('click', handleSliderItemClick));
</script>

        
        
    </div>
    {{-- @php
        $webinar_id =0;
        @endphp
        @foreach($carts as $cart)
        @php
        $webinar_id= $cart->webinar_id;
        @endphp
        
        @endforeach --}}
        <h3 class="font-16 font-weight-bold text-dark-blue mt-20">Please validate any coupon code before use</h3>
        <form  id="cartForm15" method="Post">
                    {{ csrf_field() }}
                    <div class="row"> 
                    <div class="col-8 col-lg-3"> 
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-10 {{ session('discountCoupon') ? (session('discountCoupon')=='no' ? 'is-invalid' : 'is-valid') : '' }}" value="{{ session('discountCoupon') ? (session('discountCoupon')=='no' ? '' : session('discountCoupon')) : '' }}"
                         placeholder="{{ trans('cart.enter_your_code_here') }}">
                        {{-- <input type="hidden" name="item_id" id="web_id1" value="{{$webinar_id}}" class="form-control mt-25" > --}}
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                        <input type='hidden' name='user_id' value='{{ $user["id"] }}'>
                    </div>
                    </div>
                    <div class="col-4 col-lg-3 ">
                    <button type="submit" id="checkCoupon15" class="btn btn-sm btn-primary mt-10">{{ trans('cart.validate') }}</button>
                    </div></div>
                </form>

    <div class="pick-a-time d-none" id="PickTimeContainer" data-user-id="{{ $user["id"] }}">

        <!--<div class="d-flex align-items-center my-40 rounded-lg border px-10 py-5">-->
            <!--<div class="appointment-timezone-icon">-->
            <!--    <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/timezone.svg" alt="appointment timezone">-->
            <!--</div>-->
            <!--<div class="ml-15">-->
            <!--    <div class="font-16 font-weight-bold text-dark-blue">{{ trans('update.note') }}:</div>-->
            <!--    <p class="font-14 font-weight-500 text-gray">{{ trans('update.appointment_timezone_note_hint',['timezone' => $meetingTimezone .' '. toGmtOffset($meetingTimezone)]) }}</p>-->
            <!--</div>-->
        <!--</div>-->


        {{-- Cashback Alert --}}
        @include('web.default.includes.cashback_alert',['itemPrice' => $meeting->amount, 'classNames' => 'mt-0 mb-40', 'itemType' => 'meeting'])


        <div class="loading-img d-none text-center">
            <img src="{{ config('app.js_css_url') }}/assets/default/img/loading.gif" width="80" height="80">
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

                <div class="  form-group mt-30">
                    <label class="input-label">Name*</label>
                    <input name="full_name" type="text" maxlength="40" class="form-control"  placeholder="Name*" >
                    <input name="astrologer_name" type="hidden" value="{{ $user["full_name"] }}" class="form-control"  placeholder="Name*" >
                </div>

                <div class="  form-group mt-30">
                    <label class="input-label">Email*</label>
                    <input name="email" type="email" maxlength="60" class="form-control"  placeholder="Email*">
                </div>

                <div class="  form-group mt-30">
                    <label class="input-label">Contact*</label>
                    <input name="mobile" type="number" class="form-control"  placeholder="Contact*" maxlength="10">
                </div>

                

                <!--<div class="  form-group mt-30">-->
                <!--    <label class="input-label">{{ trans('public.description') }}</label>-->
                <!--    <textarea name="description" class="form-control" rows="5" placeholder="{{ trans('update.reserve_time_description_placeholder') }}"></textarea>-->
                <!--</div>-->

                <div class=" align-items-center justify-content-end mt-30">
                    <button type="button" class="js-submit-form btn btn-primary">{{ trans('meeting.reserve_appointment') }}</button>
                </div>
            @endif
        </form>
      
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
    @endpush
@else

    @include(getTemplate() . '.includes.no-result',[
       'file_name' => 'meet.png',
       'title' => trans('site.instructor_not_available'),
       'hint' => '',
    ])

@endif
