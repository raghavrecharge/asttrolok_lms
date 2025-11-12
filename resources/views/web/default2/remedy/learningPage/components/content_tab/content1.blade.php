@php
    $icon = '';
    $hintText= '';

    if ($type == \App\Models\WebinarChapter::$chapterSession) {
        $icon = 'video';
        $hintText = dateTimeFormat($item->date, 'j M Y  H:i') . ' | ' . $item->duration . ' ' . trans('public.min');
    } elseif ($type == \App\Models\WebinarChapter::$chapterFile) {
        $hintText = $item->file_type . ($item->volume > 0 ? ' | '.$item->volume : '');

        $icon = $item->getIconByType();
    } elseif ($type == \App\Models\WebinarChapter::$chapterTextLesson) {
        $icon = 'file-text';
        $hintText= $item->study_time . ' ' . trans('public.min');
    }

    $checkSequenceContent = $item->checkSequenceContent();
    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
@endphp

<div class="1 d-flex align-items-start p-10 cursor-pointer tab-item accessdenied" >

        <span class="chapter-icon bg-gray300 mr-10">
            <i data-feather="{{ $icon }}" class="text-gray" width="16" height="16"></i>
        </span>

    <div>
        <div class="">
            <span class="font-weight-500 font-14 text-dark-blue d-block">{{ $item->title }}</span>
            <span class="font-12 text-gray d-block">{{ $hintText }}</span>
        </div>


        <div class="tab-item-info mt-15">
            <p class="font-12 text-gray d-block">
                @php
                    $description = !empty($item->description) ? $item->description : (!empty($item->summary) ? $item->summary : '');
                @endphp

                {!! truncate($description, 150) !!}
            </p>

            <div class="d-flex align-items-center justify-content-between mt-15">
                <label class="mb-0 mr-10 cursor-pointer font-weight-normal font-14 text-dark-blue" >{{ trans('public.i_passed_this_lesson') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox"  data-item="" value="" class="js-passed-lesson-toggle custom-control-input" >
                    <label class="custom-control-label" ></label>
                </div>
            </div>
        </div>
    </div>
</div>
