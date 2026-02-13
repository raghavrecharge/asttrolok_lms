@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/css-stars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/video/video-js.min.css">
    <style>
        .course-description p{
            font-family: 'main-font-family' !important;
        }
        .course-description p span{
            font-family: 'main-font-family' !important;
        }
        .course-description span{
            font-family: 'main-font-family' !important;
        }
        .course-content-sidebar .course-img.has-video .course-video-icon {

    width: 50px;
    height: 50px;

}
 .register_desktop{
        position: fixed;

    top: 110px;
    width: 27%;
    right: 6.5%;
    display: block;
        background-color: white;
        display:none;
    }

	.modal.left .modal-dialog,
	.modal.right .modal-dialog {
		position: fixed;
		right: -100%;
		margin: auto;
		width: 320px;
		height: 100%;
		-webkit-transform: translateX(100%);
		    -ms-transform: translateX(100%);
		     -o-transform: translateX(100%);
		        transform: translateX(100%);
	}
.afterpop{

    transition: all 2s  !important;
    transition-timing-function: ease-in  !important;
   -webkit-transform: translateX(0%) !important;
		    -ms-transform: translateX(0%) !important;
		     -o-transform: translateX(0%) !important;
		        transform: translateX(0%) !important;

}
	.modal.left .modal-content,
	.modal.right .modal-content {
		height: 100%;
		overflow-y: auto;
	}

	.modal.left .modal-body,
	.modal.right .modal-body {
		padding: 15px 15px 80px;
	}

	.modal.right.fade .modal-dialog {

		right: 0px;

	}

	.modal.right.fade.in .modal-dialog {
		right: 0;
		transition: all .5s;
	}

	.modal-content {
		border-radius: 0;
		border: none;
	}

	.modal-header {
		border-bottom-color: #EEEEEE;
		background-color: #FAFAFA;
	}

.webinar-card.webinar-list .image-box {
    min-height: 260px;
}
button.btn.btn-primary.rounded-pill.buynow {
    display: none;
}

    </style>
@endpush

@section('content')
<section class="course-cover-container {{ empty($activeSpecialOffer) ? 'not-active-special-offer' : '' }}">
        <img src="{{ config('app.img_dynamic_url') }}/store/1/Courses/Cover/Background.jpg" class="img-cover course-cover-img" alt="{{ $bundle->title }}"/>

        <div class="cover-content pt-40">
            <div class="container position-relative">

                <h1 class="font-30 course-title text-center py-10">{{ clean($bundle->title, 't') }}{{(isset($bundle->start_date) and $bundle->isCourse()) ?" (Upcoming)" :"" }}</h1>
                <div class="row">
                    <div class="col-12 col-lg-6 course-section-top">
                        <div class="course-img text-center {{ $bundle->video_demo ? 'has-video' :'' }}">

                        @if($bundle->video_demo)
                        <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:280px" src="{{ $bundle->video_demo_source == 'upload' ?  url($bundle->video_demo) : $bundle->video_demo }}" title="Asttrolok" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"  allowfullscreen=""></iframe>

                            @else
                              <img src="{{ config('app.img_dynamic_url') }}{{ $bundle->getImage() }}" class="img-cover" style="height:auto" alt="webinar Demo Video">
                        @endif
                       <div class="px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="item_name" value="bundle_id">

                            @if(!empty($bundle->tickets))
                                @foreach($bundle->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio"
                                               data-discount="{{ $ticket->discount }}"
                                               data-currency
                                               value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                                               name="ticket_id"
                                               id="courseOff{{ $ticket->id }}">
                                        <label class="form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                                            <span class="font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                                @endif</span>
                                            <span class="font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @php
                                $canSale = ($bundle->canSale() and !$hasBought);
                            @endphp

                            <div class="mt-20 d-flex flex-column">
                                @if($hasBought or !empty($bundle->getInstallmentOrder()))
                                    <button type="button" class="btn btn-primary" disabled>{{ trans('panel.purchased') }}</button>
                                @elseif($bundle->price > 0)
                                    <button type="{{ $canSale ? 'submit' : 'button' }}" @if(!$canSale) disabled @endif class="btn btn-primary">
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>

                                    @if($canSale and $bundle->subscribe)
                                        <a href="/subscribes/apply/bundle/{{ $bundle->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($bundle->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $bundle->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_bundles_payment_button_status')))
                                        <button type="button" class="btn btn-outline-danger mt-20 js-bundle-direct-payment">
                                            {{ trans('update.buy_now') }}
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ $canSale ? '/bundles/'. $bundle->slug .'/free' : '#' }}" class="btn btn-primary @if(!$canSale) disabled @endif">{{ trans('update.enroll_on_bundle') }}</a>
                                @endif
                            </div>

                        </form>

                    </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

        <section class="container course-content-section {{ $bundle->type }} {{ ($hasBought) ? 'has-progress-bar' : '' }}">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="course-content-body  user-select-none">

