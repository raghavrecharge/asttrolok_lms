@extends(getTemplate().'.layouts.app') @push('styles_top')
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css" />
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.css" />
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-about.css">
<style>
    .rewardss {
        position: absolute;
        width: 100%;
        left: -24px !important;
        top: 8px !important;
    }
    
    .testimonials-card h4 {
    font-size: 35px !important;
    text-align: center;
}
/* .d-block {
    font-size: 17.992px !important;
} */
.testimonials-container .testimonials-card .testimonials-user-avatar {
    position: absolute;
    top: -23px;
    width: 80px;
    height: 80px;
}
.text-dark {
    color: #1f3b64 !important;
}
.homeshow {
    display: none;
}
@media (max-width: 991px)
{
    .mob-ban {
    display:block;
}
.testimonials-container {
    display: block;
}
.homeshow {
    display: block;
}
  
}
@media (max-width: 2021px)
{
.site-top-banner {
    /*height: 400px!important;*/
}
.swiper-slide{
    padding-left: 40px;
    padding-right: 40px;
}
.text-right , .text-center{
    text-align:left!important;
}

}
@media (max-width: 1421px)
{
.site-top-banner {
    /*height: 250px!important;*/
}


}
@media (max-width: 771px)
{
.site-top-banner {
    /*height: 250px!important;*/
}
.swiper-slide{
    padding-left: 0px!important;
    padding-right: 0px!important;
}
.testimonials-container .testimonials-card .testimonials-user-avatar {
    position: absolute;
    top: -41px;
    width: 80px;
    height: 80px;
}
.testimonials-card h4 {
    font-size: 28px !important;
    text-align: center;
}
}
</style>
@endpush @section('content')

<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/lottie/lottie-player.js"></script>

<section class="mobile-home-slider mobilehome mob-ban site-top-banner search-top-banner opacity-04 position-relative hide-mobile" style="height: 200px;">
        <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Frame 427322271.webp" class="banner-redius img-cover" alt="Instructors">

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 "style="text-align: center;">About Us</h1>
                        <!--<span class="course-count-badge py-5 px-10 text-white rounded">63 Instructors</span>-->

                        <!--<div class="search-input bg-white p-10 flex-grow-1">-->
                        <!--    <form action="/instructors" method="get">-->
                        <!--        <div class="form-group d-flex align-items-center m-0">-->
                        <!--            <input type="text" name="search" class="form-control border-0" value="" placeholder="Search Instructors"/>-->
                        <!--            <button type="submit" class="btn btn-primary rounded-pill">Search</button>-->
                        <!--        </div>-->
                        <!--    </form>-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </div>
    </section>
<section class="container" >
    <div class="row">
<div class="col-12 col-lg-12  mt-lg-0 mobilefirst">

    <section class="home-sections home-sections-swiper container position-relative mt-25">
        <div class="row align-items-center">
            <div class="col-12 col-lg-7 mt-20 mt-lg-0">
                <div class="">
                    <h2 class="font-36 font-weight-bold text-dark"> <span style="padding-bottom: 1px; border-bottom:2px solid #32ba7c;"> About </span> Us</h2>
                    <p class="font-14 font-weight-normal text-dark mt-10">
                        Asttrolok offers online courses in Astrology, Palmistry, Vastu, and more, founded by Alok Khandelwal, with a global community of over 50,000 students.
                    </p>
    
                    <!--<div class="mt-35 d-flex align-items-center">-->
                    <!--    <a href="/reward-courses" class="btn btn-primary mr-15">Rewards</a>-->
    
                    <!--    <a href="/panel/rewards" class="btn btn-outline-primary">Points Club</a>-->
                    <!--</div>-->
                </div>
            </div>
            <div class="col-12 col-lg-5  mt-20">
                <div class="position-relative reward-program-section-hero-card">
                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Section.webp"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>
    
                    <!--<div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-15 d-flex align-items-center">-->
                    <!--    <div class="example-reward-card-medal">-->
                    <!--        <img loading="lazy"  src="/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal" />-->
                    <!--    </div>-->
    
                    <!--    <div class="flex-grow-1 ml-15">-->
                    <!--        <span class="font-14 font-weight-bold text-secondary d-block">You earned 50 points!</span>-->
                    <!--        <span class="text-dark font-12 font-weight-500">for completing the course...</span>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>
            </div>
        </div>
    </section>

