<div style="
    max-height: 570px;
       overflow-y: auto;
    overflow-x: hidden;
">

 <div class="course-grid">

@php
$free_video=0;
$colors = [
    "bg-galaxy-teal",
    "bg-nebula-gold",
    "bg-cosmic-purple",
    "bg-zodiac-dark",
    "bg-mars-orange",
    "bg-space-blue"
];
$i = 0;
@endphp

@foreach ($chapterItems as $chapterItem)

 @php
        $color = $colors[$i % count($colors)];
    @endphp

@if(!empty($chapterItem) and $chapterItem->file)

                    @php

 if( $chapterItem->file->getIconByType() == 'film'){
                      $free_video++;
                    }else{

                    }
@endphp
                        @include('web.default2.subscription.tabs.contents.files1' , ['file' => $chapterItem->file, 'accordionParent' => 'filesAccordion','color' => $color])

@endif

    @php $i++; @endphp
@endforeach

 <
</div>
</div>
