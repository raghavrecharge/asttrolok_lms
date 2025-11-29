<section class="mt-50">
    <div class="">
        <h2 class="section-title after-line">{{ trans('public.chapters') }} ({{ trans('public.optional') }})</h2>
    </div>

    {{--<button type="button" class="js-add-chapter btn btn-primary btn-sm mt-15" data-webinar-id="{{ $webinar->id }}">{{ trans('public.new_chapter') }}</button>--}}

    @include('admin.subscriptions.create_includes.accordions.chapter')
</section>