<section class="home-sections home-sections-swiper container position-relative mt-25">
    <div class="row align-items-center">
        <div class="col-12 col-lg-7  mt-lg-0">
            <div class="">
                <h2 class="font-36 font-weight-bold text-dark"><span style="padding-bottom: 1px; border-bottom:2px solid #32ba7c;">Who w</span>e are</h2>
                <p class="font-14 font-weight-normal text-dark mt-10">
                    Welcome to Asttrolok, a place where we explore ancient wisdom to help you find purpose and meaning in life. Join us on a journey to navigate life better with the guidance of this mystical knowledge.<br><br>
                    At Asttrolok, our motto is <span style="font-weight: 600; color:#32ba7c;">"LEARN ASTROLOGY ANYWHERE-EVERYWHERE"</span>
                </p>

                <!--<div class="mt-35 d-flex align-items-center">-->
                <!--    <a href="/reward-courses" class="btn btn-primary mr-15">Rewards</a>-->

                <!--    <a href="/panel/rewards" class="btn btn-outline-primary">Points Club</a>-->
                <!--</div>-->
            </div>
        </div>
        {{-- <div class="col-12 col-lg-5  mt-20">
            <div class="position-relative reward-program-section-hero-card">
                <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Section.webp"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

                <!--<div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-15 d-flex align-items-center">-->
                <!--    <div class="example-reward-card-medal">-->
                <!--        <img loading="lazy"  src="/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal" />-->
                <!--    </div>-->

                <!--    <div class="flex-grow-1 ml-15">-->
                <!--        <span class="font-14 font-weight-bold text-secondary d-block">You earned 50 points!</span>-->
                <!--        <span class="text-dark font-12 font-weight-500">for completing the course...</span>-->
                <!--    </div>-->
                <!--</div>-->
            </div>
        </div> --}}
    </div>
</section>



