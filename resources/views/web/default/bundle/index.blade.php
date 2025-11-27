@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/video/video-js.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-course-detailes.css">

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

@section('content')
    <section class=" course-cover-container {{ empty($activeSpecialOffer) ? 'not-active-special-offer' : '' }}">
        <img loading="lazy" src="{{ asset('assets/default/img/course/video-banner.svg') }}" class=" course-cover-img" alt="{{ $bundle->title }}"/>

        <div class="cover-content pt-40 pt-80">
            <div class="container position-relative">
                <div class="row">
                    <div class="col-12 col-lg-6 course-section-top">

                        @if($bundle->video_demo)
                            <div id="webinarDemoVideoBtn"
                                 data-video-path="{{ $bundle->video_demo_source == 'upload' ?  url($bundle->video_demo) : $bundle->video_demo }}"
                                 data-video-source="{{ $bundle->video_demo_source }}"
                                 class="mt-5 course-video-icon cursor-pointer d-flex align-items-center justify-content-center">
                               <img loading="lazy" src="{{ asset('assets/default/img/course/video-thumbnail.svg') }}" class="img-cover viedo-thumbnais"  alt="webinar Demo Video">
                            </div>
                            <h2 class="font-30 video-course-title">
                            Play this video
                        </h2>
                        @else
                        <div class="mt-80">
                        </div>
                        @endif
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="item_name" value="bundle_id">
                            @php
                                $canSale = ($bundle->canSale() and !$hasBought);
                        @endphp
                       </form>
                       <div class="pt-80 d-flex align-items-left align-items-start justify-content-between ">
                        <div class="">
                        <h3 class="font-18 text-dark-blue font-weight-bold">{{ clean($bundle->title, 't') }}</h3>
                    </div>
                    <div class="">
                    <img loading="lazy" src="{{ asset('assets/default/img/course/save-icon.svg') }}" class="img-cover save-icon"  alt="webinar Demo Video">
                    </div>
                </div>
                @include('web.default.includes.webinar.rate4',['rate' => $bundle->course_rate])

                        <div class="pt-50  align-items-left align-items-start justify-content-between ">
                            <div class="d-flex frame-427322372-acu" id="1017:948">
                                <div class="auto-group-eond-RX3" id="DPMWvH8SZiH7QxWwGEzUkM">
                                <img loading="lazy" class="frame-427322370-8wF" src="{{ asset('assets/default/img/course/play-button.svg') }}" id="1017:938"/>
                                @if($bundle->id == 2070 or $bundle->id == 2069)
                                <div class="on-demand-videos-XwT" id="864:10414">Live<br/>Course</div>
                                @else
                                <div class="on-demand-videos-XwT" id="864:10414">Video<br/>Course</div>
                                @endif
                                </div>
                                <div class=" auto-group-eond-RX3 ml-10" id="DPMX32SCYt7QUQ5s3KEond">
                                <img loading="lazy" class="frame-427322370-8wF" src="{{ asset('assets/default/img/course/green-video-icon (2).svg') }}" id="1017:939"/>
                                <div class="on-demand-videos-XwT" id="864:10422">
                                Certified

                                <br/>
                                Course

                                </div>
                                </div>
                                <div class="auto-group-eond-RX3 ml-10" id="DPMX8BxG9LFjFzh6H3sZ17">
                                <img loading="lazy" class="frame-427322370-8wF" src="{{ asset('assets/default/img/course/ind-ruppes.svg') }}" id="864:10420"/>
                                @if($activeSpecialOffer)
                                @php
                                $priceWithDiscount = handleCoursePagePrice($bundle->getPrice());
                                @endphp
                                <div class="item-17999--e2R" id="864:10423">{{ $priceWithDiscount['price'] }}/-</div>
                                @else
                                <div class="item-17999--e2R" id="864:10423">{{$bundle->price}}/-</div>
                                @endif
                                </div>
                            </div>
                            </div>
                                 @include(getTemplate().'.bundle.tabs.information')
                    </div>

                    </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=" p-20 course-teacher-card align-items-center flex-column">
<div class="row mt-lg-40 align-items-center font-14">
            <a href="https://www.youtube.com/@ASTTROLOKChannel?sub_confirmation=1" target="_blank" class="row m-0 col-6 p-5">
                <div class=" col-6 text-center ">
                <img loading="lazy" src="https://asttrolok.in/assets/default/css/landingPage/resources/img/Icon/icon1/youtube-icon.png" width="50" height="50" alt="telegram" >
                </div>
                <span class="col-6 mt-5 d-block p-0"><b>192k</b> <br/>Subscribers</span>
            </a>

             <a href="https://www.facebook.com/Asttrolok/" target="_blank" class="row m-0 col-6 p-5">
                 <div class=" col-6 text-center ">
                <img loading="lazy" src="https://asttrolok.in/assets/default/css/landingPage/resources/img/Icon/icon1/facebook-icon.png" width="50" height="50" alt="telegram">
                </div>
                <span class="col-6 mt-5 d-block p-0"><b>25.9k</b> <br/>Likes</span>
            </a>

             <a href="#" target="_blank" class="row m-0 mt-20 col-6 p-5">
                 <div class=" col-6 text-center ">
                <img loading="lazy" src="https://asttrolok.in/assets/default/css/landingPage/resources/img/Icon/icon1/Happy-Customer.png" width="50" height="50" alt="telegram">
                </div>
                <span class="col-6 mt-5 d-block p-0"><b>50000</b> <br/>Happy Students</span>
            </a>

             <a href="#" target="_blank" class="row m-0 mt-20 col-6 p-5">
                 <div class=" col-6 text-center ">
                <img loading="lazy" src="https://asttrolok.in/assets/default/css/landingPage/resources/img/Icon/icon1/global-icon.png" width="50" height="50" alt="telegram">
                </div>
                <span class="col-6 mt-5 d-block p-0"><b>52</b> <br/>Countries</span>
            </a>
        </div>

