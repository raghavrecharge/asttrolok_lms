<li data-id="{{ $subscriptionItem->id }}" id="file-item-{{ $quiz->id }}" data-id="{{ $quiz->id }}" class="accordion-row bg-white rounded-sm border border-gray300 mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class="d-flex align-items-center justify-content-between" role="tab"
        id="quiz_{{ !empty($subscriptionItem) ? $subscriptionItem->id : 'record' }}">
        <div class="d-flex align-items-center" href="#collapseFile{{ !empty($quiz) ? $quiz->id :'record' }}" aria-controls="collapseFile{{ !empty($quiz) ? $quiz->id :'record' }}" data-parent="#chapterContentAccordion{{ $subscriptionItems->isNotEmpty() ? $subscriptionItems->first()->id : 'record' }}"
       role="button" data-toggle="collapse" aria-expanded="true">
            <span class="chapter-icon chapter-content-icon mr-10">
                <i data-feather="award" class=""></i>
            </span>
            <span class="font-weight-bold text-dark-blue d-block cursor-pointer">
                {{ !empty($subscriptionItem) && !empty($subscriptionItem->quiz) ? $subscriptionItem->quiz->title : trans('public.add_new_quizzes') }}
            </span>
        </div>
        <div class="d-flex align-items-center">
            <label class="custom-control custom-switch mr-20">
                <input type="checkbox" class="custom-control-input" 
                    {{ $quiz->status == 'active' ? 'checked' : '' }}
                    onchange="toggleStatus({{ $quiz->id }}, this.checked)">
                <span id="status-label-{{ $quiz->id }}" class="custom-control-label">
                    {{ $quiz->status == 'active' ? __('Active') : __('Inactive') }}
                </span>
            </label>
            {{--@if(!empty($subscriptionItem) && !empty($subscriptionItem->quiz) && $subscriptionItem->quiz->status != \App\Models\WebinarChapter::$chapterActive)
                <span class="disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
            @endif--}}
            @if(!empty($subscriptionItem) && !empty($chapterItem))
                <button type="button" data-item-id="{{ $subscriptionItem->quiz->id ?? '' }}"
                    data-item-type="{{ \App\Models\WebinarChapterItem::$chapterQuiz }}"
                    data-chapter-id="{{ !empty($chapter) ? $chapter->id : '' }}"
                    class="js-change-content-chapter btn btn-sm btn-transparent text-gray mr-10">
                    <i data-feather="grid" class="" height="20"></i>
                </button>
            @endif
            
                <i data-feather="move" class="move-icon mr-10 cursor-pointer" height="20"></i>
           
           {{-- @if(!empty($subscriptionItem) && !empty($subscriptionItem->quiz))
                <a href="{{ getAdminPanelUrl() }}/quizzes/{{ $subscriptionItem->quiz->id }}/delete"
                    class="delete-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="trash-2" class="mr-10 cursor-pointer" height="20"></i>
                </a>
            @endif--}}
            {{--<i class="collapse-chevron-icon" data-feather="chevron-down" height="20"
                href="#collapseQuiz{{ !empty($subscriptionItem) ? $subscriptionItem->id : 'record' }}"
                aria-controls="collapseQuiz{{ !empty($subscriptionItem) ? $subscriptionItem->id : 'record' }}"
                data-parent="#quizzesAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>--}}
        </div>
    </div>
    <div id="collapseQuiz{{ !empty($subscriptionItem) ? $subscriptionItem->id : 'record' }}"
        aria-labelledby="quiz_{{ !empty($subscriptionItem) ? $subscriptionItem->id : 'record' }}"
        class="collapse @if(empty($subscriptionItem)) show @endif" role="tabpanel">
        <div class="panel-collapse text-gray">
            @include('admin.quizzes.create_quiz_form', [
                'inWebinarPage' => false, {{-- Subscription module me false rakhen --}}
                'quiz' => $subscriptionItem->quiz ?? null,
                'quizQuestions' => !empty($subscriptionItem->quiz) ? $subscriptionItem->quiz->quizQuestions : [],
                'chapters' => isset($chapters) ? $chapters : collect(),
                'webinarChapterPages' => isset($webinarChapterPages) ? $webinarChapterPages : null,
                'creator' => isset($creator) ? $creator : null,
            ])
        </div>
    </div>
</li>