<div class="position-relative home-sections testimonials-container">
    <!--<div id="parallax1" class="ltr" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; position: relative; pointer-events: none;">-->
    <!--    <div-->
    <!--        data-depth="0.2"-->
    <!--        class="gradient-box left-gradient-box"-->
    <!--        style="transform: translate3d(-38.4px, -7.5px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;"-->
    <!--    ></div>-->
    <!--</div>-->

    <section class="container home-sections home-sections-swiper hide-mobile">
        <!--<div class="text-center">-->
        <!--    <h2 class="section-title">Testimonials</h2>-->
        <!--    <p class="section-hint">#What our customers say about us</p>-->
        <!--</div>-->

        <div class="position-relative">
            <div class="swiper-container testimonials-swiper px-12 swiper-container-initialized swiper-container-horizontal">
                <div class="row mx-5" style="transform: translate3d(0px, 0px, 0px); transition: all 0ms ease 0s;">
                    <div class="d-flex col-6 col-md-12 col-lg-3 swiper-slide swiper-slide-active p-5" >
                        <div class="position-relative p-10 py-lg-30  px-lg-20 rounded-sm shadow bg-white text-center" style="width: 90%;">
                            <div class="d-flex align-items-center">
                                <div class="image-icon">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Students.svg" alt="Revathi Shridhar" class="img-cover  " />
                                </div>
                                <div class=" ml-15">
                                <h4 class="font-16 font-weight-bold text-secondary">25000+</h4>
                                <span class="d-block font-14 text-dark">Students</span>
                                </div>
                                
                            </div>
                            
                            
                        </div>
                        <div class="bottom-gradient"></div>
                    </div>
                    <div class="d-flex col-6 col-md-12 col-lg-3 swiper-slide swiper-slide-active p-5" >
                        <div class="position-relative p-10 py-lg-30  px-lg-20 rounded-sm shadow bg-white text-center" style="width: 90%;">
                            <div class="d-flex align-items-center">
                                <div class="image-icon">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Consultation.svg" alt="Revathi Shridhar" class="img-cover  " />
                                </div>
                                <div class="ml-15">
                                <h4 class="font-16 font-weight-bold text-secondary">50000+</h4>
                                <span class="d-block font-14 text-dark">Consultations</span>
                                </div>
                                
                            </div>
                            
                            
                        </div>
                        <div class="bottom-gradient"></div>
                    </div>
                    <div class="d-flex col-6 col-md-12 col-lg-3 swiper-slide swiper-slide-active p-5" >
                        <div class="position-relative p-10 py-lg-30  px-lg-20 rounded-sm shadow bg-white text-center" style="width: 90%;">
                            <div class="d-flex align-items-center">
                                <div class="image-icon">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Countries.svg" alt="Revathi Shridhar" class="img-cover  " />
                                </div>
                                <div class=" ml-15">
                                <h4 class="font-16 font-weight-bold text-secondary">50+</h4>
                                <span class="d-block font-14 text-dark">Countries</span>
                                </div>
                                
                            </div>
                            
                            
                        </div>
                        <div class="bottom-gradient"></div>
                    </div>
                    <div class="d-flex col-6 col-md-12 col-lg-3 swiper-slide swiper-slide-active p-5" >
                        <div class="position-relative p-10 py-lg-30  px-lg-20 rounded-sm shadow bg-white text-center" style="width: 90%;">
                            <div class="d-flex align-items-center">
                                <div class="image-icon">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Pannel Astrologer.svg" alt="Revathi Shridhar" class="img-cover  " />
                                </div>
                                <div class=" ml-10">
                                <h4 class="font-16 font-weight-bold text-secondary">100+</h4>
                                <span class="d-block font-14 text-dark">Astrologers</span>
                                </div>
                                
                            </div>
                            
                            
                        </div>
                        <div class="bottom-gradient"></div>
                    </div>
                   
                    
                </div>

                
            </div>

            
        </div>
    </section>
    <section class="container home-sections home-sections-swiper homehide">
        <!--<div class="text-center">-->
        <!--    <h2 class="section-title">Testimonials</h2>-->
        <!--    <p class="section-hint">#What our customers say about us</p>-->
        <!--</div>-->

        <div class="position-relative">
            <div class="swiper-container testimonials-swiper px-12 swiper-container-initialized swiper-container-horizontal">
                <div class="row" style="transform: translate3d(0px, 0px, 0px); transition: all 0ms ease 0s;">
                    <div class="col-md-12 col-lg-3 swiper-slide swiper-slide-active" >
                        <div class="testimonials-card position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm shadow bg-white text-center">
                            <div class="d-flex flex-column align-items-center">
                                <div class="testimonials-user-avatar">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Students.svg" alt="Revathi Shridhar" class="img-cover  " />
                                </div>
                                <h4 class="font-16 font-weight-bold text-secondary mt-30">25000+</h4>
                                <span class="d-block font-14 text-dark">Students</span>
                                
                            </div>
                            
                            <div class="bottom-gradient"></div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-3 swiper-slide swiper-slide-next" >
                        <div class="testimonials-card position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm shadow bg-white text-center">
                            <div class="d-flex flex-column align-items-center">
                                <div class="testimonials-user-avatar">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Consultation.svg" alt="Swagat Chakraborty" class="img-cover  " />
                                </div>
                                <h4 class="font-16 font-weight-bold text-secondary mt-30">50000+</h4>
                                <span class="d-block font-14 text-dark">Consultations</span>
                                
                            </div>
                            <!--<div class="mt-25 testimonials-p scrollbar-width-thin">-->
                            <!--    <p class="text-dark font-14 pr-5">-->
                            <!--        Finding a true Guru in Astrology was my desire, fulfilled by meeting Alok Khandelwal ji of Asttrolok. His vast knowledge and simple teaching style are excellent. I highly recommend Astrolok and Alok-->
                            <!--        Khandelwal ji for learning authentic Astrology.-->
                            <!--    </p>-->
                            <!--</div>-->
                            <div class="bottom-gradient"></div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-3 swiper-slide" >
                        <div class="testimonials-card position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm shadow bg-white text-center">
                            <div class="d-flex flex-column align-items-center">
                                <div class="testimonials-user-avatar">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Countries.svg" alt="Dipak Samtani" class="img-cover  " />
                                </div>
                                <h4 class="font-16 font-weight-bold text-secondary mt-30">50+</h4>
                                <span class="d-block font-14 text-dark">Countries</span>
                               
                            </div>
                            <!--<div class="mt-25 testimonials-p scrollbar-width-thin">-->
                            <!--    <p class="text-dark font-14 pr-5">-->
                            <!--        Learning astrology in Asttrolok with Alok sir is a great experience. His simple explanation method enhances understanding. My knowledge expanded under his guidance. Grateful to Astrolok Team and Alok sir.-->
                            <!--    </p>-->
                            <!--</div>-->
                            <div class="bottom-gradient"></div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-3 swiper-slide" >
                        <div class="testimonials-card position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm shadow bg-white text-center">
                            <div class="d-flex flex-column align-items-center">
                                <div class="testimonials-user-avatar">
                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Pannel Astrologer.svg" alt="Dipak Samtani" class="img-cover  " />
                                </div>
                                <h4 class="font-16 font-weight-bold text-secondary mt-30">100+</h4>
                                <span class="d-block font-14 text-dark">Astrologers</span>
                               
                            </div>
                            <!--<div class="mt-25 testimonials-p scrollbar-width-thin">-->
                            <!--    <p class="text-dark font-14 pr-5">-->
                            <!--        Learning astrology in Asttrolok with Alok sir is a great experience. His simple explanation method enhances understanding. My knowledge expanded under his guidance. Grateful to Astrolok Team and Alok sir.-->
                            <!--    </p>-->
                            <!--</div>-->
                            <div class="bottom-gradient"></div>
                        </div>
                    </div>
                    
                </div>

                
            </div>

            
        </div>
    </section>

    <!--<div id="parallax2" class="ltr" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; position: relative; pointer-events: none;">-->
    <!--    <div-->
    <!--        data-depth="0.4"-->
    <!--        class="gradient-box right-gradient-box"-->
    <!--        style="transform: translate3d(-76.8px, 17.1px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;"-->
    <!--    ></div>-->
    <!--</div>-->

    <!--<div id="parallax3" class="ltr" style="transform: translate3d(0px, 0px, 0px) rotate(0.0001deg); transform-style: preserve-3d; backface-visibility: hidden; position: relative; pointer-events: none;">-->
    <!--    <div-->
    <!--        data-depth="0.8"-->
    <!--        class="gradient-box bottom-gradient-box"-->
    <!--        style="transform: translate3d(-153.5px, 34.2px, 0px); transform-style: preserve-3d; backface-visibility: hidden; position: relative; display: block; left: 0px; top: 0px;"-->
    <!--    ></div>-->
    <!--</div>-->
