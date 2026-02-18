@extends('web.default2'.'.layouts.app')

@push('styles_top')
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
       <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/profile.min.css">

@endpush

{{ session()->put('my_test_key',url()->current())}}

@section('content')

<div class="profile-cover-card">
        <img src="https://storage.googleapis.com/astrolok/webp/store/1/banner/Remedies.webp" class="img-cover" alt="">
</div>

 <div class="profile-container">
        <div class="container mb-104">
            <div class="row">
                <div class="col-12 col-md-4 col-lg-3">
 <div class="profile-card-has-mask bg-white py-16 rounded-24 w-100">
                        <div class="d-flex-center flex-column text-center px-16">
                    @include('web.default2.remedy.tabs.content')
                     </div>
                    </div>
                </div>

                <div class="col-12 col-md-8 col-lg-9 mt-32 mt-md-0">
                    <div class="profile-card-has-mask position-relative bg-white pt-24 pb-20 rounded-24">
                        <div class="d-flex-center flex-column  px-16">
                           <div class="px-lg-50 mt-40">
                                <h2>{{ clean($course->title, 't') }}</h2>
<br>
<p class="duration font-14 ml-5">{!! $course->description !!}</p>
                           </div>

                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
//      setTimeout(function() {
//     $('#textpop').modal();
// }, 5000);
</script>
@endif

<script>
// Get the modal

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
        // alert('');
        $('.buy_now').click();
    }

</script>
@if(Session::has('addtocart'))
<script>

$("#myModal2").modal('show');
  $('.modal-dialog').addClass('afterpop');
    // $('.btn-demo').click();
    // $('.modal-dialog').addClass('afterpop');
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
@endpush
<style>
@media screen and (max-width: 992px) {
  #pre1 {
      width: -webkit-fill-available;
    height: 283px;

  }
  .pdf {
      display:none !important;
  }
}
@media screen and (min-width: 991px) {
  #pre1 {
      width:-webkit-fill-available;
      height:450px;
  }
  #mob1 {

      display:none !important;
  }

}

</style>
