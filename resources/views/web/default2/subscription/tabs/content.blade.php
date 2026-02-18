<div class="row">

@php
$free_video=0;
$free_video_c=0;
@endphp
@foreach ($chapterItems as $chapterItem)

@if(!empty($chapterItem) and $chapterItem->file)

                    @php

 if( $chapterItem->file->getIconByType() == 'film'){
                      $free_video++;
                      $free_video_c++;
                    }else{

                    }
@endphp

                        @include('web.default2.subscription.tabs.contents.files' , ['file' => $chapterItem->file, 'accordionParent' => 'filesAccordion','free_video_c' => $free_video_c])

@endif

@endforeach

  </div>
