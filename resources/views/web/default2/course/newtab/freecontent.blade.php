@php
    // Collect only FREE video content
    $freeVideos = collect();

    // Add FREE sessions from course
    if(!empty($course->sessions) && $course->sessions->count() > 0) {
        foreach($course->sessions as $session) {
            // Only free sessions
            if(($session->accessibility ?? 'paid') == 'free') {
                $thumbnail = null;
                if(!empty($session->image)) {
                    $thumbnail = $session->image;
                }

                // Get chapter title properly
                $chapterTitle = 'Session';
                if(!empty($session->chapter)) {
                    if(is_object($session->chapter) && isset($session->chapter->title)) {
                        $chapterTitle = $session->chapter->title;
                    } elseif(is_string($session->chapter)) {
                        $chapterTitle = $session->chapter;
                    }
                }

                $freeVideos->push([
                    'type' => 'session',
                    'id' => $session->id,
                    'title' => $session->title,
                    'thumbnail' => $thumbnail,
                    'url' => '/course/'.$course->slug.'/session/'.$session->id,
                    'chapter' => $chapterTitle
                ]);
            }
        }
    }

    // Add FREE video files from course
    if(!empty($course->files) && $course->files->count() > 0) {
        foreach($course->files as $file) {
            if($file->file_type == 'video' || strpos($file->file_type ?? '', 'video') !== false) {
                // Only free files
                if(($file->accessibility ?? 'paid') == 'free') {
                    $thumbnail = null;
                    if(!empty($file->image)) {
                        $thumbnail = $file->image;
                    }

                    // Get chapter title properly
                    $chapterTitle = 'Video';
                    if(!empty($file->chapter)) {
                        if(is_object($file->chapter) && isset($file->chapter->title)) {
                            $chapterTitle = $file->chapter->title;
                        } elseif(is_string($file->chapter)) {
                            $chapterTitle = $file->chapter;
                        }
                    }

                    $freeVideos->push([
                        'type' => 'file',
                        'id' => $file->id,
                        'title' => $file->title,
                        'thumbnail' => $thumbnail,
                        'url' => '/course/'.$course->slug.'/file/'.$file->id,
                        'chapter' => $chapterTitle
                    ]);
                }
            }
        }
    }

    // ✅ Limit to maximum 3 videos
    $freeVideos = $freeVideos->take(3);
@endphp

@if($freeVideos->count() > 0)
<div class="frame427322615-course-content">
    <div class="frame427322615-frame427322533">
        <span class="frame427322615-text150" style="width:fit-content;">Free Course Content</span>
    </div>
    <div class="frame427322615-frame427322502">

        @foreach($freeVideos as $index => $video)
            @php
                $cardNum = ($index % 3) + 1;
                $frameClass = ['frame63z', 'frame62z', 'frame59z'][$index % 3];
            @endphp

            <div class="frame427322615-{{ $frameClass }}">
                <div class="frame427322615-video-cardcom{{ $cardNum }}">

                    <div class="frame427322615-img{{ 10 + ($index % 3) }} js-play-video cursor-pointer"
                         data-id="{{ $video['id'] }}"
                         data-title="{{ $video['title'] }}">

                        <img src="/public/public/image21659-jsxk-300w.png"
                             alt=""
                             class="frame427322615-image21" />

                        <img src="/public/public/play.svg"
                             alt="Play"
                             style="z-index: 9; cursor: pointer; position: relative;" />
                    </div>

                    <div class="frame427322615-info{{ $cardNum }}">
                        <div class="frame427322615-title{{ $cardNum }}">
                            <span class="frame427322615-text{{ 151 + (($index % 3) * 7) }}">
                                <span class="frame427322615-text{{ 152 + (($index % 3) * 7) }}">{{ $video['title'] }}</span>
                            </span>
                            <span class="frame427322615-text{{ 155 + (($index % 3) * 7) }}">Chapter: {{ $video['chapter'] }}</span>
                        </div>
                        <div class="frame427322615-link{{ 10 + ($index % 3) }}">
                            <span class="frame427322615-text{{ 156 + (($index % 3) * 7) }}">Free</span>
                            <img src="/public/public/line1{{ 1659 + ($index % 3) }}-xcbn.svg"
                                 alt="Line"
                                 class="frame427322615-line{{ 110 + ($index % 3) }}"
                                 onerror="this.style.display='none'" />
                            <span class="frame427322615-text{{ 157 + (($index % 3) * 7) }}">Watch Video</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
@endif
