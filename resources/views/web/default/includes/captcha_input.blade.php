<div class="form-group">
    <label class="input-label font-weight-500 homehide">{{ trans('site.captcha') }}*</label>
    <div class="row align-items-center">
        <div class="col-5">
            <input type="text" name="captcha" placeholder="Captcha here" class="form-control @error('captcha')  is-invalid @enderror">
            @error('captcha')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="col-5 d-flex align-items-center">
            <img loading="lazy" id="captchaImageComment" class="captcha-image" src="{{ captcha_src() }}">

            <button type="button" id="refreshCaptcha" class="btn-transparent ml-10">
                <i data-feather="refresh-ccw" width="24" height="24" class=""></i>
            </button>
        </div>
    </div>
</div>
<script  >
    $('#refreshCaptcha').click(function(){
        $('#captchaImageComment').attr('src', '{{ captcha_src() }}' + '?' + Math.random());
    });
</script>