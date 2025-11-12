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

        <!--<div class="d-flex align-items-center flex-column flex-lg-row mt-50 border rounded-lg p-15 p-lg-25">-->
        <!--    <div class="default-package-icon">-->
        <!--        <img src="{{ config('app.js_css_url') }}/assets2/default/img/become-instructor/default.png" class="img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">-->
        <!--    </div>-->

        <!--    <div class="ml-lg-25 w-100 mt-20 mt-lg-0">-->
        <!--        <h2 class="font-24 font-weight-bold text-dark-blue">{{ $overviewTitle }}</h2>-->

        <!--        <div class="d-flex flex-wrap align-items-center justify-content-between w-100">-->

                    <!--<div class="d-flex align-items-center mt-20">-->
                    <!--    <i data-feather="check-square" width="20" height="20" class="text-gray"></i>-->
                    <!--    <span class="font-14 text-gray ml-5">{{ handlePrice($cash) }} {{ trans('update.cash') }}</span>-->
                    <!--</div>-->

                    <!--<div class="d-flex align-items-center mt-20">-->
                    <!--    <i data-feather="menu" width="20" height="20" class="text-gray"></i>-->
                    <!--    <span class="font-14 text-gray ml-5">{{ $plansCount }} {{ trans('update.installment_plans') }}</span>-->
                    <!--</div>-->

        <!--            <div class="d-flex align-items-center mt-20">-->
                        
                        <!--<i data-feather="dollar-sign" width="20" height="20" class="text-gray"></i>-->
        <!--                <span class="font-14 text-gray ml-5">{{ handlePrice($minimumAmount) }} {{ trans('update.minimum_installment_amount') }}</span>-->
        <!--            </div>-->

        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->

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
