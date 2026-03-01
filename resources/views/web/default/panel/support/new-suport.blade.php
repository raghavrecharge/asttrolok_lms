@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <style>
        .premium-form-container {
            background: #fff;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border: 1px solid #f8f8f8;
        }
        .input-label {
            font-size: 14px;
            font-weight: 700;
            color: #1f3b64;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 12px;
            border: 1px solid #eee;
            padding: 12px 15px;
            height: auto;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #1f3b64;
            box-shadow: 0 0 0 3px rgba(31, 59, 100, 0.05);
        }
        .glass-alert {
            background: rgba(31, 59, 100, 0.05);
            border: 1px solid rgba(31, 59, 100, 0.1);
            border-left: 5px solid #1f3b64;
            border-radius: 15px;
            padding: 20px;
            color: #1f3b64;
        }
        .glass-alert-warning {
            background: rgba(255, 193, 7, 0.05);
            border: 1px solid rgba(255, 193, 7, 0.1);
            border-left: 5px solid #ffc107;
            color: #856404;
        }
        .glass-alert-info {
            background: rgba(0, 123, 255, 0.05);
            border: 1px solid rgba(0, 123, 255, 0.1);
            border-left: 5px solid #007bff;
            color: #004085;
        }
        /* Aggressive Summernote Light Mode & Icon Fix */
        @font-face {
            font-family: "summernote";
            src: url("/assets/vendors/summernote/font/summernote.eot");
            src: url("/assets/vendors/summernote/font/summernote.eot#iefix") format("embedded-opentype"),
                 url("/assets/vendors/summernote/font/summernote.woff2") format("woff2"),
                 url("/assets/vendors/summernote/font/summernote.woff") format("woff"),
                 url("/assets/vendors/summernote/font/summernote.ttf") format("truetype");
        }

        .note-editor.note-frame {
            border-radius: 16px !important; 
            border: 1px solid #e5e7eb !important; 
            overflow: hidden;
            background-color: #ffffff !important;
            color: #334155 !important;
        }
        
        .note-editable {
            background-color: #ffffff !important;
            color: #1e293b !important;
            min-height: 200px;
        }

        .note-toolbar { 
            background: #f8fafc !important; 
            border-bottom: 1px solid #eef2f7 !important; 
            padding: 8px !important;
        }

        .note-btn {
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            color: #475569 !important;
            margin-right: 3px !important;
            padding: 5px 9px !important;
        }
        
        /* Force Summernote font for icons */
        .note-btn [class^="note-icon-"], .note-btn [class*=" note-icon-"] {
            font-family: "summernote" !important;
            font-style: normal !important;
            font-weight: normal !important;
            text-decoration: inherit;
        }
        
        .note-btn:hover { background: #f1f5f9 !important; }
        .note-btn.active { background: #e2e8f0 !important; }
        
        .note-dropdown-menu {
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }
        .btn-green {
            background: linear-gradient(135deg, #43d477 0%, #2ecc71 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 14px rgba(67, 212, 119, 0.2) !important;
        }
        .btn-green:hover {
            box-shadow: 0 6px 20px rgba(67, 212, 119, 0.3) !important;
            transform: translateY(-2px) !important;
            color: #fff !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush


@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Success/Error Toast Popup --}}
    @if(session('toast'))
    <div id="toastPopup" style="position: fixed; top: 20px; right: 20px; z-index: 10001; min-width: 300px; max-width: 400px;">
        <div class="alert alert-{{ session('toast.status') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show shadow-lg" role="alert" style="border-radius: 8px;">
            <strong style="font-weight: bold; color: #faf7f7ff;">{{ session('toast.title') }}</strong>
            <p class="mb-0 mt-2" style="color: #faf7f7ff;">{{ session('toast.msg') }}</p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
    <script>
        setTimeout(function() {
            const toast = document.getElementById('toastPopup');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s';
                setTimeout(() => toast.remove(), 500);
            }
        }, 5000);
    </script>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger shadow-sm mb-20" style="border-radius: 8px; border-left: 4px solid #dc3545;">
        <h6 class="font-weight-bold mb-2"><i class="fa fa-exclamation-triangle"></i> Please fix the following errors:</h6>
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="post" id="support-form" class="mb-80" action="/panel/support/newsuportforasttrolok/store" enctype="multipart/form-data">
        @csrf

        <section>
            <div class="d-flex align-items-center mb-25">
                <a href="{{ route('newsuportforasttrolok.index') }}" class="mr-15 text-gray">
                    <i data-feather="arrow-left" width="24" height="24"></i>
                </a>
                <h2 class="section-title mb-0">{{ trans('panel.create_support_message') }}</h2>
            </div>

            <div class="premium-form-container mt-25">

                {{-- Guest Information --}}
                @guest
                <div class="glass-alert mb-30">
                    <h5 class="font-16 font-weight-bold mb-10"><i data-feather="user" width="18" height="18" class="mr-5"></i>{{ trans('auth.your_information') }}</h5>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label">{{ trans('auth.full_name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="guest_name" value="{{ old('guest_name') }}" class="form-control" placeholder="John Doe" required/>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label">{{ trans('auth.email') }} <span class="text-danger">*</span></label>
                                <input type="email" name="guest_email" value="{{ old('guest_email') }}" class="form-control" placeholder="john@example.com" required/>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label">{{ trans('auth.mobile') }}</label>
                                <input type="text" name="guest_phone" value="{{ old('guest_phone') }}" class="form-control" placeholder="+91 9999999999"/>
                            </div>
                        </div>
                    </div>
                </div>
                @endguest

                <div class="row">
                    <div class="col-12">
                        {{-- Subject/Title --}}
                        <div class="form-group">
                            <label class="input-label">{{ trans('site.subject') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" 
                                   class="form-control @error('title') is-invalid @enderror" placeholder="Brief summary of your request" required/>
                        </div>
                    </div>
                </div>

                {{-- Description / Details --}}
                <div class="form-group">
                    <label class="input-label">Details / Reason <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="6" placeholder="Describe your issue or request in detail..." required>{{ old('description') }}</textarea>
                </div>

                {{-- Attachments --}}
                <div class="form-group mt-20">
                    <label class="input-label">{{ trans('panel.attach_file') }} (Optional)</label>
                    <div class="custom-file">
                        <input type="file" name="attachments[]" id="attachments" class="custom-file-input" multiple>
                        <label class="custom-file-label" for="attachments">Choose files...</label>
                    </div>
                </div>

                <div class="mt-30 text-right">
                    <button type="submit" id="supportSubmitBtn" class="btn btn-green px-30">
                        {{ trans('site.send_message') }}
                    </button>
                </div>
            </div>
        </section>
    </form>

@endsection

@push('scripts_bottom')
    <script>
        (function() {
            'use strict';

            // File attachments display
            $('#attachments').on('change', function() {
                if (this.files.length > 0) {
                    const names = Array.from(this.files).map(f => f.name);
                    $('.custom-file-label').text(this.files.length + ' file(s) selected');
                } else {
                    $('.custom-file-label').text('Choose files...');
                }
            });

            // Form submission handler — simple validation
            document.getElementById("support-form").addEventListener("submit", function(event) {
                let allErrors = [];

                const title = $('input[name="title"]').val();
                if (!title || !title.trim()) {
                    allErrors.push('Subject is required');
                }

                const description = $('textarea[name="description"]').val();
                if (!description || !description.trim()) {
                    allErrors.push('Details / Reason is required');
                }

                if (allErrors.length > 0) {
                    event.preventDefault();
                    $('#jsValidationErrors').remove();
                    let errorHtml = '<div id="jsValidationErrors" class="alert alert-danger shadow-sm mb-20" style="border-radius: 8px; border-left: 4px solid #dc3545;">';
                    errorHtml += '<h6 class="font-weight-bold mb-2"><i class="fa fa-exclamation-triangle"></i> Please fix the following errors:</h6><ul class="mb-0 pl-3">';
                    allErrors.forEach(function(err) { errorHtml += '<li>' + err + '</li>'; });
                    errorHtml += '</ul></div>';

                    document.getElementById("support-form").insertAdjacentHTML('beforebegin', errorHtml);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return false;
                }

                return true;
            });

        })();
    </script>
@endpush
