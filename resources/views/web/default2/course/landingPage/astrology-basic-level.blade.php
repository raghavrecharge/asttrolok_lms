<?php 
 header("Access-Control-Allow-Origin: *");
define('RAZOR_KEY_ID', 'rzp_live_80LvVdqLPUaiKR');
define('RAZOR_KEY_SECRET', 'FyiZ6gn5TDRQjzCWYAPhCbao');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Permissions-Policy" content="interest-cohort=()">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <meta name="theme-color" content="#e3a54a" />
    <title>Most Popular Astrology Course</title>
    <meta property="og:title" content="Most Popular Astrology Course">
    <meta property="og:image" content="/resources/img/Satvic_Movement.png">
    <meta property="og:image:secure_url" content="/resources/img/Satvic_Movement.png">
    <meta property="og:image:width" content="640">
    <meta property="og:image:height" content="480">
    <link href="https://www.asttrolok.com/consultation/assets/img/logos/astrolok.png" rel="icon">
    <link href="https://www.asttrolok.com/consultation/assets/img/logos/astrolok.png" rel="apple-touch-icon">
    <link  href="{{ config('app.js_css_url') }}assets/default/css/landingPage/resources/img/mobile-thumb.webp" as="image" media="(max-width: 400px)">
    <link  href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/desktop-thumb.webp" as="image" media="(min-width: 400px)">
    <link  href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/css/css1.css"  rel="stylesheet" media="all"  />
    <!--<noscript>-->
    <!--    <link rel="stylesheet" type="text/css" href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/css/css1.css" />-->
    <!--    </noscript>-->
    <link  href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/css/compressed.css_v33.css" as="style"  rel="stylesheet" media="all" />
    <!--<noscript><link href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/css/compressed.css_v33.css" rel="stylesheet" /></noscript>-->
    <link  href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/css/style_home.css_v33.css" as="style"  rel="stylesheet" media="all" />
    <!--<noscript><link href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/css/style_home.css_v33.css" rel="stylesheet" /></noscript>-->

    <link  href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/library/country-picker-flags/build/css/countrySelect.css_v33.css"  rel="stylesheet" media="all" />
    <!--<noscript><link href="{{ config('app.js_css_url') }}/assets/default/css/landingPage/library/country-picker-flags/build/css/countrySelect.css_v33.css"  rel="stylesheet" media="all"  /></noscript>-->
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app.css">
    <!-- <meta name="facebook-domain-verification" content="udpg8vc28n88hyldu649snqe56b3qr" /> -->

    @stack('styles_top')
    @stack('scripts_top')
<style>

#header .logo img {
    max-height: none !important;
}
.vboutEmbedFormResponse-97949{
    color:#fff;
}
.btn_arrow {
    margin-left: 15px;
    margin-top: 10px !important;
}
.w-100 {
    width: 85% !important;
}
#register_form .btn-get-started {
    padding: 0px 0px;
}
.px-md-32 img{
    padding-right: 1rem!important;
    padding-left: 1rem!important;
}

@media (max-width: 700px)

{
    .register_mobile{
    display:block;
}
.register_desktop{
    display:block;
}
    .counts{
    display: flex;
    flex-direction: column;
    align-items: center;
}
    .mobile-head{
    font-size:12px !important;
}
.w-100 {
    width: 100% !important;
}
    .row>* {
    width: 100% !important;

}
.curr_left {
    height: 140px !important;
}
.curr_right {
    height: 140px !important;
}
div#thumb_0 {
   
    margin-left: 15px !important;
    margin-right: 15px !important;
}
.grid.gtc-lg-4.gtc-md-2.gtc.gtc-sm-1.date_time_block {
    width: 93%;
    margin-left: 15px !important;
    margin-right: 15px !important;
}
.count_block_whiteblock {
    margin-top: 50px !important;
    padding-bottom: 50px;
    border-radius: 0;
}
/*.hero{*/
/*    display:none;*/
/*}*/
#register_form .btn-get-started {
    padding: 0px 0px !important;
}
#myBtn1{
        cursor: pointer;
    display: inline-flex;
    width: 100%;
    flex-direction: column;
}
}
@media (min-width: 750px)

{
    .register_mobile{
    display:none;
}
.register_desktop{
    display:block;
}
    .ytp-large-play-button{
        margin-top:10px;
    }
    #myBtn1{
        cursor: pointer;
    display: inline-flex;
}
/*   .cls {*/
/*    display: flex;*/
/*    justify-content: space-evenly;*/
/*    align-items: flex-end;*/
/*    flex-wrap: nowrap;*/
/*    flex-direction: row-reverse;*/
/*}*/
.register_mobilenew {
    

    display: none;

 }
}
#count1{
    font-weight: 900;
}
#count2{
    font-weight: 900;
}
#count3{
    font-weight: 900;
}
#count4{
    font-weight: 900;
}
.specialist-info1 h4 {
    font-size: 1.75rem;
    line-height: 2rem;
    letter-spacing: .00893em;
    font-weight: bold;
}
.text-center {
    text-align: left !important;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", () => {
 function counter(id, start, end, duration) {
  let obj = document.getElementById(id),
   current = start,
   range = end - start,
   increment = end > start ? 1 : -1,
   step = Math.abs(Math.floor(duration / range)),
   timer = setInterval(() => {
    current += increment;
    obj.textContent = current;
    if (current == end) {
     clearInterval(timer);
    }
   }, step);
 }
 counter("count1", 187000, 188000, 10);
 counter("count2", 22500, 23918, 100);
 counter("count3", 48000, 50000, 1000);
  counter("count4", 0, 52, 1000);
});

</script>
<style>

/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1060; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 50%;
}
@media screen and (max-width: 600px) {
  .modal-content {
  
  width: 95% !important;
}
.modal {
    padding-top: 30px !important ;
}
}
/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}
/*.register_form1 {*/
/*    padding-bottom: 100px;*/
/*}*/
#register_form1 *{
    color:#000 !important;
        
}

