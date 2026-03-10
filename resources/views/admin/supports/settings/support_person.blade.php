@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#12a608",
                        "background-light": "#f6f8f5",
                        "background-dark": "#112210",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .active-tab {
            border-bottom: 3px solid #12a608;
            color: #12a608;
        }
        /* Override some stisla defaults that might clash */
        .section .section-header {
            margin-bottom: 0 !important;
            border-bottom: 1px solid #12a6081a;
        }
        body {
            background-color: #f6f8f5 !important;
        }
    </style>
@endpush

@section('content')
    <div class="font-display text-slate-900">
        <!-- Content Area -->
        <div class="max-w-4xl mx-auto py-8 px-4 md:px-0">
            <!-- Navigation Tabs -->
            <div class="flex gap-8 border-b border-primary/10 mb-8 overflow-x-auto whitespace-nowrap pb-1">
                <a href="{{ route('admin.support_settings') }}" class="active-tab pb-4 text-sm font-bold transition-colors">Support Person</a>
                <a href="{{ route('admin.support_settings.notifications') }}" class="pb-4 text-sm font-bold text-slate-500 hover:text-primary transition-colors">Notifications</a>
                <a href="{{ route('admin.support_settings.email') }}" class="pb-4 text-sm font-bold text-slate-500 hover:text-primary transition-colors">Email Configuration</a>
                <a href="{{ route('admin.support_settings.sms') }}" class="pb-4 text-sm font-bold text-slate-500 hover:text-primary transition-colors">SMS Alerts</a>
            </div>

            <!-- Profile Management Section -->
            <form action="{{ route('admin.support_settings.store') }}" method="post" class="space-y-8">
                @csrf
                <div class="bg-white p-6 rounded-xl border border-primary/10 shadow-sm">
                    <div class="flex items-center gap-6 mb-8 flex-wrap md:flex-nowrap">
                        <div class="relative group">
                            <div class="size-24 rounded-full bg-cover bg-center ring-4 ring-primary/20" id="avatarPreview" style="background-image: url('{{ $settings['support_avatar'] ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuBQ-ySc9GFiKtQseiQeG3aPX_wUX-yXNtMRru5kM6D7tpT5nEaQbkFt5rK5eeaaAGTY6o7X0MMYKe7oHpHqq5J_f015LBdtwhNwj7pJf_9bPlhnXWbdTf824r6VNB8ys2cGZwW4ysUxEq6s--vRqHmcps1BP3pPE-z_dy2O349MkB-uPWlfg6jgYnc0lsHJBpPaSwIgHUcIHdU2-_reu6vGZ6d0DlSVThPO7oI5MTjizTEQCXHbIxgKY-lwHNnR6BnmwNex3nogDy3W' }}')"></div>
                            <button type="button" class="absolute bottom-0 right-0 size-8 bg-primary text-white rounded-full border-2 border-white flex items-center justify-center shadow-lg hover:scale-105 transition-transform admin-file-manager" data-input="support_avatar" data-preview="avatarPreview">
                                <span class="material-symbols-outlined text-sm">photo_camera</span>
                            </button>
                            <input type="hidden" name="value[support_avatar]" id="support_avatar" value="{{ $settings['support_avatar'] ?? '' }}">
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Your Avatar</h3>
                            <p class="text-sm text-slate-500 mb-3">JPG, GIF or PNG. Max size 2MB</p>
                            <div class="flex gap-2">
                                <button type="button" class="px-4 py-2 text-xs font-bold bg-primary/10 text-primary rounded-lg hover:bg-primary/20 admin-file-manager" data-input="support_avatar" data-preview="avatarPreview">Change Avatar</button>
                                <button type="button" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-red-500" onclick="document.getElementById('support_avatar').value=''; document.getElementById('avatarPreview').style.backgroundImage='none';">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Full Name</label>
                            <input class="w-full bg-slate-50 border border-primary/10 rounded-lg focus:ring-primary focus:border-primary px-4 py-3 text-sm" name="value[support_name]" type="text" value="{{ $settings['support_name'] ?? 'Alex Johnson' }}"/>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Email Address</label>
                            <input class="w-full bg-slate-50 border border-primary/10 rounded-lg focus:ring-primary focus:border-primary px-4 py-3 text-sm" name="value[support_email]" type="email" value="{{ $settings['support_email'] ?? 'alex.j@supporthub.com' }}"/>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Signature / Bio</label>
                            <textarea class="w-full bg-slate-50 border border-primary/10 rounded-lg focus:ring-primary focus:border-primary px-4 py-3 text-sm" name="value[support_signature]" rows="3">{{ $settings['support_signature'] ?? "Warm regards,\nAsttrolok Support Team" }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Notification Toggles Section -->
                <div class="bg-white p-6 rounded-xl border border-primary/10 shadow-sm">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined text-primary">notifications_active</span>
                        <h3 class="text-lg font-bold">In-App Quick Alerts</h3>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="text-sm font-bold">Ticket Assignments</p>
                                <p class="text-xs text-slate-500">Receive an alert when a new ticket is assigned to you.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="value[notify_ticket_assignment]" class="sr-only peer" {{ ($settings['notify_ticket_assignment'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-primary/5">
                            <div>
                                <p class="text-sm font-bold">Agent Mentions</p>
                                <p class="text-xs text-slate-500">Get notified when a colleague mentions you in ticket notes.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="value[notify_agent_mention]" class="sr-only peer" {{ ($settings['notify_agent_mention'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Platform Restrictions Alert -->
                <div class="bg-primary/5 border border-primary/20 p-4 rounded-xl flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary">info</span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Role Restriction</p>
                        <p class="text-xs text-slate-600 mt-1">Some configuration options are restricted to Administrative roles. Please contact your system administrator for global platform changes.</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 pb-12">
                    <button type="reset" class="px-6 py-2 rounded-lg border border-primary/20 text-sm font-bold text-slate-500 hover:bg-slate-50">Cancel Changes</button>
                    <button type="submit" class="px-8 py-2 rounded-lg bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:scale-[1.02] transition-transform">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $(document).ready(function () {
            $('.admin-file-manager').filemanager('image');
        });
    </script>
@endpush
