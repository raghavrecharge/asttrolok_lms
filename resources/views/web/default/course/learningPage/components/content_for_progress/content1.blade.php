@php
    $icon = '';
    $hintText = '';
    $totalVideos = 10; // Example: Total number of videos
    $watchedVideos = 4; // Example: Number of videos watched by the user
    $Progress = 0;

    if ($type == \App\Models\WebinarChapter::$chapterFile) {
        $hintText = $item->file_type . ($item->volume > 0 ? ' | ' . $item->volume : '');
        $icon = $item->getIconByType();
        $itemId = $item->id;
        $chapter_id = $item->chapter_id;
        $userId = request()->route('id');

        // Fetch the course progress for the current item and user
        $CourseProgress = \App\Models\CourseProgress::where('item_id', (int) $itemId)
            ->where('user_id', (int) $userId)
            ->first();
@endphp

@if($CourseProgress)
    @php
        // Assign watch percentage from CourseProgress
        $Progress = $CourseProgress->watch_percentage;
    @endphp
@endif

<div class="1 d-flex align-items-start p-10 cursor-pointer tab-item accessdenied" onclick="showProgressBar()">

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

        <!-- Progress bar section -->
        <div class="mt-20">
            <label for="videoProgress" class="font-12 text-gray">Progress</label>
            <progress id="videoProgress" value="{{ $Progress }}" max="100" class="progress-bar"></progress>
            <span id="progressValue" class="font-12 text-gray">{{ $Progress }}%</span> 
        </div>
    </div>
</div>

@php
    }
@endphp

<style>
    .progress-bar {
        width: 100%;
        height: 8px;
        background-color: #e0e0e0;
        border-radius: 4px;
    }

    progress::-webkit-progress-bar {
        background-color: #e0e0e0;
        border-radius: 4px;
    }

    progress::-webkit-progress-value {
        background-color: #007bff; /* Blue color for progress */
        border-radius: 4px;
    }

    progress::-moz-progress-bar {
        background-color: #007bff; /* Blue color for Firefox */
        border-radius: 4px;
    }
</style>

<script>
    function showProgressBar() {
        // Logic to update the progress bar dynamically can go here
    }
</script>
