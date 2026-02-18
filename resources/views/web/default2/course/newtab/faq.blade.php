@php
    // ✅ Filter only FAQ type items
    $filteredFaqs = $course->faqs->where('type', 'faq');
@endphp

@if(!empty($course->faqs) and $filteredFaqs->count() > 0)
<div class="frame427322615-frame427322593">
    <div class="frame427322615-frame427322592">
        <span class="frame427322615-text299">
            Frequently Asked Questions
        </span>
        <img
            src="/public/public/horizontaldivider2880-2m5e-200h.png"
            alt="HorizontalDivider2880"
            class="frame427322615-horizontal-divider5"
        />
    </div>

    <div class="" style="width: 100%;">
        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
            @php
            $count_faq = 1;
            @endphp
            
            @foreach($filteredFaqs as $faq)
            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{$count_faq}}">
                    <div href="#collapseFaq{{$count_faq}}" 
                         aria-controls="collapseFaq{{$count_faq}}" 
                         class="d-flex align-items-center justify-content-between" 
                         role="button" 
                         data-toggle="collapse" 
                         data-parent="#accordion" 
                         aria-expanded="true">
                        <span style="color: rgba(50, 160, 40, 1);
                                     width: 342px;
                                     height: auto;
                                     font-size: 18px;
                                     font-style: Medium;
                                     text-align: left;
                                     font-family: Inter;
                                     font-weight: 500;
                                     line-height: 28px;
                                     font-stretch: normal;
                                     text-decoration: none;">
                            {{ clean(optional($faq)->title,'title') }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             width="25" height="24" 
                             viewBox="0 0 24 24" 
                             fill="none" 
                             stroke="currentColor" 
                             stroke-width="2" 
                             stroke-linecap="round" 
                             stroke-linejoin="round" 
                             class="feather feather-plus collapse-chevron-icon">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                </div>
                <div id="collapseFaq{{$count_faq}}" 
                     aria-labelledby="faq_{{$count_faq}}" 
                     class="collapse" 
                     role="tabpanel">
                    <div class="panel-collapse text-gray">
                        {{ clean($faq->answer,'answer') }}
                    </div>
                </div>
            </div>
            @php
            $count_faq++;
            @endphp
            @endforeach
        </div>
    </div>
</div>
@endif