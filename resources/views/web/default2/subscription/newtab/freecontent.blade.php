<div class="frame427322615-course-content">
    <div class="frame427322615-frame427322533">
        <span class="frame427322615-text150" style="width:fit-content;">Free Course Content</span>
    </div>
@php
    // Static images for first 5 videos (0-based index)
    $staticImages = [
        '/public/public/image21659-jsxk-300w.png', // index 0 -> 1st
        '/public/public/Tarus.png',                // index 1 -> 2nd
        '/public/public/Gemini.png',               // index 2 -> 3rd
        '/public/public/Cancer.png',               // index 3 -> 4th
        '/public/public/Leo.png'                   // index 4 -> 5th
    ];
@endphp
    @php
        $videoCount = 0;
        $videoLimit = $subscription->free_video_count;
        
        $videos = [];
        foreach($chapterItems as $index => $chapterItem) {
            if ($videoCount >= $videoLimit) break;
            
            $file = $chapterItem->file ?? null;
            
            if($file && $file->getIconByType() == 'film') {
                $isLocked = ($file->accessibility ?? 'paid') != 'free' && !$hasBought;
                
                $videos[] = [
                    'file' => $file,
                    'isLocked' => $isLocked,
                    'index' => $videoCount
                ];
                
                $videoCount++;
            }
        }
        
        $slides = array_chunk($videos, 3);
    @endphp

    @if(count($videos) > 0)
        <div style="position:relative;width:100%;">
            <div class="swiper course-content-video-swiper">
                <div class="swiper-wrapper">
                    @foreach($slides as $slideIndex => $slideVideos)
                        <div class="swiper-slide">
                            <div class="frame427322615-frame427322502" style="display:flex;flex-wrap:nowrap;gap:16px;">
                                @foreach($slideVideos as $videoIndex => $video)
                                    @php
                                        $file = $video['file'];
                                        $isLocked = $video['isLocked'];
                                        $originalIndex = $video['index'];
                                        
                                        $cardNum = ($videoIndex % 3) + 1;
                                        if ($cardNum > 3) $cardNum = 3;
                                        
                                        $frameClasses = ['frame63z', 'frame62z', 'frame59z'];
                                        $frameClass = $frameClasses[$videoIndex % 3];
                                        
                                        $imgNum = 10 + ($videoIndex % 3);
                                        
                                        // Enrollment URL
                                        $enrollUrl = "/subscriptions/direct-payment/{$subscription->slug}";
                                    @endphp

                                    <div class="frame427322615-{{ $frameClass }}" style="width:calc(33.333% - 11px);flex-shrink:0;">
                                        <div class="frame427322615-video-cardcom{{ $cardNum }}">
                                            <div class="frame427322615-img{{ $imgNum }} 
                                                @if($hasBought || !$isLocked) js-play-video @endif cursor-pointer"
                                                @if($hasBought || !$isLocked)
                                                    data-id="{{ $file->id }}"
                                                    data-title="{{ $file->title }}"
                                                    data-accessibility="{{ $file->accessibility ?? 'paid' }}"
                                                @else
                                                    onclick="window.location.href='{{ $enrollUrl }}'"
                                                @endif
                                                style="cursor: pointer;">
                                               @if(isset($staticImages[$originalIndex]))
                                                    <img src="{{ url($staticImages[$originalIndex]) }}"
                                                        alt="{{ $file->title }}"
                                                        class="frame427322615-image21" />
                                                @else
                                                    @if($file->image_cover)
                                                        <img src="{{ url($file->image_cover) }}"
                                                            alt="{{ $file->title }}"
                                                            class="frame427322615-image21" />
                                                    @else
                                                        <img src="/public/public/image21659-jsxk-300w.png"
                                                            alt="{{ $file->title }}"
                                                            class="frame427322615-image21" />
                                                    @endif
                                                @endif
                                                <img src="/public/public/play.svg"
                                                     alt="Play"
                                                     style="z-index:9;cursor:pointer;position:relative;" />
                                            </div>

                                            <div class="frame427322615-info{{ $cardNum }}" style="padding:16px;display:flex;flex-direction:column;gap:12px;">
                                                <div class="frame427322615-title{{ $cardNum }}">
                                                    <div style="color:#32a028;font-weight:700;font-size:16px;line-height:1.3;margin-bottom:4px;">
                                                        {{ $file->title }}:
                                                    </div>
                                                </div>
                                                <div class="frame427322615-link{{ $imgNum }}" style="display:flex;gap:8px;align-items:center;font-size:10px;">
                                                    @if($hasBought || !$isLocked)
                                                        <a href="javascript:void(0)" 
                                                           onclick="openChapter({{ $file->id }}, 'file')" 
                                                           style="font-weight:500;text-decoration:none;cursor:pointer;color:#32a028;font-size:14px;">
                                                            Watch Video
                                                        </a>
                                                    @else
                                                        <a href="{{ $enrollUrl }}" 
                                                           style="font-weight:500;text-decoration:none;cursor:pointer;color:#666;font-size:14px;">
                                                             Enroll to Watch
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div style="
    display: flex;
    align-items: center;
    justify-content: center;
">
            <div class="swiper-pagination content-video-pagination a" style="margin-top:20px;"></div></div>
        </div>

        <style>
            .course-content-video-swiper {
                width: 100%;
                overflow: hidden;
                padding: 10px 0 30px 0;
            }
            
            .course-content-video-swiper .swiper-wrapper {
                display: flex;
            }
            
            .course-content-video-swiper .swiper-slide {
                width: 100%;
                flex-shrink: 0;
            }
            
            .frame427322615-frame427322502 {
                display: flex;
                flex-wrap: nowrap !important;
            }
            
            .content-video-next,
            .content-video-prev {
                width: 40px;
                height: 40px;
                background: white;
                border-radius: 50%;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                color: #32a028;
            }
            
            .content-video-next {
                right: 0;
            }
            
            .content-video-prev {
                left: 0;
            }
            
            .content-video-next:after,
            .content-video-prev:after {
                font-size: 18px;
                font-weight: bold;
            }
            
            .swiper-button-disabled {
                opacity: 0.35;
                cursor: not-allowed;
            }
            
            .content-video-pagination .swiper-pagination-bullet {
                background: #32a028;
                opacity: 0.3;
                margin: 0 4px;
            }
            
            .content-video-pagination .swiper-pagination-bullet-active {
                opacity: 1;
            }
            
            @media (max-width: 768px) {
                .frame427322615-frame63z,
                .frame427322615-frame62z,
                .frame427322615-frame59z {
                    width: 100% !important;
                }
                
                .frame427322615-frame427322502 {
                    flex-wrap: wrap !important;
                    gap: 16px !important;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const existingSwiper = document.querySelector('.course-content-video-swiper');
                if (existingSwiper && existingSwiper.swiper) {
                    existingSwiper.swiper.destroy(true, true);
                }
                
                setTimeout(function() {
                    new Swiper('.course-content-video-swiper', {
                        slidesPerView: 1,
                        spaceBetween: 0,
                        navigation: {
                            nextEl: '.content-video-next',
                            prevEl: '.content-video-prev',
                        },
                        pagination: {
                            el: '.content-video-pagination',
                            clickable: true,
                        },
                        loop: false,
                        allowTouchMove: true,
                        on: {
                            init: function() {
                                console.log('Slider initialized with ' + this.slides.length + ' slides');
                            }
                        }
                    });
                }, 100);
            });
        </script>
 
    @endif
</div>