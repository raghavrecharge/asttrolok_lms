@php
    $learningMaterialsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','learning_materials') : null;
    $companyLogosExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','company_logos') : null;
    $requirementsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','requirements') : null;
@endphp

@if($subscription->description)
    <div class="mt-20">

        <div class="mt-15 course-description">
            {!! $subscription->description !!}
        </div>
    </div>
@endif

<div style="text-align: center;">
@if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
@if($subscription->price > 0)

    <a href="/subscriptions/direct-payment-enroll/{{$subscription->slug}}" class="btn btn-primary btn-sm px-25 mt-20">Subscribe Now</a>

    @else

        @if($subscription->slug == 'learn-free-vedic-astrology-subscription-online' )
        <a href="/register-free" class=" btn btn-primary btn-sm mt-20 {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
        @else
        <a href="{{ $canSale ? '/subscription/'. $subscription->slug .'/free' : '#' }}" class="mt-20 btn btn-primary btn-sm {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
        @endif

    @endif

@endif
</div>

@if(!empty($learningMaterialsExtraDescription) and count($learningMaterialsExtraDescription))
    <div class="mt-20 ">
    <h2 class="section-title after-line">What You will get?</h2>

        @foreach($learningMaterialsExtraDescription as $learningMaterial)

            <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">

                    <div class="forums-featured-card-icon col-4" style="padding: 0;">
                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $learningMaterial->img }}" alt="What is social media?" class="img-cover">
                    </div>

                    <div class="ml-15">

                            <h4 class="font-16 font-weight-bold text-dark">{{ $learningMaterial->value }}</h4>

                        <p class="font-16 text-gray">{{ $learningMaterial->description }}</p>

                    </div>
                </div>
        @endforeach
    </div>
@endif

 <div class="mt-40">
      <h2 class="section-title after-line">What all you will learn?</h2>

 @include('web.default2'.'.subscription.tabs.content')
 </div>

<div style="text-align: center;">
@if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
@if($subscription->price > 0)

    <a href="/subscriptions/direct-payment-enroll/{{$subscription->slug}}" class="btn btn-primary btn-sm px-25 mt-20">Subscribe Now</a>
    @else

        @if($subscription->slug == 'learn-free-vedic-astrology-subscription-online' )
        <a href="/register-free" class=" btn btn-primary btn-sm mt-20 {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
        @else
        <a href="{{ $canSale ? '/subscription/'. $subscription->slug .'/free' : '#' }}" class="mt-20 btn btn-primary btn-sm {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
        @endif

    @endif

@endif
</div>
 <div class="mt-30">
        <h2 class="section-title after-line">How this Course can benefit you?</h2>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 100px; min-width: 100px; ">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Career-Advancement-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Affordable Learning Without Barriers</h4>
                <p class="font-16 text-gray">No more heavy fees. Learn complete Vedic Astrology at just ₹2100/month. A model designed so that anyone can start easily and continue step by step.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 100px; min-width: 100px; ">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Personal-Fulfilment-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Build a Career in 6 Months</h4>
                <p class="font-16 text-gray">Within half a year, you’ll gain the knowledge and confidence to start offering consultations, guide people with astrology, or even build a professional practice.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 100px; min-width: 100px; ">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Credibility-and-Trust-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">Learn Anytime, Anywhere</h4>
                <p class="font-16 text-gray">Busy schedule? No problem. With recorded video classes, you can learn at your own pace, revisit lessons anytime, and never miss a session.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 100px; min-width: 100px; ">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Enhanced-Skill-Set-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">From Beginner to Expert</h4>
                <p class="font-16 text-gray">Unlike other courses that cover only basics, this subscription takes you from fundamentals to advanced astrology. By the end, you’ll be equipped to analyze charts, give remedies, and make accurate predictions.</p>
            </div>
        </div>

        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 100px; min-width: 100px; ">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Professional-Networking-min.png" alt="What is social media?" class="img-cover">
            </div>

            <div class="ml-15">
                <h4 class="font-16 font-weight-bold text-dark">From Beginner to Expert</h4>
                <p class="font-16 text-gray">Unlike other courses that cover only basics, this subscription takes you from fundamentals to advanced astrology. By the end, you’ll be equipped to analyze charts, give remedies, and make accurate predictions.</p>
            </div>
        </div>
    </div>
     <div class="mt-30">
        <h2 class="section-title after-line">Meet your Mentor</h2>

        <div class="forums-featured-card align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 100%; ">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/Alok-sir-min.webp" alt="What is social media?" class="img-cover">
            </div>

            <div class=" mt-20">
                <center><h4 class="font-20 font-weight-bold text-dark">Mr. Alok Khandelwal</h4></center><br>
                <p class="font-14 text-gray">Mr. Alok Khandelwal is an internationally celebrated Vedic Astrologer and founder of Asttrolok, a premier institute for authentic Vedic knowledge. With 19+ years of expertise, he holds distinguished degrees including Jyotish Bhushan, Jyotish Ratna, Jyotish Rishi, MBA (Marketing), and MA (Economics). </p><br>
                <p class="font-14 text-gray">Under his leadership, Asttrolok has trained 50,000+ students across 70+ countries, hosted 50+ webinars, and nurtured 150+ trained astrologers consulting actively through Asttrolok’s platform. He has delivered over 35,000 consultations globally and continues to teach students across USA, Europe, and Russia. A committed meditator associated with The Art of Living Foundation for over 20+ years, Mr. Khandelwal’s mission is to re-establish astrology as a rational, empowering science worldwide.</p><br>

            </div>
        </div>
    </div>
 <div class="mt-40">
        <h2 class="section-title after-line">About Asttrolok</h2>
        <div class="mt-15 course-description">

