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

<div class="navmobile navmobiledisplay">
    <div>
    @if(!isset($_GET['ad']))
    <button class="navbar-toggler navbar-order" type="button" id="navbarToggle">
        <span class="navbar-toggler-icon"></span>
    </button>
    @endif
    </div>
     <a class="navbar-brand navbar-order d-flex align-items-center justify-content-center mr-0 " href="{{isset($_GET['ad'])?'#':'/'}}">
                @if(!empty($generalSettings['logo']))
                    <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $generalSettings['logo'] }}" class="img-cover" alt="site logo">
                @endif
            </a>
<div class="top-navbar">
            
    
    <div class="xs-w-100 d-flex align-items-center justify-content-between ">
            <div class="d-flex">
                  @include('web.default.includes.top_nav.currency')
                  <div class="border-left mx-5 mx-lg-15"></div>
                @include(getTemplate().'.includes.shopping-cart-dropdwon')

                <div class="border-left mx-5 mx-lg-15"></div>

                @include(getTemplate().'.includes.notification-dropdown')
            </div>
 
        </div>
            <!--{{-- User Menu --}}-->
            
        </div>
       
        
        
    </div>
            <div class="mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content " id="navbarContent">
                <div class="navbar-toggle-header text-right d-lg-none">
                    <button class="btn-transparent" id="navbarClose">
                        <i data-feather="x" width="32" height="32"></i>
                    </button>
                </div>

                <ul class="navbar-nav mr-auto d-flex align-items-center">
                    <!--@if(!empty($categories) and count($categories))-->
                    <!--    <li class="mr-lg-25">-->
                    <!--        <div class="menu-category">-->
                    <!--            <ul>-->
                    <!--                <li class="cursor-pointer user-select-none d-flex xs-categories-toggle">-->
                    <!--                    <i data-feather="grid" width="20" height="20" class="mr-10 d-none d-lg-block"></i>-->
                    <!--                    {{ trans('categories.categories') }}-->

                    <!--                    <ul class="cat-dropdown-menu">-->
                    <!--                        @foreach($categories as $category)-->
                    <!--                        @if($category->title != "Uncategories")-->
                    <!--                            <li>-->
                    <!--                                <a href="{{ $category->getUrl() }}">-->
                    <!--                                    <div class="d-flex align-items-center">-->
                    <!--                                        <img loading="lazy" decoding="async" src="{{ $category->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $category->title }} icon">-->
                    <!--                                        {{ $category->title }}-->
                    <!--                                    </div>-->

                    <!--                                    @if(!empty($category->subCategories) and count($category->subCategories))-->
                    <!--                                        <i data-feather="chevron-right" width="20" height="20" class="d-none d-lg-inline-block ml-10"></i>-->
                    <!--                                        <i data-feather="chevron-down" width="20" height="20" class="d-inline-block d-lg-none"></i>-->
                    <!--                                    @endif-->
                    <!--                                </a>-->

                    <!--                                @if(!empty($category->subCategories) and count($category->subCategories))-->
                    <!--                                    <ul class="sub-menu" data-simplebar @if((!empty($isRtl) and $isRtl)) data-simplebar-direction="rtl" @endif>-->
                    <!--                                        @foreach($category->subCategories as $subCategory)-->
                    <!--                                            <li>-->
                    <!--                                                <a href="{{ $subCategory->getUrl() }}">-->
                    <!--                                                    @if(!empty($subCategory->icon))-->
                    <!--                                                        <img loading="lazy" decoding="async" src="{{ $subCategory->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $subCategory->title }} icon">-->
                    <!--                                                    @endif-->

                    <!--                                                    {{ $subCategory->title }}-->
                    <!--                                                </a>-->
                    <!--                                            </li>-->
                    <!--                                        @endforeach-->
                    <!--                                    </ul>-->
                    <!--                                @endif-->
                    <!--                            </li>-->
                    <!--                            @endif-->
                    <!--                        @endforeach-->
                    <!--                    </ul>-->
                    <!--                </li>-->
                    <!--            </ul>-->
                    <!--        </div>-->
                    <!--    </li>-->
                    <!--@endif-->
