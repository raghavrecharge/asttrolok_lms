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
                    "accent": "#eab308",
                    "background-light": "#f6f8f5",
                },
                fontFamily: {
                    "display": ["Inter", "sans-serif"]
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "2xl": "12px",
                    "full": "9999px"
                },
            },
        },
    }
</script>
<style>
    .td-page { font-family: 'Inter', sans-serif; }
    .td-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { display: none !important; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .timeline-line { position: absolute; left: 16px; top: 36px; bottom: 0; width: 2px; background: #e2e8f0; }
    .timeline-item:last-child .timeline-line { display: none; }
    .timeline-item { padding-left: 48px !important; }
    .timeline-item .tl-icon { position: absolute; left: 0; top: 0; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
</style>
@endpush

@section('content')
@php
    $firstMessage = $support->conversations->first();
    $allConversations = $support->conversations;
    $statusMap = [
        'open' => ['bg' => 'bg-emerald-500', 'text' => 'text-white', 'label' => 'OPEN'],
        'close' => ['bg' => 'bg-slate-400', 'text' => 'text-white', 'label' => 'CLOSED'],
        'replied' => ['bg' => 'bg-amber-500', 'text' => 'text-white', 'label' => 'PENDING'],
        'supporter_replied' => ['bg' => 'bg-blue-500', 'text' => 'text-white', 'label' => 'REPLIED'],
    ];
    $st = $statusMap[$support->status] ?? ['bg' => 'bg-emerald-500', 'text' => 'text-white', 'label' => strtoupper($support->status)];
@endphp

<div class="td-page bg-background-light text-slate-800 p-4 md:p-8 min-h-screen space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ getAdminPanelUrl() }}/supports" class="text-slate-400 hover:text-primary font-semibold no-underline transition-colors">Tickets</a>
        <span class="material-symbols-outlined text-slate-300 text-[16px]">chevron_right</span>
        <span class="text-slate-600 font-bold">Ticket Detail</span>
    </nav>

    {{-- Ticket Header --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-4">
            {{-- Avatar --}}
            <div class="size-14 rounded-full bg-slate-100 flex items-center justify-center text-lg font-black text-slate-500 shrink-0 overflow-hidden">
                @if(!empty($support->user))
                    @php
                        $uName = $support->user->full_name ?? 'U';
                        $parts = explode(' ', trim($uName));
                        $initials = strtoupper(substr($parts[0],0,1) . (count($parts)>1 ? substr(end($parts),0,1) : ''));
                    @endphp
                    <span>{{ $initials }}</span>
                @else
                    <span class="material-symbols-outlined text-2xl text-slate-400">person</span>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight m-0">#TK-{{ $support->id }}: {{ $support->title }}</h1>
                    <span class="px-2.5 py-1 rounded-md {{ $st['bg'] }} {{ $st['text'] }} text-[10px] font-black uppercase tracking-widest leading-none">{{ $st['label'] }}</span>
                </div>
                <div class="flex items-center gap-3 mt-2 text-sm text-slate-500 flex-wrap">
                    <span>Requested by <strong class="text-slate-700">{{ $support->user->full_name ?? 'Unknown' }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>Customer ID: <strong class="text-slate-600">AST-{{ $support->user->id ?? '000' }}</strong></span>
                </div>
                <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-400 flex-wrap">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                        Created: {{ dateTimeFormat($support->created_at, 'M d, Y') }}
                    </span>
                    @if(!empty($support->department))
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">label</span>
                            {{ $support->department->title ?? '' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/show" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all no-underline flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">share</span>
                Share
            </a>
            <a href="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/edit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-xs font-bold hover:bg-primary/90 transition-all no-underline flex items-center gap-2 shadow-md shadow-primary/20">
                <span class="material-symbols-outlined text-[16px]">edit</span>
                Edit Ticket
            </a>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Issue Description --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">edit_note</span>
                        <h3 class="text-base font-black text-slate-800 m-0">Issue Description</h3>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Internal Reference: #{{ str_pad($support->id, 4, '0', STR_PAD_LEFT) }}-A</span>
                </div>
                @if($firstMessage)
                    <p class="text-sm text-slate-600 leading-relaxed m-0">{!! nl2br(e($firstMessage->message)) !!}</p>

                    @if(!empty($firstMessage->attach))
                        <div class="mt-4 flex items-center gap-2">
                            <a href="https://storage.googleapis.com/astrolok{{ $firstMessage->attach }}" target="_blank" class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg no-underline text-primary hover:bg-primary/5 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">attach_file</span>
                                <span class="text-[11px] font-bold uppercase tracking-widest">{{ basename($firstMessage->attach) }}</span>
                            </a>
                        </div>
                    @endif
                @endif

                {{-- Transaction Info --}}
                @if(!empty($support->webinar))
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="flex items-center gap-8">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Course</span>
                            <span class="text-sm font-bold text-slate-800">{{ $support->webinar->title ?? 'N/A' }}</span>
                        </div>
                        @if(!empty($support->webinar->teacher))
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Instructor</span>
                            <span class="text-sm font-bold text-slate-800">{{ $support->webinar->teacher->full_name ?? 'N/A' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Action Buttons Row --}}
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/edit" class="size-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary/30 transition-all shadow-sm no-underline">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                </a>
                <a href="{{ getAdminPanelUrl() }}/supports" class="size-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary/30 transition-all shadow-sm no-underline">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                </a>
            </div>

            {{-- Admin Action Panel --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-5">
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">bolt</span>
                    <h3 class="text-base font-black text-slate-800 m-0">Admin Action Panel</h3>
                </div>

                <form action="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/conversation" method="post" class="space-y-5">
                    {{ csrf_field() }}

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-widest">Resolution Notes</label>
                        <textarea name="message" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary text-sm font-medium p-4 transition-all @error('message') border-rose-500 @enderror" placeholder="Explain the actions taken to resolve this ticket..." required>{!! old('message') !!}</textarea>
                        @error('message') <p class="text-rose-500 text-[11px] font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- File Attach --}}
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">Attach File (Optional)</label>
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1.5">
                            <button type="button" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[11px] font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all admin-file-manager shadow-sm" data-input="attach" data-preview="holder">Browse</button>
                            <input type="text" name="attach" id="attach" value="{{ old('image_cover') }}" class="flex-1 bg-transparent border-none focus:ring-0 text-xs font-medium text-slate-500 px-4" placeholder="No file selected..." readonly />
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                        <button type="submit" class="flex-1 px-6 py-3.5 bg-primary text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                            Send Reply
                        </button>
                        @if($support->status != 'close')
                            <a href="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/close" class="flex-1 px-6 py-3.5 bg-slate-800 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 transition-all flex items-center justify-center gap-2 no-underline shadow-lg shadow-slate-800/10">
                                <span class="material-symbols-outlined text-[18px]">lock</span>
                                Close Ticket
                            </a>
                        @endif
                        <a href="{{ getAdminPanelUrl() }}/supports" class="flex-1 px-6 py-3.5 bg-white border border-rose-200 text-rose-500 text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 no-underline">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                            Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">

            {{-- Audit Log --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500 text-[20px]">history</span>
                        <h3 class="text-base font-black text-slate-800 m-0">Audit Log</h3>
                    </div>
                    <span class="text-[10px] font-bold text-primary cursor-pointer hover:underline">View All</span>
                </div>

                <div class="space-y-0">
                    {{-- Ticket Created Event --}}
                    <div class="relative pb-6 timeline-item">
                        <div class="timeline-line"></div>
                        <div class="tl-icon bg-emerald-100">
                            <span class="material-symbols-outlined text-emerald-600 text-[16px]">add_circle</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 m-0">Ticket Created</h4>
                        <p class="text-[10px] font-bold text-primary uppercase tracking-widest m-0 mt-0.5">{{ dateTimeFormat($support->created_at, 'M d, Y') }} • {{ dateTimeFormat($support->created_at, 'H:i') }}</p>
                        <p class="text-xs text-slate-400 m-0 mt-1">Created by {{ $support->user->full_name ?? 'User' }}.</p>
                    </div>

                    {{-- Conversation Events --}}
                    @foreach($allConversations->skip(0) as $idx => $conv)
                        @php
                            $isSupporter = empty($conv->sender_id);
                            $iconBg = $isSupporter ? 'bg-blue-100' : 'bg-amber-100';
                            $iconColor = $isSupporter ? 'text-blue-600' : 'text-amber-600';
                            $iconName = $isSupporter ? 'support_agent' : 'chat';
                            $eventTitle = $isSupporter ? 'Support Reply' : 'User Reply';
                            $personName = $isSupporter ? ($conv->supporter->full_name ?? 'Support Team') : ($conv->sender->full_name ?? 'User');
                        @endphp
                        <div class="relative pb-6 timeline-item">
                            <div class="timeline-line"></div>
                            <div class="tl-icon {{ $iconBg }}">
                                <span class="material-symbols-outlined {{ $iconColor }} text-[16px]">{{ $iconName }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800 m-0">{{ $eventTitle }}</h4>
                            <p class="text-[10px] font-bold text-primary uppercase tracking-widest m-0 mt-0.5">{{ dateTimeFormat($conv->created_at, 'M d, Y') }} • {{ dateTimeFormat($conv->created_at, 'H:i') }}</p>
                            <p class="text-xs text-slate-400 m-0 mt-1">{{ Str::limit($conv->message, 60) }}</p>
                        </div>
                    @endforeach

                    {{-- Status indicator --}}
                    @if($support->status == 'close')
                        <div class="relative pb-2 timeline-item">
                            <div class="tl-icon bg-slate-100">
                                <span class="material-symbols-outlined text-slate-400 text-[16px]">lock</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800 m-0">Ticket Closed</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest m-0 mt-0.5">Status updated to 'Closed'.</p>
                        </div>
                    @else
                        <div class="relative pb-2 timeline-item">
                            <div class="tl-icon bg-slate-50 border-2 border-dashed border-slate-200">
                                <span class="material-symbols-outlined text-slate-300 text-[16px]">schedule</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-500 m-0">Awaiting Response</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest m-0 mt-0.5">Upcoming Action</p>
                            <p class="text-xs text-slate-400 m-0 mt-1">Status: '{{ ucfirst($support->status) }}'.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Statistics --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-5">
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">insights</span>
                    <h3 class="text-base font-black text-slate-800 m-0">Customer Statistics</h3>
                </div>

                @php
                    $ticketUser = $support->user;
                    $totalOrders = 0;
                    $memberSince = 'N/A';
                    if (!empty($ticketUser)) {
                        $totalOrders = \App\Models\Order::where('user_id', $ticketUser->id)->where('status', 'paid')->count();
                        $memberSince = $ticketUser->created_at ? date('M Y', $ticketUser->created_at) : 'N/A';
                    }
                @endphp

                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-slate-500 font-medium">Total Orders</span>
                        <span class="text-sm font-black text-slate-800">{{ $totalOrders }}</span>
                    </div>
                    <div class="border-t border-slate-100"></div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-slate-500 font-medium">Member Since</span>
                        <span class="text-sm font-bold text-slate-800">{{ $memberSince }}</span>
                    </div>
                    <div class="border-t border-slate-100"></div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-slate-500 font-medium">Customer ID</span>
                        <span class="text-sm font-bold text-primary">AST-{{ $ticketUser->id ?? '000' }}</span>
                    </div>
                    <div class="border-t border-slate-100"></div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-slate-500 font-medium">Role</span>
                        <span class="text-sm font-bold text-slate-800">{{ ucfirst($ticketUser->role_name ?? 'user') }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
