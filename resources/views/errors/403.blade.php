
@php
$agent = new \Jenssegers\Agent\Agent;
$layout = $agent->isMobile() ? 'web.default.layouts.app' : 'web.default2.layouts.app';
$baseUrl = config('app.manual_base_url');
$slug = $baseUrl . '/course/' . last(explode('/', url()->current()));
@endphp

@extends($layout)
@section('content')

    <section class="my-50 container text-center">
        <div class="row justify-content-md-center">
            <div class="col col-md-6">

               <h1>Access period has expired</h1>
               <!--<a href="{{ url('/') }}">Go to Homepage</a>-->
               @if(!empty($installUrl))
               <p>Your enrollment period for this course has expired.
                Renew your subscription to regain full access.</p>
                <a href="{{ $installUrl }}" 
                   class="btn btn-primary mt-3">
                    Pay Now
                    </a>
                @else
                <p>Your enrollment period for this course has expired.
                Renew your course to regain full access.</p>
                <a href="{{$slug}}" 
                   class="btn btn-primary mt-3">
                    Pay Now
                    </a>
                @endif
            </div>
        </div>

    </section>
@endsection
