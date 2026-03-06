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
            @php
                $isFinalStatus = in_array($supportRequest->status, ['completed', 'executed', 'closed', 'rejected']);
                $isSupportRole = auth()->user()->role_name === 'Support Role';
                $isAdmin = auth()->user()->role_name === 'admin';
                $canProcess = !$isFinalStatus && ($isSupportRole || $isAdmin);

                // Actions panel should be hidden if status is final
                // For Support Role, we hide it if they've already approved it (sent to admin)
                $isSupportRoleProcessed = $isSupportRole && in_array($supportRequest->status, ['approved']);
                $shouldHideActions = $isFinalStatus || $isSupportRoleProcessed;
            @endphp
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
                                    <strong>Course:</strong> {{ $supportRequest->webinar?->title ?? 'Not assigned yet' }}<br>
                                    <strong>Instructor:</strong> {{ $supportRequest->webinar?->creator?->full_name ?? '-' }}<br>
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
                                    <strong>Refund Method:</strong> Wallet Credit<br>
                                    <strong>Status:</strong> {{ ucfirst($supportRequest->status) }}
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
                                                    @if(in_array($supportRequest->status, ['executed', 'completed', 'closed']))
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
                                            <strong>Amount Paid:</strong> ₹{{ number_format($supportRequest->cash_amount, 0) }}<br>
                                            <strong>Payment Date:</strong> {{ $supportRequest->payment_date }}<br>
                                            <strong>Receipt Number:</strong> {{ $supportRequest->payment_receipt_number ?? 'N/A' }}<br>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Payment Location:</strong> {{ $supportRequest->payment_location }}<br>
                                            @if(in_array($supportRequest->status, ['executed', 'completed', 'closed']))
                                                <span class="badge bg-success bg-opacity-25 text-success">Wallet Credited</span><br>
                                                <small class="text-muted">₹{{ number_format($supportRequest->cash_amount, 0) }} added to student's wallet</small>
                                            @else
                                                <span class="badge badge-warning">Pending Approval</span><br>
                                                <small class="text-muted">On approval, ₹{{ number_format($supportRequest->cash_amount, 0) }} will be credited to student's wallet</small>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!in_array($supportRequest->status, ['executed', 'completed', 'closed']))
                                    <div class="alert alert-info mt-2 mb-0 py-2">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>How it works:</strong> Approving this ticket will credit
                                        <strong>₹{{ number_format($supportRequest->cash_amount, 0) }}</strong>
                                        to the student's wallet. The student can then use the wallet balance to purchase their course.
                                    </div>
                                    @endif

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
                                            <strong>Plan Total:</strong> ₹{{ number_format($restructureData['plan']->total_amount, 0) }}<br>
                                            <strong>Plan Status:</strong> <span class="badge badge-{{ $restructureData['plan']->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($restructureData['plan']->status) }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            @if($restructureData['target_schedule'])
                                                <strong>Target EMI:</strong> #{{ $restructureData['target_schedule']->sequence }}
                                                ({{ $restructureData['is_upfront'] ? 'Upfront' : 'Step ' . $restructureData['target_schedule']->sequence }})<br>
                                                <strong>EMI Amount:</strong> ₹{{ number_format($restructureData['schedule_amount'], 0) }}<br>
                                                <strong>Remaining:</strong> ₹{{ number_format($restructureData['schedule_remaining'], 0) }}<br>
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
                                                        <td>₹{{ number_format($sched->amount_due, 0) }}</td>
                                                        <td>{{ ($sched->amount_paid ?? 0) > 0 ? '₹' . number_format($sched->amount_paid, 0) : '-' }}</td>
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

                                {{-- Admin Split Form (show when not completed and has restructure data) --}}
                                @if(!in_array($supportRequest->status, ['completed', 'executed', 'closed']) && in_array(Auth::user()->role_name, ['admin', 'Support Role', 'support']))
                                    @if($supportRequest->support_scenario === 'installment_restructure' && (!isset($restructureData['target_schedule']) || !$restructureData['target_schedule']))
                                        {{-- Preview interface - show available installment courses for selection --}}
                                        <div class="card border-info mt-3">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0"><i class="fas fa-layer-group"></i> Installment Restructure - Select Course</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    First, select the installment course you want to restructure. Then click "Process & Complete" to load the EMI schedule and define the restructure split.
                                                </p>
                                                
                                                <div class="form-group">
                                                    <label><strong>Select Installment Course</strong></label>
                                                    <select name="webinar_id" class="form-control" required>
                                                        <option value="">-- Select Installment Course --</option>
                                                        @if(isset($installmentCourses) && $installmentCourses->isNotEmpty())
                                                            @foreach($installmentCourses as $course)
                                                                <option value="{{ $course['id'] }}" {{ ($supportRequest->webinar_id == $course['id']) ? 'selected' : '' }}>
                                                                    {{ $course['title'] }}
                                                                </option>
                                                            @endforeach
                                                        @elseif(isset($studentPurchases) && $studentPurchases->isNotEmpty())
                                                            @foreach($studentPurchases as $course)
                                                                <option value="{{ $course['id'] }}" {{ ($supportRequest->webinar_id == $course['id']) ? 'selected' : '' }}>
                                                                    {{ $course['title'] }}
                                                                </option>
                                                            @endforeach
                                                        @else
                                                            <option value="" disabled>No installment courses found</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label><strong>Reason for Restructure</strong></label>
                                                    <textarea name="restructure_reason" class="form-control" rows="2" placeholder="Enter reason for installment restructure...">{{ $supportRequest->restructure_reason ?? '' }}</textarea>
                                                </div>
                                                
                                                @if(Auth::user()->role_name === 'Support Role')
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> 
                                                        <strong>Support Role:</strong> After processing, an admin will need to complete the restructure execution.
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-info-circle"></i> 
                                                        <strong>Note:</strong> After selecting the course and reason, click "Process & Complete" to load the EMI schedule and define the restructure split.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($restructureData['target_schedule'])
                                        <div class="card border-warning mt-3">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0"><i class="fas fa-cut"></i> Define Restructure Split</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-2">
                                                    Split EMI #{{ $restructureData['target_schedule']->sequence }}
                                                    (₹{{ number_format($restructureData['schedule_remaining'], 0) }})
                                                    into sub-installments. Set the number of parts, then define each amount and due date.
                                                </p>
                                                @if($supportRequest->status === 'approved')
                                                    @if(Auth::user()->role_name === 'Support Role')
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i>
                                                            <strong>Support Role:</strong> The restructure has been prepared. An admin will need to complete the execution.
                                                        </div>
                                                    @else
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i>
                                                            <strong>Ready for restructure:</strong> Define the split below and then click "Update Status" → "Completed" to execute the restructure.
                                                        </div>
                                                    @endif
                                                @endif

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
                                                <strong>Total: ₹<span id="restructureSplitTotal">0</span></strong>
                                                <span id="restructureSplitError" class="text-danger ml-3" style="display:none;"></span>
                                            </div>

                                            <input type="hidden" name="restructure_schedule_id" value="{{ $restructureData['target_schedule']->id }}">
                                            <input type="hidden" name="restructure_plan_id" value="{{ $restructureData['plan']->id }}">
                                            <input type="hidden" name="restructure_sub_schedules" id="restructureSubSchedulesJson" value="">
                                        </div>
                                    </div>

