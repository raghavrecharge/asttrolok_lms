@php
    $statisticsSettings = getStatisticsSettings();
@endphp

@if(!empty($statisticsSettings['enable_statistics']))
    @if(!empty($statisticsSettings['display_default_statistics']) and !empty($homeDefaultStatistics))
        <div class="hide-mobile stats-container {{ ($heroSection == "2") ? 'page-has-hero-section-2' : '' }}">
            <div class="container">
                <div class="row ">
                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box teacher mt-10">
                                <img loading="lazy"  loading="lazy" decoding="async" src="/assets/default/img/stats/teacher.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['skillfulTeachersCount'] }}</strong>-->
                            <h4 class="stat-title my-25">Astrology</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.skillful_teachers_hint') }}</p>-->
                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box student mt-10">
                                <img loading="lazy"  loading="lazy" decoding="async" src="/assets/default/img/stats/student.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['studentsCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.happy_students') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.happy_students_hint') }}</p>-->
                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box video mt-10">
                                <img loading="lazy"  loading="lazy" decoding="async" src="/assets/default/img/stats/video.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['liveClassCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.live_classes') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.live_classes_hint') }}</p>-->
                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box course mt-10">
                                <img loading="lazy"  loading="lazy" decoding="async" src="/assets/default/img/stats/course.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['offlineCourseCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.offline_courses') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.offline_courses_hint') }}</p>-->
                        </div>
                    </div>
                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box course mt-10">
                                <img loading="lazy"  loading="lazy" decoding="async" src="/assets/default/img/stats/course.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['offlineCourseCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.offline_courses') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.offline_courses_hint') }}</p>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(!empty($homeCustomStatistics))

        <div class="hide-mobile stats-container mt-50">
            <div class="container">
                <div class="d-flex justify-content-between">
                    <div >
                        <h2 class="section-title">Courses Categories</h2>
                    </div>
            
                    <a href="/classes" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                </div>
                
                <div class="row mt-20">
                    
                    <?php $i=1; 
                    
                    
                    
                    ?>
                  @foreach($categories as $category)
                    @if($i>1)
                       <a href="{{ $category->getUrl() }}">   <div class=" mt-lg-0 mt-lg-0 cats ">
                            <div class="stats-item d-flex flex-column align-items-center text-center pt-10 px-0 " style="width:100%;">
                                <div class="stat-icon-box" style="padding: 0;">
                                    <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $category->icon }}" alt="{{ $category->title }}" class="img-fluid"/>
                                </div>
                                
                              <a href="{{ $category->getUrl() }}">  <h4 class="stat-title mt-20">{!! nl2br($category->title) !!}</h4></a>
                                
                            </div>
                        </div></a>
                        @endif
                        <?php $i++; ?>
                    @endforeach

                    
                </div>
            </div>
        </div>
    @else
        <div class="my-40"></div>
    @endif
@else
    <div class="my-40"></div>
@endif
