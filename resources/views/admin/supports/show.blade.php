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
                    "background-light": "#f6f8f5",
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
    body { font-family: 'Inter', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
    #processScenarioFields { transition: all 0.3s ease; }
    .soft-shadow { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
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
        'completed', 'executed' => 'bg-emerald-500 text-white',
        'rejected', 'closed' => 'bg-rose-500 text-white',
        'approved' => 'bg-sky-500 text-white',
        'pending' => 'bg-amber-500 text-white',
        'in_review' => 'bg-blue-500 text-white',
        default => 'bg-amber-500 text-white'
    };
@endphp

<div class="bg-background-light dark:bg-slate-950 font-display text-slate-900 dark:text-slate-100 min-h-screen -m-8">
    <div class="max-w-7xl mx-auto p-6 md:p-10">
        
        {{-- RAPID RESPONSE HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">
                    <a href="{{ route('admin.support.index') }}" class="hover:text-primary transition-colors">Tickets</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-slate-600">Sequential Triage</span>
                </nav>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    Rapid Response Agent
                    <span class="bg-primary/10 text-primary text-[10px] px-3 py-1 rounded-full uppercase tracking-widest">Active Processing</span>
                </h1>
                <p class="text-slate-500 text-sm mt-1">Focused Triage Flow • Sequential Processing Active for Ticket #TK-{{ $supportRequest->ticket_number }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white dark:bg-slate-900 px-6 py-3 rounded-2xl border border-primary/10 soft-shadow flex items-center gap-4">
                    <div class="size-2 bg-primary rounded-full animate-pulse"></div>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Priority Segment</p>
                    <span class="text-xs font-black text-red-500 bg-red-50 px-3 py-1 rounded-lg uppercase tracking-widest">High Load</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col xl:flex-row gap-8">
            <div class="flex-grow xl:w-2/3 space-y-8">
                
                {{-- ACTIVE TASK CARD --}}
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border-2 border-primary/20 shadow-2xl shadow-primary/5 overflow-hidden relative">
                    <div class="bg-primary px-10 py-5 flex justify-between items-center">
                        <div class="flex items-center gap-6">
                            <span class="bg-white text-primary text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">Active Task</span>
                            <span class="text-white/80 text-xs font-black uppercase tracking-widest">#TK-{{ $supportRequest->ticket_number }} • ID: {{ $supportRequest->id }}</span>
                        </div>
                        <div class="flex gap-2">
                             <span class="px-4 py-1.5 rounded-full {{ $badgeColors }} text-[10px] font-black uppercase tracking-widest border border-white/20">{{ strtoupper($supportRequest->status) }}</span>
                        </div>
                    </div>

                    <div class="p-10 md:p-14">
                        {{-- USER PROFILE SECTION --}}
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-12">
                            <div class="relative">
                                @if($supportRequest->user && $supportRequest->user->avatar)
                                    <img src="{{ $supportRequest->user->getAvatar() }}" class="size-24 rounded-3xl bg-slate-100 object-cover shadow-xl border-4 border-white" alt="">
                                @else
                                    <div class="size-24 rounded-3xl bg-primary/10 border-4 border-white shadow-xl flex items-center justify-center">
                                        <span class="text-3xl font-black text-primary">{{ strtoupper(substr($supportRequest->user->full_name ?? 'U', 0, 2)) }}</span>
                                    </div>
                                @endif
                                <div class="absolute -bottom-2 -right-2 bg-emerald-500 size-6 rounded-full border-4 border-white"></div>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <h3 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ $supportRequest->user->full_name ?? 'Unknown User' }}</h3>
                                <div class="flex flex-wrap justify-center md:justify-start gap-4 mt-2">
                                    <span class="text-sm font-bold text-primary bg-primary/5 px-4 py-1 rounded-full border border-primary/10">Premium Student</span>
                                    <span class="text-sm font-bold text-slate-400 bg-slate-50 px-4 py-1 rounded-full border border-slate-100 uppercase tracking-widest text-[10px]">AST-{{ $supportRequest->user_id }}</span>
                                    <span class="text-sm font-bold text-slate-500 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">event</span>
                                        Joined {{ \Carbon\Carbon::createFromTimestamp($supportRequest->user->created_at ?? time())->format('M Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-center md:text-right hidden md:block">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Category Meta</p>
                                <span class="inline-flex items-center gap-2 text-orange-600 bg-orange-50 px-5 py-2 rounded-full text-[10px] font-black border border-orange-100 uppercase tracking-widest shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                    Urgent Request
                                </span>
                            </div>
                        </div>

                        {{-- ISSUE DESCRIPTION --}}
                        <div class="bg-slate-50/80 dark:bg-slate-800/50 p-8 md:p-10 rounded-[2rem] border border-slate-100 dark:border-slate-800 mb-12 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-6 opacity-5">
                                <span class="material-symbols-outlined text-6xl">format_quote</span>
                            </div>
                            <p class="text-lg md:text-xl text-slate-700 dark:text-slate-300 leading-relaxed font-medium italic">
                                "@if($supportRequest->support_scenario === 'relatives_friends_access')
                                    {!! nl2br(e($supportRequest->relative_description)) !!}
                                @else
                                    {!! nl2br(e($supportRequest->description)) !!}
                                @endif"
                            </p>
                        </div>

                        {{-- SCENARIO BLOCKS --}}
                        @if($supportRequest->support_scenario === 'offline_cash_payment')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                            <div class="p-8 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Transaction ID</p>
                                <p class="text-xl font-black text-slate-800 dark:text-white">TXN_{{ $supportRequest->payment_receipt_number ?? 'N/A' }}</p>
                            </div>
                            <div class="p-8 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Amount Paid</p>
                                <p class="text-xl font-black text-primary">₹{{ number_format($supportRequest->cash_amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                        @endif

                        @if($supportRequest->support_scenario === 'course_extension')
                        <div class="mb-12 p-8 bg-emerald-50 dark:bg-emerald-500/5 rounded-3xl border border-emerald-100 dark:border-emerald-500/20">
                            <h4 class="text-xs font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">calendar_today</span>
                                Extension Request Details
                            </h4>
                            <div class="flex flex-col md:flex-row gap-6">
                                <div class="flex-1">
                                    <p class="text-[10px] font-black text-emerald-600/60 uppercase tracking-widest mb-1">Requested Period</p>
                                    <p class="text-lg font-black text-emerald-900 dark:text-emerald-300">{{ $supportRequest->extension_days }} Days</p>
                                </div>
                                <div class="flex-[3]">
                                    <p class="text-[10px] font-black text-emerald-600/60 uppercase tracking-widest mb-1">Reason provided</p>
                                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400">{{ $supportRequest->extension_reason }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($supportRequest->support_scenario === 'installment_restructure' && isset($restructureData) && $restructureData)
                        <div class="mb-12 bg-slate-900 dark:bg-black rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
                            <div class="p-8 border-b border-slate-800 flex items-center justify-between">
                                <h4 class="text-xs font-black text-white uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">account_tree</span>
                                    Proposed EMI Restructure Schedule
                                </h4>
                                <span class="bg-primary/20 text-primary text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Active Plan</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-800/50">
                                            <th class="py-5 px-8">Sequence</th>
                                            <th class="py-5 px-8">Due Date</th>
                                            <th class="py-5 px-8">Amount</th>
                                            <th class="py-5 px-8 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/50">
                                        @foreach($restructureData['schedules'] as $sched)
                                        @php $isTarget = $restructureData['target_schedule'] && $sched->id === $restructureData['target_schedule']->id; @endphp
                                        <tr class="{{ $isTarget ? 'bg-primary/5' : 'hover:bg-white/5' }} transition-colors">
                                            <td class="py-5 px-8">
                                                <span class="text-xs font-black {{ $isTarget ? 'text-primary' : 'text-slate-500' }}">EMI #{{ $sched->sequence }}</span>
                                            </td>
                                            <td class="py-5 px-8">
                                                <span class="text-xs font-bold {{ $isTarget ? 'text-white' : 'text-slate-400' }}">{{ $sched->due_date ? \Carbon\Carbon::parse($sched->due_date)->format('d M Y') : '-' }}</span>
                                            </td>
                                            <td class="py-5 px-8">
                                                <span class="text-xs font-black {{ $isTarget ? 'text-primary' : 'text-slate-300' }}">₹{{ number_format($sched->amount_due, 0) }}</span>
                                            </td>
                                            <td class="py-5 px-8 text-right">
                                                @php
                                                    $schedBadge = match($sched->status) { 'paid' => 'bg-emerald-500/20 text-emerald-400', 'overdue' => 'bg-rose-500/20 text-rose-400', 'due' => 'bg-amber-500/20 text-amber-400', default => 'bg-slate-800 text-slate-500' };
                                                @endphp
                                                <span class="px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest {{ $schedBadge }} border border-white/5">{{ $sched->status }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- ATTACHMENTS SECTION --}}
                        @php
                            $hasAttachments = $supportRequest->attachments && count($supportRequest->attachments) > 0;
                            $hasScreenshot = $supportRequest->support_scenario === 'offline_cash_payment' && !empty($supportRequest->payment_screenshot);
                        @endphp
                        @if($hasAttachments || $hasScreenshot)
                        <div class="mb-12">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">attach_file</span> Verified Evidence & Attachments
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                @if($hasScreenshot)
                                    <div class="group relative aspect-[4/3] rounded-[2rem] overflow-hidden border-2 border-slate-100 bg-slate-50 soft-shadow">
                                        <img src="{{ asset('store/' . $supportRequest->payment_screenshot) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                                            <p class="text-[9px] font-black text-white uppercase tracking-widest">Payment Receipt</p>
                                        </div>
                                        <a href="{{ asset('store/' . $supportRequest->payment_screenshot) }}" target="_blank" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="material-symbols-outlined text-white text-3xl">zoom_in</span>
                                        </a>
                                    </div>
                                @endif
                                @if($hasAttachments)
                                    @foreach($supportRequest->attachments as $attachment)
                                        @php $ext = strtolower(pathinfo($attachment, PATHINFO_EXTENSION)); $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']); @endphp
                                        @if($isImage)
                                            <div class="group relative aspect-[4/3] rounded-[2rem] overflow-hidden border-2 border-slate-100 bg-slate-50 soft-shadow">
                                                <img src="{{ asset('store/' . $attachment) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                                <a href="{{ asset('store/' . $attachment) }}" target="_blank" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="material-symbols-outlined text-white text-3xl">zoom_in</span>
                                                </a>
                                            </div>
                                        @else
                                            <a href="{{ asset('store/' . $attachment) }}" target="_blank" class="group flex flex-col p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border-2 border-slate-100 dark:border-slate-800 hover:border-primary/30 transition-all soft-shadow no-underline">
                                                <div class="size-12 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-primary mb-4 shadow-sm group-hover:scale-110 transition-transform">
                                                    <span class="material-symbols-outlined">draft</span>
                                                </div>
                                                <span class="text-xs font-black text-slate-800 dark:text-white truncate mb-1">{{ basename($attachment) }}</span>
                                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ strtoupper($ext) }} Document</span>
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- TRIAGE STEPS SECTION --}}
                        <div class="space-y-10 pt-6">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <span class="size-5 bg-primary text-white text-[9px] rounded-full flex items-center justify-center shadow-lg shadow-primary/20">1</span>
                                    Identify & Confirm Scenario
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @php
                                        $scenarioIcons = [
                                            'technical' => 'settings_remote', 'billing' => 'payments',
                                            'course_access' => 'school', 'general' => 'chat'
                                        ];
                                        $currentCat = match($supportRequest->support_scenario) {
                                            'course_extension', 'temporary_access', 'relatives_friends_access', 'free_course_grant' => 'course_access',
                                            'offline_cash_payment', 'installment_restructure', 'refund_payment', 'post_purchase_coupon' => 'billing',
                                            default => 'general'
                                        };
                                    @endphp
                                    @foreach(['technical', 'billing', 'course_access', 'general'] as $cat)
                                        <div class="border-2 {{ $currentCat == $cat ? 'border-primary bg-primary/5' : 'border-slate-100 dark:border-slate-800' }} p-6 rounded-3xl flex flex-col items-center gap-3 transition-all">
                                            <span class="material-symbols-outlined {{ $currentCat == $cat ? 'text-primary' : 'text-slate-300' }} text-2xl">{{ $scenarioIcons[$cat] }}</span>
                                            <span class="text-[10px] font-black {{ $currentCat == $cat ? 'text-primary' : 'text-slate-500' }} uppercase tracking-widest">{{ ucfirst(str_replace('_', ' ', $cat)) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- PROCESSING FORM & PANEL --}}
                            @if($canProcess)
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <span class="size-5 bg-primary text-white text-[9px] rounded-full flex items-center justify-center shadow-lg shadow-primary/20">2</span>
                                    Resolution & Processing
                                </p>
                                
                                <form method="POST" action="{{ route('admin.support.processTicket', $supportRequest->id) }}" id="processTicketForm" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="support_scenario" value="{{ $supportRequest->support_scenario }}">
                                    
                                    <div class="bg-slate-50 dark:bg-slate-800/50 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-slate-800 overflow-hidden relative group">
                                         <textarea name="admin_remarks" class="w-full bg-transparent border-none text-lg font-bold text-slate-700 dark:text-slate-300 placeholder:text-slate-300 focus:ring-0 italic p-0" rows="3" placeholder="Click here to type internal resolution notes or final executive summary...">{{ old('admin_remarks', $supportRequest->approval_remarks) }}</textarea>
                                         <div class="h-0.5 w-full bg-slate-200 dark:bg-slate-700 mt-4 group-focus-within:bg-primary transition-colors"></div>
                                    </div>

                                    <div id="processScenarioFields" class="hidden mt-6">
                                        {{-- Dynamic fields rendered by JS --}}
                                    </div>

                                    <div class="mt-10">
                                        <button type="submit" name="action" value="execute" class="w-full bg-primary text-white py-6 rounded-3xl text-xl font-black hover:shadow-2xl hover:shadow-primary/30 transition-all flex items-center justify-center gap-4 group">
                                            <span class="material-symbols-outlined text-3xl group-hover:animate-bounce">rocket_launch</span>
                                            TRIAGE & EXECUTE RESOLUTION
                                        </button>
                                        <div class="grid grid-cols-2 gap-4 mt-4">
                                            <button type="submit" name="action" value="assign" class="bg-slate-900 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-800 transition-all">Assign Specialist</button>
                                            <button type="submit" name="action" value="reject" class="bg-white text-rose-500 border border-rose-200 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-rose-50 transition-all">Terminate Ticket</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @endif

                            {{-- RESOLVED STATE ALERT --}}
                            @if($shouldHideActions)
                            <div class="p-8 bg-emerald-50 dark:bg-emerald-500/5 rounded-[2.5rem] border-2 border-emerald-100 dark:border-emerald-500/10 flex items-center gap-8">
                                <div class="size-16 rounded-full bg-white dark:bg-slate-900 shadow-xl flex items-center justify-center text-emerald-500">
                                    <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1">verified</span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-emerald-800 dark:text-emerald-400 mb-1">Task Successfully Executed</h4>
                                    <p class="text-sm font-bold text-emerald-600/80">Ticket has been archived and resolution deployed on {{ \Carbon\Carbon::parse($supportRequest->executed_at ?? $supportRequest->updated_at)->format('d M Y - H:i') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: AUDIT LOG & STATS --}}
        <div class="xl:w-1/3 space-y-8">

            {{-- AUDIT LOG & PROGRESS --}}
            <div class="bg-white dark:bg-slate-950 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 p-8 md:p-10 soft-shadow">
                <div class="flex items-center justify-between mb-8">
                    <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">history</span>
                        Processing Timeline
                    </h4>
                    <span class="text-[9px] font-black text-primary px-3 py-1 bg-primary/5 rounded-full uppercase tracking-widest">Real-time</span>
                </div>

                <div class="space-y-8 relative">
                    <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-slate-50 dark:bg-slate-900"></div>

                    {{-- Ticket Created --}}
                    <div class="flex gap-6 relative group">
                        <div class="size-8 rounded-full bg-emerald-500 shadow-lg shadow-emerald-200 dark:shadow-none flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-white text-sm" style="font-variation-settings: 'FILL' 1">add_circle</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 dark:text-white">Request Initialized</p>
                            <p class="text-[9px] font-black text-primary uppercase tracking-widest mt-1">{{ \Carbon\Carbon::parse($supportRequest->created_at)->format('M d, Y • H:i') }}</p>
                            <p class="text-[11px] font-medium text-slate-400 mt-2 leading-relaxed">System-generated entry via incoming support channel.</p>
                        </div>
                    </div>

                    {{-- Assigned to Support --}}
                    @if($supportRequest->support_handler_id)
                    <div class="flex gap-6 relative group">
                        <div class="size-8 rounded-full bg-sky-500 shadow-lg shadow-sky-200 dark:shadow-none flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-white text-sm" style="font-variation-settings: 'FILL' 1">person</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 dark:text-white">Agent Assigned</p>
                            <p class="text-[9px] font-black text-primary uppercase tracking-widest mt-1">Routing Complete</p>
                            <p class="text-[11px] font-medium text-slate-400 mt-2 leading-relaxed">Automatically routed to <strong>{{ $supportRequest->supportHandler->full_name ?? 'Senior Agent' }}</strong>.</p>
                        </div>
                    </div>
                    @endif

                    {{-- Status Changed --}}
                    <div class="flex gap-6 relative group">
                        <div class="size-8 rounded-full bg-orange-500 shadow-lg shadow-orange-200 dark:shadow-none flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-white text-sm" style="font-variation-settings: 'FILL' 1">change_circle</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 dark:text-white">State Transition</p>
                            <p class="text-[9px] font-black text-primary uppercase tracking-widest mt-1">{{ \Carbon\Carbon::parse($supportRequest->updated_at)->format('H:i') }} • Update</p>
                            <p class="text-[11px] font-medium text-slate-400 mt-2 leading-relaxed">System status migrated to <span class="text-orange-600 font-bold">'{{ ucfirst($supportRequest->status) }}'</span>.</p>
                        </div>
                    </div>

                    {{-- Upcoming Action --}}
                    @if(!$isFinalStatus)
                    <div class="flex gap-6 relative group grayscale opacity-50">
                        <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center relative z-10">
                            <span class="material-symbols-outlined text-slate-400 text-sm">schedule</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-400 dark:text-slate-600">Archival & Closure</p>
                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mt-1">Awaiting Execution</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- QUICK STATUS MANAGEMENT --}}
            @if(!$shouldHideActions)
            <div class="bg-slate-900 rounded-[2.5rem] p-1 shadow-2xl overflow-hidden group">
                <div class="bg-white dark:bg-slate-950 rounded-[2.3rem] p-8 md:p-10">
                    <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest mb-8 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">bolt</span>
                        Quick Management
                    </h4>
                    <form method="POST" action="{{ route('admin.support.updateStatus', $supportRequest->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="relative">
                            <select name="status" id="statusSelect" class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800 rounded-2xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none" required>
                                @if(Auth::user()->role_name === 'Support Role')
                                    <option value="pending" {{ $supportRequest->status == 'pending' ? 'selected' : '' }}>Pending Stage</option>
                                    <option value="in_review" {{ $supportRequest->status == 'in_review' ? 'selected' : '' }}>Analysis / Review</option>
                                    <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Rejection</option>
                                @elseif(Auth::user()->role_name === 'admin')
                                    <option value="approved" {{ $supportRequest->status == 'approved' ? 'selected' : '' }}>Approval</option>
                                    <option value="completed" {{ $supportRequest->status == 'completed' ? 'selected' : '' }}>Complete Task</option>
                                    <option value="rejected" {{ $supportRequest->status == 'rejected' ? 'selected' : '' }}>Rejection</option>
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
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">unfold_more</span>
                        </div>

                        @if(Auth::user()->role_name === 'Support Role' || Auth::user()->role_name === 'admin')
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Internal Feedback</label>
                            <textarea name="support_remarks" class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800 rounded-2xl p-4 text-xs font-medium focus:ring-4 focus:ring-primary/10 appearance-none transition-all" rows="3" placeholder="Click to provide executive feedback..." required>{{ old('support_remarks', $supportRequest->support_remarks ?? '') }}</textarea>
                        </div>
                        @endif

                        <div id="rejectionReasonDiv" class="hidden animate-in fade-in slide-in-from-top-4 duration-300">
                             <div class="p-6 bg-rose-50 dark:bg-rose-500/5 rounded-2xl border-2 border-rose-100 dark:border-rose-500/20">
                                <label class="text-[10px] font-black text-rose-500 uppercase tracking-widest block mb-2">Detailed Fatal Error / Reason</label>
                                <textarea name="rejection_reason" class="w-full bg-transparent border-none p-0 text-xs font-bold text-rose-800 dark:text-rose-300 placeholder:text-rose-200 focus:ring-0" rows="3" placeholder="Describe the definitive reason for rejection..."></textarea>
                             </div>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 dark:bg-slate-800 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-black dark:hover:bg-slate-700 transition-all shadow-xl shadow-slate-900/10">
                            Apply Transition
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- CUSTOMER METRICS CARD --}}
            <div class="bg-white dark:bg-slate-950 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 overflow-hidden soft-shadow">
                <div class="p-8 md:p-10">
                    <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest mb-10 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500" style="font-variation-settings: 'FILL' 1">stars</span>
                        Customer Profile
                    </h4>

                    <div class="space-y-8">
                        <div class="flex items-center justify-between p-6 bg-slate-50 dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Purchases</p>
                                <p class="text-xl font-black text-slate-800 dark:text-white">{{ $supportRequest->user ? $supportRequest->user->purchases()->count() : 0 }}</p>
                            </div>
                            <div class="size-12 rounded-2xl bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined">shopping_bag</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-6 bg-slate-50 dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Lifetime Value</p>
                                @php
                                    $lifetimeValue = $supportRequest->user ? \App\Models\Sale::where('buyer_id', $supportRequest->user->id)->sum('total_amount') : 0;
                                @endphp
                                <p class="text-xl font-black text-primary">₹{{ number_format($lifetimeValue, 0) }}</p>
                            </div>
                            <div class="size-12 rounded-2xl bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center text-emerald-500 font-black text-lg italic">
                                LTV
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-6 bg-slate-50 dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2">Loyalty</p>
                                <p class="text-xs font-black text-slate-700 dark:text-white">{{ $supportRequest->user ? \Carbon\Carbon::createFromTimestamp($supportRequest->user->created_at)->diffForHumans(null, true) : 'N/A' }}</p>
                            </div>
                            <div class="p-6 bg-slate-50 dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800/50">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2">Segment</p>
                                <p class="text-xs font-black text-slate-700 dark:text-white">{{ $lifetimeValue > 10000 ? 'VIP Tier' : 'Standard' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-900 dark:bg-black p-6 flex items-center justify-between group">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest group-hover:text-primary transition-colors duration-500">System Trace Active</span>
                    <span class="material-symbols-outlined text-primary text-xl animate-pulse">monitoring</span>
                </div>
            </div>

            {{-- SYSTEM DATA CARD --}}
            <div class="bg-white dark:bg-slate-950 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 p-8 md:p-10 soft-shadow">
               <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">System Fingerprint</p>
               <div class="space-y-6">
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Execution Engine</span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200">{{ $supportRequest->getFlowTypeLabel() }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Access Privilege</span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200">{{ ucfirst($supportRequest->purchase_status) }}</span>
                    </div>
                    @if($supportRequest->course_purchased_at)
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Enrolled Temporal</span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($supportRequest->course_purchased_at)->format('j M Y • H:i') }}</span>
                    </div>
                    @endif
               </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // STATUS TRANSITION LOGIC
    (function() {
        const select = document.getElementById('statusSelect');
        const rejectDiv = document.getElementById('rejectionReasonDiv');
        if (!select || !rejectDiv) return;

        select.addEventListener('change', function() {
            if (this.value === 'rejected') {
                rejectDiv.classList.remove('hidden');
            } else {
                rejectDiv.classList.add('hidden');
            }
        });
    })();

    // EMI RESTRUCTURE LOGIC (PRESERVED)
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

                html += `<div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900 p-6 rounded-3xl border-2 border-slate-100 dark:border-slate-800">
                    <div class="size-10 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center text-[10px] font-black text-primary shadow-sm">EMI ${i+1}</div>
                    <div class="flex-[2]"><input type="number" class="w-full bg-transparent border-none text-slate-800 dark:text-white font-black p-0 focus:ring-0 text-xl restructure-amt" data-idx="${i}" value="${amt}" step="1" min="0" ${mode !== 'custom' ? 'readonly' : ''}><span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Amount (₹)</span></div>
                    <div class="flex-[2]"><input type="date" class="w-full bg-transparent border-none text-slate-800 dark:text-white font-bold p-0 focus:ring-0 text-sm restructure-date" data-idx="${i}" value="${dateStr}"><span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Due Date</span></div>
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
});
</script>
@endsection
