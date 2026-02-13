@extends('web.default2'.'.layouts.app') @push('styles_top')
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css" />
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.css" />

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
.d-block {
    font-size: 17.992px !important;
}
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

}

}
@media (max-width: 771px)
{
.site-top-banner {

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
<style>

    .highlight_review {
        margin-top: 70px;
        border-radius: 20px;
        background: #f8f8f8;
        padding: 5px 2px 0px 10px;
    }
    .highlight_review .review {
        background: #fff;
        color: #b79d74;
        border-radius: 10px;
        padding: 8px 10px;
        display: inline-block;
    }
    .review_img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin-top: 20px;
        margin-bottom: 10px;
        display: block;
        margin: 20px auto 0;
    }
    .review_name {
        color: #566890;
        font-size: 18px;
        font-family: "Poppins", Sans-serif;
        font-weight: 700;
    }
    .review_location {
        color: #2e2e2e;
        font-size: 16px;
        font-family: "Poppins", Sans-serif;
        margin-bottom: 10px;
    }
    .review_detail {
        color: #2e2e2e;
        font-size: 16px;
        font-family: "Poppins", Sans-serif;
        margin-top: 15px;
        margin-bottom: 15px;
        line-height: 25px;
    }
    .people_block {
        padding-left: 20px;
    }
    img.person {
        width: 100px;
        height: 100px;
        float: left;
        margin-right: 10px;
        border-radius: 50%;
    }
    .people_block .name_star {
        color: #566890;
        font-size: 17px;
        font-family: "Poppins", Sans-serif;
        font-weight: 700;
    }
    .people_block .name {
        color: #566890;
        font-size: 18px;
        font-family: "Poppins", Sans-serif;
        font-weight: 700;
    }
    .people_block .place {
        color: #2e2e2e;
        font-size: 16px;
        font-family: "Poppins", Sans-serif;
    }
    .detail.show-read-more {
        color: #2e2e2e;
        font-size: 16px;
        font-family: "Poppins", Sans-serif;
        margin-bottom: 30px;
        padding-left: 20px;
    }
    .px-md-32 img {
        padding-right: 1rem !important;
        padding-left: 1rem !important;
    }
    .heading_who {
        color: #146EA1;
        font-size: 18px;
        margin-top: 20px;
        margin-bottom: 10px;
    }
    .pl-md-3, .px-md-3 {
        padding-left: 20px !important;
    }
 </style>

@endpush @section('content')

<script src="{{ config('app.js_css_url') }}/assets/default/vendors/lottie/lottie-player.js"></script>

<section class="mob-ban site-top-banner search-top-banner opacity-04 position-relative " style="height: 421px;background: url('https://storage.googleapis.com/astrolok/store/1/Courses/Cover/Background.jpg');background-repeat: no-repeat;background-size: cover;">

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-9">
                    <div class="top-search-categories-form"  style="text-align: center;line-height: 3;">
                        <h1 class=" font-30" style="text-align: center;font-size: 50px;">Learn Astrology the Right Way</h1>
                        <p  class="py-10 px-10 "><span>Structured courses, authentic knowledge, and the personal guidance of Alok Khandelwal.</span></p>

<a href="/contact" class="mt-10 btn btn-primary rounded-pill px-50 py-30" style="font-size: 25px;font-weight: 600;">Talk to Our Team</a>

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
                <h2 class="font-36 font-weight-bold text-dark">About Asttrolok</h2>
                <p class="font-16 font-weight-normal text-dark mt-10">
                    Asttrolok is <b>India’s leading institute</b> for learning Vedic Astrology. With thousands of students worldwide, we are dedicated to presenting astrology as a structured science, taught with clarity and depth.
                </p>

            </div>
        </div>
        <div class="col-12 col-lg-5  mt-20">
            <div class="position-relative reward-program-section-hero-card">
                <img src="{{ config('app.img_dynamic_url') }}/store/1/about/Section.png"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

            </div>
        </div>
    </div>
</section>

<div class="position-relative home-sections testimonials-container">

</div>

<section class="home-sections home-sections-swiper container  position-relative mt-25">
    <div class="row align-items-center ">
        <div class="col-12 col-lg-4">
            <div class="position-relative reward-program-section-hero-card">
                <img src="{{ config('app.img_dynamic_url') }}/store/1/about/Alok ssir 2.png"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

            </div>
        </div>
        <div class="col-12 col-lg-8 mt-20 mt-lg-0">
            <div class="">
                <h2 class="font-36 font-weight-bold text-dark">Learn From a World-Renowned Mentor</h2>
                <p class="font-16 font-weight-normal text-dark mt-10">
                    <b class="font-20 font-weight-bold text-dark">Alok Khandelwal – Mentor & Co-Founder</b><br><br>

                <ul class="ml-15">
                  <li style="list-style: disc;padding-top: 10px;">25+ years of experience in Vedic Astrology.</li>
                  <li style="list-style: disc;padding-top: 10px;">Guided over 25,000 people through consultations.</li>
                  <li style="list-style: disc;padding-top: 10px;">Has taught astrology students in 40+ countries.</li>
                  <li style="list-style: disc;padding-top: 10px;">Co-founder of Asttrolok, with a vision to make astrology simple, practical, and myth-free.</li>
                  <li style="list-style: disc;padding-top: 10px;">Renowned for his ability to simplify complex concepts for beginners and professionals alike.</li>
                </ul>
                </p>
                <a href="/contact" class="mt-10 btn btn-primary rounded-pill" style="font-size: 16px;font-weight: 600;">Talk to Our Team</a>

            </div>
        </div>

    </div>
</section>

<section class="home-sections home-sections-swiper container mt-25">

    <div class="row" style="margin-bottom: 5%;">
        <div class="col-lg-12 pt-4 main_video pe-0 pe-sm-5 d-flex align-content-end" >
            <h2 class="font-36 font-weight-bold text-dark">Student Stories</h2>
        </div>
        <div class="col-lg-12 pt-4 main_video pe-0 pe-sm-5 d-flex align-content-end" style="margin-bottom: 5%;">
            <div class="col-lg-12 video_container top_video p-0 me-0 me-sm-5" id="thumb_0">
                <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:800px; height:450px;" src="https://www.youtube.com/embed/7fCFk8leidM?si=ncBQnaUKr9ru9lLG" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen="" data-gtm-yt-inspected-8="true"></iframe>
                <br>
            </div>
        </div>

        <div class="col-sn-12 col-nd-5 col-lg-4" style="margin-top:5%;">
            <center>
                <div class="highlight_review_desktop">
                    <div class="highlight_review text-left mt-0">
                        <div class="review">Highlighted review</div>
                        <img class="review_img" src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/review_images/05-min.jpg" alt="People">
                        <div class="review_name">Kartik Pathak</div>
                        <div class="review_location">Nagpur </div>
                        <div>
                            <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                            <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                            <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                            <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                            <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                        </div>
                        <div class="review_detail show-read-more">
                           I am a 25-year-old guy who has learned Jyotish from Alok Khandelwal Sir to break the stereotype thinking that it is only for old aged people.
                       Astrology is the first step of enlightenment. I think Alok Sir has made a group of people who are devoted to god and it's called Asttrolok.
                             </div>
                    </div>
                </div>
            </center>
        </div>
        <div class="col-sn-12 col-nd-7 col-lg-8">
            <div id="one" class="testimonial active">
                <div class="people_block">
                    <img class="person" src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/review_images/02-min.jpg" alt="People" width="100" height="100">
                    <div class="name_star">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                    </div>
                    <div class="name">Vikas Gupta</div>
                    <div class="place"> Indore</div>
                </div><br><br>
                <div class="detail show-read-more">
                    I know Asttrolok since the day it was formed. For me Asttrolok is not an institute or a medium of astrology, <a class="show_hide" data-content="toggle-text" style="color: #244A82 !important;    font-weight: 600;">Read More</a>
                    <div class="testinomial-content-more" id="more-data" style="display: none;">
                        its a medium to live a life for me. My Asttrolok’s journey is very delightful and memorable.</div>
                </div>
                <div class="people_block">
                    <img class="person" src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/review_images/03-min.jpg" alt="People" width="100" height="100">
                    <div class="name_star">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                    </div>
                    <div class="name">Ritu Dixit</div>
                    <div class="place"> Delhi </div>
                </div><br><br>
                <div class="detail show-read-more">
                    I joined Asttrolok last year. After coming to Asttrolok, I realized how important the guidance of a guru is in life. Ever since I joined this community, <a class="show_hide" data-content="toggle-text" style="color: #244A82 !important;    font-weight: 600;">Read More</a>
                    <div class="testinomial-content-more" id="more-data" style="display: none;">I have come to know how astrology can change your life. I also came to know that astrology is such a thing that if you know astrology, it has the power to change your whole life.</div>

                </div>
                <div class="people_block">
                    <img class="person" src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/review_images/04-min.jpg" alt="People" width="100" height="100">
                    <div class="name_star">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                    </div>
                    <div class="name">Neha Gupta</div>
                    <div class="place"> Faridabad </div>
                </div><br><br>
                <div class="detail show-read-more">
                    I am a student of Astro Shiromani 2022 in Asttrolok. I started my journey in Vedic science from here. And I've learned how to live life in a new way from here.

                </div>
                 <div class="people_block">
                    <img class="person" src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/review_images/01-min.jpg" alt="People" width="100" height="100">
                    <div class="name_star">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                        <img src="https://asttrolok.in/public/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                    </div>

                    <div class="review_name">Aarti Puri</div>
                    <div class="review_location"> Mumbai </div>
                </div><br>
                <div class="review_detail show-read-more">
                    It is a life changing experience with Asttrolok. Before I joined Asttrolok, I was a non-believer and I had many questions about Astrology, but when I joined Asttrolok, <a class="show_hide" data-content="toggle-text" style="color: #244A82 !important;    font-weight: 600;">Read More</a>
                    <div class="testinomial-content-more" id="more-data" style="display: none;">It turned me into a believer when I came to know about Astrology and how it can change lives.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-sections home-sections-swiper container position-relative mt-25 homeshow">
    <div class="row align-items-center">

        <div class="col-12 col-lg-4">
            <div class="position-relative reward-program-section-hero-card">
                <img src="{{ config('app.img_dynamic_url') }}/store/1/about/composition-with-question-mark-nature-landscape 1.png"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

            </div>
        </div>
        <div class="col-12 col-lg-8 mt-20 mt-lg-0">

            <div class="text-right">
                <h2 class="font-36 font-weight-bold text-dark">Why Choose Asttrolok?</h2>
                <p class="font-16 font-weight-normal text-dark mt-10">
                    <ul class="ml-15">
                      <li style="list-style: disc;padding-top: 10px;">Courses designed and mentored by Alok Ji.</li>
                      <li style="list-style: disc;padding-top: 10px;">Trusted by thousands of learners across the globe.</li>
                      <li style="list-style: disc;padding-top: 10px;">A supportive community of astrology enthusiasts.</li>
                      <li style="list-style: disc;padding-top: 10px;">Blend of tradition and modern teaching methods.</li>
                    </ul>
                    </p>

            </div>

        </div>
    </div>
</section>

<section class="home-sections home-sections-swiper container position-relative mt-25 homehide">
    <div class="row align-items-center">
        <div class="col-12 col-lg-8 mt-20 mt-lg-0">

            <div class="text-right">
                <h2 class="font-36 font-weight-bold text-dark">Why Choose Asttrolok?</h2>
                <p class="font-16 font-weight-normal text-dark mt-10">
                    <ul class="ml-15">
                      <li style="list-style: disc;padding-top: 10px;">Courses designed and mentored by Alok Ji.</li>
                      <li style="list-style: disc;padding-top: 10px;">Trusted by thousands of learners across the globe.</li>
                      <li style="list-style: disc;padding-top: 10px;">A supportive community of astrology enthusiasts.</li>
                      <li style="list-style: disc;padding-top: 10px;">Blend of tradition and modern teaching methods.</li>
                    </ul>
                </p>

            </div>

        </div>
        <div class="col-12 col-lg-4">
            <div class="position-relative reward-program-section-hero-card">
                <img src="{{ config('app.img_dynamic_url') }}/store/1/about/composition-with-question-mark-nature-landscape 1.png"  class="reward-program-section-hero" alt="Win Club Points" style="width: -webkit-fill-available;"/>

            </div>
        </div>
    </div>
</section>

<section class="mob-ban site-top-banner search-top-banner opacity-04 position-relative mt-50" style="height: 250px;background: #e8f3fa;border-radius: 15px;">

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-9">
                    <div class="top-search-categories-form"  style="text-align: center;line-height: 3;">
                        <h2 class=" font-30" style="text-align: center;font-size: 35px;">Start Your Astrology Journey With Alok Ji</h2>
                        <p  class="py-10 px-10 "><span>Whether you’re a beginner or looking to deepen your practice, Asttrolok offers the right path for you.</span></p>

                                    <a href="/contact" class="mt-10 btn btn-primary rounded-pill px-50 py-30" style="font-size: 25px;font-weight: 600;">Talk to Our Team</a>

                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
</div>
</section>

@endsection @push('scripts_bottom')

<script src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
<script src="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
<script src="{{ config('app.js_css_url') }}/assets/default/vendors/parallax/parallax.min.js"></script>
<script src="{{ config('app.js_css_url') }}/assets/default/js/parts/home.min.js"></script>
<script>
    function featurjquery(urls) {
        window.location.href = urls;
    }
</script>
@endpush
