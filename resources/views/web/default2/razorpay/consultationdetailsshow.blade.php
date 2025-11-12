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
                    <h1 class="font-20 font-weight-bold">Birth Details</h1>

                    <form method="post" action="/razorpay/consultationdetails" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="order_id" value="{{$orderItem->order_id}}">
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

                       <div class="row">
    
    <div class="col-6">
                        <div class="form-group">
                            <label class="input-label" for="amount">Birth Date:</label>
                            <input name="birthdate" type="date" required
                                   class="form-control @error('amount') is-invalid @enderror" id="amount"
                                   aria-describedby="passwordHelp">
                            @error('amount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        </div>
                        <div class="col-6">
                        <div class="form-group">
                            <label class="input-label" for="amount">Birth Time:</label>
                            <input name="birthtime" type="time" required
                                   class="form-control @error('amount') is-invalid @enderror" id="amount"
                                   aria-describedby="passwordHelp">
                            @error('amount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        </div>
                        </div>
                        
                    
                        
                        <div class="form-group">
                            <label class="input-label" for="amount">Birth Place:</label>
                            <input name="birthplace" type="text" required
                                   class="form-control @error('amount') is-invalid @enderror" id="amount"
                                   aria-describedby="passwordHelp">
                            @error('amount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="input-label" for="full_name">{{ trans('auth.full_name') }}:</label>
                            <input name="full_name" type="text" value="{{ $orderItem->user->full_name }}" class="form-control @error('full_name') is-invalid @enderror">
                            @error('full_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                       
   <div class="row">
    <div class="col-5">
        <div class="form-group">
            <label class="input-label" for="mobile">{{ trans('auth.country') }}:</label>
            <select name="country_code" class="form-control select2">
                @foreach(getCountriesMobileCode() as $country => $code)
                 <!--<option value="{{ $code }}" @if($code == old('country_code')) selected @endif>{{ $country }}</option>-->
                    <option value="{{ $code }}" @if($code == '+91')) selected @endif>{{ $country }}</option>
                @endforeach
            </select>

            @error('mobile')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>

    <div class="col-7">
        <div class="form-group">
            <label class="input-label" for="mobile">{{ trans('auth.mobile') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}:</label>
            <input name="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror" required
                   value="{{ $contact_no ? $contact_no :$orderItem->user->mobile }}" id="mobile" aria-describedby="mobileHelp" maxlength="10">

            @error('mobile')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</div>
<div class="form-group">
    <label class="input-label" for="email">{{ trans('auth.email') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}:</label>
    <input name="email" type="text" class="form-control @error('email') is-invalid @enderror" required
           value="{{ $orderItem->user->email }}" id="email" aria-describedby="emailHelp">

    @error('email')
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

                  

                        

                        <button type="submit" class="btn btn-primary btn-block mt-20">Submit</button>
                    </form>

                  
                        
                    

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
@endpush
