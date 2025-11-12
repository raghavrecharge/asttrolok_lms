@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
    <form method="post" id="support-form" class="mb-80" action="/panel/support/store">
        {{ csrf_field() }}

        <section>
            <h2 class="section-title">{{ trans('panel.create_support_message') }}</h2>

            <div class="mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-white">

                <div class="form-group">
                    <label class="input-label">{{ trans('site.subject') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title')  is-invalid @enderror"/>
                    @error('title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label d-block">{{ trans('public.type') }}</label>

                    <select name="type" id="supportType" class="form-control  @error('type')  is-invalid @enderror" data-allow-clear="false" data-search="false">
                        <option selected disabled></option>
                        <option value="course_support" @if($errors->has('webinar_id')) selected @endif>{{ trans('panel.course_support') }}</option>
                        <option value="platform_support" @if($errors->has('department_id')) selected @endif>{{ trans('panel.platform_support') }}</option>
                    </select>

                    @error('type')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div id="departmentInput" class="form-group @if(!$errors->has('department_id')) d-none @endif">
                    <label class="input-label d-block">{{ trans('panel.department') }}</label>

                    <select name="department_id" id="departments" class="form-control select2 @error('department_id')  is-invalid @enderror" data-allow-clear="false" data-search="false">
                        <option selected disabled></option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->title }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div id="courseInput" class="form-group @if(!$errors->has('webinar_id')) d-none @endif">
                    <label class="input-label d-block">{{ trans('product.course') }}</label>
                    <select name="webinar_id" class="form-control select2 @error('webinar_id')  is-invalid @enderror">
                        <option value="" selected disabled>{{ trans('panel.select_course') }}</option>

                        @foreach($webinars as $webinar)
                            <option value="{{ $webinar->id }}">{{ $webinar->title }} - {{ $webinar->creator->full_name }}</option>
                        @endforeach
                    </select>
                    @error('webinar_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label d-block">{{ trans('site.message') }}</label>
                    <textarea name="message" class="form-control" rows="15">{{ old('message') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-8 d-flex align-items-center">
                        <div class="form-group">
                            <label class="input-label">{{ trans('panel.attach_file') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" class="input-group-text panel-file-manager" data-input="attach" data-preview="holder">
                                        <i data-feather="arrow-up" width="18" height="18" class="text-white"></i>
                                    </button>
                                </div>
                                <input type="text" name="attach" id="attach" value="{{ old('attach') }}" class="form-control"/>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm ml-40 mt-10">{{ trans('site.send_message') }}</button>
                    </div>
                </div>
            </div>
        </section>
    </form>
    <!-- Screen Blocker -->
<div id="screen-blocker" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;"></div>

<!-- Popup Modal -->
<div id="submissionPopup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000; background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h4>Submitting...</h4>
    <p>Please wait while your message is being sent.</p>
    <div class="spinner" style="margin-top: 10px; border: 4px solid rgba(0, 0, 0, 0.2); border-top: 4px solid #007bff; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite;"></div>
</div>



@endsection

@push('scripts_bottom')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("support-form");
        const screenBlocker = document.getElementById("screen-blocker");
        const submissionPopup = document.getElementById("submissionPopup");

        form.addEventListener("submit", function (event) {
            let isValid = true;

            // Get inputs for validation
            const titleInput = form.querySelector("input[name='title']");
            const typeSelect = form.querySelector("select[name='type']");
            const messageTextarea = form.querySelector("textarea[name='message']");

            // Clear previous error styles
            [titleInput, typeSelect, messageTextarea].forEach(input => input.classList.remove("is-invalid"));

            // If valid, show blocker and popup
            screenBlocker.style.display = "block";
            submissionPopup.style.display = "block";

            // Simulate a delay for demo purposes (remove in production)
            event.preventDefault(); // Remove this in production
            form.submit(); // Actually submit the form
            setTimeout(() => {
                
                screenBlocker.style.display = "none";
                submissionPopup.style.display = "none";
            }, 10000); // Delay for 2 seconds
        });
    });
</script>


    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/conversations.min.js"></script>
@endpush