<div class=" p-20 course-teacher-card d-flex align-items-center flex-column">
<div class=" mt-lg-40 row align-items-center font-14">
            <a href="https://www.youtube.com/@ASTTROLOKChannel?sub_confirmation=1" target="_blank" class="col text-center " style="width:500px;">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/youtube-icon.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">192k <br/>Subscribers</span>
            </a>

             <a href="https://www.facebook.com/Asttrolok/" target="_blank" class="col text-center">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/facebook-icon.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">25.9k <br/>Likes</span>
            </a>

             <a href="#" target="_blank" class="col text-center">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/Happy-Customer.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">50000 <br/>Happy Students</span>
            </a>

             <a href="#" target="_blank" class="col text-center">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/global-icon.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">52 <br/>Countries</span>
            </a>
        </div>

</div>
                    <div class="mt-35">

@include('web.default2'.'.bundle.tabs.information')

                    </div>

                </div>
            </div>

           <div class="course-content-sidebar col-12 col-lg-4 mt-25 mt-lg-0">
                <div class="register_desktop1">
                <div class="rounded-lg shadow-sm">

                   <div class="px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="item_name" value="bundle_id">

                            @if(!empty($bundle->tickets))
                                @foreach($bundle->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio"
                                               data-discount="{{ $ticket->discount }}"
                                               data-currency
                                               value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                                               name="ticket_id"
                                               id="courseOff{{ $ticket->id }}">
                                        <label class="form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                                            <span class="font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                                @endif</span>
                                            <span class="font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if($bundle->price > 0)
                                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class="text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($bundle->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $bundle->price }}"
                                              data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : ''}}"
                                              class=" @if(!empty($activeSpecialOffer)) font-16 text-gray text-decoration-line-through @else font-30 text-primary @endif">
                                            {{ $realPrice['price'] }}
                                        </span>

                                        @if(!empty($realPrice['tax']) and empty($activeSpecialOffer))
                                            <span class=" font-14 text-gray">+ {{ $realPrice['tax'] }} {{ trans('cart.tax') }}</span>
                                        @endif
                                    </div>

                                    @if(!empty($activeSpecialOffer))
                                        <div class="text-center">
                                            @php
                                                $priceWithDiscount = handleCoursePagePrice($bundle->getPrice());
                                            @endphp
                                            <span id="priceWithDiscount"
                                                  class=" font-30 text-primary">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>

                                            @if(!empty($priceWithDiscount['tax']))
                                                <span class="font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} {{ trans('cart.tax') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex align-items-center justify-content-center mt-20">
                                    <span class="font-36 text-primary">{{ trans('public.free') }}</span>
                                </div>
                            @endif

                            @php
                                $canSale = ($bundle->canSale() and !$hasBought);
                            @endphp

                            <div class="mt-20 d-flex flex-column">
                                @if($hasBought or !empty($bundle->getInstallmentOrder()))
                                    <button type="button" class="btn btn-primary" disabled>{{ trans('panel.purchased') }}</button>
                                @elseif($bundle->price > 0)
                                    <button type="{{ $canSale ? 'submit' : 'button' }}" @if(!$canSale) disabled @endif class="btn btn-primary">
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>

                                    @if($canSale and $bundle->subscribe)
                                        <a href="/subscribes/apply/bundle/{{ $bundle->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($bundle->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $bundle->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_bundles_payment_button_status')))
                                        <button type="button" class="btn btn-outline-danger mt-20 js-bundle-direct-payment">
                                            {{ trans('update.buy_now') }}
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ $canSale ? '/bundles/'. $bundle->slug .'/free' : '#' }}" class="btn btn-primary @if(!$canSale) disabled @endif">{{ trans('update.enroll_on_bundle') }}</a>
                                @endif
                            </div>

                        </form>

                       <div class="mt-35">
                            <strong class="d-block text-secondary font-weight-bold">This Course includes:</strong>
                                                            <div class="mt-20 d-flex align-items-center text-gray">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download-cloud"><polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path></svg>
                                    <span class="ml-5 font-14 font-weight-500">Downloadable content</span>
                                </div>

                                                            <div class="mt-20 d-flex align-items-center text-gray">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-headphones"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                                    <span class="ml-5 font-14 font-weight-500">Instructor support</span>
                                </div>
                                                    </div>

                    </div>
                </div>
