@php
    $learningMaterialsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','learning_materials') : null;
    $companyLogosExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','company_logos') : null;
    $requirementsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','requirements') : null;
@endphp

@if(!empty($installments) and count($installments) and getInstallmentsSettings('installment_plans_position') == 'top_of_page')
    @foreach($installments as $installmentRow)
        @include('web.default2.installment.card',['installment' => $installmentRow, 'itemPrice' => $course->getPrice(), 'itemId' => $course->id, 'itemType' => 'course'])
    @endforeach
@endif

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

@if($course->description)
    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('product.Webinar_description') }}</h2>
        <div class="mt-15 course-description">
            {!! $course->description !!}
        </div>
    </div>
@endif

@if(!empty($learningMaterialsExtraDescription) and count($learningMaterialsExtraDescription))
    <div class="mt-40">
        <h2 class="section-title after-line">What You will get?</h2>

        @foreach($learningMaterialsExtraDescription as $learningMaterial)

                 <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">

                    <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                        <img src="{{ config('app.img_dynamic_url') }}{{ $learningMaterial->img }}" alt="What is social media?" class="img-cover">
                    </div>

                    <div class="ml-15">

                            <h4 class="font-16 font-weight-bold text-dark">{{ $learningMaterial->value }}</h4>

                        <p class="font-16 text-gray">{{ $learningMaterial->description }}</p>

                    </div>
                </div>

        @endforeach
    </div>
@endif
@if($course->id != 2107)
    <div class="mt-40">
        <h2 class="section-title after-line">What all you will learn?</h2>
        @include('web.default2.course.tabs.content')
    </div>
@endif

    <div class="mt-30">
        <h2 class="section-title after-line">How this Course can benefit you?</h2>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Career-Advancement-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Career Options</h4>
                <p class="font-16 text-gray">Explore new opportunities as a professional astrologer, consultant, or teacher.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Personal-Fulfilment-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Income Potential</h4>
                <p class="font-16 text-gray">Use your specialized knowledge to offer astrology services and increase your earning potential.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Credibility-and-Trust-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Professional Credibility</h4>
                <p class="font-16 text-gray">Get certification from a reputable Vedic institute of the country getting trained by a renowned astrologer, building trust and credibility.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Enhanced-Skill-Set-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Skill Advancement</h4>
                <p class="font-16 text-gray">Learn chart interpretation, prediction techniques, and analysis, becoming a sought-after astrologer.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Professional-Networking-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Professional Networking</h4>
                <p class="font-16 text-gray">Connect with industry professionals, fellow astrologers, and mentors, expanding your professional network.</p>
            </div>
        </div>
    </div>

    @if($course->teacher->id == 1015)
    <div class="mt-30">
        <h2 class="section-title after-line">Meet your Mentor</h2>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 250px; min-width: 250px; ">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Alok-sir-min.webp" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-50">
                <h4 class="font-20 font-weight-bold text-dark">Mr. Alok Khandelwal</h4><br>
                <p class="font-16 text-gray">Mr. Alok Khandelwal is a renowned astrologer with a global presence, spreading Vedic knowledge to students across countries like Russia through professional translators. His passion for teaching and public speaking takes him worldwide, where he conducts consultations and delivers insightful talks on Vedic science through his project Unwinding.
