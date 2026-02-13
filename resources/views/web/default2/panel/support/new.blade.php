@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <form method="post" action="/panel/support/store">
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
                        <option value="installment_restructure" @if($errors->has('installment_order_id')) selected @endif>Installment Restructure Request</option>
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

                <div id="installmentInput" class="form-group @if(!$errors->has('installment_order_id')) d-none @endif">
                    <label class="input-label d-block">Select Installment Order</label>
                    <select name="installment_order_id" id="installmentOrders" class="form-control select2 @error('installment_order_id')  is-invalid @enderror">
                        <option value="" selected disabled>Select your installment order</option>
                    </select>
                    @error('installment_order_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div id="installmentStepInput" class="form-group @if(!$errors->has('installment_step_id')) d-none @endif">
                    <label class="input-label d-block">Select Installment Step to Restructure</label>
                    <select name="installment_step_id" id="installmentSteps" class="form-control select2 @error('installment_step_id')  is-invalid @enderror">
                        <option value="" selected disabled>Select installment step</option>
                    </select>
                    @error('installment_step_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div id="restructureReasonInput" class="form-group @if(!$errors->has('reason')) d-none @endif">
                    <label class="input-label d-block">Reason for Restructure Request</label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Please explain why you need to restructure this installment step...">{{ old('reason') }}</textarea>
                    @error('reason')
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
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/conversations.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle support type change
        $('#supportType').on('change', function() {
            var type = $(this).val();
            
            // Hide all conditional fields
            $('#departmentInput').addClass('d-none');
            $('#courseInput').addClass('d-none');
            $('#installmentInput').addClass('d-none');
            $('#installmentStepInput').addClass('d-none');
            $('#restructureReasonInput').addClass('d-none');
            
            // Show relevant fields based on type
            if (type === 'platform_support') {
                $('#departmentInput').removeClass('d-none');
            } else if (type === 'course_support') {
                $('#courseInput').removeClass('d-none');
            } else if (type === 'installment_restructure') {
                $('#installmentInput').removeClass('d-none');
                loadInstallmentOrders();
            }
        });
        
        // Load installment orders when restructure is selected
        function loadInstallmentOrders() {
            $.get('/panel/support/installment-orders', function(data) {
                $('#installmentOrders').empty().append('<option value="" selected disabled>Select your installment order</option>');
                
                if (data.orders && data.orders.length > 0) {
                    $.each(data.orders, function(index, order) {
                        $('#installmentOrders').append('<option value="' + order.id + '">' + order.course_title + ' - Order #' + order.id + '</option>');
                    });
                } else {
                    $('#installmentOrders').append('<option value="" disabled>No active installment orders found</option>');
                }
            });
        }
        
        // Load installment steps when order is selected
        $('#installmentOrders').on('change', function() {
            var orderId = $(this).val();
            
            if (orderId) {
                $.get('/panel/support/installment-steps/' + orderId, function(data) {
                    $('#installmentSteps').empty().append('<option value="" selected disabled>Select installment step</option>');
                    
                    if (data.steps && data.steps.length > 0) {
                        $.each(data.steps, function(index, step) {
                            var status = step.status === 'paid' ? '(Paid)' : '(Pending)';
                            $('#installmentSteps').append('<option value="' + step.id + '">Step ' + step.order + ': ₹' + step.amount + ' ' + status + '</option>');
                        });
                    } else {
                        $('#installmentSteps').append('<option value="" disabled>No installment steps found</option>');
                    }
                });
                
                $('#installmentStepInput').removeClass('d-none');
                $('#restructureReasonInput').removeClass('d-none');
            } else {
                $('#installmentStepInput').addClass('d-none');
                $('#restructureReasonInput').addClass('d-none');
            }
        });
        
        // Filter out paid steps
        $('#installmentSteps').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var stepText = selectedOption.text();
            
            if (stepText.includes('(Paid)')) {
                alert('You cannot restructure a paid installment step. Please select a pending step.');
                $(this).val('');
                return false;
            }
        });
    });
    </script>
@endpush
