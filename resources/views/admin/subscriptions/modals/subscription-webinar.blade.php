<!--Course Modal -->
<div class="d-none" id="subscriptionWebinarsModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.add_new_course') }}</h3>

    <div class="js-form" data-action="{{ getAdminPanelUrl() }}/subscription-webinars/store">
        <input type="hidden" name="subscription_id" value="{{  !empty($subscription) ? $subscription->id :''  }}">

        <div class="form-group mt-15">
            <label class="input-label d-block">{{ trans('panel.select_course') }}</label>
            <select name="webinar_id" class="js-ajax-webinar_id form-control subscriptionWebinars-select" data-subscription-id="{{  !empty($subscription) ? $subscription->id : '' }}" data-placeholder="{{ trans('panel.select_course') }}">

                @if(!empty($userWebinars) and count($userWebinars))
                    @foreach($userWebinars as $userWebinar)
                        <option value="{{ $userWebinar->id }}">{{ $userWebinar->title }}</option>
                    @endforeach
                @endif 
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveSubscriptionWebinar" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
<!--Products model-->
<div class="d-none" id="subscriptionProductsModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.add_new_product1') }}</h3>

    <div class="js-form" data-action="{{ getAdminPanelUrl() }}/subscription-products/store">
        <input type="hidden" name="subscription_id" value="{{  !empty($subscription) ? $subscription->id :''  }}">

        <div class="form-group mt-15">
            <label class="input-label d-block">{{ trans('panel.select_product') }}</label>
            <select name="product_id" class="js-ajax-product_id form-control subscriptionProducts-select" data-subscription-id="{{  !empty($subscription) ? $subscription->id : '' }}" data-placeholder="{{ trans('panel.select_product') }}">

                @if(!empty($userProducts) and count($userProducts))
                    @foreach($userProducts as $product)
                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                @endforeach
                @endif 
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveSubscriptionProduct" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
<!--Consultation model-->
<div class="d-none" id="subscriptionConsultationModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.add_new_consultation') }}</h3>
    
    <div class="js-form" data-action="{{ getAdminPanelUrl() }}/subscription-consultations/store">
        <input type="hidden" name="subscription_id" value="{{  !empty($subscription) ? $subscription->id :''  }}">


<div class="form-group mt-15">
    <label class="input-label d-block">{{ trans('panel.select_consultation_type') }}</label>
    <select id="consultationType" name="consultation_type" class="js-ajax-consultation_id form-control subscriptionConsultant-select">
        <option value="">-- Select --</option>
        <option value="specific">Specific Consultant</option>
        <option value="range">Price Range</option>
        <option value="all">All Consultants</option>
    </select>
     <div class="invalid-feedback"></div>
</div>

<!-- Specific Consultant -->
<div id="specificConsultant" class="form-group mt-15 d-none">
    <label class="input-label d-block">{{ trans('panel.select_consultant') }}</label>
    <select name="consultant_id" class="form-control">
        @if(!empty($userConsultants) and count($userConsultants))
            @foreach($userConsultants as $consultant)
                <option value="{{ $consultant->id }}">{{ $consultant->full_name }}</option>
            @endforeach
        @endif
    </select>
    
</div>

<!-- Price Range -->
<div id="priceRange" class="form-group mt-15 d-none">
    <label class="input-label d-block">Starting From</label>
    <input type="number" name="starting_price" class="form-control"/>
    <label class="input-label d-block">To</label>
    <input type="number" name="ending_price" class="form-control"/>
</div>

<!-- Slot Time -->
<div id="slotTime" class="form-group mt-15 d-none">
    <label class="input-label d-block">Slot Time</label>
    <select name="slot_time" class="form-control">
        <option value="15">15 Min</option>
        <option value="30">30 Min</option>
        <option value="both">Both</option>
    </select>
</div>


        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveSubscriptionConsultation" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