</p><br>
                <p class="font-16 text-gray">With over 50,000 students and growing, he is celebrated for his deep expertise in Ancient Vedic Astrology and his ability to offer practical solutions to personal and professional challenges. A member of the Art of Living foundation, Mr. Khandelwal is also a sought-after guest speaker at leading institutions across India.</p><br>

            </div>
        </div>
    </div>
    @endif

     <div class="mt-40">
        <h2 class="section-title after-line">About Asttrolok</h2>
        <div class="mt-15 course-description">

            <div>Asttrolok, founded in 2016, stands as one of the top three reputable online Vedic institutes in the country, dedicated to dispelling misconceptions and championing fact-based knowledge of Vedic Science in the fields of Astrology, Numerology, Palmistry, Yoga, Ayurveda & Scriptures. With students hailing from over 70+ countries, including professionals like lawyers, doctors, IITians, and actors, Asttrolok boasts a diverse and esteemed student body.</div>

            <div class="mt-20">The institute's reputation is further enhanced by its association with the Founder, Renowned Astrologer & Trainer Mr. Alok Khandelwal & 50+ other mentors & panelists, who all bring their extensive expertise and experience to the teaching. Asttrolok's commitment to protecting & spreading the knowledge that liberates & transforms solidifies its standing as a leading institution in the realm of Vedic astrology.</div>
        </div>
    </div>

     <div class="mt-40">
        <h2 class="section-title after-line">People are saying</h2>
        <div class="mt-15 course-description">

            <div><b>35,000+ people have already completed the Most Popular Astrology Course</b></div>

            <div class="row">
                <div class="col-lg-12 pt-4 main_video pe-0 pe-sm-5 d-flex align-content-end" style="margin-bottom: 5%;">
                    <div class="col-lg-12 video_container top_video p-0 me-0 me-sm-5" id="thumb_0">
                        <iframe style="border-radius: 30px; cursor:pointer; text-align: center; width:100%; height:400px;" src="https://www.youtube.com/embed/7fCFk8leidM?si=ncBQnaUKr9ru9lLG" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen=""></iframe>
                        <br>
                    </div>
                </div>
                <div class="col-sn-12 col-nd-5 col-lg-4" style="margin-top:5%;">
                    <center>
                        <div class="highlight_review_desktop">
                            <div class="highlight_review text-left mt-0">
                                <div class="review">Highlighted review</div>
                                <img class="review_img" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/05-min.jpg" alt="People">
                                <div class="review_name">Kartik Pathak</div>
                                <div class="review_location">Nagpur </div>
                                <div>
                                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" alt="Star" width="20" height="19">
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
                            <img class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/02-min.jpg" alt="People" width="100" height="100">
                            <div class="name_star">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
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
                            <img class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/03-min.jpg" alt="People" width="100" height="100">
                            <div class="name_star">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                            </div>
                            <div class="name">Ritu Dixit</div>
                            <div class="place"> Delhi </div>
                        </div><br><br>
                        <div class="detail show-read-more">
                            I joined Asttrolok last year. After coming to Asttrolok, I realized how important the guidance of a guru is in life. Ever since I joined this community, <a class="show_hide" data-content="toggle-text" style="color: #244A82 !important;    font-weight: 600;">Read More</a>
                            <div class="testinomial-content-more" id="more-data" style="display: none;">I have come to know how astrology can change your life. I also came to know that astrology is such a thing that if you know astrology, it has the power to change your whole life.</div>

                        </div>
                        <div class="people_block">
                            <img class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/04-min.jpg" alt="People" width="100" height="100">
                            <div class="name_star">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                            </div>
                            <div class="name">Neha Gupta</div>
                            <div class="place"> Faridabad </div>
                        </div><br><br>
                        <div class="detail show-read-more">
                            I am a student of Astro Shiromani 2022 in Asttrolok. I started my journey in Vedic science from here. And I've learned how to live life in a new way from here.

                        </div>
                         <div class="people_block">
                            <img class="person" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/01-min.jpg" alt="People" width="100" height="100">
                            <div class="name_star">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
                                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.webp.html" alt="Star" width="20" height="19">
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

        </div>
    </div>

    <div class="mt-40">
        <h2 class="section-title after-line">Who should Enroll?</h2>
        <div class="mt-15 course-description">

            <div class="row mt-40">
                <div class="col-lg-6 px-md-32">
                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Entrepreneurs-min.png" alt="healthy" height="50">
                    <h3 class="heading_who px-md-3">For Entrepreneurs:</h3>
                    <div class="text_who px-2 px-md-3">Astrology can provide valuable insights into business decisions and investment opportunities, giving entrepreneurs an edge in the competitive business world.</div>
                </div>
                <div class="col-lg-6 px-md-32">
                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Artists-min.png" alt="healthy" height="50">
                    <h3 class="heading_who px-md-3">For Artists:</h3>
                    <div class="text_who px-2 px-md-3">Astrology can help artists tap into their creativity and find inspiration, unlocking new levels of self-expression and enhancing their artistic abilities.
                    </div>
                </div>
            </div>

            <div class="row mt-40">
                <div class="col-lg-6 px-md-32">
                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/Housewife Icon-min.png" alt="healthy" height="50">
                    <h3 class="heading_who px-md-3">For Homemakers / Housewives:</h3>
                    <div class="text_who px-2 px-md-3">Studying astrology can lead to a new job in the consulting area. You may learn to use astrology skills to solve problems in your own and other people's lives.
                    </div>
                </div>
                <div class="col-lg-6 px-md-32">
                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Working Professionals-min.png" alt="healthy" height="50">
                    <h3 class="heading_who px-md-3">For Working Professionals / Freelancers:</h3>
                    <div class="text_who px-2 px-md-3">Astrology can help working professionals and freelancers better understand their strengths and weaknesses, leading to better career choices, improved relationships with colleagues and clients, and increased success.
                    </div>
                </div>
            </div>

            <div class="row mt-40">
                <div class="col-lg-6 px-md-32">

                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Student-min.png" alt="healthy" height="50">
                    <h3 class="heading_who px-md-3">For Students:</h3>
                    <div class="text_who px-2 px-md-3">Unlock your potential and discover new opportunities by exploring the world of astrology and astrology can provide valuable insights into personality traits, strengths, and weaknesses, helping students make informed
                        decisions about their education and career paths.
                    </div>
                </div>
                <div class="col-lg-6 px-md-32">
                    <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/review_images/For Spiritual Seekers-min.png" alt="healthy" height="50">
                    <h3 class="heading_who px-md-3">For Spiritual Seekers:</h3>
                    <div class="text_who px-2 px-md-3">Astrology can help spiritual seekers better understand themselves, their purpose, and their connection to the universe, leading to a deeper sense of fulfillment and spiritual growth.

                    </div>
                </div>
            </div>
        </div>
    </div>