</div>



<section class="home-sections home-sections-swiper container  position-relative mt-25">
    <div class="row align-items-center">
        <div class="col-12 col-lg-12 mt-20 mt-lg-0">
            <div class="text-center">
                <h2 class="font-36 font-weight-bold text-dark"><span style="padding-bottom: 1px; border-bottom:2px solid #32ba7c;">What</span> We Do</h2>
                <p class="font-14 font-weight-normal text-dark mt-10">
                    
Asttrolok is one of the biggest online institutes offering courses in Astrology, Palmistry, Vastu, Yoga, Ayurveda, Numerology, and Scriptures. These special courses aim to make invaluable cultural and traditional knowledge accessible to people worldwide.
<br><br>Our online platform ensures that even those with hectic lifestyles can pursue their passion for learning. With our commitment to accessibility and quality education, we aim to share this rich heritage with enthusiasts across the globe. 
<br><br><span class="font-16" style="font-weight: 600; color:#32ba7c;">Join us and unlock the treasures of ancient wisdom from wherever you are.</span>
                </p>

                <!--<div class="mt-35 d-flex align-items-center">-->
                <!--    <a href="/reward-courses" class="btn btn-primary mr-15">Rewards</a>-->

                <!--    <a href="/panel/rewards" class="btn btn-outline-primary">Points Club</a>-->
                <!--</div>-->
            </div>
        </div>
        <!--<div class="col-12 col-lg-4">-->
        <!--    <div class="position-relative reward-program-section-hero-card">-->
        <!--        <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Section.png"  class="reward-program-section-hero" alt="Win Club Points" />-->

        <!--        <div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-15 d-flex align-items-center">-->
        <!--            <div class="example-reward-card-medal">-->
        <!--                <img loading="lazy"  src="/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal" />-->
        <!--            </div>-->

        <!--            <div class="flex-grow-1 ml-15">-->
        <!--                <span class="font-14 font-weight-bold text-secondary d-block">You earned 50 points!</span>-->
        <!--                <span class="text-dark font-12 font-weight-500">for completing the course...</span>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
    </div>