</div>
    </section>

    <section class="container course-content-section course-content-top {{ $bundle->type }} {{ ($hasBought) ? 'has-progress-bar' : '' }}">
        <div class="row">

            <div class="course-content-sidebar col-12 col-lg-4 mt-25 mt-lg-0 homehide">
                <div class="rounded-lg shadow-sm">

                    <div class="px-20 pb-30">
                        <form action="{{ (auth()->check()) ? '/cart/store' : '/course/buy-now' }}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="item_name" value="bundle_id">

                            @if(!empty($bundle->tickets))
                                @foreach($bundle->tickets as $ticket)

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
                                @if(!$canSale and $bundle->canJoinToWaitlist())
                                    <button type="button" data-slug="{{ $bundle->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                                @elseif($hasBought or !empty($bundle->getInstallmentOrder()))
                                    <a href="{{ $bundle->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
                                @elseif($bundle->price > 0)
                                    <button type="button" class="btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($bundle->cantSaleStatus($hasBought) .' disabled ') }}" >
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>

                                    @if($canSale and $bundle->subscribe)
                                        <a href="/subscribes/apply/{{ $bundle->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($bundle->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $bundle->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))

                                    @if(auth()->check())
                                       <button type="button" class="btn btn-outline-danger buy_now mt-20 js-course-direct-payment">
                                           {{ trans('update.buy_now') }}
                                       </button>
                                        @else

                                       <button type="submit" class="btn btn-outline-danger buy_now mt-20">
                                           {{ trans('update.buy_now') }}
                                       </button>
                                       @endif
                                   @endif

                                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                                        <a href="/course/{{ $bundle->slug }}/installments" class="btn btn-outline-primary mt-20">
                                            {{ trans('update.pay_with_installments') }}
                                        </a>
                                    @endif
                                @else
                                    @if($bundle->slug == 'learn-free-vedic-astrology-course-online' )
                                        @if(empty($authUser))
                                            <a href="/register-free" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $bundle->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @elseif($bundle->slug == 'learn-free-astrology-course-english')
                                        @if(empty($authUser))
                                            <a href="/register-free-english" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $bundle->slug .'/free' : '#' }}" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @else
                                    <a href="{{ $canSale ? '/course/'. $bundle->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                    @endif
                                @endif
                            </div>

                        </form>

                        @if(!empty(getOthersPersonalizationSettings('show_guarantee_text')) and getOthersPersonalizationSettings('show_guarantee_text'))

                        @endif

                        <div class="mt-35">
                            <strong class="d-block text-secondary font-weight-bold">{{ trans('webinars.this_webinar_includes',['classes' => trans('webinars.'.$bundle->type)]) }}</strong>

                            @if($bundle->support)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="headphones" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.instructor_support') }}</span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                @include('web.default.includes.cashback_alert',['itemPrice' => $bundle->price])

                @if($bundle->canSale() and !empty(getGiftsGeneralSettings('status')) and !empty(getGiftsGeneralSettings('allow_sending_gift_for_courses')))
                    <a href="/gift/course/{{ $bundle->slug }}" class="d-flex align-items-center mt-30 rounded-lg border p-15">
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
                    <div class="rounded-lg shadow-sm mt-35 d-flex">
                        <div class="offline-icon offline-icon-left d-flex align-items-stretch">
                            <div class="d-flex align-items-center">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/profile/time-icon.png" alt="offline">
                            </div>
                        </div>

                        <div class="p-15">
                            <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
                            <p class="font-14 font-weight-500 text-gray mt-15">{{ $bundle->teacher->offline_message }}</p>
                        </div>
                    </div>
                @endif

                <div class="rounded-lg shadow-sm mt-35 px-25 py-20">
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

                @if($bundle->creator_id != $bundle->teacher_id)
                    @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $bundle->creator])
                @endif

                @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $bundle->teacher])

                @if($bundle->tags->count() > 0)
                    <div class="rounded-lg tags-card shadow-sm mt-35 px-25 py-20">
                        <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('public.tags') }}</h3>

                        <div class="d-flex flex-wrap mt-20">
                            @foreach($bundle->tags as $tag)
                                <a href="" class="tag-item bg-gray200 p-5 font-14 text-gray font-weight-500 rounded">{{ $tag->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row">

           </div>

            </div>
        </div>

        @if(!empty($advertisingBanners) and count($advertisingBanners))
            <div class="mt-30 mt-md-50">
                <div class="row">
                    @foreach($advertisingBanners as $banner)
                        <div class="col-{{ $banner->size }}">
                            <a href="{{ $banner->link }}">
                                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $banner->image }}" class="img-cover rounded-sm" alt="{{ $banner->title }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </section>

    <div id="webinarReportModal" class="d-none">
        <h3 class="section-title after-line font-20 text-dark-blue">{{ trans('product.report_the_course') }}</h3>

        <form action="/course/{{ $bundle->id }}/report" method="post" class="mt-25">

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

<div class="container demo">

	<div class="text-center d-none">

		<button type="button" class="btn btn-demo" data-toggle="modal" data-target="#myModal2">
			Right Sidebar Modal
		</button>
	</div>

	<div class="modal right fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" style="z-index: 52022;">
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
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" alt="product title" class="img-cover"/>
                                    </a>

                                    <div class="navbar-cart-info">
                                        <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank">
                                            <h4>{{ $cartItemInfo['title'] }}</h4>
                                        </a>
                                        <div class="price mt-20">
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
    @if($hasBought or !empty($bundle->getInstallmentOrder()))
    <a href="{{ $bundle->getLearningPageUrl() }}" class="buy-btn1 btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
    @else
        @if($bundle->slug == 'learn-free-vedic-astrology-course-online')
            @if(empty($authUser))
                <a href="/register-free" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
            @else
                <a href="{{ $canSale ? '/course/'. $bundle->slug .'/free' : '#' }}" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
            @endif
        @elseif($bundle->slug == 'learn-free-astrology-course-english')
            @if(empty($authUser))
                <a href="/register-free-english" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
            @else
                <a href="{{ $canSale ? '/course/'. $bundle->slug .'/free' : '#' }}" class="buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $bundle->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
            @endif
        @else
            @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
            <button type="button" data-toggle="modal" data-target="#buynow_modal" class=" btn btn-primary btn-sm disabled px-25 buy-btn1" >
                BUY NOW
            </button>

            @endif
        @endif
    @endif

    @include('web.default.bundle.share_modal')
    @include('web.default.bundle.buy_with_point_modal')
    @include('web.default.bundle.buynow_modal')

@endsection

@push('scripts_bottom')
<script   >
        // Select all "Read More" links and add event listeners
        document.querySelectorAll('.show_hide').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent the default link behavior

                // Find the corresponding hidden content for this link
                const moreContent = this.nextElementSibling;

                if (moreContent.style.display === "none" || moreContent.style.display === "") {
                    moreContent.style.display = "block";
                    this.textContent = "Read Less"; // Change the link text
                } else {
                    moreContent.style.display = "none";
                    this.textContent = "Read More"; // Change back to "Read More"
                }
            });
        });
    </script>
