@extends('web.default2.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/video/video-js.min.css">
@endpush

@section('content')
    <div class="container pt-50 mt-10">
        <div class="text-center">
            <h1 class="font-36">{{ $overviewTitle }}</h1>
            <p class="mt-10 font-16 text-gray">{{ trans('update.select_an_installment_plan') }}</p>
        </div>

        @foreach($installments as $installmentRow)
            @include('web.default2.installment.card',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach

    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/video.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/installment_verify.min.js"></script>
@endpush
