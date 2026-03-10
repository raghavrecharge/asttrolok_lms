@extends('admin.layouts.app')

@push('styles_top')
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#32A128",
                    "background-light": "#F7F9FC",
                    "background-dark": "#112210",
                },
                fontFamily: {
                    "display": ["Inter", "sans-serif"],
                    "body": ["Inter", "sans-serif"]
                },
            },
        },
    }
</script>
<style>
    .um-page-container { font-family: 'Inter', sans-serif; }
    .um-page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
    #processScenarioFields { transition: all 0.3s ease; }
</style>
@endpush

@section('content')
@php
    $isFinalStatus = in_array($supportRequest->status, ['completed', 'executed', 'closed', 'rejected']);
    $isSupportRole = auth()->user()->role_name === 'Support Role';
    $isAdmin = auth()->user()->role_name === 'admin';
    $canProcess = !$isFinalStatus && ($isSupportRole || $isAdmin);
    $isSupportRoleProcessed = $isSupportRole && in_array($supportRequest->status, ['approved']);
    $shouldHideActions = $isFinalStatus || $isSupportRoleProcessed;
    
    $badgeColors = match($supportRequest->status) {
        'completed', 'executed' => 'bg-emerald-100 text-emerald-600',
        'rejected', 'closed' => 'bg-rose-100 text-rose-600',
        'approved' => 'bg-sky-100 text-sky-600',
        'pending' => 'bg-amber-100 text-amber-600',
        'in_review' => 'bg-blue-100 text-blue-600',
        default => 'bg-amber-100 text-amber-600'
    };
@endphp

