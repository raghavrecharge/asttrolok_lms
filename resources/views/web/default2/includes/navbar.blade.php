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

    // Fetch dynamic navbar links
    $dynamicNavLinks =  fetchNavbarLinks();

    // Fetch dynamic categories for dropdown
    $dynamicCategories = $categories ?? fetchNavbarCategories();

    // Items to exclude from navbar (case-insensitive)
    $excludeItems = ['astrology', 'ayurveda', 'numerology'];
@endphp
<style>
    .xs-categories-toggle:hover > .cat-dropdown-menu {
    opacity: 1;
    visibility: visible;
    top: 15px;
    transform: translateY(15px);
}
</style>

<style>
    body {
      background-color: #f8f9fa;
    }

    .navbar {
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .nav-link, .dropdown-toggle {
      color: #171347 !important;
      font-weight: 400;
      cursor: pointer;
    }

    .cat-dropdown-menu {
      list-style: none;
      margin: 0;
      padding: 8px 0;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      min-width: 220px;
      z-index: 999;
    }

    .cat-dropdown-menu li {
      padding: 5px 15px;
    }

    .cat-dropdown-menu li:hover {
      background-color: #f0f0f0;
    }

    .cat-dropdown-menu a {
      text-decoration: none;
      color: #171347;
      font-weight: 500;
    }

    .cat-dropdown-menu-icon {
      width: 24px;
      height: 24px;
      margin-right: 10px;
    }

    .xs-categories-toggle:hover .cat-dropdown-menu,
    .cat-dropdown-menu:hover {
      display: block;
    }

    .xs-categories-toggle {
      position: relative;
    }
   .col-6.col-lg-5.mt-12.mt-lg-0 {
    margin-left: 50px !important;
}
.nav-item .nav-link,
.xs-categories-toggle > .nav-link,
.cat-dropdown-menu li a div {
    white-space: nowrap !important;
}

    @media (max-width: 768px) {
      .cat-dropdown-menu {
        position: static;
        display: none;
        box-shadow: none;
      }
      .cat-dropdown-menu.show {
        display: block;
      }
    }

.theme-header-1__main {
    position: relative;
    z-index: 1000;
    transition: all 0.3s ease;
    background-color: transparent !important;
    overflow: visible;

}

.item-sticky.sticky {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background-color: #fff;
    z-index: 9999;
}

</style>

<div  class="theme-header-1__main ">
    <div class="container h-100 position-relative">

        <div class="position-relative z-index-2 bg-white rounded-24 w-100 h-100 p-16 item-sticky">
            <div class="row align-items-center h-100">

                <div class="col-6 col-lg-2">
                    <a href="{{ config('app.manual_base_url') }}" class="theme-header-1__logo text-left d-block">
                        <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/asttroloklogo-min_converted.webp" class="img-fluid light-only" alt="logo">
                        <!-- <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/asttroloklogo-min_converted.webp" class="img-fluid dark-only" alt="logo"> -->
                    </a>
                </div>

                <div class="col-6 col-lg-5 mt-12 mt-lg-0">
                    <div class="d-flex justify-content-center align-items-center gap-16 gap-lg-32">

                        @if(!empty($dynamicNavLinks) && count($dynamicNavLinks) > 0)

                            @foreach($dynamicNavLinks as $navLink)
                                @php
                                    // Handle both object and array format
                                    $linkTitle = is_object($navLink) ? $navLink->title : ($navLink['title'] ?? '');
                                    $linkUrl = is_object($navLink) ? $navLink->link : ($navLink['link'] ?? '#');

                                    // Skip excluded items
                                    if (in_array(strtolower($linkTitle), $excludeItems)) {
                                        continue;
                                    }
                                @endphp

                                @if(strtolower($linkTitle) == 'courses')

                                    <li class="nav-item xs-categories-toggle position-relative">
                                        <span class="dropdown-toggle nav-link">{{ $linkTitle }}</span>

                                        <ul class="cat-dropdown-menu">
                                            @if(!empty($dynamicCategories) && count($dynamicCategories) > 0)
                                                @foreach($dynamicCategories as $category)
                                                    @php
                                                        $catTitle = is_object($category) ? $category->title : ($category['title'] ?? '');
                                                        $catSlug = is_object($category) ? $category->slug : ($category['slug'] ?? '');
                                                        $catIcon = is_object($category) ? $category->icon : ($category['icon'] ?? '');
                                                    @endphp

                                                    @if($catTitle != "Uncategories")
                                                        <li>
                                                            <a href="{{ config('app.manual_base_url') }}/categories/{{ $catSlug }}">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ !empty($catIcon) ? config('app.img_dynamic_url') . $catIcon : 'https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%201.webp' }}"
                                                                         class="cat-dropdown-menu-icon"
                                                                         alt="{{ $catTitle }}">
                                                                    {{ $catTitle }}
                                                                </div>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            @else

                                                <li>
                                                    <a href="{{ config('app.manual_base_url') }}/categories/astrology">
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%201.webp" class="cat-dropdown-menu-icon" alt="">
                                                            Astrology
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ config('app.manual_base_url') }}/categories/ayurveda">
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon" alt="">
                                                            Ayurveda
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ config('app.manual_base_url') }}/categories/numerology">
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon" alt="">
                                                            Numerology
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ config('app.manual_base_url') }}/categories/palmistry">
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%202.webp" class="cat-dropdown-menu-icon" alt="">
                                                            Palmistry
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ config('app.manual_base_url') }}/categories/veda">
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%205.webp" class="cat-dropdown-menu-icon" alt="">
                                                            Veda
                                                        </div>
                                                    </a>
                                                </li>
                                            @endif

                                            <li>
                                                <a href="{{ config('app.manual_base_url') }}/classes">
                                                    <div class="d-flex align-items-center" style="font-size:13px;font-weight:600;">
                                                        View All
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                @else

                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ config('app.manual_base_url') }}/{{ $linkUrl }}">{{ $linkTitle }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @else

                            <li class="nav-item">
                                <a class="nav-link" href="{{ config('app.manual_base_url') }}">Home</a>
                            </li>
                             <li class="nav-item xs-categories-toggle position-relative">
                                <span class="dropdown-toggle nav-link">Courses</span>

                                <ul class="cat-dropdown-menu">
                                    <li>
                                        <a href="{{ config('app.manual_base_url') }}/categories/astrology">
                                            <div class="d-flex align-items-center">
                                                <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%201.webp" class="cat-dropdown-menu-icon" alt="">
                                                Astrology
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ config('app.manual_base_url') }}/categories/ayurveda">
                                            <div class="d-flex align-items-center">
                                                <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon" alt="">
                                                Ayurveda
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ config('app.manual_base_url') }}/categories/numerology">
                                            <div class="d-flex align-items-center">
                                                <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon" alt="">
                                                Numerology
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ config('app.manual_base_url') }}/categories/palmistry">
                                            <div class="d-flex align-items-center">
                                                <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%202.webp" class="cat-dropdown-menu-icon" alt="">
                                                Palmistry
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ config('app.manual_base_url') }}/categories/vastu">
                                            <div class="d-flex align-items-center">
                                                <img src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%205.webp" class="cat-dropdown-menu-icon" alt="">
                                                Veda
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ config('app.manual_base_url') }}/categories">
                                            <div class="d-flex align-items-center" style="font-size:13px;font-weight:600;">
                                                View All
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ config('app.manual_base_url') }}/consult-with-astrologers">Consultation</a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link" href="{{ config('app.manual_base_url') }}/remedies">Remedies</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ config('app.manual_base_url') }}/blog">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ config('app.manual_base_url') }}/contact">Contact</a>
                            </li>
                        @endif

                        @if(!empty($navbarPages) and count($navbarPages))
                            <div class="navmobile">
                                <li class="nav-item">
                                    @if(!empty($navBtnUrl))
                                        <a href="{{ $navBtnUrl }}" class="">
                                            {{ $navBtnText }}
                                        </a>
                                    @endif
                                </li>

                                @if(!empty($authUser))
                                    <li class="navbar-auth-user-dropdown-item">
                                        <a href="/logout" class="d-flex align-items-center w-500 py-10 text-danger font-14 bg-transparent">
                                            <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/user_menu/logout.svg" class="icons">
                                            <span class="ml-5">{{ trans('auth.logout') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-6 col-lg-3 mt-12 mt-lg-0 d-flex align-items-center justify-content-end mr-0">

                    <div class="nav-icons-or-start-live navbar-order navbar-order1">
                        @if(!empty($navBtnUrl))
                            <a href="{{ $navBtnUrl }}" class="d-none d-lg-flex btn btn-sm btn-success nav-start-a-live-btn" style="background-color: #32A128;">
                                {{ $navBtnText }}
                            </a>

                            <a href="{{ $navBtnUrl }}" class="d-flex d-lg-none text-primary nav-start-a-live-btn font-14">
                                {{ $navBtnText }}
                            </a>
                        @endif

                        <div class="d-none nav-notify-cart-dropdown top-navbar ">
                            @include(getTemplate().'.includes.shopping-cart-dropdwon')

                            <div class="border-left mx-15"></div>

                            @include(getTemplate().'.includes.notification-dropdown')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainNavbar = document.querySelector('.theme-header-1__main');
    const topNavbar = document.querySelector('.theme-header-1__top-navbar'); // Top navbar

    let topNavHeight = topNavbar ? topNavbar.offsetHeight : 0;

    window.addEventListener('scroll', function() {
        if (window.scrollY > topNavHeight) {
            mainNavbar.classList.add('sticky');
        } else {
            mainNavbar.classList.remove('sticky');
        }
    });
});
</script>
