@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<style>
    .bg-secondary1 {
    background-color: #f6f5f5 !important;
}
.nav-tabs {
    border-bottom: 1px solid #ececec !important;
}
.rounded-sm1 {
    border-radius: 0.625rem !important;
}
.nav-tabs .nav-item a.active,.nav-tabs .nav-item a:hover {
    background-color: white !important;
    border: none !important;
    padding: 7px;
    border-radius: 6px;
    font-weight: 600;
    color: #32ba7c;
    padding-left:50px;
    padding-right:50px;
}
</style>
@endpush

@section('content')
    <section class="cart-banner mobile-home-slider search-top-banner opacity-04 position-relative">

        <div class="container">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 mb-15">Courses</h1>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-20 ">
        <form id="filtersForm" class="consult-filter" style="display:block;" action="/{{ $page }}" method="get">

        @include('web.default.pages.includes.top_filters')
         </form>

         <div class="mt-15">
            <div>

                        <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'active' : '' }}" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false">Hindi</a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a  class="position-relative font-14   {{ (request()->get('tab','') == 'content') ? 'active' : '' }}" id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false">English</a>
                            </li>

                        </ul>
            </div>
        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40 mt-20">

                <div class="row mt-10">
                    <div class="col-12 col-lg-12">

                                  <div class="tab-content " id="nav-tabContent">
                            <div class="tab-pane fade  {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'show active' : '' }}" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                @if(empty(request()->get('card')) or request()->get('card') == 'grid' )
                                <div class="row">
                                    <div class="col-md-6 col-lg-4 mt-20  mobilegrid">
                                    <div class="webinar-card grid-card">
    <figure>

        <div class="image-box str">
            <div class="star-rating">

               <div class="radius-20  stars-card d-flex align-items-center shadow-sm " style="padding-left: 1px; padding-right:1px; padding-top:1px; ">

                    <span class="radius-20 badge badge-primary1 rate1">

                <svg width="14" height="14" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_263_410)">
                    <path d="M14.6461 5.51542L9.78752 5.06481L7.85756 0.583302C7.72244 0.269504 7.27751 0.269504 7.1424 0.583302L5.21247 5.06484L0.353914 5.51542C0.0137192 5.54697 -0.123771 5.97011 0.132899 6.19558L3.79869 9.41594L2.7259 14.176C2.65078 14.5093 3.01073 14.7708 3.30448 14.5963L7.49999 12.1051L11.6955 14.5963C11.9893 14.7708 12.3492 14.5093 12.2741 14.176L11.2013 9.41594L14.8671 6.19558C15.1238 5.97011 14.9863 5.54697 14.6461 5.51542Z" fill="#FFDC64"></path>
                    <path d="M7.85756 0.583302C7.72244 0.269504 7.27751 0.269504 7.1424 0.583302L5.21247 5.06484L0.353914 5.51542C0.0137192 5.54697 -0.123771 5.97011 0.132899 6.19558L3.79869 9.41594L2.7259 14.176C2.65078 14.5093 3.01073 14.7708 3.30448 14.5963L4.2409 14.0403C4.37051 8.70532 6.84931 4.94838 8.81185 2.7992L7.85756 0.583302Z" fill="#FFC850"></path>
                    </g>
                    <defs>
                    <clipPath id="clip0_263_410">
                    <rect width="14" height="14" fill="white"></rect>
                    </clipPath>
                    </defs>
                    </svg>
                    4.5</span>
            </div>
                </div>
                            <span class="badge badge-primary hide">Course</span>

            <a href="https://www.asttrolok.com/subscriptions/asttrolok-pathshala">

                <img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/subscription/Astrology Learning Program.jpg" class="img-cover" alt="Astrology Learning Program">
            </a>
            <div class="d-flex justify-content-between mt-auto">
                <div class=" h-25 mx-15"></div>
                 <form action="/cart/store" method="post">
                            <input type="hidden" name="_token" value="RBfstrPjoYpQJ0PsfNQuqOur0ozwfVJdSpKADJjz">
                            <input type="hidden" name="item_id" value="2074">
                            <input type="hidden" name="item_name" value="webinar_id">

            <div class="dropdown dropdown-card" style="
    position: absolute;
    right: 0px;
    bottom: -12px;

    box-shadow: 0 5px 12px 0 rgba(0, 0, 0, 0.1);
">
    <button type="button" disabled="" class="btn btn-transparent dropdown-toggle1 js-course-add-to-cart-btn" id="" data-toggle="dropdown1" aria-haspopup="true" aria-expanded="false" style="height: 30px ;">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart ml-5 mr-10 shoping-cart"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>

            </button>

    <div class="dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class="d-md-none border-bottom mb-20 pb-10 text-right">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close-dropdown"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
        <div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" style="height: auto; overflow: hidden;"><div class="simplebar-content" style="padding: 0px;">
                                    <div class="d-flex align-items-center text-center py-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10 "><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span class="">Your cart is empty</span>
                    </div>
                            </div></div></div></div><div class="simplebar-placeholder" style="width: 0px; height: 0px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: hidden;"><div class="simplebar-scrollbar" style="height: 0px; display: none;"></div></div></div>
        </div>
    </div>
