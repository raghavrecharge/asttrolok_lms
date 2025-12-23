@php
    $userLanguages = !empty($generalSettings['site_language']) ? [$generalSettings['site_language'] => getLanguages($generalSettings['site_language'])] : [];

    if (!empty($generalSettings['user_languages']) and is_array($generalSettings['user_languages'])) {
        $userLanguages = getLanguages($generalSettings['user_languages']);
    }

    $localLanguage = [];

    foreach($userLanguages as $key => $userLanguage) {
        $localLanguage[localeToCountryCode($key)] = $userLanguage;
    }

@endphp
<style>
.mail-icon, .mail-text {
    display: flex;
    align-items: center;
    color: #fff;
}
</style>
<div class="theme-header-1__top-navbar d-flex pb-54 pt-12"style="background-color:#32A128">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-12 col-lg-4">
                <div class="d-flex align-items-center gap-24">

                                            <div class="d-flex align-items-center gap-6 opacity-75">
                            <a href="tel:09174822333"class="mail-icon" style="color:#fff; margin-right:10px;" target="_blank"><svg width="16px" height="16x" class="icons text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
  <path stroke-miterlimit="10" stroke-width="1.5" d="M21.97 18.33c0 .36-.08.73-.25 1.09-.17.36-.39.7-.68 1.02-.49.54-1.03.93-1.64 1.18-.6.25-1.25.38-1.95.38-1.02 0-2.11-.24-3.26-.73s-2.3-1.15-3.44-1.98a28.75 28.75 0 01-3.28-2.8 28.414 28.414 0 01-2.79-3.27c-.82-1.14-1.48-2.28-1.96-3.41C2.24 8.67 2 7.58 2 6.54c0-.68.12-1.33.36-1.93.24-.61.62-1.17 1.15-1.67C4.15 2.31 4.85 2 5.59 2c.28 0 .56.06.81.18.26.12.49.3.67.56l2.32 3.27c.18.25.31.48.4.7.09.21.14.42.14.61 0 .24-.07.48-.21.71-.13.23-.32.47-.56.71l-.76.79c-.11.11-.16.24-.16.4 0 .08.01.15.03.23.03.08.06.14.08.2.18.33.49.76.93 1.28.45.52.93 1.05 1.45 1.58.54.53 1.06 1.02 1.59 1.47.52.44.95.74 1.29.92.05.02.11.05.18.08.08.03.16.04.25.04.17 0 .3-.06.41-.17l.76-.75c.25-.25.49-.44.72-.56.23-.14.46-.21.71-.21.19 0 .39.04.61.13.22.09.45.22.7.39l3.31 2.35c.26.18.44.39.55.64.1.25.16.5.16.78z"></path>
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.5 9c0-.6-.47-1.52-1.17-2.27-.64-.69-1.49-1.23-2.33-1.23M22 9c0-3.87-3.13-7-7-7"></path>
</svg>     </a><a href="tel:09174822333"class="mail-text" style="color:#fff; margin-right:10px;" target="_blank">
                                        <span class="text-white">09174822333</span></a>
                        </div>

                                  <div class="d-flex align-items-center gap-8 opacity-75 ml-40">
    <a href="mailto:astrolok.vedic@gmail.com" class="mail-icon" target="_blank">
        <svg width="16px" height="16px" class="icons text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5" d="M17 20.5H7c-3 0-5-1.5-5-5v-7c0-3.5 2-5 5-5h10c3 0 5 1.5 5 5v7c0 3.5-2 5-5 5z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5" d="M17 9l-3.13 2.5c-1.03.82-2.72.82-3.75 0L7 9"></path>
        </svg>
    </a>

    <a href="mailto:astrolok.vedic@gmail.com" class="mail-text" target="_blank">
        <span class="text-white">astrolok.vedic@gmail.com</span>
    </a>
</div>

                </div>
            </div>

            <div class="col-12 col-lg-8 mt-12 mt-lg-0">
                <div class="row">

                    <div class="col-12 col-lg-4">

                        <form action="/search" method="get" class="theme-header-1__top-navbar-search position-relative">
<input
  class="form-control bg-transparent opacity-75"
  type="text"
  name="search"
  placeholder="{{ trans('navbar.search_anything') }}"
  aria-label="Search"
  style="color:white; ::placeholder { color:white; }"
>

                            <button type="submit" class="btn-transparent d-flex-center search-icon">
                                <svg width="16px" height="16px" class="icons text-white opacity-75" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 20a9 9 0 100-18 9 9 0 000 18zM18.93 20.69c.53 1.6 1.74 1.76 2.67.36.85-1.28.29-2.33-1.25-2.33-1.14-.01-1.78.88-1.42 1.97z"></path>
                                                        </svg>                            
                                                    </button>
                        </form>

                    </div>

                                         <div class="col-12 col-lg-8 mt-12 mt-lg-8">

                        <div class="d-flex align-items-center justify-content-between gap-12 gap-lg-24">
                            <div class="d-flex align-items-center gap-12 gap-lg-24">

                                <div class="">
                          <div class="d-flex align-items-center gap-8">
                        {{--<div class="size-32 d-flex-center bg-white-10 rounded-8">
                            <span class="font-12 text-white opacity-75">$</span>
                        </div>--}}
               @include('web.default2.includes.top_nav.currency')

               @if(!empty($localLanguage) and count($localLanguage) > 1)
                   <form action="/locale" method="post" class="mr-15 mx-md-20">
                       {{ csrf_field() }}

                       <input type="hidden" name="locale" value="{{ app()->getLocale() }}">

                       <div class="language-select">
                           <div id="localItems"
                                data-selected-country="{{ localeToCountryCode(mb_strtoupper(app()->getLocale())) }}"
                                data-countries='{{ json_encode($localLanguage) }}'
                           ></div>
                       </div>
                   </form>
               @else
                   <div class="mr-15 mx-md-20"></div>
               @endif

                                      </div>
    </div>

 <div class="xs-w-100 d-flex align-items-center justify-content-between ">
            <div class="d-flex">

                @include(getTemplate().'.includes.shopping-cart-dropdwon')

        </div>
    </div>
                                </div>

                            @if(empty($authUser))
                                <div class="d-flex align-items-center">
                                    <a href="{{ config('app.manual_base_url') }}/login" class="d-flex align-items-center text-white opacity-75">
                                    <span class="">Login</span>
                                    </a>

                                    <a href="{{ config('app.manual_base_url') }}/register" class="d-flex align-items-center text-white opacity-75 ml-32">
                                        <span class="">Register</span>
                                    </a>
                                </div>
                            @else
                                <div class="d-flex align-items-center">
                                    <a href="{{ config('app.manual_base_url') }}/logout" class="d-flex align-items-center text-white opacity-75">
                                    <span class="">Logout</span>
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts_bottom')

    <link href="{{ config('app.js_css_url') }}/assets/default/vendors/flagstrap/css/flags.css" rel="stylesheet">
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/flagstrap/js/jquery.flagstrap.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/top_nav_flags.min.js"></script>
@endpush