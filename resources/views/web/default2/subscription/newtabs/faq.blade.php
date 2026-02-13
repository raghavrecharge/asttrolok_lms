
@if(!empty($subscription->faqs) and $subscription->faqs->count() > 0)
          <div class="frame427322615-frame427322593">
            <div class="frame427322615-frame427322592">
              <span class="frame427322615-text299">
                {{ trans('public.faq') }}
              </span>
              <img
                src="/public/public/horizontaldivider2880-2m5e-200h.png"
                alt="HorizontalDivider2880"
                class="frame427322615-horizontal-divider5"
              />
             
            </div>

 <div class="" style="
    width: 100%;
">
        <!-- <h2 class="section-title after-line">Frequently Asked Questions (FAQs)</h2> -->

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
           @foreach($subscription->faqs as $faq)
            <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_18">
                        <div href="#collapseFaq18" aria-controls="collapseFaq18" class="d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span style="
                        color: rgba(50, 160, 40, 1);
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
                              {{ clean($faq->title,'title') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down collapse-chevron-icon"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div id="collapseFaq18" aria-labelledby="fBeginners Curious About Astrologyaq_18" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
 {{ clean($faq->answer,'answer') }}                        </div>
                    </div>
                </div>
                @endforeach
                    </div>
    </div>
          </div>
          @endif