</section>

<section class="home-sections home-sections-swiper container  position-relative mt-25">
    <div class="row align-items-center ">
        <div class="col-12 col-lg-4">
            <div class="position-relative reward-program-section-hero-card">
                <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/Alok ssir 2.webp"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

                <!--<div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-15 d-flex align-items-center">-->
                <!--    <div class="example-reward-card-medal">-->
                <!--        <img loading="lazy"  src="/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal" />-->
                <!--    </div>-->

                <!--    <div class="flex-grow-1 ml-15">-->
                <!--        <span class="font-14 font-weight-bold text-secondary d-block">You earned 50 points!</span>-->
                <!--        <span class="text-dark font-12 font-weight-500">for completing the course...</span>-->
                <!--    </div>-->
                <!--</div>-->
            </div>
        </div>
        <div class="col-12 col-lg-8 mt-20 mt-lg-0">
            <div class="">
                <h2 class="font-36 font-weight-bold text-dark"><span style="padding-bottom: 1px; border-bottom:2px solid #32ba7c;">About</span> Founder</h2>
                <p class="font-14 font-weight-normal text-dark mt-10">
                    
Mr. Alok Khandelwal, the founder of Asttrolok, holds esteemed degrees in astrology, including Jyotish Bhushan, JyotishRatna, and Jyotish Rishi. His expertise extends globally, attracting learners and clients from over 50 countries who seek his consultations and guidance. 
<br><br>As the visionary force behind Asttrolok, Mr. Khandelwal with an experience of 25+ years is dedicated to cultivating proficient astrologers to provide accurate guidance worldwide. His primary goal is to empower individuals to become skilled practitioners, ensuring people globally receive reliable and insightful astrological guidance.
<br><br>His teachings resonate globally, offering profound insights to individuals from diverse cultural backgrounds. His international seminars have contributed to Asttrolok's widespread influence, showcasing the impact of his expertise and qualifications in astrology.
</p>

                <!--<div class="mt-35 d-flex align-items-center">-->
                <!--    <a href="/reward-courses" class="btn btn-primary mr-15">Rewards</a>-->

                <!--    <a href="/panel/rewards" class="btn btn-outline-primary">Points Club</a>-->
                <!--</div>-->
            </div>
        </div>
        
    </div>
</section>


