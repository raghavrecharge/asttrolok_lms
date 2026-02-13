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

    </style>
@endpush
{{ session()->put('my_test_key',url()->current())}}

@section('content')
    <section class="course-cover-container {{ empty($activeSpecialOffer) ? 'not-active-special-offer' : '' }}">
        <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}/store/1/Courses/Cover/Background.webp" class="img-cover course-cover-img" alt="{{ $course->title }}"/>

        <div class="cover-content pt-40">
            <div class="container position-relative">

                <h2 class="font-30 course-title text-center py-10">{{ clean($course->title, 't') }}{{(isset($course->start_date) and $course->isCourse()) ?" (Upcoming)" :"" }}</h2>
                <div class="row">
                    <div class="col-12 col-lg-6 course-section-top">
                        <div class="course-img text-center {{ $course->video_demo ? 'has-video' :'' }}">

                        @if($course->video_demo)
                        <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:280px" src="{{ $course->video_demo_source == 'upload' ?  url($course->video_demo) : $course->video_demo }}" title="Asttrolok" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"  allowfullscreen=""></iframe>

                            @else
                              <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $course->getImage() }}" class="img-cover" style="height:auto" alt="webinar Demo Video">
                        @endif
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $course->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">
                            @php
                                $canSale = ($course->canSale() and !$hasBought);
                        @endphp
                        @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                        @if($course->price > 0)
                        <button type="button" class="btn-primary btn  buy_now mt-20 js-course-direct-payment">{{ trans('update.buy_now') }}</button>

                         @else
                                    @if($course->slug == 'learn-free-vedic-astrology-course-online' )
                                        @if(empty($authUser))
                                            <a href="/register-free" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @elseif($course->slug == 'learn-free-astrology-course-english')
                                        @if(empty($authUser))
                                            <a href="/register-free-english" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @else
                                    <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                    @endif
                                @endif
                                            @endif
                       </form>
                    </div>
                    </div>
                    <div class="col-12 col-lg-6 text-center course-section-top">
                        <div class="course-img {{ $course->video_demo ? 'has-video' :'' }}">

                        <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $course->getImageCover() }}" class="img-cover" style="height:auto;" alt="webinar Demo Video">

                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container course-content-section {{ $course->type }} {{ ($hasBought or $course->isWebinar()) ? 'has-progress-bar' : '' }}">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="course-content-body  user-select-none">
                    <div class="course-body-on-cover text-white">
                        @if(url()->current()!='https://lms.asttrolok.com/course/Free-Astrology-Course')

                        @php
                            $percent = $course->getProgress();
                        @endphp

                        @if($hasBought or $percent)

                        @endif
                        @else
                        <div class="course-body-on-cover text-white" style="min-height: 240px;"></div>
                        @endif
                    </div>
<div class=" p-20 course-teacher-card d-flex align-items-center flex-column">
<div class=" mt-lg-40 row align-items-center font-14">
            <a href="https://www.youtube.com/@ASTTROLOKChannel?sub_confirmation=1" target="_blank" class="col text-center " style="width:500px;">
                <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/youtube-icon.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">196k <br/>Subscribers</span>
            </a>

             <a href="https://www.facebook.com/Asttrolok/" target="_blank" class="col text-center">
                <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/facebook-icon.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">125.9k <br/>Likes</span>
            </a>

             <a href="#" target="_blank" class="col text-center">
                <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/Happy-Customer.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">50000 <br/>Happy Students</span>
            </a>

             <a href="#" target="_blank" class="col text-center">
                <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/global-icon.png" width="50" height="50" alt="telegram">
                <span class="mt-10 d-block">70 <br/>Countries</span>
            </a>
        </div>

</div>
                    <div class="mt-35">

                        <h1 class="font-30 course-h1 text-left py-10">{{ clean($pageH1, 't') }}</h1>
@include('web.default2'.'.course.tabs.information')

                    </div>

                </div>
            </div>
@php
    $iconSize = 20;
