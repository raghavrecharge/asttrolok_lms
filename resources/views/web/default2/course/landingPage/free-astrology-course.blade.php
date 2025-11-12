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
    font-family: "Poppins", Sans-serif;
    width: 232px;
    transition: .3s all ease-in-out;
    box-shadow: 0 10px 10px #f1e4d2;
}

</style>

<!--<script src="//code.jivosite.com/widget/1gG0N75UaM" async></script>-->
</head>

<body>
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
                    <h1 id='title'>This Astrology knowledge is actually FREE with World-renowned Astrologer,</h1><h2 style="color: #43d276 !important;text-transform: uppercase;font-weight: 900;font-size: x-large;"><strong>Mr. Alok Khandelwal</strong></h2>
                    <!--<h2>Get an insight into everything the universe holds for your future.Enroll today in the best astrology course online and kick-start learning with masterfully-crafted guidance from world-renowned astrologer, <strong>Mr. Alok Khandelwal.</strong></h2>-->
                    
                    <div class="cls">
                        <!--<h2 class="hero"><strong>Total Fee 64900</strong></h2>-->
                        <!--<a  id="myBtn" class="btn-get-started scrollto" style="cursor: pointer;">Submit Appplication<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" /></a>-->
                        <a  href="/course/Astromani_2023" class="btn-get-started scrollto" style="cursor: pointer;">Submit Appplication<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" /></a>
                   <!--<button id="myBtn" class="btn-get-started scrollto">Register Now<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" /></button>-->
                    
                    </div><br><span style="font-family: "Poppins", Sans-serif;"><b>200+</b> people have already joined</span> <br><br>
                  <!--<button id="myBtn">Open Modal</button>-->
                    <div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close">&times;</span>
   <div id="register_form1" class="register_form text-center">
  <center>
      <h3 class="register_tag">Register Now</h3>
  </center>
  <div class="form_block register_form1" >
      <form action="https://www.vbt.io/embedcode/submit/101004/?_format=page"   id="vboutEmbedForm-101004" name="vboutEmbedForm-101004" data-vboutform="101004" class="" method="post" enctype="multipart/form-data">

          <div class="row">
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Name</label>
                  <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="form-control vfb-text  required  "    />
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
              </div>
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Email</label>
                  <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              </div>
          </div>
          <div class="row">
             
              
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-non1e">Phone Number</label>
                  <!--<input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">-->
                  <input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="form-control vfb-text  required  validate-phone "    data-countrylist="yes" />
                  <!--<div id="email_error"></div>-->
              </div>
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none1">City</label>-->
                  <!--<input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
              <!--    <input type="text" name="vbout_EmbedForm[field][710850]" id="custom-710850" value="" class="form-control vfb-text  required  "    />-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
          </div>
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Date of birth</label>-->
          <!--        <input type="date" name="vbout_EmbedForm[field][717036]" id="custom-717036" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth Time</label>-->
          <!--        <input type="time" name="vbout_EmbedForm[field][717033]" id="custom-717033" value="" class="form-control vfb-text  required   "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
          <!--    </div>-->
          <!--</div>-->
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth place</label>-->
          <!--        <input type="text" name="vbout_EmbedForm[field][358671]" id="custom-358671" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
              <!--    <label class="d-none1">Email</label>-->
              <!--    <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              <!--</div>-->
          <!--</div>-->

          <center>
             
              <button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
           <button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
   
      </form>
      <!--<form action="checkout.php" id="razorpay-frm-payment12" method="post" enctype="multipart/form-data"  style=" display:none;">-->


      <!--    <div class="row">-->
      <!--        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
      <!--            <label class="d-none">Name</label>-->
      <!--            <input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
      <!--        </div>-->
      <!--        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
      <!--            <label class="d-none">Email</label>-->
      <!--            <input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
      <!--        </div>-->
      <!--    </div>-->
      <!--    <div class="row">-->
             
              
      <!--        <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
      <!--            <label class="d-none">Phone Number</label>-->
      <!--            <input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
      <!--        </div>-->
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none">City</label>-->
              <!--    <input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
      <!--    </div>-->

      <!--    <center>-->
      <!--       <button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
      <!--     <button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
   
              <!--<button type="submit" class="btn-get-started1" style="margin-top: 0px;" id="submit_checkout">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
          
      <!--</form>-->
      
  </div>
  </div>
  </div>

</div><div id="myModal13" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close close4 ">&times;</span>
   <div id="register_form1" class="register_form text-center">
  <center>
      <h3 class="register_tag">Register Now</h3>
  </center>
  <div class="form_block register_form1" >
      <form action="https://www.vbt.io/embedcode/submit/101004/?_format=page"   id="vboutEmbedForm-101004" name="vboutEmbedForm-101004" data-vboutform="101004" class="" method="post" enctype="multipart/form-data">

          <div class="row">
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Name</label>
                  <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="form-control vfb-text  required  "    />
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
              </div>
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Email</label>
                  <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              </div>
          </div>
          <div class="row">
             
              
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-non1e">Phone Number</label>
                  <!--<input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">-->
                  <input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="form-control vfb-text  required  validate-phone "    data-countrylist="yes" />
                  <!--<div id="email_error"></div>-->
              </div>
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none1">City</label>-->
                  <!--<input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
              <!--    <input type="text" name="vbout_EmbedForm[field][710850]" id="custom-710850" value="" class="form-control vfb-text  required  "    />-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
          </div>
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Date of birth</label>-->
          <!--        <input type="date" name="vbout_EmbedForm[field][717036]" id="custom-717036" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth Time</label>-->
          <!--        <input type="time" name="vbout_EmbedForm[field][717033]" id="custom-717033" value="" class="form-control vfb-text  required   "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
          <!--    </div>-->
          <!--</div>-->
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth place</label>-->
          <!--        <input type="text" name="vbout_EmbedForm[field][358671]" id="custom-358671" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
              <!--    <label class="d-none1">Email</label>-->
              <!--    <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              <!--</div>-->
          <!--</div>-->

          <center>
             
              <button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
           <button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
   
      </form>
      <form action="checkout.php" id="razorpay-frm-payment12" method="post" enctype="multipart/form-data"  style=" display:none;">


          <div class="row">
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none">Name</label>
                  <input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">
                  <!--<div id="name_error"></div>-->
              </div>
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none">Email</label>
                  <input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">
                  <!--<div id="email_error"></div>-->
              </div>
          </div>
          <div class="row">
             
              
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none">Phone Number</label>
                  <input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">
                  <!--<div id="email_error"></div>-->
              </div>
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none">City</label>-->
              <!--    <input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
          </div>

          <center>
             
             <!--<button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
           <!--<button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>-->
   
              <button type="submit" class="btn-get-started1" style="margin-top: 0px;" id="submit_checkout">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
          
      </form>
      
  </div>
  </div>
  </div>