</div>
                <div class="register_desktop">
                <div class="rounded-lg shadow-sm">

               <div class="px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="item_name" value="bundle_id">

                            @if(!empty($bundle->tickets))
                                @foreach($bundle->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio"
                                               data-discount="{{ $ticket->discount }}"
                                               data-currency
                                               value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                                               name="ticket_id"
                                               id="courseOff{{ $ticket->id }}">
                                        <label class="form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                                            <span class="font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                                @endif</span>
                                            <span class="font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if($bundle->price > 0)
                                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class="text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($bundle->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $bundle->price }}"
                                              data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : ''}}"
                                              class=" @if(!empty($activeSpecialOffer)) font-16 text-gray text-decoration-line-through @else font-30 text-primary @endif">
                                            {{ $realPrice['price'] }}
                                        </span>

                                        @if(!empty($realPrice['tax']) and empty($activeSpecialOffer))
                                            <span class=" font-14 text-gray">+ {{ $realPrice['tax'] }} {{ trans('cart.tax') }}</span>
                                        @endif
                                    </div>

                                    @if(!empty($activeSpecialOffer))
                                        <div class="text-center">
                                            @php
                                                $priceWithDiscount = handleCoursePagePrice($bundle->getPrice());
                                            @endphp
                                            <span id="priceWithDiscount"
                                                  class=" font-30 text-primary">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>

                                            @if(!empty($priceWithDiscount['tax']))
                                                <span class="font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} {{ trans('cart.tax') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex align-items-center justify-content-center mt-20">
                                    <span class="font-36 text-primary">{{ trans('public.free') }}</span>
                                </div>
                            @endif

                            @php
                                $canSale = ($bundle->canSale() and !$hasBought);
                            @endphp

                            <div class="mt-20 d-flex flex-column">
                                @if($hasBought or !empty($bundle->getInstallmentOrder()))
                                    <button type="button" class="btn btn-primary" disabled>{{ trans('panel.purchased') }}</button>
                                @elseif($bundle->price > 0)
                                    <button type="{{ $canSale ? 'submit' : 'button' }}" @if(!$canSale) disabled @endif class="btn btn-primary">
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>

                                    @if($canSale and $bundle->subscribe)
                                        <a href="/subscribes/apply/bundle/{{ $bundle->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($bundle->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $bundle->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_bundles_payment_button_status')))
                                        <button type="button" class="btn btn-outline-danger mt-20 js-bundle-direct-payment">
                                            {{ trans('update.buy_now') }}
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ $canSale ? '/bundles/'. $bundle->slug .'/free' : '#' }}" class="btn btn-primary @if(!$canSale) disabled @endif">{{ trans('update.enroll_on_bundle') }}</a>
                                @endif
                            </div>

                        </form>

                       <div class="mt-35">
                            <strong class="d-block text-secondary font-weight-bold">This Course includes:</strong>
                                                            <div class="mt-20 d-flex align-items-center text-gray">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download-cloud"><polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path></svg>
                                    <span class="ml-5 font-14 font-weight-500">Downloadable content</span>
                                </div>

                                                            <div class="mt-20 d-flex align-items-center text-gray">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-headphones"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                                    <span class="ml-5 font-14 font-weight-500">Instructor support</span>
                                </div>
                        </div>

                    </div>
                </div>
