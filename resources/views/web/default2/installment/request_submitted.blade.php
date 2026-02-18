@extends('web.default2.layouts.app')
@push('scripts_top')
<?php $paymentType = request()->get('payment_type'); ?>
@endpush
@php
    $userId = request()->get('user_id') ?? auth()->id();
    $lmsUrl = config('app.manual_base_url') . '/panel?user_id=' . $userId;
@endphp
@section('content')
    @if(isset($_GET['payment_status']))
        <div class="container mt-20 my-50">
            <div class="row align-items-center justify-content-center">
                <div class="col-12 col-md-8">
                    <div class="installment-request-card d-flex align-items-center justify-content-center flex-column border rounded-lg">
<img src="/success.gif" alt="{{ trans('cart.success_pay_title') }}" width="267" height="265" style="width:400px; height:auto; mix-blend-mode:multiply;">
    
                        <h1 class="font-20 mt-30">{{ trans('cart.success_pay_title') }}</h1>
                        <p class="font-14 text-gray mt-5">{!! trans('cart.success_pay_msg') !!}</p>
    
                       <a href="/panel" class="btn btn-primary mt-15">{{ trans('public.my_panel') }}</a> 
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container mt-20 my-50">
            <div class="row align-items-center justify-content-center">
                <div class="col-12 col-md-8">
                    <div class="installment-request-card d-flex align-items-center justify-content-center flex-column border rounded-lg">
<img src="/success.gif" alt="{{ trans('cart.success_pay_title') }}" width="267" height="265"   style="width:400px; height:auto; mix-blend-mode:multiply;">
    
                        <h1 class="font-20 mt-30">{{ ucfirst(request()->get('payment_type', 'N/A')) }}
 Request Submitted</h1>
                       {{--  <p class="font-14 text-gray mt-5">{{ request()->get('payment_type') }} request</p> --}}
                   
                       
                        <a href=" {{ $lmsUrl }}" class="btn btn-primary mt-15" style="font-family: 'Inter', sans-serif !important;font-size:15px !important;">Dashboard</a> 
                    </div>
                </div>
            </div> 
        </div>
    @endif
@endsection
