@extends('web.default.layouts.app')

@section('content')
<style>
    body{
        font-family: "Poppins", Sans-serif !important;
                font-size: 16px !important;
    }
    .forums-featured-section {
    padding-bottom: 0px !important;
}
</style>

<section class="container forums-featured-section mt-30 mt-md-50">

            <div class="text-center mb-30">
                <h1 class="font-36 font-weight-bold text-secondary">How can we help you ?</h1>
                <p class="font-14 text-gray">Follow simple steps and video tutorials to easily navigate and use the site effectively.</p>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to Login.svg" alt="What is social media ?" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to Login ?</h4>
                            </a>

                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to Reset Your Password.svg" alt="How do you put a Group Link in a note card" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import1" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to Reset Your Password ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to Purchase an Online Course.svg" alt="The best texture quality settings for makeup" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import2" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How  to Purchase an Online Course ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to See Access Course Content.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import3" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to See/Access Course Content ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to View Quiz Results and Answers.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import4" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to View Quiz Results and Answers ?</h4>
                            </a>
                        </div>
                    </div>

                </div>
                <div class="col-12 col-lg-6">

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to give a second attempt for Quiz.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import5" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to give a second attempt for Quiz ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to set a meeting time for the instructor.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import6" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to set a meeting time for the instructor ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to download a ppt file.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import7" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to download a ppt file ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/How to Complete the Quiz in Waiting.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import8" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to Complete the Quiz in Waiting ?</h4>
                            </a>
                        </div>
                    </div>

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                        <div class="forums-featured-card-icon">
                            <img loading="lazy" src="/store/1/default_images/forums/icons/QUIZ.svg" alt="What favorite food and or beverage do you enjoy" class="img-cover">
                        </div>
                        <div class="ml-15">
                            <a href="#" data-toggle="modal" data-target="#import9" class="">
                                <h4 class="font-16 font-weight-bold text-dark">How to attempt the Quiz ? </h4>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="forums-featured-bg-box"></div>
        </section>

    <section class="container forum-question-section bg-info-light rounded-lg">
        <div class="row">
            <div class="col-12 col-md-7">
                <div class="px-10 px-md-25 py-25 p-md-50">
                    <h2 class="font-36 font-weight-bold text-secondary mt-50">
                        <span class="">Still Have Questions ?</span>
                        <span class="d-block">Our support team is here for you!</span>
                    </h2>

                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center mt-15">
                        <a href="/contact" class="btn btn-primary">
                            <i data-feather="phone" class="mr-5 text-white" width="16" height="16"></i>
                            Contact Us
                        </a>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-5 d-none d-md-block position-relative">
                <div class="forum-question-section__img">
                    <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/forum/question-section.png" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </section>
  <div class="modal fade" id="import" tabindex="-1" aria-labelledby="import" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to Login ?</h2>

                                                <p class="mt-10">1. Click on the <a href="/login" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Login</a> option from right corner
                                                </p>
                                                 <p class="mt-10">2. Now enter the email id in  username box
                                                </p>
                                                 <p class="mt-10 ">3. Now enter the password
                                                </p>

                                                 <p class="mt-10">4. Click the <b>Login</b> button.
                                                </p>
                                                 <p class="mt-10">5. Wait a few moments and my purchase page will be open
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/8-ex8z1_1x8?si=qClj_ywgoI5WQjv5?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to Login ?</h2>

                                                <p class="mt-10">1. Click on the <a href="/login" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Login</a> option from right corner
                                                </p>
                                                 <p class="mt-10">2. Now enter the email id in  username box
                                                </p>
                                                 <p class="mt-10 ">3. Now enter the password
                                                </p>

                                                 <p class="mt-10">4. Click the <b>Login</b> button.
                                                </p>
                                                 <p class="mt-10">5. Wait a few moments and my purchase page will be open
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/8-ex8z1_1x8?si=qClj_ywgoI5WQjv5?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import1" tabindex="-1" aria-labelledby="import1" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to Reset Your Password ?</h2>

                                                <p class="mt-10">1. Click on the login button from the top right corner (or click on this link )
                                                </p>
                                                 <p class="mt-10 ">2. Click on the <a href="/forget-password" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Forgot Password</a>
                                                </p>
                                                 <p class="mt-10">3. Enter the register email id in email box
                                                </p>
                                                 <p class="mt-10">4. Click the <b>Reset Password</b> button
                                                </p>
                                                <p class="mt-10">5. You will receive confirmation that a password reset email has been sent.
                                                </p>
                                                <p class="mt-10">6. Open your email inbox or spam and search for an email from <b>Asttrolok</b>.
                                                </p>
                                                <p class="mt-10">7. Open the email and click on the link provided to reset your password.
                                                </p>
                                                 <p class="mt-10">8. After clicking the link, you can create a new password
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/Nxd5jQrPjcM?si=es7uwmVSo0KJ-Gcl?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to Reset Your Password ?</h2>

                                                <p class="mt-10">1. Click on the login button from the top right corner (or click on this link )
                                                </p>
                                                 <p class="mt-10 ">2. Click on the <a href="/forget-password" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Forgot Password</a>
                                                </p>
                                                 <p class="mt-10">3. Enter the register email id in email box
                                                </p>
                                                 <p class="mt-10">4. Click the <b>Reset Password</b> button
                                                </p>
                                                <p class="mt-10">5. You will receive confirmation that a password reset email has been sent.
                                                </p>
                                                <p class="mt-10">6. Open your email inbox or spam and search for an email from <b>Asttrolok</b>.
                                                </p>
                                                <p class="mt-10">7. Open the email and click on the link provided to reset your password.
                                                </p>
                                                 <p class="mt-10">8. After clicking the link, you can create a new password
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/Nxd5jQrPjcM?si=es7uwmVSo0KJ-Gcl?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import2" tabindex="-1" aria-labelledby="import2" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How  to Purchase an Online Course ?</h2>

                                                <p class="mt-10">1. Go to courses dropdown and click on the <b>View all</b> options (click on the link)
                                                </p>
                                                 <p class="mt-10">2. Click on the course name you want to purchase and see course details
                                                </p>
                                                 <p class="mt-10 ">3. On the course details page, Click on the <b>Buy Now</b> button.
                                                </p>
                                                 <p class="mt-10">4. On Checkout screen  Enter your <b>name, email ID and mobile number</b> in the form (Fill The Form)
                                                </p>
                                                 <p class="mt-10">5. Click the <b>Start Payment</b> button.
                                                </p>
                                                <p class="mt-10">6. You choose the payment options like (UPI ,Credit/Debit card and Internet Banking)
                                                </p>
                                                <p class="mt-10">7. Once the payment is successful, wait for few seconds you will be redirected back to the website
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/0kHqc10rPZI?si=W4uSgqRVWI8bn0uG?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How  to Purchase an Online Course ?</h2>

                                                <p class="mt-10">1. Go to courses dropdown and click on the <b>View all</b> options (click on the link)
                                                </p>
                                                 <p class="mt-10">2. Click on the course name you want to purchase and see course details
                                                </p>
                                                 <p class="mt-10 ">3. On the course details page, Click on the <b>Buy Now</b> button.
                                                </p>
                                                 <p class="mt-10">4. On Checkout screen  Enter your <b>name, email ID and mobile number</b> in the form (Fill The Form)
                                                </p>
                                                 <p class="mt-10">5. Click the <b>Start Payment</b> button.
                                                </p>
                                                <p class="mt-10">6. You choose the payment options like (UPI ,Credit/Debit card and Internet Banking)
                                                </p>
                                                <p class="mt-10">7. Once the payment is successful, wait for few seconds you will be redirected back to the website
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/0kHqc10rPZI?si=W4uSgqRVWI8bn0uG?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import3" tabindex="-1" aria-labelledby="import3" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to See/Access Course Content ?</h2>

                                                <p class="mt-10">1. Login with your credentials from <a href="/login" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Login</a> page.
                                                </p>
                                                 <p class="mt-10">2. On purchase section Find course and click on the <b>Learning Page</b> button
                                                </p>
                                                 <p class="mt-10 ">3. Here you can see all the available content from this  course in the right side
                                                </p>
                                                 <p class="mt-10">4. Click on the section and click on any <b>video or file</b> to open
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/DB6JfvY638o?si=aVWB-6FigO4m1VIc?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to See/Access Course Content ?</h2>

                                                <p class="mt-10">1. Login with your credentials from <a href="/login" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Login</a> page.
                                                </p>
                                                 <p class="mt-10">2. On purchase section Find course and click on the <b>Learning Page</b> button
                                                </p>
                                                 <p class="mt-10 ">3. Here you can see all the available content from this  course in the right side
                                                </p>
                                                 <p class="mt-10">4. Click on the section and click on any <b>video or file</b> to open
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/DB6JfvY638o?si=aVWB-6FigO4m1VIc?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import4" tabindex="-1" aria-labelledby="import4" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to View Quiz Results and Answers?</h2>

                                                <p class="mt-10">1. <p class="mt-10">1. Login with your credentials from <a href="/login" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Login</a> page.
                                                </p>
                                                 <p class="mt-10">2. Once logged in, find the <b>Purchase Course</b> section.
                                                </p>
                                                 <p class="mt-10 ">3. Navigate to the <b>Quizzes</b> tab.
                                                </p>
                                                 <p class="mt-10">4. Click on the <b>My Results</b> option.
                                                </p>
                                                 <p class="mt-10">5. Find the quiz you want to view the result and click on the <b>three-dot</b> icon.
                                                </p>
                                                <p class="mt-10">6. Click on <b>View Answers</b>.
                                                </p>
                                                <p class="mt-10">7. Click the <b>Next</b> button to browse through all the answers.
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/TRdIIYBnJ5w?si=2F2FK1vXOQ4BUpTu?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to View Quiz Results and Answers ?</h2>

                                                <p class="mt-10">1. <p class="mt-10">1. Login with your credentials from <a href="/login" target="_blank" class="font-14" style="font-weight: 800;color: #1c0e97;text-decoration-line: underline;-weight: 800;">Login</a> page.
                                                </p>
                                                 <p class="mt-10">2. Once logged in, find the <b>Purchase Course</b> section.
                                                </p>
                                                 <p class="mt-10 ">3. Navigate to the <b>Quizzes</b> tab.
                                                </p>
                                                 <p class="mt-10">4. Click on the <b>My Results</b> option.
                                                </p>
                                                 <p class="mt-10">5. Find the quiz you want to view the result and click on the <b>three-dot</b> icon.
                                                </p>
                                                <p class="mt-10">6. Click on <b>View Answers</b>.
                                                </p>
                                                <p class="mt-10">7. Click the <b>Next</b> button to browse through all the answers.
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/TRdIIYBnJ5w?si=2F2FK1vXOQ4BUpTu?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import5" tabindex="-1" aria-labelledby="import5" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to give a second attempt for Quiz ?</h2>

                                                <p class="mt-10">1. Login with your credentials on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click to the <b>Quizzes</b> tab.from dashboard
                                                </p>
                                                 <p class="mt-10 ">3. Click on the <b>My Results</b> option.
                                                </p>
                                                 <p class="mt-10">4. Find the quiz failed quiz you want to retry and click on the <b>three-dot</b> Option
                                                </p>
                                                 <p class="mt-10">5. Go through the quiz and answer each question as it appears.
                                                </p>
                                                <p class="mt-10">6. After answering the question, click <b>Next</b> button  to proceed.to the next question
                                                </p>
                                                <p class="mt-10">7. Once you  have Answered all the questions then click on the <b>Finish</b> button to submit your answers and complete the quiz.
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/6DOlFe5tJOA?si=eLUR_YhDPFVANTty?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to give a second attempt for Quiz ?</h2>

                                                <p class="mt-10">1. Login with your credentials on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click to the <b>Quizzes</b> tab.from dashboard
                                                </p>
                                                 <p class="mt-10 ">3. Click on the <b>My Results</b> option.
                                                </p>
                                                 <p class="mt-10">4. Find the quiz failed quiz you want to retry and click on the <b>three-dot</b> Option
                                                </p>
                                                 <p class="mt-10">5. Go through the quiz and answer each question as it appears.
                                                </p>
                                                <p class="mt-10">6. After answering the question, click <b>Next</b> button  to proceed.to the next question
                                                </p>
                                                <p class="mt-10">7. Once you  have Answered all the questions then click on the <b>Finish</b> button to submit your answers and complete the quiz.
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/6DOlFe5tJOA?si=eLUR_YhDPFVANTty?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import6" tabindex="-1" aria-labelledby="import6" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to set a meeting time for the instructor ?</h2>

                                                <p class="mt-10">1. Login with your credentials (Instructor)  on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click on the <b>meeting</b> tab from the left side from dashboard page
                                                </p>
                                                 <p class="mt-10 ">3.  Click on the <b>setting</b> option  available inside meeting tab
                                                </p>
                                                 <p class="mt-10">4. New screen shows seven days of week now click on the <b>three dot</b> option against any day
                                                </p>
                                                 <p class="mt-10">5. Select <b>slot</b> as per your availability with am and pm with <b>30 min slot only</b>
                                                </p>
                                                <p class="mt-10">6. Now select <b>online only</b> , enter the <b>description</b> and  click on the <b>save</b>
                                                </p>
                                                <p class="mt-10">7. After completing your availability click on the <b>save</b> button
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/9SRoTq4tOuc?si=wjd85QeemltfhOwY?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to set a meeting time for the instructor ?</h2>

                                                <p class="mt-10">1. Login with your credentials (Instructor)  on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click on the <b>meeting</b> tab from the left side from dashboard page
                                                </p>
                                                 <p class="mt-10 ">3.  Click on the <b>setting</b> option  available inside meeting tab
                                                </p>
                                                 <p class="mt-10">4. New screen shows seven days of week now click on the <b>three dot</b> option against any day
                                                </p>
                                                 <p class="mt-10">5. Select <b>slot</b> as per your availability with am and pm with <b>30 min slot only</b>
                                                </p>
                                                <p class="mt-10">6. Now select <b>online only</b> , enter the <b>description</b> and  click on the <b>save</b>
                                                </p>
                                                <p class="mt-10">7. After completing your availability click on the <b>save</b> button
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/9SRoTq4tOuc?si=wjd85QeemltfhOwY?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import7" tabindex="-1" aria-labelledby="import7" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to download a ppt file ?</h2>

                                                <p class="mt-10">1. Login with your credentials from login page
                                                </p>
                                                 <p class="mt-10">2. Find Courses in my purchase section
                                                </p>
                                                 <p class="mt-10 ">3. Click on the <b>learning</b> page button
                                                </p>
                                                 <p class="mt-10">4. On this page, you can see various course materials, including PPT files
                                                </p>
                                                 <p class="mt-10">5. now click on the <b>ppt files</b>
                                                </p>
                                                <p class="mt-10">6. Now click on the <b>download</b> button
                                                </p>
                                                <p class="mt-10">7. now click on the <b>save</b> button PPt  file will be saved in your system.
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/2lUt1uVtv_k?si=iEJJkhYuryMnLKsy?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to download a ppt file ?</h2>

                                                <p class="mt-10">1. Login with your credentials from login page
                                                </p>
                                                 <p class="mt-10">2. Find Courses in my purchase section
                                                </p>
                                                 <p class="mt-10 ">3. Click on the <b>learning</b> page button
                                                </p>
                                                 <p class="mt-10">4. On this page, you can see various course materials, including PPT files
                                                </p>
                                                 <p class="mt-10">5. now click on the <b>ppt files</b>
                                                </p>
                                                <p class="mt-10">6. Now click on the <b>download</b> button
                                                </p>
                                                <p class="mt-10">7. now click on the <b>save</b> button PPt  file will be saved in your system.
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/2lUt1uVtv_k?si=iEJJkhYuryMnLKsy?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import8" tabindex="-1" aria-labelledby="import8" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to Complete the Quiz in Waiting ?</h2>

                                                <p class="mt-10">1. Login with your credentials on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click to the <b>Quizzes</b> tab.from dashboard or My Purchase page
                                                </p>
                                                 <p class="mt-10 ">3. Click on the <b>My Results</b> option.
                                                </p>
                                                 <p class="mt-10">4. Find the quiz in waiting status
                                                </p>
                                                 <p class="mt-10">5. Now Click on the <b>three dot</b> option and and Click on the <b>try again</b> button
                                                </p>
                                                <p class="mt-10">6. Answer all the questions and Click on the <b>next</b> button
                                                </p>
                                                <p class="mt-10">7.  Once all question are answered click on the <b>finish</b> button
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/1Qo0AcrHUro?si=DyCOhbLiiKCnufLk?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to Complete the Quiz in Waiting ?</h2>

                                                <p class="mt-10">1. Login with your credentials on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click to the <b>Quizzes</b> tab.from dashboard or My Purchase page
                                                </p>
                                                 <p class="mt-10 ">3. Click on the <b>My Results</b> option.
                                                </p>
                                                 <p class="mt-10">4. Find the quiz in waiting status
                                                </p>
                                                 <p class="mt-10">5. Now Click on the <b>three dot</b> option and and Click on the <b>try again</b> button
                                                </p>
                                                <p class="mt-10">6. Answer all the questions and Click on the <b>next</b> button
                                                </p>
                                                <p class="mt-10">7.  Once all question are answered click on the <b>finish</b> button
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/1Qo0AcrHUro?si=DyCOhbLiiKCnufLk?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>
  <div class="modal fade" id="import9" tabindex="-1" aria-labelledby="import9" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="width:95%;max-width:95%;">

            <div class="container modal-content py-20" id="videolink" style="padding: 30px;">
                 <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25">X</i>
                </button>
            </div>
                 <ul class="col-12 nav nav-tabs p-15 d-flex align-items-center justify-content-between bg-secondary1 rounded-sm1" id="tabs-tab" role="tablist">
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;">
                                <a class="position-relative font-14  active" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/DesktopSelectorIcon.svg" alt="" role="presentation"><p>Desktop</p></a>
                            </li>
                            <li class="col-6 nav-item" style="display: flex;justify-content: center;" >
                                <a  class="position-relative font-14   " id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false" style="text-align: center;"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/MobileSelectorIcon.svg" alt="" role="presentation" ><p>Mobile</p></a>
                            </li>

                        </ul>

                        <div id="instructorsList" class=" mt-15">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">

                        <div class="row">
                             <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class=" font-weight-bold text-dark">How to attempt the Quiz  ?</h2>

                                                <p class="mt-10">1. Log in with your credentials on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click to the Quizzes tab from the dashboard.
                                                </p>
                                                 <p class="mt-10 ">3. Select Quiz Option.
                                                </p>

                                                 <p class="mt-10">4. Click on the Quiz button
                                                </p>
                                                 <p class="mt-10">5. Go through the quiz and answer each question as it appears.
                                                </p>
                                                <p class="mt-10">6. After answering the question, click Next button to proceed to the next question
                                                </p>
                                                <p class="mt-10">7. Once you have Answered all the questions then click on the Finish button to submit your answers and complete the quiz.
                                                </p>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/VJK9SxcNs1Y?si=wrcS4k62elYtGDBk?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>
                                          </div>

            </div>
               <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">

                                          <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                             <h2 class=" font-weight-bold text-dark">How to attempt the Quiz  ?</h2>

                                                <p class="mt-10">1. Log in with your credentials on the Login page (Skip if already login)
                                                </p>
                                                 <p class="mt-10">2. Click to the Quizzes tab from the dashboard.
                                                </p>
                                                 <p class="mt-10 ">3. Select Quiz Option.
                                                </p>
                                                 <p class="mt-10">4. Click on the Quiz button
                                                </p>
                                                 <p class="mt-10">5. Go through the quiz and answer each question as it appears.
                                                </p>
                                                <p class="mt-10">6. After answering the question, click Next button to proceed to the next question
                                                </p>
                                                <p class="mt-10">7. Once you have Answered all the questions then click on the Finish button to submit your answers and complete the quiz.
                                                </p>
                        </div>
                    </div>

                  <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <iframe width="-webkit-fill-available" id="videoiframe" width="100%" allow="autoplay" title="YouTube video player" frameborder="0" allowfullscreen="" src="https://www.youtube.com/embed/VJK9SxcNs1Y?si=wrcS4k62elYtGDBk?autoplay=1" style="width:100%; height:300px;"></iframe>
                    </div>
                </div>
            </section>

                            </div></div>
                              </div>

            </div>

            </div>
        </div>
    </div>

@endsection
