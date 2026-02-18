@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app.css">
     <link rel="canonical" href="https://www.asttrolok.com/consult-with-astrologers" />
      <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/profile.min.css">

    <script>
      gtag('event', 'conversion', {'send_to': 'AW-795191608/yJCeCNrvt5cZELjSlvsC'});
    </script>

  <style>

        .card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            width:200px;
            margin: 8px;
            border: 2px solid #E8E8E8
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 16px;
            overflow: hidden;
            background: linear-gradient(135deg, #ffc7c7 0%, #ffe4b5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 6px;
            min-height: 41px;
        }

        .specialization {
            font-size: 13px;
            color: #666;
            margin-bottom: 4px;
        }

        .experience {
            font-size: 12px;
            color: #888;
            margin-bottom: 16px;
        }

        .pricing {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-bottom: 8px;
        }

        .price {
            font-size: 21px;
            font-weight: 700;
            color: #0d9f4f;
        }

        .currency {
            font-size: 20px;
        }

        .per-min {
            font-size: 12px;
            color: #424040;
            font-weight: 400;
        }

        .rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 16px;
        }

        .stars {
            display: flex;
            gap: 2px;
        }

        .star {
            color: #ffc107;
            font-size: 12px;
        }

        .rating-badge {
            background: #0d9f4f;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .book-btn {
            width: 100%;
            background: #0d9f4f;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .book-btn:hover {
            background: #0b8543;
            transform: scale(1.02);
        }

        .book-btn:active {
            transform: scale(0.98);
        }

        @media (max-width: 768px) {

            .card {
                padding: 16px;
            }
        }
        .consultation-card {
    font-family: 'Montserrat', sans-serif !important;
}

 .profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: 0 auto 16px;
    overflow: hidden;
    background: linear-gradient(135deg, #ffc7c7 0%, #ffe4b5 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
    </style>
@endpush

@section('content')
<div class="profile-cover-card">
        <img src="https://storage.googleapis.com/astrolok/webp/store/1/banner/Consult.webp" class="img-cover" alt="">
</div>

    <div class="mob-cat container" style="z-index: 9;max-width: 1140px !important;margin-top: -245px;background-color: #ffffff;position: relative;opacity: 1;border-radius: 24px;">

        <section>
            @php

                $to_day=date("l");
            @endphp

            <div id="instructorsList" class=" mt-20">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'show active' : '' }}" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        <div class="row">
                            @foreach($consult as $instructor2)
                                @php
                                    $canReserve2 = false;
                                    if(!empty($instructor2->meeting) and !$instructor2->meeting->disabled and !empty($instructor2->meeting->meetingTimes) and $instructor2->meeting->meeting_times_count > 0) {
                                        $canReserve2 = true;
                                    }

                                     if($canReserve2){

                                @endphp
                                @if($instructor2->consultant == 1)
                                            @include('web.default2.pages.instructor_card1',['instructor' => $instructor2])
                                        @endif
                                    @php
                                        }
                                    @endphp
                            @endforeach

                        </div>
                    </div>
                </div>
             </div>
        </section>
     </div>

    </div>

@endsection

@push('scripts_bottom')

@endpush
