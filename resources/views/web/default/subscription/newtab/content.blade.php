@php
    $free_video   = 0;
    $free_video_c = 0;
    $allVideos    = collect();

    // Collect all video files from chapterItems
    foreach ($chapterItems as $chapterItem) {
        if (!empty($chapterItem) && $chapterItem->file) {
            if ($chapterItem->file->getIconByType() == 'film') {
                $free_video++;
                $free_video_c++;

                $thumbnail = !empty($chapterItem->file->image)
                    ? $chapterItem->file->image
                    : null;

                // Check if video is free
                $isFree = ($chapterItem->file->accessibility ?? 'paid') == 'free';

                $allVideos->push([
                    'type'      => 'file',
                    'id'        => $chapterItem->file->id,
                    'title'     => $chapterItem->file->title,
                    'thumbnail' => $thumbnail,
                    'can_view'  => $hasBought || $isFree,
                    'url'       => '/subscriptions/'.$subscription->slug.'/file/'.$chapterItem->file->id,
                    'is_free'   => $isFree,
                ]);
            }
        }
    }

    // YAHAN SIRF LOCKED VIDEOS LO (free hata do)
    $displayVideos = $allVideos->filter(function ($video) {
        return !$video['is_free'];   // only non‑free / locked
    })->values();
    $displayVideos = $displayVideos->slice($subscription->free_video_count)->values();
@endphp
@if($displayVideos->count() > 0)
 @php
    $learnTexts = $subscription->extraDetails ? json_decode($subscription->extraDetails->learn_text, true) ?? [] : [];
    $learnIcons = $subscription->extraDetails ? json_decode($subscription->extraDetails->learn_icon, true) ?? [] : [];
  @endphp
  <style>
    </style>
  <div class="frame1000001692-group40182 " style="width: 100% !important;">
    <img
      src="{{ asset($learnIcons[0] ?? null) }}"
      alt="BackgroundBorder1186"
      class="frame1000001692-background-border3"
    />
    <span class="frame1000001692-text173">
      <span>{!! nl2br(e($learnTexts[0] ?? null)) !!}</span>
    </span>
    <div class="frame1000001692-background-border4">
      <img
        src="{{ asset($learnIcons[1] ?? null) }}"
        alt="SVG1187"
        class="frame1000001692svg17"
      />
    </div>
    <span class="frame1000001692-text177">
      <span>{!! nl2br(e($learnTexts[1] ?? null)) !!}</span>
    </span>
    <img
      src="{{ asset($learnIcons[2] ?? null) }}"
      alt="BackgroundBorder1187"
      class="frame1000001692-background-border5"
    />
    <img
      src="{{ asset($learnIcons[3] ?? null) }}"
      alt="BackgroundBorder1187"
      class="frame1000001692-background-border6"
    />
    <span class="frame1000001692-text181">
      {!! nl2br(e($learnTexts[2] ?? null)) !!}
    </span>
    <span class="frame1000001692-text182">
      {!! nl2br(e($learnTexts[3] ?? null)) !!}
    </span>
  </div>
</div>



    <div style="max-height: 380px; overflow-y: auto; overflow-x: hidden; padding-right: 10px; margin-top:110px; width:100%;">
        <div class="frame427322615-frame427322543 mt-10">
            @php
                $videoIndex = 0;
                $rows = ceil($displayVideos->count() / 2);
            @endphp

            @for($row = 0; $row < $rows && $videoIndex < $displayVideos->count(); $row++)
                @php
                    // Row classes ko bhi 0-2 ke beech cycle karo (3 row classes hain CSS me)
                    $rowCssIndex = $row % 2;
                @endphp

                <div class="frame427322615-frame42732254{{ $rowCssIndex }}"style="width:100% !important;">
                    @for($col = 0; $col < 2 && $videoIndex < $displayVideos->count(); $col++, $videoIndex++)
                        @php
                            $video    = $displayVideos[$videoIndex];
                            // Video CSS classes ko 0-8 ke beech cycle karo
                            $cssIndex = $videoIndex % 9;
                        @endphp

                        <div class="frame427322615-img{{ 15 + $cssIndex }}" style="position: relative;width:48% !important;">

                            <div class="frame427322615-frame42732248{{ 89 + $cssIndex }}"
                                 style="position: absolute; top: 10px; left: 10px; right: 10px; z-index: 5;
                                        display: flex; flex-direction: column; gap: 5px;">

                                <span class="frame427322615-text{{ 209 + $cssIndex }}"
                                      title="{{ $video['title'] }}"
                                      style="font-size: 15px !important;
                                             font-weight: 600 !important;
                                             color: white;
                                             display: -webkit-box;
                                             -webkit-line-clamp: 2;
                                             -webkit-box-orient: vertical;
                                             overflow: hidden;
                                             text-overflow: ellipsis;
                                             line-height: 1.3;
                                             max-height: 39px;
                                             word-break: break-word;
                                             margin-top:10px;">
                                    {{ $video['title'] }}
                                </span>

                                <div class="frame427322615-link{{ 15 + $cssIndex }}"
                                     style="display: flex; align-items: center; gap: 4px; flex-wrap: nowrap;">
                                    @if($video['can_view'])
                                        <a href="https://lms.asttrolok.com/subscriptions/learning/asttrolok-pathshala"
                                           style="text-decoration: none;
                                                  color: white;
                                                  display: flex;
                                                  align-items: center;
                                                  gap: 4px;
                                                  white-space: nowrap;">
                                            <span style="font-size:13px !important; font-weight:500; display:block; margin-top:30px;">
                                                Preview
                                            </span>
                                            <span style="font-size:13px !important; font-weight:500; display:block; margin-top:30px;margin-left:30px;">
                                                Watch Video
                                            </span>
                                        </a>
                                    @else
                                        <div style="display: flex;
                                                   align-items: center;
                                                   gap: 4px;
                                                   opacity: 0.8;
                                                   color: white;
                                                   white-space: nowrap;">
                                            <span style="font-size: 13px !important; font-weight: 500; display:block; margin-top:30px;margin-left:0px;">
                                                Enroll to Watch
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="frame427322615-group66{{ 1 + $cssIndex }}" style="left: 60% !important;">
                                <img src="/public/public/ellipse162864-9lr-200h.png"
                                     alt="Background"
                                     class="frame427322615-ellipse16{{ 1 + $cssIndex }}" />

                                <div class="frame427322615-frame42732250{{ 31 + $cssIndex }}">
                                    @if(!empty($video['thumbnail']))
                                        <img src="{{ $video['thumbnail'] }}"
                                             alt="{{ $video['title'] }}"
                                             class="frame427322615-image9{{ 1 + $cssIndex }}"
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;"
                                             onerror="this.onerror=null; this.src='/store/1/default_images/courses/course3.jpg';" />
                                    @else
                                        <img src="/public/public/image92864-0onb-200w.png"
                                             alt="{{ $video['title'] }}"
                                             class="frame427322615-image9{{ 1 + $cssIndex }}"
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" />
                                    @endif
                                </div>

                                <img src="/public/public/ellipse152864-g68i-200h.png"
                                     alt="Overlay"
                                     class="frame427322615-ellipse15{{ 1 + $cssIndex }}" />
                            </div>

                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; z-index: 10;">
                                @if($video['can_view'])
                                    <img src="/public/public/vector2864-t39k.svg"
                                         alt="Play Icon"
                                         class="frame427322615-vector{{ 16 + $cssIndex }}"
                                         style="width: 50px; height: 50px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));" />
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>
@endif
