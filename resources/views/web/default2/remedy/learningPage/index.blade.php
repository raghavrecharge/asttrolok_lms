@extends('web.default2.layouts.app',['appFooter' => false, 'appHeader' => false])

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/learning_page/styles.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">
   <style>
       .learning-page .learning-content {
  
    overflow-y: unset !important;
}
.learning-page .learning-content-iframe iframe {
    width: 100%;
    height: 100% !important;
}
.learning-content-iframe div{
    position: relative;
    max-width: 100% !important;
    height: 100% !important;
}
   </style>
@endpush

@section('content')

    <div class="learning-page">

        @include('web.default2.course.learningPage.components.navbar')

        <div class="d-flex position-relative">
            <div class="learning-page-content flex-grow-1 bg-info-light p-15">
                @include('web.default2.course.learningPage.components.content')
            </div>

            <div class="learning-page-tabs show">
                <ul class="nav nav-tabs py-15 d-flex align-items-center justify-content-around" id="tabs-tab" role="tablist">
                    <li class="nav-item">
                        <a class="position-relative font-14 d-flex align-items-center active" id="content-tab"
                           data-toggle="tab" href="#content" role="tab" aria-controls="content"
                           aria-selected="true">
                            <i class="learning-page-tabs-icons mr-5">
                                @include('web.default2.panel.includes.sidebar_icons.webinars')
                            </i>
                            <span class="learning-page-tabs-link-text">{{ trans('product.content') }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="position-relative font-14 d-flex align-items-center" id="quizzes-tab" data-toggle="tab"
                           href="#quizzes" role="tab" aria-controls="quizzes"
                           aria-selected="false">
                            <i class="learning-page-tabs-icons mr-5">
                                @include('web.default2.panel.includes.sidebar_icons.quizzes')
                            </i>
                            <span class="learning-page-tabs-link-text">{{ trans('quiz.quizzes') }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="position-relative font-14 d-flex align-items-center" id="certificates-tab" data-toggle="tab"
                           href="#certificates" role="tab" aria-controls="certificates"
                           aria-selected="false">
                            <i class="learning-page-tabs-icons mr-5">
                                @include('web.default2.panel.includes.sidebar_icons.certificate')
                            </i>
                            <span class="learning-page-tabs-link-text">{{ trans('panel.certificates') }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content h-100" id="nav-tabContent">
                    <div class="pb-20 tab-pane fade show active h-100" id="content" role="tabpanel"
                         aria-labelledby="content-tab">
                        @include('web.default2.course.learningPage.components.content_tab.index')
                    </div>

                    <div class="pb-20 tab-pane fade  h-100" id="quizzes" role="tabpanel"
                         aria-labelledby="quizzes-tab">
                        @include('web.default2.course.learningPage.components.quiz_tab.index')
                    </div>

                    <div class="pb-20 tab-pane fade  h-100" id="certificates" role="tabpanel"
                         aria-labelledby="certificates-tab">
                        @include('web.default2.course.learningPage.components.certificate_tab.index')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/vimeo.js"></script>

    <script>
        var defaultItemType = '{{ request()->get('type') }}'
        var defaultItemId = '{{ request()->get('item') }}'
        var loadFirstContent = {{ (!empty($dontAllowLoadFirstContent) and $dontAllowLoadFirstContent) ? 'false' : 'true' }}; // allow to load first content when request item is empty

        var courseUrl = '{{ $course->getUrl() }}';

        // lang
        var pleaseWaitForTheContentLang = '{{ trans('update.please_wait_for_the_content_to_load') }}';
        var downloadTheFileLang = '{{ trans('update.download_the_file') }}';
        var downloadLang = '{{ trans('home.download') }}';
        var showHtmlFileLang = '{{ trans('update.show_html_file') }}';
        var showLang = '{{ trans('update.show') }}';
        var sessionIsLiveLang = '{{ trans('update.session_is_live') }}';
        var youCanJoinTheLiveNowLang = '{{ trans('update.you_can_join_the_live_now') }}';
        var joinTheClassLang = '{{ trans('update.join_the_class') }}';
        var coursePageLang = '{{ trans('update.course_page') }}';
        var quizPageLang = '{{ trans('update.quiz_page') }}';
        var sessionIsNotStartedYetLang = '{{ trans('update.session_is_not_started_yet') }}';
        var thisSessionWillBeStartedOnLang = '{{ trans('update.this_session_will_be_started_on') }}';
        var sessionIsFinishedLang = '{{ trans('update.session_is_finished') }}';
        var sessionIsFinishedHintLang = '{{ trans('update.this_session_is_finished_You_cant_join_it') }}';
        var goToTheQuizPageForMoreInformationLang = '{{ trans('update.go_to_the_quiz_page_for_more_information') }}';
        var downloadCertificateLang = '{{ trans('update.download_certificate') }}';
        var enjoySharingYourCertificateWithOthersLang = '{{ trans('update.enjoy_sharing_your_certificate_with_others') }}';
        var attachmentsLang = '{{ trans('public.attachments') }}';
        var checkAgainLang = '{{ trans('update.check_again') }}';
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
        var sequenceContentErrorModalTitle = '{{ trans('update.sequence_content_error_modal_title') }}';
        var sendAssignmentSuccessLang = '{{ trans('update.send_assignment_success') }}';
        var saveAssignmentRateSuccessLang = '{{ trans('update.save_assignment_grade_success') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var changesSavedSuccessfullyLang = '{{ trans('update.changes_saved_successfully') }}';
        var oopsLang = '{{ trans('update.oops') }}';
        var somethingWentWrongLang = '{{ trans('update.something_went_wrong') }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var cantStartQuizToastTitleLang = '{{ trans('public.request_failed') }}';
        var cantStartQuizToastMsgLang = '{{ trans('quiz.cant_start_quiz') }}';
        var learningPageEmptyContentTitleLang = '{{ trans('update.learning_page_empty_content_title') }}';
        var learningPageEmptyContentHintLang = '{{ trans('update.learning_page_empty_content_hint') }}';
        var expiredQuizLang = '{{ trans('update.expired_quiz') }}';
    </script>
    <script type="text/javascript" src="{{ config('app.js_css_url') }}/assets/default/vendors/dropins/dropins.js"></script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/learning_page/scripts.min.js"></script>
<script>
    $('.learning-content-iframe iframe').attr('allowFullScreen', '');
</script>
<script>

    if($('#readTogglefile'+defaultItemId).length){
	 
}else{
    //  alert($('#chapter_'+defaultItemId).length);
    $('.pratul').removeClass('active');
   
	$('#learningPageContent').html('<div class="course-private-content text-center w-100 border rounded-lg" style="  margin: 0px 0;  padding: 0px 0;"><div class="course-private-content-icon m-auto"><img src="{{ config('app.js_css_url') }}/assets/default/img/course/private_content_icon.svg" alt="private content icon" class="img-cover"></div><div class="mt-30"><h2 class="font-20 text-dark-blue">Access Denied </h2><p class="font-14 font-weight-500 text-gray">You have an overdue installment. Please pay it to access this course!</p><a href="/panel/financial/installments" class="btn btn-primary mt-15">Pay Now</a></div> </div>');
}
function accessdenied(){
     $('.pratul').removeClass('active');
  $('#learningPageContent').html('<div class="course-private-content text-center w-100 border rounded-lg" style="  margin: 0px 0;  padding: 0px 0;"><div class="course-private-content-icon m-auto"><img src="{{ config('app.js_css_url') }}/assets/default/img/course/private_content_icon.svg" alt="private content icon" class="img-cover"></div><div class="mt-30"><h2 class="font-20 text-dark-blue">Access Denied </h2><p class="font-14 font-weight-500 text-gray">You have an overdue installment. Please pay it to access this course!</p><a href="/panel/financial/installments" class="btn btn-primary mt-15">Pay Now</a></div> </div>');
  
}

$(function() {                       //run when the DOM is ready
  $(".accessdenied").click(function() {
       $('.pratul').removeClass('active');
       $('.accessdenied').removeClass('active');
  $('#learningPageContent').html('<div class="course-private-content text-center w-100 border rounded-lg" style="  margin: 0px 0;  padding: 0px 0;"><div class="course-private-content-icon m-auto"><img src="{{ config('app.js_css_url') }}/assets/default/img/course/private_content_icon.svg" alt="private content icon" class="img-cover"></div><div class="mt-30"><h2 class="font-20 text-dark-blue">Access Denied </h2><p class="font-14 font-weight-500 text-gray">You have an overdue installment. Please pay it to access this course!</p><a href="/panel/financial/installments" class="btn btn-primary mt-15">Pay Now</a></div> </div>');
  //use a class, since your ID gets mangled
    $(this).addClass("active");      //add the class to the clicked element
  });
});
</script>
    @if((!empty($isForumPage) and $isForumPage) or (!empty($isForumAnswersPage) and $isForumAnswersPage))
        <script src="{{ config('app.js_css_url') }}/assets/learning_page/forum.min.js"></script>
    @endif
@endpush
