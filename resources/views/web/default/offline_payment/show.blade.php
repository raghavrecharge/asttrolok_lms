@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">Payment Details</h2>
            <a href="{{ route('offline_payment.index') }}" class="btn btn-sm btn-primary mt-3 mt-md-0">
                <i class="fa fa-list mr-1"></i>
                My Payments
            </a>
        </div>

        <div class="mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-white">
            
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            {{-- Payment Status Alert --}}
            @if($payment->status === \App\Models\OfflinePayment::$approved)
                <div class="alert alert-success">
                    <h5><i class="fa fa-check-circle"></i> Payment Approved! 🎉</h5>
                    <p>Your payment has been verified and approved. You now have full access to the course.</p>
                    <div class="mt-3">
                        <a href="{{ route('webinar', $payment->webinar_id) }}" class="btn btn-success">
                            <i class="fa fa-play-circle mr-2"></i>Start Learning
                        </a>
                        <a href="{{ route('user.purchased_courses') }}" class="btn btn-outline-primary ml-2">
                            <i class="fa fa-list mr-2"></i>My Courses
                        </a>
                    </div>
                </div>
            @elseif($payment->status === \App\Models\OfflinePayment::$failed)
                <div class="alert alert-danger">
                    <h5><i class="fa fa-times-circle"></i> Payment Verification Failed</h5>
                    <p>Your payment could not be verified. Please contact support or resubmit payment details.</p>
                    @if($payment->admin_remark)
                        <hr>
                        <strong>Reason:</strong> {{ $payment->admin_remark }}
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('offline_payment.create', $payment->webinar_id) }}" class="btn btn-primary">
                            <i class="fa fa-redo mr-2"></i>Resubmit Payment
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-secondary ml-2">
                            <i class="fa fa-headset mr-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <h5><i class="fa fa-clock"></i> Payment Under Review</h5>
                    <p>Your payment details have been submitted and are currently being verified by our team.</p>
                    <p>This process usually takes 24-48 hours. You will be notified once the verification is complete.</p>
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-lg-8">
                    <h4 class="mb-3">Payment Information</h4>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Payment ID</label>
                                        <p class="mb-0"><strong>#{{ $payment->id }}</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">UTR Number</label>
                                        <p class="mb-0"><strong>{{ $payment->getUtrNumber() }}</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Amount Paid</label>
                                        <p class="mb-0 text-success h5">{{ $payment->getFormattedAmount() }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Payment Date</label>
                                        <p class="mb-0"><strong>{{ $payment->pay_date->format('j M Y') }}</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Bank</label>
                                        <p class="mb-0"><strong>{{ $payment->bank }}</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Status</label>
                                        <p class="mb-0">
                                            <span class="badge badge-{{ $payment->getStatusBadgeClass() }}">
                                                {{ $payment->getStatusLabel() }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($payment->admin_remark)
                                <hr>
                                <div class="mb-0">
                                    <label class="text-muted small">Admin Remarks</label>
                                    <p class="mb-0">{{ $payment->admin_remark }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <h4 class="mb-3">Course Information</h4>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                @if($payment->webinar->cover_img)
                                    <img src="{{ asset($payment->webinar->cover_img) }}" 
                                         class="rounded mr-3" style="width: 100px; height: 100px; object-fit: cover;">
                                @endif
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">{{ $payment->webinar->title }}</h5>
                                    <p class="text-muted mb-2">by {{ $payment->webinar->creator->full_name }}</p>
                                    <p class="card-text">{{ Str::limit($payment->webinar->description, 150) }}</p>
                                    <div class="mt-2">
                                        <span class="badge badge-info">{{ $payment->webinar->type ?? 'Course' }}</span>
                                        @if($payment->webinar->duration)
                                            <span class="badge badge-secondary ml-1">{{ $payment->webinar->duration }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-3">Payment Screenshot</h4>
                    
                    <div class="card">
                        <div class="card-body text-center">
                            @if($payment->getAttachmentUrl())
                                <a href="{{ $payment->getAttachmentUrl() }}" target="_blank">
                                    <img src="{{ $payment->getAttachmentUrl() }}" 
                                         class="img-thumbnail" style="max-width: 100%; max-height: 400px;">
                                </a>
                                <p class="mt-2">
                                    <a href="{{ $payment->getAttachmentUrl() }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-expand mr-1"></i>View Full Size
                                    </a>
                                </p>
                            @else
                                <p class="text-muted">No screenshot available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fa fa-info-circle"></i> Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Payment Submitted</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $payment->created_at->format('j M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($payment->processed_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $payment->status === \App\Models\OfflinePayment::$approved ? 'success' : 'danger' }}">
                                        <i class="fa fa-{{ $payment->status === \App\Models\OfflinePayment::$approved ? 'check' : 'times' }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>{{ $payment->getStatusLabel() }}</h6>
                                        <p class="text-muted small mb-0">
                                            {{ $payment->processed_at->format('j M Y H:i') }}
                                        </p>
                                        @if($payment->processedBy)
                                            <p class="text-muted small mb-0">
                                                by {{ $payment->processedBy->full_name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning">
                                        <i class="fa fa-clock"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>Waiting for Verification</h6>
                                        <p class="text-muted small mb-0">
                                            Usually takes 24-48 hours
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($payment->status === \App\Models\OfflinePayment::$pending)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fa fa-question-circle"></i> Need Help?</h5>
                            </div>
                            <div class="card-body">
                                <p class="small">If you have any questions about your payment or need to update payment details:</p>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-envelope mr-1"></i>Contact Support
                                    </a>
                                    <a href="{{ route('offline_payment.create', $payment->webinar_id) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fa fa-redo mr-1"></i>Submit New Payment
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <style>
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .timeline-content h6 {
            margin-bottom: 2px;
            font-size: 14px;
        }
        
        .timeline-content p {
            margin-bottom: 0;
        }
    </style>
@endsection
