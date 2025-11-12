<div class="content-tab p-15 pb-50">
    @php
    $limit1=$limit;
    @endphp

    @if(
        (empty($chapterItems) or !count($chapterItems))
    )
        <div class="learning-page-forum-empty d-flex align-items-center justify-content-center flex-column">
            <div class="learning-page-forum-empty-icon d-flex align-items-center justify-content-center">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/learning/content-empty.svg" class="img-fluid" alt="">
            </div>

            <div class="d-flex align-items-center flex-column mt-10 text-center">
                <h3 class="font-20 font-weight-bold text-dark-blue text-center">{{ trans('update.learning_page_empty_content_title') }}</h3>
                <p class="font-14 font-weight-500 text-gray mt-5 text-center">{{ trans('update.learning_page_empty_content_hint') }}</p>
            </div>
        </div>
    @else
        @foreach($chapterItems as $chapterItem)
        @php
    $limit1--;
    @endphp
    

         {{--   @if(!empty($sessionsWithoutChapter) and count($sessionsWithoutChapter))
                @foreach($sessionsWithoutChapter as $session)
                    @include('web.default.subscription.learningPage.components.content_tab.content',['item' => $session, 'type' => \App\Models\WebinarChapter::$chapterSession])
                @endforeach
            @endif
    
            @if(!empty($textLessonsWithoutChapter) and count($textLessonsWithoutChapter))
                @foreach($textLessonsWithoutChapter as $textLesson)
                    @include('web.default.subscription.learningPage.components.content_tab.content',['item' => $textLesson, 'type' => \App\Models\WebinarChapter::$chapterTextLesson])
                @endforeach
            @endif--}}
    
            @if(!empty($chapterItem->file))
              {{--  @foreach($filesWithoutChapter as $file) --}}
                    @include('web.default.subscription.learningPage.components.content_tab.content',['item' => $chapterItem->file, 'type' => \App\Models\WebinarChapter::$chapterFile])
               {{-- @endforeach --}}
            @endif
    
           {{-- @if(!empty($subscription->chapters) and count($subscription->chapters))
                @include('web.default.subscription.learningPage.components.content_tab.chapter')
            @endif --}}
        @endforeach
    @endif
</div>
