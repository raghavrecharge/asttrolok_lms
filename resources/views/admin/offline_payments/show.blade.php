@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Payment Details - {{ $payment->getUtrNumber() }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.offline_payments.index') }}">Offline Payments</a></div>
                <div class="breadcrumb-item">{{ $payment->getUtrNumber() }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-8">
                    {{-- Payment Information --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Payment Information</h4>
                            <div class="card-header-action">
                                <span class="btn btn-{{ $payment->getStatusBadgeClass() }} btn-sm">
                                    {{ $payment->getStatusLabel() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Payment ID</label>
                                        <p class="mb-0"><strong>#{{ $payment->id }}</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">UTR Number</label>
                                        <p class="mb-0"><strong><code>{{ $payment->getUtrNumber() }}</code></strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Amount</label>
                                        <p class="mb-0 text-success h4">{{ $payment->getFormattedAmount() }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Bank</label>
                                        <p class="mb-0"><strong>{{ $payment->bank }}</strong></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small">Payment Date</label>
                                        <p class="mb-0"><strong>{{ $payment->pay_date->format('j M Y') }}</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Submitted On</label>
                                        <p class="mb-0"><strong>{{ $payment->created_at->format('j M Y H:i') }}</strong></p>
                                    </div>
                                    @if($payment->processed_at)
                                        <div class="mb-3">
                                            <label class="text-muted small">Processed On</label>
                                            <p class="mb-0"><strong>{{ $payment->processed_at->format('j M Y H:i') }}</strong></p>
                                        </div>
                                    @endif
                                    @if($payment->sale_id)
                                        <div class="mb-3">
                                            <label class="text-muted small">Sale ID</label>
                                            <p class="mb-0">
                                                <a href="{{ route('admin.sales.show', $payment->sale_id) }}" target="_blank">
                                                    <strong>#{{ $payment->sale_id }}</strong>
                                                </a>
                                            </p>
                                        </div>
                                    @endif
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

                    {{-- User Information --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>User Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="mr-3">
                                    @if($payment->user->avatar)
                                        <img src="{{ asset($payment->user->avatar) }}" 
                                             class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <span class="text-white font-weight-bold">
                                                {{ strtoupper(substr($payment->user->full_name, 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $payment->user->full_name }}</h5>
                                    <p class="text-muted mb-2">{{ $payment->user->email }}</p>
                                    @if($payment->user->mobile)
                                        <p class="mb-2"><strong>Mobile:</strong> {{ $payment->user->mobile }}</p>
                                    @endif
                                    <p class="mb-0"><strong>Joined:</strong> {{ $payment->user->created_at->format('j M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Course Information --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Course Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                @if($payment->webinar->cover_img)
                                    <img src="{{ asset($payment->webinar->cover_img) }}" 
                                         class="rounded mr-3" style="width: 100px; height: 100px; object-fit: cover;">
                                @endif
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $payment->webinar->title }}</h5>
                                    <p class="text-muted mb-2">by {{ $payment->webinar->creator->full_name }}</p>
                                    <p class="mb-2">{{ Str::limit($payment->webinar->description, 200) }}</p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-info">{{ $payment->webinar->type ?? 'Course' }}</span>
                                        @if($payment->webinar->duration)
                                            <span class="badge badge-secondary ml-1">{{ $payment->webinar->duration }}</span>
                                        @endif
                                        <span class="text-success font-weight-bold ml-3">
                                            {{ $payment->webinar->getFormattedPrice() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Screenshot --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Payment Screenshot</h4>
                        </div>
                        <div class="card-body text-center">
                            @if($payment->getAttachmentUrl())
                                <a href="{{ $payment->getAttachmentUrl() }}" target="_blank">
                                    <img src="{{ $payment->getAttachmentUrl() }}" 
                                         class="img-thumbnail" style="max-width: 100%; max-height: 400px;">
                                </a>
                                <p class="mt-3">
                                    <a href="{{ $payment->getAttachmentUrl() }}" target="_blank" 
                                       class="btn btn-outline-primary">
                                        <i class="fa fa-expand mr-1"></i>View Full Size
                                    </a>
                                </p>
                            @else
                                <p class="text-muted">No screenshot available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    {{-- Actions --}}
                    @if($payment->canBeApproved())
                        <div class="card">
                            <div class="card-header">
                                <h4>Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success approve-btn" data-id="{{ $payment->id }}">
                                        <i class="fa fa-check mr-1"></i>Approve Payment
                                    </button>
                                    <button type="button" class="btn btn-danger reject-btn" data-id="{{ $payment->id }}">
                                        <i class="fa fa-times mr-1"></i>Reject Payment
                                    </button>
                                </div>
                                
                                <hr>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fa fa-info-circle"></i> Approval Actions</h6>
                                    <p class="mb-0 small">
                                        <strong>Approve:</strong> Creates order and grants course access<br>
                                        <strong>Reject:</strong> Marks payment as failed, user can resubmit
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Timeline --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Timeline</h4>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success">
                                        <i class="fa fa-paper-plane"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>Payment Submitted</h6>
                                        <p class="text-muted small mb-0">
                                            {{ $payment->created_at->format('j M Y H:i') }}
                                        </p>
                                        <p class="text-muted small mb-0">
                                            by {{ $payment->user->full_name }}
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
                                                Pending admin review
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    @if($payment->status === \App\Models\OfflinePayment::$approved && $payment->sale_id)
                        <div class="card">
                            <div class="card-header">
                                <h4>Order Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <label class="text-muted small">Order ID</label>
                                    <p class="mb-0">
                                        <a href="{{ route('admin.sales.show', $payment->sale_id) }}" target="_blank">
                                            <strong>#{{ $payment->sale_id }}</strong>
                                        </a>
                                    </p>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small">Order Status</label>
                                    <p class="mb-0">
                                        <span class="badge bg-success bg-opacity-25 text-success">Success</span>
                                    </p>
                                </div>
                                <div class="mb-0">
                                    <label class="text-muted small">Payment Method</label>
                                    <p class="mb-0"><strong>Offline</strong></p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve this payment? This will create an order and grant course access to the user.</p>
                        <div class="form-group">
                            <label>Admin Remark (Optional)</label>
                            <textarea name="admin_remark" class="form-control" rows="3" 
                                      placeholder="Add any remarks about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check mr-1"></i>Approve Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject this payment? The user will need to resubmit payment details.</p>
                        <div class="form-group">
                            <label>Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="admin_remark" class="form-control" rows="3" required
                                      placeholder="Please specify the reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-times mr-1"></i>Reject Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 0;
        }
        
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

@push('scripts')
    <script>
        $(document).ready(function() {
            let currentPaymentId = {{ $payment->id }};

            // Approve button click
            $('.approve-btn').click(function() {
                $('#approveModal').modal('show');
            });

            // Reject button click
            $('.reject-btn').click(function() {
                $('#rejectModal').modal('show');
            });

            // Approve form submission
            $('#approveForm').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: `/admin/offline-payments/${currentPaymentId}/approve`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#approveModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Approved!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error approving payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });

            // Reject form submission
            $('#rejectForm').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: `/admin/offline-payments/${currentPaymentId}/reject`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#rejectModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Rejected!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error rejecting payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });
        });
    </script>
@endpush
