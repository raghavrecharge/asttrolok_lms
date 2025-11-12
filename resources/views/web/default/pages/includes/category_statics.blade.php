@php
    $statisticsSettings = getStatisticsSettings();
@endphp

@if(!empty($statisticsSettings['enable_statistics']))
    @if(!empty($statisticsSettings['display_default_statistics']) and !empty($homeDefaultStatistics))
        {{--<div class="stats-container {{ ($heroSection == "2") ? 'page-has-hero-section-2' : '' }} homehide">
            <div class="container">
                <div class="row ">
                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box teacher mt-10">
                                <img loading="lazy"  src="/assets/default/img/stats/teacher.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['skillfulTeachersCount'] }}</strong>-->
                            <h4 class="stat-title my-25">Astrology</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.skillful_teachers_hint') }}</p>-->
                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box student mt-10">
                                <img loading="lazy"  src="/assets/default/img/stats/student.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['studentsCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.happy_students') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.happy_students_hint') }}</p>-->
                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box video mt-10">
                                <img loading="lazy"  src="/assets/default/img/stats/video.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['liveClassCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.live_classes') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.live_classes_hint') }}</p>-->
                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box course mt-10">
                                <img loading="lazy"  src="/assets/default/img/stats/course.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['offlineCourseCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.offline_courses') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.offline_courses_hint') }}</p>-->
                        </div>
                    </div>
                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box course mt-10">
                                <img loading="lazy"  src="/assets/default/img/stats/course.svg" alt="" class="img-fluid"/>
                            </div>
                            <!--<strong class="stat-number mt-10">{{ $homeDefaultStatistics['offlineCourseCount'] }}</strong>-->
                            <h4 class="stat-title my-25">{{ trans('home.offline_courses') }}</h4>
                            <!--<p class="stat-desc mt-10">{{ trans('home.offline_courses_hint') }}</p>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>--}}
    @elseif(!empty($homeCustomStatistics))
        {{--<div class="stats-container mt-50 homehide">
            <div class="container">
                <!--<div class="text-center" style="margin-bottom:30px;">-->
                <!--        <h2 class="section-title"> Our Services</h2>-->
                <!--        <p class="section-hint">#Your satisfaction is our priority - discover excellence through our services</p>-->
                <!--    </div>-->
                <div class="row ">
                    
                    <?php $i=1; 
                    
                    $cate123[1]="/classes";
                    $cate123[2]="/consult-with-astrologers";
                    $cate123[3]="https://asttroveda.asttrolok.com/asttrolok/personalizedkundali";
                    $cate123[4]="https://asttroveda.asttrolok.com/numerology/order";
                    
                    $title[1]='Online <br> Courses';
                    $title[2]="Online <br> Consultation";
                    $title[3]="Personalized <br> Reports";
                    $title[4]="Numerology <br> Report";
                    
                    ?>
                  
                    
                    @foreach($homeCustomStatistics as $homeCustomStatistic)
                    <?php
                    
                     
                    // $urls_cat=$cate123[$i]->getUrl();
                    //  $urls_cat='#';
                    ?>
                       <a href="{{ $cate123[$i] }}">   <div class=" mt-lg-0 mt-lg-0 cats mobile{{ $i }}">
                            <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-0 " style="width:100%;">
                                <div class="stat-icon-box  mt-15" style="background-color: {{ $homeCustomStatistic->color }};padding: 0;">
                                    <img loading="lazy"  src="{{ config('app.js_css_url') }}{{ $homeCustomStatistic->icon }}" alt="{{ $title[$i] }}" class="img-fluid"/>
                                </div>
                                <!--<strong class="stat-number mt-10">{{ $homeCustomStatistic->count }}</strong>-->
                              <a href="{{$cate123[$i] }}">  <h4 class="stat-title my-15">{!! nl2br($title[$i]) !!}</h4></a>
                                <!--<p class="stat-desc mt-10">{{ $homeCustomStatistic->description }}</p>-->
                            </div>
                        </div></a>
                        <?php $i++; ?>
                    @endforeach
                </div>
            </div>
        </div>--}}
    @else
        <div class="my-40"></div>
    @endif
@else
    <div class="my-40"></div>
@endif
