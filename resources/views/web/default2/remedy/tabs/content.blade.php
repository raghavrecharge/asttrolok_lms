@if(!empty($course->chapters) and count($course->chapters))
    <section class="">
        @include('web.default2.remedy.tabs.contents.chapter')
    </section>
@endif
