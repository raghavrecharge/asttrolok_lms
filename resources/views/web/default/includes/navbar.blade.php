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

<!-- NEW CLEAN NAVBAR -->
<style>

    /* MAKE MOBILE HEADER FIXED */
.astro-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 99999;
}

/* FIX CONTENT SHIFT (IMPORTANT) */
#navbarVacuum, body {
    padding-top: 65px !important; /* navbar height */
}

/* RESET */
.astro-navbar *, .nav-menu * { box-sizing: border-box; }

/* MAIN NAVBAR */
.astro-navbar {
    width: 100%;
    padding: 10px 18px;
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.astro-left img {
    height: 28px;
}

/* RIGHT ICONS */
.astro-right {
    display: flex;
    align-items: center;
    gap: 18px;
}

.astro-right svg {
    width: 23px;
    height: 23px;
    stroke: #333;
    cursor: pointer;
}

/* NOTIFICATION DOT */
.notif-dot {
    width: 8px;
    height: 8px;
    background: #ff3b30;
    border-radius: 50%;
    position: absolute;
    top: -2px;
    right: -2px;
}

/* HAMBURGER */
.hamburger {
    display: flex;
    flex-direction: column;
    gap: 4px;
    cursor: pointer;
}

.hamburger span {
    width: 22px;
    height: 2px;
    background: #333;
    border-radius: 2px;
}

/* ---------------- MENU DRAWER ---------------- */
.nav-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 85%;
    height: 100vh;
    background: #fff;
    z-index: 9999;
    padding: 20px;
    transition: 0.3s ease;
    overflow-y: auto;
}

.nav-menu.active {
    right: 0;
}

.menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.menu-header svg {
    width: 30px;
    height: 30px;
    cursor: pointer;
    stroke: #333;
}

/* MENU LINKS */
.nav-menu ul {
    list-style: none;
    padding: 0;
}

.nav-menu ul li {
    padding: 12px 0;
    border-bottom: 1px solid #f1f1f1;
}

.nav-menu ul li a {
    font-size: 15px;
    font-weight: 600;
    color: #1e1e1e;
}

/* LOGIN / REGISTER BUTTONS */
.menu-buttons {
    margin-top: 25px;
}
.astro-center {
    display: flex;
    align-items: center;
    margin-left: auto;   /* 👈 logo ke baad shift */
    margin-right: 16px; 
    margin-bottom: 10px; /* 👈 icons se gap */
}


.menu-buttons a {
    display: block;
    text-align: center;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-weight: 700;
    background: #007bff;
    color: #fff !important;
    font-size: 14px;
}

/* RESPONSIVE */
@media(min-width: 992px) {
    .nav-menu {
        width: 350px;
    }
}
</style>

<!-- ---------------- NAVBAR HTML ---------------- -->
<nav class="astro-navbar">

    <!-- LEFT: LOGO -->
    <div class="astro-left">
        <a href="/">
            <img src="/assets/design_1/img/home_mobile_image/public/asttroloklogo-min_converted.png" alt="logo">
        </a>
    </div>
               <div class="astro-center">
        @include('web.default.includes.top_nav.currency')
    </div>
 @include(getTemplate().'.includes.shopping-cart-dropdwon1')
    <!-- RIGHT: Icons -->
    <div class="astro-right">

        <!-- SEARCH -->
        {{--<a href="{{ url('/search?search=') }}">
            <img src="/assets/design_1/img/home_mobile_image/public/vector1171-cfjp.svg">

        </a>--}}

        <!-- NOTIFICATIONS -->
       {{-- <div style="position: relative;">
            <a href="{{ url('/notifications') }}">
               
                <img src="/assets/design_1/img/home_mobile_image/public/vector1171-3ntp.svg">

            </a>

            @if(!empty($unreadNotifications))
                <span class="notif-dot"></span>
            @endif
        </div>--}}

        <!-- HAMBURGER MENU -->
        <div class="hamburger" id="menuOpen">
            <img src="/assets/design_1/img/home_mobile_image/public/vector1171-lxk7.svg" alt="Vector1171" class="">
        </div>

    </div>
</nav>

<!-- ---------------- DRAWER MENU ---------------- -->
<div class="nav-menu" id="mobileMenu">

    <div class="menu-header">
        <strong style="font-size:18px;">Menu</strong>

        <svg id="menuClose" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>

    </div>
    
<button type="button" class="close" data-dismiss="modal" aria-label="Close" id="menuCloseButton">
    <span aria-hidden="true">&times;</span>
</button>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/consult-with-astrologers">Consult with Astrologer's</a></li>

        <li><a href="/classes">Courses</a></li>
        <li><a href="/remedies">Remedies</a></li>
        <li><a href="/contact">Contact Us</a></li>

        <li><a href="/tutorial-guide">Tutorial Guide</a></li>
    </ul>

    <div class="menu-buttons" >
        <a href="/login" style=" background-color: rgb(50, 160, 40) !important;">Login</a>
        <a href="/register" style=" background-color: rgb(50, 160, 40) !important;">Register</a>
    </div>

</div>

<script>
document.getElementById('menuOpen').onclick = function () {
    document.getElementById('mobileMenu').classList.add('active');
}

document.getElementById('menuClose').onclick = function () {
    document.getElementById('mobileMenu').classList.remove('active');
}
</script>
<script>
document.getElementById('menuOpen').onclick = function () {
    document.getElementById('mobileMenu').classList.add('active');
}

document.getElementById('menuClose').onclick = function () {
    document.getElementById('mobileMenu').classList.remove('active');
}

// Yahan new code add karein
document.getElementById('menuCloseButton').onclick = function () {
    document.getElementById('mobileMenu').classList.remove('active');
}
</script>

@endif