@php
    $learningMaterialsExtraDescription = !empty($talk->webinarExtraDescription) ? $talk->webinarExtraDescription->where('type','learning_materials') : null;
    $companyLogosExtraDescription = !empty($talk->webinarExtraDescription) ? $talk->webinarExtraDescription->where('type','company_logos') : null;
    $requirementsExtraDescription = !empty($talk->webinarExtraDescription) ? $talk->webinarExtraDescription->where('type','requirements') : null;
@endphp

@if(!empty($installments) and count($installments) and getInstallmentsSettings('installment_plans_position') == 'top_of_page')
    @foreach($installments as $installmentRow)
        @include('web.default2.installment.card',['installment' => $installmentRow, 'itemPrice' => $talk->getPrice(), 'itemId' => $talk->id, 'itemType' => 'talk'])
    @endforeach
@endif

<style>
    .highlight_review { margin-top: 70px; border-radius: 20px; background: #f8f8f8; padding: 5px 2px 0px 10px; }
    .highlight_review .review { background: #fff; color: #b79d74; border-radius: 10px; padding: 8px 10px; display: inline-block; }
    .review_img { width: 120px; height: 120px; border-radius: 50%; margin: 20px auto 0; display: block; }
    .review_name { color: #566890; font-size: 18px; font-family: "Poppins", Sans-serif; font-weight: 700; }
    .review_location { color: #2e2e2e; font-size: 16px; font-family: "Poppins", Sans-serif; margin-bottom: 10px; }
    .review_detail { color: #2e2e2e; font-size: 16px; font-family: "Poppins", Sans-serif; margin: 15px 0; line-height: 25px; }
    .people_block { padding-left: 20px; }
    img.person { width: 100px; height: 100px; float: left; margin-right: 10px; border-radius: 50%; }
    .people_block .name_star, .people_block .name { color: #566890; font-size: 17-18px; font-family: "Poppins", Sans-serif; font-weight: 700; }
    .people_block .place { color: #2e2e2e; font-size: 16px; font-family: "Poppins", Sans-serif; }
    .detail.show-read-more { color: #2e2e2e; font-size: 16px; font-family: "Poppins", Sans-serif; margin-bottom: 30px; padding-left: 20px; }
    .px-md-32 img { padding: 0 1rem !important; }
    .heading_who { color: #146EA1; font-size: 18px; margin: 20px 0 10px; }
    .pl-md-3, .px-md-3 { padding-left: 20px !important; }
</style>

@if($talk->description)
    <div class="mt-20">
        <h2 class="section-title after-line">About this Talk</h2>
        <div class="mt-15 course-description">
            {!! $talk->description !!}
        </div>
    </div>
@endif

@if(!empty($learningMaterialsExtraDescription) and count($learningMaterialsExtraDescription))
    <div class="mt-40">
        <h2 class="section-title after-line">What You will get?</h2>
        @foreach($learningMaterialsExtraDescription as $learningMaterial)
            <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
                <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px;">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $learningMaterial->img }}" alt="{{ $learningMaterial->value }}" class="img-cover">
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

</div>

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

@if(!empty($talk->teacher))
    <div class="mt-30">
        <h2 class="section-title after-line">Meet your Mentor</h2>
        <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
            <div class="forums-featured-card-icon" style="width: 250px; min-width: 250px;">
                <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/faqs/Alok-sir1.png" class="img-cover">
            </div>
            <div class="ml-50">
                <h4 class="font-20 font-weight-bold text-dark">{{ $talk->teacher->name }}</h4>
                <p class="font-16 text-gray">{{ $talk->teacher->bio }}</p>
            </div>
        </div>
    </div>
@endif

@if(!empty($talk->reviews))
    <div class="mt-40">
        <h2 class="section-title after-line">People are saying</h2>
        <div class="mt-15 course-description">
            <div><b>{{ $talk->reviews_count ?? 'Thousands' }} people have already attended this Talk</b></div>
            @foreach($talk->reviews as $review)
                <div class="people_block">
                    <img class="person" src="{{ config('app.img_dynamic_url') }}{{ $review->image }}" alt="{{ $review->name }}">
                    <div class="name_star">
                        @for($i=0;$i<$review->rating;$i++)
                            <img src="{{ config('app.js_css_url') }}/assets/default/css/landingPage/resources/img/star.png" width="20" height="19">
                        @endfor
                    </div>
                    <div class="name">{{ $review->name }}</div>
                    <div class="place">{{ $review->location }}</div>
                    <div class="review_detail show-read-more">{{ $review->comment }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if(!empty($requirementsExtraDescription) and count($requirementsExtraDescription))
    <div class="mt-30">
        <h2 class="section-title after-line">Bonuses with this Talk</h2>
        @foreach($requirementsExtraDescription as $requirementExtraDescription)
            <div class="forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 shadow-lg rounded-lg mt-15">
                <div class="forums-featured-card-icon" style="width: 130px; min-width: 130px;">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $requirementExtraDescription->img }}" class="img-cover">
                </div>
                <div class="ml-15">
                    <h4 class="font-16 font-weight-bold text-dark">{{ $requirementExtraDescription->value }}</h4>
                    <p class="font-16 text-gray">{{ $requirementExtraDescription->description }}</p>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if(!empty($companyLogosExtraDescription) and count($companyLogosExtraDescription))
    <div class="mt-40">
        <h2 class="section-title after-line">About Asttrolok</h2>
        <div class="mt-15 course-description">
            <div>Asttrolok, founded in 2016, is a top Vedic institute with students from 50+ countries.</div>
        </div>
        <div class="row mt-20">
            @foreach($companyLogosExtraDescription as $companyLogo)
                <div class="col text-center">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $companyLogo->value }}" class="webinar-extra-description-company-logos" alt="Company Logo">
                </div>
            @endforeach
        </div>
    </div>
@endif

@if(!empty($talk->faqs) and $talk->faqs->count() > 0)
    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>
        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach($talk->faqs as $faq)
                <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{ $faq->id }}">
                        <div href="#collapseFaq{{ $faq->id }}" aria-controls="collapseFaq{{ $faq->id }}" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>{{ clean($faq->title,'title') }}</span>
                            <i class="collapse-chevron-icon" data-feather="chevron-down" width="25" class="text-gray"></i>
                        </div>
                    </div>
                    <div id="collapseFaq{{ $faq->id }}" aria-labelledby="faq_{{ $faq->id }}" class="collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                            {{ clean($faq->answer,'answer') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if(!empty($installments) and count($installments) and getInstallmentsSettings('installment_plans_position') == 'bottom_of_page')
    @foreach($installments as $installmentRow)
        @include('web.default.installment.card',['installment' => $installmentRow, 'itemPrice' => $talk->getPrice(), 'itemId' => $talk->id, 'itemType' => 'talk'])
    @endforeach
@endif