</div>




 <div id="myModalnew1" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close close2">&times;</span>
   <div id="register_form1" class="register_form text-center">
  <center>
      <h3 class="register_tag">Register Now</h3>
  </center>
  <div class="form_block register_form1" >
      <form action="https://www.vbt.io/embedcode/submit/101004/?_format=page"   id="vboutEmbedForm-101004" name="vboutEmbedForm-101004" data-vboutform="101004" class="" method="post" enctype="multipart/form-data">

          <div class="row">
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Name</label>
                  <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="form-control vfb-text  required  "    />
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
              </div>
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Email</label>
                  <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              </div>
          </div>
          <div class="row">
             
              
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Phone Number</label>
                  <!--<input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">-->
                  <input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="form-control vfb-text  required  validate-phone "    data-countrylist="yes" />
                  <!--<div id="email_error"></div>-->
              </div>
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none1">City</label>-->
                  <!--<input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
              <!--    <input type="text" name="vbout_EmbedForm[field][710850]" id="custom-710850" value="" class="form-control vfb-text  required  "    />-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
          </div>
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Date of birth</label>-->
          <!--        <input type="date" name="vbout_EmbedForm[field][717036]" id="custom-717036" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth Time</label>-->
          <!--        <input type="time" name="vbout_EmbedForm[field][717033]" id="custom-717033" value="" class="form-control vfb-text  required   "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
          <!--    </div>-->
          <!--</div>-->
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth place</label>-->
          <!--        <input type="text" name="vbout_EmbedForm[field][358671]" id="custom-358671" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
              <!--    <label class="d-none1">Email</label>-->
              <!--    <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              <!--</div>-->
          <!--</div>-->

          <center>
             
             <button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
           <button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
   
      </form>
     
      
  </div>
  </div>
  </div>

</div>

 <div id="myModalnew2" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close close3">&times;</span>
   <div id="register_form1" class="register_form text-center">
  <center>
      <h3 class="register_tag">Register Now</h3>
  </center>
  <div class="form_block register_form1" >
      <form action="https://www.vbt.io/embedcode/submit/101004/?_format=page"   id="vboutEmbedForm-101004" name="vboutEmbedForm-101004" data-vboutform="101004" class="" method="post" enctype="multipart/form-data">

          <div class="row">
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Name</label>
                  <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="form-control vfb-text  required  "    />
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
              </div>
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Email</label>
                  <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              </div>
          </div>
          <div class="row">
             
              
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Phone Number</label>
                  <!--<input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">-->
                  <input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="form-control vfb-text  required  validate-phone "    data-countrylist="yes" />
                  <!--<div id="email_error"></div>-->
              </div>
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none1">City</label>-->
                  <!--<input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
              <!--    <input type="text" name="vbout_EmbedForm[field][710850]" id="custom-710850" value="" class="form-control vfb-text  required  "    />-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
          </div>
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Date of birth</label>-->
          <!--        <input type="date" name="vbout_EmbedForm[field][717036]" id="custom-717036" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth Time</label>-->
          <!--        <input type="time" name="vbout_EmbedForm[field][717033]" id="custom-717033" value="" class="form-control vfb-text  required   "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
          <!--    </div>-->
          <!--</div>-->
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth place</label>-->
          <!--        <input type="text" name="vbout_EmbedForm[field][358671]" id="custom-358671" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
              <!--    <label class="d-none1">Email</label>-->
              <!--    <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              <!--</div>-->
          <!--</div>-->

          <center>
             
              <button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
           <button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
   
      </form>
     
      
  </div>
  </div>
  </div>

