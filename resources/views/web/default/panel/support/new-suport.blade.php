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
    </style>
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
                            <i data-feather="alert-circle" width="18" height="18" class="mr-5"></i>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Amount Paid (₹)</label>
                                    <input type="number" name="cash_amount" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Transaction ID / UTR</label>
                                    <input type="text" name="payment_receipt_number" class="form-control" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Date</label>
                                    <input type="date" name="payment_date" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Upload Receipt</label>
                                    <input type="file" name="payment_screenshot" class="form-control-file" disabled>
                                </div>
                            </div>
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
                                        <label class="input-label">Wrong Course</label>
                                        <select name="wrong_course_id" class="form-control select2" disabled>
                                            @foreach($userPurchases as $p) <option value="{{ $p->id }}">{{ $p->title }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label">Correct Course</label>
                                        <select name="correct_course_id" class="form-control select2" disabled>
                                            @foreach($webinars as $w) <option value="{{ $w->id }}">{{ $w->title }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                            @elseif($scen == 'relatives_friends_access' || $scen == 'mentor_access')
                                <div class="form-group">
                                    <label class="input-label">Select Course</label>
                                    <select name="webinar_id" class="form-control select2" disabled>
                                        @foreach($webinars as $w) <option value="{{ $w->id }}">{{ $w->title }}</option> @endforeach
                                    </select>
                                </div>
                            @elseif($scen == 'installment_restructure' || $scen == 'refund_payment' || $scen == 'post_purchase_coupon')
                                <div class="form-group">
                                    <label class="input-label">Select Course</label>
                                    <select name="webinar_id" class="form-control select2" disabled>
                                        @foreach($userPurchases as $p) <option value="{{ $p->id }}">{{ $p->title }}</option> @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group mt-15">
                                <label class="input-label">Details / Reason</label>
                                @if($scen == 'relatives_friends_access')
                                    <textarea name="relative_description" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @elseif($scen == 'mentor_access')
                                    <textarea name="mentor_change_reason" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @elseif($scen == 'wrong_course_correction')
                                    <textarea name="correction_reason" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @else
                                    <textarea name="description" class="form-control" rows="4" placeholder="Describe your request..." disabled></textarea>
                                @endif
                            </div>
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
                    <button type="submit" id="supportSubmitBtn" class="btn btn-primary px-30">
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
                'new_service_access': ['webinar_id', 'requested_service', 'service_details'],
                'refund_payment': ['purchase_to_refund', 'refund_reason', 'bank_account_number', 'ifsc_code', 'account_holder_name'],
                'post_purchase_coupon': ['webinar_id'],
                'wrong_course_correction': ['wrong_course_id', 'correct_course_id']
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

            // Extension limit check
            // function checkExtensionLimit() {
            //     const scenario = $('#supportScenario').val();
            //     const webinarId = $('.scenario-field[data-scenario="course_extension"] select[name="webinar_id"]').val();
            //     const warning = $('#extensionLimitWarning');
            //     const submitBtn = $('#supportSubmitBtn');

            //     if (scenario === 'course_extension' && webinarId) {
            //         const usedCount = window.courseExtensionCounts[webinarId] || 0;
                    
            //         if (usedCount >= 3) {
            //             warning.show();
            //             submitBtn.prop('disabled', true);
            //             return;
            //         }
            //     }
                
            //     warning.hide();
            //     submitBtn.prop('disabled', false);
            // }

            // // Watch for webinar selection in course extension
            // $(document).on('change', '.scenario-field[data-scenario="course_extension"] select[name="webinar_id"]', checkExtensionLimit);
            // $('#supportScenario').on('change', checkExtensionLimit);

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

            // Function to get scenario-specific fields
            function getScenarioFields(scenario) {
                const scenarioFields = {
                    'course_extension': ['extension_days', 'extension_reason'],
                    'temporary_access': ['pending_amount', 'expected_payment_date'],
                    'mentor_access': ['mentor_change_reason'],
                    'relatives_friends_access': ['description'],
                    'free_course_grant': ['free_course_reason'],
                    'offline_cash_payment': ['cash_amount', 'payment_receipt_number', 'payment_date', 'payment_location', 'payment_screenshot'],
                    'installment_restructure': ['installment_amount'],
                    'new_service_access': ['requested_service', 'service_details'],
                    'refund_payment': ['purchase_to_refund', 'refund_reason', 'bank_account_number', 'ifsc_code', 'account_holder_name'],
                    'post_purchase_coupon': [ 'coupon_apply_reason'],
                    'wrong_course_correction': ['wrong_course_id', 'correct_course_id', 'correction_reason']
                };
                
                return scenarioFields[scenario] || [];
            }

            // Simple jQuery change listener as backup
            $(document).ready(function() {
                // Initially set proper states - hide both fields
                $('#messageField').hide();
                $('#courseSelectionField').hide();
                
                $('#supportScenario').on('change', function() {
                    const selectedScenario = $(this).val();

                    console.log('Scenario selected:', selectedScenario);
                    
                    // Define scenarios that need course selection
                    const scenariosNeedingCourse = [
                        'course_extension', 
                        'temporary_access', 
                        'mentor_access', 
                        'relatives_friends_access', 
                        'free_course_grant', 
                        'offline_cash_payment', 
                        'installment_restructure', 
                        'new_service_access', 
                        'wrong_course_correction'
                    ];
                    
                    // Define scenarios that DON'T need course selection
                    const scenariosNotNeedingCourse = [
                        'refund_payment', 
                        'post_purchase_coupon'
                    ];
                    
                    // Hide/Show course selection field based on scenario
                    if (scenariosNeedingCourse.includes(selectedScenario)) {
                        $('#courseSelectionField').show();
                        $('#webinarSelect').attr('required', 'required');
                        console.log('Showing course field for scenario:', selectedScenario);
                    } else if (scenariosNotNeedingCourse.includes(selectedScenario)) {
                        $('#courseSelectionField').hide();
                        $('#webinarSelect').removeAttr('required');
                        $('#webinarSelect').val('').trigger('change');
                        console.log('Hiding course field for scenario:', selectedScenario);
                    } else {
                        // For general support or no scenario, show course field but not required
                        $('#courseSelectionField').show();
                        $('#webinarSelect').removeAttr('required');
                        console.log('Showing course field (optional) for scenario:', selectedScenario);
                    }
                    
                    // Hide message field when any scenario is selected (especially relatives_friends_access and mentor_access)
                    if (selectedScenario && (selectedScenario === 'relatives_friends_access' || selectedScenario === 'mentor_access')) {
                        $('#messageField').hide();  // Always hide when scenario selected
                        $('#messageField textarea').removeAttr('required');
                        // Add class to body for CSS targeting
                        $('body').addClass('scenario-' + selectedScenario);
                        // Add direct class and inline style to message field
                        $('#messageField').addClass('hide-relative').attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important; height: 0 !important; overflow: hidden !important;');
                        console.log('Hiding message field for scenario:', selectedScenario);
                    } else if (selectedScenario) {
                        $('#messageField').hide();  // Hide for other scenarios too
                        $('#messageField textarea').removeAttr('required');
                        // Add class to body for CSS targeting
                        $('body').addClass('scenario-' + selectedScenario);
                        console.log('Hiding message field for scenario:', selectedScenario);
                    } else {
                        $('#messageField').show();  // Only show when no scenario
                        $('#messageField textarea').attr('required', 'required');
                        // Remove scenario classes from body and message field
                        $('body').removeClass(function(index, className) {
                            return (className.match(/(^|\s)scenario-\S+/g) || []).join(' ');
                        });
                        $('#messageField').removeClass('hide-relative').removeAttr('style');
                        console.log('Showing message field (no scenario)');
                    }
                    
                    // Hide all scenario fields
                    $('.scenario-field').removeClass('active debug-visible');
                    
                    // Show selected scenario field
                    if (selectedScenario) {
                        const targetField = $(`.scenario-field[data-scenario="${selectedScenario}"]`);
                        targetField.addClass('active debug-visible');
                        console.log('Showing scenario field for:', selectedScenario);
                    }

                    // Handle special cases for webinar_id capture
                    const selectedWebinar = $('#webinarSelect').val();
                    if (selectedWebinar && (selectedScenario === 'relatives_friends_access' || selectedScenario === 'mentor_access')) {
                        // Remove any existing selectedWebinarDiv
                        $('#selectedWebinarDiv').remove();
                        
                        const inputDiv = `
                            <div class="form-group mt-2" id="selectedWebinarDiv">
                                <input type="hidden"
                                    name="selected_webinar_id"
                                    value="${selectedWebinar}">
                            </div>
                        `;

                        $('#courseSelectionField').after(inputDiv);
                        console.log('Added hidden input for webinar_id:', selectedWebinar, 'scenario:', selectedScenario);
                    } else {
                        // Remove the div if scenario changed
                        $('#selectedWebinarDiv').remove();
                    }
                });
                
                // Also listen to webinar select change to ensure value is captured
                $('#webinarSelect').on('change', function() {
                    const selectedWebinar = $(this).val();
                    const selectedScenario = $('#supportScenario').val();
                    
                    console.log('Webinar selected:', selectedWebinar, 'for scenario:', selectedScenario);
                    
                    if (selectedWebinar && (selectedScenario === 'relatives_friends_access' || selectedScenario === 'mentor_access')) {
                        $('#selectedWebinarDiv').remove();
                        const inputDiv = `
                            <div class="form-group mt-2" id="selectedWebinarDiv">
                                <input type="hidden"
                                    name="selected_webinar_id"
                                    value="${selectedWebinar}">
                            </div>
                        `;
                        $('#courseSelectionField').after(inputDiv);
                        console.log('Updated hidden input for webinar_id:', selectedWebinar, 'scenario:', selectedScenario);
                    }
                });
            });

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
            form.addEventListener("submit", function(event) {
                const selectedScenario = supportScenario.value;
                
                console.log('Form submitting with scenario:', selectedScenario);
                
                // Copy relative_description to description field for relatives scenario
                if (selectedScenario === 'relatives_friends_access') {
                    const relativeDesc = document.querySelector('textarea[name="relative_description"]');
                    const mainDesc = document.querySelector('textarea[name="description"]');
                    if (relativeDesc && mainDesc) {
                        mainDesc.value = relativeDesc.value;
                        console.log('Copied relative_description to description field');
                    }
                }
                
                // Collect all errors
                let allErrors = [];
                
                // Basic validation
                const title = document.querySelector('input[name="title"]');
                const description = document.querySelector('textarea[name="description"]');
                
                if (!title || !title.value.trim()) {
                    allErrors.push('Title is required');
                }
                
                // Only validate description if no scenario is selected
                if (!selectedScenario) {
                    if (!description || !description.value.trim()) {
                        allErrors.push('Description is required');
                    }
                }
                
                if (!selectedScenario) {
                    alert('Please select a support scenario');
                    e.preventDefault();
                    return false;
                }

                // Method 1: Disable all inactive scenario fields
                $('.scenario-field:not(.active)').find('input, select, textarea').prop('disabled', true).removeAttr('name');
                
                // Method 2: Ensure active scenario fields are enabled and have names
                $('.scenario-field.active').find('input, select, textarea').prop('disabled', false);

                // Method 3: Get active webinar_id if exists
                const activeWebinarId = $('.scenario-field.active [name="webinar_id"]').val();
                
                // Log for debugging
                // console.log('Selected Scenario:', selectedScenario);
                // console.log('Active webinar_id:', activeWebinarId);
                // console.log('All form data:', $(this).serialize());
                
                // Final validation - check if webinar_id is required for this scenario
                const scenariosNeedingWebinar = [
                    'course_extension', 'temporary_access', 'mentor_access', 
                    'relatives_friends_access', 'free_course_grant', 'offline_cash_payment',
                    'installment_restructure', 'new_service_access', 'wrong_course_correction'
                ];
                
                if (scenariosNeedingWebinar.includes(selectedScenario)) {
                    if (!activeWebinarId || activeWebinarId === '' ) {
                        if (selectedScenario === 'wrong_course_correction') {
                        return true;
                        } else {
                        alert('Please select a course for this scenario');
                        }
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Scenario-specific validations
                if (selectedScenario === 'offline_cash_payment') {
                    const cashAmount = document.querySelector('input[name="cash_amount"]');
                    const receiptNumber = document.querySelector('input[name="payment_receipt_number"]');
                    const paymentDate = document.querySelector('input[name="payment_date"]');
                    const paymentLocation = document.querySelector('input[name="payment_location"]');
                    const paymentScreenshot = document.querySelector('input[name="payment_screenshot"]');
                    
                    if (!cashAmount || !cashAmount.value || cashAmount.value <= 0) {
                        allErrors.push('Payment amount is required and must be greater than 0');
                    }
                    
                    if (!receiptNumber || !receiptNumber.value.trim()) {
                        allErrors.push('UTR/Transaction number is required');
                    }
                    
                    if (!paymentDate || !paymentDate.value) {
                        allErrors.push('Payment date is required');
                    }
                    
                    if (!paymentLocation || !paymentLocation.value.trim()) {
                        allErrors.push('Bank name is required');
                    }
                    
                    if (!paymentScreenshot || !paymentScreenshot.files || paymentScreenshot.files.length === 0) {
                        allErrors.push('Payment screenshot is required');
                    }
                }
                
                if (selectedScenario === 'refund_payment') {
                    const purchaseToRefund = document.querySelector('select[name="webinar_id"]');
                    const refundReason = document.querySelector('textarea[name="refund_reason"]');
                    const bankAccount = document.querySelector('input[name="bank_account_number"]');
                    const ifscCode = document.querySelector('input[name="ifsc_code"]');
                    const accountHolder = document.querySelector('input[name="account_holder_name"]');
                    
                    if (!purchaseToRefund || !purchaseToRefund.value) {
                        allErrors.push('Please select a purchase to refund');
                    }
                    
                    
                    
                    if (!refundReason || !refundReason.value || refundReason.value.trim() === '') {
                        allErrors.push('Reason for refund is required');
                    }
                    
                    if (!bankAccount || !bankAccount.value || bankAccount.value.trim() === '') {
                        allErrors.push('Bank account number is required');
                    }
                    
                    if (!ifscCode || !ifscCode.value || ifscCode.value.trim() === '') {
                        allErrors.push('IFSC code is required');
                    }
                    
                    if (!accountHolder || !accountHolder.value || accountHolder.value.trim() === '') {
                        allErrors.push('Account holder name is required');
                    }
                }
                
                if (selectedScenario === 'wrong_course_correction') {
                    const wrongCourse = document.querySelector('select[name="wrong_course_id"]');
                    const correctCourse = document.querySelector('select[name="correct_course_id"]');
                    const correctionReason = document.querySelector('textarea[name="correction_reason"]');
                    
                    if (!wrongCourse || !wrongCourse.value) {
                        allErrors.push('Please select the wrong course you purchased');
                    }
                    
                    if (!correctCourse || !correctCourse.value) {
                        allErrors.push('Please select the correct course you need');
                    }
                    
                    if (wrongCourse && correctCourse && wrongCourse.value === correctCourse.value) {
                        allErrors.push('Wrong course and correct course cannot be the same');
                    }
                    
                    if (!correctionReason || !correctionReason.value.trim()) {
                        allErrors.push('Reason for correction is required');
                    }
                }
                
                if (selectedScenario === 'course_extension') {
                    const extensionDays = document.querySelector('select[name="extension_days"]');
                    const extensionReason = document.querySelector('textarea[name="extension_reason"]');
                    
                    if (!extensionDays || !extensionDays.value || extensionDays.value <= 0) {
                        allErrors.push('Extension days must be greater than 0');
                    }
                    
                    if (!extensionReason || !extensionReason.value.trim()) {
                        allErrors.push('Reason for extension is required');
                    }
                }
                
                if (selectedScenario === 'temporary_access') {
                    // const pendingAmount = document.querySelector('input[name="pending_amount"]');
                    // const expectedPaymentDate = document.querySelector('input[name="expected_payment_date"]');
                    
                    // if (!pendingAmount || !pendingAmount.value || pendingAmount.value <= 0) {
                    //     allErrors.push('Pending amount must be greater than 0');
                    // }
                    
                    // if (!expectedPaymentDate || !expectedPaymentDate.value) {
                    //     allErrors.push('Expected payment date is required');
                    // }
                }
                
                if (selectedScenario === 'mentor_access') {
                    const mentorChangeReason = document.querySelector('textarea[name="mentor_change_reason"]');
                    
                    if (!mentorChangeReason || !mentorChangeReason.value.trim()) {
                        allErrors.push('Reason for mentor change is required');
                    }
                }
                
                if (selectedScenario === 'relatives_friends_access') {
                    const description = document.querySelector('textarea[name="relative_description"]');
                    
                    if (!description || !description.value.trim()) {
                        allErrors.push('Description is required for relatives/friends access');
                    }
                }
                
                if (selectedScenario === 'free_course_grant') {
                    const freeCourseReason = document.querySelector('textarea[name="free_course_reason"]');
                    
                    if (!freeCourseReason || !freeCourseReason.value.trim()) {
                        allErrors.push('Reason for free course grant is required');
                    }
                }
                
                
                if (selectedScenario === 'new_service_access') {
                    const requestedService = document.querySelector('input[name="requested_service"]');
                    const serviceDetails = document.querySelector('textarea[name="service_details"]');
                    
                    if (!requestedService || !requestedService.value.trim()) {
                        allErrors.push('Service name is required');
                    }
                    
                    if (!serviceDetails || !serviceDetails.value.trim()) {
                        allErrors.push('Service details are required');
                    }
                }
                
                
                // Show errors if any
                if (allErrors.length > 0) {
                    event.preventDefault();
                    // Remove any existing error alert
                    const existingAlert = document.getElementById('jsValidationErrors');
                    if (existingAlert) existingAlert.remove();
                    
                    // Build error HTML
                    let errorHtml = '<div id="jsValidationErrors" class="alert alert-danger shadow-sm mb-20" style="border-radius: 8px; border-left: 4px solid #dc3545;">';
                    errorHtml += '<h6 class="font-weight-bold mb-2"><i class="fa fa-exclamation-triangle"></i> Please fix the following errors:</h6><ul class="mb-0 pl-3">';
                    allErrors.forEach(function(err) { errorHtml += '<li>' + err + '</li>'; });
                    errorHtml += '</ul></div>';
                    
                    // Insert before the form
                    form.insertAdjacentHTML('beforebegin', errorHtml);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return false;
                }
                
                // Show loading
                if (screenBlocker && submissionPopup) {
                    screenBlocker.style.display = 'block';
                    submissionPopup.style.display = 'block';
                }
                
                // Allow normal form submission
                console.log('Form submitting successfully...');
                return true;
            });

        })();
    </script>
@endpush