@php
    $icon = '';
    $hintText= '';
    //$limit1=$limit1-1;

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
    $userId = request()->segment(3);
$query = \App\Models\SubscriptionCourseProgress::where('subscription_id', $subscription->id)
    ->where('user_id', $userId)
    ->where('item_id', $item->id)
     ->first();

    $checkSequenceContent = $item->checkSequenceContent();
    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
@endphp

            <div class="course-card" >
                <div class="card-header">
                    <div class="video-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <div class="card-title">
                        <div class="class-number">{{ $item->title }}</div>

                    </div>
                </div>
                <div class="card-content">
                    <div class="content-type">video</div>
                    <div class="progress-section">
                        <div class="progress-label">Progress</div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ optional($query)->watch_percentage ?? 0 }}%"></div>
                        </div>
                        <div class="progress-percentage">{{ optional($query)->watch_percentage ?? 0 }}</div>
                    </div>

                </div>
            </div>
