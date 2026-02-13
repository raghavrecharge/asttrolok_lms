@extends('web.default.layouts.app',['appFooter' => false, 'appHeader' => false])

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/learning_page/styles.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">

   <style>
       .learning-page .learning-content {

    overflow-y: unset !important;
}
.learning-page .learning-content-iframe iframe {
    width: 100%;
    height: 88% !important;
}
.learning-content-iframe div{
    position: relative;
    max-width: 100% !important;
    height: 100% !important;
}
.learning-page .learning-page-tabs.show {
    width: 100% !important;

}
@media (min-width: 0px) and (max-width: 2000px){

    .webinar-card.webinar-list {
        flex-direction: row;
    }
.webinar-card .image-box .badge {
        position: absolute !important;
        top: 10px !important;
        right: 9px !important;
        font-size: 12px !important;
        font-weight: normal !important;
        line-height: 1 !important;
        color: #ffffff;
        pointer-events: none !important;
    }

.webinar-card.list-card .image-box {
        position: relative;
        min-width: 146px !important;

        height: 130px !important;
        min-height: 130px !important;
    }

    .hide {
        display: none !important;
    }
    .webinar-card.webinar-list .image-box img {
        border-radius: 10px 10px 10px 10px !important;
    }
    .dropdown-card {
        margin-top: -29px;
        background-color: white;
        border-radius: 22px;
        width: 29px;
        height: 29px;
    }
            .webinar-card .webinar-card-body .webinar-title {
        height: 40px !important;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .real {
        font-size: 11.65px !important;
        color: #2bc161 !important;
        font-family: "Inter", sans-serif;
    }
        .btn {

        padding-right: 10px;
        padding-left: 10px;
        height: 37px;
    }
    .dropdown-menu {
        padding: 15px;
        min-width: 296px !important;
    }
    .webinar-card.webinar-list .webinar-card-body {
        padding: 5px 5px 10px 5px !important;
    }
        .user-inline-avatar .avatar {
        width: 16.81px !important;
        max-width: 16.81px !important;
        min-width: 16.81px !important;
        height: 16.81px !important;
        border-radius: 50%;
    }
        .stars-card {
        min-height: 0px !important;
    }
        .radius-20 {
        border-radius: 20px !important;
    }
        .stars-card svg {
        margin-right: 0px !important;
        color: #3f3f3f;
    }
    button.buynow {
        float: inline-end !important;
        max-width: 65px !important;
        height: 20.58px !important;
        border-radius: 0.25rem !important;
        font-family: "Inter", sans-serif;
    }
    .webinar-title {
    font-size: 14.99px !important;
}
.user-name {
    font-size: 10.992px !important;
}
}
   </style>
@endpush

@section('content')

    <div class="learning-page">

        @include('web.default.course.learningPage.components.navbar')

        <div class="d-flex position-relative">

            <div class="learning-page-tabs show">

                <div class="tab-content h-100" id="nav-tabContent">
                    <div class="pb-20 tab-pane fade show active h-100" id="content" role="tabpanel"
                         aria-labelledby="content-tab">
                        @include('web.default.course.learningPage.components.content_for_progress.index')
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('web.default2.course.consultation_popup')
@endsection

@push('scripts_bottom')

<script defer>

                </script>

    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/video/youtube.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/video/vimeo.js"></script>

    <script defer>
        var defaultItemType = '{{ request()->get('type') }}'
        var defaultItemId = '{{ request()->get('item') }}'

        var loadFirstContent = {{ (!empty($dontAllowLoadFirstContent) and $dontAllowLoadFirstContent) ? 'false' : 'true' }};

        var courseUrl = '{{ $course->getUrl() }}';

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
    <script defer type="text/javascript" src="{{ config('app.js_css_url') }}/assets/default/vendors/dropins/dropins.js"></script>
    <script defer src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>

    <script defer src="{{ config('app.js_css_url') }}/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/learning_page/scripts.min.js"></script>
<script defer>
    $('.learning-content-iframe iframe').attr('allowFullScreen', '');
</script>
<script defer>

    if($('#readTogglefile'+defaultItemId).length){

}else{

    $('.pratul').removeClass('active');

	$('#learningPageContent').html('<div class="course-private-content text-center w-100 border rounded-lg" style="  margin: 0px 0;  padding: 0px 0;"><div class="course-private-content-icon m-auto"><img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/course/private_content_icon.svg" alt="private content icon" class="img-cover"></div><div class="mt-30"><h2 class="font-20 text-dark-blue">Access Denied </h2><p class="font-14 font-weight-500 text-gray">You have an overdue installment. Please pay it to access this course!</p><a href="#" class="btn btn-primary mt-15">Pay Now</a></div> </div>');
}
function accessdenied(){
     $('.pratul').removeClass('active');
  $('#learningPageContent').html('<div class="course-private-content text-center w-100 border rounded-lg" style="  margin: 0px 0;  padding: 0px 0;"><div class="course-private-content-icon m-auto"><img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/course/private_content_icon.svg" alt="private content icon" class="img-cover"></div><div class="mt-30"><h2 class="font-20 text-dark-blue">Access Denied </h2><p class="font-14 font-weight-500 text-gray">You have an overdue installment. Please pay it to access this course!</p><a href="#" class="btn btn-primary mt-15">Pay Now</a></div> </div>');

}

$(function() {
  $(".accessdenied").click(function() {
       $('.pratul').removeClass('active');
       $('.accessdenied').removeClass('active');
  $('#learningPageContent').html('<div class="course-private-content text-center w-100 border rounded-lg" style="  margin: 0px 0;  padding: 0px 0;"><div class="course-private-content-icon m-auto"><img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/course/private_content_icon.svg" alt="private content icon" class="img-cover"></div><div class="mt-30"><h2 class="font-20 text-dark-blue">Access Denied </h2><p class="font-14 font-weight-500 text-gray">You have an overdue installment. Please pay it to access this course!</p><a href="#" class="btn btn-primary mt-15">Pay Now</a></div> </div>');

    $(this).addClass("active");
  });
});
</script>

 <script defer>
    let getPaused = false;
    let duration = 0;
    let intervalId = null;
    let progressSaved = false;
    let totalVideoDuration = 0;
    let previousPercentage = sessionStorage.getItem('previousPercentage') || 0;

    function pauseAndFetchDuration() {
      const iframe = document.getElementsByTagName('iframe')[0];
      if (iframe) {
        iframe.contentWindow.postMessage({
          context: 'player.js',
          method: 'getCurrentTime'
        }, '*');

         iframe.contentWindow.postMessage({
          context: 'player.js',
          method: 'getDuration'
        }, '*');
      }
    }

    window.addEventListener('message', function(event) {
      let jsonData = event.data;

      if (typeof jsonData === 'string') {
        try {
          jsonData = JSON.parse(jsonData);
        } catch (e) {
          console.error('Error parsing JSON:', e);
          return;
        }
      }

      if (jsonData && jsonData.event) {
        if (jsonData.event === 'getCurrentTime') {
          duration = jsonData.value;
         sessionStorage.setItem('duration', duration);
        }
        if (jsonData.event === 'getDuration') {
              totalVideoDuration = parseInt(jsonData.value);
        }

        if (jsonData.event === 'getPaused') {
          if (jsonData.value === true) {
            if (!progressSaved && duration > 0) {

                const activeElement = document.querySelector('.tab-item.active');
              let itemId = 0;
                if (activeElement) {
                     itemId = activeElement.getAttribute('data-id');
                    console.log('Active Tab Data ID:', itemId);
                }

              const chapterId = 0;
              const userId = 1244;
              const webinarId = '{{ $course->id }}';
              const courseUrl = '{{ $course->id }}';
            const watchPercentage = parseInt((duration / totalVideoDuration) * 100);

              console.log(`Saving progress: ${watchPercentage}% watched`);
              saveCourseProgress(itemId, chapterId, webinarId, userId, duration,watchPercentage,totalVideoDuration,courseUrl);

              progressSaved = true;
              getPaused = true;
              sessionStorage.setItem('progressSaved', 'true');
              sessionStorage.setItem('duration', duration);

            }

          } else {
            getPaused = false;
            progressSaved = false;
          }
        }
      }
    });

      intervalId = setInterval(() => {
    const iframe = document.getElementsByTagName('iframe')[0];
    if (iframe) {
        iframe.contentWindow.postMessage({
          context: 'player.js',
          method: 'getPaused'
        }, '*');

        pauseAndFetchDuration();
    }
      }, 1000);

    window.addEventListener('beforeunload', () => {
      if (intervalId) {
        clearInterval(intervalId);
      }
    });

    function saveCourseProgress(itemId, chapterId, webinarId, userId, watchedDuration,watchPercentage,totalVideoDuration,courseUrl) {
      $.ajax({
        url: "{{ route('store.watched.duration') }}",
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          item_id: itemId,
          user_id: userId,
          webinar_id: webinarId,
          chapter_id: chapterId,
          watched_duration: watchedDuration,
          watch_percentage: watchPercentage,
          total_duration: totalVideoDuration,
          courseUrl: courseUrl
        },
        success: function(response) {
          console.log('Course progress saved successfully!');

        },
        error: function(xhr) {
          console.error('Error saving progress:', xhr.responseText);
        }
      });
    }
    window.onload = function() {
      const savedDuration = sessionStorage.getItem('currentDuration');
       const sessionDuration = sessionStorage.getItem('duration');
      if (savedDuration && sessionDuration) {
        duration = parseInt(sessionDuration);
      }

    };
  </script>

    @if((!empty($isForumPage) and $isForumPage) or (!empty($isForumAnswersPage) and $isForumAnswersPage))
        <script defer src="{{ config('app.js_css_url') }}/assets/learning_page/forum.min.js"></script>
    @endif
@endpush
