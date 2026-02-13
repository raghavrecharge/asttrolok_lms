<div class="d-none" id="subscriptionFaqModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_faq') }}</h3>

    <!-- ✅ CHANGED: div to form -->
    <form class="js-faq-form" data-action="{{ getAdminPanelUrl() }}/faqs/store">
        <input type="hidden" name="subscription_id" value="{{ !empty($subscription) ? $subscription->id : '' }}">
        <input type="hidden" name="webinar_id" value="">
        <input type="hidden" name="bundle_id" value="">
        <input type="hidden" name="upcoming_course_id" value="">
        
        <!-- ✅ ADD THIS: Type field with class for JS targeting -->
        <input type="hidden" name="type" value="faq" class="js-faq-type">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class="form-group">
                <label class="input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class="form-control">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif

        <div class="form-group">
            <label class="input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class="js-ajax-title form-control" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group">
            <label class="input-label">{{ trans('public.answer') }}</label>
            <textarea name="answer" class="js-ajax-answer form-control" rows="6"></textarea>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveFAQ" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>