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

                            {{-- Installment Restructure Details --}}
                            @if($supportRequest->support_scenario === 'installment_restructure' && isset($restructureData) && $restructureData)
                                <div class="alert alert-primary">
                                    <h6><i class="fas fa-layer-group"></i> EMI Restructure Request</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Course:</strong> {{ $restructureData['plan']->sale->product->name ?? 'N/A' }}<br>
                                            <strong>Plan Total:</strong> ₹{{ number_format($restructureData['plan']->total_amount, 2) }}<br>
                                            <strong>Plan Status:</strong> <span class="badge badge-{{ $restructureData['plan']->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($restructureData['plan']->status) }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            @if($restructureData['target_schedule'])
                                                <strong>Target EMI:</strong> #{{ $restructureData['target_schedule']->sequence }}
                                                ({{ $restructureData['is_upfront'] ? 'Upfront' : 'Step ' . $restructureData['target_schedule']->sequence }})<br>
                                                <strong>EMI Amount:</strong> ₹{{ number_format($restructureData['schedule_amount'], 2) }}<br>
                                                <strong>Remaining:</strong> ₹{{ number_format($restructureData['schedule_remaining'], 2) }}<br>
                                                @if($restructureData['target_schedule']->due_date)
                                                    <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($restructureData['target_schedule']->due_date)->format('d M Y') }}
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @if($supportRequest->restructure_reason)
                                        <strong>Student's Reason:</strong> {{ $supportRequest->restructure_reason }}<br>
                                    @endif

                                    {{-- Full Schedule Table --}}
                                    <hr>
                                    <h6 class="mb-2">Current Payment Schedule</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered text-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Due Date</th>
                                                    <th>Amount</th>
                                                    <th>Paid</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($restructureData['schedules'] as $sched)
                                                    @php
                                                        $isTarget = $restructureData['target_schedule'] && $sched->id === $restructureData['target_schedule']->id;
                                                    @endphp
                                                    <tr class="{{ $isTarget ? 'table-warning font-weight-bold' : '' }}">
                                                        <td>{{ $sched->sequence }}{{ $isTarget ? ' ⬅' : '' }}</td>
                                                        <td>{{ $sched->due_date ? \Carbon\Carbon::parse($sched->due_date)->format('d M Y') : '-' }}</td>
                                                        <td>₹{{ number_format($sched->amount_due, 2) }}</td>
                                                        <td>{{ ($sched->amount_paid ?? 0) > 0 ? '₹' . number_format($sched->amount_paid, 2) : '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ match($sched->status) { 'paid' => 'success', 'due' => 'warning', 'overdue' => 'danger', 'partial' => 'info', 'waived' => 'secondary', default => 'light' } }}">
                                                                {{ ucfirst($sched->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Admin Split Form (only show when not yet completed) --}}
                                @if(!in_array($supportRequest->status, ['completed', 'executed', 'closed']) && $restructureData['target_schedule'] && Auth::user()->role_name === 'admin')
                                    <div class="card border-warning mt-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-cut"></i> Define Restructure Split</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-2">
                                                Split EMI #{{ $restructureData['target_schedule']->sequence }}
                                                (₹{{ number_format($restructureData['schedule_remaining'], 2) }})
                                                into sub-installments. Set the number of parts, then define each amount and due date.
                                            </p>

                                            <div class="form-group">
                                                <label><strong>Number of Sub-Installments</strong></label>
                                                <select id="restructureNumParts" class="form-control" style="width:150px">
                                                    <option value="2">2 parts</option>
                                                    <option value="3" selected>3 parts</option>
                                                    <option value="4">4 parts</option>
                                                    <option value="5">5 parts</option>
                                                    <option value="6">6 parts</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label><strong>Split Mode</strong></label><br>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary active" id="modeEqual">Equal Split</button>
                                                    <button type="button" class="btn btn-outline-primary" id="modePercent">By Percentage</button>
                                                    <button type="button" class="btn btn-outline-primary" id="modeCustom">Custom Amounts</button>
                                                </div>
                                            </div>

                                            <div id="restructureSplitTable" class="mt-3">
                                                {{-- Dynamically generated by JS --}}
                                            </div>

                                            <div class="mt-2">
                                                <strong>Total: ₹<span id="restructureSplitTotal">0.00</span></strong>
                                                <span id="restructureSplitError" class="text-danger ml-3" style="display:none;"></span>
                                            </div>

                                            <input type="hidden" name="restructure_schedule_id" value="{{ $restructureData['target_schedule']->id }}">
                                            <input type="hidden" name="restructure_plan_id" value="{{ $restructureData['plan']->id }}">
                                            <input type="hidden" name="restructure_sub_schedules" id="restructureSubSchedulesJson" value="">
                                        </div>
                                    </div>

<script>
(function() {
    const targetAmount = {{ $restructureData['schedule_remaining'] }};
    const scheduleId = {{ $restructureData['target_schedule']->id }};
    const planId = {{ $restructureData['plan']->id }};
    const container = document.getElementById('restructureSplitTable');
    const numSelect = document.getElementById('restructureNumParts');
    const totalSpan = document.getElementById('restructureSplitTotal');
    const errorSpan = document.getElementById('restructureSplitError');
    const hiddenInput = document.getElementById('restructureSubSchedulesJson');

    let mode = 'equal'; // equal | percent | custom

    document.getElementById('modeEqual').addEventListener('click', function() { mode = 'equal'; setActiveBtn(this); render(); });
    document.getElementById('modePercent').addEventListener('click', function() { mode = 'percent'; setActiveBtn(this); render(); });
    document.getElementById('modeCustom').addEventListener('click', function() { mode = 'custom'; setActiveBtn(this); render(); });
    numSelect.addEventListener('change', render);

    function setActiveBtn(btn) {
        document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function render() {
        const n = parseInt(numSelect.value);
        let html = '<table class="table table-sm table-bordered"><thead><tr><th>#</th>';
        if (mode === 'percent') html += '<th>Percentage (%)</th>';
        html += '<th>Amount (₹)</th><th>Due Date</th></tr></thead><tbody>';

        const equalAmt = Math.floor(targetAmount / n);
        const today = new Date();

        for (let i = 0; i < n; i++) {
            const dueDate = new Date(today);
            dueDate.setDate(dueDate.getDate() + (i * 30));
            const dateStr = dueDate.toISOString().split('T')[0];

            let amt = (i === n - 1) ? Math.round(targetAmount - equalAmt * (n - 1)) : equalAmt;
            let pct = (i === n - 1) ? (100 - Math.floor(100/n) * (n-1)).toFixed(1) : (100/n).toFixed(1);

            html += '<tr>';
            html += '<td>' + (i + 1) + '</td>';
            if (mode === 'percent') {
                html += '<td><input type="number" class="form-control form-control-sm restructure-pct" data-idx="'+i+'" value="'+pct+'" step="0.1" min="0" max="100"></td>';
            }
            html += '<td><input type="number" class="form-control form-control-sm restructure-amt" data-idx="'+i+'" value="'+amt+'" step="1" min="0" '+(mode === 'percent' ? 'readonly' : (mode === 'equal' ? 'readonly' : ''))+'></td>';
            html += '<td><input type="date" class="form-control form-control-sm restructure-date" data-idx="'+i+'" value="'+dateStr+'" required></td>';
            html += '</tr>';
        }

        html += '</tbody></table>';
        container.innerHTML = html;

        // Bind events
        container.querySelectorAll('.restructure-pct').forEach(el => {
            el.addEventListener('input', recalcFromPercent);
        });
        container.querySelectorAll('.restructure-amt').forEach(el => {
            el.addEventListener('input', recalcTotal);
        });

        recalcTotal();
    }

    function recalcFromPercent() {
        const pcts = container.querySelectorAll('.restructure-pct');
        const amts = container.querySelectorAll('.restructure-amt');
        pcts.forEach((pctEl, i) => {
            const pct = parseFloat(pctEl.value) || 0;
            amts[i].value = Math.round(targetAmount * pct / 100);
        });
        recalcTotal();
    }

    function recalcTotal() {
        const amts = container.querySelectorAll('.restructure-amt');
        let total = 0;
        amts.forEach(el => { total += parseFloat(el.value) || 0; });
        totalSpan.textContent = Math.round(total);

        if (Math.abs(total - targetAmount) > 1) {
            errorSpan.textContent = '(Must equal ₹' + Math.round(targetAmount) + ')';
            errorSpan.style.display = 'inline';
        } else {
            errorSpan.style.display = 'none';
        }

        // Build JSON for hidden input
        const schedules = [];
        amts.forEach((el, i) => {
            const dateEl = container.querySelectorAll('.restructure-date')[i];
            schedules.push({
                amount: Math.round(parseFloat(el.value) || 0),
                due_date: dateEl ? dateEl.value : ''
            });
        });
        hiddenInput.value = JSON.stringify(schedules);
    }

    // Hook into the status form submission to inject restructure data
    // NOTE: The form element is rendered AFTER this script in the DOM (right column),
    // so we must wait for DOMContentLoaded before querying it.
    document.addEventListener('DOMContentLoaded', function() {
        const statusForm = document.querySelector('form[action$="/status"]') || document.querySelector('form[action*="updateStatus"]');
        if (statusForm) {
            statusForm.addEventListener('submit', function(e) {
                // Ensure hidden fields are inside the form
                const existingHidden1 = statusForm.querySelector('input[name="restructure_sub_schedules"]');
                const existingHidden2 = statusForm.querySelector('input[name="restructure_schedule_id"]');
                const existingHidden3 = statusForm.querySelector('input[name="restructure_plan_id"]');

                if (!existingHidden1) {
                    const h1 = document.createElement('input'); h1.type='hidden'; h1.name='restructure_sub_schedules'; h1.value=hiddenInput.value;
                    statusForm.appendChild(h1);
                } else {
                    existingHidden1.value = hiddenInput.value;
                }
                if (!existingHidden2) {
                    const h2 = document.createElement('input'); h2.type='hidden'; h2.name='restructure_schedule_id'; h2.value=scheduleId;
                    statusForm.appendChild(h2);
                }
                if (!existingHidden3) {
                    const h3 = document.createElement('input'); h3.type='hidden'; h3.name='restructure_plan_id'; h3.value=planId;
                    statusForm.appendChild(h3);
                }
            });
        } else {
            console.warn('Restructure: Could not find status form to hook into.');
        }
    });

    render();
})();
</script>
                                @endif
                            @elseif($supportRequest->support_scenario === 'installment_restructure')
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-layer-group"></i> EMI Restructure Request</h6>
                                    <strong>Reason:</strong> {{ $supportRequest->restructure_reason ?? $supportRequest->description }}<br>
                                    @if($supportRequest->installment_amount)
                                        <strong>Installment Amount:</strong> ₹{{ number_format($supportRequest->installment_amount, 2) }}
                                    @endif
                                    <br><small class="text-muted">No UPE plan data linked. This may be a legacy ticket.</small>
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




@if($supportRequest->support_scenario === 'temporary_access' && (auth()->user()->role_name === 'Support Role' || auth()->user()->role_name === 'admin'))
    <div class="form-group">
        <label>
            Approved Access Percentage (%) <span class="text-danger">*</span>
        </label>
        <input type="number"
               name="temporary_access_percentage"
               class="form-control"
               min="1"
               max="100"
               value="{{ old('temporary_access_percentage', $supportRequest->temporary_access_percentage) }}"
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