<script>
(function() {
    const targetAmount = Math.round({{ $restructureData['schedule_remaining'] }});
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
                // Remove any previously injected hidden fields to avoid duplicates
                statusForm.querySelectorAll('.restructure-hidden-field, .offline-hidden-field').forEach(el => el.remove());
                
                // Inject restructure data if available
                if (typeof hiddenInput !== 'undefined') {
                    const existingHidden1 = statusForm.querySelector('input[name="restructure_sub_schedules"]');
                    if (!existingHidden1) {
                        const h1 = document.createElement('input'); h1.type='hidden'; h1.name='restructure_sub_schedules'; h1.value=hiddenInput.value;
                        h1.className = 'restructure-hidden-field';
                        statusForm.appendChild(h1);
                    } else {
                        existingHidden1.value = hiddenInput.value;
                    }
                    if (typeof scheduleId !== 'undefined') {
                        const h2 = document.createElement('input'); h2.type='hidden'; h2.name='restructure_schedule_id'; h2.value=scheduleId;
                        h2.className = 'restructure-hidden-field';
                        statusForm.appendChild(h2);
                    }
                    if (typeof planId !== 'undefined') {
                        const h3 = document.createElement('input'); h3.type='hidden'; h3.name='restructure_plan_id'; h3.value=planId;
                        h3.className = 'restructure-hidden-field';
                        statusForm.appendChild(h3);
                    }
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
                                @endif
                            @elseif($supportRequest->support_scenario === 'installment_restructure')
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-layer-group"></i> EMI Restructure Request</h6>
                                    <strong>Reason:</strong> {{ $supportRequest->restructure_reason ?? $supportRequest->description }}<br>
                                    @if($supportRequest->installment_amount)
                                        <strong>Installment Amount:</strong> ₹{{ number_format($supportRequest->installment_amount, 0) }}
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
                            

                            {{-- Attachments & Payment Screenshot --}}
                            @php
                                $hasAttachments = $supportRequest->attachments && count($supportRequest->attachments) > 0;
                                $hasScreenshot = $supportRequest->support_scenario === 'offline_cash_payment' && !empty($supportRequest->payment_screenshot);
                            @endphp
                            @if($hasAttachments || $hasScreenshot)
                                <hr>
                                <h6><i class="fas fa-paperclip"></i> Attachments:</h6>
                                <div class="row">
                                    @if($hasScreenshot)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border shadow-sm">
                                                <a href="{{ asset('store/' . $supportRequest->payment_screenshot) }}" target="_blank">
                                                    <img src="{{ asset('store/' . $supportRequest->payment_screenshot) }}" class="card-img-top" alt="Payment Screenshot" style="max-height:200px; object-fit:contain; background:#f8f9fa; padding:4px;">
                                                </a>
                                                <div class="card-body py-2 px-2 text-center">
                                                    <small class="text-muted"><i class="fas fa-receipt"></i> Payment Screenshot</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($hasAttachments)
                                        @foreach($supportRequest->attachments as $attachment)
                                            @php
                                                $ext = strtolower(pathinfo($attachment, PATHINFO_EXTENSION));
                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                            @endphp
                                            <div class="col-md-4 mb-3">
                                                @if($isImage)
                                                    <div class="card border shadow-sm">
                                                        <a href="{{ asset('store/' . $attachment) }}" target="_blank">
                                                            <img src="{{ asset('store/' . $attachment) }}" class="card-img-top" alt="{{ basename($attachment) }}" style="max-height:200px; object-fit:contain; background:#f8f9fa; padding:4px;">
                                                        </a>
                                                        <div class="card-body py-2 px-2 text-center">
                                                            <small class="text-muted"><i class="fas fa-image"></i> {{ basename($attachment) }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <a href="{{ asset('store/' . $attachment) }}" target="_blank" class="btn btn-outline-primary btn-block">
                                                        <i class="fas fa-file"></i> {{ basename($attachment) }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
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
                    {{-- ══════════════════════════════════════════════════ --}}
                    {{-- Process / Take Action Card --}}
                    {{-- ══════════════════════════════════════════════════ --}}
                    @if($canProcess)
                    <div class="card border-primary mt-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="text-white"><i class="fas fa-bolt"></i> Process / Take Action</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.support.processTicket', $supportRequest->id) }}" id="processTicketForm" enctype="multipart/form-data">
                                @csrf

                                {{-- Scenario Selection --}}
                                <div class="form-group">
                                    <label><strong>Support Scenario</strong> <span class="text-danger">*</span></label>
                                    <select name="support_scenario" id="processScenario" class="form-control" required>
                                        <option value="">-- Select Scenario --</option>
                                        @php
                                            $scenarios = [
                                                'course_extension' => 'Course Extension',
                                                'temporary_access' => 'Temporary Access',
                                                'mentor_access' => 'Mentor Access',
                                                'relatives_friends_access' => 'Relatives/Friends Access',
                                                'free_course_grant' => 'Free Course Grant',
                                                'offline_cash_payment' => 'Offline/Cash Payment',
                                                'installment_restructure' => 'Installment Restructure',
                                                'refund_payment' => 'Refund Payment',
                                                'post_purchase_coupon' => 'Post-Purchase Coupon',
                                            ];

                                            // Exclude refund scenarios for non-paid access
                                            if ($supportRequest->flow_type === 'flow_no_refund') {
                                                unset($scenarios['refund_payment']);
                                            }

                                            $currentValues = [
                                                'webinar_id' => $supportRequest->webinar_id,
                                                'support_scenario' => $supportRequest->support_scenario,
                                                'extension_days' => $supportRequest->extension_days,
                                                'extension_reason' => $supportRequest->extension_reason,
                                                'temporary_access_days' => $supportRequest->temporary_access_days,
                                                'temporary_access_percentage' => $supportRequest->temporary_access_percentage,
                                                'mentor_change_reason' => $supportRequest->mentor_change_reason,
                                                'relative_description' => $supportRequest->relative_description,
                                                'free_course_reason' => $supportRequest->free_course_reason,
                                                'cash_amount' => $supportRequest->cash_amount,
                                                'payment_receipt_number' => $supportRequest->payment_receipt_number,
                                                'payment_date' => $supportRequest->payment_date,
                                                'payment_location' => $supportRequest->payment_location,
                                                'restructure_reason' => $supportRequest->restructure_reason,
                                                'refund_reason' => $supportRequest->refund_reason,
                                                'coupon_code' => $supportRequest->coupon_code,
                                                'coupon_apply_reason' => $supportRequest->coupon_apply_reason,
                                                'admin_remarks' => $supportRequest->approval_remarks,
                                            ];
                                        @endphp
                                        @foreach($scenarios as $val => $label)
                                            <option value="{{ $val }}" {{ $supportRequest->support_scenario === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Dynamic Scenario Fields Container --}}
                                <div id="processScenarioFields"></div>

                                {{-- Admin Remarks --}}
                                <div class="form-group">
                                    <label>Admin Remarks</label>
                                    <textarea name="admin_remarks" class="form-control" rows="2" placeholder="Optional remarks...">{{ old('admin_remarks', $supportRequest->approval_remarks) }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block" id="processSubmitBtn">
                                    <i class="fas fa-check-circle"></i> 
                                    @if(auth()->user()->role_name === 'Support Role')
                                        Process & Send for Approval
                                    @else
                                        Process & Complete
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const scenarioSelect = document.getElementById('processScenario');
                        const fieldsContainer = document.getElementById('processScenarioFields');
                        const submitBtn = document.getElementById('processSubmitBtn');
                        const studentUserId = {{ $supportRequest->user_id ?? 'null' }};

                        // Current values for pre-filling
                        const currentValues = @json($currentValues);

                        // Check if this is non-paid access (no refund scenarios allowed)
                        const isNonPaidAccess = '{{ $supportRequest->flow_type }}' === 'flow_no_refund';

                        // All active webinars (passed from controller)
                        const allWebinars = @json($allWebinars ?? []);

                        // Student's purchased courses (passed from controller)
                        const studentPurchases = @json($studentPurchases ?? []);

                        // Student's expired courses (for extension)
                        const expiredCourses = @json($expiredCourses ?? []);

                        // Student's installment courses
                        const installmentCourses = @json($installmentCourses ?? []);

                        // Student's refundable courses
                        const refundableCourses = @json($refundableCourses ?? []);

                        scenarioSelect.addEventListener('change', function() {
                            const scenario = this.value;
                            fieldsContainer.innerHTML = '';
                            submitBtn.disabled = !scenario;

                            if (!scenario) return;

                            let html = '';

                            switch (scenario) {
                                case 'refund_payment':
                                    // Check if this is non-paid access - if so, show error message
                                    if (isNonPaidAccess) {
                                        html = `<div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Refund Not Available:</strong> This course was accessed through non-paid access (free, mentor, or temporary access). Refund scenarios are not applicable for non-paid access courses.
                                        </div>`;
                                        submitBtn.disabled = true;
                                    } else {
                                        html = buildCourseSelect('webinar_id', 'Select Course for Refund', refundableCourses.length > 0 ? refundableCourses : studentPurchases, true, currentValues.webinar_id);
                                        html += buildTextarea('refund_reason', 'Refund Reason', currentValues.refund_reason);
                                        html += `<div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="credit_to_wallet" class="custom-control-input" id="creditToWallet" value="1">
                                                <label class="custom-control-label" for="creditToWallet">
                                                    <strong>Credit to Wallet Now</strong><br>
                                                    <small class="text-muted">Check this box to credit the refund to user's wallet immediately. Uncheck to create pending refund for manual processing later.</small>
                                                </label>
                                            </div>
                                        </div>`;
                                    }
                                    break;
                                case 'course_extension':
                                    html = buildCourseSelect('webinar_id', 'Select Expired Course', expiredCourses, true, currentValues.webinar_id);
                                    html += `<div class="form-group"><label>Extension Period <span class="text-danger">*</span></label>
                                        <select name="extension_days" class="form-control" required>
                                            <option value="7" ${currentValues.extension_days == 7 ? 'selected' : ''}>7 Days</option>
                                            <option value="15" ${currentValues.extension_days == 15 ? 'selected' : ''}>15 Days</option>
                                            <option value="30" ${currentValues.extension_days == 30 ? 'selected' : ''}>30 Days</option>
                                        </select></div>`;
                                    html += buildTextarea('extension_reason', 'Reason', currentValues.extension_reason);
                                    break;

                                case 'temporary_access':
                                    html = buildCourseSelect('webinar_id', 'Select Course', allWebinars, true, currentValues.webinar_id);
                                    html += `<div class="form-group"><label>Duration <span class="text-danger">*</span></label>
                                        <select name="temporary_access_days" class="form-control" required>
                                            <option value="7" ${currentValues.temporary_access_days == 7 ? 'selected' : ''}>7 Days</option>
                                            <option value="15" ${currentValues.temporary_access_days == 15 ? 'selected' : ''}>15 Days</option>
                                        </select></div>`;
                                    html += `<div class="form-group"><label>Access Percentage (%) <span class="text-danger">*</span></label>
                                        <input type="number" name="temporary_access_percentage" class="form-control" min="1" max="100" value="${(currentValues.temporary_access_percentage !== null && currentValues.temporary_access_percentage !== undefined) ? currentValues.temporary_access_percentage : 100}" required></div>`;
                                    break;

                                case 'mentor_access':
                                    html = buildCourseSelect('webinar_id', 'Select Course', allWebinars, true, currentValues.webinar_id);
                                    html += buildTextarea('mentor_change_reason', 'Reason', currentValues.mentor_change_reason);
                                    break;

                                case 'relatives_friends_access':
                                    html = buildCourseSelect('webinar_id', 'Select Course', allWebinars, true, currentValues.webinar_id);
                                    html += buildTextarea('relative_description', 'Description', currentValues.relative_description);
                                    break;

                                case 'free_course_grant':
                                    html = buildCourseSelect('webinar_id', 'Select Course', allWebinars, true, currentValues.webinar_id);
                                    html += buildTextarea('free_course_reason', 'Reason', currentValues.free_course_reason);
                                    break;

                                case 'offline_cash_payment':
                                    html += `<div class="alert alert-info py-2 mb-2"><i class="fas fa-info-circle"></i> Cash will be credited directly to student's wallet.</div>`;
                                    html += `<div class="form-group"><label>Amount Paid (₹) <span class="text-danger">*</span></label>
                                        <input type="number" name="cash_amount" class="form-control" step="1" min="0" value="${(currentValues.cash_amount !== null && currentValues.cash_amount !== undefined) ? currentValues.cash_amount : ''}" required></div>`;
                                    html += `<div class="form-group"><label>Transaction ID / UTR</label>
                                        <input type="text" name="payment_receipt_number" class="form-control" value="${currentValues.payment_receipt_number || ''}"></div>`;
                                    html += `<div class="form-group"><label>Payment Date</label>
                                        <input type="date" name="payment_date" class="form-control" value="${currentValues.payment_date || ''}"></div>`;
                                    html += `<div class="form-group"><label>Bank/Location</label>
                                        <input type="text" name="payment_location" class="form-control" value="${currentValues.payment_location || ''}"></div>`;
                                    break;

                                case 'installment_restructure':
                                    html = buildCourseSelect('webinar_id', 'Select Installment Course', installmentCourses.length > 0 ? installmentCourses : studentPurchases, true, currentValues.webinar_id);
                                    html += buildTextarea('restructure_reason', 'Reason', currentValues.restructure_reason);
                                    break;

                                case 'refund_payment':
                                    html = buildCourseSelect('webinar_id', 'Select Course for Refund', refundableCourses.length > 0 ? refundableCourses : studentPurchases, true, currentValues.webinar_id);
                                    html += buildTextarea('refund_reason', 'Refund Reason', currentValues.refund_reason);
                                    html += `<div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="credit_to_wallet" class="custom-control-input" id="creditToWallet" value="1">
                                            <label class="custom-control-label" for="creditToWallet">
                                                <strong>Credit to Wallet Now</strong><br>
                                                <small class="text-muted">Check this box to credit the refund to user's wallet immediately. Uncheck to create pending refund for manual processing later.</small>
                                            </label>
                                        </div>
                                    </div>`;
                                    break;

                                case 'post_purchase_coupon':
                                    html = buildCourseSelect('webinar_id', 'Select Purchased Course', studentPurchases, true, currentValues.webinar_id);
                                    html += `<div class="form-group">
                                        <label>Coupon Code <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="coupon_code" id="ppcCouponInput" class="form-control" placeholder="Enter coupon code" value="${escapeHtml(currentValues.coupon_code || '')}" style="text-transform:uppercase;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary" id="ppcValidateCouponBtn">
                                                    <i class="fas fa-tag"></i> Apply Coupon
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text" id="ppcCouponFeedback"></small>
                                    </div>`;
                                    html += buildTextarea('coupon_apply_reason', 'Reason', currentValues.coupon_apply_reason);

                                    // Wire validate button after HTML is injected
                                    setTimeout(function() {
                                        const btn = document.getElementById('ppcValidateCouponBtn');
                                        const input = document.getElementById('ppcCouponInput');
                                        const feedback = document.getElementById('ppcCouponFeedback');
                                        const courseSelect = fieldsContainer.querySelector('select[name="webinar_id"]');
                                        if (!btn || !input) return;

                                        btn.addEventListener('click', function() {
                                            const code = input.value.trim().toUpperCase();
                                            const webinarId = courseSelect ? courseSelect.value : '';
                                            if (!code) { feedback.innerHTML = '<span class="text-danger">Please enter a coupon code.</span>'; return; }
                                            btn.disabled = true;
                                            btn.textContent = 'Validating...';
                                            feedback.innerHTML = '';

                                            fetch('{{ route("admin.support.validateCoupon") }}', {
                                                method: 'POST',
                                                credentials: 'same-origin',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({ coupon_code: code, webinar_id: webinarId })
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                if (data.success) {
                                                    input.value = code;
                                                    feedback.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + (data.message || 'Coupon is valid!') + '</span>';
                                                } else {
                                                    feedback.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> ' + (data.message || 'Invalid coupon.') + '</span>';
                                                }
                                            })
                                            .catch(() => { feedback.innerHTML = '<span class="text-danger">Validation request failed.</span>'; })
                                            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-tag"></i> Apply Coupon'; });
                                        });
                                    }, 50);
                                    break;

                            }

                            fieldsContainer.innerHTML = html;
                        });

                        function buildCourseSelect(name, label, courses, required, defaultValue) {
                            let options = '<option value="">-- Select --</option>';
                            (courses || []).forEach(function(c) {
                                const selected = (defaultValue == c.id) ? 'selected' : '';
                                options += '<option value="' + c.id + '" ' + selected + '>' + escapeHtml(c.title || ('Course #' + c.id)) + '</option>';
                            });
                            return '<div class="form-group"><label>' + label + (required ? ' <span class="text-danger">*</span>' : '') + '</label>' +
                                '<select name="' + name + '" class="form-control"' + (required ? ' required' : '') + '>' + options + '</select></div>';
                        }

                        function buildTextarea(name, label, defaultValue) {
                            return '<div class="form-group"><label>' + label + '</label>' +
                                '<textarea name="' + name + '" class="form-control" rows="2">' + escapeHtml(defaultValue || '') + '</textarea></div>';
                        }

                        function escapeHtml(str) {
                            if (!str) return '';
                            const div = document.createElement('div');
                            div.textContent = str;
                            return div.innerHTML;
                        }

                        // Trigger change on load if scenario exists
                        if (scenarioSelect.value) {
                            // If current scenario is refund_payment but flow is no_refund, clear it
                            if (isNonPaidAccess && scenarioSelect.value === 'refund_payment') {
                                scenarioSelect.value = '';
                                fieldsContainer.innerHTML = `<div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Scenario Cleared:</strong> The previously selected "Refund Payment" scenario is not applicable for non-paid access courses. Please select a different scenario.
                                </div>`;
                                submitBtn.disabled = true;
                            } else {
                                scenarioSelect.dispatchEvent(new Event('change'));
                            }
                        }
                    });

                    </script>
                    @endif
                </div>

                <div class="col-12 col-md-4">

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

                                @if($supportRequest->support_scenario === 'post_purchase_coupon' && (auth()->user()->role_name === 'Support Role' || auth()->user()->role_name === 'admin'))
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
            {{-- Status options based on role --}}
            @if(Auth::user()->role_name === 'Support Role')
                <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                {{-- NOTE: 'approved' (Process) is removed from here for Support Role --}}
                {{-- They must use the "Process / Take Action" card above to approve/process --}}
                <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
            @endif
            
            @if(Auth::user()->role_name === 'admin')
                <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
            @endif
            
            @if(Auth::user()->role_name !== 'Support Role' && Auth::user()->role_name !== 'admin')
                <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                <option value="executed" {{ $supportRequest->status == 'executed' ? 'selected' : '' }}>Executed</option>
                <option value="closed" {{ $supportRequest->status == 'closed' ? 'selected' : '' }}>Closed</option>
            @endif
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
                                @if($supportRequest->flow_type === 'flow_no_refund')
                                    <li class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Non-Paid Access:</strong> This course was accessed for free, through mentor, or temporary access. Refund scenarios are not applicable.
                                        @if($supportRequest->access_type)
                                            <br><small>Access Type: {{ ucfirst($supportRequest->access_type) }}</small>
                                        @endif
                                    </li>
                                @endif
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
                resultDiv.innerHTML = `<strong><i class="fa fa-check-circle"></i> Coupon valid</strong> — discount will be applied when you submit.<br>`;
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