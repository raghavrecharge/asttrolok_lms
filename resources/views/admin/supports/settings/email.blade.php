@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
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
                        "display": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #32A128;
        }
        .toggle-checkbox:checked + .toggle-label .toggle-dot {
            transform: translateX(100%);
        }
        .active-tab {
            border-bottom: 3px solid #32A128;
            color: #32A128;
        }
        body {
            background-color: #F7F9FC !important;
        }
    </style>
@endpush

@section('content')
    <div class="font-display text-slate-900 antialiased">
        <main class="max-w-4xl mx-auto py-8 px-4 md:px-0">
            <!-- Page Title -->
            <div class="mb-8">
                <h1 class="text-slate-900 text-3xl font-extrabold leading-tight tracking-tight">Email Configuration</h1>
                <p class="text-slate-500 mt-1">Manage automated email triggers for support communication.</p>
            </div>

            <!-- Primary Tabs -->
            <div class="border-b border-slate-200 mb-6">
                <div class="flex gap-8 overflow-x-auto whitespace-nowrap pb-1">
                    <a href="{{ route('admin.support_settings') }}" class="pb-4 text-slate-500 font-medium hover:text-slate-700 transition-all border-b-2 border-transparent">Support Person</a>
                    <a href="{{ route('admin.support_settings.notifications') }}" class="pb-4 text-slate-500 font-medium hover:text-slate-700 transition-all border-b-2 border-transparent">Notifications</a>
                    <a href="{{ route('admin.support_settings.email') }}" class="pb-4 text-primary font-bold border-b-2 border-primary active-tab">Email Configuration</a>
                    <a href="{{ route('admin.support_settings.sms') }}" class="pb-4 text-slate-500 font-medium hover:text-slate-700 transition-all border-b-2 border-transparent">SMS Alerts</a>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Sub-tabs -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <div class="flex bg-slate-200/50 p-1 rounded-lg w-fit">
                        <a href="{{ route('admin.support_settings.notifications') }}" class="px-6 py-1.5 text-sm font-medium rounded-md text-slate-600 hover:bg-white/50 transition-all">In-App</a>
                        <button type="button" class="px-6 py-1.5 text-sm font-bold rounded-md bg-white text-slate-900 shadow-sm">Email</button>
                        <a href="{{ route('admin.support_settings.sms') }}" class="px-6 py-1.5 text-sm font-medium rounded-md text-slate-600 hover:bg-white/50 transition-all">SMS</a>
                    </div>
                </div>

                <!-- Settings List -->
                <form action="{{ route('admin.support_settings.store') }}" method="post">
                    @csrf
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-slate-900">Email Trigger Types</h3>
                            <p class="text-sm text-slate-500">Enable or disable automated emails for these support events.</p>
                        </div>
                        <div class="space-y-0 divide-y divide-slate-100">
                            @php
                                $triggers = [
                                    ['id' => 'support_new_ticket', 'title' => 'New Ticket Verification', 'desc' => 'Confirmation email sent to customer when they create a ticket.'],
                                    ['id' => 'support_agent_reply', 'title' => 'Agent Reply Notification', 'desc' => 'Notify customer when an agent posts a reply to their ticket.'],
                                    ['id' => 'support_ticket_closed', 'title' => 'Ticket Closure Alert', 'desc' => 'Send final summary when a ticket is marked as resolved.'],
                                ];
                            @endphp

                            @foreach($triggers as $t)
                            <div class="flex items-center justify-between py-5 first:pt-0">
                                <div class="flex-1 pr-8">
                                    <h4 class="text-sm font-semibold text-slate-900">{{ $t['title'] }}</h4>
                                    <p class="text-sm text-slate-500">{{ $t['desc'] }}</p>
                                </div>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="value[{{ $t['id'] }}]" id="{{ $t['id'] }}" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-200 right-6 checked:right-0 transition-all duration-300" {{ ($settings[$t['id']] ?? true) ? 'checked' : '' }}/>
                                    <label class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-200 cursor-pointer transition-colors duration-300" for="{{ $t['id'] }}"></label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 pb-6">
                        <button type="reset" class="px-5 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-all">Discard Changes</button>
                        <button type="submit" class="px-8 py-2 text-sm font-bold text-white bg-primary hover:bg-opacity-90 rounded-lg shadow-md transition-all">Save Preferences</button>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 p-6 bg-primary/5 rounded-xl border border-primary/20 flex items-start gap-4 mb-12">
                <span class="material-symbols-outlined text-primary">info</span>
                <div>
                    <h5 class="text-sm font-bold text-primary">Looking for SMTP Configuration?</h5>
                    <p class="text-sm text-slate-600 mt-1">SMTP settings are managed globally in the <a href="/admin/settings/general" class="underline font-bold">System Settings</a>. These toggles control business logic only.</p>
                </div>
            </div>
        </main>
    </div>
@endsection
