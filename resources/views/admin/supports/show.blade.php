@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Support Ticket: {{ $supportRequest->ticket_number }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.support.index') }}">Support Tickets</a></div>
                <div class="breadcrumb-item">{{ $supportRequest->ticket_number }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8">
                    {{-- Ticket Details --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $supportRequest->title }}</h4>
                            <div class="card-header-action">
                                <span class="btn btn-{{ $supportRequest->getStatusBadgeClass() }} btn-sm">
                                    {{ ucfirst($supportRequest->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Basic Info --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <strong>Requester:</strong> {{ $supportRequest->getRequesterName() }}<br>
                                    <strong>Email:</strong> {{ $supportRequest->getRequesterEmail() }}<br>
                                    <strong>Scenario:</strong> {{ $supportRequest->getScenarioLabel() }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Course:</strong> {{ $supportRequest->webinar?->title }}<br>
                                    <strong>Instructor:</strong> {{ $supportRequest->webinar?->creator->full_name }}<br>
                                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($supportRequest->created_at)->format('j M Y H:i') }}
                                </div>
                            </div>
   
                            {{-- Scenario Details --}}
                            @if($supportRequest->support_scenario === 'course_extension')
                                <div class="alert alert-info">
                                    <strong>Extension Days:</strong> {{ $supportRequest->extension_days }} days<br>
                                    <strong>Reason:</strong> {{ $supportRequest->extension_reason }}
                                </div>
                            @endif

                            @if($supportRequest->support_scenario === 'refund_payment')
                                <div class="alert alert-warning">
                                    <strong>Reason:</strong> {{ $supportRequest->refund_reason }}<br>
                                    <strong>Bank:</strong> {{ $supportRequest->bank_account_number }} ({{ $supportRequest->ifsc_code }})<br>
                                    <strong>Holder:</strong> {{ $supportRequest->account_holder_name }}
                                </div>
                            @endif


                            @if($supportRequest->support_scenario === 'post_purchase_coupon')
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-tag"></i> Post-Purchase Coupon Request</h6>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong>Coupon Code:</strong> 
                                                    <span class="badge badge-primary">{{ $supportRequest->coupon_code }}</span><br>
                                                    @if($supportRequest->coupon_apply_reason)
                                                        <strong>Reason:</strong> {{ $supportRequest->coupon_apply_reason }}<br>
                                                    @endif
                                                    @if($supportRequest->status === 'executed')
                                                        <span class="badge bg-success bg-opacity-25 text-success mt-2">Coupon Applied</span><br>
                                                        <small class="text-muted">Coupon has been applied to the purchase</small>
                                                    @else
                                                        <span class="badge badge-warning mt-2">Pending Processing</span><br>
                                                        <small class="text-muted">Waiting for admin approval</small>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($supportRequest->execution_notes)
                                                <hr>
                                                <strong>Execution Notes:</strong> {{ $supportRequest->execution_notes }}
                                            @endif
                                        </div>
                                    @endif

                            @if($supportRequest->support_scenario === 'offline_cash_payment')
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-money-bill-wave"></i> Offline Cash Payment Details</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Amount Paid:</strong> ₹{{ number_format($supportRequest->cash_amount, 2) }}<br>
                                            <strong>Payment Date:</strong> {{ $supportRequest->payment_date }}<br>
                                            <strong>Receipt Number:</strong> {{ $supportRequest->payment_receipt_number ?? 'N/A' }}<br>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Payment Location:</strong> {{ $supportRequest->payment_location }}<br>
                                            @if($supportRequest->status === 'executed')
                                                <span class="badge bg-success bg-opacity-25 text-success">Payment Processed</span><br>
                                                <small class="text-muted">Order created and access granted</small>
                                            @else
                                                <span class="badge badge-warning">Pending Processing</span><br>
                                                <small class="text-muted">Waiting for admin approval</small>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($supportRequest->execution_notes)
                                        <hr>
                                        <strong>Execution Notes:</strong> {{ $supportRequest->execution_notes }}
                                    @endif
                                </div>
                            @endif

                            {{-- Description --}}
                            @if($supportRequest->description || $supportRequest->relative_description)
                                <hr>
                                <h6>Description:</h6>
                                @if($supportRequest->support_scenario === 'relatives_friends_access')
                                    <p>{!! nl2br(e($supportRequest->relative_description)) !!}</p>
                                @else
                                    <p>{!! nl2br(e($supportRequest->description)) !!}</p>
                                @endif
                            @endif
                            

                            {{-- Attachments --}}
                            @if($supportRequest->attachments && count($supportRequest->attachments) > 0)
                                <hr>
                                <h6>Attachments:</h6>
                                <div class="row">
                                    @foreach($supportRequest->attachments as $attachment)
                                        <div class="col-md-4 mb-2">
                                            <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="btn btn-outline-primary btn-block">
                                                <i class="fas fa-file"></i> {{ basename($attachment) }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Activity Logs 
                    @if($supportRequest->logs && count($supportRequest->logs) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h4>Activity Timeline</h4>
                        </div>
                        <div class="card-body">
                            <div class="activities">
                                @foreach($supportRequest->logs as $log)
                                <div class="activity">
                                    <div class="activity-icon bg-primary text-white">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job">{{ \Carbon\Carbon::parse($log->created_at)->format('j M Y H:i') }}</span>
                                            <span class="bullet"></span>
                                            <span class="text-job text-primary">{{ $log->user ? $log->user->full_name : 'System' }}</span>
                                        </div>
                                        <p><strong>{{ ucfirst($log->action) }}</strong></p>
                                        @if($log->remarks)
                                            <p>{{ $log->remarks }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    --}}
                </div>

                <div class="col-12 col-md-4">
                    @php
                        $isFinalStatus = in_array($supportRequest->status, ['completed', 'executed', 'closed', 'rejected']);
                        $isSupportRoleProcessed = (auth()->user()->role_name === 'Support Role') && in_array($supportRequest->status, ['approved']);
                        $shouldHideActions = $isFinalStatus || $isSupportRoleProcessed;
                    @endphp
                    @if(!$shouldHideActions)
                    {{-- Action Panel --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Actions</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.support.updateStatus', $supportRequest->id) }}">
                                @csrf
                                @method('PUT')

                                @if($supportRequest->support_scenario === 'post_purchase_coupon' && auth()->user()->role_name === 'Support Role')
    <div class="form-group">
        <label>Coupon Code to Apply</label>
        <input name="coupon_code" 
               id="couponCodeInput" 
               type="text" 
               class="form-control" 
               value="{{ old('coupon_code', $supportRequest->coupon_code) }}"
               placeholder="Enter coupon code">
        <small class="text-muted">Enter the coupon code to apply to this purchase</small>
    </div>
    
    <!-- Validation Result Display -->
    <div id="couponValidationResult" style="display: none;" class="mb-3"></div>
@endif




@if($supportRequest->support_scenario === 'temporary_access' && auth()->user()->role_name === 'Support Role')
    <div class="form-group">
        <label>
            Approved Access Percentage (%) <span class="text-danger">*</span>
        </label>
                                <div class="form-group">
                                    <label>Change Status</label>
                                    <select name="status" id="statusSelect" class="form-control" required>
                                        <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                        @if(auth()->check() && auth()->user()->role === 'admin')
                                        <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>

                                            <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>
                                                Completed
                                            </option>
                                                                                    <option value="executed" {{ $supportRequest->status == 'executed' ? 'selected' : '' }}>Executed</option>
                                        <option value="closed" {{ $supportRequest->status == 'closed' ? 'selected' : '' }}>Closed</option>

                                        @endif
                                        <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                </div>

        <input type="number"
               name="temporary_access_percentage"
               class="form-control"
               min="1"
               max="100"
               placeholder="Enter access percentage"
               required>

        <small class="text-muted">
            Access will be granted only for 7 days
        </small>
    </div>
@endif


                                {{-- Status Change Section - Hide for Support Role on free_course_grant --}}
@if(Auth::user()->role_name === 'Support Role' && $supportRequest->support_scenario === 'free_course_grant')
    {{-- Show warning message only - no status dropdown --}}
    <div class="alert alert-warning">
        <h6><i class="fas fa-exclamation-triangle"></i> Access Restricted</h6>
        <p class="mb-0">
            <strong>Free Course Grant</strong> requests can only be approved or completed by Admin. 
            You can view the details but cannot change the status.
        </p>
    </div>
@else
    {{-- Show normal status dropdown --}}
    <div class="form-group">
        <label>Change Status</label>
        <select name="status" id="statusSelect" class="form-control" required>
            <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>
            
            {{-- Support Role Options --}}
            @if(Auth::user()->role_name === 'Support Role')
                {{-- Support Role can approve regular requests but NOT free_course_grant --}}
                @if($supportRequest->support_scenario !== 'free_course_grant')
                    <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                @endif
            @endif
            
            {{-- Admin Only Options --}}
            @if(Auth::user()->role_name === 'admin')
                <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
            @endif
            
            {{-- Rejection options --}}
            @if(Auth::user()->role_name === 'admin' || ($supportRequest->support_scenario !== 'free_course_grant' && Auth::user()->role_name === 'Support Role'))
                <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
            @endif
            
            <option value="executed" {{ $supportRequest->status == 'executed' ? 'selected' : '' }}>Executed</option>
            <option value="closed" {{ $supportRequest->status == 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
    </div>

    {{-- Support Remarks - Visible to Support Role and Admin --}}
    @if(Auth::user()->role_name === 'Support Role' || Auth::user()->role_name === 'admin')
    <div class="form-group">
        <label>Support Remarks <span class="text-danger">*</span></label>
        <textarea name="support_remarks" class="form-control" rows="3" placeholder="Add support remarks here..." required>{{ old('support_remarks', $supportRequest->support_remarks ?? '') }}</textarea>
        <small class="form-text text-muted">Support team remarks and initial assessment</small>
    </div>
    @endif

    {{-- Admin Remarks - Only visible to Admin --}}
    @if(Auth::user()->role_name === 'admin')
    <div class="form-group" id="adminRemarksDiv">
        <label>Admin Remarks <span class="text-danger" id="adminRemarksRequired" style="display: none;">*</span></label>
        <textarea name="admin_remarks" class="form-control" rows="3" placeholder="Add admin remarks here...">{{ old('admin_remarks', $supportRequest->approval_remarks) }}</textarea>
        <small class="form-text text-muted">Admin remarks for completion status</small>
    </div>
    @endif

    <div class="form-group" id="rejectionReasonDiv" style="display: none;">
        <label>Rejection Reason <span class="text-danger">*</span></label>
        <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Why are you rejecting this request?"></textarea>
    </div>

    
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.querySelector('select[name="status"]');
    const adminRemarksDiv = document.getElementById('adminRemarksDiv');
    const adminRemarksRequired = document.getElementById('adminRemarksRequired');
    const adminRemarksTextarea = document.querySelector('textarea[name="admin_remarks"]');
    
    function toggleAdminRemarks() {
        if (statusSelect.value === 'completed') {
            adminRemarksRequired.style.display = 'inline';
            adminRemarksTextarea.setAttribute('required', 'required');
        } else {
            adminRemarksRequired.style.display = 'none';
            adminRemarksTextarea.removeAttribute('required');
        }
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', toggleAdminRemarks);
        toggleAdminRemarks(); // Initial check
    }
});
</script>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Quick Info --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Quick Info</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><strong>Flow Type:</strong> {{ $supportRequest->getFlowTypeLabel() }}</li>
                                <li><strong>Purchase Status:</strong> {{ ucfirst($supportRequest->purchase_status) }}</li>
                                @if($supportRequest->course_purchased_at)
                                    <li><strong>Purchased:</strong> {{ \Carbon\Carbon::parse($supportRequest->course_purchased_at)->format('j M Y') }}</li>
                                @endif
                                @if($supportRequest->support_handler_id)
                                    <li><strong>Handler:</strong> {{ $supportRequest->supportHandler->full_name }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @else
                    {{-- Show completion message when ticket is already processed --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Ticket Status</h4>
                        </div>
                        <div class="card-body">
                            @if($supportRequest->status === 'rejected')
                                <div class="alert alert-danger" role="alert">
                                    <h6><i class="fas fa-times-circle"></i> Ticket Rejected</h6>
                                    <p class="mb-0">
                                        This support ticket has been <strong>rejected</strong>. 
                                        No further actions can be taken on this request.
                                    </p>
                                </div>
                                @if($supportRequest->rejection_reason)
                                    <p><strong>Rejection Reason:</strong></p>
                                    <p class="text-muted">{{ $supportRequest->rejection_reason }}</p>
                                @endif
                            @elseif($isSupportRoleProcessed && auth()->user()->role_name === 'Support Role')
                                <div class="alert alert-info" role="alert">
                                    <h6><i class="fas fa-check-circle"></i> Approved & Closed</h6>
                                    <p class="mb-0">You have already approved this ticket. It is now waiting for Admin to complete the action.</p>
                                </div>
                            @else
                                <div class="alert alert-success" role="alert">
                                    <h6 class="text-center mt-2">Ticket Successfully Resolved & Closed.</h6>
                                </div>
                            @endif
                            @if($supportRequest->executed_at)
                                <p><strong>Completed On:</strong> {{ \Carbon\Carbon::parse($supportRequest->executed_at)->format('j M Y H:i') }}</p>
                            @endif
                            @if($supportRequest->execution_notes)
                                <p><strong>Notes:</strong> {{ $supportRequest->execution_notes }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('statusSelect').addEventListener('change', function() {
            const rejectionDiv = document.getElementById('rejectionReasonDiv');
            if (this.value === 'rejected') {
                rejectionDiv.style.display = 'block';
                rejectionDiv.querySelector('textarea').setAttribute('required', 'required');
            } else {
                rejectionDiv.style.display = 'none';
                rejectionDiv.querySelector('textarea').removeAttribute('required');
            }
        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Status change handler for rejection reason
    const statusSelect = document.getElementById('statusSelect');
    const rejectionDiv = document.getElementById('rejectionReasonDiv');
    const adminRemarksDiv = document.getElementById('adminRemarksDiv');
    const adminRemarksRequired = document.getElementById('adminRemarksRequired');
    const adminRemarksTextarea = document.querySelector('textarea[name="admin_remarks"]');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            // Handle rejection reason
            if (this.value === 'rejected' && rejectionDiv) {
                rejectionDiv.style.display = 'block';
                rejectionDiv.querySelector('textarea').setAttribute('required', 'required');
            } else if (rejectionDiv) {
                rejectionDiv.style.display = 'none';
                rejectionDiv.querySelector('textarea').removeAttribute('required');
            }
            
            // Handle admin remarks for completed status
            if (this.value === 'completed' && adminRemarksDiv) {
                adminRemarksRequired.style.display = 'inline';
                adminRemarksTextarea.setAttribute('required', 'required');
            } else if (adminRemarksDiv) {
                adminRemarksRequired.style.display = 'none';
                adminRemarksTextarea.removeAttribute('required');
            }
        });
        
        // Trigger on page load
        statusSelect.dispatchEvent(new Event('change'));
    }
    
    // Coupon validation (only if coupon input exists)
    const couponInput = document.getElementById('couponCodeInput');
    if (couponInput) {
        const validateBtn = document.createElement('button');
        validateBtn.type = 'button';
        validateBtn.className = 'btn btn-sm btn-info mt-2';
        validateBtn.id = 'validateCouponBtn';
        validateBtn.innerHTML = '<i class="fa fa-check"></i> Validate Coupon';
        couponInput.parentNode.appendChild(validateBtn);
        
        validateBtn.addEventListener('click', validateCouponCode);
        couponInput.addEventListener('blur', validateCouponCode);
        couponInput.addEventListener('input', function() {
            document.getElementById('couponValidationResult').style.display = 'none';
        });
    }
    
    function validateCouponCode() {
        const couponCode = document.getElementById('couponCodeInput').value.trim();
        const webinarId = '{{ $supportRequest->webinar_id }}';
        const resultDiv = document.getElementById('couponValidationResult');
        
        if (!couponCode) {
            resultDiv.style.display = 'none';
            return;
        }
        
        // Show loading
        resultDiv.className = 'alert alert-info';
        resultDiv.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Validating coupon...';
        resultDiv.style.display = 'block';
        
        // AJAX Request
        fetch('/admin/supports/newsuportforasttrolok/validate-coupon', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                coupon_code: couponCode,
                webinar_id: webinarId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = 'alert alert-success';
                resultDiv.innerHTML = `
                    <strong><i class="fa fa-check-circle"></i>Coupon Applied Success</strong><br>
                `;
            } else {
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = `<i class="fa fa-times-circle"></i> ${data.message}`;
            }
        })
        .catch(error => {
            resultDiv.className = 'alert alert-danger';
            resultDiv.innerHTML = '<i class="fa fa-times-circle"></i> Error validating coupon';
        });
    }
});
</script>
@endsection