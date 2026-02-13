@php
$free_video=0;
@endphp
@foreach ($chapterItems as $chapterItem)

@if(!empty($chapterItem) and $chapterItem->file)
    <section class="mt-20">
        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="filesAccordion" role="tablist" aria-multiselectable="true">

                    @php

 if( $chapterItem->file->getIconByType() == 'film'){
                      $free_video++;
                    }else{

                    }
@endphp
                        @include('web.default2.subscription.tabs.contents.files' , ['file' => $chapterItem->file, 'accordionParent' => 'filesAccordion'])

                </div>
            </div>
        </div>
    </section>
@endif

@if(!empty($chapterItem) and $chapterItem->quiz)
    <section class="mt-20">

        <div class="row">
            <div class="col-12">
                <div class="accordion-content-wrapper" id="quizAccordion" role="tablist" aria-multiselectable="true">

                        @include('web.default2.subscription.tabs.contents.quiz' , ['quiz' => $chapterItem->quiz, 'accordionParent' => 'quizAccordion'])

                </div>
            </div>
        </div>
    </section>

    <section class="">
        @include('web.default2.subscription.tabs.contents.certificate' , ['quiz' => $chapterItem->quiz])
    </section>
@endif

@include('web.default.subscription.tabs.play_modal.play_modal')

@endforeach
