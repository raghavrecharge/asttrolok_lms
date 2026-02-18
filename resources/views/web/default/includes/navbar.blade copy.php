@php
    $hideNavbar = request()->is('login') || request()->is('register');
@endphp

@if(!$hideNavbar)

@php
    if (empty($authUser) and auth()->check()) {
        $authUser = auth()->user();
    }

    $navBtnUrl = null;
    $navBtnText = null;

    if(request()->is('forums*')) {
        $navBtnUrl = '/forums/create-topic';
        $navBtnText = trans('update.create_new_topic');
    } else {
        $navbarButton = getNavbarButton(!empty($authUser) ? $authUser->role_id : null, empty($authUser));

        if (!empty($navbarButton)) {
            $navBtnUrl = $navbarButton->url;
            $navBtnText = $navbarButton->title;
        }
    }
@endphp
<style>
#navbarShopingCart svg {
    stroke: #040404ff !important;
}

</style>
<div id="navbarVacuum"></div>
<nav id="navbar" class="navbar navbar-expand-lg navbar-light">

      <div class="container">

             <div class="d-flex align-items-center justify-content-between w-100">

<div class="navmobile navmobiledisplay">
    <div>
        <button class="navbar-toggler navbar-order" type="button" id="navbarToggle">
        <span class="navbar-toggler-icon"></span>
    </button>
        </div>
     <a class="navbar-brand navbar-order d-flex align-items-center justify-content-center mr-0 " href="/">
                                    <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/asttroloklogo-min_converted.webp" class="img-cover" alt="site logo">
                            </a>