<section class=" home-sections home-sections-swiper container position-relative mt-25 homeshow">
    <div class="row align-items-center">
        
        <div class="col-12 col-lg-4">
            <div class="position-relative reward-program-section-hero-card">
                <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/composition-with-question-mark-nature-landscape 1.webp"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

                <!--<div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-15 d-flex align-items-center">-->
                <!--    <div class="example-reward-card-medal">-->
                <!--        <img loading="lazy"  src="/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal" />-->
                <!--    </div>-->

                <!--    <div class="flex-grow-1 ml-15">-->
                <!--        <span class="font-14 font-weight-bold text-secondary d-block">You earned 50 points!</span>-->
                <!--        <span class="text-dark font-12 font-weight-500">for completing the course...</span>-->
                <!--    </div>-->
                <!--</div>-->
            </div>
        </div>
        <div class="col-12 col-lg-8 mt-20 mt-lg-0">
            
            <div class="text-right">
                <h2 class="font-36 font-weight-bold text-dark"><span style="padding-bottom: 1px; border-bottom:2px solid #32ba7c;">Why A</span>sttrolok</h2>
                <p class="font-14 font-weight-normal text-dark mt-10">
                    Asttrolok thrives today with a vibrant community spanning over 50 countries and a growing family of 50,000+ students worldwide. We understand the importance of accuracy in predictions. That's why we prioritize comprehensive training for our students, empowering them to offer precise solutions to those seeking guidance.
<br><br>Our commitment goes beyond accuracy. Respecting your privacy is a core value. We aim to build trust, ensuring that your journey with us remains confidential and trustworthy. So, when you talk about us, you'll certainly do so with a nod of satisfaction and assurance!
</p>

                <!--<div class="mt-35 d-flex align-items-center">-->
                <!--    <a href="/reward-courses" class="btn btn-primary mr-15">Rewards</a>-->

                <!--    <a href="/panel/rewards" class="btn btn-outline-primary">Points Club</a>-->
                <!--</div>-->
            </div>
            
        </div>
    </div>
</section>

<section class="home-sections home-sections-swiper container position-relative mt-25 homehide">
    <div class="row align-items-center">
        <div class="col-12 col-lg-8 mt-20 mt-lg-0">
            
            <div class="text-right">
                <h2 class="font-36 font-weight-bold text-dark">Why Asttrolok</h2>
                <p class="font-16 font-weight-normal text-dark mt-10">
                    Asttrolok thrives today with a vibrant community spanning over 50 countries and a growing family of 50,000+ students worldwide. We understand the importance of accuracy in predictions. That's why we prioritize comprehensive training for our students, empowering them to offer precise solutions to those seeking guidance.
<br><br>Our commitment goes beyond accuracy. Respecting your privacy is a core value. We aim to build trust, ensuring that your journey with us remains confidential and trustworthy. So, when you talk about us, you'll certainly do so with a nod of satisfaction and assurance!
</p>

                <!--<div class="mt-35 d-flex align-items-center">-->
                <!--    <a href="/reward-courses" class="btn btn-primary mr-15">Rewards</a>-->

                <!--    <a href="/panel/rewards" class="btn btn-outline-primary">Points Club</a>-->
                <!--</div>-->
            </div>
            
        </div>
        <div class="col-12 col-lg-4">
            <div class="position-relative reward-program-section-hero-card">
                <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}/store/1/about/composition-with-question-mark-nature-landscape 1.webp"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

                <!--<div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-15 d-flex align-items-center">-->
                <!--    <div class="example-reward-card-medal">-->
                <!--        <img loading="lazy"  src="/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal" />-->
                <!--    </div>-->

                <!--    <div class="flex-grow-1 ml-15">-->
                <!--        <span class="font-14 font-weight-bold text-secondary d-block">You earned 50 points!</span>-->
                <!--        <span class="text-dark font-12 font-weight-500">for completing the course...</span>-->
                <!--    </div>-->
                <!--</div>-->
            </div>
        </div>
    </div>
</section>



</div>
</div>
</section>


@endsection @push('scripts_bottom')

<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/parallax/parallax.min.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/home.min.js"></script>
<script  >
    function featurjquery(urls) {
        window.location.href = urls;
    }
</script>
@endpush
