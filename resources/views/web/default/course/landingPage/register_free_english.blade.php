@extends(getTemplate().'.layouts.app')

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
            <div class="col-12 col-md-6 pl-0"  style="display: flex;flex-wrap: nowrap;align-items: center;">
                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}/store/1/default_images/01.jpg"  style="height:auto;" class="img-cover" alt="Login">
            </div>
            <div class="col-12 col-md-6">
                <div class="login-card">
                    <h1 class="font-20 font-weight-bold">Create Your Account</h1>

                    <form method="post" action="/register-free/learn-free-astrology-course-english" class="mt-35">
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
                            <input name="full_name" maxlength="50" type="text" value="{{ old('full_name') }}" class="form-control @error('full_name') is-invalid @enderror">
                            @error('full_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                         @if($registerMethod == 'mobile')

                           <div class="row">
                                <div class="col-5">
                                    <div class="form-group">
                                        <label class="input-label" for="mobile">{{ trans('auth.country') }}:</label>
                                        <select name="country_code" class="form-control select2 @error('country_code') is-invalid @enderror">
                                            <option value="">{{ trans('public.select') }}</option>
                                            @foreach(getCountriesMobileCode() as $country => $code)

                                                <option value="{{ $code }}">{{ $country }}</option>
                                            @endforeach
                                        </select>

                                        @error('country_code')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-7">
                                    <div class="form-group">
                                        <label class="input-label" for="mobile">{{ trans('auth.mobile') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}:</label>
                                        <input name="mobile" maxlength="10" type="text" class="form-control @error('mobile') is-invalid @enderror"
                                               value="{{ old('mobile') }}" id="mobile" aria-describedby="mobileHelp">

                                        @error('mobile')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if($showOtherRegisterMethod)
                                @include('web.default.auth.register_includes.email_field',['optional' => true])
                            @endif
                        @else

                        <div class="row">
                                <div class="col-5">
                                    <div class="form-group">
                                        <label class="input-label" for="mobile">{{ trans('auth.country') }}:</label>
                                        <select name="country_code" class="form-control select2 @error('country_code') is-invalid @enderror">
                                            <option value="">{{ trans('public.select') }}</option>
                                            @foreach(getCountriesMobileCode() as $country => $code)
                                             <option value="{{ $code }}" @if($code == old('country_code')) selected @endif>{{ $country }}</option>

                                            @endforeach
                                        </select>

                                        @error('country_code')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-7">
                                    <div class="form-group">
                                        <label class="input-label" for="mobile">{{ trans('auth.mobile') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}:</label>
                                        <input name="mobile" maxlength="10" type="text" class="form-control @error('mobile') is-invalid @enderror"
                                               value="{{ old('mobile') }}" id="mobile" aria-describedby="mobileHelp">

                                        @error('mobile')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                             @include('web.default.auth.register_includes.email_field')
                        @endif
                    <div class="form-group">
                        <label class="input-label" for="password">{{ trans('auth.create_password') }}:</label>
                        <div class="input-group">
                            <input name="password" type="password" maxlength="40"
                                   class="form-control @error('password') is-invalid @enderror" id="password">
                            <div class="input-group-append">
                                <button type="button" class="toggle-password" data-toggle="#password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Password must be at least 6 characters.</small>
                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="input-label" for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                        <div class="input-group">
                            <input name="password_confirmation" type="password" maxlength="40"
                                   class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm_password">
                            <div class="input-group-append">
                                <button type="button" class="toggle-password" data-toggle="#confirm_password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small id="password-match-error" style="color:red; display:none;">Passwords do not match.</small>
                       <small id="password-match-success" style="color:green; display:none;">Passwords match.</small>
                        @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                        @if($showCertificateAdditionalInRegister)
                            <div class="form-group">
                                <label class="input-label" for="certificate_additional">{{ trans('update.certificate_additional') }}</label>
                                <input name="certificate_additional" id="certificate_additional" class="form-control @error('certificate_additional') is-invalid @enderror"/>
                                @error('certificate_additional')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        @endif

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

                        @if(!empty($referralSettings) and $referralSettings['status'])
                            <div class="form-group ">
                                <label class="input-label" for="referral_code">{{ trans('financial.referral_code') }}:</label>
                                <input name="referral_code" type="text"
                                       class="form-control @error('referral_code') is-invalid @enderror" id="referral_code"
                                       value="{{ !empty($referralCode) ? $referralCode : old('referral_code') }}"
                                       aria-describedby="confirmPasswordHelp">
                                @error('referral_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        @endif

                        @if(!empty(getGeneralSecuritySettings('captcha_for_register')))
                            @include('web.default.includes.captcha_input')
                        @endif

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="term" value="1" {{ (!empty(old('term')) and old('term') == '1') ? 'checked' : '' }} class="custom-control-input @error('term') is-invalid @enderror" id="term">
                            <label class="custom-control-label font-14" for="term">{{ trans('auth.i_agree_with') }}
                                <a href="pages/terms" target="_blank" class="text-secondary font-weight-bold font-14">{{ trans('auth.terms_and_rules') }}</a>
                            </label>

                            @error('term')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        @error('term')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <button type="submit" class="btn btn-primary btn-block mt-20">{{ trans('auth.signup') }}</button>
                    </form>

                    <div class="text-center mt-20">
                        <span class="text-secondary">
                            {{ trans('auth.already_have_an_account') }}
                            <a href="/login-free-english" class="text-secondary font-weight-bold">{{ trans('auth.login') }}</a>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
<script >
$(document).ready(function() {

    $(".toggle-password").click(function() {
        let input = $($(this).data("toggle"));
        if(input.attr("type") === "password") {
            input.attr("type", "text");
            $(this).find("i").removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.attr("type", "password");
            $(this).find("i").removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

    function checkPasswordMatch() {
        let password = $("#password").val();
        let confirmPassword = $("#confirm_password").val();

        if(confirmPassword === '') {
            $("#password-match-error").hide();
            $("#password-match-success").hide();
            $("#confirm_password").css('border-color', '');
            return;
        }

        if(password === confirmPassword) {
            $("#password-match-error").hide();
            $("#password-match-success").show();
            $("#confirm_password").css('border-color', 'green');
        } else {
            $("#password-match-success").hide();
            $("#password-match-error").show();
            $("#confirm_password").css('border-color', 'red');
        }
    }

    $("#password, #confirm_password").on('input', checkPasswordMatch);
});
</script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
@endpush
@push('styles_top')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush
