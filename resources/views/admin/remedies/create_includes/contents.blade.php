<section class="mt-50">
    <div class="">
        <h2 class="section-title after-line">{{ trans('public.chapters') }} ({{ trans('public.optional') }})</h2>
    </div>
<button type="button" class="js-add-chapter btn btn-primary btn-sm mt-15" data-remedy-id="{{ $remedy->id }}">{{ trans('public.new_chapter') }}</button>

    @include('admin.remedies.create_includes.accordions.chapter')
</section>

<!--@if($remedy->isRemedy())-->
<!--    <div id="newSessionForm" class="d-none">-->
<!--        @include('admin.remedies.create_includes.accordions.session',['remedy' => $remedy])-->
<!--    </div>-->
<!--@endif-->

<div id="newFileForm" class="d-none">
    @include('admin.remedies.create_includes.accordions.file',['remedy' => $remedy])
</div>

@if(getFeaturesSettings('new_interactive_file'))
    <div id="newInteractiveFileForm" class="d-none">
        @include('admin.remedies.create_includes.accordions.new_interactive_file',['remedy' => $remedy])
    </div>
@endif


<!--<div id="newTextLessonForm" class="d-none">-->
<!--    @include('admin.remedies.create_includes.accordions.text-lesson',['remedy' => $remedy])-->
<!--</div>-->



<!--@if(getFeaturesSettings('remedy_assignment_status'))-->
<!--    <div id="newAssignmentForm" class="d-none">-->
<!--        @include('admin.remedies.create_includes.accordions.assignment',['remedy' => $remedy])-->
<!--    </div>-->
<!--@endif-->

@include('admin.remedies.create_includes.chapter_modal')

@include('admin.remedies.create_includes.change_chapter_modal')
