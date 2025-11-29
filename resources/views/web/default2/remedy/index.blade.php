@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/video/video-js.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">

<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-remedies.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<meta name="robots" content="noindex, nofollow" />

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
    .webinar-card .image-box {
    height: 100px !important;
}

	.modal-content {
		border-radius: 0;
		border: none;
	}
.r-video{
    display:none;
}
	.modal-header {
		border-bottom-color: #EEEEEE;
		background-color: #FAFAFA;
	}

    </style>
@endpush
{{ session()->put('my_test_key',url()->current())}}

@section('content')
    <section class="mobile-home-slider site-top-banner search-top-banner opacity-04 position-relative">
        <img src="{{ config('app.img_dynamic_url') }}{{ $course->getImageCover() }}" class="banner-redius img-cover" alt="{{ $course->title }}"/>

        <div class="cover-content pt-40">
            <div class="container position-relative">
                @if(!empty($activeSpecialOffer))

                @endif
            </div>
        </div>
    </section>

    <section class="container course-content-section {{ $course->type }} {{ ($hasBought or $course->isRemedy()) ? 'has-progress-bar' : '' }}">
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="course-content-body  user-select-none">
                <div class="rounded-lg shadow-sm">
                    <div class="px-20 mt-50 pb-30">
                        <form action="/cart/store" method="post">
                            <input type="hidden" name="_token" value="SXuDDKudplSl9qnS61QkBZIc2ItObinac6yO5Lgj">
                            <input type="hidden" name="item_id" value="5">
                            <input type="hidden" name="item_name" value="webinar_id">
                            <div id="priceBox" class="d-flex  mt-20 pt-20 ">
                                <div class="text-left">
                                    <span id="realPrice" data-value="2100" data-special-offer="" class="  font-30 text-secondary ">  {{ clean($course->title, 't') }}</span>
                                </div>
                            </div>
                        </form>
                        <div class="mt-35">
                            <p dir="ltr" style="line-height:1.38;margin-top:12pt;margin-bottom:12pt;">{!! $course->description !!}</p>
                         </div>
                    </div>
        </div>

                    @include(getTemplate().'.remedy.tabs.content')
<style>
    .homehide{
        display:none;
    }
</style>

                </div>
            </div>

        </div>

    </section>

<div class="container demo">

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
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" alt="product title" class="img-cover"/>
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

    @include('web.default.course.share_modal')
    @include('web.default.course.buy_with_point_modal')
    @include('web.default.course.login_modal')
    @include('web.default.remedy.file_view')
    @include('web.default.course.buynow_modal')

@endsection

@push('scripts_bottom')
<script>

         function myFunction() {

   var dots = document.getElementById("abouthide");
   var gradiant1 = document.getElementById("gradiant1");
   var moreText = document.getElementById("readmore");
   if (dots.style.overflow == "hidden") {
     dots.style.overflow = "unset";
     dots.style.maxHeight = "100%";
     gradiant1.style.display = "none";
     moreText.text = "Read less";
   } else {
     dots.style.overflow = "hidden";
     dots.style.maxHeight = "100px";
     gradiant1.style.display = "block";

     moreText.text = "Read more";
   }
 }
 function view(condi){
if(condi=='pdf'){
$('.r-pdf').show();
$(".r-video").hide();
$(".vdo").removeClass("active");
$(".pdfs").addClass("active");
}else{
    $('.r-pdf').hide();
$(".r-video").show();
$(".vdo").addClass("active");
$(".pdfs").removeClass("active");
}
 }
     </script>
@if(empty($authUser))
<script>

</script>
@endif

<script>

function viewfile(src1,id){

    var viewfile2 = document.getElementById("pre1");

    console.log(viewfile2.src);
    viewfile2.src = src1;
    $('#textpop1').modal();
    console.log(viewfile2.src);

}

</script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
     <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/1212youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/vimeo.js"></script>
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

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
<style>

</style>