.register_form1 input[type=email], .register_form1 input[type=password], .register_form1 input[type=text], .register_form1 input[type=tel], .register_form1 input[type=date], .register_form1 input[type=time] {
    color: #000;
    text-align: left;
    width: 100%;
    font-size: 17px;
    background: 0 0;
    border: 2px solid #000;
    border-radius: 50px;
    
}
.register_form1 input{
    text-align: left;
    width: 100%;
    font-size: 17px;
    background: 0 0;
    border: 2px solid #101010;
    border-radius: 50px;
    color: #000 !important;
}
.register_form1 ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
  color: #000 !important;
  opacity: 1; /* Firefox */
}
.register_form1 input:-webkit-autofill, input:-webkit-autofill:focus, input:-webkit-autofill:hover, select:-webkit-autofill, select:-webkit-autofill:focus, select:-webkit-autofill:hover, textarea:-webkit-autofill, textarea:-webkit-autofill:focus, textarea:-webkit-autofill:hover {
    border: 1px solid #000;
    -webkit-text-fill-color: #000;
    -webkit-box-shadow: 0 0 0 1000px transparent inset;
    transition: background-color 5000s ease-in-out 0s;
}
.btn-get-started1 {
    background: #fcae36;
    background: -moz-linear-gradient(left, #fcae36 0, #ff9100 100%);
    background: -webkit-linear-gradient(left, #fcae36 0, #ff9100 100%);
    background: linear-gradient(to right, #fcae36 0, #ff9100 100%);
    font-weight: 700;
    font-size: 16px;
    line-height: 20px;
    letter-spacing: 1px;
    display: inline-block;
    padding: 0px 30px;
    border-radius: 3px;
    margin-top: 25px;
    color: #fff;
    border-radius: 50px;
    font-family: 'Poppins', Sans-serif !important;
    width: 232px;
    transition: .3s all ease-in-out;
    box-shadow: 0 10px 10px #f1e4d2;
}

</style>
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/vendors/leaflet/leaflet.css">
    
<!--<script src="//code.jivosite.com/widget/1gG0N75UaM" async></script>-->


<!-- Facebook Pixel Code -->
<script>

  setTimeout(function() {
        $("[href='https://elfsight.com/google-reviews-widget/?utm_source=websites&utm_medium=clients&utm_content=google-reviews&utm_term=www.asttrolok.com&utm_campaign=free-widget']").hide();
    //   $('.WidgetBackground__Content-sc-1ho7q3r-2 > a').find('.inline').last().attr("style", "display:none !important");
    
              
}, 2000);
//   setTimeout(function() {
//         $("[href='https://elfsight.com/google-reviews-widget/?utm_source=websites&utm_medium=clients&utm_content=google-reviews&utm_term=www.asttrolok.com&utm_campaign=free-widget']").hide();
//     //   $('.WidgetBackground__Content-sc-1ho7q3r-2 > a').find('.inline').last().attr("style", "display:none !important");   
              
// }, 4000);

  setTimeout(function() {
   

!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '447559993145549');
fbq('track', 'PageView');
}, 20000);
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=447559993145549&ev=PageView&noscript=1"
/>
</noscript>

<!-- End Facebook Pixel Code -->

<!-- Google Tag Manager  23-05-2024-->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MH675X5');</script>
<!-- End Google Tag Manager -->

</head>

<body>
    
    <!-- Google Tag Manager (noscript) 23-05-2024 -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MH675X5"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <!--<header id="header" class="fixed-top">-->
    <!--    <div class="container d-flex align-items-center justify-content-between my-1 px-4">-->
    <!--        <h1 class="logo" style="width:250px;">-->
                <!-- <a href="index.html"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/satvic-movement-logo-grey-1.webp" alt="Logo" width="250px" height="" /></a> -->
    <!--            <a href="/"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/asttrolok_1592999765_51-removebg-preview.png" alt="Logo" width="" height="" style="width:100%" /></a>-->
    <!--        </h1>-->
    <!--        <nav id="navbar" class="navbar">-->
    <!--            <ul>-->
                    
    <!--                <li><a class="nav-link scrollto" href="#benefits">Bonuses</a></li>-->
    <!--                <li><a class="nav-link scrollto" href="#Syllabus">Syllabus</a></li>-->
    <!--                <li><a class="nav-link scrollto" href="#faq1">FAQs</a></li>-->
    <!--                <li><a class="nav-link scrollto" href="#about">About</a></li>-->
                    <!--<li> <a class="nav-link scrollto register-now"  href="index.php#register_form">Submit Appplication</a>-->
    <!--                <li> <a class="nav-link scrollto register-now" id="myBtn4" style="cursor: pointer;">Submit Appplication</a>-->
    <!--                </li>-->
    <!--            </ul>-->
    <!--        </nav>-->
    <!--    </div>-->
    <!--</header>-->
    <div id="app" class="{{ (!empty($floatingBar) and $floatingBar->position == 'top' and $floatingBar->fixed) ? 'has-fixed-top-floating-bar' : '' }}">
    @if(!empty($floatingBar) and $floatingBar->position == 'top')
        @include('web.default2.includes.floating_bar')
    @endif

    @if(!isset($appHeader))
        @include('web.default2.includes.top_nav')
        @include('web.default2.includes.navbar')
    @endif

    @if(!empty($justMobileApp))
        @include('web.default2.includes.mobile_app_top_nav')
    @endif

    @yield('content')


</div>
    <section id="hero" class="d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 pt-4">
                    <h1 id='title'>Astrology Level 1</h1><h2 style="color: #43d276 !important;text-transform: uppercase;font-weight: 900;font-size: x-large;"></h2>
                    <h2>Get an insight into everything the universe holds for your future.Enroll today in the best astrology course online and kick-start learning with masterfully-crafted guidance from world-renowned astrologer, <strong>Mr. Alok Khandelwal.</strong></h2>
                    
                    <div class="cls">
                        <!--<h2 class="hero"><strong>Total Fee 7999</strong></h2>-->
                        <!--<a  id="myBtn" class="btn-get-started scrollto" style="cursor: pointer;">Submit Appplication<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" /></a>-->
                        <a  href="/course/astrology-basic-level" class="btn-get-started scrollto" style="cursor: pointer;">Enroll Now<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" /></a>
                   <!--<button id="myBtn" class="btn-get-started scrollto">Register Now<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" /></button>-->
                    
                    </div><br> <br><br>
                  <!--<button id="myBtn">Open Modal</button>-->
                   




 

 

 
                </div>
                <div class="col-lg-6 pt-4 main_video pe-0 pe-sm-5 d-flex align-content-end" id="homepageimage">
                    <div class="video_container top_video p-0 me-0 me-sm-5" id="thumb_0">
                        <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:100%;" src="https://www.youtube.com/embed/7eYA1MxNr1M" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" class="youtubeiframe" allowfullscreen></iframe>

                    </div>

                </div>
            </div>
        </div>
    </section>
<div  class="grid gtc-lg-5 gtc-md-2 gtc gtc-sm-1 date_time_block px-20" style="z-index: 20; position:relative;background: #f3f3f3;font-family: 'Poppins', Sans-serif !important;">
                            
                            <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/4.png" alt="Time">
                                <div class="stdate">1 Live Class</div>
                                <div class="fulldate">Per Month</div>
                            </div>
                             <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/video-shooting.png" alt="video-shooting">
                                <div class="stdate">120+</div>
                                <div class="fulldate">Video</div>
                            </div>
                            <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/3.png" alt="Global">
                                <div class="stdate">WhatsApp</div>
                                <div class="fulldate">Group</div>
                            </div>
                            <div class="item date_time duaration_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/2.png" alt="Duration">
                                <div class="stdate">20 </div>
                                <div class="fulldate">PDFs</div>
                            </div>
                            <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/1.png" alt="Appointment">
                                <div class="stdate">Lifetime </div>
                                <div class="fulldate" >Support</div>
                            </div>
                        </div>


    <main id="main" style="font-family: 'Poppins', Sans-serif !important;">
        <section id="included" class="Included">
            <div class="container">
                <div class="row ">
                    <div class="col-lg-9 main_video">
                        <div class=" grid gtc-lg-4 gtc-md-2 gtc gtc-sm-2 date_time_block px-20" style="flex-wrap: nowrap;">
                            <div class="item date_time counts">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/youtube-icon.png" alt="Time">
                                <div class="stdate"  id="count1"></div><b><span>Subscribers</span></b>
                            </div>
                            <div class="item date_time counts">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/facebook-icon.png" alt="Global">
                                <div class="stdate"  id="count2"></div><b><span>Likes</span></b>
                            </div>
                            <div class="item date_time duaration_time counts">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/Happy-Customer.png" alt="Duration">
                                <div class="stdate"  id="count3"></div><b><span>Happy Students</span></b>
                            </div>
                            <div class="item date_time duaration_time counts">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/global-icon.png" alt="Duration">
                                <div class="stdate"  id="count4"></div><b><span>Countries</span></b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          
           
            <div class="container Included_Block">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="white_block white_block_transparent count_block_whiteblock">
                            <center><h3 class="brown_heading whats_head py-3">What You will get?</h3></center>
                            <div class="include_details">
                                <div>

                                    <!--<img  srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/whats/tiny/21.webp 480w,resources/img/whats/tiny/21.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/whats/tiny/21.webp">-->
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/32-Live-Classes-min.png" alt="32 Live Classes - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>1 Live Class & Recorded Video</h4>
                                        <div class="include_text">Dive deep into astrology with  dynamic live sessions taught by the renowned astrologer & trainer.</div>
                                    </div>
                                </div>
                            </div>
                             
                            <div class="include_details">
                                <div>

                                    <img   src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/30-Downloadable-PDFs-min.png" alt="30 Downloadable Pdfs - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Downloadable PDFs</h4>
                                        <div class="include_text pb-4">Get comprehensive study material with 30+ downloadable PDFs for reference and offline learning.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img   src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/25-Doubt-Sessions-with-Mentors-min.png" alt="25 Doubt Sessions With Mentors - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Doubt Sessions with Mentors</h4>
                                        <div class="include_text">Interact and clarify your doubts through 25 dedicated doubt sessions with experienced mentors.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Test-Quiz-Assignments-min.png" alt="Test Quiz Assignments - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Test/Quiz/Assignments</h4>
                                        <div class="include_text">Reinforce your learning with engaging tests, quizzes, and assignments to solidify your understanding.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/WhatsApp-Group-min.png" alt="Whatsapp Group - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>WhatsApp Group</h4>
                                        <div class="include_text">Connect with fellow astrology enthusiasts in a supportive WhatsApp group for discussions and networking.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Online-Exam-Certificate-min.png" alt="Online Exam Certificate - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Online Exam & Certificate</h4>
                                        <div class="include_text">Validate your skills through an online exam and receive a prestigious certificate upon course completion.</div>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
            
            
            
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        
                        
                        
                        

                        
                        
                        <div id="Syllabus" class="white_block text-center faq">
                            <!--<div class="faq_mobile">-->

                            <!--    <img  class="img-fluid" srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp 480w,resources/img/faqs/tiny/faq-2-tiny.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp" id="faq_image">-->
                            <!--</div>-->
                            <center><h3 class="brown_heading py-3">What all you will learn?</h3></center>
                            <!--<div class="faq_desktop">-->

                            <!--    <img  class="img-fluid" srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp 480w,resources/img/faqs/tiny/faq-2-tiny.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp">-->
                            <!--</div>-->
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
										Significance of Jyotish as Vedanga
</button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <!--<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">-->
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                    Introduction to Vedic science

                                                </li>
                                                <li>
                                                   Significance of Vedic astrology
                                                </li>
                                                <li>
                                                    Purpose of Vedanga
                                                </li>
                                                
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
											Characteristics & importance of Navagrahas
</button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Introduction to Navgrahas </li>
<li>Planet gunas and tatva</li>
<li>Five elements</li>
<li>Grah devtas</li>
<li>Fundamentals of Navagrahas</li>
<!--<li>Mutual relationship with Planets</li>-->
<!--<li>Panchada maître chakra</li>-->
<!--<li>Aspects of various Planets</li>-->
<!--<li>Planets in different Houses</li>-->
<!--<li>Bhava lords in different Houses</li>-->
<!--<li>-->
<!--                                                    General rules of judgment of horoscopes-->
<!--                                                </li>-->
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
											In detail Mythological Stories of all Planets
</button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Mythological Story of Planet Sun </li>
                                                 <li>Mythological Story of Planet Moon </li>
                                                 <li>Mythological Story of Planet Venus </li>
                                                  <li>Mythological Story of Planet Mars </li>
                                                   <li>Mythological Story of Planet Mercury </li>
                                                    <li>Mythological Story of Planet Jupiter </li>
                                                     <li>Mythological Story of Planet Saturn </li>
                                                      <li>Mythological Story of Planet Rahu </li>
                                                       <li>Mythological Story of Planet Ketu </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
											Introduction to Rashi, Elements, Bhavas, and Karakas 
</button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Rashi & its Characteristics </li>
                                                <li>Rashi & its Elements </li>
                                                 <li>Introduction on 12 Bhavas </li>
                                                  <li>Karakas in Astrology</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSix">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
										How to cast a horoscope</button>
                                    </h2>
                                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                    Division of Sky
                                                </li>
                                                 <li>
                                                    What is your lagna
                                                </li>
                                                 <li>
                                                    How the horoscope is casted
                                                </li>
                                                 <li>
                                                    How to place planet in your chart
                                                </li>
                                                <!--<li>-->
                                                <!--   Use of Navamsa for Prediction-->
                                                <!--</li>-->
                                                <!--<li>-->
                                                <!--    Result of 12 houses of Horoscope and Analysis of Yogas-->
                                                <!--</li>-->
                                                <!--<li>-->
                                                <!--    Analysis of Health, Finance, Education, Profession, Children, Marriage-->
                                                <!--</li>-->
                                                <!--<li>-->
                                                <!--    Prediction through Parashari Methods, Lagna Kundli, Chandra kundli,-->
                                                <!--</li>-->
                                                <!--<li>-->
                                                <!--    Dashmansh and their effect on Predictions-->
                                                <!--</li>-->
                                                <!--<li>Gun Milan in Marriage Astrology</li>-->
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingEight">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
											Solar Cabinet & its Importance in Vedic Astrology
</button>
                                    </h2>
                                    <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                   A detailed description of all 9 Planets: Properties, Role in Different Houses, Prediction & Professions
                                                </li>
                                            
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                

                                <!--<a class="load_more" id="load" href="index.php#">Load More</a>-->
                            </div>
                        </div>
                        
                        
                        <div id="curriculum" class="white_block text-center curriculum">
                            <center><h3 class="brown_heading py-3">How this Astrology Course can benefit you?</h3></center>
                            <div class="content">
                                <div class="content-item active">
                                    <div class="grid gtc curr-sm-2 curr-md-2 curr-lg-2 curr_grid">
                                         <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Career-Advancement-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Career Options
                                            </div>
                                            <div class="heading_cur3">Explore new opportunities as a professional astrologer, consultant, or teacher.
                                            </div>
                                        </div>
                                        
                                         <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Personal-Fulfilment-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Income Potential
                                            </div>
                                            <div class="heading_cur3"> Use your specialized knowledge to offer astrology services and increase your earning potential.
                                            </div>
                                        </div>
                                        
                                        <div class="curr_left">
                                            <div class="heading_cur1 habit_icon"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Credibility-and-Trust-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Professional Credibility
                                            </div>
                                            <div class="heading_cur3">Get certification from a reputable Vedic institute of the country getting trained by a renowned astrologer, building trust and credibility.
                                            </div>
                                        </div>
                                       
                                        <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Enhanced-Skill-Set-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Skill Advancement
                                            </div>
                                            <div class="heading_cur3">Learn chart interpretation, prediction techniques, and analysis, becoming a sought-after astrologer.
                                            </div>
                                        </div>
                                        <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Professional-Networking-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Professional Networking
                                            </div>
                                            <div class="heading_cur3">Connect with industry professionals, fellow astrologers, and mentors, expanding your professional network.
                                            </div>
                                        </div>
                                       
                                    </div>
                                     <div class="cls" style="text-align:center;">
                                        <a   id="myBtn1" href="/course/astrology-basic-level"  style="cursor: pointer;" class="btn-get-started scrollto right_register">Buy Now<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;"></a></div>
                                       
                                </div>
                            </div>
                        </div>
                        
                        
                                      <div id="about" class="white_block_no_padding about">
<div id="about-1" class="white_block about-1 text-center">
<div class="specialist-info1 queries  queries-info1" style="text-align:center;">
                                <h4 class="queries-head">For more queries or assistance <br></h4>
                               <h4 class="queries-head1">Call Now- <a href="tel:09174822333"> 09174822333</a></h4> 
                               <!--<h4 class="queries-head1" >Call Now- <a href="#" type="call">09174822333</a></h4>-->
                        </div>

</div>
</div>
                        <div id="about" class="white_block_no_padding about">

                            <div id="about-1" class="white_block about-1 text-center">
                                <center>
                                    <h4 class="brown_heading py-3">Meet your Mentor</h4>
                                </center>
                                <div class="row">
                                    <div class="col-lg-6"><img  class="about_img w-100" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/Alok-sir1.png" alt="Alok Sir - Asttrolok">

                                    </div>
                                    <div class="col-lg-6">
                                        <!-- <img  class="about_img w-100" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/Subah.webp" alt=""> -->  

                                        <h3 class="brown_heading mt-4 mb-3" id="hostname" style="font-size: 30px;">Mr. Alok Khandelwal</h3>
                                        <p class="aboput_text px-0 px-ml-5 mt-10">
                                            <!-- <span class="d-block">Mr. Alok Khandelwal</span> -->
                                            <span class=" mt-md-3 mt-10">Mr. Alok Khandelwal is not only recognized for his exceptional astrological expertise but also for his global impact. He extends his teachings beyond borders, making Vedic Knowledge reach students in countries like Russia with the help of professional translators. </span>
                                            </p><p class="aboput_text px-0 px-ml-5 mt-10"><span class=" mt-md-3 mt-5">Moreover, Mr. Khandelwal's passion for sharing knowledge takes him to various countries where he engages in public speaking, teaching, and consultation, and delivers introductory talks on Vedic science with a project called Unwinding.</span>
 </p><p class="aboput_text px-0 px-ml-5 mt-10"><span class=" mt-md-3 mt-10">With over 35,000 students already taught and hundreds more added each year, Mr. Khandelwal's impact continues to grow. He is highly regarded for his expertise in Ancient Vedic Astrology and interpersonal skills, offering practical solutions to professional, personal, emotional, and mental challenges. Additionally, he holds membership in the esteemed 'Art of Living' foundation and is sought-after as a guest speaker in prestigious institutions nationwide.</span>

                                        </p>
                                       
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                        
                        <div id="people" class="white_block people">
                            <center><h3 class="brown_heading text-center mb-2">People are saying</h3>
                            <h5 class="text-center review_location  mb-4 ln-23">35,000+ people have already completed the Most Popular Astrology Course</h5></center>
                            <div class="row">
                                
                                <div class="col-lg-12 pt-4 main_video pe-0 pe-sm-5 d-flex align-content-end" style="margin-bottom: 5%;">
                    <div class="video_container top_video p-0 me-0 me-sm-5" id="thumb_0">
                       <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:100%;" src="https://www.youtube.com/embed/ZvyItRmmR70?si=pT57-wyssPd70tXX" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                   <br/> </div>

                </div>
                                <div class="col-sn-12 col-nd-5 col-lg-4" style="margin-top:5%;">
                                    <center>
                                    <div class="highlight_review_mobile">
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review">Highlighted review</div>
                                            <img  class="review_img" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/05-min.jpg" alt="People">
                                            <div class="review_name">Kartik Pathak</div>
                                            <div class="review_location">Nagpur </div>
                                            <div>
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                            </div>
                                            <div class="review_detail show-read-more">
                                               I am a 25-year-old guy who has learned Jyotish from Alok Khandelwal Sir to break the stereotype thinking that it is only for old aged people. 
                                           Astrology is the first step of enlightenment. I think Alok Sir has made a group of people who are devoted to god and it's called Asttrolok.
                                                 </div>
                                        </div>
                                    </div>
                                    <div class="highlight_review_desktop">
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review">Highlighted review</div>
                                            <img  class="review_img" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/05-min.jpg" alt="People">
                                            <div class="review_name">Kartik Pathak</div>
                                            <div class="review_location">Nagpur </div>
                                            <div>
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                            </div>
                                            <div class="review_detail show-read-more">
                                               I am a 25-year-old guy who has learned Jyotish from Alok Khandelwal Sir to break the stereotype thinking that it is only for old aged people. 
                                           Astrology is the first step of enlightenment. I think Alok Sir has made a group of people who are devoted to god and it's called Asttrolok.
                                                 </div>
                                        </div>
                                    </div></center>
                                </div>
                                
                               
                                <div class="col-sn-12 col-nd-7 col-lg-8">
                                    <div id="one" class="testimonial active">
                                        <div class="people_block">
                                            <img  class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/02-min.jpg" alt="People" width="100" height="100" />
                                            <div class="name_star">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                            </div>
                                            <div class="name">Vikas Gupta</div>
                                            <div class="place"> Indore</div>
                                        </div>
                                        <div class="detail show-read-more">
                                            I know Asttrolok since the day it was formed. For me Asttrolok is not an institute or a medium of astrology, <a class="show_hide" data-content="toggle-text">Read More</a>
                                            <div class="testinomial-content-more" id="more-data">
                                                its a medium to live a life for me. My Asttrolok’s journey is very delightful and memorable.</div>
                                        </div>
                                        <div class="people_block">
                                            <img  class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/03-min.jpg" alt="People" width="100" height="100" />
                                            <div class="name_star">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                            </div>
                                            <div class="name">Ritu Dixit</div>
                                            <div class="place"> Delhi </div>
                                        </div>
                                        <div class="detail show-read-more">
                                            I joined Asttrolok last year. After coming to Asttrolok, I realized how important the guidance of a guru is in life. Ever since I joined this community, <a class="show_hide" data-content="toggle-text">Read More</a>
                                            <div class="testinomial-content-more" id="more-data">I have come to know how astrology can change your life. I also came to know that astrology is such a thing that if you know astrology, it has the power to change your whole life.</div>

                                        </div>
                                        <div class="people_block">
                                            <img  class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/04-min.jpg" alt="People" width="100" height="100" />
                                            <div class="name_star">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                            </div>
                                            <div class="name">Neha Gupta</div>
                                            <div class="place"> Faridabad </div>
                                        </div>
                                        <div class="detail show-read-more">
                                            I am a student of Astro Shiromani 2022 in Asttrolok. I started my journey in Vedic science from here. And I've learned how to live life in a new way from here.
                                            <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                            <!--<div class="testinomial-content-more" id="more-data">After the session on relationships, I went to their home and apologised. It wasn't easy, it was difficult. But, it was worth it. After doing so, I felt so light in my heart. I can't put it into words. It felt-->
                                            <!--    as if the weight that I was carrying for many many years, had started shedding off. </div>-->
                                        </div>
                                          <div class="people_block">
                                            <img  class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/01-min.jpg" alt="People" width="100" height="100" />
                                            <div class="name_star">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                            </div>
                                           
                                            <div class="review_name">Aarti Puri</div>
                                            <div class="review_location"> Mumbai </div>
                                        </div>
                                        <div class="review_detail show-read-more">
                                                It is a life changing experience with Asttrolok. Before I joined Asttrolok, I was a non-believer and I had many questions about Astrology, but when I joined Asttrolok, <a class="show_hide" data-content="toggle-text">Read More</a>
                                                <div class="testinomial-content-more" id="more-data">It turned me into a believer when I came to know about Astrology and how it can change lives.</div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <div id="who" class="white_block who ">
                            <h3 class="brown_heading py-3">Who should Enroll?</h3>
                            <div class="row">
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Entrepreneurs-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">For Entrepreneurs:</h3>
                                    <div class="text_who px-2 px-md-3">Astrology can provide valuable insights into business decisions and investment opportunities, giving entrepreneurs an edge in the competitive business world.</div>
                                </div>
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Artists-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">For Artists:</h3>
                                    <div class="text_who px-2 px-md-3">Astrology can help artists tap into their creativity and find inspiration, unlocking new levels of self-expression and enhancing their artistic abilities.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Housewife Icon-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">For Homemakers / Housewives:</h3>
                                    <div class="text_who px-2 px-md-3">Studying astrology can lead to a new job in the consulting area. You may learn to use astrology skills to solve problems in your own and other people's lives.
                                    </div>
                                </div>
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Working Professionals-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">For Working Professionals / Freelancers:</h3>
                                    <div class="text_who px-2 px-md-3">Astrology can help working professionals and freelancers better understand their strengths and weaknesses, leading to better career choices, improved relationships with colleagues and clients, and increased success.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 px-md-32">
                                    <!--<img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Working Professionals-min.png" alt="healthy" height="50"  />-->
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Student-min.png" alt="healthy" height="50"  />
                                    <h3 class="heading_who px-md-3">For Students:</h3>
                                    <div class="text_who px-2 px-md-3">Unlock your potential and discover new opportunities by exploring the world of astrology and astrology can provide valuable insights into personality traits, strengths, and weaknesses, helping students make informed
                                        decisions about their education and career paths.
                                    </div>
                                </div>
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Spiritual Seekers-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">For Spiritual Seekers:</h3>
                                    <div class="text_who px-2 px-md-3">Astrology can help spiritual seekers better understand themselves, their purpose, and their connection to the universe, leading to a deeper sense of fulfillment and spiritual growth.


                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        

                        <div id="about" class="white_block_no_padding about">
<div id="about-1" class="white_block about-1 text-center">
<h3 class="brown_heading">About Asttrolok</h3>
<p class="aboput_text">
Asttrolok, founded in 2016, stands as one of the top three reputable online Vedic institutes in the country, dedicated to dispelling misconceptions and championing fact-based knowledge of Vedic Science in the fields of Astrology, Numerology, Palmistry, Yoga, Ayurveda & Scriptures. With students hailing from over 50+ countries, including professionals like lawyers, doctors, IITians, and actors, Asttrolok boasts a diverse and esteemed student body.</p><p>
</p><p class="aboput_text">
The institute's reputation is further enhanced by its association with the Founder, Renowned Astrologer & Trainer Mr. Alok Khandelwal & 50+ other mentors & panelists, who all bring their extensive expertise and experience to the teaching. Asttrolok's commitment to protecting & spreading the knowledge that liberates & transforms solidifies its standing as a leading institution in the realm of Vedic astrology.  
</p>
</div>
</div>
                        <!-- ################################################################-->
                        
                        
                        <div class="benefits_block"  id="benefits">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="white_block white_block_transparent count_block_whiteblock">
                            <center><h3 class="brown_heading whats_head py-3">Bonuses with this Astrology Course</h3></center>
                            <div class="include_details">
                                <div>

                                    <!--<img  srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/whats/tiny/21.webp 480w,resources/img/whats/tiny/21.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/whats/tiny/21.webp">-->
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Philosophical-min.png" alt="Philosophical - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Philosophical Discussions</h4>
                                        <div class="include_text">Explore the philosophical aspects of astrology, discussing its connection to life, destiny, and spirituality.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Astrology-Games-min.png" alt="Astrology Games - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Astrology Games</h4>
                                        <div class="include_text">Enjoy astrology-themed games and quizzes to test your knowledge and have fun while learning.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Mythology-min.png" alt="Mythology - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Mythology Exploration</h4>
                                        <div class="include_text pb-4">Discover the rich mythological stories behind ancient Hindu Veda, Vishnu Puran, Shiv Puran, zodiac signs, planets etc.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Meditation-and-min.png" alt="Meditation And - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Meditation and Mindfulness</h4>
                                        <div class="include_text">Learn techniques to enhance your intuitive abilities and connect with cosmic energies.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Software-min.png" alt="Software - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Software Training Videos</h4>
                                        <div class="include_text">A detailed instruction on how to use the tools & software for learning.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Career-Opportunity-through-min.png" alt="Career Opportunity Through - Asttrolok">
                                    <div class="include_details_bg">
                                        <h4>Career Opportunity through Asttrolok URGE</h4>
                                        <div class="include_text">Get a Platform to Showcase your Talent on Social Media accounts made for students only</div>
                                    </div>
                                </div>
                            </div>
                            
                            
                        </div>
                    </div>
                </div>
            </div>
                       
                        
                        <!--#################################################################-->
                        <div style="display:none;" class="white_block_mobile_adjust white_block text-center">
                           <center> <h3 class="brown_heading py-3">Bonuses with this Astrology Course</h3></center>
                            <!-- <h3 class="brown_heading py-3">Benefits You Will Gain <span class="gray">by taking this challenge</span></h3> -->
                            <div class="benefits_block">
                                <div class="row">
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Future Foresight-min.png" alt="Future Foresight - Asttrolok">
                                            <div class="benefits_desc">
                                                <div>Philosophical  </div>
                                                <div><strong>Discussions</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Decision-Making-min.png" alt="Decision Making - Asttrolok">
                                            <div class="benefits_desc">
                                                <div>Astrology  </div>
                                                <div><strong>Games</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Career Opportunity-min.png" alt="Career Opportunity - Asttrolok">
                                            <div class="benefits_desc">
                                                <div>Mythology  </div>
                                                <div><strong>Exploration</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Reduced Anxiety-min.png" alt="Reduced Anxiety - Asttrolok">
                                            <div class="benefits_desc">
                                                <div><strong> Meditation  </strong>and</div>
                                                <div><strong>Mindfulness</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content benefits_block_content_middle pt-0">

                                            <img  class="img-fluid" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Financial Stability-min.png" alt="Financial Stability - Asttrolok">
                                            <div class="benefits_desc">
                                                <div>Software  </div>
                                                <div><strong>Training Videos</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Trusted By 10K Students-min.png" alt="Trusted By 10k Students - Asttrolok">
                                            <div class="benefits_desc">
                                                <div>Career Opportunity through  </div>
                                                <div><strong>Asttrolok URGE</strong></div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        
                        
                        
                        <div id="about" class="white_block_no_padding about">
<div id="about-1" class="white_block about-1 text-center">
<div class="specialist-info1 queries  queries-info1" style="text-align:center;">
                                <h4 class="queries-head">For more queries or assistance <br></h4>
                               <h4 class="queries-head1">Call Now- <a href="tel:09174822333"> 09174822333</a></h4> 
                               <!--<h4 class="queries-head1" >Call Now- <a href="#" type="call">09174822333</a></h4>-->
                        </div>

</div>
</div>
                        
                        
                        <div id="faq1" class="white_block text-center faq">
                            <!--<div class="faq_mobile">-->

                            <!--    <img  class="img-fluid" srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp 480w,resources/img/faqs/tiny/faq-2-tiny.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp" id="faq_image">-->
                            <!--</div>-->
                           <center> <h3 class="brown_heading py-3">FAQs
                            </h3></center>
                            <!--<div class="faq_desktop">-->

                            <!--    <img  class="img-fluid" srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp 480w,resources/img/faqs/tiny/faq-2-tiny.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp">-->
                            <!--</div>-->
                            <div class="accordion" id="accordionExample1">
                                
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>How will live astrology classes be held?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                Online astrology classes will be held on an app called Zoom. We will provide you with a Zoom meeting Id from which you can connect 
                                               to the live class. Zoom has both audio & video features where you can speak your doubt.
                                     
                                                <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                                <div class="testinomial-content-more" id="more-data">   </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What is the duration of the courses?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                The entire online astrology course will be covered in 3 months, including assignments, practical, and exams. 
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What qualifications are required to do this course?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                There is no qualification required. Anybody can do this course who have the interest to learn astrology or Jyotish and build a career as a professional astrologer.
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What if we are not able to attend the course at a particular given time?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                We provide video recordings after every class, which you can watch later. You will get limited period access to all the recordings.
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What all is included in the study material?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                We will provide you with the entire study material that will help you to learn & practice this ancient education. Our study material 
                                                includes ppt & video recordings. <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                                <div class="testinomial-content-more" id="more-data">includes ppt & video recordings.
                                        </div>
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>How will the study material be provided?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                Study material (PPT & VIDEO) will be provided online through Portal only.
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>How long can I access the video recordings & notes?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                We give all the video recordings and notes which you can access for a limited period.
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">and we will contact you asap with the answer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What if I have any questions during the course?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                               You can ask all your doubts in between the classes. There will be a WhatsApp group too, in which you can drop your question, and we will contact you asap with the answer.
                                                         <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                                <div class="testinomial-content-more" id="more-data"> 
                                        </div>
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>Will there be any practical exam & assignments?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                Yes, we conduct practical sessions to get our students to practice better, as well as you need to submit assignments during the course.
                                                <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>How will the exam be conducted?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                After every course, we give a month for preparation. After that, an online exam will be held which is mandatory to get the certification in astrology.
                                                <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>Will I get a certificate? How do I receive the certificate after I finish the course? Is there any extra cost for it?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                Yes, the certificate will be given without any extra cost. There will be a certification ceremony in the institute else we will send it through courier.
                                                <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>Is there any installment facility?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                                Yes, you can pay the fee in installments. Installment details are mentioned above with timeframes.
                                        <!--                 <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data"> and click on enroll. With easy and safe transactions you can enroll yourself successfully.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        
                                

                                 
                        </div>
                        
                        
                        

                    </div>
                    
                    
<!--                     <div id="faq" class="white_block text-center faq">-->
                            
                            
<!--                            <div id="register_form" class="register_form text-center">-->
<!--                            <center>-->
<!--                                <h3 class="register_tag">Discover Your Potential as an Astrologer with Your Birth Details </h3>-->
<!--                            </center>-->
<!--                            <div class="register_mobile_details">-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date-white.png" alt="Date" width="100" height="100" />1 Live Class Per Month (Saturday)</span>-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-white.png" alt="Date" width="100" height="100" />Hindi</span>-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-white.png" alt="Date" width="100" height="100" />3 Months</span>-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee-white.png" alt="Date" width="100" height="100" /> Rs: 7999/-</span>-->
<!--                            </div>-->
<!--                          
<!--                            <div class="form_block">-->
                                
<!--                                <div id="vboutEmbedFormWrapper-100864">-->
<!--<form action="https://www.vbt.io/embedcode/submit/100864/?_format=page" target="_blank"  id="vboutEmbedForm-100864" name="vboutEmbedForm-100864" data-vboutform="100864" class="" method="post" enctype="multipart/form-data">-->
<!--<h1>Astromani 2023 All lead Form</h1>-->


<!--<div id="vboutEmbedFormResponse-100864" style="display: none;"></div>-->
<!--<fieldset>-->
<!--<div class="vbf-step">-->
<!--    <div class="vboutEmbedFormRow">-->
<!--        <label class="title" for="custom-358666">name<span class="required-asterisk">*</span></label>-->
<!--        <div class="vboutEmbedFormField"></div></div>-->
<!--        <div class="vboutEmbedFormRow"><label class="title" for="custom-358668">email<span class="required-asterisk">*</span></label>-->
<!--        <div class="vboutEmbedFormField"></div></div>-->
<!--        <div class="vboutEmbedFormRow"><label class="title" for="custom-358669">phone<span class="required-asterisk">*</span></label>-->
<!--        <div class="vboutEmbedFormField"></div></div>-->
<!--        <div class="vboutEmbedFormRow"><label class="title" for="custom-701452">city<span class="required-asterisk">*</span></label>-->
<!--        <div class="vboutEmbedFormField"></div></div></div>-->
<!--<div style="margin: 10px 0;">-->
<!--<div class="vboutEmbedFormRow vfb-submit ">-->
<!--<button type="submit" class="vbf-submit">Submit</button>-->
<!--</div>-->
<!--</div>-->
<!--</fieldset>-->
<!--</form>-->
<!--</div>-->
                                
<!--<form action="https://www.vbt.io/embedcode/submit/100864/?_format=page" target="_blank"  id="vboutEmbedForm-100864" name="vboutEmbedForm-100864" data-vboutform="100864" class="" method="post" enctype="multipart/form-data">-->


   
   
<!--       <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="display:none;">-->
<!--        <label for="inputEmail4">Amount</label>-->
<!--        <input type="number"  id="amount" class="require" name="amount" placeholder="amount" value="100" required>-->
<!--      </div>-->
<!--                                    <div class="row">-->
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Name</label>-->
<!--                                            <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="form-control vfb-text  required  358666" data-error="" placeholder="Name" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;" />-->
<!--                                            <div id="name_error"></div>-->
<!--                                        </div>-->
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Email</label>-->
<!--                                            <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control 358668 vfb-text  required  validate-email " data-error="" placeholder="Email" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;"  />-->
<!--                                            <div id="email_error"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="row">-->
                                        
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Phone Number</label>-->
<!--                                            <input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="form-control 358669 vfb-text  required  validate-phone blue" data-error="" placeholder="Whatsapp Number"  data-countrylist="yes" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;"/>-->
<!--                                            <div id="email_error"></div>-->
<!--                                        </div>-->
                                        
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
<!--                                            <label class="d-none">City</label>-->
<!--                                            <input type="text" name="vbout_EmbedForm[field][701452]" id="custom-701452" value="" class=" form-control vfb-text  required  " data-error="" placeholder="City" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;" />-->
<!--                                            <div id="city_error"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="row">-->
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Date of birth</label>-->
<!--                                            <input type="date" name="vbout_EmbedForm[field][717034]" id="custom-717034" value="" style=" color-scheme: dark;" class="form-control vfb-text  required  blue" data-error="" placeholder="Date of birth" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;" />-->
<!--                                            <div id="name_error"></div>-->
<!--                                        </div>-->
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Birth Time</label>-->
<!--                                            <input type="time" name="vbout_EmbedForm[field][717035]" id="custom-717035" value="" style=" color-scheme: dark;" class="form-control vfb-text  required  blue" data-error="" placeholder="Birth Time" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;"  />-->
<!--                                            <div id="email_error"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="row">-->
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Birth place</label>-->
<!--                                            <input type="text" name="vbout_EmbedForm[field][358671]" id="custom-358671" value="" class="form-control vfb-text  required  " data-error="" placeholder="Birth place" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;" />-->
<!--                                            <div id="name_error"></div>-->
<!--                                        </div>-->
<!--                                        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
<!--                                            <label class="d-none">Birth Time</label>-->
<!--                                            <input type="text" name="vbout_EmbedForm[field][717035]" id="custom-717035" value="" class="form-control vfb-text  required  " data-error="" placeholder="Birth Time" style="-webkit-text-fill-color: #fff;border: 1px solid #fff;"  />-->
<!--                                            <div id="email_error"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->

                                   
<!--                                    <center>-->
<!--                                        <button type="button" onclick="calls();" class="btn-get-started" style="margin-top: 0px; display=block;"  >Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;" /></button></center>-->
                                    
<!--                                        <button type="submit"  class="btn-get-started" style="margin-top: 0px; " id="submitBtn" >Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;" /></button></center>-->
<!--                                        <button type="button" onclick="submit_vboutcheckout1()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
<!--                                        <button type="submit"  id="submit_vbout1" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
<!--   <button type="submit"  style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" /></button></center>-->
<!--                                     <div class="contribution mobile_display_none">Contribution: ₹ 590</div> -->
<!--                                    <div id="success_msg"></div>-->
<!--                                </form>-->
                                
                             
<!--                            </div>-->
                            
                            
                            
                            
                            
                            
                            
                            
<!--                            <div class="right_block_container" id="pay2" style="display:none;">-->
<!--                        <div class="right_block mt-5" style="width:48%; float:left; background: #d3efff;" id="highlight_pay_desktop">-->
<!--                            <h3>One Shot Payment</h3>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date.png" alt="Date" width="100" height="100" />2 Classes Per Month</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="Date" width="100" height="100" />3 Months</span>-->

<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100"  />Rs: <strike>7999</strike> 7999/-</span>-->
<!--                            <a href="https://rzp.io/l/0dETgaUtX" class="btn-get-started scrollto right_register" id="register_right_button" target="_blank">Pay now-->
<!--                            <img  class="btn_arrow" id="right_register_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" />-->
<!--                            </a>-->
<!--                            <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Hindi</span>-->
<!--                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />valid only for the next 24 hours. </span> -->
<!--                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Reach your optimal weight</span> -->
<!--                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Eat and sleep better</span> -->
<!--                            <div class="people_joined_right">-->
<!--                                 <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/group.png" alt="Date"> -->
<!--                                 <span>-->
<!--<strong>-->
<!--56 </strong> people have already joined</span> -->
<!--                            </div>-->
<!--                        </div>-->
                        
<!--                        <div class="right_block mt-5" style="width:48%; float:right; background: #d3efff;" id="highlight_pay_desktop">-->
<!--                            <h3>Installments</h3>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date.png" alt="Date" width="100" height="100" />2 Classes Per Month</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="Date" width="100" height="100" />3 Months</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100"  />1st: Rs: 15000/-</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100"  />2nd: Rs: 22000/-</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100"  />3rd: Rs: 22000/-</span>-->
<!--                            <a href="https://rzp.io/l/vUtG84YkCk" class="btn-get-started scrollto right_register" id="register_right_button" target="_blank">Pay now-->
<!--                            <img  class="btn_arrow" id="right_register_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" />-->
<!--                            </a>-->
<!--                            <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Hindi</span>-->
<!--                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />valid only for the next 24 hours. </span> -->
<!--                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Reach your optimal weight</span> -->
<!--                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Eat and sleep better</span> -->
<!--                            <div class="people_joined_right">-->
<!--                                 <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/group.png" alt="Date"> -->
<!--                                 <span>-->
<!--<strong>-->
<!--56 </strong> people have already joined</span> -->
<!--                            </div>-->
<!--                        </div>-->
                        
                        
<!--                    </div>-->
                            
<!--                        </div>-->
<!--                        </div>-->

<section class="mt-30 mb-80 mt-md-50" style="font-family: 'Poppins', Sans-serif !important;">
            <h3 class="brown_heading">For more inquiry</h3>

            @if(!empty(session()->has('msg')))
                <div class="alert alert-success my-25 d-flex align-items-center">
                    <i data-feather="check-square" width="50" height="50" class="mr-2"></i>
                    {{ session()->get('msg') }}
                </div>
            @endif
<div id="validation">
                    
                </div>
            <form action="/contact/course"  id="formVal12345" method="post" class="mt-20 formVal12345">
                {{ csrf_field() }}
  <input type="hidden" name="subject" id="subject" maxlength="100" value="Astro-Shiromani-2024"/>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class=" font-weight-600  show-read-more1">Name*</label>
                            <input type="text" name="name" maxlength="60" id="customer_name" value="{{ old('name') }}" class="form-control @error('name')  is-invalid @enderror"/>
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class=" font-weight-600  show-read-more1">{{ trans('public.email') }}*</label>
                            <input type="text" name="email" maxlength="60" id="customer_email" value="{{ old('email') }}" class="form-control @error('email')  is-invalid @enderror"/>
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class=" font-weight-600  show-read-more1">{{ trans('site.phone_number') }}*</label>
                            <input type="text" name="phone" maxlength="10" id="customer_number" value="{{ old('phone') }}" class="form-control @error('phone')  is-invalid @enderror"/>
                            @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    {{--  <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class=" font-weight-600 d-none ">{{ trans('site.subject') }}*</label>--}}
                            <input type="hidden" name="course" maxlength="100" value="{{ $slug }}" class="form-control @error('subject')  is-invalid @enderror"/>
                           {{--    @error('subject')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>--}}
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class=" font-weight-600  show-read-more1">{{ trans('site.message') }}</label>
                            <textarea name="message" id="message" rows="5" maxlength="400" class="form-control @error('message')  is-invalid @enderror">{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

              {{--  <div class="row">
                    <div class="col-12 col-md-6">
                        @include('web.default2.includes.captcha_input')
                    </div>
                </div> --}}

                <button type="button" id="FormsubmitBtnsd" class="btn-get-started scrollto right_register">{{ trans('site.send_message') }}</button>
            </form>
        </section>
                                      <div id="about" class="white_block_no_padding about">
<div id="about-1" class="white_block about-1 text-center">
<div class="specialist-info1 queries  queries-info1" style="text-align:center;">
                                <h4 class="queries-head">For more queries or assistance <br></h4>
                               <h4 class="queries-head1">Call Now- <a href="tel:09174822333"> 09174822333</a></h4> 
                               <!--<h4 class="queries-head1" >Call Now- <a href="#" type="call">09174822333</a></h4>-->
                        </div>

</div>
</div>
                        <!-- ################################################################-->

                    <div class="right_block_container">
                        <div class="right_block register_desktop mt-5" style="">
                            <h3>Astrology Level 1</h3>
                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="Date" width="100" height="100" />3 Months</span>

                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100" />Rs: <span id="realPrice" data-value="75000" data-special-offer="13.4666" class="   text-gray text-decoration-line-through ">
                                            17,999
                                        </span><span class="font-16">7999 </span>/-</span>
                            <a  id="myBtn2" href="/course/astrology-basic-level"  style="cursor: pointer;" class="btn-get-started scrollto right_register" >Buy Now
                            <!--<img  class="btn_arrow" id="right_register_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" />-->
                            </a>
                            
                              
                            <!-- <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Eat and sleep better</span> -->
                            <div class="people_joined_right">
                                <!-- <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/group.png" alt="Date"> -->
                                <!-- <span>
<strong>
56 </strong> people have already joined</span> -->
                            </div>
                        </div>
                        <div class="register_mobile " >
<div class="register_mobile_heading">
<h3 class="mobile-head">Certified Astrology Course</h3>
<a  id="myBtn31" href="/course/astrology-basic-level" style="cursor: pointer;"  class="btn-get-started scrollto right_register">Register</a>
</div>
<div class="register_mobile_detail">
<span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Rupee">Total Fee: Rs: <span id="realPrice" data-value="75000" data-special-offer="13.4666" class="   text-gray text-decoration-line-through ">
                                            17,999
                                        </span><span class="font-16">7999 </span></span>
<!--<span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="duration" width="100" height="100"><span><strong>8</strong> Months</span></span>-->
</div>
</div>
                        <!--<div class="register_mobile">-->
                        <!--    <div class="register_mobile_heading">-->
                        <!--        <h3>Most Popular Astrology Course</h3>-->
                        <!--        <a href="index.html#register_form" class="btn-get-started scrollto right_register_text">Register</a>-->
                        <!--    </div>-->
                        <!--    <div class="register_mobile_detail">-->
                        <!--         <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Rupee">Contribution: Rs: 590</span> -->
                        <!--        <span class="date-and-time register_count_mobile"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/group.png" alt="Group" width="100" height="100" /><span><strong>56</strong> registered</span></span>-->
                        <!--    </div>-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
            
            
        
        </section>
        
    </main>
    <!-- <footer id="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 footer_column_1">
                    <a href="index.html"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/asttrolok_1592999765_51-removebg-preview.png" alt="icon" width="225" height="" /></a>
                    <p class="copyright1 mt-4">©2019 asttrolok | <a href="https://satvicmovement.org/pages/privacy-policy" target='_blank'>Privacy Policy</a></p>
                </div>
                <div class="col-lg-3 col-md-6 footer_column_2">
                </div>
                <div class="col-lg-3 col-md-6 footer_column_3">
                    <div class="social_media d-none">
                        <a href="https://www.facebook.com/satvicmovement/"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/fb.png" alt="social" width="100" height="100" /></a>
                        <a href="https://www.instagram.com/satvicmovement/?hl=en"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/sm.png" alt="social" width="100" height="100" /></a>
                        <a href="https://www.youtube.com/satvicmovement"><img  class="yt" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/yt.png" alt="social" width="100" height="100" /></a>
                    </div>
                </div>
            </div>
        </div>
    </footer> -->
    <!--<script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/optimize.js" ></script>-->
    <script async src="https://www.vbt.io/ext/vbtforms.js?lang=en" charset="utf-8"></script>



    
    
    
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/jquery-ui.min.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/main_rzp.js" ></script>
  

    
    <script>
    
    
    
      document.getElementById("submitBtn").addEventListener("click", myFunction);  
      function myFunction() { 
     
          if(document.getElementByName("name").value != "" && document.getElementByName("email").value != "" && document.getElementByName("phone").value != "" && document.getElementByName("message").value != ""){
        
      }
      }

    
    </script>
    

 <script>
        var page_url = "https://rechargestudio.com/astromani/";
        var workshop_date = "Sunday, 21 July 2024";
    </script>

   


    <script src="{{ config('app.js_css_url') }}/assets2/vendors/leaflet/leaflet.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/contact.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/optimize.js" ></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/library/country-picker-flags/build/js/countrySelect.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/library/country-picker-flags/build/js/country-std-code.js"  defer></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/jquery-ui.min.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/main_rzp.js" ></script>
    
    
    <script>
    // document.getElementById("FormsubmitBtn").addEventListener("click", contantFormSubmit);
     $('#FormsubmitBtnsd').click(function(){
    //   if(document.getElementsByName("name").value == "" || document.getElementsByName("email").value == "" || document.getElementsByName("phone").value == "" ){

    //     $('#validation').html('<div class="alert alert-danger my-25 d-flex align-items-center"><i data-feather="check-square" width="50" height="50" class="mr-2"></i>All Field is Requierd!<div>');
        

    //   }else{
    //       alert(document.getElementsByName("name").value);
    //     //   $('.sb').click();
    //   }
  
var formData = $("#formVal12345").serialize();
  name = document.getElementById("customer_name").value ;
   email = document.getElementById("customer_email").value;
     mobile = document.getElementById("customer_number").value;
      message = document.getElementById("message").value;
      subject=document.getElementById("subject").value;
 
      if(name == "" || email == "" || mobile == "" ){

        $('#validation').html('<div class="alert alert-danger my-25 d-flex align-items-center"><i data-feather="check-square" width="50" height="50" class="mr-2"></i>All Field is Requierd!<div>');
        

      }
 var data = {
            name: name,
            email: email,
            phone: mobile,
            message: message,
             subject: subject,
          }

//   $.ajax({
//     async: true,
//     type:'POST',
//     url:'/contact/course',
//     data: data,
//     cache: false,
//     processData: false,
//     contentType: false,
//     success: function (data) {
//       console.log(data)
//     },
//     error: function(request, status, error) {
//       console.log("error")
//     }
//   });
          $.ajax({
                method: 'post',
                 headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: '/contact/course',
                data: data,
            }).done(function(response, status){
                 $('#validation').html('<div class="alert alert-success my-25 d-flex align-items-center"><i data-feather="check-square" width="50" height="50" class="mr-2"></i>Form successfully submitted!<div>');
        
            }).fail(function(jqXHR, textStatus, errorThrown){
              $('#validation').html('<div class="alert alert-danger my-25 d-flex align-items-center"><i data-feather="check-square" width="50" height="50" class="mr-2"></i>All Field is Requierd!<div>');
        
            });
     })
   

    
      document.getElementById("submitBtn").addEventListener("click", myFunction);  
    //   document.getElementById("pay1").addEventListener("click", myFunction1);  
      function myFunction() { 
          if(document.getElementById("custom-358668").value != "" && document.getElementById("custom-358665").value != "" && document.getElementById("custom-358669").value != "" && document.getElementById("custom-701452").value != ""){
        // document.getElementById("submitBtn").style.display="none";
        document.getElementById("pay2").style.display="block";
        document.getElementById("pay1").style.display="block";}
      }
    //   function myFunction1() {
    //     window.location.href="http://www.asttrolok.com";
    //   }
    
    </script>
    
    <script>
        /*var movie = document.getElementById('content_video');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            movie.load();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            movie.onloadstart = function() {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                movie.play();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                movie.onerror = function() {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $("#thumb_0").html(youtubeVideoPlay(0, '1zqFp1bY4p8'));
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                };
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            };*/
        $(document).ready(function() {

            setTimeout(init_execution, 3000);

            function loadScript(url, callback) {
                var script = document.createElement("script");
                script.type = "text/javascript";
                if (script.readyState) { //IE
                    script.onreadystatechange = function() {
                        if (script.readyState == "loaded" || script.readyState == "complete") {
                            script.onreadystatechange = null;
                            script.setAttribute("crossorigin", "anonymous")
                            callback();
                        }
                    };
                } else { //Others
                    script.onload = function() {
                        callback();
                    };
                }
                script.src = url;
                document.getElementsByTagName("head")[0].appendChild(script);
            }

            function init_execution() {

                $(document).scroll(function() {
                    var y = $(this).scrollTop();
                    if (y > 190) {
                        $('.register_desktop').fadeIn();
                        $('.register_desktop').css("display", "block");
                    } else {
                        $('.register_desktop').fadeOut();
                        $('.register_desktop').css("display", "none");
                    }
                });

                $(document).scroll(function() {
                    var y = $(this).scrollTop();
                    var z = $(window).width();
                    if (y > 70 && y < 11500 && z < 769) {
                        $('.register_mobile').fadeIn();
                        $('.register_mobile').css("display", "block");
                    } else if (y >= 12500 && z < 769) {
                        $('.register_mobile').fadeIn();
                        $('.register_mobile').css("display", "block");
                    } else {
                        $('.register_mobile').fadeOut();
                        $('.register_mobile').css("display", "none");
                    }
                });
                $(document).ready(function() {
                    var workshop_date = $('#workshop_date').text();
                    var date = moment(workshop_date, "Do MMM, YYYY").format("Do MMM, Y");
                    $('#workshop_date').html(date);

                    /* loadScript("/resources/js/main_rzp.js?v33", function() {});
                    loadScript("/resources/js/jquery-ui.min.js", function() {});
                    loadScript("https://checkout.razorpay.com/v1/checkout.js", function() {}); */
                    /* loadScript("/library/country-picker-flags/build/js/countrySelect.js", function() {});
				loadScript("/library/country-picker-flags/build/js/country-std-code.js", function() {
				$("#country_selector").countrySelect({
                	defaultCountry:"in"
            	});
			}); */
                    //var video_link = 'https://www.youtube.com/embed/ADpoFKUSSHo';
                    //$('#banner-videos').prop('src', video_link);
                });

            }
            $("#country_selector").countrySelect({
                defaultCountry: "in"
            });
            $('#country_selector').keypress(function(event) {
                event.preventDefault();
                return false;
            });
        });
    </script>
    <script>
        var page_url = "https://ultimatehealthchallenge.in/";
        var workshop_date = "Sunday, 21 July 2024";
    </script>
    <script>
        let tabs = document.querySelectorAll('.tab');
        let content = document.querySelectorAll('.content-item');
        for (let i = 0; i < tabs.length; i++) {
            tabs[i].addEventListener('click', () => tabClick(i));
        }

        function tabClick(currentTab) {
            removeActive();
            tabs[currentTab].classList.add('active');
            content[currentTab].classList.add('active');
            console.log(currentTab);
        }

        function removeActive() {
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
                content[i].classList.remove('active');
            }
        }
    </script>
    <script>
        function openFullscreen(id) {
            var elem = document.getElementById(id);
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                /* IE11 */
                elem.msRequestFullscreen();
            }
            elem.play();
        }

        function youtubeVideoPlay(id, link) {
            if (id == 0) {
                $('#thumb_' + id).html('<iframe loading="lazy" width="100%" height="360" src="' & #32;+&# 32; link & #32;+&# 32;
                    '_autoplay=1.html" title="YouTube video player" frameborder="0" style="border-radius: 30px; width:636px;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
            } else {
                $('#thumb_' + id).html('<iframe loading="lazy" width="100%" src="' & #32;+&# 32; link & #32;+&# 32;
                    '_autoplay=1.html" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
            }
            return;
        }

        function isOnScreen(elem) {
            // if the element doesn't exist, abort
            if (elem.length == 0) {
                return;
            }
            var $window = jQuery(window)
            var viewport_top = $window.scrollTop()
            var viewport_height = $window.height()
            var viewport_bottom = viewport_top + viewport_height
            var $elem = jQuery(elem)
            var top = $elem.offset().top
            var height = $elem.height()
            var bottom = top + height

            return (top >= viewport_top && top < viewport_bottom) ||
                (bottom > viewport_top && bottom <= viewport_bottom) ||
                (height > viewport_height && top <= viewport_top && bottom >= viewport_bottom)
        }

        jQuery(document).ready(function() {
            window.addEventListener('scroll', function(e) {
                if (isOnScreen(jQuery('#register_form'))) { /* Pass element id/class you want to check */
                    $('#header').hide();
                } else {
                    $('#header').show();
                }
            });
        });
    </script>
 
@stack('styles_bottom')
@stack('scripts_bottom')


</body>

</html>