</div>

                @include('web.default2.includes.cashback_alert',['itemPrice' => $bundle->price])

                @if($bundle->canSale() and !empty(getGiftsGeneralSettings('status')) and !empty(getGiftsGeneralSettings('allow_sending_gift_for_courses')))
                    <a href="/gift/course/{{ $bundle->slug }}" class="d-flex d-none align-items-center mt-30 rounded-lg border p-15">
                        <div class="size-40 d-flex-center rounded-circle bg-gray200">
                            <i data-feather="gift" class="text-gray" width="20" height="20"></i>
                        </div>
                        <div class="ml-5">
                            <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.gift_this_course') }}</h4>
                            <p class="font-12 text-gray">{{ trans('update.gift_this_course_hint') }}</p>
                        </div>
                    </a>
                @endif

                @if($bundle->teacher->offline)
                    <div class="rounded-lg shadow-sm mt-35 d-flex d-none ">
                        <div class="offline-icon offline-icon-left d-flex align-items-stretch">
                            <div class="d-flex align-items-center">
                                <img src="{{ config('app.js_css_url') }}/assets2/default/img/profile/time-icon.png" alt="offline">
                            </div>
                        </div>

                        <div class="p-15">
                            <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
                            <p class="font-14 font-weight-500 text-gray mt-15">{{ $bundle->teacher->offline_message }}</p>
                        </div>
                    </div>
                @endif

                <div class="rounded-lg shadow-sm mt-35 px-25 py-20 d-none ">
                    <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('webinars.'.$bundle->type) .' '. trans('webinars.specifications') }}</h3>

                    <div class="mt-30">

                        @if(!empty($bundle->access_days))
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <i data-feather="alert-circle" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('update.access_period') }}:</span>
                                </div>
                                <span class="font-14">{{ $bundle->access_days }} {{ trans('public.days') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($bundle->creator_id != $bundle->teacher_id && 1==2)
                    @include('web.default2.course.sidebar_instructor_profile', ['courseTeacher' => $bundle->creator])
                @endif

                 @if(1==2)
                @include('web.default2.course.sidebar_instructor_profile', ['courseTeacher' => $bundle->teacher])
 @endif
    @if(1==2)
                @if($bundle->webinarPartnerTeacher->count() > 0)
                    @foreach($bundle->webinarPartnerTeacher as $webinarPartnerTeacher)
                        @include('web.default2.course.sidebar_instructor_profile', ['courseTeacher' => $webinarPartnerTeacher->teacher])
                    @endforeach
                @endif
                @endif

                @if($bundle->tags->count() > 0)
                    <div class="rounded-lg tags-card shadow-sm mt-35 px-25 py-20  d-none ">
                        <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('public.tags') }}</h3>

                        <div class="d-flex flex-wrap mt-10">
                            @foreach($bundle->tags as $tag)
                                <a href="" class="tag-item bg-gray200 p-5 font-14 text-gray font-weight-500 rounded">{{ $tag->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row  d-none ">

           </div>

            </div>
        </div>

        @if(!empty($advertisingBanners) and count($advertisingBanners))
            <div class="mt-30 mt-md-50  d-none ">
                <div class="row">
                    @foreach($advertisingBanners as $banner)
                        <div class="col-{{ $banner->size }}">
                            <a href="{{ $banner->link }}">
                                <img src="{{ config('app.img_dynamic_url') }}{{ $banner->image }}" class="img-cover rounded-sm" alt="{{ $banner->title }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </section>

    @include('web.default.bundle.share_modal')
    @include('web.default.bundle.buy_with_point_modal')
@endsection

@push('scripts_bottom')
<script>

        document.querySelectorAll('.show_hide').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const moreContent = this.nextElementSibling;

                if (moreContent.style.display === "none" || moreContent.style.display === "") {
                    moreContent.style.display = "block";
                    this.textContent = "Read Less";
                } else {
                    moreContent.style.display = "none";
                    this.textContent = "Read More";
                }
            });
        });
    </script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/video.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/vimeo.js"></script>

    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var reportFailLang = '{{ trans('panel.report_fail') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var canNotTryAgainQuizToastTitleLang = '{{ trans('public.can_not_try_again_quiz_toast_lang') }}';
        var canNotTryAgainQuizToastMsgLang = '{{ trans('public.can_not_try_again_quiz_toast_msg_lang') }}';
        var canNotDownloadCertificateToastTitleLang = '{{ trans('public.can_not_download_certificate_toast_lang') }}';
        var canNotDownloadCertificateToastMsgLang = '{{ trans('public.can_not_download_certificate_toast_msg_lang') }}';
        var sessionFinishedToastTitleLang = '{{ trans('public.session_finished_toast_title_lang') }}';
        var sessionFinishedToastMsgLang = '{{ trans('public.session_finished_toast_msg_lang') }}';
        var sequenceContentErrorModalTitle = '{{ trans('update.sequence_content_error_modal_title') }}';

    </script>
<script>

    $(document).scroll(function() {
                    var y = $(this).scrollTop();
                     var y1 = $(this).scrollTop($(this)[0].scrollHeight);
                    if (y > 480 ) {
                        $('.register_desktop').fadeIn();
                        $('.register_desktop').css("display", "block");
                         $('.register_desktop1').css("display", "none");
                    } else {
                        $('.register_desktop').fadeOut();
                        $('.register_desktop').css("display", "none");
                        $('.register_desktop1').css("display", "block");
                    }

                });
</script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>
@endpush
