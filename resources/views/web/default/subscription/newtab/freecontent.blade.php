@php
    // Static images for first 5 videos (0-based index)
    $staticImages = [
        '/public/public/Img.png', // index 0 -> 1st
        '/public/public/Img (1).png',                // index 1 -> 2nd
        '/public/public/image 2.png',               // index 2 -> 3rd
        '/public/public/image 8.png',               // index 3 -> 4th
        '/public/public/Img (2).png'                   // index 4 -> 5th
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
        
        $slides = array_chunk($videos, 1);
    @endphp
 @if(count($videos) > 0)
   <div class="frame427322615-course-content">
    <div class="">
        <span class="frame427322615-text150" style="width:fit-content;">Free Course Content</span>
    </div>
   
        <div style="position:relative;width:100%;">
            <div class="swiper course-content-video-swiper">
                <div class="swiper-wrapper">
                    @foreach($slides as $slideIndex => $slideVideos)
                     @foreach($slideVideos as $videoIndex => $video)
                        <div class="swiper-slide">
                            <div class="frame427322615-frame427322502" style="display:flex;flex-wrap:nowrap;gap:16px;">
                               
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
                                        $enrollUrl = "/subscriptions/direct-payment-enroll/{$subscription->slug}";
                                    @endphp

                                    <div class="frame427322615-{{ $frameClass }}" style="width:100%;">
                                        <div class="frame427322615-video-cardcom{{ $cardNum }}" style="width:100%;padding:0;margin:0;">
                                            <div class="frame427322615-img{{ $imgNum }} 
                                                @if($hasBought || !$isLocked) js-play-video @endif 
                                                cursor-pointer video-image-container"
                                                @if($hasBought || !$isLocked)
                                                    data-id="{{ $file->id }}"
                                                    data-title="{{ $file->title }}"
                                                    data-accessibility="{{ $file->accessibility ?? 'paid' }}"
                                                @else
                                                    onclick="window.location.href='{{ $enrollUrl }}'"
                                                @endif
                                                 style="position:relative;width:100%;height:200px;overflow:hidden;border-radius:8px;padding:0;margin:0;cursor:pointer;">
                                               @if(isset($staticImages[$originalIndex]))
                                                    <img src="{{ url($staticImages[$originalIndex]) }}"
                                                        alt="{{ $file->title }}"
                                                        style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;padding:0;margin:0;display:block;" />
                                                @else
                                                    @if($file->image_cover)
                                                        <img src="{{ url($file->image_cover) }}"
                                                            alt="{{ $file->title }}"
                                                            style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;padding:0;margin:0;display:block;" />
                                                    @else
                                                        <img src="/public/public/image21659-jsxk-300w.png"
                                                            alt="{{ $file->title }}"
                                                            style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;padding:0;margin:0;display:block;" />
                                                    @endif
                                                @endif

                                                <img src="/public/public/play.svg"
                                                     alt="Play"
                                                     class="play-button-overlay"
                                                     style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10;cursor:pointer;width:60px;height:60px;pointer-events:none;" />
                                                
                                                {{-- Lock icon for non-enrolled users --}}
                                                @if(!$hasBought && $isLocked)
                                                    <div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.6);border-radius:50%;padding:8px;z-index:11;">
                                                        <span style="color:white;font-size:20px;"></span>
                                                    </div>
                                                @endif
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
                              
                            </div>
                              @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div style="display: flex;align-items: center;justify-content: center;">
                <div class="swiper-pagination content-video-pagination a" style="margin-top:20px;"></div>
            </div>
        </div>

        <style>
            /* Video Container */
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

            /* ===== RESET ALL VIDEO CARD PADDING/MARGIN ===== */
            .frame427322615-video-cardcom1,
            .frame427322615-video-cardcom2,
            .frame427322615-video-cardcom3 {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
                border-radius: 8px !important;
            }

            .frame427322615-frame63z,
            .frame427322615-frame62z,
            .frame427322615-frame59z {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* ===== IMAGE CONTAINER - NO SPACE ===== */
            .video-image-container,
            .frame427322615-img10,
            .frame427322615-img11,
            .frame427322615-img12 {
                position: relative !important;
                width: 100% !important;
                height: 200px !important;
                overflow: hidden !important;
                border-radius: 8px !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                background: #f0f0f0 !important;
            }

            /* ===== IMAGE - FULL FILL ===== */
            .video-image-container img:not(.play-button-overlay),
            .frame427322615-img10 img:not(.play-button-overlay),
            .frame427322615-img11 img:not(.play-button-overlay),
            .frame427322615-img12 img:not(.play-button-overlay) {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                border: none !important;
            }

            /* Play Button Overlay */
            .play-button-overlay {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                z-index: 10 !important;
                cursor: pointer !important;
                width: 60px !important;
                height: 60px !important;
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
                transition: transform 0.2s ease;
                pointer-events: none !important;
            }

            .video-image-container:hover .play-button-overlay {
                transform: translate(-50%, -50%) scale(1.1) !important;
            }
            
            /* Navigation Buttons */
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
            
            /* Pagination */
            .content-video-pagination .swiper-pagination-bullet {
                background: #32a028;
                opacity: 0.3;
                margin: 0 4px;
            }
            
            .content-video-pagination .swiper-pagination-bullet-active {
                opacity: 1;
            }

            .swiper-pagination {
                bottom: 14px !important;
            }
                            
            /* Responsive Design */
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

                .video-image-container,
                .frame427322615-img10,
                .frame427322615-img11,
                .frame427322615-img12 {
                    height: 180px !important;
                }
            }

            @media (max-width: 425px) {
                .video-image-container,
                .frame427322615-img10,
                .frame427322615-img11,
                .frame427322615-img12 {
                    height: 164px !important;
                }

                .play-button-overlay {
                    width: 50px !important;
                    height: 50px !important;
                }
            }

            @media (max-width: 345px) {
                .video-image-container,
                .frame427322615-img10,
                .frame427322615-img11,
                .frame427322615-img12 {
                    height: 146px !important;
                }

                .play-button-overlay {
                    width: 45px !important;
                    height: 45px !important;
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
                        slidesPerView: 2,
                        spaceBetween: 10,
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
 </div>
    @endif