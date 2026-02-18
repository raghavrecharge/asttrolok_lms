@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-remedies.css">
    <link rel="stylesheet" href="/public/assets/design_1/css/home_mobile_css/index.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <style>
 html,
body {
    margin: 0;
    padding: 0;
    background-color: #F4FFF2 !important;
}
.consultation-page-frame5 {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px; /* Height kam karne ke liye padding kam karein */
    border-radius: 999px;
    border: 1px solid rgba(218, 218, 218, 1);
    background-color: #f2f2f2;
}
.search-btn,
.close-btn {
    margin-top: 0 !important;
}
/* Inner wrapper: icons + input ek line me */
.search-wrapper {
    justify-content: center !important;
}
.search-box {
    width: auto !important;  
    text-align: center !important; /* text center */
}
.search-box {
    text-align: center !important;   /* horizontal center */
}

/* Sirf placeholder ke लिए (कुछ browsers में जरूरत पड़ती है) */
.search-box::placeholder {
    text-align: center;
}
/* Icons left/right fixed width */
.search-btn{
 
    width: 35px;
    height: 35px;
    display: flex;
    align-items: left;
    justify-content: center;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;

}
.close-btn {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;
}

.search-btn img,
.close-btn img {
    width: 18px;
    height: 18px;
}

/* Input center me */
.search-box {
    flex: 1;                   /* beech ka area le */
    border: none;
    background: transparent;
    font-size: 14px;
    line-height: 1.2;
    padding: 0;
    margin: 0;
    text-align: center;        /* text + placeholder center */
    outline: none;
}


.search-wrapper {
    display: flex;
    align-items: flex-end; /* Input ko bottom par lane ke liye */
    justify-content: center;
    flex: 1;
    height: 32px;
    padding-bottom: 2px; /* Optional: thoda niche lane ke liye */
}

search-box {
    border: none;
    margin-top: auto;
    background: transparent;
    flex-grow: 1;
    font-size: 15px;
    outline: none;
    text-align: center;
}
.search-box {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 14px;
    line-height: 1.2;
    padding: 0;
    margin: 0;
    text-align: center;
    outline: none;
}
.search-box {
    text-align: center !important;
}

.search-box {
    width: auto !important;
    text-align: center !important;
}
.search-box {
    margin-top: 4px;
    text-align: center !important;
}
.search-btn img, .close-btn img {

    width: 18px;
    height: 18px;
}
.search-btn {
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;

}


.consultation-page-frame5 {
    padding: 4px 12px !important;
}
    /* Existing remedies image height tweaks */
    .webinar-card .image-box,
    .remediescss .image-box {
        position: relative;
        width: 100%;
        height: 250px !important;
    }

    @media (min-width: 320px) and (max-width: 424px) {
        .webinar-card.grid-card .image-box {
            position: relative;
            width: 100%;
            height: 250px !important;
        }
    }
    @media (min-width: 424px) and (max-width: 660px) {
        .webinar-card.grid-card .image-box {
            position: relative;
            width: 100%;
            height: 250px !important;
        }
    }

   

    .consult-banner {
      width: calc(100% - 20px) !important;
    margin: 10px !important;
        margin: 12px auto 0;
        padding: 12px 16px;
        border-radius: 16px;
        background-image: url("/assets/design_1/img/instructors/public/tq_31gyia7qxt-7k8g-3600h.png");
        background-size: cover;
        background-position: center;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .consult-banner-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .consult-banner-line1 { font-size: 16px; font-weight: 400; }
    .consult-banner-line2 { font-size: 20px; font-weight: 700; }
    .consult-banner-line3 { font-size: 15px; font-weight: 400; }
    .consult-banner-line3 .price { font-weight: 700; }

    .consult-banner-arrow {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .checkbox-button.primary-selected {
        max-width: 380px;
        width: 100%;
        margin: 16px auto 12px;
    }

    .consultation-page-frame5 {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        border: 1px solid rgba(218, 218, 218, 1);
        background-color: #f2f2f2;
    }



    /* Reduce gap below search bar */
    .checkbox-button.primary-selected {
        margin-bottom: 8px !important;
    }

    .consultation-page-frame5 {
        margin-bottom: 4px !important;
    }

    .container.mt-30 {
        margin-top: 12px !important;
    }

    @media (min-width: 576px) {
      .checkbox-button.primary-selected {
        max-width: 540px;
      }
    }

    @media (min-width: 768px) {
      .checkbox-button.primary-selected {
        max-width: 720px;
      }
    }
    </style>
@endpush

@section('content')
    {{-- Black banner (consult जैसा look, remedies text) --}}
    <div class="consult-banner mb-20">
        <div class="consult-banner-text">
            <div class="consult-banner-line1">
              Ancient solutions for modern life
            </div>
            <div class="consult-banner-line2">
               Cosmic Healing Solutions
            </div>
            <div class="consult-banner-line3">
               Explore for you suitable solutions
            </div>
        </div>
    </div>
<div style=" margin: 0 15px
;">
    @include('web.default.includes.search')
</div>
    {{-- Filters + listing --}}
    <div class="container mt-5">
        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">
            <div class="row  px-15">
                <div class="col-12 col-lg-12">
                    @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                        <div class="row mt-0">
                            @foreach($remedies as $remedy)
                                <div class="col-12 col-lg-4 mt-20 loadid mobilegrid">
                                    @include('web.default.includes.remedy.grid-card',['remedy' => $remedy])
                                </div>
                            @endforeach
                        </div>
                    @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')
                        @foreach($remedies as $remedy)
                            <div class="mt-20 load-card-list">
                                @include('web.default.includes.remedy.list-card',['remedy' => $remedy])
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="mt-50 pt-30">
                {{ $remedies->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        </section>
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/categories.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/instructors.min.js"></script>
@endpush