</div>

 <div id="myModalnew" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close close1">&times;</span>
   <div id="register_form1" class="register_form text-center">
  <center>
      <h3 class="register_tag">Register Now </h3>
  </center>
  <div class="form_block register_form1" >
      <form action="https://www.vbt.io/embedcode/submit/101004/?_format=page"   id="vboutEmbedForm-101004" name="vboutEmbedForm-101004" data-vboutform="101004" class="" method="post" enctype="multipart/form-data">

          <div class="row">
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Name</label>
                  <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="form-control vfb-text  required  "    />
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
              </div>
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Email</label>
                  <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              </div>
          </div>
          <div class="row">
             
              
              <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">
                  <label class="d-none1">Phone Number</label>
                  <!--<input type="text" maxlength="10" class="form-control" placeholder="Whatsapp Number*" name="billing-phone1" id="billing-phone1" required="" style="color: #000;">-->
                  <input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="form-control vfb-text  required  validate-phone "    data-countrylist="yes" />
                  <!--<div id="email_error"></div>-->
              </div>
              
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0 city_div">-->
              <!--    <label class="d-none1">City</label>-->
                  <!--<input type="text" class="form-control" placeholder="City*" required="" name="city1" id="city1" style="color: #000;">-->
              <!--    <input type="text" name="vbout_EmbedForm[field][710850]" id="custom-710850" value="" class="form-control vfb-text  required  "    />-->
                  <!--<div id="city_error"></div>-->
              <!--</div>-->
          </div>
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Date of birth</label>-->
          <!--        <input type="date" name="vbout_EmbedForm[field][717036]" id="custom-717036" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth Time</label>-->
          <!--        <input type="time" name="vbout_EmbedForm[field][717033]" id="custom-717033" value="" class="form-control vfb-text  required   "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
          <!--    </div>-->
          <!--</div>-->
          <!--<div class="row">-->
          <!--    <div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
          <!--        <label class="d-none1">Birth place</label>-->
          <!--        <input type="text" name="vbout_EmbedForm[field][358671]" id="custom-358671" value="" class="form-control vfb-text  required  "    />-->
                  <!--<input type="text" class="form-control" placeholder="Name*" name="billing-name1" id="billing-name1" style="color: #000;">-->
                  <!--<div id="name_error"></div>-->
          <!--    </div>-->
              <!--<div class="col-sm-12 col-lg-6 mb-3 mb-sm-0">-->
              <!--    <label class="d-none1">Email</label>-->
              <!--    <input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="form-control vfb-text  required  validate-email "    />-->
                  <!--<input type="email" class="form-control" placeholder="Email*" name="billing-email1" id="billing-email1" required="" style="color: #000;">-->
                  <!--<div id="email_error"></div>-->
              <!--</div>-->
          <!--</div>-->

          <center>
             
          <button type="button" onclick="submit_vboutcheckout()" class="btn-get-started1" style="margin-top: 0px;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
           <button type="submit"  id="submit_vbout" class="btn-get-started1" style="margin-top: 0px; display:none;">Submit <img  class="btn_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow-brown.png" alt="arrow" width="20" height="17" style="margin-bottom: 12px;"></button></center>
   
      </form>
     
      
  </div>
  </div>
  </div>

</div>
                </div>
                <div class="col-lg-6 pt-4 main_video pe-0 pe-sm-5 d-flex align-content-end" id="homepageimage">
                    <div class="video_container top_video p-0 me-0 me-sm-5" id="thumb_0">
                        <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:100%;" src="https://www.youtube.com/embed/jj4r-D-t4TY?si=JBXt0lp2f6NcailN" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" class="youtubeiframe" allowfullscreen></iframe>

                    </div>

                </div>
            </div>
        </div>
    </section>
<div  class="grid gtc-lg-5 gtc-md-2 gtc gtc-sm-1 date_time_block px-20" style="z-index: 20; position:relative;background: #f3f3f3;font-family: "Poppins", Sans-serif;">
                            
                            <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/4.png" alt="Time">
                                <div class="stdate">1 Live Class</div>
                                <div class="fulldate">Group Discussions</div>
                            </div>
                             <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/Icon2/165x93-4.png" alt="Time">
                                <div class="stdate">1 Recorded Video</div>
                                <div class="fulldate">13 Videos</div>
                            </div>
                            <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/3.png" alt="Global">
                                <div class="stdate">Language</div>
                                <div class="fulldate">Hindi</div>
                            </div>
                            <div class="item date_time duaration_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/2.png" alt="Duration">
                                <div class="stdate">Price</div>
                                <div class="fulldate">Free</div>
                            </div>
                            <div class="item date_time">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/1.png" alt="Appointment">
                                <div class="stdate">Class Timings</div>
                                <div class="fulldate" >5 Hours</div>
                            </div>
                        </div>


    <main id="main" style="font-family: "Poppins", Sans-serif;">
        <section id="included" class="Included">
            <div class="container">
                <div class="row ">
                    <div class="col-lg-9 main_video">
                        <div class="grid gtc-lg-4 gtc-md-2 gtc gtc-sm-1 date_time_block">
                            
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
                                <div class="stdate"  id="count3"></div><b><span>50,000+ Students</span></b>
                            </div>
                            <div class="item date_time duaration_time counts">
                                <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Icon/icon1/global-icon.png" alt="Duration">
                                <div class="stdate"  id="count4"></div><b><span>50+ Countries</span></b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          
           
            <div class="container Included_Block">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="white_block white_block_transparent count_block_whiteblock">
                            <center><h3 class="brown_heading whats_head py-3">What You get</h3></center>
                            <!--<div class="include_details">-->
                            <!--    <div>-->

                                    <!--<img  srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/whats/tiny/21.webp 480w,resources/img/whats/tiny/21.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/whats/tiny/21.webp">-->
                            <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/32-Live-Classes-min.png">-->
                            <!--        <div class="include_details_bg">-->
                            <!--            <h4>1 Live Class & 1 Recorded Video per Week</h4>-->
                            <!--            <div class="include_text">Dive deep into astrology with 32 dynamic live sessions taught by the renowned astrologer & trainer.</div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                             <div class="include_details">
                                <div>

                                    <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Class-Recordings-min.png">
                                    <div class="include_details_bg">
                                        <h4>Video Lectures</h4>
                                        <div class="include_text">A bundle of recorded videos to explain each & every detail of the course that you can go through any time.</div>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="include_details">-->
                            <!--    <div>-->

                            <!--        <img   src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/250-Additional-Videos-min.png">-->
                            <!--        <div class="include_details_bg">-->
                            <!--            <h4>250+ Additional Videos</h4>-->
                            <!--            <div class="include_text">Enhance your knowledge with over 250 additional videos covering various astrological concepts.</div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="include_details">
                                <div>

                                    <img   src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/30-Downloadable-PDFs-min.png">
                                    <div class="include_details_bg">
                                        <h4>Study Material PDFs</h4>
                                        <div class="include_text pb-4">Easy & quick access to the downloadable study material in the form of PPTs or PDFs for the topics that will get covered in class that help grasp the core concepts better.</div>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="include_details">-->
                            <!--    <div>-->

                            <!--        <img   src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/25-Doubt-Sessions-with-Mentors-min.png">-->
                            <!--        <div class="include_details_bg">-->
                            <!--            <h4>25 Doubt Sessions with Mentors</h4>-->
                            <!--            <div class="include_text">Interact and clarify your doubts through 25 dedicated doubt sessions with experienced mentors.</div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="include_details">-->
                            <!--    <div>-->

                            <!--        <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Test-Quiz-Assignments-min.png">-->
                            <!--        <div class="include_details_bg">-->
                            <!--            <h4>Test/Quiz/Assignments</h4>-->
                            <!--            <div class="include_text">Reinforce your learning with engaging tests, quizzes, and assignments to solidify your understanding.</div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="include_details">
                                <div>

                                    <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/WhatsApp-Group-min.png">
                                    <div class="include_details_bg">
                                        <h4>WhatsApp Group</h4>
                                        <div class="include_text"> Lifetime access to a group so you are always connected for any related information and get your queries resolved in case of doubts from our mentors and faculty.</div>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="include_details">-->
                            <!--    <div>-->

                            <!--        <img    src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Online-Exam-Certificate-min.png">-->
                            <!--        <div class="include_details_bg">-->
                            <!--            <h4>Online Exam & Certificate</h4>-->
                            <!--            <div class="include_text">Validate your skills through an online exam and receive a prestigious certificate upon course completion.</div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                           
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
                            <center><h3 class="brown_heading py-3">This Astrology Course to uplift your way of living</h3></center>
                            <!--<div class="faq_desktop">-->

                            <!--    <img  class="img-fluid" srcset="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp 480w,resources/img/faqs/tiny/faq-2-tiny.webp 800w" sizes="(max-width: 600px) 480px, 800px" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/tiny/faq-2-tiny.webp">-->
                            <!--</div>-->
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
									Complete Future Foresight