@if(!empty($requirementsExtraDescription) and count($requirementsExtraDescription))
    <div class="mt-30">

        <h2 class="section-title after-line">Bonuses with this Astrology Course</h2>

        @foreach($requirementsExtraDescription as $requirementExtraDescription)

                    <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
                        <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px; ">
                        <img src="{{ config('app.img_dynamic_url') }}{{ $requirementExtraDescription->img }}" alt="What is social media?" class="img-cover">
                    </div>

                    <div class="ml-15">

                            <h4 class="font-16 font-weight-bold text-dark">{{ $requirementExtraDescription->value }}</h4>

                        <p class="font-16 text-gray">{{ $requirementExtraDescription->description }}.</p>

                    </div>
                </div>

        @endforeach
    </div>
@endif
@if(!empty($companyLogosExtraDescription) and count($companyLogosExtraDescription))

 <div class="mt-40">
        <h2 class="section-title after-line">About Asttrolok</h2>
        <div class="mt-15 course-description">

<div>Asttrolok, founded in 2016, stands as one of the top three reputable online Vedic institutes in the country, dedicated to dispelling misconceptions and championing fact-based knowledge of Vedic Science in the fields of Astrology, Numerology, Palmistry, Yoga, Ayurveda & Scriptures. With students hailing from over 70+ countries, including professionals like lawyers, doctors, IITians, and actors, Asttrolok boasts a diverse and esteemed student body.</div>

<div class="mt-20">The institute's reputation is further enhanced by its association with the Founder, Renowned Astrologer & Trainer Mr. Alok Khandelwal & 50+ other mentors & panelists, who all bring their extensive expertise and experience to the teaching. Asttrolok's commitment to protecting & spreading the knowledge that liberates & transforms solidifies its standing as a leading institution in the realm of Vedic astrology.</div>

        </div>
    </div>
        <div class="row mt-20">
            @foreach($companyLogosExtraDescription as $companyLogo)
                <div class="col text-center">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $companyLogo->value }}" class="webinar-extra-description-company-logos" alt="{{ trans('update.company_logos') }}">
                </div>
            @endforeach
        </div>

@endif

@if(!empty($course->faqs) and $course->faqs->count() > 0)
    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach($course->faqs as $faq)
                <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{ $faq->id }}">
                        <div href="#collapseFaq{{ $faq->id }}" aria-controls="collapseFaq{{ $faq->id }}" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>{{ clean($faq->title,'title') }}</span>
                            <i class="collapse-chevron-icon" data-feather="chevron-down" width="25" class="text-gray"></i>
                        </div>
                    </div>
                    <div id="collapseFaq{{ $faq->id }}" aria-labelledby="faq_{{ $faq->id }}" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                            {{ clean($faq->answer,'answer') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if(!empty($course->prerequisites) and $course->prerequisites->count() > 0)

    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.prerequisites') }}</h2>

        @foreach($course->prerequisites as $prerequisite)
            @if($prerequisite->prerequisiteWebinar)
                @include('web.default2.includes.webinar.list-card',['webinar' => $prerequisite->prerequisiteWebinar])
            @endif
        @endforeach
    </div>
@endif

@if(!empty($installments) and count($installments) and getInstallmentsSettings('installment_plans_position') == 'bottom_of_page' and 1==2)
    @foreach($installments as $installmentRow)
        @include('web.default2.installment.card',['installment' => $installmentRow, 'itemPrice' => $course->getPrice(), 'itemId' => $course->id, 'itemType' => 'course'])
    @endforeach
@endif

@include('web.default.includes.comments',[
        'comments' => $course->comments,
        'inputName' => 'webinar_id',
        'inputValue' => $course->id
    ])
