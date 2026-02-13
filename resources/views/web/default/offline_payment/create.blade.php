@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">Submit Payment Details</h2>
            <a href="{{ route('webinar', $webinar->id) }}" class="btn btn-sm btn-primary mt-3 mt-md-0">
                <i class="fa fa-arrow-left mr-1"></i>
                Back to Course
            </a>
        </div>

        <div class="mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-white">
            
            @if($pendingPayment)
                <div class="alert alert-warning">
                    <h5><i class="fa fa-exclamation-triangle"></i> Pending Payment</h5>
                    <p>You already have a pending payment submission for this course. Our team is currently reviewing it.</p>
                    <p><strong>UTR Number:</strong> {{ $pendingPayment->getUtrNumber() }}</p>
                    <p><strong>Submitted:</strong> {{ $pendingPayment->created_at->format('j M Y H:i') }}</p>
                    <p><strong>Status:</strong> <span class="badge badge-warning">{{ $pendingPayment->getStatusLabel() }}</span></p>
                    <hr>
                    <a href="{{ route('offline_payment.show', $pendingPayment->id) }}" class="btn btn-outline-primary">
                        View Payment Details
                    </a>
                </div>
            @else

            <div class="row">
                <div class="col-12 col-lg-8">
                    <h4 class="mb-3">Course Information</h4>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">{{ $webinar->title }}</h5>
                            <p class="card-text text-muted">by {{ $webinar->creator->full_name }}</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Course Price:</strong> 
                                    <span class="text-success h5">{{ $webinar->getFormattedPrice() }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Duration:</strong> {{ $webinar->duration ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-3">Payment Details</h4>
                    <form id="paymentForm" method="POST" action="{{ route('offline_payment.store', $webinar->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label class="input-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" 
                                       value="{{ $webinar->price }}" required>
                            </div>
                            <small class="form-text text-muted">Enter the exact amount you paid</small>
                        </div>

                        <div class="form-group">
                            <label class="input-label">UTR / Transaction Number <span class="text-danger">*</span></label>
                            <input type="text" name="utr_number" class="form-control" required
                                   placeholder="Enter your UTR or transaction number">
                            <small class="form-text text-muted">
                                Unique Transaction Reference number from your payment receipt
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="input-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required
                                   max="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label class="input-label">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" class="form-control" required
                                   placeholder="e.g., State Bank of India, HDFC Bank, etc.">
                        </div>

                        <div class="form-group">
                            <label class="input-label">Payment Screenshot <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" name="screenshot" class="custom-file-input" id="screenshotInput" 
                                       accept="image/*" required>
                                <label class="custom-file-label" for="screenshotInput">
                                    Choose payment screenshot...
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Upload a clear screenshot of your payment receipt (JPG/PNG, max 5MB)
                            </small>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>

                        <div class="form-group">
                            <label class="input-label">Remark / Note (Optional)</label>
                            <textarea name="remark" class="form-control" rows="3" 
                                      placeholder="Any additional information about your payment..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fa fa-info-circle"></i> Important Information</h6>
                            <ul class="mb-0">
                                <li>Your payment will be verified by our team within 24-48 hours</li>
                                <li>Course access will be granted once payment is confirmed</li>
                                <li>Keep your payment receipt handy for reference</li>
                                <li>For any issues, contact our support team</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fa fa-paper-plane mr-2"></i>Submit Payment Details
                        </button>

                        <div id="processingMessage" style="display: none;" class="mt-3">
                            <div class="alert alert-info">
                                <i class="fa fa-spinner fa-spin mr-2"></i>
                                Processing your payment submission...
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fa fa-question-circle"></i> How to Submit Payment</h5>
                        </div>
                        <div class="card-body">
                            <ol class="small">
                                <li class="mb-2">Make the payment for the course amount</li>
                                <li class="mb-2">Take a clear screenshot of the payment receipt</li>
                                <li class="mb-2">Note down the UTR/Transaction number</li>
                                <li class="mb-2">Fill in all the required details above</li>
                                <li class="mb-2">Upload the payment screenshot</li>
                                <li class="mb-2">Submit the form and wait for verification</li>
                            </ol>
                            
                            <hr>
                            
                            <h6>Payment Methods Accepted:</h6>
                            <ul class="small">
                                <li>Bank Transfer</li>
                                <li>UPI Payment</li>
                                <li>Cash Deposit</li>
                                <li>Mobile Banking</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @endif
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            // Handle file input change
            $('#screenshotInput').change(function(e) {
                var file = e.target.files[0];
                var preview = $('#imagePreview');
                
                if (file) {
                    // Check file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        $(this).val('');
                        preview.empty();
                        return;
                    }
                    
                    // Check file type
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file');
                        $(this).val('');
                        preview.empty();
                        return;
                    }
                    
                    // Show preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html('<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 200px;">');
                    };
                    reader.readAsDataURL(file);
                    
                    // Update label
                    $(this).next('.custom-file-label').text(file.name);
                } else {
                    preview.empty();
                    $(this).next('.custom-file-label').text('Choose payment screenshot...');
                }
            });
            
            // Handle form submission
            $('#paymentForm').submit(function(e) {
                e.preventDefault();
                
                var submitBtn = $('#submitBtn');
                var processingMsg = $('#processingMessage');
                
                // Show processing message
                submitBtn.prop('disabled', true);
                processingMsg.show();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Submitted!',
                                text: response.message,
                                confirmButtonText: 'View Payment Details'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.redirect_url;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'An error occurred while submitting your payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        // Hide processing message
                        submitBtn.prop('disabled', false);
                        processingMsg.hide();
                    }
                });
            });
        });
    </script>
@endpush
