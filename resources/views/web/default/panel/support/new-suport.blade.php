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
        .scenario-field {
            display: none;
            animation: fadeIn 0.4s ease;
        }
        .scenario-field.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
@endpush

@push('scripts_bottom')
<script>
    window.courseExtensionCounts = {!! json_encode($extensionCounts ?? []) !!};
</script>
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
                    <div class="col-12 col-md-6">
                        {{-- Subject/Title --}}
                        <div class="form-group">
                            <label class="input-label">{{ trans('site.subject') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" 
                                   class="form-control @error('title') is-invalid @enderror" placeholder="Brief summary of your request" required/>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        {{-- Support Scenario Selection --}}
                        <div class="form-group">
                            <label class="input-label d-block">{{ trans('panel.support_scenario') }} <span class="text-danger">*</span></label>
                            <select name="support_scenario" id="supportScenario" 
                                    class="form-control select2 @error('support_scenario') is-invalid @enderror" 
                                    data-placeholder="{{ trans('panel.select_support_scenario') }}" required>
                                <option value="">{{ trans('panel.select_support_scenario') }}</option>
                                <option value="course_extension">Course Extension</option>
                                <option value="temporary_access">Temporary Access</option>
                                <option value="mentor_access">Mentor Access</option>
                                <option value="relatives_friends_access">Relatives/Friends Access</option>
                                <option value="offline_cash_payment">Offline/Cash Payment</option>
                                <option value="installment_restructure">Installment Restructure</option>
                                <option value="refund_payment">Refund Payment</option>
                                <option value="post_purchase_coupon">Post-Purchase Coupon Apply</option>
                                <option value="wrong_course_correction">Wrong Course Correction</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Scenario fields wrapper with padding --}}
                <div class="scenario-fields-wrapper mt-10">
                    {{-- Course Extension --}}
                    <div class="scenario-field" data-scenario="course_extension">
                        <div class="glass-alert-warning mb-20 d-flex align-items-center">
                            <i data-feather="clock" width="20" height="20" class="mr-10"></i>
                            <span class="font-14">Only expired courses can be extended. Max 3 extensions per course.</span>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="input-label">Select Expired Course</label>
                                    <select name="webinar_id" class="form-control select2 extension-course-select" disabled>
                                        <option value="">{{ trans('panel.select_course') }}</option>
                                        @if(isset($expiredCourses))
                                            @foreach($expiredCourses as $course)
                                                @php $cnt = $extensionCounts[$course->id] ?? 0; @endphp
                                                <option value="{{ $course->id }}" data-extension-count="{{ $cnt }}" @if($cnt >= 3) disabled @endif>
                                                    {{ $course->title }} (Used: {{ $cnt }}/3)
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">Period</label>
                                    <select name="extension_days" class="form-control" disabled>
                                        <option value="7">7 Days</option>
                                        <option value="15">15 Days</option>
                                        <option value="30">30 Days</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Reason</label>
                            <textarea name="extension_reason" class="form-control" rows="3" disabled></textarea>
                        </div>
                    </div>

                    {{-- Temporary Access --}}
                    <div class="scenario-field" data-scenario="temporary_access">
                        <div class="glass-alert-info mb-20">
                            <i data-feather="alert-circle justify-content-center" width="18" height="18" class="mr-5"></i>
                            <span class="font-14">Request brief access while payment is being processed.</span>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="input-label">Select Overdue Course</label>
                                    <select name="webinar_id" class="form-control select2 temporary-course-select" disabled>
                                        <option value="">{{ trans('panel.select_course') }}</option>
                                        @if(isset($overdueCourses))
                                            @foreach($overdueCourses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">Duration</label>
                                    <select name="temporary_access_days" class="form-control" disabled>
                                        <option value="7">7 Days</option>
                                        <option value="15">15 Days</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <textarea name="temporary_access_reason" class="form-control" rows="3" placeholder="Reason..." disabled></textarea>
                    </div>

                    {{-- Offline Payment --}}
                    <div class="scenario-field" data-scenario="offline_cash_payment">
                        <div class="glass-alert mb-20">Submit payment screenshot for verification.</div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="input-label">Select Course <span class="text-danger">*</span></label>
                                    <select name="webinar_id" class="form-control select2" disabled>
                                        <option value="">{{ trans('panel.select_course') }}</option>
                                        @foreach($webinars as $w)
                                            <option value="{{ $w->id }}">{{ $w->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Amount Paid (₹) <span class="text-danger">*</span></label>
                                    <input type="number" name="cash_amount" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Transaction ID / UTR <span class="text-danger">*</span></label>
                                    <input type="text" name="payment_receipt_number" class="form-control" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Bank Name/Location <span class="text-danger">*</span></label>
                                    <input type="text" name="payment_location" class="form-control" placeholder="e.g. HDFC Bank, Delhi" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Upload Receipt <span class="text-danger">*</span></label>
                            <input type="file" name="payment_screenshot" class="form-control-file" id="paymentScreenshot" disabled>
                            <div id="screenshotPreview" class="mt-10"></div>
                        </div>
                    </div>`

                    {{-- Free Course Grant --}}
                    <div class="scenario-field" data-scenario="free_course_grant">
                        <div class="form-group">
                            <label class="input-label">Select Course <span class="text-danger">*</span></label>
                            <select name="webinar_id" class="form-control select2" disabled>
                                <option value="">{{ trans('panel.select_course') }}</option>
                                @foreach($webinars as $w)
                                    <option value="{{ $w->id }}">{{ $w->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-15">
                            <label class="input-label">Reason for Request <span class="text-danger">*</span></label>
                            <textarea name="free_course_reason" class="form-control" rows="4" placeholder="Describe why you should be granted this course..." disabled></textarea>
                        </div>
                    </div>

                    {{-- New Service Access --}}
                    <div class="scenario-field" data-scenario="new_service_access">
                        <div class="form-group">
                            <label class="input-label">Requested Service Name <span class="text-danger">*</span></label>
                            <input type="text" name="requested_service" class="form-control" placeholder="e.g. Personal Project Review" disabled>
                        </div>
                        <div class="form-group mt-15">
                            <label class="input-label">Service Details <span class="text-danger">*</span></label>
                            <textarea name="service_details" class="form-control" rows="4" placeholder="Describe the service you are looking for..." disabled></textarea>
                        </div>
                    </div>
                    
                    {{-- Generic dropdown for other scenarios --}}
                    @php
                        $genericScenarios = ['mentor_access', 'relatives_friends_access', 'installment_restructure', 'refund_payment', 'post_purchase_coupon', 'wrong_course_correction'];
                    @endphp
                    @foreach($genericScenarios as $scen)
                        <div class="scenario-field" data-scenario="{{ $scen }}">
                            @if($scen == 'wrong_course_correction')
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="input-label">Wrong Course <span class="text-danger">*</span></label>
                                        <select name="wrong_course_id" class="form-control select2" disabled>
                                            <option value="">Select Wrong Course</option>
                                            @foreach($userPurchases as $p) <option value="{{ $p->id }}">{{ $p->title }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label">Correct Course <span class="text-danger">*</span></label>
                                        <select name="correct_course_id" class="form-control select2" disabled>
                                            <option value="">Select Correct Course</option>
                                            @foreach($webinars as $w) <option value="{{ $w->id }}">{{ $w->title }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                            @elseif($scen == 'relatives_friends_access' || $scen == 'mentor_access')
                                <div class="form-group">
                                    <label class="input-label">Select Course <span class="text-danger">*</span></label>
                                    <select name="webinar_id" class="form-control select2" disabled>
                                        <option value="">{{ trans('panel.select_course') }}</option>
                                        @foreach($webinars as $w) <option value="{{ $w->id }}">{{ $w->title }}</option> @endforeach
                                    </select>
                                </div>
                            @elseif($scen == 'installment_restructure' || $scen == 'refund_payment' || $scen == 'post_purchase_coupon')
                                <div class="form-group">
                                    <label class="input-label">Select Purchased Course <span class="text-danger">*</span></label>
                                    <select name="webinar_id" class="form-control select2" disabled>
                                        <option value="">{{ trans('panel.select_course') }}</option>
                                        @foreach($userPurchases as $p) <option value="{{ $p->id }}">{{ $p->title }}</option> @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group mt-15">
                                <label class="input-label">Details / Reason <span class="text-danger">*</span></label>
                                @if($scen == 'relatives_friends_access')
                                    <textarea name="relative_description" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @elseif($scen == 'mentor_access')
                                    <textarea name="mentor_change_reason" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @elseif($scen == 'wrong_course_correction')
                                    <textarea name="correction_reason" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @elseif($scen == 'post_purchase_coupon')
                                    <textarea name="coupon_apply_reason" class="form-control" rows="4" placeholder="Reason for applying coupon post-purchase..." disabled></textarea>
                                @elseif($scen == 'installment_restructure')
                                    <textarea name="restructure_reason" class="form-control" rows="4" placeholder="Reason for installment restructuring..." disabled></textarea>
                                @elseif($scen == 'refund_payment')
                                    <textarea name="refund_reason" class="form-control" rows="4" placeholder="Reason for refund..." disabled></textarea>
                                @else
                                    <textarea name="description" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @endif
                            </div>

                            @if($scen == 'refund_payment')
                                <div class="glass-alert-info mt-20 mb-20 text-dark">
                                    <strong>Bank Details for Refund</strong>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="input-label">Bank Account Number <span class="text-danger">*</span></label>
                                            <input type="text" name="bank_account_number" class="form-control" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="input-label">IFSC Code <span class="text-danger">*</span></label>
                                            <input type="text" name="ifsc_code" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="input-label">Account Holder Name <span class="text-danger">*</span></label>
                                            <input type="text" name="account_holder_name" class="form-control" disabled>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
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
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    
    <script>
        (function() {
            'use strict';

             const form = document.getElementById("support-form");
            const supportScenario = document.getElementById("supportScenario");

            const webinarSelect = document.getElementById("webinarSelect");
            const screenBlocker = document.getElementById("screen-blocker");
            const submissionPopup = document.getElementById("submissionPopup");
            const attachmentsInput = document.getElementById("attachments");
            const fileList = document.getElementById("fileList");

            // Initialize Select2
            $(document).ready(function() {
                $('.select2').select2({
                    width: '100%'
                });

                // Initially disable all scenario fields
                $('.scenario-field input, .scenario-field select, .scenario-field textarea').prop('disabled', true);
            });

            // Scenario field mapping
            const scenarioFields = {
                'course_extension': ['webinar_id', 'extension_days', 'extension_reason'],
                'temporary_access': ['webinar_id', 'temporary_access_reason'],
                'mentor_access': ['webinar_id', 'requested_mentor_id', 'mentor_change_reason'],
                'relatives_friends_access': ['webinar_id', 'relative_name', 'relative_email', 'relative_phone', 'relative_relation'],
                'free_course_grant': ['webinar_id', 'free_course_reason'],
                'offline_cash_payment': ['webinar_id', 'cash_amount', 'payment_receipt_number', 'payment_date', 'payment_location', 'payment_screenshot'],
                'installment_restructure': ['webinar_id', 'restructure_reason'], // Uses purchased courses dropdown
                'new_service_access': ['requested_service', 'service_details'],
                'refund_payment': ['webinar_id', 'refund_reason', 'bank_account_number', 'ifsc_code', 'account_holder_name'],
                'post_purchase_coupon': ['webinar_id', 'coupon_apply_reason'],
                'wrong_course_correction': ['wrong_course_id', 'correct_course_id', 'correction_reason']
            };

            // Handle scenario change
            $('#supportScenario').on('change', function() {
                const selectedScenario = $(this).val();
                
                // Hide all scenario fields and disable their inputs
                $('.scenario-field').removeClass('active').hide();
                $('.scenario-field input, .scenario-field select, .scenario-field textarea').removeAttr('required').prop('disabled', true);
                
                // Show and enable only the selected scenario fields
                if (selectedScenario && scenarioFields[selectedScenario]) {
                    const $activeField = $(`.scenario-field[data-scenario="${selectedScenario}"]`);
                    $activeField.addClass('active').show();
                    
                    // Enable and make required fields required
                    $activeField.find('input, select, textarea').prop('disabled', false);
                    
                    scenarioFields[selectedScenario].forEach(fieldName => {
                        $activeField.find(`[name="${fieldName}"]`).attr('required', 'required');
                    });

                    // Re-initialize select2 for the active scenario
                    $activeField.find('.select2').select2({
                        width: '100%'
                    });

                    // Update generic webinar_id hidden field if a select inside the scenario has name="webinar_id"
                    $activeField.find('select[name="webinar_id"]').on('change', function() {
                        const val = $(this).val();
                        if (val) {
                             if ($('#webinar_id_generic_hidden').length === 0) {
                                 $('#support-form').append('<input type="hidden" name="webinar_id_generic" id="webinar_id_generic_hidden">');
                             }
                             $('#webinar_id_generic_hidden').val(val);
                        }
                    });
                }
            });


            // Extension limit check with new selector
                function checkExtensionLimit() {
                    const scenario = $('#supportScenario').val();
                    const selectedOption = $('.extension-course-select option:selected');
                    const extensionCount = parseInt(selectedOption.data('extension-count') || 0);
                    const warning = $('#extensionLimitWarning');
                    const submitBtn = $('#supportSubmitBtn');

                    if (scenario === 'course_extension' && selectedOption.val()) {
                        if (extensionCount >= 3) {
                            warning.show();
                            submitBtn.prop('disabled', true);
                            return false;
                        }
                    }
                    
                    warning.hide();
                    submitBtn.prop('disabled', false);
                    return true;
                }

                // Watch for course selection in extension scenario
                $(document).on('change', '.extension-course-select', checkExtensionLimit);
                $('#supportScenario').on('change', checkExtensionLimit);

            // Debug: Check if elements are found
            console.log('Form:', form);
            console.log('Support Scenario:', supportScenario);
            console.log('All scenario fields:', document.querySelectorAll('.scenario-field').length);

            // Initialize Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    width: '100%'
                });
            }

            // File attachments display
            $('#attachments').on('change', function() {
                const fileList = $('#fileList');
                fileList.html('');
                
                if (this.files.length > 0) {
                    const listContainer = $('<div class="mt-2"></div>');
                    
                    Array.from(this.files).forEach((file, index) => {
                        const fileItem = $('<div class="d-flex align-items-center justify-content-between p-2 mb-1"></div>')
                            .css({
                                'background-color': '#f8f9fa',
                                'border-radius': '4px'
                            });
                        
                        const fileName = $('<span class="text-truncate"></span>')
                            .text(`${index + 1}. ${file.name} (${formatFileSize(file.size)})`);
                        
                        fileItem.append(fileName);
                        listContainer.append(fileItem);
                    });
                    
                    fileList.append(listContainer);
                    $('.custom-file-label').text(`${this.files.length} file(s) selected`);
                }
            });

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }

            // Payment screenshot preview
            $('#paymentScreenshot').on('change', function(e) {
                const file = e.target.files[0];
                const preview = $('#screenshotPreview');
                preview.html('');
                
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        this.value = '';
                        return;
                    }
                    
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = $('<div></div>').html(`
                            <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">
                            <div class="mt-2">
                                <small class="text-muted">${file.name} (${formatFileSize(file.size)})</small>
                            </div>
                        `);
                        preview.append(previewDiv);
                    };
                    reader.readAsDataURL(file);
                    
                    $(this).next('.custom-file-label').text(file.name);
                }
            });

            // Form submission handler
            document.getElementById("support-form").addEventListener("submit", function(event) {
                const selectedScenario = $('#supportScenario').val();
                let allErrors = [];
                
                // Subject is always required
                const title = $('input[name="title"]').val();
                if (!title || !title.trim()) {
                    allErrors.push('Subject/Summary is required');
                }

                if (!selectedScenario) {
                    allErrors.push('Please select a support scenario');
                } else {
                    const $activeField = $(`.scenario-field[data-scenario="${selectedScenario}"]`);
                    
                    // Scenario-specific mandatory validation
                    if (selectedScenario === 'offline_cash_payment') {
                        if (!$activeField.find('select[name="webinar_id"]').val()) allErrors.push('Please select a course');
                        if (!$activeField.find('input[name="cash_amount"]').val()) allErrors.push('Payment amount is required');
                        if (!$activeField.find('input[name="payment_receipt_number"]').val()) allErrors.push('UTR/Transaction number is required');
                        if (!$activeField.find('input[name="payment_date"]').val()) allErrors.push('Payment date is required');
                        if (!$activeField.find('input[name="payment_location"]').val()) allErrors.push('Bank name/location is required');
                        if (!$activeField.find('input[name="payment_screenshot"]').val()) allErrors.push('Payment receipt screenshot is required');
                    } else if (selectedScenario === 'refund_payment') {
                        if (!$activeField.find('select[name="webinar_id"]').val()) allErrors.push('Please select a purchase to refund');
                        if (!$activeField.find('textarea[name="refund_reason"]').val().trim()) allErrors.push('Reason for refund is required');
                        if (!$activeField.find('input[name="bank_account_number"]').val().trim()) allErrors.push('Bank account number is required');
                        if (!$activeField.find('input[name="ifsc_code"]').val().trim()) allErrors.push('IFSC code is required');
                        if (!$activeField.find('input[name="account_holder_name"]').val().trim()) allErrors.push('Account holder name is required');
                    } else if (selectedScenario === 'wrong_course_correction') {
                        if (!$activeField.find('select[name="wrong_course_id"]').val()) allErrors.push('Please select the wrong course');
                        if (!$activeField.find('select[name="correct_course_id"]').val()) allErrors.push('Please select the correct course');
                        if (!$activeField.find('textarea[name="correction_reason"]').val().trim()) allErrors.push('Reason for correction is required');
                    } else if (selectedScenario === 'course_extension') {
                        if (!$activeField.find('select[name="webinar_id"]').val()) allErrors.push('Please select a course to extend');
                        if (!$activeField.find('textarea[name="extension_reason"]').val().trim()) allErrors.push('Reason for extension is required');
                    } else if (selectedScenario === 'free_course_grant') {
                        if (!$activeField.find('select[name="webinar_id"]').val()) allErrors.push('Please select a course');
                        if (!$activeField.find('textarea[name="free_course_reason"]').val().trim()) allErrors.push('Reason for request is required');
                    } else if (selectedScenario === 'new_service_access') {
                        if (!$activeField.find('input[name="requested_service"]').val().trim()) allErrors.push('Service name is required');
                        if (!$activeField.find('textarea[name="service_details"]').val().trim()) allErrors.push('Service details are required');
                    } else if (selectedScenario === 'relatives_friends_access' || selectedScenario === 'mentor_access') {
                        if (!$activeField.find('select[name="webinar_id"]').val()) allErrors.push('Please select a course');
                        const reasonField = selectedScenario === 'mentor_access' ? 'mentor_change_reason' : 'relative_description';
                        if (!$activeField.find(`textarea[name="${reasonField}"]`).val().trim()) allErrors.push('Reason/Description is required');
                    } else if (selectedScenario === 'post_purchase_coupon' || selectedScenario === 'installment_restructure' || selectedScenario === 'temporary_access') {
                        if (!$activeField.find('select[name="webinar_id"]').val()) allErrors.push('Please select a course');
                        const reasonField = (selectedScenario === 'post_purchase_coupon') ? 'coupon_apply_reason' : ((selectedScenario === 'installment_restructure') ? 'restructure_reason' : 'temporary_access_reason');
                        if (!$activeField.find(`textarea[name="${reasonField}"]`).val().trim()) allErrors.push('Reason/Description is required');
                    }
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