</button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <!--<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">-->
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                   Astrology is an ancient, complex and ever-evolving art and in order to fully harness the benefits that your horoscope provides, you need access to more than just a cookie-cutter astrological chart service so as not to miss any important details related specifically when it comes down to what it means for your life.
                                                </li>
                                               
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
										Better Decision-Making
</button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Develop a stronghold over your life with the knowledge of your stars which can give you more insight into how you think and react to certain situations. Using the knowledge of astrology, you can understand yourself with clarity and can even harness your talents in order to pursue your goals and ambitions.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
										Helps To Reduce Anxiety
</button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                   The alignment of your stars in your astrological birth chart has a lot to say about the course of your life. These planets have an influence over you, on everything from the way you deal with others, to how motivated or happy you are. With this knowledge at hand, make decisions that will eliminate these weaknesses and learn to manage the anxiety associated with the appropriate remedies that astrology offers.
                                                </li>
                                               
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
										Better Financial Stability
</button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                   Having learned this invaluable knowledge, channel your expertise to consult and guide people to lead a better life or join our panel of experts at the institute and contribute to the community of learners from around the world. Improve your financial interests with the newfound career opportunities & walk towards a better space in life.
                                                </li>
                                               
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSix">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
									Create Additional Opportunities</button>
                                    </h2>
                                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>
                                                  You can move on to other positions in life without leaving the existing one with Asttrolok. Here, you will get a chance to rework every facet of life creating an array of new opportunities. You'll be able to connect and grow with a vast community of learners committed to elevating themselves from all around the globe.
                                                </li>
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>

<!--                                <div class="accordion-item">-->
<!--                                    <h2 class="accordion-header" id="headingEight">-->
<!--                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">-->
<!--											Marriage & Children Astrology</button>-->
<!--                                    </h2>-->
<!--                                    <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#accordionExample">-->
<!--                                        <div class="accordion-body">-->
<!--                                            <ul>-->
<!--                                                <li>-->
<!--                                                    Marriage - When and How-->
<!--                                                </li>-->
<!--                                                <li>-->
<!--                                                    Calculation of Marriage Age-->
<!--                                                </li>-->
<!--                                                <li>-->
<!--                                                    Early Marriage, Late Marriage-->
<!--                                                </li>-->
<!--                                                <li>-->
<!--                                                    Denial of Marriage-->
<!--                                                </li>-->
<!--                                                <li>-->
<!--                                                    Coordination with Life Partner-->
<!--                                                </li>-->
<!--                                                <li>Love Marriage, Arrange Marriage</li>-->
<!--<li>Children- When and How</li>-->
<!--<li>Relationship between Children and Parents</li>-->
<!--                                            </ul>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="accordion-item">-->
<!--                                    <h2 class="accordion-header" id="headingFive">-->
<!--                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">-->
<!--											Career Astrology</button>-->
<!--                                    </h2>-->
<!--                                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionExample">-->
<!--                                        <div class="accordion-body">-->
                                            <!-- The charges for this workshop are 590 rupees (500 rupees + GST). -->
<!--                                            <ul>-->
<!--                                                <li>-->
<!--                                                    Assessment of Professional Promise from D10 chart-->
<!--                                                </li>-->
<!--                                                <li>-->
<!--                                                    Selection of Profession – Self-employed, Doctors, Accountant, Film Actors,-->
<!--                                                </li>-->
<!--                                                <li>-->
<!--                                                    Journalist, Writers, Business or Job, Professor-->
<!--                                                </li>-->
<!--                                                <li>Ups and down in Career</li>-->
<!--<li>Timing of important periods of Career through Horoscope</li>-->
<!--<li>Retirement period</li>-->
<!--                                            </ul>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
                                
           <!--                     <div class="accordion-item">-->
           <!--                         <h2 class="accordion-header" id="headingseven">-->
           <!--                             <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseseven" aria-expanded="false" aria-controls="collapseseven">-->
											<!--Remedial Astrology</button>-->
           <!--                         </h2>-->
           <!--                         <div id="collapseseven" class="accordion-collapse collapse" aria-labelledby="headingseven" data-bs-parent="#accordionExample">-->
           <!--                             <div class="accordion-body">-->
           <!--                                 <ul>-->
           <!--                                     <li>-->
           <!--                                         Remedies for malefic effects of planets-->
           <!--                                     </li>-->
           <!--                                     <li>-->
           <!--                                         Importance of Stones,Yantras and Mantras & Rudraksha-->
           <!--                                     </li>-->
           <!--                                     <li>-->
           <!--                                         Worship of Planets-->
           <!--                                     </li>-->
           <!--                                     <li>-->
           <!--                                         Remedies of various Planetary Problems-->
           <!--                                     </li>-->
                                                
           <!--                                     <li>Mantras Shlokas & Homas for different planets</li>-->
                                                
           <!--                                     <li>How to make Customized Remedy for a Person</li>-->
           <!--                                 </ul>-->
           <!--                             </div>-->
           <!--                         </div>-->
           <!--                     </div>-->

