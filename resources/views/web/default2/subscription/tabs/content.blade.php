@php
$free_video=0;
@endphp
@foreach ($chapterItems as $chapterItem)
{{-- Sessions 

@if(!empty($course->chapters) and count($course->chapters))
    <section class="">
        @include('web.default.course.tabs.contents.chapter')
    </section>
@endif

@if(!empty($sessionsWithoutChapter) and count($sessionsWithoutChapter))
    <section class="mt-20">
        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="sessionsAccordion" role="tablist" aria-multiselectable="true">
                    @foreach($sessionsWithoutChapter as $session)
                        @include('web.default.course.tabs.contents.sessions' , ['session' => $session, 'accordionParent' => 'sessionsAccordion'])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif --}}

{{-- Files --}}

@if(!empty($chapterItem) and $chapterItem->file)
    <section class="mt-20">
        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="filesAccordion" role="tablist" aria-multiselectable="true">
                    {{--@foreach($filesWithoutChapter as $file) --}}
                    @php
                    
 if( $chapterItem->file->getIconByType() == 'film'){
                      $free_video++;
                    }else{
                  
                    }
@endphp
                        @include('web.default2.subscription.tabs.contents.files' , ['file' => $chapterItem->file, 'accordionParent' => 'filesAccordion'])
                    {{--@endforeach--}}
                </div>
            </div>
        </div>
    </section>
@endif

{{-- TextLessons 

@if(!empty($textLessonsWithoutChapter) and count($textLessonsWithoutChapter))
    <section class="mt-20">
        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="textLessonsAccordion" role="tablist" aria-multiselectable="true">
                    @foreach($textLessonsWithoutChapter as $textLesson)
                        @include('web.default.course.tabs.contents.text_lessons' , ['textLesson' => $textLesson, 'accordionParent' => 'textLessonsAccordion'])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif --}}

{{-- Quizzes --}}
@if(!empty($chapterItem) and $chapterItem->quiz)
    <section class="mt-20">
        {{--<h2 class="section-title after-line">{{ trans('update.quiz_and_certificates') }}</h2>--}}

        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="quizAccordion" role="tablist" aria-multiselectable="true">
                    {{--@foreach($quizzes as $quiz)--}}
                        @include('web.default2.subscription.tabs.contents.quiz' , ['quiz' => $chapterItem->quiz, 'accordionParent' => 'quizAccordion'])
                    {{--@endforeach--}}
                </div>
            </div>
        </div>
    </section>

    {{-- Certificates --}}

    <section class="">
        @include('web.default2.subscription.tabs.contents.certificate' , ['quiz' => $chapterItem->quiz])
    </section>
@endif


@include('web.default.subscription.tabs.play_modal.play_modal')

@endforeach
