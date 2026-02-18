@php
    // Collect all video content directly from course object
    $allVideos = collect();

    // Add sessions from course (exclude free ones)
    if(!empty($course->sessions) && $course->sessions->count() > 0) {
        foreach($course->sessions as $session) {
            // Skip free sessions
            if(($session->accessibility ?? 'paid') == 'free') {
                continue;
            }

            $thumbnail = null;
            if(!empty($session->image)) {
                $thumbnail = $session->image;
            }

            $allVideos->push([
                'type' => 'session',
                'id' => $session->id,
                'title' => $session->title,
                'thumbnail' => $thumbnail,
                'can_view' => $hasBought,
                'url' => '/course/'.$course->slug.'/session/'.$session->id,
                'is_free' => false
            ]);
        }
    }

    // Add video files from course (exclude free ones)
    if(!empty($course->files) && $course->files->count() > 0) {
        foreach($course->files as $file) {
            if($file->file_type == 'video' || strpos($file->file_type ?? '', 'video') !== false) {
                // Skip free files
                if(($file->accessibility ?? 'paid') == 'free') {
                    continue;
                }

                $thumbnail = null;
                if(!empty($file->image)) {
                    $thumbnail = $file->image;
                }

                $allVideos->push([
                    'type' => 'file',
                    'id' => $file->id,
                    'title' => $file->title,
                    'thumbnail' => $thumbnail,
                    'can_view' => $hasBought,
                    'url' => '/course/'.$course->slug.'/file/'.$file->id,
                    'is_free' => false
                ]);
            }
        }
    }

    // Display all videos
    $displayVideos = $allVideos;
@endphp

@if($displayVideos->count() > 0)

<div style="max-height: 380px; overflow-y: auto; overflow-x: hidden; padding-right: 10px;">
    <div class="frame427322615-frame427322543">
        @php
            $videoIndex = 0;
            $rows = ceil($displayVideos->count() / 3);
        @endphp

        @for($row = 0; $row < $rows && $videoIndex < $displayVideos->count(); $row++)
            @php
                // Row classes ko bhi 0-2 ke beech cycle karo (3 row classes hain CSS me)
                $rowCssIndex = $row % 3;
            @endphp

            <div class="frame427322615-frame42732254{{ $rowCssIndex }}">
                @for($col = 0; $col < 3 && $videoIndex < $displayVideos->count(); $col++, $videoIndex++)
                    @php
                        $video = $displayVideos->values()[$videoIndex];
                        // Video CSS classes ko 0-8 ke beech cycle karo
                        $cssIndex = $videoIndex % 9;
                    @endphp

                    <div class="frame427322615-img{{ 15 + $cssIndex }}" style="position: relative;">

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
                                         word-break: break-word;margin-top:10px;">
                                {{ $video['title'] }}
                            </span>

                            <div class="frame427322615-link{{ 15 + $cssIndex }}"
                                 style="display: flex; align-items: center; gap: 4px; flex-wrap: nowrap;">
                                @if($video['can_view'])
                                    <a href="{{ $course->getLearningPageUrl1() }}?type=file&item={{ $video['id'] }}"
                                       style="text-decoration: none;
                                              color: white;
                                              display: flex;
                                              align-items: center;
                                              gap: 4px;
                                              white-space: nowrap;">
                                        {{--<span style="font-size:13px !important; font-weight:500; display:block; margin-top:30px;">
                                            Preview
                                        </span>--}}
                                        <span style="font-size:13px !important; font-weight:500; display:block; margin-top:30px;">
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
                                        {{--<span style="font-size: 13px !important; font-weight: 500; display:block; margin-top:30px;"> Locked</span>--}}
                                        <span style="font-size: 13px !important; font-weight: 500; display:block; margin-top:30px;">Enroll to Watch</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="frame427322615-group66{{ 1 + $cssIndex }}">
                            <img src="/public/public/ellipse162864-9lr-200h.png"
                                 alt="Background"
                                 class="frame427322615-ellipse16{{ 1 + $cssIndex }}" />

                            <div class="frame427322615-frame42732250{{ 31 + $cssIndex }}">
                                @if(!empty($video['thumbnail']))

                                    <img src="{{ config('app.img_dynamic_url') }}{{ $video['thumbnail'] }}"
                                         alt="{{ $video['title'] }}"
                                         class="frame427322615-image9{{ 1 + $cssIndex }}"
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;"
                                         onerror="this.onerror=null; this.src='{{ config('app.js_css_url') }}/assets2/default/img/course/video-placeholder.png';" />
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

<style>

div[style*="max-height: 380px"]::-webkit-scrollbar {
    width: 8px;
}

div[style*="max-height: 380px"]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
    margin: 10px 0;
}

div[style*="max-height: 380px"]::-webkit-scrollbar-thumb {
    background: #4CAF50;
    border-radius: 10px;
}

div[style*="max-height: 380px"]::-webkit-scrollbar-thumb:hover {
    background: #45a049;
}

.frame427322615-frame427322543 [class*="frame427322615-img"] {
    overflow: hidden !important;
}
</style>

@endif