<!--                                 <div class="accordion-item">-->
<!--                                    <h2 class="accordion-header" id="headingTen">-->
<!--                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">-->
<!--Practical-->
<!--</button>-->
<!--                                    </h2>-->
<!--                                    <div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen" data-bs-parent="#accordionExample">-->
<!--                                        <div class="accordion-body">-->
<!--                                            <ul>-->
<!--                                                <li>-->
<!--                                                    Practical for all the important topics will be conducted on a regular basis.-->
<!--                                                </li>-->
<!--                                            </ul>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->

                                <!--<a class="load_more" id="load" href="index.php#">Load More</a>-->
                            </div>
                        </div>
                        
                        
                        <div id="curriculum" class="white_block text-center curriculum">
                            <center><h3 class="brown_heading py-3">What's inside?</h3></center>
                            <div class="content">
                                <div class="content-item active">
                                    <div class="grid gtc curr-sm-2 curr-md-2 curr-lg-2 curr_grid">
                                         <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Career-Advancement-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Introduction to Jyotish
                                            </div>
                                            <div class="heading_cur3">It's time to get through the real meaning and importance of Astrology. The lesson explains the real aspects of prediction and Vedic Science. How it brings you closer to your mind & body. Get ready to explore the significance and history of Astrology.
                                            </div>
                                        </div>
                                        
                                         <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Personal-Fulfilment-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Chronological footprints of Astrology
                                            </div>
                                            <div class="heading_cur3"> Astrology is a divine knowledge that connects us with life happenings and these life happenings are categories into various different parts of Indian Scriptures. This lesson will let you explore who you are and who you are becoming
                                            </div>
                                        </div>
                                        
                                        <div class="curr_left">
                                            <div class="heading_cur1 habit_icon"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Credibility-and-Trust-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Basic Guidelines to become a good Astrologer
                                            </div>
                                            <div class="heading_cur3">Every knowledge has its own guidelines. In this lesson, you will be exploring the basic guidelines, do's & don'ts of the prediction process. You will be focussing on keen observations and exploring new techniques of prediction.
                                            </div>
                                        </div>
                                       
                                        <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Enhanced-Skill-Set-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Role & Significance of Karma
                                            </div>
                                            <div class="heading_cur3">When we talk about Karma, it means actions. It is believed that our life events depend on the actions of our previous birth, what the Astrological significance of Karma and how it is related to our lives, will be explored in this lesson
                                            </div>
                                        </div>
                                        <div class="curr_left">
                                            <div class="heading_cur1 habit_icon m-0 p-0"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Professional-Networking-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Introduction to all 12 zodiac sign

                                            </div>
                                            <div class="heading_cur3">Introduction to Aries 
                                                Introduction to Taurus
                                                Introduction to Gemini
                                                Introduction to Cancer
                                                Introduction to Leo
                                                Introduction to Virgo
                                                Introduction to Libra
                                                Introduction to Scorpio
                                                Introduction to Sagittarius
                                                Introduction to Capricorn
                                                Introduction to Aquarius
                                                Introduction to Pisces
                                            </div>
                                        </div>
                                         <div class="curr_left">
                                            <div class="heading_cur1 habit_icon"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Credibility-and-Trust-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">Characteristics of all 9 Planets
                                            </div>
                                            <div class="heading_cur3">
                                                Characteristics of Planet Sun
                                                Characteristics of Planet Moon
                                                Characteristics of Planet Mars
                                                Characteristics of Planet Mercury
                                                Characteristics of Planet Venus
                                                Characteristics of Planet Jupiter
                                                Characteristics of Planet Saturn
                                                Characteristics of Planet Rahu
                                                Characteristics of Planet Ketu
                                            </div>
                                        </div>
                                        
                                         <div class="curr_left">
                                            <div class="heading_cur1 habit_icon"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Credibility-and-Trust-min.png" alt="healthy" class="w-100" /></div>
                                        </div>
                                        <div class="curr_right d-flex d-sm-block align-content-center flex-wrap">
                                            <div class="heading_cur1">How learning Astrology with Asttrolok is different?
                                            </div>
                                            <div class="heading_cur3">This unique knowledge culture has accommodated a wide variety of regional indigenous disciplines over time. Start exploring the language of stars with Asttrolok. This is pure spirituality that takes you to the purity and sacredness of the mother of all the sciences.
                                            </div>
                                        </div>
                                       
                                    </div>
                                     <div class="cls" style="text-align:center;">
                                        <a   id="myBtn1" href="/course/Astromani_2023"  style="cursor: pointer;" class="btn-get-started scrollto right_register">Apply Now<img  class="btn_arrow" id="register-arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;"></a></div>
                                       
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
                                    <h4 class="brown_heading py-3">Your Mentor</h4>
                                </center>
                                <div class="row">
                                    <div class="col-lg-6"><img  class="about_img w-100" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/Alok-sir1.png" alt="">

                                    </div>
                                    <div class="col-lg-6">
                                        <!-- <img  class="about_img w-100" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/Subah.webp" alt=""> -->  

                                        <h3 class="brown_heading mt-4 mb-3" id="hostname" style="font-size: 30px;">Mr. Alok Khandelwal</h3>
                                        <p class="aboput_text px-0 px-ml-5">
                                            <!-- <span class="d-block">Mr. Alok Khandelwal</span> -->
                                            <span class="d-block mt-md-3 mt-3">Mr. Alok Khandelwal is not only recognized for his exceptional astrological expertise but also for his global impact. He extends his teachings beyond borders, making Vedic Knowledge reach students in countries like Russia with the help of professional translators. Moreover, Mr. Khandelwal's passion for sharing knowledge takes him to various countries where he engages in public speaking, teaching, and consultation, and delivers introductory talks on Vedic science with a project called Unwinding. With over 35,000 students already taught and hundreds more added each year, Mr. Khandelwal's impact continues to grow. He is highly regarded for his expertise in Ancient Vedic Astrology and interpersonal skills, offering practical solutions to professional, personal, emotional, and mental challenges. Additionally, he holds membership in the esteemed 'Art of Living' foundation and is sought-after as a guest speaker in prestigious institutions nationwide. </span>
 <!--                                           <span class="d-block mt-md-3 mt-3">Moreover, Mr. Khandelwal's passion for sharing knowledge takes him to various countries where he engages in public speaking, teaching, and consultation, and delivers introductory talks on Vedic science with a project called Unwinding.</span>-->
 <!--<span class="d-block mt-md-3 mt-3">With over 35,000 students already taught and hundreds more added each year, Mr. Khandelwal's impact continues to grow. He is highly regarded for his expertise in Ancient Vedic Astrology and interpersonal skills, offering practical solutions to professional, personal, emotional, and mental challenges. Additionally, he holds membership in the esteemed 'Art of Living' foundation and is sought-after as a guest speaker in prestigious institutions nationwide.</span>-->

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
                       <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:100%;" src="https://www.youtube.com/embed/erO_9IDKUqs" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
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
                                             I am a 25-year-old guy who has learned Jyotish from Alok Khandelwal Sir to break the stereotype thinking that it is only for old aged people. Astrology is the first step of enlightenment. I think Alok Sir has made a group of people who are devoted to god and it's called Asttrolok.
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
                                        <!--<div class="people_block">-->
                                        <!--    <img  class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/02-min.jpg" alt="People" width="100" height="100" />-->
                                        <!--    <div class="name_star">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--    </div>-->
                                        <!--    <div class="name">Vikas Gupta</div>-->
                                        <!--    <div class="place"> Indore</div>-->
                                        <!--</div>-->
                                        <!--<div class="detail show-read-more">-->
                                        <!--    I know Asttrolok since the day it was formed. For me Asttrolok is not an institute or a medium of astrology, <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--    <div class="testinomial-content-more" id="more-data">-->
                                        <!--        its a medium to live a life for me. My Asttrolok’s journey is very delightful and memorable.</div>-->
                                        <!--</div>-->
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
                                          I joined Asttrolok last year. After coming to Asttrolok, I realized how important the guidance of a guru is in life. Ever since I joined this community, I have come to know how astrology can change your life. I also came to know that astrology is such a thing that if you know astrology, it has the power to change your whole life.
                                            <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                            <!--<div class="testinomial-content-more" id="more-data">I have come to know how astrology can change your life. I also came to know that astrology is such a thing that if you know astrology, it has the power to change your whole life.</div>-->

                                        </div>
                                        <!--<div class="people_block">-->
                                        <!--    <img  class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/04-min.jpg" alt="People" width="100" height="100" />-->
                                        <!--    <div class="name_star">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--        <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">-->
                                        <!--    </div>-->
                                        <!--    <div class="name">Neha Gupta</div>-->
                                        <!--    <div class="place"> Faridabad </div>-->
                                        <!--</div>-->
                                        <!--<div class="detail show-read-more">-->
                                        <!--    I am a student of Astromani 2022 in Asttrolok. I started my journey in Vedic science from here. And I've learned how to live life in a new way from here.-->
                                            <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                            <!--<div class="testinomial-content-more" id="more-data">After the session on relationships, I went to their home and apologised. It wasn't easy, it was difficult. But, it was worth it. After doing so, I felt so light in my heart. I can't put it into words. It felt-->
                                            <!--    as if the weight that I was carrying for many many years, had started shedding off. </div>-->
                                        <!--</div>-->
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
                                               It is a life changing experience with Asttrolok. Before I joined Asttrolok, I was a non-believer and I had many questions about Astrology, but when I joined Asttrolok, It turned me into a believer when I came to know about Astrology and how it can change lives.
                                                <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                                <!--<div class="testinomial-content-more" id="more-data">It turned me into a believer when I came to know about Astrology and how it can change lives.</div>-->
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <div id="who" class="white_block who ">
                            <h3 class="brown_heading py-3">How impactful this course is for you?</h3>
                            <div class="row">
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Entrepreneurs-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">Complete Future Foresight</h3>
                                    <!--<div class="text_who px-2 px-md-3">Astrology can provide valuable insights into business decisions and investment opportunities, giving entrepreneurs an edge in the competitive business world.</div>-->
                                </div>
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Artists-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">Understand yourself through your Zodiac Sign</h3>
                                    <!--<div class="text_who px-2 px-md-3">Astrology can help artists tap into their creativity and find inspiration, unlocking new levels of self-expression and enhancing their artistic abilities.-->
                                    <!--</div>-->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Housewife Icon-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">Build a strong connection with Vedas</h3>
                                    <!--<div class="text_who px-2 px-md-3">Studying astrology can lead to a new job in the consulting area. You may learn to use astrology skills to solve problems in your own and other people's lives.-->
                                    <!--</div>-->
                                </div>
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Working Professionals-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">History & significance of Astrology</h3>
                                    <!--<div class="text_who px-2 px-md-3">Astrology can help working professionals and freelancers better understand their strengths and weaknesses, leading to better career choices, improved relationships with colleagues and clients, and increased success.-->
                                    <!--</div>-->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 px-md-32">
                                    <!--<img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Working Professionals-min.png" alt="healthy" height="50"  />-->
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Student-min.png" alt="healthy" height="50"  />
                                    <h3 class="heading_who px-md-3">Fact-based flexible guidance by Mr. Alok Khandelwal</h3>
                                    <!--<div class="text_who px-2 px-md-3">Unlock your potential and discover new opportunities by exploring the world of astrology and astrology can provide valuable insights into personality traits, strengths, and weaknesses, helping students make informed-->
                                    <!--    decisions about their education and career paths.-->
                                    <!--</div>-->
                                </div>
                                <div class="col-lg-6 px-md-32">
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Spiritual Seekers-min.png" alt="healthy" height="50" />
                                    <h3 class="heading_who px-md-3">Know what qualities can make you a Good Astrologer</h3>
                                    <!--<div class="text_who px-2 px-md-3">Astrology can help spiritual seekers better understand themselves, their purpose, and their connection to the universe, leading to a deeper sense of fulfillment and spiritual growth.-->
                                    <!--</div>-->
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
                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Philosophical-min.png">
                                    <div class="include_details_bg">
                                        <h4>Philosophical Discussions</h4>
                                        <div class="include_text">Explore the philosophical aspects of astrology, discussing its connection to life, destiny, and spirituality.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Astrology-Games-min.png">
                                    <div class="include_details_bg">
                                        <h4>Astrology Games</h4>
                                        <div class="include_text">Enjoy astrology-themed games and quizzes to test your knowledge and have fun while learning.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Mythology-min.png">
                                    <div class="include_details_bg">
                                        <h4>Mythology Exploration</h4>
                                        <div class="include_text pb-4">Discover the rich mythological stories behind ancient Hindu Veda, Vishnu Puran, Shiv Puran, zodiac signs, planets etc.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Meditation-and-min.png">
                                    <div class="include_details_bg">
                                        <h4>Meditation and Mindfulness</h4>
                                        <div class="include_text">Learn techniques to enhance your intuitive abilities and connect with cosmic energies.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Software-min.png">
                                    <div class="include_details_bg">
                                        <h4>Software Training Videos</h4>
                                        <div class="include_text">A detailed instruction on how to use the tools & software for learning.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="include_details">
                                <div>

                                    <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/bonus/Career-Opportunity-through-min.png">
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

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Future Foresight-min.png">
                                            <div class="benefits_desc">
                                                <div>Philosophical  </div>
                                                <div><strong>Discussions</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Decision-Making-min.png">
                                            <div class="benefits_desc">
                                                <div>Astrology  </div>
                                                <div><strong>Games</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Career Opportunity-min.png">
                                            <div class="benefits_desc">
                                                <div>Mythology  </div>
                                                <div><strong>Exploration</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Reduced Anxiety-min.png">
                                            <div class="benefits_desc">
                                                <div><strong> Meditation  </strong>and</div>
                                                <div><strong>Mindfulness</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content benefits_block_content_middle pt-0">

                                            <img  class="img-fluid" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Financial Stability-min.png">
                                            <div class="benefits_desc">
                                                <div>Software  </div>
                                                <div><strong>Training Videos</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="benefits_block_content">

                                            <img  class="img-fluid"  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Trusted By 10K Students-min.png">
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
                                            <div class="review"><h4>What is the duration of this video courses?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                              This course comprises of total 13 videos (5 hours) which you can watch anywhere, anytime. You will get limited period access to all the recordings.
                                     
                                                <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                                <div class="testinomial-content-more" id="more-data">   </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What qualifications are required to do this course?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                               There is no qualification required. Anybody can buy this video course who have the interest to learn astrology or Jyotish and build a career as a professional astrologer.​
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What will get included in the study material?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                               We will provide you with an entire study material which will help you a lot. Our study material includes ppt & video recordings.
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
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
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                            </div>
                                        </div>
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>What all is included in the study material?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        We will provide you with the entire study material that will help you to learn & practice this ancient education. Our study material -->
                                        <!--        includes ppt & video recordings. <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">includes ppt & video recordings.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>How will the study material be provided?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        Study material (PPT & VIDEO) will be provided online through Portal only.-->
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>How long can I access the video recordings & notes?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        We give all the video recordings and notes which you can access for a limited period.-->
                                        <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">and we will contact you asap with the answer.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        <div class="highlight_review text-center mt-0">
                                            <div class="review"><h4>What if I have any questions after completing the video course?</h4></div>
                                            
                                            <div class="review_detail show-read-more1">
                                              There will be a WhatsApp group where you can drop your question, and we will contact you asap with the answers.
                                                         <!--<a class="show_hide" data-content="toggle-text">Read More</a>-->
                                                <div class="testinomial-content-more" id="more-data"> 
                                        </div>
                                            </div>
                                        </div>
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>Will there be any practical exam & assignments?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        Yes, we conduct practical sessions to get our students to practice better, as well as you need to submit assignments during the course.-->
                                                <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>How will the exam be conducted?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        After every course, we give a month for preparation. After that, an online exam will be held which is mandatory to get the certification in astrology.-->
                                                <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>Will I get a certificate? How do I receive the certificate after I finish the course? Is there any extra cost for it?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        Yes, the certificate will be given without any extra cost. There will be a certification ceremony in the institute else we will send it through courier.-->
                                                <!--         <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data">awareness of Astrology principles. As a result, there is an increasing demand for professional and reliable experts in the domain and it would be a wise decision for anyone thinking of becoming a professional Astrologer.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        <!--<div class="highlight_review text-center mt-0">-->
                                        <!--    <div class="review"><h4>Is there any installment facility?</h4></div>-->
                                            
                                        <!--    <div class="review_detail show-read-more1">-->
                                        <!--        Yes, you can pay the fee in installments. Installment details are mentioned above with timeframes.-->
                                        <!--                 <a class="show_hide" data-content="toggle-text">Read More</a>-->
                                        <!--        <div class="testinomial-content-more" id="more-data"> and click on enroll. With easy and safe transactions you can enroll yourself successfully.-->
                                        <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        
                                        
                                

                                 
                        </div>
                        
                        
                        

                    </div>
                    
                    
