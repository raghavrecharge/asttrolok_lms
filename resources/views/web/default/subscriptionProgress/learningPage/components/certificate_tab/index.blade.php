@php
    $hasCertificateItem=false;
@endphp

<div class="content-tab p-15 pb-50">
    @if($subscription->certificate)
        @php
            $hasCertificateItem = true;
        @endphp

        <div class="course-certificate-item cursor-pointer p-10 border border-gray200 rounded-sm mb-15" data-course-certificate="{{ !empty($subscriptionCertificate) ? $subscriptionCertificate->id : '' }}">
            <div class="d-flex align-items-center">
                <span class="chapter-icon bg-gray300 mr-10">
                    <i data-feather="award" class="text-gray" width="16" height="16"></i>
                </span>

                <div class="flex-grow-1">
                    <span class="font-weight-500 font-14 text-dark-blue d-block">{{ trans('update.subscription_certificate') }}</span>

                    <div class="d-flex align-items-center">
                        @if(!empty($subscriptionCertificate))
                            <span class="font-12 text-gray">{{ trans("public.date") }}: {{ dateTimeFormat($subscriptionCertificate->created_at, 'j F Y') }}</span>
                        @else
                            <span class="font-12 text-gray">{{ trans("update.not_achieve") }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @endif

    @if(!empty($subscription->quizzes) and count($subscription->quizzes))
        @foreach($subscription->quizzes as $subscriptionQuiz)
            @if($subscriptionQuiz->certificate)
                @php
                    $hasCertificateItem = true;
                @endphp

                <div class="certificate-item cursor-pointer p-10 border border-gray200 rounded-sm mb-15" data-result="{{ $subscriptionQuiz->result ? $subscriptionQuiz->result->id : '' }}">
                    <div class="d-flex align-items-center">
                        <span class="chapter-icon bg-gray300 mr-10">
                            <i data-feather="award" class="text-gray" width="16" height="16"></i>
                        </span>

                        <div class="flex-grow-1">
                            <span class="font-weight-500 font-14 text-dark-blue d-block">{{ $subscriptionQuiz->title }}</span>

                            <div class="d-flex align-items-center">
                                <span class="font-12 text-gray">{{ $subscriptionQuiz->pass_mark }}/{{ $subscriptionQuiz->quizQuestions->sum('grade') }}</span>

                                @if(!empty($subscriptionQuiz->result))
                                    <span class="font-12 text-gray ml-10">{{ dateTimeFormat($subscriptionQuiz->result->created_at, 'j M Y H:i') }}</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        @endforeach
    @endif

    @if(!$hasCertificateItem)
        <div class="learning-page-forum-empty d-flex align-items-center justify-content-center flex-column">
            <div class="learning-page-forum-empty-icon d-flex align-items-center justify-content-center">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/learning/certificate-empty.svg" class="img-fluid" alt="">
            </div>

            <div class="d-flex align-items-center flex-column mt-10 text-center">
                <h3 class="font-20 font-weight-bold text-dark-blue text-center">{{ trans('update.learning_page_empty_certificate_title') }}</h3>
                <p class="font-14 font-weight-500 text-gray mt-5 text-center">{{ trans('update.learning_page_empty_certificate_hint') }}</p>
            </div>
        </div>
    @endif
</div>