</div>
            </form>
               </div>

                            <div class="progress">
                    <span class="progress-bar" style="width: 6.9%"></span>
                </div>

                    </div>

        <figcaption class="webinar-card-body">

            <a href="https://www.asttrolok.com/subscriptions/asttrolok-pathshala">

                <h3 class="mt-5 webinar-title webinartitle font-weight-bold font-16 text-dark-blue">Asttrolok Pathshala</h3>
            </a>
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/astrologer_mobile/Alok Sir.jpg" class="img-cover" alt="Mr.Alok Khandelwal">
                </div>
                <a href="/users/1015/astrologer-mr.alok-khandelwal" target="_blank" class="user-name ml-5 font-14">Mr.Alok Khandelwal</a>
            </div>
            <hr>

            <div class="webinar-price-box mt-5">
                                                        <span class="real">₹2100 /-</span>

            <a href="https://www.asttrolok.com/subscriptions/asttrolok-pathshala">

                    <button type="submit" class="btn btn-primary rounded-pill buynow homehide1">BUY NOW</button>
                    </a>

            </div>
        </figcaption>
    </figure>
</div>
                                </div>
                                    @foreach($hindi_classes as $hindi_class)

                                <div class="col-md-6 col-lg-4 mt-20  mobilegrid">
                                    @include('web.default.includes.webinar.grid-card',['webinar' => $hindi_class])
                                </div>
                            @endforeach

                            </div>
                            @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')
                            <div class="row">
                                 @foreach($hindi_classes as $webinar)

                                <div class="col-12 col-lg-4 mt-20  ">
                                    @include('web.default.includes.webinar.list-card',['webinar' => $webinar])
                                </div>
                                @endforeach
                                 </div>
                            @endif
                            </div>
                            <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                @if(empty(request()->get('card')) or request()->get('card') == 'grid' )
                                <div class="row">
                                    @foreach($englishclasses as $englishclass)

                                    <div class="col-md-6 col-lg-4 mt-20  mobilegrid">
                                        @include('web.default.includes.webinar.grid-card',['webinar' => $englishclass])
                                    </div>
                                 @endforeach

                               </div>
                                @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')
                                <div class="row">
                                    @foreach($englishclasses as $english)

                                    <div class="col-12 col-lg-4 mt-20 ">
                                         @include('web.default.includes.webinar.list-card',['webinar' => $english])
                                    </div>
                                 @endforeach

                               </div>
                                @endif
                            </div>

                            </div>
                     </div>

                    </div>

                </div>

        </section>
    </div>

    <style>
    .dropdown-menu {
    position: absolute;
    right: 0 !important;
    z-index: 1000;
    left: unset !important;
    min-width: 10rem !important;
    top: 35% !important;
}
        .course-teacher-card.instructors-list .off-label1 {
        position: absolute;
        top: 7px;
        right: 7px;
        border-radius: 15px 15px 15px 15px !important;
        z-index: 10;
    }
    .course-teacher-card.instructors-list .off-label {
        position: absolute;
        top: 7px;
        left: 7px;
        border-radius: 15px 15px 15px 15px !important;
        z-index: 10;
    }
    .loadid {
        display:none;

    }
    .loadid.display {
        display: inline-block;
    }
    .load-card-list {
        display:none;
    }
    .load-card-list.display {
        display: inline-block;
    }
    </style>

    <script   src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script   >
    //   $(document).ready(function(){
    //     $(".load-card-list").slice(0,10).show();
    //   $("#listloadMore").click(function(e){
    //     e.preventDefault();
    //     $(".load-card-list:hidden").slice(0,10).fadeIn("slow");
    //     console.log($(".load-card-list:hidden").length);
    //     if($(".load-card-list:hidden").length == 0){
    //       $("#listloadMore").fadeOut("slow");
    //       }
    //   });
    //   if($(".load-card-list").length < 9){
    //   $("#listloadMore").hide();

    //   }
    //   $(".load-card-list1").slice(0,10).show();
    //   $("#listloadMore1").click(function(e){
    //     e.preventDefault();
    //     $(".load-card-list1:hidden").slice(0,10).fadeIn("slow");
    //     console.log($(".load-card-list1:hidden").length);
    //     if($(".load-card-list1:hidden").length == 0){
    //       $("#listloadMore1").fadeOut("slow");
    //       }
    //   });
    //   if($(".load-card-list1").length < 9){
    //   $("#listloadMore1").hide();

    //   }

    //   $(".loadid").slice(0,10).show();
    //   $("#loadMore").click(function(e){
    //     e.preventDefault();
    //     $(".loadid:hidden").slice(0,10).fadeIn("slow");
    //     console.log($(".loadid:hidden").length);
    //     if($(".loadid:hidden").length == 0){
    //       $("#loadMore").fadeOut("slow");
    //       }
    //   });
    //   if($(".loadid").length < 10){
    //   $("#loadMore").hide();

    //   }

    //   $(".loadid1").slice(0,10).show();
    //   $("#loadMore1").click(function(e){
    //     e.preventDefault();
    //     $(".loadid1:hidden").slice(0,10).fadeIn("slow");
    //     console.log($(".loadid1:hidden").length);
    //     if($(".loadid1:hidden").length == 0){
    //       $("#loadMore1").fadeOut("slow");
    //       }
    //   });
    //   if($(".loadid1").length < 10){
    //   $("#loadMore1").hide();

    //   }
    // })
    </script>

@endsection

@push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/categories.min.js"></script>
       <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/instructors.min.js"></script>
         <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/home.min.js"></script>
          <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>
@endpush
