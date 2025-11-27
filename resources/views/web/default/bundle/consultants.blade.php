@extends('web.default2.layouts.app')
@section('content')
<section class="container mt-50">
    @forelse($consultations ?? [] as $c)
        @php
            $consultation = $c['consultation'];
            $consultants = collect($c['consultants'] ?? []);
        @endphp

        @if(!empty($consultation->consultation_type))
            <h3 class="mt-4">
                Consultation Type: {{ ucfirst($consultation->consultation_type) }}
                @if($consultation->consultation_type === 'range'
                    && !empty($consultation->starting_price)
                    && !empty($consultation->ending_price))
                    ({{ $consultation->starting_price }} - {{ $consultation->ending_price }})
                @endif
            </h3>
        @endif

        <div class="row justify-content-center">
            @forelse($consultants as $instructor)
                @php
                    $meeting = $instructor->meeting;
                    $canReserve = !empty($meeting)
                        && !$meeting->disabled
                        && !empty($meeting->meetingTimes)
                        && $instructor->meeting_times_count > 0;
                    $amount = $meeting->amount ?? 0;
                    $discount = $meeting->discount ?? 0;
                    $finalAmount = $amount - (($amount * $discount) / 100);
                @endphp

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="rounded-lg shadow-sm mt-20 px-25 py-15 course-teacher-card instructors-list text-left d-flex align-items-left flex-column position-relative">

                        <div class="row">
                            <div class="col-11 col-md-6 col-lg-11" style="padding:0;"></div>
                            <div class="col-1 col-md-6 col-lg-1" style="padding:0;">
                                @if($discount > 0)
                                    <span class="px-10 py-10 bg-danger off-label1 text-white font-12">{{ $discount }}% {{ trans('public.off') }}</span>
                                @else
                                    <span class="px-10 py-10 bg-primary off-label text-white font-12"></span>
                                @endif
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-3 col-md-6 col-lg-3" style="padding:0;">
                                <a href="{{ $instructor->getProfileUrl() }}{{ $canReserve ? '?tab=appointments' : '' }}" class="text-left d-flex flex-column align-items-left justify-content-left">
                                    <div class="teacher-avatar mt-5 position-relative">
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(190) }}" class="img-cover" alt="{{ $instructor->full_name }}">
                                        @if($instructor->offline)
                                            <span class="user-circle-badge unavailable">
                                                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                                            </span>
                                        @elseif($instructor->verified)
                                            <span class="user-circle-badge has-verified d-flex align-items-left justify-content-left">
                                                <i data-feather="check" width="20" height="20" class="text-white"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="stars-card d-flex align-items-center mt-10">
                                        @include('web.default2.includes.webinar.rate1',['rate' => $instructor->rating])
                                    </div>
                                </a>
                            </div>

                            <div class="col-9 col-md-6 col-lg-9">
                                <h3 class="font-16 font-weight-bold text-dark-blue text-left ml-10">{{ $instructor->full_name }}</h3>
                                @if(!empty($instructor->bio))
                                    <pre class="mt-10 font-13 text-dark-blue ml-10" style="font-family: var(--font-family-base) !important;">{{ $instructor->bio }}</pre>
                                @endif

                                <div class="row">
                                    <div class="col-7 col-md-7 col-lg-7">
                                        <div class="mt-15 pl-10">
                                            @if($amount > 0 && !$meeting->disabled)

                                                @if($discount > 0)
                                                    <span class="font-14 text-gray text-decoration-line-through ml-10">{{ handlePrice($amount / 30) }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-5 col-md-5 col-lg-5" style="padding:0;">
                                        <div class="align-items-right justify-content-right w-100">
                                            <a href="{{ route('bundle.consultation.profile', ['id' => $instructor->id, 'name' => Str::slug($instructor->full_name)]) }}?bundle_id={{ $bundle_id }}&bundle_webinar_id={{ $bundle_webinar_id }}&time={{ $time }}" class="btn btn-primary btn-block" style="padding:0px!important;height:36px;">
                                                Book Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
            @endforelse
        </div>
    @empty
    @endforelse
</section>
@endsection