@endphp
            <div class="course-content-sidebar col-12 col-lg-4 mt-25 mt-lg-0">
                <div class="register_desktop1">
                <div class="rounded-lg shadow-sm">

                    <div class="px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $course->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">

                            @if(!empty($course->tickets))
                                @foreach($course->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio" data-discount="{{ $ticket->discount }}" value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
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

                            @if($course->price > 0)
                                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class="text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($course->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $course->price }}"
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
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
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
                                $canSale = ($course->canSale() and !$hasBought);
                            @endphp

                            <div class=" d-flex flex-column">
                                @if(!$canSale and $course->canJoinToWaitlist())
                                    <button type="button" data-slug="{{ $course->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                                @elseif($hasBought or !empty($course->getInstallmentOrder()))
                                    <a href="{{ $course->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
                                @elseif($course->price > 0)

                                    @if($canSale and $course->subscribe)
                                        <a href="/subscribes/apply/{{ $course->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($course->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $course->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                                        <button type="button" class="btn btn-outline-danger buy_now mt-20 js-course-direct-payment">
                                            {{ trans('update.buy_now') }}
                                        </button>
                                    @endif

                                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                                        <a href="/course/{{ $course->slug }}/installments" class="btn btn-outline-primary mt-20">
                                            {{ trans('update.pay_with_installments') }}
                                        </a>
                                    @endif

                                <button type="button" class="mt-20 btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($course->cantSaleStatus($hasBought) .' disabled ') }}" >
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>
                                    @else
                                    @if($course->slug == 'learn-free-vedic-astrology-course-online' )
                                        @if(empty($authUser))
                                            <a href="/register-free" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @elseif($course->slug == 'learn-free-astrology-course-english')
                                        @if(empty($authUser))
                                            <a href="/register-free-english" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @else
                                    <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                    @endif
                                @endif
                            </div>

                        </form>

                        @if(!empty(getOthersPersonalizationSettings('show_guarantee_text')) and getOthersPersonalizationSettings('show_guarantee_text'))

                        @endif

                        <div class="mt-35">
                            <strong class="d-block text-secondary font-weight-bold">Course Details -</strong>
                            @if($course->isDownloadable())
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="download-cloud" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.downloadable_content') }}</span>
                                </div>
                            @endif

                            @if($course->quizzes->where('certificate', 1)->count() > 0)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="award" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.official_certificate') }}</span>
                                </div>
                            @endif
                            @if($course->id == 2102)
                                 <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Start Date – 12th December, 2025</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Class Time – Every Saturday, 7-9 AM IST</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Language – English</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="8" r="7"/>
                                        <polyline points="8 12 7 22 12 19 17 22 16 12"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Certification Course</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M9.95 2.58a3.07 3.07 0 0 1 4.1 0l.43.37a2 2 0 0 0 1.18.44h.56a3.07 3.07 0 0 1 3.07 3.07v.56c0 .45.16.89.44 1.18l.37.43a3.07 3.07 0 0 1 0 4.1l-.37.43a2 2 0 0 0-.44 1.18v.56a3.07 3.07 0 0 1-3.07 3.07h-.56a2 2 0 0 0-1.18.44l-.43.37a3.07 3.07 0 0 1-4.1 0l-.43-.37a2 2 0 0 0-1.18-.44h-.56a3.07 3.07 0 0 1-3.07-3.07v-.56c0-.45-.16-.89-.44-1.18l-.37-.43a3.07 3.07 0 0 1 0-4.1l.37-.43a2 2 0 0 0 .44-1.18v-.56A3.07 3.07 0 0 1 8.38 3.4h.56c.45 0 .89-.16 1.18-.44z"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Duration – 3 Months</span>
                                </div>

                            @endif
                            @if($course->id == 2107)
                                 <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Start Date – 19th March 2026</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Class Time – Every Saturday, 7 - 9 PM IST</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Language – Hindi</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="8" r="7"/>
                                        <polyline points="8 12 7 22 12 19 17 22 16 12"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Certification – Course completion</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M9.95 2.58a3.07 3.07 0 0 1 4.1 0l.43.37a2 2 0 0 0 1.18.44h.56a3.07 3.07 0 0 1 3.07 3.07v.56c0 .45.16.89.44 1.18l.37.43a3.07 3.07 0 0 1 0 4.1l-.37.43a2 2 0 0 0-.44 1.18v.56a3.07 3.07 0 0 1-3.07 3.07h-.56a2 2 0 0 0-1.18.44l-.43.37a3.07 3.07 0 0 1-4.1 0l-.43-.37a2 2 0 0 0-1.18-.44h-.56a3.07 3.07 0 0 1-3.07-3.07v-.56c0-.45-.16-.89-.44-1.18l-.37-.43a3.07 3.07 0 0 1 0-4.1l.37-.43a2 2 0 0 0 .44-1.18v-.56A3.07 3.07 0 0 1 8.38 3.4h.56c.45 0 .89-.16 1.18-.44z"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Duration – 3 Months</span>
                                </div>

                            @endif

                            @if($course->quizzes->where('status', \App\models\Quiz::ACTIVE)->count() > 0)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="file-text" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.online_quizzes_count',['quiz_count' => $course->quizzes->where('status', \App\models\Quiz::ACTIVE)->count()]) }}</span>
                                </div>
                            @endif

                            @if($course->support)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="headphones" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.instructor_support') }}</span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
</div>
                <div class="register_desktop">
                <div class="rounded-lg shadow-sm">

                    <div class="px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $course->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">

                            @if(!empty($course->tickets))
                                @foreach($course->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio" data-discount="{{ $ticket->discount }}" value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
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

                            @if($course->price > 0)
                                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class="text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($course->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $course->price }}"
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
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
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
                                $canSale = ($course->canSale() and !$hasBought);
                            @endphp

                            <div class=" d-flex flex-column">
                                @if(!$canSale and $course->canJoinToWaitlist())
                                    <button type="button" data-slug="{{ $course->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                                @elseif($hasBought or !empty($course->getInstallmentOrder()))
                                    <a href="{{ $course->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
                                @elseif($course->price > 0)

                                    @if($canSale and $course->subscribe)
                                        <a href="/subscribes/apply/{{ $course->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($course->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $course->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                                        <button type="button" class="btn btn-outline-danger buy_now mt-20 js-course-direct-payment">
                                            {{ trans('update.buy_now') }}
                                        </button>
                                    @endif

                                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                                        <a href="/course/{{ $course->slug }}/installments" class="btn btn-outline-primary mt-20">
                                            {{ trans('update.pay_with_installments') }}
                                        </a>
                                    @endif

                                <button type="button" class="mt-20 btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($course->cantSaleStatus($hasBought) .' disabled ') }}" >
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>
                                    @else

                                    @if($course->slug == 'learn-free-vedic-astrology-course-online' )
                                        @if(empty($authUser))
                                            <a href="/register-free" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @elseif($course->slug == 'learn-free-astrology-course-english')
                                        @if(empty($authUser))
                                            <a href="/register-free-english" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @else

                                        <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                    @endif
                                @endif
                            </div>

                        </form>

                        @if(!empty(getOthersPersonalizationSettings('show_guarantee_text')) and getOthersPersonalizationSettings('show_guarantee_text'))

                        @endif

                        <div class="mt-35">
                            <strong class="d-block text-secondary font-weight-bold">Course Details -</strong>
                            @if($course->isDownloadable())
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="download-cloud" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.downloadable_content') }}</span>
                                </div>
                            @endif
               @if($course->id == 2102)
                                 <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Start Date – 12th December, 2025</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Class Time – Every Saturday, 7-9 AM IST</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Language – English</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="8" r="7"/>
                                        <polyline points="8 12 7 22 12 19 17 22 16 12"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Certification – Course completion</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M9.95 2.58a3.07 3.07 0 0 1 4.1 0l.43.37a2 2 0 0 0 1.18.44h.56a3.07 3.07 0 0 1 3.07 3.07v.56c0 .45.16.89.44 1.18l.37.43a3.07 3.07 0 0 1 0 4.1l-.37.43a2 2 0 0 0-.44 1.18v.56a3.07 3.07 0 0 1-3.07 3.07h-.56a2 2 0 0 0-1.18.44l-.43.37a3.07 3.07 0 0 1-4.1 0l-.43-.37a2 2 0 0 0-1.18-.44h-.56a3.07 3.07 0 0 1-3.07-3.07v-.56c0-.45-.16-.89-.44-1.18l-.37-.43a3.07 3.07 0 0 1 0-4.1l.37-.43a2 2 0 0 0 .44-1.18v-.56A3.07 3.07 0 0 1 8.38 3.4h.56c.45 0 .89-.16 1.18-.44z"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Duration – 3 Months</span>
                                </div>

                            @endif
                            @if($course->id == 2107)
                                 <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Start Date – 19th March 2026</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Class Time – Every Saturday,7 - 9 PM IST</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Language – Hindi</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <circle cx="12" cy="8" r="7"/>
                                        <polyline points="8 12 7 22 12 19 17 22 16 12"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Certification Course</span>
                                </div>

                                <div class="mt-20 d-flex align-items-center text-gray">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="icon flex-shrink-0">
                                        <path d="M9.95 2.58a3.07 3.07 0 0 1 4.1 0l.43.37a2 2 0 0 0 1.18.44h.56a3.07 3.07 0 0 1 3.07 3.07v.56c0 .45.16.89.44 1.18l.37.43a3.07 3.07 0 0 1 0 4.1l-.37.43a2 2 0 0 0-.44 1.18v.56a3.07 3.07 0 0 1-3.07 3.07h-.56a2 2 0 0 0-1.18.44l-.43.37a3.07 3.07 0 0 1-4.1 0l-.43-.37a2 2 0 0 0-1.18-.44h-.56a3.07 3.07 0 0 1-3.07-3.07v-.56c0-.45-.16-.89-.44-1.18l-.37-.43a3.07 3.07 0 0 1 0-4.1l.37-.43a2 2 0 0 0 .44-1.18v-.56A3.07 3.07 0 0 1 8.38 3.4h.56c.45 0 .89-.16 1.18-.44z"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                    <span class="ml-5 font-14 font-weight-500">Duration – 3 Months</span>
                                </div>

                            @endif
                            @if($course->quizzes->where('certificate', 1)->count() > 0)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="award" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.official_certificate') }}</span>
                                </div>
                            @endif

                            @if($course->quizzes->where('status', \App\models\Quiz::ACTIVE)->count() > 0)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="file-text" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.online_quizzes_count',['quiz_count' => $course->quizzes->where('status', \App\models\Quiz::ACTIVE)->count()]) }}</span>
                                </div>
                            @endif

                            @if($course->support)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="headphones" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.instructor_support') }}</span>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
</div>

                @include('web.default2.includes.cashback_alert',['itemPrice' => $course->price])

                @if($course->canSale() and !empty(getGiftsGeneralSettings('status')) and !empty(getGiftsGeneralSettings('allow_sending_gift_for_courses')))
                    <a href="/gift/course/{{ $course->slug }}" class="d-flex d-none align-items-center mt-30 rounded-lg border p-15">
                        <div class="size-40 d-flex-center rounded-circle bg-gray200">
                            <i data-feather="gift" class="text-gray" width="20" height="20"></i>
                        </div>
                        <div class="ml-5">
                            <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.gift_this_course') }}</h4>
                            <p class="font-12 text-gray">{{ trans('update.gift_this_course_hint') }}</p>
                        </div>
                    </a>
                @endif

                @if($course->teacher->offline)
                    <div class="rounded-lg shadow-sm mt-35 d-flex d-none ">
                        <div class="offline-icon offline-icon-left d-flex align-items-stretch">
                            <div class="d-flex align-items-center">
                                <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets2/default/img/profile/time-icon.png" alt="offline">
                            </div>
                        </div>

                        <div class="p-15">
                            <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
                            <p class="font-14 font-weight-500 text-gray mt-15">{{ $course->teacher->offline_message }}</p>
                        </div>
                    </div>
                @endif

                <div class="rounded-lg shadow-sm mt-35 px-25 py-20 d-none ">
                    <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('webinars.'.$course->type) .' '. trans('webinars.specifications') }}</h3>

                    <div class="mt-30">
                        @if($course->isWebinar())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <i data-feather="calendar" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.start_date') }}:</span>
                                </div>
                                <span class="font-14">{{ dateTimeFormat($course->start_date, 'j M Y | H:i') }}</span>
                            </div>
                        @endif

                        @if($course->isWebinar())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets2/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.sessions') }}:</span>
                                </div>

                            </div>
                        @endif

                        @if($course->isTextCourse())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets2/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.text_lessons') }}:</span>
                                </div>
                                <span class="font-14">{{ $course->textLessons->count() }}</span>
                            </div>
                        @endif

                        @if($course->isCourse() or $course->isTextCourse())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets2/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.files') }}:</span>
                                </div>
                                <span class="font-14">{{ $course->files->count() }}</span>
                            </div>

                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets2/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.created_at') }}:</span>
                                </div>
                                <span class="font-14">{{ dateTimeFormat($course->created_at,'j M Y') }}</span>
                            </div>
                        @endif

                        @if(!empty($course->access_days))
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <i data-feather="alert-circle" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('update.access_period') }}:</span>
                                </div>
                                <span class="font-14">{{ $course->access_days }} {{ trans('public.days') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($course->creator_id != $course->teacher_id && 1==2)
                    @include('web.default2.course.sidebar_instructor_profile', ['courseTeacher' => $course->creator])
                @endif

                 @if(1==2)
                @include('web.default2.course.sidebar_instructor_profile', ['courseTeacher' => $course->teacher])
 @endif
    @if(1==2)
                @if($course->webinarPartnerTeacher->count() > 0)
                    @foreach($course->webinarPartnerTeacher as $webinarPartnerTeacher)
                        @include('web.default2.course.sidebar_instructor_profile', ['courseTeacher' => $webinarPartnerTeacher->teacher])
                    @endforeach
                @endif
                @endif

                @if($course->tags->count() > 0)
                    <div class="rounded-lg tags-card shadow-sm mt-35 px-25 py-20  d-none ">
                        <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('public.tags') }}</h3>

                        <div class="d-flex flex-wrap mt-10">
                            @foreach($course->tags as $tag)
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
                                <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $banner->image }}" class="img-cover rounded-sm" alt="{{ $banner->title }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </section>

    <div id="webinarReportModal" class="d-none">
        <h3 class="section-title after-line font-20 text-dark-blue">{{ trans('product.report_the_course') }}</h3>

        <form action="/course/{{ $course->id }}/report" method="post" class="mt-25">

            <div class="form-group">
                <label class="text-dark-blue font-14">{{ trans('product.reason') }}</label>
                <select id="reason" name="reason" class="form-control">
                    <option value="" selected disabled>{{ trans('product.select_reason') }}</option>

                    @foreach(getReportReasons() as $reason)
                        <option value="{{ $reason }}">{{ $reason }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="text-dark-blue font-14" for="message_to_reviewer">{{ trans('public.message_to_reviewer') }}</label>
                <textarea name="message" id="message_to_reviewer" class="form-control" rows="10"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <p class="text-gray font-16">{{ trans('product.report_modal_hint') }}</p>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class="js-course-report-submit btn btn-sm btn-primary">{{ trans('panel.report') }}</button>
                <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
<div class="container demo  d-none ">

	<div class="text-center d-none">

		<button type="button" class="btn btn-demo" data-toggle="modal" data-target="#myModal2">
			Right Sidebar Modal
		</button>
	</div>

	<div class="modal right fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
		<div class="modal-dialog" role="document">
			<div class="modal-content">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

				</div>

				<div class="modal-body">
					<div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar>
                @if(!empty($userCarts) and count($userCarts) > 0)
                    <div class="mb-auto">
                        @foreach($userCarts as $cart)
                            @php
                                $cartItemInfo = $cart->getItemInfo();
                            @endphp

                            @if(!empty($cartItemInfo))
                                <div class="navbar-cart-box d-flex align-items-center">

                                    <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank" class="navbar-cart-img">
                                        <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" alt="product title" class="img-cover"/>
                                    </a>

                                    <div class="navbar-cart-info">
                                        <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank">
                                            <h4>{{ $cartItemInfo['title'] }}</h4>
                                        </a>
                                        <div class="price mt-10">
                                            @if(!empty($cartItemInfo['discountPrice']))
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                                                <span class="off ml-15">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @else
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @endif

                                            @if(!empty($cartItemInfo['quantity']))
                                                <span class="font-12 text-warning font-weight-500 ml-10">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="navbar-cart-actions">
                        <div class="navbar-cart-total mt-15 border-top d-flex align-items-center justify-content-between">
                            <strong class="total-text">{{ trans('cart.total') }}</strong>
                            <strong class="text-primary font-weight-bold">{{ !empty($totalCartsPrice) ? handlePrice($totalCartsPrice, true, true, false, null, true) : 0 }}</strong>
                        </div>

                        <a href="/cart/" class="btn btn-sm btn-primary btn-block mt-50 mt-md-15">{{ trans('cart.go_to_cart') }}</a>
                    </div>
                @else
                    <div class="d-flex align-items-center text-center py-50">
                        <i data-feather="shopping-cart" width="20" height="20" class="mr-10"></i>
                        <span class="">{{ trans('cart.your_cart_empty') }}</span>
                    </div>
                @endif
            </div>
        </div>
				</div>

			</div>
		</div>
	</div>

</div>

    @include('web.default2.course.share_modal')
    @include('web.default2.course.buy_with_point_modal')
    @include('web.default2.course.login_modal')
     @include('web.default2.course.pop_up')
    @include('web.default2.course.buynow_modal')

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
@if(empty($authUser))
<script>

</script>
@endif
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/1212youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/vimeo.js"></script>
    <script>
        function buy_course(){

            $('.buy_now').click();
        }

    </script>
@if(Session::has('addtocart'))
<script>

$("#myModal2").modal('show');
  $('.modal-dialog').addClass('afterpop');

</script>
@endif
@php
    Illuminate\Support\Facades\Session::forget('addtocart');
@endphp

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
        var courseHasBoughtStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasBoughtStatusToastMsgLang = '{{ trans('site.you_bought_webinar') }}';
        var courseNotCapacityStatusToastTitleLang = '{{ trans('public.request_failed') }}';
        var courseNotCapacityStatusToastMsgLang = '{{ trans('cart.course_not_capacity') }}';
        var courseHasStartedStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasStartedStatusToastMsgLang = '{{ trans('update.class_has_started') }}';
        var joinCourseWaitlistLang = '{{ trans('update.join_course_waitlist') }}';
        var joinCourseWaitlistModalHintLang = "{{ trans('update.join_course_waitlist_modal_hint') }}";
        var joinLang = '{{ trans('footer.join') }}';
        var nameLang = '{{ trans('auth.name') }}';
        var emailLang = '{{ trans('auth.email') }}';
        var phoneLang = '{{ trans('public.phone') }}';
        var captchaLang = '{{ trans('site.captcha') }}';
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
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/webinar_show.min.js"></script>
@endpush
<style>
    .modal-backdrop.show {
    opacity: 0 !important;
    display:none !important;
}
</style>