<!--                     <div id="faq" class="white_block text-center faq">-->
                            
                            
<!--                            <div id="register_form" class="register_form text-center">-->
<!--                            <center>-->
<!--                                <h3 class="register_tag">Discover Your Potential as an Astrologer with Your Birth Details </h3>-->
<!--                            </center>-->
<!--                            <div class="register_mobile_details">-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date-white.png" alt="Date" width="100" height="100" />1 Live Class Per Week (Saturday)</span>-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-white.png" alt="Date" width="100" height="100" />Hindi</span>-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-white.png" alt="Date" width="100" height="100" />8 Months</span>-->
<!--                                <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee-white.png" alt="Date" width="100" height="100" /> Rs: 64900/-</span>-->
<!--                            </div>-->
<!--                            <form style="display: none;" id="rxp_frm">-->
<!--                                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">-->
<!--                                <input type="hidden" name="razorpay_signature" id="razorpay_signature">-->
<!--                                <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">-->
<!--                            </form>-->
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
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date.png" alt="Date" width="100" height="100" />2 Classes Per week</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="Date" width="100" height="100" />8 Months</span>-->

<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100"  />Rs: <strike>64900</strike> 64900/-</span>-->
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
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date.png" alt="Date" width="100" height="100" />2 Classes Per week</span>-->
<!--                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="Date" width="100" height="100" />8 Months</span>-->
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
                            <h3>Let's open the door of opportunities with the knowledge of Astrology at no cost.</h3>
                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/date.png" alt="Date" width="100" height="100" />13 Video</span>
                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/time-blue.png" alt="Date" width="100" height="100" />5 Hours</span>

                            <span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Date" width="100" height="100" />FREE</span>
                            <a  id="myBtn2" href="/course/Astromani_2023"  style="cursor: pointer;" class="btn-get-started scrollto right_register" >Avail Now
                            <!--<img  class="btn_arrow" id="right_register_arrow" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/btn-arrow.png" alt="Arrow" width="20px" height="14px" style="margin-top: 0px; display: none;" />-->
                            </a>
                             <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Hindi</span>
                             <!--<span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />1 Recorded Video Per Week</span>-->
                             <!--<span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Class Timings : 7-9 pm IST</span> -->
                            <!-- <span class="date-and-time gray"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/green-tick.png" alt="Date" width="100" height="100" />Eat and sleep better</span> -->
                            <div class="people_joined_right">
                                <!-- <img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/group.png" alt="Date"> -->
                                <!-- <span>