@if(empty($authUser))
<script   >

//          function myFunction() {

//   var dots = document.getElementById("abouthide");
//   var gradiant1 = document.getElementById("gradiant1");
//   var moreText = document.getElementById("readmore");
//   if (dots.style.overflow == "hidden") {
//      dots.style.overflow = "unset";
//      dots.style.maxHeight = "100%";
//      gradiant1.style.display = "none";
//      moreText.text = "Read less";
//   } else {
//      dots.style.overflow = "hidden";
//      dots.style.maxHeight = "176px";
//      gradiant1.style.display = "block";

//      moreText.text = "Read more";
//   }
//  }
//      setTimeout(function() {
//     $('#textpop').modal();
// }, 5000);
</script>
@endif
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/time-counter-down.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script   src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
   <script   src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
     <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/video/1212youtube.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/video/vimeo.js"></script>
<script   >
    function buy_course(){
        // alert('');
        $('.buy_now').click();
    }

</script   >
@if(Session::has('addtocart'))
<script   >

$("#myModal2").modal('show');
  $('.modal-dialog').addClass('afterpop');
    // $('.btn-demo').click();
    // $('.modal-dialog').addClass('afterpop');
</script>
@endif
@php
    Illuminate\Support\Facades\Session::forget('addtocart');
@endphp

    <script   >
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
<script   >
// $('#myModal21').modal();
// $("#myModal21").modal('show');
//   $('.modal-dialog').addClass('afterpop');
    // $('.btn-demo').click();
    // $('.modal-dialog').addClass('afterpop');
</script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/comment.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>
@endpush

<style>
    .modal-backdrop.show {
    opacity: 0 !important;
    display:none !important;
}
 .cover-content {
    position: relative;
    height: auto !important;
    width: 100%;
}
.img-cover {
    width: 100%;
    height: auto !important;
    overflow: hidden;
    -o-object-fit: cover;
    object-fit: cover;
    -o-object-position: 50% 50%;
    object-position: 50% 50%;
}
</style>