<div>Asttrolok, founded in 2016, stands as one of the top three reputable online Vedic institutes in the country, dedicated to dispelling misconceptions and championing fact-based knowledge of Vedic Science in the fields of Astrology, Numerology, Palmistry, Yoga, Ayurveda & Scriptures. With students hailing from over 70+ countries, including professionals like lawyers, doctors, IITians, and actors, Asttrolok boasts a diverse and esteemed student body.</div>

        </div>
    </div>
@if(!empty($requirementsExtraDescription) and count($requirementsExtraDescription))
    <div class="mt-20">

<h2 class="section-title after-line">Bonuses with this Astrology Course</h2>
        @foreach($requirementsExtraDescription as $requirementExtraDescription)

            <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
                        <div class="forums-featured-card-icon col-3" style="padding: 0;">
                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $requirementExtraDescription->img }}" alt="What is social media?" class="img-cover">
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

<div>Asttrolok, founded in 2016, stands as one of the top three reputable online Vedic institutes in the country, dedicated to dispelling misconceptions and championing fact-based knowledge of Vedic Science in the fields of Astrology, Numerology, Palmistry, Yoga, Ayurveda & Scriptures. With students hailing from over 50+ countries, including professionals like lawyers, doctors, IITians, and actors, Asttrolok boasts a diverse and esteemed student body.</div>

<div class="mt-20">The institute's reputation is further enhanced by its association with the Founder, Renowned Astrologer & Trainer Mr. Alok Khandelwal & 50+ other mentors & panelists, who all bring their extensive expertise and experience to the teaching. Asttrolok's commitment to protecting & spreading the knowledge that liberates & transforms solidifies its standing as a leading institution in the realm of Vedic astrology.</div>

        </div>
    </div>
        <div class="row mt-20">
            @foreach($companyLogosExtraDescription as $companyLogo)
                <div class="col text-center">
                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $companyLogo->value }}" class="webinar-extra-description-company-logos" alt="{{ trans('update.company_logos') }}">
                </div>
            @endforeach
        </div>

@endif

@if(!empty($subscription->prerequisites) and $subscription->prerequisites->count() > 0)

    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.prerequisites') }}</h2>

        @foreach($subscription->prerequisites as $prerequisite)
            @if($prerequisite->prerequisiteWebinar)
                @include('web.default.includes.webinar.list-card',['webinar' => $prerequisite->prerequisiteWebinar])
            @endif
        @endforeach
    </div>
@endif

<div class="mt-20">
        <h2 class="section-title after-line">Frequently Asked Questions (FAQs)</h2>

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
                            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_18">
                        <div href="#collapseFaq18" aria-controls="collapseFaq18" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>1. What is the fee for this course?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down collapse-chevron-icon"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div id="collapseFaq18" aria-labelledby="fBeginners Curious About Astrologyaq_18" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                            The subscription fee is ₹2100 per month. You can learn at your own pace and continue for as many months as you wish.
                        </div>
                    </div>
                </div>
                            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_19">
                        <div href="#collapseFaq19" aria-controls="collapseFaq19" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>2. How will the classes be conducted?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down collapse-chevron-icon"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div id="collapseFaq19" aria-labelledby="faq_19" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                           Classes will be in the form of pre-recorded video lessons, which you can watch anytime, anywhere. This gives you complete flexibility.
                        </div>
                    </div>
                </div>
                            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_20">
                        <div href="#collapseFaq20" aria-controls="collapseFaq20" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>3. Will I get a certificate?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down collapse-chevron-icon"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div id="collapseFaq20" aria-labelledby="faq_20" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                           Yes ✅ After successful completion of the subscription course, you will receive a certificate from Asttrolok.
                        </div>
                    </div>
                </div>
                            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_21">
                        <div href="#collapseFaq21" aria-controls="collapseFaq21" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>4. Will study material be provided?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down collapse-chevron-icon"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div id="collapseFaq21" aria-labelledby="faq_21" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray"> Yes Along with video lessons, you will also get study material, notes, and reference charts to support your learning.</div>
                    </div>
                </div>

                            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_23">
                        <div href="#collapseFaq23" aria-controls="collapseFaq23" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>5. Who can join this course?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down collapse-chevron-icon"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div id="collapseFaq23" aria-labelledby="faq_23" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                           Anyone interested in astrology—whether a beginner or someone with basic knowledge—can join. No prior background is required.
                        </div>
                    </div>
                </div>

                    </div>
    </div>

@if(!empty($subscription->faqs) and $subscription->faqs->count() > 0)
    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach($subscription->faqs as $faq)
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
