@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/video/video-js.min.css">
@endpush
<style>
    .answer-item{
        position: relative;
        width: 100%;
        height: auto !important;
        padding: 10px !important;
    }
    
    .quiz-info-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 20px;
    }
    
    @media (min-width: 768px) {
        .quiz-info-container {
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
    }
    
    .quiz-info-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 120px;
    }
    
    .quiz-info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }
    
    .card-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }
    
    .card-icon-box img {
        width: 22px;
        height: 22px;
        object-fit: contain;
    }
    
    .card-value {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.1;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .card-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-top: 6px;
    }
    
    .quiz-timer-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    
    .quiz-timer-box .timer {
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
    }
    
    .quiz-timer-box .timer > span {
        margin: 0 1px !important;
    }

    .activities-container {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        margin-top: 25px;
    }
</style>
@section('content')
    <div class="container">
        <section class="mt-40">
            <h2 class="font-weight-bold font-16 text-dark-blue">{{ $quiz->title }}</h2>
            <p class="text-gray font-14 mt-5">
                <a href="{{ $quiz->webinar->getUrl() }}" target="_blank" class="text-gray">{{ $quiz->webinar->title }}</a>
                | {{ trans('public.by') }}
                <span class="font-weight-bold">
                    <a href="{{ $quiz->creator->getProfileUrl() }}" target="_blank" class="font-14"> {{ $quiz->creator->full_name }}</a>
                </span>
            </p>

            <div class="quiz-info-container">
                <div class="quiz-info-card">
                    <div class="card-icon-box" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                        <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/activity/58.svg" alt="">
                    </div>
                    <div class="card-value">{{ $quiz->pass_mark }}/{{ $quizQuestions->sum('grade') }}</div>
                    <div class="card-label">{{ trans('public.min') }} {{ trans('quiz.grade') }}</div>
                </div>

                <div class="quiz-info-card">
                    <div class="card-icon-box" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                        <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/activity/88.svg" alt="">
                    </div>
                    <div class="card-value">{{ $attempt_count }}/{{ $quiz->attempt }}</div>
                    <div class="card-label">{{ trans('quiz.attempts') }}</div>
                </div>

                <div class="quiz-info-card">
                    <div class="card-icon-box" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);">
                        <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/activity/47.svg" alt="">
                    </div>
                    <div class="card-value">{{ $totalQuestionsCount }}</div>
                    <div class="card-label">{{ trans('public.questions') }}</div>
                </div>

                <div class="quiz-info-card">
                    <div class="card-icon-box" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                        <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/activity/clock.svg" alt="">
                    </div>
                    <div class="card-value quiz-timer-box">
                        @if(!empty($quiz->time))
                            <div class="timer ltr" data-minutes-left="{{ $quiz->time }}"></div>
                        @else
                            {{ trans('quiz.unlimited') }}
                        @endif
                    </div>
                    <div class="card-label">{{ trans('quiz.remaining_time') }}</div>
                </div>
            </div>
        </section>

        <section class="mt-30 quiz-form">
            <form action="/panel/quizzes/{{ $quiz->id }}/store-result" method="post" class="">
                {{ csrf_field() }}
                <input type="hidden" name="quiz_result_id" value="{{ $newQuizStart->id }}" class="form-control" placeholder=""/>
                <input type="hidden" name="attempt_number" value="{{ $attempt_count }}" class="form-control" placeholder=""/>

                <div class="d-flex align-items-center mt-30 mb-30">
                    <button type="button" class="previous btn btn-sm btn-primary mr-20">{{ trans('quiz.previous_question') }}</button>
                    <button type="button" class="next btn btn-sm btn-primary mr-auto">{{ trans('quiz.next_question') }}</button>
                    <button type="submit" class="finish btn btn-sm btn-danger">{{ trans('public.finish') }}</button>
                </div>
                @foreach($quizQuestions as $key => $question)

                    <fieldset class="question-step question-step-{{ $key + 1 }}">
                        <div class="rounded-lg shadow-sm py-25 px-20">
                            <div class="quiz-card">

                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-gray font-14">
                                        <span>{{ trans('quiz.question_grade') }} : {{ $question->grade }} </span>
                                    </p>

                                    <div class="rounded-sm border border-gray200 p-15 text-gray">{{ $key + 1 }}/{{ $totalQuestionsCount }}</div>
                                </div>

                                @if(!empty($question->image) or !empty($question->video))
                                    <div class="quiz-question-media-card rounded-lg mt-10 mb-15">
                                        @if(!empty($question->image))
                                            <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $question->image }}" class="img-cover rounded-lg" alt="">
                                        @else
                                            <video id="questionVideo{{ $question->id }}" oncontextmenu="return false;" controlsList="nodownload" class="video-js" controls preload="auto" width="100%" data-setup='{"fluid": true}'>
                                                <source src="{{ $question->video }}" type="video/mp4"/>
                                            </video>
                                        @endif
                                    </div>
                                @endif

                                <div class="">
                                    <h3 class="font-weight-bold font-16 text-secondary">{{ $question->title }}</h3>
                                </div>

                                @if($question->type === \App\Models\QuizzesQuestion::$descriptive)
                                    <div class="form-group mt-35">
                                        <textarea name="question[{{ $question->id }}][answer]" rows="15" class="form-control"></textarea>
                                    </div>
                                @else
                                    <div class="question-multi-answers mt-35">
                                        @foreach($question->quizzesQuestionsAnswers as $key => $answer)
                                            <div class="answer-item">
                                                <input id="asw-{{ $answer->id }}" type="radio" name="question[{{ $question->id }}][answer]" value="{{ $answer->id }}">
                                                @if(!$answer->image)
                                                    <label for="asw-{{ $answer->id }}" class="answer-label font-16 text-dark-blue d-flex align-items-center justify-content-center">
                                                            <span class="answer-title">
                                                                {{ $answer->title }}
                                                            </span>
                                                    </label>
                                                @else
                                                    <label for="asw-{{ $answer->id }}" class="answer-label font-16 text-dark-blue d-flex align-items-center justify-content-center">
                                                        <div class="image-container">
                                                            <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ config('app_url') . $answer->image }}" class="img-cover" alt="">
                                                        </div>
                                                    </label>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </fieldset>
                @endforeach
 <div class="d-flex align-items-center mt-30" style="min-height:30px;">

      </div>

            </form>
        </section>

    </div>
@endsection

@push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/video/video.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/jquery.simple.timer/jquery.simple.timer.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/quiz-start.min.js"></script>
@endpush
