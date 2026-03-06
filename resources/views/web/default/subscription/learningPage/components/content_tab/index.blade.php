<div class="content-tab p-15 pb-50">
    {{--
     | CONTENT UNLOCK RULE (production Path A — subscription learning page)
     |
     | Items 1..unlockedItemCount are accessible (rendered with tab-item class).
     | Items beyond unlockedItemCount are locked (js-sequence-content-error-modal class).
     |
     | unlockedItemCount = access_content_count + free_video_count
     |   where access_content_count = video_count × paid_payment_count
     |
     | $itemIndex increments for EVERY chapterItem in order (including non-file items)
     | to preserve slot-position semantics of the ordered list.
     | $isItemUnlocked is passed explicitly to content.blade.php.
     --}}
    @php $itemIndex = 0; @endphp

    @if(empty($chapterItems) or !count($chapterItems))
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
                $itemIndex++;
                $isItemUnlocked = ($itemIndex <= $unlockedItemCount);
            @endphp

            @if(!empty($chapterItem->file))
                @include('web.default.subscription.learningPage.components.content_tab.content', [
                    'item'           => $chapterItem->file,
                    'type'           => \App\Models\WebinarChapter::$chapterFile,
                    'isItemUnlocked' => $isItemUnlocked,
                ])
            @endif

        @endforeach
    @endif
</div>
