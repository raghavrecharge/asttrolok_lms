<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp

<head>
    
    @include('web.default.includes.metas')
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- General CSS File -->
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/simplebar/simplebar.css">
    {{-- <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/app.css"> --}}
    <link rel="stylesheet" href="/assets/default/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    
    

    @if($isRtl)
        <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/rtl-app.css">
    @endif

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeFontsSettings() !!}

        {!! getThemeColorsSettings() !!}
    </style>


    @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
    @endif
    <!-- Google tag (gtag.js) --> 
   
   
  


    <!-- Facebook Pixel Code -->
    <script>
        setTimeout(function() {
            $("[href='https://elfsight.com/google-reviews-widget/?utm_source=websites&utm_medium=clients&utm_content=google-reviews&utm_term=www.asttrolok.com&utm_campaign=free-widget']").hide();
            //   $('.WidgetBackground__Content-sc-1ho7q3r-2 > a').find('.inline').last().attr("style", "display:none !important");
        }, 2000);
    </script>
    <!-- End Facebook Pixel Code -->


    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MH675X5');</script>
    <!-- End Google Tag Manager -->

</head>

<body class="@if($isRtl) rtl @endif">
@php
    $dynamic_rate = [
        '2025' => 4.1,
        '2026' => 4.5,
        '2027' => 4.75,
        '2028' => 4.8,
        '2029' => 4.6,
        '2030' => 4.5,
        '2031' => 4.9,
        '2033' => 4.5,
        '2034' => 4.75,
        '2035' => 4.8,
        '2036' => 4.1,
        '2038' => 4.5,
        '2045' => 4.4,
        '2046' => 4.5,
        '2047' => 4.75,
        '2048' => 4.8,
        '2049' => 4.4,
        '2050' => 4.5,
        '2052' => 4.1,
        '2053' => 4.5,
        '2055' => 4.75,
        '2056' => 4.8,
        '2057' => 4.3,
        '2058' => 4.5,
        '2062' => 4.2,
        '2063' => 4.5,
        '2064' => 4.75,
        '2065' => 4.8,
        '2066' => 4.9,
        '2067' => 4.5,
        '2068' => 4.1,
    ];
@endphp
    
    
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src=""
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div id="app" class="{{ (!empty($floatingBar) and $floatingBar->position == 'top' and $floatingBar->fixed) ? 'has-fixed-top-floating-bar' : '' }}">
        @if(!empty($floatingBar) and $floatingBar->position == 'top')
            @include('web.default.includes.floating_bar')
        @endif

        @if(!isset($appHeader))
            @include('web.default.includes.top_nav')
            @include('web.default.includes.navbar')
        @endif

        @if(!empty($justMobileApp))
            @include('web.default.includes.mobile_app_top_nav')
        @endif

        @yield('content')

    </div>

    @php 
        $agent = new \Jenssegers\Agent\Agent;
    @endphp
    
    @if(!isset($appFooter))
        @if($agent->isMobile())
            @include('web.default.includes.footer')
        @else
            @include('web.default2.includes.footer')
        @endif
    @endif

    {{-- @include('web.default.includes.advertise_modal.index') --}}

    @if(!empty($floatingBar) and $floatingBar->position == 'bottom')
        @include('web.default.includes.floating_bar')
    @endif
    
    <!-- Template JS File -->
    <script src="{{ config('app.js_css_url') }}/assets/default/js/app.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/moment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/toast/jquery.toast.min.js"></script>
    <script type="text/javascript" src="{{ config('app.js_css_url') }}/assets/default/vendors/simplebar/simplebar.min.js"></script>

    @if(empty($justMobileApp) and checkShowCookieSecurityDialog())
        @include('web.default.includes.cookie-security')
    @endif

    <script>
        var deleteAlertTitle = '{{ trans('public.are_you_sure') }}';
        var deleteAlertHint = '{{ trans('public.deleteAlertHint') }}';
        var deleteAlertConfirm = '{{ trans('public.deleteAlertConfirm') }}';
        var deleteAlertCancel = '{{ trans('public.cancel') }}';
        var deleteAlertSuccess = '{{ trans('public.success') }}';
        var deleteAlertFail = '{{ trans('public.fail') }}';
        var deleteAlertFailHint = '{{ trans('public.deleteAlertFailHint') }}';
        var deleteAlertSuccessHint = '{{ trans('public.deleteAlertSuccessHint') }}';
        var forbiddenRequestToastTitleLang = '{{ trans('public.forbidden_request_toast_lang') }}';
        var forbiddenRequestToastMsgLang = '{{ trans('public.forbidden_request_toast_msg_lang') }}';
    </script>

    @if(session()->has('toast'))
        <script>
            (function () {
                "use strict";

                $.toast({
                    heading: '{{ session()->get('toast')['title'] ?? '' }}',
                    text: '{{ session()->get('toast')['msg'] ?? '' }}',
                    bgColor: '@if(session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                    textColor: 'white',
                    hideAfter: 10000,
                    position: 'bottom-right',
                    icon: '{{ session()->get('toast')['status'] }}'
                });
            })(jQuery)
        </script>
    @endif

    @stack('styles_bottom')
    @stack('scripts_bottom')

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/main.min.js"></script>

    <script>
        @if(session()->has('registration_package_limited'))
        (function () {
            "use strict";

            handleLimitedAccountModal('{!! session()->get('registration_package_limited') !!}')
        })(jQuery)

        {{ session()->forget('registration_package_limited') }}
        @endif

        {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
    </script>

    {{-- @include('web.default2.course.pop_up')
    @include('web.default.course.pop_up1') --}}
    
    @php
        $str_arr = explode("/", request()->getRequestUri()); 
        
        // Check for specific page
        if(in_array("what-is-pitru-paksha-and-why-it-is-observed", $str_arr)) {
            
            if(session()->has('started1234')) {
                // Calculate remaining time
                $time = (session('duration1234') - (time() - session('started1234')));
                
                if($time <= 0) {
                    // Session expired, clear it
                    session()->forget(['started1234', 'duration1234']);
                }
            } else {
                // Start new session
                session(['started1234' => time()]);
                session(['duration1234' => (4*60*60)]); // 4 hours
    @endphp
                <script>
                    // Show modal after 30 seconds
                    setTimeout(function() {
                        $('#myModal21').modal();
                    }, 30000);
                </script>
    @php
            }
        }

        // General session handling
        if(session()->has('started123')) {
            $time = (session('duration123') - (time() - session('started123')));
            
            if($time <= 0) {
                // Session expired, clear it
                session()->forget(['started123', 'duration123']);
            }
        } else {
            // Start new session
            session(['started123' => time()]);
            session(['duration123' => (3*24*60*60)]); // 3 days
            
            if(!in_array("what-is-pitru-paksha-and-why-it-is-observed", $str_arr)) {
                // Add any additional logic here if needed
            }
        }
    @endphp

    <style>
        .r-flex {
            display: flex;
            flex-direction: row-reverse;
        }

        /* Uncomment if needed
        .cat-dropdown-menu {
            opacity: 1;
            visibility: visible;
            top: 43px;
        }

        .xs-categories-toggle:not(:hover) > .cat-dropdown-menu {
            visibility: hidden; 
            opacity: 0; 
            transform: translateY(15px);
        }
        */
        
        @media (max-width: 700px) { 
            .mobile4 {
                display: none!important;
            }
        }
    </style>
</body>
</html>