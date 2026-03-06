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

    // $isItemUnlocked is passed explicitly from content_tab/index.blade.php.
    // True  → item is within the user's unlockedItemCount → rendered as accessible (tab-item).
    // False → item is beyond the unlock boundary → rendered as locked (js-sequence-content-error-modal).
    // Default true keeps backward compatibility if included without the variable.
    $isItemUnlocked = $isItemUnlocked ?? true;
@endphp

<div class="1 pratul d-flex align-items-start p-10 cursor-pointer {{ $isItemUnlocked ? 'tab-item' : 'js-sequence-content-error-modal' }}"
     data-type="{{ $type }}"
     data-id="{{ $item->id }}"
     onclick =accessdenied()
>

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

        </div>
    </div>
</div>
