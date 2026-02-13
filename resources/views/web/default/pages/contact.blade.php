@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/leaflet/leaflet.css">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-contact.css">
@endpush
canonical

@section('content')
    <section class="site-top-banner search-top-banner opacity-04 position-relative">
        <img  loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $contactSettings['background'] }}" class="homehide img-cover" alt="{{ $pageTitle ?? 'contact' }}"/>

        <div class="container h-100 banner-contact ">

            <div class="pt-20 d-flex align-items-left align-items-start justify-content-between ">
                <div class="col-md-6 ">
                   <a href="/" class="back-button"><img  loading="lazy"  src="/assets/default/img/profile/left-arrow.svg" alt="Left Arrow Icon - Asttrolok" class="verify-img1  back-button1 py-10 px-10"></a> <h1 class="ml-50 pt-5 text-white font-30 mb-15">Contact Us</h1>
                </div>
                <div class="ft-right mr-30">
                    <svg width="35" height="39" viewBox="0 0 35 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.38698 26.5809C4.38698 23.8259 6.62841 21.5844 9.38347 21.5844H25.6161C28.3711 21.5844 30.6126 23.8259 30.6126 26.5809V31.1185C33.3426 28.0317 35.0005 23.9747 35.0005 19.5298C35.0005 9.8644 27.1651 2.02905 17.4997 2.02905C7.83437 2.02905 -0.000976562 9.86447 -0.000976562 19.5298C-0.000976562 23.9747 1.65698 28.0317 4.38698 31.1185V26.5809Z" fill="#D8ECFE"/>
                        <path d="M29.6527 23.6411C30.2556 24.4667 30.6127 25.4827 30.6127 26.5809V31.1185C33.3427 28.0317 35.0006 23.9747 35.0006 19.5298C35.0006 13.2708 31.7141 7.78018 26.7734 4.68677C29.5478 7.78369 31.2358 11.8741 31.2358 16.3593C31.2358 18.958 30.6687 21.4238 29.6527 23.6411Z" fill="#C4E2FF"/>
                        <path d="M20.0596 21.1159C20.0596 21.3755 20.2708 21.5867 20.5304 21.5867H23.9933V14.6094H23.8338C23.4849 16.795 22.0282 18.6155 20.0596 19.4758V21.1159Z" fill="#C57A44"/>
                        <path d="M14.9405 21.1161V19.476C12.9718 18.6157 11.5151 16.7952 11.1663 14.6096H11.0068V21.587H14.4697C14.7294 21.5869 14.9405 21.3757 14.9405 21.1161Z" fill="#D88A55"/>
                        <path d="M25.6158 21.5083H23.8911L23.8149 21.5845V21.8127C23.8149 25.295 20.9818 28.1281 17.4995 28.1281V28.2043L17.425 28.1262C13.977 28.0859 11.1841 25.27 11.1841 21.8127V21.5881L11.1079 21.5083H9.38321C6.58161 21.5083 4.31055 23.7794 4.31055 26.581V31.0327C4.96463 31.782 5.68187 32.4747 6.45341 33.1032C6.74607 33.3415 6.91166 33.702 6.89338 34.0789L6.69518 38.1607C6.66365 38.81 7.37936 39.2232 7.92589 38.8712L11.6439 36.4769C11.932 36.2914 12.2859 36.2427 12.615 36.3382C14.1653 36.7884 15.804 37.0307 17.4996 37.0307C22.7615 37.0307 27.4805 34.708 30.6886 31.0326V26.581C30.6885 23.7794 28.4173 21.5083 25.6158 21.5083Z" fill="#FE5694"/>
                        <path d="M20.1379 21.116V19.4398C19.3327 19.8046 18.4401 20.0095 17.5001 20.0095C16.56 20.0095 15.6675 19.8046 14.8622 19.4397V21.1159C14.8622 21.3326 14.6866 21.5083 14.4699 21.5083H11.1084V21.8127C11.1084 25.3426 13.97 28.2043 17.5 28.2043C21.0299 28.2043 23.8916 25.3426 23.8916 21.8127V21.5083H20.5301C20.3135 21.5084 20.1379 21.3327 20.1379 21.116Z" fill="#FFB09E"/>
                        <path d="M8.61954 9.00641H11.0852V7.91334C11.0852 5.15958 13.3256 2.91914 16.0794 2.91914H18.9202C21.674 2.91914 23.9144 5.1595 23.9144 7.91334V9.00641H26.38C26.5358 9.00641 26.6874 9.02401 26.8335 9.0563V7.91334C26.8335 3.54991 23.2835 0 18.9201 0H16.0794C11.716 0 8.16602 3.54991 8.16602 7.91342V9.05638C8.31211 9.02401 8.46377 9.00641 8.61954 9.00641Z" fill="#685E68"/>
                        <path d="M20.3594 0.131592C20.8097 0.889273 21.0691 1.77378 21.0691 2.71923V3.40614C22.7502 4.21098 23.9142 5.92873 23.9142 7.91346V9.00653H26.3798C26.5355 9.00653 26.6871 9.02413 26.8333 9.05642V7.91346C26.8334 4.04142 24.0379 0.810359 20.3594 0.131592Z" fill="#554E56"/>
                        <path d="M26.3797 8.92798H23.9141V13.5949C23.9141 13.9401 23.8858 14.2788 23.833 14.6094H26.1604L26.2569 14.6856H26.3797C26.6733 14.6856 26.9577 14.6301 27.2268 14.5205L27.303 14.4041C27.303 14.4041 27.303 14.4041 27.3029 14.4041C28.0442 14.0566 28.5579 13.304 28.5579 12.4311V11.1062C28.5579 9.90321 27.5826 8.92798 26.3797 8.92798Z" fill="#433F43"/>
                        <path d="M11.0853 13.5949V8.92798H8.61962C7.41663 8.92798 6.44141 9.90321 6.44141 11.1062V12.4311C6.44141 13.6341 7.41663 14.6093 8.61962 14.6093H11.1663C11.1136 14.2787 11.0853 13.9401 11.0853 13.5949Z" fill="#7A6D79"/>
                        <path d="M23.8416 8.54504H21.0963C20.705 8.54504 20.3405 8.33457 20.1449 7.99576L19.4596 6.80885C19.3905 6.68903 19.2665 6.61743 19.1281 6.61743C18.9896 6.61743 18.8657 6.68903 18.7965 6.80885L18.1112 7.99576C17.9157 8.33457 17.5511 8.54504 17.1598 8.54504H11.2654L11.0068 8.62349V13.5948C11.0068 17.1808 13.9139 20.0878 17.4999 20.0878C21.0859 20.0878 23.9929 17.1808 23.9929 13.5948V8.62349L23.8416 8.54504Z" fill="#FFDFCF"/>
                        <path d="M23.8417 8.5451H21.0964C21.0874 8.5451 21.0786 8.54442 21.0697 8.54419V13.0069C21.0697 16.5217 18.2765 19.3823 14.7891 19.4947C15.6143 19.8745 16.532 20.0879 17.5001 20.0879C21.0861 20.0879 23.9931 17.1809 23.9931 13.5948V8.62356L23.8417 8.5451Z" fill="#FFCEBF"/>
                        <path d="M13.7348 33.8602C10.3292 33.8602 7.15145 32.8864 4.46289 31.2035C5.07569 31.8873 5.74106 32.5229 6.45349 33.1031C6.74614 33.3416 6.91174 33.7019 6.89346 34.0789L6.69526 38.1607C6.66372 38.8099 7.37944 39.2232 7.92597 38.8712L11.644 36.4769C11.9321 36.2914 12.286 36.2426 12.615 36.3382C14.1654 36.7883 15.8041 37.0306 17.4997 37.0306C22.7616 37.0306 27.4805 34.708 30.6887 31.0326V26.581C30.6887 25.4489 30.3175 24.4036 29.6906 23.5596C26.9453 29.6336 20.8337 33.8602 13.7348 33.8602Z" fill="#FD3581"/>
                        <path d="M18.9202 2.84058H16.0795C13.2779 2.84058 11.0068 5.11172 11.0068 7.91324V8.62339H17.1598C17.5804 8.62339 17.9689 8.39907 18.1792 8.03489L18.8644 6.84798C18.9816 6.64506 19.2745 6.64506 19.3916 6.84798L20.0768 8.03489C20.2871 8.39907 20.6757 8.62339 21.0962 8.62339H23.9928V7.91324C23.9928 5.11172 21.7217 2.84058 18.9202 2.84058Z" fill="#D88A55"/>
                        <path d="M21.0693 3.31836V8.62274C21.0782 8.62297 21.0872 8.62365 21.0961 8.62365H23.9927V7.9135C23.9927 5.88032 22.7962 4.12746 21.0693 3.31836Z" fill="#C57A44"/>
                        <path d="M17.5 31.6036C17.1845 31.6036 16.9287 31.3478 16.9287 31.0323V30.7969C16.9287 30.4814 17.1845 30.2256 17.5 30.2256C17.8155 30.2256 18.0713 30.4814 18.0713 30.7969V31.0323C18.0713 31.3478 17.8155 31.6036 17.5 31.6036Z" fill="white"/>
                        <path d="M17.5 34.7455C17.1845 34.7455 16.9287 34.4897 16.9287 34.1742V33.9387C16.9287 33.6232 17.1845 33.3674 17.5 33.3674C17.8155 33.3674 18.0713 33.6232 18.0713 33.9387V34.1742C18.0713 34.4897 17.8155 34.7455 17.5 34.7455Z" fill="white"/>
                        <path d="M14.9639 12.0356C14.6484 12.0356 14.3926 11.7798 14.3926 11.4643V10.957C14.3926 10.6415 14.6484 10.3857 14.9639 10.3857C15.2794 10.3857 15.5352 10.6415 15.5352 10.957V11.4643C15.5352 11.7798 15.2794 12.0356 14.9639 12.0356Z" fill="#FFB09E"/>
                        <path d="M20.0361 12.0356C19.7206 12.0356 19.4648 11.7798 19.4648 11.4643V10.957C19.4648 10.6415 19.7206 10.3857 20.0361 10.3857C20.3516 10.3857 20.6074 10.6415 20.6074 10.957V11.4643C20.6074 11.7798 20.3516 12.0356 20.0361 12.0356Z" fill="#FFB09E"/>
                        <path d="M26.3802 14.6093H26.161V15.831C26.161 16.3551 25.7346 16.7815 25.2105 16.7815H19.1992V17.7678L19.2754 17.9241H25.2105C26.3646 17.9241 27.3036 16.9851 27.3036 15.831V14.4041C27.0232 14.5355 26.7104 14.6093 26.3802 14.6093Z" fill="#685E68"/>
                        <path d="M19.2755 17.9242H16.5295C16.085 17.9242 15.7246 17.5639 15.7246 17.1193V16.1928C15.7246 15.7483 16.085 15.3879 16.5295 15.3879H18.4705C18.9151 15.3879 19.2754 15.7483 19.2754 16.1928V17.9242H19.2755Z" fill="#7A6D79"/>
                        </svg>

                </div>
            </div>

        </div>
    </section>

    <div class="container">
        <section class=" ">
            @if(!empty($contactSettings['latitude']) and !empty($contactSettings['longitude']))

            @endif

            <div class="row">
                <div class="col-6 col-md-4 mobilegrid1">
                    <a href="https://maps.app.goo.gl/SogMK8SxiX9eHbRc9">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">

                        <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.745117" y="0.344482" width="45.0353" height="45.0353" rx="5.62942" fill="url(#paint0_linear_1182_4335)"/>
                            <path d="M35.4428 28.7689C35.0262 28.5401 34.5031 28.6923 34.2744 29.1088C34.0455 29.5253 34.1977 30.0485 34.6142 30.2772C35.6392 30.8403 36.2271 31.4646 36.2271 31.9899C36.2271 32.6325 35.3167 33.6283 32.7615 34.4964C30.2371 35.3541 26.8637 35.8264 23.2626 35.8264C19.6615 35.8264 16.288 35.3541 13.7637 34.4964C11.2085 33.6284 10.2981 32.6325 10.2981 31.9899C10.2981 31.4646 10.886 30.8403 11.911 30.2772C12.3275 30.0484 12.4796 29.5252 12.2508 29.1087C12.022 28.6922 11.499 28.5401 11.0823 28.7689C9.93951 29.3967 8.57715 30.4652 8.57715 31.9899C8.57715 33.1587 9.38095 34.825 13.2101 36.1259C15.908 37.0426 19.4781 37.5474 23.2626 37.5474C27.0471 37.5474 30.6171 37.0426 33.3151 36.1259C37.1442 34.825 37.948 33.1587 37.948 31.9899C37.948 30.4652 36.5857 29.3967 35.4428 28.7689Z" fill="white"/>
                            <path d="M16.7661 33.2525C18.5129 33.7459 20.82 34.0177 23.2626 34.0177C25.7051 34.0177 28.0123 33.746 29.7591 33.2525C31.8961 32.6488 32.9796 31.7749 32.9796 30.6553C32.9796 29.5356 31.8961 28.6618 29.7591 28.0581C29.2846 27.9241 28.7687 27.8066 28.2202 27.7063C27.9223 28.221 27.6102 28.7508 27.2838 29.2958C27.891 29.3869 28.461 29.4986 28.9787 29.6304C30.5514 30.031 31.1259 30.482 31.2441 30.6553C31.1259 30.8287 30.5514 31.2797 28.9787 31.6802C27.4902 32.0593 25.575 32.2756 23.5578 32.2949C23.4601 32.3022 23.3617 32.3062 23.2626 32.3062C23.1634 32.3062 23.065 32.3022 22.9673 32.2949C20.9501 32.2756 19.035 32.0594 17.5464 31.6802C15.9737 31.2797 15.3992 30.8287 15.281 30.6553C15.3992 30.482 15.9737 30.031 17.5464 29.6304C18.0641 29.4986 18.6341 29.3869 19.2414 29.2958C18.915 28.7508 18.6029 28.2209 18.305 27.7063C17.7565 27.8066 17.2405 27.9241 16.7661 28.0581C14.6291 28.6618 13.5455 29.5356 13.5455 30.6553C13.5455 31.7749 14.6291 32.6488 16.7661 33.2525Z" fill="white"/>
                            <path d="M23.2626 30.5852C24.0286 30.5852 24.7244 30.1949 25.124 29.5411C27.9239 24.9601 31.2616 18.9227 31.2616 16.1754C31.2616 11.7648 27.6733 8.17651 23.2626 8.17651C18.8519 8.17651 15.2636 11.7648 15.2636 16.1754C15.2636 18.9227 18.6013 24.9601 21.4012 29.5411C21.8008 30.1949 22.4966 30.5852 23.2626 30.5852ZM20.0474 15.6262C20.0474 13.8535 21.4898 12.4112 23.2626 12.4112C25.0354 12.4112 26.4777 13.8535 26.4777 15.6262C26.4777 17.399 25.0354 18.8413 23.2626 18.8413C21.4898 18.8413 20.0474 17.3991 20.0474 15.6262Z" fill="white"/>
                            <defs>
                            <linearGradient id="paint0_linear_1182_4335" x1="0.745117" y1="0.344482" x2="45.7804" y2="45.3798" gradientUnits="userSpaceOnUse">
                            <stop offset="0.1" stop-color="#3C8CE7"/>
                            <stop offset="1" stop-color="#00EAFF"/>
                            </linearGradient>
                            </defs>
                            </svg>

                        <h3 class="mt-10 font-16 font-weight-bold text-dark-blue">{{ trans('site.our_address') }}</h3>
                        @if(!empty($contactSettings['address']))
                            <p class="font-weight-500 font-12 text-gray mt-10">{!! nl2br($contactSettings['address']) !!}</p>
                        @else
                            <p class="font-weight-500 text-gray font-12 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 mobilegrid1">
                    <a href="tel:09174822333">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">

                        <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.972656" y="0.344482" width="45.0353" height="45.0353" rx="5.62942" fill="url(#paint0_linear_1182_4345)"/>
                            <path d="M26.5575 36.242C28.2192 36.242 29.777 35.7198 31.1271 34.3622L32.3733 33.1091C33.308 32.1692 33.308 30.7072 32.3733 29.7673L29.1539 26.53C28.2192 25.5901 26.7652 25.5901 25.8306 26.53L25.1036 27.261C24.5843 27.7831 22.715 27.1566 20.8456 25.2768C18.9762 23.3971 18.3531 21.5174 18.8724 20.9952L19.5994 20.2642C20.534 19.3243 20.534 17.8623 19.5994 16.9225L16.4838 13.894C15.5491 12.9541 14.0951 12.9541 13.1604 13.894L11.9142 15.1472C7.96778 19.1155 11.0834 26.0078 15.6529 30.6028C18.8724 33.7356 23.0265 36.242 26.5575 36.242Z" fill="white"/>
                            <path d="M24.4805 10.1345C23.8573 10.1345 23.4419 10.5522 23.4419 11.1788C23.4419 11.8054 23.8573 12.2231 24.4805 12.2231C29.5693 12.2231 33.6196 16.4003 33.6196 21.4129C33.6196 22.0395 34.035 22.4572 34.6581 22.4572C35.2812 22.4572 35.6966 22.0395 35.6966 21.4129C35.6966 15.2516 30.7117 10.1345 24.4805 10.1345Z" fill="white"/>
                            <path d="M24.4805 16.4003C27.2845 16.4003 29.4654 18.6978 29.4654 21.4129C29.4654 22.0395 29.8808 22.4572 30.504 22.4572C31.1271 22.4572 31.5425 22.0395 31.5425 21.4129C31.6463 17.4446 28.4269 14.3117 24.4805 14.3117C23.8573 14.3117 23.4419 14.7294 23.4419 15.356C23.4419 15.9826 23.9612 16.4003 24.4805 16.4003Z" fill="white"/>
                            <path d="M24.4805 20.4731C24.9997 20.4731 25.4151 20.8908 25.4151 21.4129C25.4151 22.0395 25.8306 22.4572 26.4537 22.4572C27.0768 22.4572 27.4922 22.0395 27.4922 21.4129C27.4922 19.7421 26.1421 18.3845 24.4805 18.3845C23.8573 18.3845 23.4419 18.8022 23.4419 19.4288C23.4419 20.0553 23.9612 20.4731 24.4805 20.4731Z" fill="white"/>
                            <defs>
                            <linearGradient id="paint0_linear_1182_4345" x1="0.972656" y1="0.344482" x2="46.008" y2="45.3798" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#11C99C"/>
                            <stop offset="1" stop-color="#00E31D"/>
                            </linearGradient>
                            </defs>
                            </svg>

                        <h3 class="mt-10 font-16 font-weight-bold text-dark-blue">{{ trans('site.phone_number') }}</h3>
                        @if(!empty($contactSettings['phones']))
                            <p class="font-weight-500 text-gray font-12 mt-10">{!! nl2br(str_replace(',','<br/>',$contactSettings['phones'])) !!}</p>
                        @else
                            <p class="font-weight-500 text-gray font-12 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 mobilegrid1">
                    <a href="mailto:astrolok.vedic@gmail.com">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">

                        <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.745117" y="0.703369" width="45.0353" height="45.0353" rx="5.62942" fill="url(#paint0_linear_1182_4369)"/>
                            <path d="M36.5244 14.6437L24.8962 25.9454C24.3701 26.4683 23.6788 26.7297 22.9875 26.7297C22.2962 26.7297 21.6049 26.4683 21.0788 25.9454L9.55084 14.4605C9.32781 14.877 9.18945 15.3446 9.18945 15.8484V30.1538C9.18945 30.6569 9.32743 31.1239 9.54993 31.54L15.7885 25.3493C16.14 25.0001 16.7095 25.0001 17.061 25.3493C17.4125 25.6982 17.4125 26.2646 17.061 26.6136L10.8309 32.7959C11.2406 33.0049 11.6977 33.1341 12.189 33.1341H33.786C34.2773 33.1341 34.7345 33.0049 35.1441 32.7959L28.914 26.6135C28.5625 26.2646 28.5625 25.6982 28.914 25.3492C29.2655 25 29.835 25 30.1865 25.3492L36.4251 31.54C36.6476 31.1239 36.7856 30.6569 36.7856 30.1538V15.8484C36.7856 15.419 36.6896 15.0129 36.5244 14.6437Z" fill="#F7F7F7"/>
                            <path d="M33.786 12.8682H12.189C11.6984 12.8682 11.2418 12.997 10.8325 13.2054L22.3513 24.6811C22.7022 25.0298 23.2728 25.0298 23.6238 24.6811L35.3279 13.3055C34.8758 13.0338 34.3518 12.8682 33.786 12.8682Z" fill="#F7F7F7"/>
                            <defs>
                            <linearGradient id="paint0_linear_1182_4369" x1="0.745117" y1="0.703369" x2="45.7804" y2="45.7387" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#0821FF"/>
                            <stop offset="1" stop-color="#B499FF"/>
                            </linearGradient>
                            </defs>
                            </svg>

                        <h3 class="mt-10 font-16 font-weight-bold text-dark-blue">{{ trans('public.email') }}</h3>
                        @if(!empty($contactSettings['emails']))
                            <p class="font-weight-500 text-gray font-12 mt-10">{!! nl2br(str_replace(',','<br/>',$contactSettings['emails'])) !!}</p>
                        @else
                            <p class="font-weight-500 text-gray font-12 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 mobilegrid1">
                    <a href="mailto:astrolok.vedic@gmail.com">
                    <div class="contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">

                        <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.782227" y="0.790283" width="45.0353" height="45.0353" rx="5.62942" fill="url(#paint0_linear_1182_4371)"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4803 35.7062C26.4803 34.5914 25.597 33.6877 24.5074 33.6877H22.0916C21.5683 33.6877 21.0665 33.9003 20.6965 34.2789C20.3265 34.6574 20.1187 35.1709 20.1187 35.7062C20.1187 36.821 21.002 37.7248 22.0916 37.7248H24.5074C25.597 37.7248 26.4803 36.821 26.4803 35.7062ZM11.6846 30.5012C12.0142 30.527 12.4129 30.5418 12.8133 30.5178C13.0267 31.6247 13.5567 32.6515 14.3478 33.461C15.3981 34.5355 16.8226 35.1392 18.3079 35.1392H19.0606C19.0274 35.3251 19.0104 35.5148 19.0104 35.7062C19.0104 35.8998 19.0274 36.0894 19.0601 36.2732H18.3079C16.5286 36.2732 14.8222 35.55 13.5641 34.2628C12.5604 33.2358 11.9076 31.917 11.6846 30.5012ZM11.1209 29.2946C10.2459 29.1437 9.43167 28.7181 8.79607 28.0677C7.98997 27.2429 7.53711 26.1243 7.53711 24.9579V22.2778C7.53711 21.1114 7.98997 19.9927 8.79607 19.168C9.60216 18.3432 10.6955 17.8798 11.8355 17.8798H12.1216C12.579 11.9734 17.4088 7.32593 23.2995 7.32593C29.1902 7.32593 34.02 11.9734 34.4773 17.8798H34.7635C35.9035 17.8798 36.9968 18.3432 37.8029 19.168C38.609 19.9927 39.0618 21.1114 39.0618 22.2778V24.9579C39.0618 26.1243 38.609 27.2429 37.8029 28.0677C36.9968 28.8925 35.9035 29.3558 34.7635 29.3558H33.495C32.969 29.3558 32.5425 28.9195 32.5425 28.3813V18.7989C32.5425 13.5759 28.4043 9.34184 23.2995 9.34184C18.1947 9.34184 14.0564 13.5759 14.0564 18.7989V28.3813C14.0564 28.7613 13.8438 29.0906 13.5336 29.2513C12.6798 29.5544 11.3555 29.3351 11.1209 29.2946Z" fill="white"/>
                            <defs>
                            <linearGradient id="paint0_linear_1182_4371" x1="0.782227" y1="0.790283" x2="45.8176" y2="45.8256" gradientUnits="userSpaceOnUse">
                            <stop offset="0.1" stop-color="#F761A1"/>
                            <stop offset="1" stop-color="#8C1BAB"/>
                            </linearGradient>
                            </defs>
                            </svg>

                        <h3 class="mt-10 font-16 font-weight-bold text-dark-blue">Help</h3>
                        @if(!empty($contactSettings['emails']))
                            <p class="font-weight-500 text-gray font-12 mt-10">Our any other help contact us</p>
                        @else
                            <p class="font-weight-500 text-gray font-12 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="mt-30 mb-80 mt-md-50 p-20 shadow-sm">
            <h2 class="font-16 font-weight-bold text-secondary">{{ trans('site.send_your_message_directly') }}</h2>

            @if(!empty(session()->has('msg')))
                <div class="alert alert-success my-25 d-flex align-items-center">
                    <i data-feather="check-square" width="50" height="50" class="mr-2"></i>
                    {{ session()->get('msg') }}
                </div>
            @endif

            <form action="/contact/store" method="post" class="mt-20">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label font-weight-500 homehide">{{ trans('site.your_name') }}*</label>
                            <input type="text" name="name" placeholder="Enter your Full Name here" maxlength="60" value="{{ old('name') }}" class="form-control @error('name')  is-invalid @enderror"/>
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label font-weight-500 homehide">{{ trans('public.email') }}*</label>
                            <input type="text" name="email" placeholder="Enter your E-mail ID here" maxlength="60" value="{{ old('email') }}" class="form-control @error('email')  is-invalid @enderror"/>
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label font-weight-500 homehide">{{ trans('site.phone_number') }}*</label>
                            <input type="text" name="phone" placeholder="Enter your Phone Number here" maxlength="10" value="{{ old('phone') }}" class="form-control @error('phone')  is-invalid @enderror"/>
                            @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label font-weight-500 homehide">{{ trans('site.subject') }}*</label>
                            <input type="text" name="subject" placeholder="Enter your Subject here" maxlength="100" value="{{ old('subject') }}" class="form-control @error('subject')  is-invalid @enderror"/>
                            @error('subject')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="input-label font-weight-500 homehide">{{ trans('site.message') }}</label>
                            <textarea name="message" id="" rows="5" placeholder="Type your Message here in min 30 words" maxlength="400" class="form-control @error('message')  is-invalid @enderror">{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        @include('web.default.includes.captcha_input')
                    </div>
                </div>
                <div style="
    display: flex;
    justify-content: center;
">
                <button type="submit" class="btn btn-primary mt-20">{{ trans('site.send_message') }}</button>
                </div>
            </form>
        </section>

        <section class="pb-20">
            <h2 class="text-center">Frequently Asked Questions</h2>

            <div class="row">
                <div class="col-12">
                    <div class="accordion-content-wrapper" id="chaptersAccordion" role="tablist" aria-multiselectable="true">
                        <div class="accordion-row rounded-sm border mt-20">
                            <div class="d-flex align-items-center justify-content-between p-10" role="tab" id="chapter_68">
                                <div
                                    class="js-chapter-collapse-toggle d-flex align-items-center"
                                    href="#collapseChapter68"
                                    aria-controls="collapseChapter68"
                                    data-parent="#chaptersAccordion"
                                    role="button"
                                    data-toggle="collapse"
                                    aria-expanded="true"
                                >

                                    <span class="ml-10 font-weight-bold text-secondary font-14">What is Astrology?</span>
                                </div>

                                <div class="d-flex align-items-center">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather collapse-chevron-icon feather-chevron-up"
                                        href="#collapseChapter68"
                                        aria-controls="collapseChapter68"
                                        data-parent="#chaptersAccordion"
                                        role="button"
                                        data-toggle="collapse"
                                        aria-expanded="true"
                                    >
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <div id="collapseChapter68" aria-labelledby="chapter_68" class="collapse show" role="tabpanel" style="">
                                <div class="">
                                    <div class="accordion-row rounded-sm p-10 border-top1">
                                        <div class="d-flex align-items-center justify-content-between" role="tab" id="files_162">
                                            <div class="d-flex align-items-center collapsed" href="#collapseFiles162" aria-controls="collapseFiles162" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="false">

                                                <span class=" ml-10 text-secondary font-14 file-title">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.</span>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="accordion-row rounded-sm border mt-20">
                            <div class="d-flex align-items-center justify-content-between p-10" role="tab" id="chapter_69">
                                <div
                                    class="js-chapter-collapse-toggle d-flex align-items-center collapsed"
                                    href="#collapseChapter69"
                                    aria-controls="collapseChapter69"
                                    data-parent="#chaptersAccordion"
                                    role="button"
                                    data-toggle="collapse"
                                    aria-expanded="false"
                                >

                                    <span class="font-weight-bold text-secondary font-14 ml-10">How does astrology deiffer from astronomy?</span>
                                </div>

                                <div class="d-flex align-items-center">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-chevron-down collapse-chevron-icon collapsed"
                                        href="#collapseChapter69"
                                        aria-controls="collapseChapter69"
                                        data-parent="#chaptersAccordion"
                                        role="button"
                                        data-toggle="collapse"
                                        aria-expanded="false"
                                    >
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <div id="collapseChapter69" aria-labelledby="chapter_69" class="collapse" role="tabpanel">
                                <div class="">
                                    <div class="accordion-row rounded-sm p-10 border-top1">
                                        <div class="d-flex align-items-center justify-content-between" role="tab" id="files_163">
                                            <div class="d-flex align-items-center collapsed" href="#collapseFiles163" aria-controls="collapseFiles163" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="false">

                                                <span class="ml-10 text-secondary font-14 file-title">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable.</span>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="accordion-row rounded-sm border mt-20">
                            <div class="d-flex align-items-center justify-content-between p-10" role="tab" id="chapter_70">
                                <div
                                    class="js-chapter-collapse-toggle d-flex align-items-center collapsed"
                                    href="#collapseChapter70"
                                    aria-controls="collapseChapter70"
                                    data-parent="#chaptersAccordion"
                                    role="button"
                                    data-toggle="collapse"
                                    aria-expanded="false"
                                >

                                    <span class="font-weight-bold text-secondary font-14 ml-10">What are horoscopes and how are they created?</span>
                                </div>

                                <div class="d-flex align-items-center">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-chevron-down collapse-chevron-icon collapsed"
                                        href="#collapseChapter70"
                                        aria-controls="collapseChapter70"
                                        data-parent="#chaptersAccordion"
                                        role="button"
                                        data-toggle="collapse"
                                        aria-expanded="false"
                                    >
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <div id="collapseChapter70" aria-labelledby="chapter_70" class="collapse" role="tabpanel">
                                <div class="">
                                    <div class="accordion-row rounded-sm p-10 border-top1">
                                        <div class="d-flex align-items-center justify-content-between" role="tab" id="files_164">
                                            <div class="d-flex align-items-center collapsed" href="#collapseFiles164" aria-controls="collapseFiles164" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="false">

                                                <span class="ml-10 text-secondary font-14 file-title">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</span>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="accordion-row rounded-sm border mt-20">
                            <div class="d-flex align-items-center justify-content-between p-10" role="tab" id="chapter_95">
                                <div
                                    class="js-chapter-collapse-toggle d-flex align-items-center collapsed"
                                    href="#collapseChapter95"
                                    aria-controls="collapseChapter95"
                                    data-parent="#chaptersAccordion"
                                    role="button"
                                    data-toggle="collapse"
                                    aria-expanded="false"
                                >

                                    <span class="font-weight-bold text-secondary font-14 ml-10">Can astrology predict the future?</span>
                                </div>

                                <div class="d-flex align-items-center">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-chevron-down collapse-chevron-icon collapsed"
                                        href="#collapseChapter95"
                                        aria-controls="collapseChapter95"
                                        data-parent="#chaptersAccordion"
                                        role="button"
                                        data-toggle="collapse"
                                        aria-expanded="false"
                                    >
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <div id="collapseChapter95" aria-labelledby="chapter_95" class="collapse" role="tabpanel">
                                <div class="">
                                    <div class="accordion-row rounded-sm p-10 border-top1">
                                        <div class="d-flex align-items-center justify-content-between" role="tab" id="files_224">
                                            <div class="d-flex align-items-center collapsed" href="#collapseFiles224" aria-controls="collapseFiles224" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="false">

                                                <span class="ml-10 text-secondary font-14 file-title">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable.</span>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="accordion-row rounded-sm border mt-20">
                            <div class="d-flex align-items-center justify-content-between p-10" role="tab" id="chapter_104">
                                <div
                                    class="js-chapter-collapse-toggle d-flex align-items-center collapsed"
                                    href="#collapseChapter104"
                                    aria-controls="collapseChapter104"
                                    data-parent="#chaptersAccordion"
                                    role="button"
                                    data-toggle="collapse"
                                    aria-expanded="false"
                                >

                                    <span class="font-weight-bold text-secondary font-14 ml-10">What are the twelve zodiac signs, and what do they reoresent?</span>
                                </div>

                                <div class="d-flex align-items-center">

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-chevron-down collapse-chevron-icon collapsed"
                                        href="#collapseChapter104"
                                        aria-controls="collapseChapter104"
                                        data-parent="#chaptersAccordion"
                                        role="button"
                                        data-toggle="collapse"
                                        aria-expanded="false"
                                    >
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <div id="collapseChapter104" aria-labelledby="chapter_104" class="collapse" role="tabpanel">
                                <div class="">
                                    <div class="accordion-row rounded-sm p-10 border-top1">
                                        <div class="d-flex align-items-center justify-content-between" role="tab" id="files_307">
                                            <div class="d-flex align-items-center collapsed" href="#collapseFiles307" aria-controls="collapseFiles307" data-parent="#chaptersAccordion" role="button" data-toggle="collapse" aria-expanded="false">

                                                <span class="ml-10 text-secondary font-14 file-title">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English.</span>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/vendors/leaflet/leaflet.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/contact.min.js"></script>
@endpush