<div class="my-20" style="width: 100%;">
     @if(empty($authUser))
                             <li class="nav-item">
                   
 <a href="/login" style="font-weight: 800;width:48%; font-size:14px !important;" class="btn btn-primary">{{ trans('auth.login') }}</a>
                  
                            <!--</li>-->
                            <!-- <li class="nav-item">-->
        <a href="/register" style="font-weight: 800;width:48%; float:right; font-size:14px !important;" class="btn btn-primary">{{ trans('auth.register') }}</a>
                   
                            </li>
                             @endif
                             
                             
                             
                            </div>
                            <div class="navmobile">
                             <li class="nav-item">


                @if(!empty($navBtnUrl))
                   

                    <a href="{{ $navBtnUrl }}" style="font-size: 16px;font-weight: 600;color:#343434;" class="">
                        {{ $navBtnText }}
                    </a>
                @endif




                            </li>
                            
                            </div>
                    @if(!empty($navbarPages) and count($navbarPages))
                        @foreach($navbarPages as $navbarPage)
                           
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
                                        <!--<i data-feather="grid" width="20" height="20" class="mr-10 d-none d-lg-block"></i>-->
                                        <span class="dropdown-toggle nav-item nav-link">{{ $navbarPage['title'] }}</span>
                                        

                                        <ul class="cat-dropdown-menu">
                                            @foreach($categories as $category)
                                            @if($category->title != "Uncategories")
                                                <li>
                                                    
                                                    <a href="{{ $category->getUrl() }}" >
                                                        <div class="d-flex align-items-center" style="    font-size: 14px;font-weight: 600;text-align:center;color: #171347;">
                                                            <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $category->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $category->title }} icon">
                                                            {{ $category->title }}
                                                        </div>

                                                        @if(!empty($category->subCategories) and count($category->subCategories))
                                                            <!--<i data-feather="chevron-right" width="20" height="20" class="d-none d-lg-inline-block ml-10"></i>-->
                                                            <!--<i data-feather="chevron-down" width="20" height="20" class="d-inline-block d-lg-none"></i>-->
                                                        @endif
                                                    </a>

                                                    @if(!empty($category->subCategories) and count($category->subCategories))
                                                        <!--<ul class="sub-menu" data-simplebar @if((!empty($isRtl) and $isRtl)) data-simplebar-direction="rtl" @endif>-->
                                                            @foreach($category->subCategories as $subCategory)
                                                                <!--<li>-->
                                                                <!--    <a href="{{ $subCategory->getUrl() }}">-->
                                                                <!--        @if(!empty($subCategory->icon))-->
                                                                <!--            <img loading="lazy" decoding="async" src="{{ $subCategory->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $subCategory->title }} icon">-->
                                                                <!--        @endif-->

                                                                <!--        {{ $subCategory->title }}-->
                                                                <!--    </a>-->
                                                                <!--</li>-->
                                                            @endforeach
                                                        <!--</ul>-->
                                                    @endif
                                                </li>
                                                @endif
                                            @endforeach
                                            <li style="float:right;">
                                                <a href="/classes" >
                                                        <div class="d-flex align-items-center" style="    font-size: 12px;font-weight: 600;text-align:center;color: #171347;">
                                                            <!--<img loading="lazy" decoding="async" src="https://www.asttrolok.in/store/1/Home/icon/Astrology.png" class="cat-dropdown-menu-icon mr-10" alt="all icon">-->
                                                            View All
                                                        </div>

                                                    </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!--<li class="nav-item homeshow">-->
                        <!--        <a class="nav-link" href="{{ $navbarPage['link'] }}">{{ $navbarPage['title'] }}</a>-->
                        <!--</li>-->
                        
                        
                    @endif      
                         
                           
                           @endif
                        @endforeach
                                
                         
                                
                        <div class="navmobile">
                             
                            <li class="nav-item">
                                <a href="/tutorial-guide" style="font-size: 14px;font-weight: 600;" >Tutorial Guide</a> 
                            </li>
                            @if(!empty($authUser))
                            <!--<li class="nav-item">-->
                            <!--    <a href="/panel" style="font-size: 14px;font-weight: 600;" >Dashboard</a> -->
                            <!--</li>-->
                            <li class="navbar-auth-user-dropdown-item">
                    <a href="/logout" class="d-flex align-items-center w-500 py-10 text-danger font-14 bg-transparent">
                        <img loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/icons/user_menu/logout.svg" class="icons">
                        <span class="ml-5">{{ trans('auth.logout') }}</span>
                    </a>
                </li>
                 @endif
                            </div>
                    @endif
                </ul>
            </div>
            

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
        </div>
    </div>
</nav>

@push('scripts_bottom')
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/navbar.min.js"></script>
@endpush