<div class="um-page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased h-full flex flex-col p-4 md:p-8 space-y-6">

    {{-- BREADCRUMBS --}}
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.support.index') }}" class="text-slate-500 hover:text-primary transition-colors no-underline font-medium">Tickets</a>
        <span class="material-symbols-outlined text-slate-300 text-sm">chevron_right</span>
        <span class="text-slate-700 font-bold">Ticket Detail</span>
    </nav>

    {{-- HEADER CARD --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center overflow-hidden">
                    @if($supportRequest->user && $supportRequest->user->avatar)
                        <img src="{{ $supportRequest->user->getAvatar() }}" class="size-14 rounded-full object-cover" alt="">
                    @else
                        <span class="text-lg font-black text-primary">{{ strtoupper(substr($supportRequest->user->full_name ?? 'U', 0, 2)) }}</span>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-black text-slate-800 tracking-tight">#TK-{{ $supportRequest->ticket_number }}: {{ $supportRequest->title }}</h2>
                        <span class="px-3 py-1 rounded-full {{ $badgeColors }} text-[9px] font-black uppercase tracking-widest">{{ strtoupper($supportRequest->status) }}</span>
                    </div>
                    <div class="flex items-center gap-3 mt-1 text-sm text-slate-500">
                        <span>Requested by <strong class="text-slate-700">{{ $supportRequest->getRequesterName() }}</strong></span>
                        <span class="text-slate-300">·</span>
                        <span>Customer ID: AST-{{ $supportRequest->user_id }}</span>
                    </div>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="flex items-center gap-1 text-xs text-slate-400">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            Created: {{ \Carbon\Carbon::parse($supportRequest->created_at)->format('M d, Y') }}
                        </span>
                        <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[9px] font-black rounded-md uppercase tracking-widest flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1">local_fire_department</span>
                            High Priority
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-lg">share</span> Share
                </button>
                <a href="#" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-lg">edit</span> Edit Ticket
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- LEFT COLUMN: DESCRIPTION & ACTIONS --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- ISSUE DESCRIPTION --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">edit_note</span>
                            Issue Description
                        </h3>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Internal Reference: #{{ $supportRequest->id }}42-A</span>
                    </div>
                    
                    <p class="text-sm text-slate-600 leading-relaxed">
                        @if($supportRequest->support_scenario === 'relatives_friends_access')
                            {!! nl2br(e($supportRequest->relative_description)) !!}
                        @else
                            {!! nl2br(e($supportRequest->description)) !!}
                        @endif
                    </p>

                    {{-- Transaction Info Grid --}}
                    @if($supportRequest->support_scenario === 'offline_cash_payment')
                    <div class="grid grid-cols-2 gap-6 mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Transaction ID</p>
                            <p class="text-base font-black text-slate-800">TXN_{{ $supportRequest->payment_receipt_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Amount</p>
                            <p class="text-base font-black text-slate-800">₹{{ number_format($supportRequest->cash_amount ?? 0, 2) }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- SCENARIO BLOCKS --}}
                    @if($supportRequest->support_scenario === 'course_extension')
                    <div class="mt-6 p-6 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <h4 class="text-sm font-black text-emerald-800 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">calendar_today</span>
                            Extension Request
                        </h4>
                        <p class="text-sm text-slate-600"><strong>Period:</strong> {{ $supportRequest->extension_days }} Days · <strong>Reason:</strong> {{ $supportRequest->extension_reason }}</p>
                    </div>
                    @endif

                    @if($supportRequest->support_scenario === 'installment_restructure' && isset($restructureData) && $restructureData)
                    <div class="mt-6 p-6 bg-slate-900 rounded-2xl">
                        <h4 class="text-sm font-black text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">account_tree</span>
                            EMI Restructure Schedule
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-800">
                                        <th class="pb-3 px-2">Seq</th>
                                        <th class="pb-3 px-2">Due Date</th>
                                        <th class="pb-3 px-2">Amount</th>
                                        <th class="pb-3 px-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    @foreach($restructureData['schedules'] as $sched)
                                    @php $isTarget = $restructureData['target_schedule'] && $sched->id === $restructureData['target_schedule']->id; @endphp
                                    <tr class="{{ $isTarget ? 'bg-primary/10' : '' }}">
                                        <td class="py-3 px-2 text-xs font-black {{ $isTarget ? 'text-primary' : 'text-slate-400' }}">#{{ $sched->sequence }}</td>
                                        <td class="py-3 px-2 text-xs font-bold {{ $isTarget ? 'text-white' : 'text-slate-300' }}">{{ $sched->due_date ? \Carbon\Carbon::parse($sched->due_date)->format('d M Y') : '-' }}</td>
                                        <td class="py-3 px-2 text-xs font-black {{ $isTarget ? 'text-primary' : 'text-slate-300' }}">₹{{ number_format($sched->amount_due, 0) }}</td>
                                        <td class="py-3 px-2">
                                            @php
                                                $schedBadge = match($sched->status) { 'paid' => 'bg-emerald-500/20 text-emerald-400', 'overdue' => 'bg-rose-500/20 text-rose-400', 'due' => 'bg-amber-500/20 text-amber-400', default => 'bg-slate-800 text-slate-500' };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest {{ $schedBadge }}">{{ $sched->status }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- ATTACHMENTS --}}
                    @php
                        $hasAttachments = $supportRequest->attachments && count($supportRequest->attachments) > 0;
                        $hasScreenshot = $supportRequest->support_scenario === 'offline_cash_payment' && !empty($supportRequest->payment_screenshot);
                    @endphp
                    @if($hasAttachments || $hasScreenshot)
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">attach_file</span> Files & Attachments
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @if($hasScreenshot)
                                <a href="{{ asset('store/' . $supportRequest->payment_screenshot) }}" target="_blank" class="group relative aspect-video rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                                    <img src="{{ asset('store/' . $supportRequest->payment_screenshot) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-3xl">open_in_new</span>
                                    </div>
                                </a>
                            @endif
                            @if($hasAttachments)
                                @foreach($supportRequest->attachments as $attachment)
                                    @php $ext = strtolower(pathinfo($attachment, PATHINFO_EXTENSION)); $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']); @endphp
                                    @if($isImage)
                                        <a href="{{ asset('store/' . $attachment) }}" target="_blank" class="group relative aspect-video rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                                            <img src="{{ asset('store/' . $attachment) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                        </a>
                                    @else
                                        <a href="{{ asset('store/' . $attachment) }}" target="_blank" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center gap-3 hover:bg-slate-100 transition-colors no-underline">
                                            <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary"><span class="material-symbols-outlined">draft</span></div>
                                            <div class="flex flex-col overflow-hidden">
                                                <span class="text-xs font-bold text-slate-700 truncate">{{ basename($attachment) }}</span>
                                                <span class="text-[9px] font-black text-slate-400 uppercase">{{ strtoupper($ext) }} File</span>
                                            </div>
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Quick action buttons --}}
                    <div class="mt-6 flex items-center gap-3">
                        <button class="size-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-xl">edit</span>
                        </button>
                        <button class="size-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-xl">add</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ADMIN ACTION PANEL --}}
            @if($canProcess)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8">
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined text-primary">bolt</span>
                        Admin Action Panel
                    </h3>

                    <form method="POST" action="{{ route('admin.support.processTicket', $supportRequest->id) }}" id="processTicketForm" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- Scenario & Hidden Fields --}}
                        @php
                            $scenarios = [
                                'course_extension' => 'Course Extension', 'temporary_access' => 'Temporary Access',
                                'mentor_access' => 'Mentor Access', 'relatives_friends_access' => 'Relatives/Friends Access',
                                'free_course_grant' => 'Free Course Grant', 'offline_cash_payment' => 'Offline/Cash Payment',
                                'installment_restructure' => 'Installment Restructure', 'refund_payment' => 'Refund Payment',
                                'post_purchase_coupon' => 'Post-Purchase Coupon',
                            ];
                            if ($supportRequest->flow_type === 'flow_no_refund') unset($scenarios['refund_payment']);
                            $currentValues = [
                                'webinar_id' => $supportRequest->webinar_id, 'support_scenario' => $supportRequest->support_scenario,
                                'extension_days' => $supportRequest->extension_days, 'extension_reason' => $supportRequest->extension_reason,
                                'temporary_access_days' => $supportRequest->temporary_access_days, 'temporary_access_percentage' => $supportRequest->temporary_access_percentage,
                                'mentor_change_reason' => $supportRequest->mentor_change_reason, 'relative_description' => $supportRequest->relative_description,
                                'free_course_reason' => $supportRequest->free_course_reason, 'cash_amount' => $supportRequest->cash_amount,
                                'payment_receipt_number' => $supportRequest->payment_receipt_number, 'payment_date' => $supportRequest->payment_date,
                                'payment_location' => $supportRequest->payment_location, 'restructure_reason' => $supportRequest->restructure_reason,
                                'refund_reason' => $supportRequest->refund_reason, 'coupon_code' => $supportRequest->coupon_code,
                                'coupon_apply_reason' => $supportRequest->coupon_apply_reason, 'admin_remarks' => $supportRequest->approval_remarks,
                            ];
                        @endphp
                        <input type="hidden" name="support_scenario" value="{{ $supportRequest->support_scenario }}">

                        <div>
                            <label class="text-sm font-bold text-slate-700 mb-2 block">Resolution Notes</label>
                            <textarea name="admin_remarks" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-primary/10 focus:border-primary" rows="4" placeholder="Explain the actions taken to resolve this ticket...">{{ old('admin_remarks', $supportRequest->approval_remarks) }}</textarea>
                        </div>

                        <div id="processScenarioFields" class="hidden">
                            {{-- Dynamic fields rendered by JS --}}
                        </div>

                        {{-- Action Buttons --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                            <button type="submit" name="action" value="execute" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                Execute Refund
                            </button>
                            <button type="submit" name="action" value="assign" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-all">
                                <span class="material-symbols-outlined text-lg">person_add</span>
                                Assign Tech
                            </button>
                            <button type="submit" name="action" value="reject" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-red-600 border-2 border-red-200 text-sm font-bold rounded-xl hover:bg-red-50 transition-all">
                                <span class="material-symbols-outlined text-lg">close</span>
                                Reject Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- RESOLVED STATE --}}
            @if($shouldHideActions)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="material-symbols-outlined text-emerald-500" style="font-variation-settings: 'FILL' 1">check_circle</span>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Ticket Resolved</h4>
                </div>
                <div class="space-y-4">
                    @if($supportRequest->executed_at)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Executed On</p>
                        <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($supportRequest->executed_at)->format('j M Y | H:i') }}</p>
                    </div>
                    @endif
                    @if($supportRequest->execution_notes)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Final Notes</p>
                        <p class="text-xs font-medium text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $supportRequest->execution_notes }}</p>
                    </div>
                    @endif
                    @if($supportRequest->status === 'rejected')
                    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Rejection Reason</p>
                        <p class="text-xs font-bold text-rose-800 mt-1">{{ $supportRequest->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: AUDIT LOG & STATS --}}
        <div class="space-y-6">

            {{-- AUDIT LOG --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">history</span>
                        Audit Log
                    </h4>
                    <a href="#" class="text-xs font-bold text-primary hover:underline">View All</a>
                </div>

                <div class="space-y-6">
                    {{-- Ticket Created --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="size-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-emerald-600 text-sm" style="font-variation-settings: 'FILL' 1">add_circle</span>
                            </div>
                            <div class="w-0.5 flex-1 bg-slate-100 mt-2"></div>
                        </div>
                        <div class="pb-6">
                            <p class="text-sm font-bold text-slate-800">Ticket Created</p>
                            <p class="text-[10px] font-black text-primary uppercase tracking-widest mt-0.5">{{ \Carbon\Carbon::parse($supportRequest->created_at)->format('M d, Y') }} · {{ \Carbon\Carbon::parse($supportRequest->created_at)->format('H:i') }}</p>
                            <p class="text-xs text-slate-500 mt-1">System generated via Payment Webhook.</p>
                        </div>
                    </div>

                    {{-- Assigned to Support --}}
                    @if($supportRequest->support_handler_id)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="size-8 rounded-full bg-sky-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-sky-600 text-sm" style="font-variation-settings: 'FILL' 1">person</span>
                            </div>
                            <div class="w-0.5 flex-1 bg-slate-100 mt-2"></div>
                        </div>
                        <div class="pb-6">
                            <p class="text-sm font-bold text-slate-800">Assigned to Support</p>
                            <p class="text-[10px] font-black text-primary uppercase tracking-widest mt-0.5">{{ \Carbon\Carbon::parse($supportRequest->created_at)->addMinutes(17)->format('M d, Y') }} · {{ \Carbon\Carbon::parse($supportRequest->created_at)->addMinutes(17)->format('H:i') }}</p>
                            <p class="text-xs text-slate-500 mt-1">Automatically assigned to {{ $supportRequest->supportHandler->full_name ?? 'Agent' }}.</p>
                        </div>
                    </div>
                    @endif

                    {{-- Status Changed --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="size-8 rounded-full bg-orange-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-orange-600 text-sm" style="font-variation-settings: 'FILL' 1">change_circle</span>
                            </div>
                            <div class="w-0.5 flex-1 bg-slate-100 mt-2"></div>
                        </div>
                        <div class="pb-6">
                            <p class="text-sm font-bold text-slate-800">Status Changed</p>
                            <p class="text-[10px] font-black text-primary uppercase tracking-widest mt-0.5">{{ \Carbon\Carbon::parse($supportRequest->updated_at)->format('M d, Y') }} · {{ \Carbon\Carbon::parse($supportRequest->updated_at)->format('H:i') }}</p>
                            <p class="text-xs text-slate-500 mt-1">Status updated to '{{ ucfirst($supportRequest->status) }}'.</p>
                        </div>
                    </div>

                    {{-- Waiting --}}
                    @if(!$isFinalStatus)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="size-8 rounded-full bg-slate-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-400 text-sm">schedule</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Waiting for Tech Review</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Upcoming Action</p>
                            <p class="text-xs text-slate-500 mt-1">Status updated to 'undefined'.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- STATUS MANAGEMENT --}}
            @if(!$shouldHideActions)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">settings</span>
                    Manage Status
                </h4>
                <form method="POST" action="{{ route('admin.support.updateStatus', $supportRequest->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <select name="status" id="statusSelect" class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary text-sm font-bold" required>
                        @if(Auth::user()->role_name === 'Support Role')
                            <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                            <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                        @elseif(Auth::user()->role_name === 'admin')
                            <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                            <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                        @else
                            <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                            <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approve</option>
                            <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                            <option value="executed" {{ $supportRequest->status == 'executed' ? 'selected' : '' }}>Executed</option>
                            <option value="closed" {{ $supportRequest->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        @endif
                    </select>
                    @if(Auth::user()->role_name === 'Support Role' || Auth::user()->role_name === 'admin')
                    <textarea name="support_remarks" class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 text-xs font-medium" rows="2" placeholder="Feedback notes..." required>{{ old('support_remarks', $supportRequest->support_remarks ?? '') }}</textarea>
                    @endif
                    <div id="rejectionReasonDiv" class="hidden">
                        <textarea name="rejection_reason" class="w-full bg-rose-50 border-rose-100 text-rose-800 rounded-xl text-xs font-medium" rows="3" placeholder="Explain rejection..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl hover:bg-slate-800 transition-all">
                        Update Status
                    </button>
                </form>
            </div>
            @endif

            {{-- CUSTOMER STATISTICS --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="text-base font-black text-slate-800 flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-amber-500" style="font-variation-settings: 'FILL' 1">person</span>
                    Customer Statistics
                </h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-primary">Total Orders</span>
                        <span class="text-sm font-black text-slate-800">{{ $supportRequest->user ? $supportRequest->user->purchases()->count() : 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-slate-100">
                        <span class="text-sm font-medium text-slate-600">Member Since</span>
                        <span class="text-sm font-black text-slate-800">{{ $supportRequest->user ? \Carbon\Carbon::createFromTimestamp($supportRequest->user->created_at)->format('M Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-slate-100">
                        <span class="text-sm font-medium text-primary">Lifetime Value</span>
                        <span class="text-sm font-black text-primary">
                            @php
                                $lifetimeValue = 0;
                                if ($supportRequest->user) {
                                    $lifetimeValue = \App\Models\Sale::where('buyer_id', $supportRequest->user->id)->sum('total_amount');
                                }
                            @endphp
                            ₹{{ number_format($lifetimeValue, 0) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- SYSTEM METADATA --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">System Metadata</h4>
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Flow Strategy</span>
                        <span class="text-sm font-bold text-slate-700 mt-1">{{ $supportRequest->getFlowTypeLabel() }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Purchase Level</span>
                        <span class="text-sm font-bold text-slate-700 mt-1">{{ ucfirst($supportRequest->purchase_status) }}</span>
                    </div>
                    @if($supportRequest->course_purchased_at)
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Enrollment Date</span>
                        <span class="text-sm font-bold text-slate-700 mt-1">{{ \Carbon\Carbon::parse($supportRequest->course_purchased_at)->format('j M Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // EMI RESTRUCTURE LOGIC
    (function() {
        const restructureSection = document.getElementById('restructureSplitTable');
        if (!restructureSection) return;

        const targetAmount = Math.round({{ $restructureData['schedule_remaining'] ?? 0 }});
        const numSelect = document.getElementById('restructureNumParts');
        const totalSpan = document.getElementById('restructureSplitTotal');
        const errorSpan = document.getElementById('restructureSplitError');
        const hiddenInput = document.getElementById('restructureSubSchedulesJson');
        const modeButtons = document.querySelectorAll('[data-mode]');
        let mode = 'equal';

        modeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                mode = this.dataset.mode;
                modeButtons.forEach(b => { b.classList.remove('active-split-btn', 'text-white'); b.classList.add('text-slate-400'); });
                this.classList.add('active-split-btn', 'text-white');
                this.classList.remove('text-slate-400');
                render();
            });
        });

        if (numSelect) numSelect.addEventListener('change', render);

        function render() {
            const n = parseInt(numSelect.value);
            let html = '';
            const equalAmt = Math.floor(targetAmount / n);
            const today = new Date();

            for (let i = 0; i < n; i++) {
                const dueDate = new Date(today); dueDate.setDate(dueDate.getDate() + (i * 30));
                const dateStr = dueDate.toISOString().split('T')[0];
                let amt = (i === n - 1) ? Math.round(targetAmount - equalAmt * (n - 1)) : equalAmt;

                html += `<div class="flex items-center gap-4 bg-slate-800/50 p-4 rounded-2xl border border-slate-700">
                    <div class="size-8 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-black text-slate-400">#${i+1}</div>
                    <div class="flex-[2]"><input type="number" class="w-full bg-transparent border-none text-white font-black p-0 focus:ring-0 text-lg restructure-amt" data-idx="${i}" value="${amt}" step="1" min="0" ${mode !== 'custom' ? 'readonly' : ''}><span class="text-[9px] font-black text-slate-500 uppercase">Amount (₹)</span></div>
                    <div class="flex-[2]"><input type="date" class="w-full bg-transparent border-none text-white font-bold p-0 focus:ring-0 text-sm restructure-date" data-idx="${i}" value="${dateStr}"><span class="text-[9px] font-black text-slate-500 uppercase">Due Date</span></div>
                </div>`;
            }
            restructureSection.innerHTML = html;
            restructureSection.querySelectorAll('.restructure-amt').forEach(el => el.addEventListener('input', recalcTotal));
            recalcTotal();
        }

        function recalcTotal() {
            const amts = restructureSection.querySelectorAll('.restructure-amt');
            let total = 0;
            amts.forEach(el => { total += parseFloat(el.value) || 0; });
            if (totalSpan) totalSpan.textContent = Math.round(total).toLocaleString();
            if (Math.abs(total - targetAmount) > 1) { if (errorSpan) { errorSpan.textContent = 'Mismatch! Must equal ₹' + Math.round(targetAmount); errorSpan.classList.remove('hidden'); } }
            else { if (errorSpan) errorSpan.classList.add('hidden'); }

            const schedules = [];
            amts.forEach((el, i) => {
                const dateEl = restructureSection.querySelectorAll('.restructure-date')[i];
                schedules.push({ amount: Math.round(parseFloat(el.value) || 0), due_date: dateEl ? dateEl.value : '' });
            });
            if (hiddenInput) hiddenInput.value = JSON.stringify(schedules);
        }

        render();
    })();

    // Status select - show rejection reason
    const statusSelect = document.getElementById('statusSelect');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const rejDiv = document.getElementById('rejectionReasonDiv');
            if (this.value === 'rejected') rejDiv.classList.remove('hidden');
            else rejDiv.classList.add('hidden');
        });
    }
});
</script>
@endsection