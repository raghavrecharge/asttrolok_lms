
<div class="content-tab p-15 pb-50">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    <style>
      
 body {
           
            background: #f4f6f9;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: black;
            margin-bottom: 50px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .video-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .video-icon i {
            color: white;
            font-size: 24px;
        }

        .card-title {
            flex: 1;
        }

        .class-number {
            font-size: 0.85rem;
            color: #000;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .class-name {
            font-size: 1.1rem;
            color: #2d3748;
            font-weight: 600;
            line-height: 1.4;
        }

        .card-content {
            margin-top: 20px;
        }

        .content-type {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 15px;
        }

        .progress-section {
            margin-top: 15px;
        }

        .progress-label {
            font-size: 0.85rem;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.5s ease;
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .progress-percentage {
            font-size: 0.85rem;
            color: #4a5568;
            margin-top: 5px;
            font-weight: 600;
        }

        .start-button {
            margin-top: 20px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 0.95rem;
        }

        .start-button:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .course-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
     <div class="container">
 <div class="header">
            <h1>Astrology Pathshala</h1>
            <p>Course Progress</p>
        </div>    
    <div class="course-grid">
    @php
    
    $userId = request()->segment(3);
          $access_content = DB::table('subscription_access')
            ->where('subscription_id',  $subscription->id)
            ->where('user_id', $userId)
            ->first();
            
            $video_limit=($access_content->access_content_count)+5;
            
            
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
    
            @if(!empty($chapterItem->file) && $video_limit>0)
              {{--  @foreach($filesWithoutChapter as $file) --}}
                    @include('web.default.subscriptionProgress.learningPage.components.content_tab.content',['item' => $chapterItem->file, 'type' => \App\Models\WebinarChapter::$chapterFile])
               {{-- @endforeach --}}
            @endif
    
           {{-- @if(!empty($subscription->chapters) and count($subscription->chapters))
                @include('web.default.subscription.learningPage.components.content_tab.chapter')
            @endif --}}
            @php
             $video_limit--;
             @endphp
        @endforeach
    @endif
</div></div>
</div>
  <script>
        function startClass(classNum) {
            alert(`Starting Class ${classNum}! This would navigate to the video player.`);
        }

        // Demo: Simulate progress on first card after page load
        // setTimeout(() => {
        //     const firstProgressBar = document.querySelector('.progress-bar');
        //     const firstProgressText = document.querySelector('.progress-percentage');
        //     console.log('firstProgressText.textContent',firstProgressText.textContent);
        //     let progress = 0;
        //     const interval = setInterval(() => {
        //         if (progress <= 35) {
        //             progress += 1;
        //             firstProgressBar.style.width = firstProgressText.textContent + '%';
        //             firstProgressText.textContent = firstProgressText.textContent + '%';
        //         } else {
        //             clearInterval(interval);
        //         }
        //     }, 20);
        // }, 500);
    </script>
