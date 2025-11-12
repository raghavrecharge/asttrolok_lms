@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    @php
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        $showOtherRegisterMethod = getFeaturesSettings('show_other_register_method') ?? false;
        $showCertificateAdditionalInRegister = getFeaturesSettings('show_certificate_additional_in_register') ?? false;
        $selectRolesDuringRegistration = getFeaturesSettings('select_the_role_during_registration') ?? null;
    @endphp

    <div class="container">
        <div class="row login-container1">
            <!--<div class="col-12 col-md-6 pl-0"  style="display: flex;flex-wrap: nowrap;align-items: center;">-->
            <!--    <img src="{{ getPageBackgroundSettings('register') }}"  style="height:auto;" class="img-cover" alt="Login">-->
            <!--</div>-->
            <div class="col-12 col-md-12">
                <div class="login-card">
                    <h1 class="font-20 font-weight-bold">Payment by Razorpay</h1>

                    <form method="post" action="/razorpay/pay" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
              @if (session()->has('my_test_key'))
     <input type="hidden" name="rd" value="{{ session()->get('my_test_key') }}">
@endif
                        @if(!empty($selectRolesDuringRegistration) and count($selectRolesDuringRegistration))
                            <div class="form-group">
                                <label class="input-label">{{ trans('financial.account_type') }}</label>

                                <div class="d-flex align-items-center wizard-custom-radio mt-5">
                                    <div class="wizard-custom-radio-item flex-grow-1">
                                        <input type="radio" name="account_type" value="user" id="role_user" class="" checked>
                                        <label class="font-12 cursor-pointer px-15 py-10" for="role_user">{{ trans('update.role_user') }}</label>
                                    </div>

                                    @foreach($selectRolesDuringRegistration as $selectRole)
                                        <div class="wizard-custom-radio-item flex-grow-1">
                                            <input type="radio" name="account_type" value="{{ $selectRole }}" id="role_{{ $selectRole }}" class="">
                                            <label class="font-12 cursor-pointer px-15 py-10" for="role_{{ $selectRole }}">{{ trans('update.role_'.$selectRole) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                       

                        <div class="form-group">
                            <label class="input-label" for="full_name">{{ trans('auth.full_name') }}:</label>
                            <input name="full_name" type="text" value="{{ old('full_name') }}" class="form-control @error('full_name') is-invalid @enderror">
                            @error('full_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                         @if($registerMethod == 'mobile')
                            @include('web.default.auth.register_includes.mobile_field')

                            @if($showOtherRegisterMethod)
                                @include('web.default.auth.register_includes.email_field',['optional' => true])
                            @endif
                        @else
                        @include('web.default.auth.register_includes.mobile_field',['optional' => false])
                            

                            <!--@if($showOtherRegisterMethod)-->
                            <!--    @include('web.default.auth.register_includes.mobile_field',['optional' => true])-->
                            <!--@endif-->
                             @include('web.default.auth.register_includes.email_field')
                        @endif
                        
                     

                        <div class="form-group">
                            <label class="input-label" for="amount">Amount:</label>
                            <input name="amount" type="text"
                                   class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" id="amount"
                                   aria-describedby="passwordHelp">
                            @error('amount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

              
                

                        @if(getFeaturesSettings('timezone_in_register'))
                            @php
                                $selectedTimezone = getGeneralSettings('default_time_zone');
                            @endphp

                            <div class="form-group">
                                <label class="input-label">{{ trans('update.timezone') }}</label>
                                <select name="timezone" class="form-control select2" data-allow-clear="false">
                                    <option value="" {{ empty($user->timezone) ? 'selected' : '' }} disabled>{{ trans('public.select') }}</option>
                                    @foreach(getListOfTimezones() as $timezone)
                                        <option value="{{ $timezone }}" @if($selectedTimezone == $timezone) selected @endif>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        @endif

                  

                        

                        <button type="submit" class="btn btn-primary btn-block mt-20">Pay Now</button>
                    </form>

                  
                        
                    

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
@endpush
