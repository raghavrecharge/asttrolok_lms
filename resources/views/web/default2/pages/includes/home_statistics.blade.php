@php
    $statisticsSettings = getStatisticsSettings();
@endphp

@if(!empty($statisticsSettings['enable_statistics']))
    @if(!empty($statisticsSettings['display_default_statistics']) and !empty($homeDefaultStatistics))
        <div class="stats-container {{ ($heroSection == "2") ? 'page-has-hero-section-2' : '' }} ">
            <div class="container">
                <div class="row categories">
                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box teacher mt-10">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/stats/teacher.svg" alt="" loading="lazy" class="img-fluid"/>
                            </div>

                            <h4 class="stat-title my-25">Astrology</h4>

                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box student mt-10">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/stats/student.svg" alt="" loading="lazy" class="img-fluid"/>
                            </div>

                            <h4 class="stat-title my-25">{{ trans('home.happy_students') }}</h4>

                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box video mt-10">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/stats/video.svg" alt="" loading="lazy" class="img-fluid"/>
                            </div>

                            <h4 class="stat-title my-25">{{ trans('home.live_classes') }}</h4>

                        </div>
                    </div>

                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box course mt-10">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/stats/course.svg" alt="" loading="lazy" class="img-fluid"/>
                            </div>

                            <h4 class="stat-title my-25">{{ trans('home.offline_courses') }}</h4>

                        </div>
                    </div>
                    <div class="col mt-25 mt-lg-0 cats">
                        <div class="stats-item d-flex flex-column align-items-center text-center py-10 px-5 w-100">
                            <div class="stat-icon-box course mt-10">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/stats/course.svg" alt=""  loading="lazy" class="img-fluid"/>
                            </div>

                            <h4 class="stat-title my-25">{{ trans('home.offline_courses') }}</h4>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(!empty($homeCustomStatistics))
        <div class="stats-container ">
            <div class="container">
                <div class="row categories">
                    <?php $i=1;

                $cate123[1]="/classes";
                $cate123[2]="/instructors";
                $cate123[3]="https://asttroveda.asttrolok.com/asttrolok/personalizedkundali";
                $cate123[4]="https://asttroveda.asttrolok.com/numerology/order";
                    ?>
                    @foreach($homeCustomStatistics as $homeCustomStatistic)
                    <?php

                    $urls_cat=$cate123[$i];

                    ?>
                       <a>   <div class="mx-10 px-20 mt-lg-0 mt-lg-0 cats mobile{{ $i }} ">
                            <div class="stats-item d-flex flex-column align-items-center text-center py-20 px-0 " style="width:100%;">
                               <a >   <div class="stat-icon-box" style="background-color: {{ $homeCustomStatistic->color }};padding: 0;">
                                    <img src="{{ config('app.img_dynamic_url') }}{{ $homeCustomStatistic->icon }}" alt="{{ $homeCustomStatistic->title }}" loading="lazy" class="img-fluid"/>
                                </div></a>
                                <a  class=" mt-15">

                              <a>  <h4 class="stat-title mt-5">{{ $homeCustomStatistic->title }}</h4></a>

                            </div>
                        </div></a>
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