<strong>
56 </strong> people have already joined</span> -->
                            </div>
                        </div>
                        <div class="register_mobile d-block" >
<div class="register_mobile_heading">
<h3 class="mobile-head">Certified Astrology Course</h3>
<a  id="myBtn31" href="/course/Astromani_2023" style="cursor: pointer;"  class="btn-get-started scrollto right_register">Register</a>
</div>
<div class="register_mobile_detail">
<span class="date-and-time"><img  src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/rupee.png" alt="Rupee">Total Fee: Rs: 64900 </span>
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
     
          if(document.getElementById("custom-358668").value != "" && document.getElementById("custom-358665").value != "" && document.getElementById("custom-358669").value != "" && document.getElementById("custom-701452").value != ""){
        
      }
      }

    
    </script>
    

 <script>
        var page_url = "https://rechargestudio.com/astromani/";
        var workshop_date = "Sunday, 21 July 2024";
    </script>

   
    <script>
// Get the modal myModalnew
var modal = document.getElementById("myModal");
var myModal13 = document.getElementById("myModal13");
var myModalnew = document.getElementById("myModalnew");
var myModalnew1 = document.getElementById("myModalnew1");
var myModalnew2 = document.getElementById("myModalnew2");
// Get the button that opens the modal
var btn = document.getElementById("myBtn");
var btn1 = document.getElementById("myBtn1");
var btn2 = document.getElementById("myBtn2");
var btn31 = document.getElementById("myBtn31");
var btn4 = document.getElementById("myBtn4");
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];
var span1 = document.getElementsByClassName("close1")[0];
var span2 = document.getElementsByClassName("close2")[0];
var span3 = document.getElementsByClassName("close3")[0];
var span4 = document.getElementsByClassName("close4")[0];
// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}
btn1.onclick = function() {
  modal.style.display = "block";
}
btn2.onclick = function() {
  modal.style.display = "block";
}
btn31.onclick = function() {
  modal.style.display = "block";
}
btn4.onclick = function() {
  modal.style.display = "block";
}
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}
span4.onclick = function() {
  modal.style.display = "none";
}
span1.onclick = function() {
   modal.style.display = "none";
}
span2.onclick = function() {
  modal.style.display = "none";
}
span3.onclick = function() {
  modal.style.display = "none";
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
//   if (event.target == myModal13) {
//     modal.style.display = "none";
//   }
//   if (event.target == myModalnew) {
//     modal.style.display = "none";
//   }
//   if (event.target == myModalnew1) {
//     modal.style.display = "none";
//   }
//   if (event.target == myModalnew2) {
//     modal.style.display = "none";
//   }
}
function submit_vboutcheckout(){
   var name=$('#custom-358666').val();
    var email = $('#custom-358668').val();
    var phone = $('#custom-358669').val();
    // var city=$('#custom-710850').val();
    $('#billing-name1').val(name);
    $('#billing-email1').val(email);
    $('#billing-phone1').val(phone);
    // $('#city1').val(city);
    $('#submit_checkout').click();
    $('#submit_vbout').click();
    
}
function submit_vboutcheckout1(){
   
   var name=$('.358666').val();
    var email = $('.358668').val();
    var phone = $('.358669').val();
    // var city=$('#custom-710850').val();
    $('#billing-name1').val(name);
    $('#billing-email1').val(email);
    $('#billing-phone1').val(phone);
    // $('#city1').val(city);
     alert(phone);
    $('#submit_checkout').click();
    $('#submit_vbout1').click();
    
}
</script>



    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/optimize.js" ></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/library/country-picker-flags/build/js/countrySelect.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/library/country-picker-flags/build/js/country-std-code.js"  defer></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/jquery-ui.min.js" defer></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/js/main_rzp.js" ></script>
    
    <script>
    
    
    
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
 

</body>

</html>