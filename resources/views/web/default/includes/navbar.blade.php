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
     <a class="navbar-brand navbar-order d-flex align-items-center justify-content-center mr-0 " href="https://marketing.asttrolok.com">
                                    <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/Asttrolok-Logo.png" class="img-cover" alt="site logo">
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
    <button type="button" disabled="" class="btn btn-transparent dropdown-toggle" id="navbarShopingCart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>

            </button>

    <div class="dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class="d-md-none border-bottom mb-20 pb-10 text-right">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close-dropdown"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
        <div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" style="height: 100%; overflow: hidden;"><div class="simplebar-content" style="padding: 0px;">
                                    <div class="d-flex align-items-center text-center py-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span class="">Your cart is empty</span>
                    </div>
                            </div></div></div></div><div class="simplebar-placeholder" style="width: auto; height: 121px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: hidden;"><div class="simplebar-scrollbar" style="height: 0px; display: none;"></div></div></div>
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
            <!---->
            
        </div>
       
        
        
    </div>
            <div class="mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content  show" id="navbarContent">
                <div class="navbar-toggle-header text-right d-lg-none">
                    <button class="btn-transparent" id="navbarClose">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <ul class="navbar-nav mr-auto d-flex align-items-center">
                    
<div class="my-20" style="width: 100%;">
                                  <li class="nav-item">
                   
 <a href="/login" style="font-weight: 800;width:48%; font-size:14px !important;" class="btn btn-primary">Login</a>
                  
                            <!--</li>-->
                            <!-- <li class="nav-item">-->
        <a href="/register" style="font-weight: 800;width:48%; float:right; font-size:14px !important;" class="btn btn-primary">Register</a>
                   
                            </li>
                                                          
                             
                             
                            </div>
                            <div class="navmobile">
                             <li class="nav-item">


                



                            </li>
                            
                            </div>
                                                                       
                                                        <li class="nav-item ">
                                <a class="nav-link" href="https://marketing.asttrolok.com/">Home</a>
                            </li>
                                
                                                                               
                                                        <li class="nav-item ">
                                <a class="nav-link" href="https://marketing.asttrolok.com/consult-with-astrologers">Consult with Astrologer's</a>
                            </li>
                                
                                                                               
                               
                                                       
                           
                                                    <li class="nav-item ">
                            <div class="menu-category ">
                                <ul>
                                    <li class="cursor-pointer user-select-none d-flex xs-categories-toggle">
                                        <!--<i data-feather="grid" width="20" height="20" class="mr-10 d-none d-lg-block"></i>-->
                                        <span class="dropdown-toggle nav-item nav-link">Courses</span>
                                        

                                        <ul class="cat-dropdown-menu">
                                                                                                                                                                                                                                <li>
                                                    
                                                    <a href="https://marketing.asttrolok.com/categories/astrology">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 1.webp" class="cat-dropdown-menu-icon mr-10" alt="Astrology icon">
                                                            Astrology
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>
                                                    
                                                    <a href="https://marketing.asttrolok.com/categories/ayurveda">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon mr-10" alt="Ayurveda icon">
                                                            Ayurveda
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>
                                                    
                                                    <a href="https://marketing.asttrolok.com/categories/numerology">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 3.webp" class="cat-dropdown-menu-icon mr-10" alt="Numerology icon">
                                                            Numerology
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>
                                                    
                                                    <a href="https://marketing.asttrolok.com/categories/palmistry">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 2.webp" class="cat-dropdown-menu-icon mr-10" alt="Palmistry icon">
                                                            Palmistry
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                                                                        <li>
                                                    
                                                    <a href="https://marketing.asttrolok.com/categories/vastu">
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 5.webp" class="cat-dropdown-menu-icon mr-10" alt="Vastu icon">
                                                            Vastu
                                                        </div>

                                                                                                            </a>

                                                                                                    </li>
                                                                                                                                        <li style="float:right;">
                                                <a href="https://marketing.asttrolok.com/classes">
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
                                <a class="nav-link" href="https://marketing.asttrolok.com/blog">Blog</a>
                            </li>
                                
                                                                               
                                                        <li class="nav-item ">
                                <a class="nav-link" href="https://marketing.asttrolok.com/contact">Contact Us</a>
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
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>

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

@push('scripts_bottom')
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/navbar.min.js"></script>
@endpush