<div class="top-navbar">

    <div class="xs-w-100 d-flex align-items-center justify-content-between ">
            <div class="d-flex">
                  <div class="js-currency-select custom-dropdown position-relative" style="margin-top:7px;">
        <form action="/set-currency" method="post">
            <input type="hidden" name="_token" value="VN3yV218tFXUdCDz1q7sUmHkNKU0HYi3DvojFDnn" autocomplete="off">
            <input type="hidden" name="currency" value="INR">

                                                <div class="custom-dropdown-toggle d-flex align-items-center cursor-pointer">
                        <div class="mr-5 text-secondary">
                            <span class="js-lang-title font-14">INR (₹)</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14px" height="14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down icons"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                                                                </form>

        <div class="custom-dropdown-body py-10">

                            <div class="js-currency-dropdown-item custom-dropdown-body__item cursor-pointer active" data-value="INR" data-title="INR (₹)">
                    <div class=" d-flex align-items-center w-100 px-15 py-5 text-gray bg-transparent">
                        <div class="size-32 position-relative d-flex-center bg-gray100 rounded-sm">
                            ₹
                        </div>

                        <span class="ml-5 font-14">India Rupee</span>
                    </div>
                </div>
                            <div class="js-currency-dropdown-item custom-dropdown-body__item cursor-pointer " data-value="USD" data-title="USD ($)">
                    <div class=" d-flex align-items-center w-100 px-15 py-5 text-gray bg-transparent">
                        <div class="size-32 position-relative d-flex-center bg-gray100 rounded-sm">
                            $
                        </div>

                        <span class="ml-5 font-14">United States Dollar</span>
                    </div>
                </div>

        </div>
    </div>

                  <div class="border-left mx-5 mx-lg-15"></div>
                <div class="dropdown">
   <button type="button" {{ (empty($userCarts) or count($userCarts) < 1) ? 'disabled' : '' }} class="btn btn-transparent dropdown-toggle" id="navbarShopingCart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>

             @if(!empty($userCarts) and count($userCarts))
             <span class="badge badge-circle-primary d-flex align-items-center justify-content-center">{{ count($userCarts) }}</span>
             @endif
    </button>

    <div class="dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class="d-md-none border-bottom mb-20 pb-10 text-right">
            <i class="close-dropdown" data-feather="x" width="32" height="32" class="mr-10"></i>
        </div>
        <div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar>
                @if(!empty($userCarts) and count($userCarts) > 0)
                    <div class="mb-auto">
                        @foreach($userCarts as $cart)
                            @php
                                $cartItemInfo = $cart->getItemInfo();
                            @endphp

                            @if(!empty($cartItemInfo))
                                <div class="navbar-cart-box d-flex align-items-center">

                                    <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank" class="navbar-cart-img">
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" alt="product title" class="img-cover"/>
                                    </a>

                                    <div class="navbar-cart-info">
                                        <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank">
                                            <h4>{{ $cartItemInfo['title'] }}</h4>
                                        </a>
                                        <div class="price mt-10">
                                            @if(!empty($cartItemInfo['discountPrice']))
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                                                <span class="off ml-15">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @else
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @endif

                                            @if(!empty($cartItemInfo['quantity']))
                                                <span class="font-12 text-warning font-weight-500 ml-10">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="navbar-cart-actions">
                        <div class="navbar-cart-total mt-15 border-top d-flex align-items-center justify-content-between">
                            <strong class="total-text">{{ trans('cart.total') }}</strong>
                            <strong class="text-primary font-weight-bold">{{ !empty($totalCartsPrice) ? handlePrice($totalCartsPrice, true, true, false, null, true) : 0 }}</strong>
                        </div>

                        <a href="/cart" class="btn btn-sm btn-primary btn-block mt-50 mt-md-15" style="font-family: 'Inter', sans-serif !important;">{{ trans('cart.go_to_cart') }}</a>
                    </div>
                @else
                    <div class="d-flex align-items-center text-center py-50">
                        <i data-feather="shopping-cart" width="20" height="20" class="mr-10"></i>
                        <span class="">{{ trans('cart.your_cart_empty') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

                <div class="border-left mx-5 mx-lg-15"></div>

                <div class="dropdown">
    <button type="button" class="btn btn-transparent dropdown-toggle" disabled="" id="navbarNotification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell mr-10"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>

            </button>

    <div class="dropdown-menu pt-20" aria-labelledby="navbarNotification">
        <div class="d-flex flex-column h-100">
            <div class="mb-auto navbar-notification-card" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" style="height: 100%; overflow: hidden;"><div class="simplebar-content" style="padding: 0px;">
                <div class="d-md-none border-bottom mb-20 pb-10 text-right">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close-dropdown"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </div>

                                    <div class="d-flex align-items-center text-center py-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell mr-10"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="">Empty notifications</span>
                    </div>

            </div></div></div></div><div class="simplebar-placeholder" style="width: auto; height: 189px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: hidden;"><div class="simplebar-scrollbar" style="height: 0px; display: none;"></div></div></div>

                    </div>
    </div>
</div>
            </div>

        </div>

        </div>

    </div>
            <div class="mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content  " id="navbarContent">
                <div class="navbar-toggle-header text-right d-lg-none">
                    <button class="btn-transparent" id="navbarClose">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <ul class="navbar-nav mr-auto d-flex align-items-center">

<div class="my-20" style="width: 100%;">
                                  <li class="nav-item">

 <a href="{{ config('app.manual_base_url') }}/login" style="font-weight: 800;width:48%; font-size:14px !important;" class="btn btn-primary">Login</a>

        <a href="{{ config('app.manual_base_url') }}/register" style="font-weight: 800;width:48%; float:right; font-size:14px !important;" class="btn btn-primary">Register</a>

                            </li>
                            </div>
                            <div class="navmobile">
                             <li class="nav-item">

                            </li>

                            </div>

                                                        <li class="nav-item ">
                                <a class="nav-link" href="/">Home</a>
                            </li>

                                                        <li class="nav-item ">
                                <a class="nav-link" href="/consult-with-astrologers">Consult with Astrologer's</a>
                            </li>

                                                    <li class="nav-item ">
                            <div class="menu-category ">
                                <ul>
                                    <li class="cursor-pointer user-select-none d-flex xs-categories-toggle">

                                        <span class="dropdown-toggle nav-item nav-link">Courses</span>

                                        <ul class="cat-dropdown-menu">
                                                                                                                                                                                                                                <li>

                                                    <a href="/categories/astrology">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 1.webp" class="cat-dropdown-menu-icon mr-10" alt="Astrology icon">
                                                            Astrology
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>

                                                    <a href="/categories/ayurveda">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon mr-10" alt="Ayurveda icon">
                                                            Ayurveda
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>

                                                    <a href="/categories/numerology">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 3.webp" class="cat-dropdown-menu-icon mr-10" alt="Numerology icon">
                                                            Numerology
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>

                                                    <a href="/categories/palmistry">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 2.webp" class="cat-dropdown-menu-icon mr-10" alt="Palmistry icon">
                                                            Palmistry
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>

                                                    <a href="/categories/vastu">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 5.webp" class="cat-dropdown-menu-icon mr-10" alt="Vastu icon">
                                                            Vastu
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                        <li style="float:right;">
                                                <a href="/classes">
                                                        <div class="d-flex align-items-center" style="    font-size: 12px;font-weight: 600;text-align:center;color: #171347;">

                                                            View All
                                                        </div>

                                                    </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </li>

                                                        <li class="nav-item ">
                                <a class="nav-link" href="/blog">Blog</a>
                            </li>

                                                        <li class="nav-item ">
                                <a class="nav-link" href="/contact">Contact Us</a>
                            </li>

                        <div class="navmobile">

                            <li class="nav-item">
                                <a href="/tutorial-guide" style="font-size: 14px;font-weight: 600;">Tutorial Guide</a>
                            </li>
                                                        </div>
                                    </ul>
            </div>

            <div class="nav-icons-or-start-live navbar-order navbar-order1">

                <div class="d-none nav-notify-cart-dropdown top-navbar ">
                    <div class="dropdown">
    <button type="button" disabled="" class="btn btn-transparent dropdown-toggle" id="navbarShopingCart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"  style="color:black"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>

            </button>

    <div class="dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class="d-md-none border-bottom mb-20 pb-10 text-right">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close-dropdown"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
        <div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" style="height: auto; overflow: hidden;"><div class="simplebar-content" style="padding: 0px;">
                                    <div class="d-flex align-items-center text-center py-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span class="">Your cart is empty</span>
                    </div>
                            </div></div></div></div><div class="simplebar-placeholder" style="width: 0px; height: 0px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: hidden;"><div class="simplebar-scrollbar" style="height: 0px; display: none;"></div></div></div>
        </div>
    </div>
</div>

                    <div class="border-left mx-15"></div>

                    <div class="dropdown">
    <button type="button" class="btn btn-transparent dropdown-toggle" disabled="" id="navbarNotification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell mr-10"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>

            </button>

    <div class="dropdown-menu pt-20" aria-labelledby="navbarNotification">
        <div class="d-flex flex-column h-100">
            <div class="mb-auto navbar-notification-card" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" style="height: auto; overflow: hidden;"><div class="simplebar-content" style="padding: 0px;">
                <div class="d-md-none border-bottom mb-20 pb-10 text-right">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close-dropdown"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </div>

                                    <div class="d-flex align-items-center text-center py-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell mr-10"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="">Empty notifications</span>
                    </div>

            </div></div></div></div><div class="simplebar-placeholder" style="width: 0px; height: 0px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: hidden;"><div class="simplebar-scrollbar" style="height: 0px; display: none;"></div></div></div>

                    </div>
    </div>
</div>
                </div>

            </div>
        </div>
    </div>
</nav>
@endif
@push('scripts_bottom')
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/navbar.min.js"></script>
@endpush
