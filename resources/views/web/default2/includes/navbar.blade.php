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
<div class="navdesk">
            <a class="navbar-brand navbar-order d-flex align-items-center justify-content-center mr-0 ml-auto" href="{{ config('app.manual_base_url') }}/">
                                    <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/asttroloklogo-min_converted.webp" class="img-cover" alt="site logo">
                            </a>

            <button class="navbar-toggler navbar-order" type="button" id="navbarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
             </div>

            <div class="mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content " id="navbarContent">
                <div class="navbar-toggle-header text-right d-lg-none">
                    <button class="btn-transparent" id="navbarClose">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <ul class="navbar-nav mr-auto d-flex align-items-center">
                    
                      
                                                                                                     
                                                                                            <li class="nav-item ">
                                    <a class="nav-link" href="{{ config('app.manual_base_url') }}/">Home</a>
                                </li>
                                    
                                                                                                               
                                                                                            <li class="nav-item ">
                                    <a class="nav-link" href="{{ config('app.manual_base_url') }}/consult-with-astrologers">Consult with Astrologer's</a>
                                </li>
                                    
                                                                                                               
                                                               
                                                               
                               
                                                            <li class="nav-item ">
                                <div class="menu-category ">
                                    <ul>
                                        <li class="cursor-pointer user-select-none d-flex xs-categories-toggle ">
                                            <span class="dropdown-toggle nav-item nav-link">Courses</span>
                                            
    
                                            <ul class="cat-dropdown-menu">
                                                                                                                                                                                                                                                    <li>
                                                        <a href="{{ config('app.manual_base_url') }}/categories/astrology">
                                                            <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 1.webp" class="cat-dropdown-menu-icon mr-10" alt="Astrology icon">
                                                                Astrology
                                                            </div>
    
                                                                                                                            
                                                                                                                    </a>
    
                                                                                                                                                                                          
                                                                                                                                                                            </li>
                                                                                                                                                                                                        <li>
                                                        <a href="{{ config('app.manual_base_url') }}/categories/ayurveda">
                                                            <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse%204.webp" class="cat-dropdown-menu-icon mr-10" alt="Ayurveda icon">
                                                                Ayurveda
                                                            </div>
    
                                                                                                                    </a>
    
                                                                                                            </li>
                                                                                                                                                                                                        <li>
                                                        <a href="{{ config('app.manual_base_url') }}/categories/numerology">
                                                            <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 3.webp" class="cat-dropdown-menu-icon mr-10" alt="Numerology icon">
                                                                Numerology
                                                            </div>
    
                                                                                                                    </a>
    
                                                                                                            </li>
                                                                                                                                                                                                        <li>
                                                        <a href="{{ config('app.manual_base_url') }}/categories/palmistry">
                                                            <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 2.webp" class="cat-dropdown-menu-icon mr-10" alt="Palmistry icon">
                                                                Palmistry
                                                            </div>
    
                                                                                                                    </a>
    
                                                                                                            </li>
                                                                                                                                                                                                        <li>
                                                        <a href="{{ config('app.manual_base_url') }}/categories/vastu">
                                                            <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/Home/ICONS/Ellipse 5.webp" class="cat-dropdown-menu-icon mr-10" alt="Vastu icon">
                                                                Vastu
                                                            </div>
    
                                                                                                                    </a>
    
                                                                                                            </li>
                                                                                                                                                    <li style="float:right;">
                                                    <a href="{{ config('app.manual_base_url') }}/classes">
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
                                    <a class="nav-link" href="{{ config('app.manual_base_url') }}/blog">Blog</a>
                                </li>
                                    
                                                                                                               
                                                                                            <li class="nav-item ">
                                    <a class="nav-link" href="{{ config('app.manual_base_url') }}/contact">Contact Us</a>
                                </li>
                                    
                                                                                                                    
                         
                                
                        
                                    </ul>
            </div>
            
            <div class="d-flex px-10 homehide" style="float:right !important;"><a href="tel:09174822333" class="btn btn-primary" style="    padding-right: 14px;padding-left: 14px;border-radius: 30px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-phone text-white"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></a></div>
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
{{--
<div id="navbarVacuum"></div>
<nav id="navbar" class="navbar navbar-expand-lg navbar-light">
    @if (empty($authUser)) 
      <div class="{{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container'}}">
    
     @else
      @if($authUser->isUser())
  <div class="{{ (!empty($isPanel) and $isPanel) ? 'container' : 'container'}}">
        @else
         <div class="{{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container'}}">
        
            @endif
    @endif
        <div class="d-flex align-items-center justify-content-between w-100">
<div class="navdesk">
            <a class="navbar-brand navbar-order d-flex align-items-center justify-content-center mr-0 {{ (empty($navBtnUrl) and empty($navBtnText)) ? 'ml-auto' : '' }}" href="{{isset($_GET['ad'])?'#':'/'}}">
                @if(!empty($generalSettings['logo']))
                    <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $generalSettings['logo'] }}" class="img-cover" loading="lazy" alt="site logo">
                @endif
            </a>

            <button class="navbar-toggler navbar-order" type="button" id="navbarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
             </div>

            <div class="mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content " id="navbarContent">
                <div class="navbar-toggle-header text-right d-lg-none">
                    <button class="btn-transparent" id="navbarClose">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <ul class="navbar-nav mr-auto d-flex align-items-center">
                    
                      @if(empty($authUser))

                              @endif
                    @if(!empty($navbarPages) and count($navbarPages))
                        @foreach($navbarPages as $navbarPage)
                           
                            @if(!isset($_GET['ad']))
                                @if($navbarPage['title'] !="Courses")
                                <li class="nav-item ">
                                    <a class="nav-link" href="{{ $navbarPage['link'] }}">{{ $navbarPage['title'] }}</a>
                                </li>
                                 @endif   
                                @if($navbarPage['title'] =="Courses")
                               
                               
                                @if(!empty($categories) and count($categories))
                            <li class="nav-item ">
                                <div class="menu-category ">
                                    <ul>
                                        <li class="cursor-pointer user-select-none d-flex xs-categories-toggle ">
                                            <span class="dropdown-toggle nav-item nav-link">{{ $navbarPage['title'] }}</span>
                                            
    
                                            <ul class="cat-dropdown-menu">
                                                @foreach($categories as $category)
                                                @if($category->title != "Uncategories")
                                                    <li>
                                                        <a href="{{ $category->getUrl() }}" >
                                                            <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                                <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $category->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $category->title }} icon" loading="lazy" >
                                                                {{ $category->title }}
                                                            </div>
    
                                                            @if(!empty($category->subCategories) and count($category->subCategories))
                                                                
                                                            @endif
                                                        </a>
    
                                                        @if(!empty($category->subCategories) and count($category->subCategories))
                                                                @foreach($category->subCategories as $subCategory)
                                                                  
                                                                @endforeach
                                                        @endif
                                                    </li>
                                                    @endif
                                                @endforeach
                                                <li style="float:right;">
                                                    <a href="/classes" >
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
                           
                        @endif      
                             
                               
                               @endif
                            @endif
                        @endforeach
                                
                         
                                
                        
                    @endif
                </ul>
            </div>
            
            <div class="d-flex px-10 homehide" style="float:right !important;"><a href="tel:09174822333" class="btn btn-primary" style="    padding-right: 14px;padding-left: 14px;border-radius: 30px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-phone text-white"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></a></div>
            @if(!isset($_GET['ad']))
            <div class="nav-icons-or-start-live navbar-order navbar-order1">

                @if(!empty($navBtnUrl))
                    <a href="{{ $navBtnUrl }}" class="d-none d-lg-flex btn btn-sm btn-primary nav-start-a-live-btn">
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
            @endif
        </div>
    </div>
</nav>
--}}
@push('scripts_bottom')
    <script defer src="{{ config('app.js_css_url') }}/assets/default/js/parts/navbar.min.js"></script>
@endpush
