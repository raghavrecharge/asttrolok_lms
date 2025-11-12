
<div class="content-tab p-15 pb-50">
<!--    @if(!empty($course->quizzes) and $course->quizzes->count())-->
<!--        @foreach($course->quizzes as $quiz)-->
<!--            @include('web.default.course.learningPage.components.quiz_tab.quiz',['item' => $quiz, 'type' => 'quiz','class' => 'px-10 border border-gray200 rounded-sm mb-15'])-->
<!--        @endforeach-->

<!--    @else-->
<!--        <div class="learning-page-forum-empty d-flex align-items-center justify-content-center flex-column">-->
<!--            <div class="learning-page-forum-empty-icon d-flex align-items-center justify-content-center">-->
<!--                <img src="{{ config('app.js_css_url') }}/assets/default/img/learning/quiz-empty.svg" class="img-fluid" alt="">-->
<!--            </div>-->

<!--            <div class="d-flex align-items-center flex-column mt-10 text-center">-->
<!--                <h3 class="font-20 font-weight-bold text-dark-blue text-center">{{ trans('update.learning_page_empty_quiz_title') }}</h3>-->
<!--                <p class="font-14 font-weight-500 text-gray mt-5 text-center">{{ trans('update.learning_page_empty_quiz_hint') }}</p>-->
<!--            </div>-->
<!--        </div>-->
<!--    @endif-->






    <div class="row">
        @foreach($webinars as $webinar)
            <!--<div class="col-12 col-lg-4 mt-20 loadid1 ">-->
            <div class="col-12 col-lg-12 mt-10  ">
                @include('web.default.course.learningPage.components.course_tab.list-card',['webinar' => $webinar])
            </div>
        @endforeach
    </div>
    <div class="mt-15 mb-20" style="display: flex; justify-content: center;">
        <a class="btn learning-page-navbar-btn btn-sm border-gray200" href="/classes">View all</a>
    </div>
</div>

