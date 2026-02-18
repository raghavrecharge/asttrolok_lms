@if(!empty($subscription->faqs) and $subscription->faqs->count() > 0)
<div class="frame427322615-frame427322593">

    <div class="frame427322615-frame427322536 mb-10">
        <div class="frame427322615-frame427322535">
            <span class="frame427322615-text194"
                  style="white-space: nowrap; width: auto; display: inline-block;">
                Frequently Asked Questions
            </span>
        </div>

        {{-- same green underline line --}}
        <img
            src="/public/public/horizontaldivider2863-fyl2-200h.png"
            alt="HorizontalDivider2863"
            class="frame427322615-horizontal-divider3"
        />
    </div>


    <div style="width: 100%;">

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">

            @foreach($subscription->faqs as $faq)
            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">

                <!-- HEAD -->
                <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{ $loop->index }}">
                    <div href="#collapseFaq{{ $loop->index }}"
                        aria-controls="collapseFaq{{ $loop->index }}"
                        class="d-flex align-items-center justify-content-between"
                        role="button"
                        data-toggle="collapse"
                        data-parent="#accordion"
                        aria-expanded="false">

                        <span style="
                            color: rgba(50, 160, 40, 1);
                            height: auto;
                            font-size: 18px;
                            font-style: Medium;
                            text-align: left;
                            font-family: Inter;
                            font-weight: 500;
                            line-height: 28px;
                            font-stretch: normal;
                            text-decoration: none;
                            display: block;
                        ">
                            {{ clean($faq->title,'title') }}
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

                <!-- BODY -->
                <div id="collapseFaq{{ $loop->index }}"
                    aria-labelledby="faq_{{ $loop->index }}"
                    class="collapse"
                    role="tabpanel">

                    <div class="panel-collapse text-gray">
                        {{ clean($faq->answer,'answer') }}
                    </div>

                </div>

            </div>
            @endforeach

        </div>

    </div>
</div>
@endif
