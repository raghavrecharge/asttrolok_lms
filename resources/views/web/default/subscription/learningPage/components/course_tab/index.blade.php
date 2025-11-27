<div class="content-tab p-15 pb-50">

    <div class="row">
        @foreach($webinars as $webinar)

            <div class="col-12 col-lg-12 mt-10  ">
                @include('web.default.course.learningPage.components.course_tab.list-card',['webinar' => $webinar])
            </div>
        @endforeach
    </div>
    <div class="mt-15 mb-20" style="display: flex; justify-content: center;">
        <a class="btn learning-page-navbar-btn btn-sm border-gray200" href="/classes">View all</a>
    </div>
</div>
