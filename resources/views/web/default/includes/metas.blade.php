{{-- Essential Meta Tags --}}
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Dynamic Robots --}}
    @if (isset($robots) && !empty($robots))
        <meta name='robots' content="{{ $robots }}">
    @else
        <meta name='robots' content="{{ $pageRobot ?? 'follow, index' }}">
    @endif

    {{-- Dynamic Description & OG --}}
    @if (isset($pageDescription) && !empty($pageDescription))
        <meta name="description" content="{{ $pageDescription }}">
        <meta property="og:description" content="{{ $ogDescription ?? $pageDescription }}">
        <meta name='twitter:description' content="{{ $ogDescription ?? $pageDescription }}">
    @endif

    {{-- Favicon & PWA --}}
    <link rel='shortcut icon' type='image/x-icon' href="{{ url($generalSettings['fav_icon'] ?? '') }}">
    <link rel="manifest" href="/mix-manifest.json?v=4">
    <meta name="theme-color" content="#FFF">
    <meta name="msapplication-starturl" content="/">
    <meta name="msapplication-TileColor" content="#FFF">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">

    {{-- Apple PWA --}}
    <meta name="apple-mobile-web-app-title" content="{{ $generalSettings['site_name'] ?? '' }}">
    <link rel="apple-touch-icon" href="{{ url($generalSettings['fav_icon'] ?? '') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    {{-- Web App Meta --}}
    <link rel='icon' href="{{ url($generalSettings['fav_icon'] ?? '') }}">
    <meta name="application-name" content="{{ $generalSettings['site_name'] ?? '' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="layoutmode" content="fitscreen/standard">
    <link rel="home" href="{{ url('') }}">

    {{-- Social OG Tags --}}
    <meta property='og:title' content="{{ $pageTitle ?? '' }}">
    <meta name='twitter:card' content='summary'>
    <meta name='twitter:title' content="{{ $pageTitle ?? '' }}">
    <meta property='og:site_name' content="{{ url($generalSettings['site_name'] ?? '') }}">

    {{-- Dynamic OG Image --}}
    @php
        $main_url = request()->path();
        $url_array = explode("/", $main_url);
    @endphp
    @if(isset($url_array[2]) && $url_array[1] == "blog")
        <meta property='og:image' content="{{ url($post->image ? config('app.img_dynamic_url') . $post->image : '') }}">
        <meta property='twitter:image' content="{{ url($post->image ? config('app.img_dynamic_url') . $post->image : '') }}">
    @else
        <meta property='og:image' content="{{ url($generalSettings['fav_icon'] ?? '') }}">
        <meta name='twitter:image' content="{{ url($generalSettings['fav_icon'] ?? '') }}">
    @endif

    {{-- Additional OG --}}
    <meta property='og:locale' content="{{ $generalSettings['locale'] ?? 'en_US' }}">
    <meta property='og:type' content='website'>
    {!! getSeoMetas('extra_meta_tags') !!}

    {{-- Dynamic Keywords --}}
    @if(isset($url_array[1]))
        @if($url_array[1] == "instructors")
            <meta name="keywords" content="consult with an Astrologer, Astrology in Hindi, Horoscope, Kundli Bhagya, Kundli, Kundli match, Zodiac Signs, match-making horoscope, matchmaking marriage, Jyotish, Talk to Astrologer, Consultation vedic astrologer, Prediction for your future, Online Astrology Predictions by Best Astrologer">
        @elseif($url_array[1] == "classes" && request()->get('sort') == "newest")
            <meta name="keywords" content="Learn Astrology, learn astrology online, Learn Basic to Advance Vedic Astrology, learn vedic astrology in hindi, learn plamistry, learn vastu, Learn Palmistry Course Online, School of astrology, Free Astrology Course, online astrology course, live astrology course, learn astrology with certification, astrology learning course, Vedic Astrology with a modern touch, online platform for Vedic astrology, learn astrology online free, free astrology tutorials online, consultation with astrologer, Vedic Science Courses">
        @elseif($url_array[1] == "contact")
            <meta name="keywords" content="Contact Asttrolok, Reach Asttrolok, Contact Information Asttrolok, Get in Touch with Asttrolok, Asttrolok Contact Details, Contact Us at Asttrolok, Asttrolok Contact Page, Asttrolok Customer Support, Contact Asttrolok Online, Asttrolok Phone Number, Asttrolok Email Address">
        @endif
    @elseif($main_url == "/")
        <meta name="keywords" content="learn astrology online, institute of vedic astrology, Certified astrology course, learn plamistry, Learn vastu, Learn numrology, best astrologer, online vedic astrology, vedic astrology course, vedic indian astrology course, best book to learn vedic astrology, best vedic astrology books for beginners, distance learning course on vedic astrology, learn vedic astrology, learn vedic astrology in hindi, learn vedic astrology online, learning vedic astrology step by step, online vedic astrology certification, online vedic astrology course in english, vedic astrology books, vedic astrology courses in india, vedic astrology for beginners, vedic astrology online course, how to learn astrology, hindu astrology, online astrology courses, is astrology a science, aastrology classes online, astrology certificate courses">
    @endif

    {{-- Canonical URL --}}
    @php $canonical = url()->current(); @endphp
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Performance Optimized Fonts (30+ duplicates → 1 link) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link rel="stylesheet" href="https://unpkg.com/animate.css@4.1.1/animate.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300..900;1,300..900&family=STIX+Two+Text:ital,wght@0,400..700;1,400..700&family=Noto+Sans:ital,wght@0,300..900;1,300..900&family=Roboto:ital,wght@0,300..900;1,300..900&family=Poppins:ital,wght@0,300..900;1,300..900&display=swap" data-tag="font">
    
    <link rel="stylesheet" href="https://unpkg.com/@teleporthq/teleport-custom-scripts/dist/style.css">

    {{-- JivoSite (unchanged - delayed load) --}}
    <script>
        setTimeout(function() {
            var headID = document.getElementsByTagName("head")[0];
            var newScript = document.createElement('script');
            newScript.type = 'text/javascript';
            newScript.src = '//code.jivosite.com/widget/0vDO9nN5Jy';
            headID.appendChild(newScript);
        }, 20000);
    </script>