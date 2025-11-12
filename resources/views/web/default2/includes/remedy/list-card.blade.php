<div class="webinar-card webinar-list webinar-list-2 d-flex mt-30">
    <div class="image-box">
        @if($remedy->bestTicket() < $remedy->price)
            <span class="badge badge-danger">{{ trans('public.offer',['off' => $remedy->bestTicket(true)['percent']]) }}</span>
        @elseif(empty($isFeature) and !empty($remedy->feature))
            <span class="badge badge-warning">{{ trans('home.featured') }}</span>
        @elseif($remedy->type == 'remedy')
            @if($remedy->start_date > time())
                <span class="badge badge-primary">{{  trans('panel.not_conducted') }}</span>
            @elseif($remedy->isProgressing())
                <span class="badge badge-secondary">{{ trans('webinars.in_progress') }}</span>
            @else
                <span class="badge badge-secondary">{{ trans('public.finished') }}</span>
            @endif
        @else
            <span class="badge badge-primary">{{ trans('webinars.'.$remedy->type) }}</span>
        @endif

        <a href="{{ $remedy->getUrl() }}">
            <img src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}" class="img-cover" alt="{{ $remedy->title }}">
        </a>

        <div class="progress-and-bell d-flex align-items-center">

            @if($remedy->type == 'remedy')
                <a href="{{ $remedy->addToCalendarLink() }}" target="_blank" class="webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class="webinar-icon"></i>
                </a>
            @endif

            @if($remedy->type == 'remedy')
                <div class="progress ml-10">
                    <span class="progress-bar" style="width: {{ $remedy->getProgress() }}%"></span>
                </div>
            @endif
        </div>
    </div>

    <div class="webinar-card-body w-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ $remedy->getUrl() }}">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($remedy->title,'title') }}</h3>
            </a>
        </div>

        @if(!empty($remedy->category))
            <span class="d-block font-14 mt-10">{{ trans('public.in') }} <a href="{{ $remedy->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $remedy->category->title }}</a></span>
        @endif

        <div class="user-inline-avatar d-flex align-items-center mt-10">
            <div class="avatar bg-gray200">
                <img src="{{ config('app.img_dynamic_url') }}{{ $remedy->teacher->getAvatar() }}" class="img-cover" alt="{{ $remedy->teacher->full_name }}">
            </div>
            <a href="{{ $remedy->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $remedy->teacher->full_name }}</a>
        </div>

        @include(getTemplate() . '.includes.remedy.rate',['rate' => $remedy->getRate()])

        <div class="d-flex justify-content-between mt-auto">
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                    <span class="duration ml-5 font-14">{{ convertMinutesToHourAndMinute($remedy->duration) }} {{ trans('home.hours') }}</span>
                </div>

                <div class="vertical-line h-25 mx-15"></div>

                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                    <span class="date-published ml-5 font-14">{{ dateTimeFormat(!empty($remedy->start_date) ? $remedy->start_date : $remedy->created_at,'j M Y') }}</span>
                </div>
            </div>

            <div class="webinar-price-box d-flex flex-column justify-content-center align-items-center">
            @if(!empty($remedy->price) and $remedy->price > 0)
                    @if($remedy->bestTicket() < $remedy->price)
                        <span class="off">{{ handlePrice($remedy->price, true, true, false, null, true) }}</span>
                        <span class="real">{{ handlePrice($remedy->bestTicket(), true, true, false, null, true) }}</span>
                    @else
                        <span class="real">{{ handlePrice($remedy->price, true, true, false, null, true) }}</span>
                    @endif
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
