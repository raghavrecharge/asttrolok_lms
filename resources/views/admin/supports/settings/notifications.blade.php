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
                <a href="{{ route('admin.support_settings') }}" class="pb-4 text-sm font-bold text-slate-500 hover:text-primary transition-colors">Support Person</a>
                <a href="{{ route('admin.support_settings.notifications') }}" class="active-tab pb-4 text-sm font-bold transition-colors">Notifications</a>
                <a href="{{ route('admin.support_settings.email') }}" class="pb-4 text-sm font-bold text-slate-500 hover:text-primary transition-colors">Email Configuration</a>
                <a href="{{ route('admin.support_settings.sms') }}" class="pb-4 text-sm font-bold text-slate-500 hover:text-primary transition-colors">SMS Alerts</a>
            </div>

            <!-- Profile Management Section -->
            <section class="space-y-8">
                <!-- Notification Sub-tabs -->
                <div class="flex gap-4 mb-6">
                    <button class="px-4 py-2 text-sm font-bold bg-primary text-white rounded-lg">In-App</button>
                    <a href="{{ route('admin.support_settings.email') }}" class="px-4 py-2 text-sm font-bold text-slate-500 hover:bg-primary/5 hover:text-primary rounded-lg transition-colors">Email</a>
                    <a href="{{ route('admin.support_settings.sms') }}" class="px-4 py-2 text-sm font-bold text-slate-500 hover:bg-primary/5 hover:text-primary rounded-lg transition-colors">SMS</a>
                </div>

                <form action="{{ route('admin.support_settings.store') }}" method="post" class="space-y-4">
                    @csrf
                    <!-- Header Row -->
                    <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <div class="col-span-6">Notification Type</div>
                        <div class="col-span-3 text-center">Status</div>
                        <div class="col-span-3">Frequency</div>
                    </div>

                    <!-- Notification Rows -->
                    <div class="bg-white rounded-xl border border-primary/10 shadow-sm divide-y divide-primary/5">
                        @php
                            $notifs = [
                                ['id' => 'new_ticket', 'title' => 'New Ticket Assignment', 'desc' => 'When a new ticket is assigned specifically to you.'],
                                ['id' => 'status_update', 'title' => 'Ticket Status Update', 'desc' => 'When a ticket you are watching changes state.'],
                                ['id' => 'agent_mention', 'title' => 'Agent Mention', 'desc' => 'When someone tags you with @name in internal notes.'],
                                ['id' => 'customer_message', 'title' => 'New Customer Message', 'desc' => 'Inbound reply on an active ticket you are managing.'],
                            ];
                        @endphp

                        @foreach($notifs as $n)
                        <div class="grid grid-cols-12 gap-4 items-center p-6">
                            <div class="col-span-12 md:col-span-6">
                                <p class="text-sm font-bold">{{ $n['title'] }}</p>
                                <p class="text-xs text-slate-500">{{ $n['desc'] }}</p>
                            </div>
                            <div class="col-span-6 md:col-span-3 flex justify-center mt-2 md:mt-0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="value[notif_{{ $n['id'] }}]" class="sr-only peer" {{ ($settings['notif_'.$n['id']] ?? true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            <div class="col-span-6 md:col-span-3 mt-2 md:mt-0">
                                <select name="value[freq_{{ $n['id'] }}]" class="w-full bg-slate-50 border-none rounded-lg text-xs font-medium focus:ring-1 focus:ring-primary py-2">
                                    <option value="instant" {{ ($settings['freq_'.$n['id']] ?? 'instant') == 'instant' ? 'selected' : '' }}>Instant</option>
                                    <option value="hourly" {{ ($settings['freq_'.$n['id']] ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="daily" {{ ($settings['freq_'.$n['id']] ?? '') == 'daily' ? 'selected' : '' }}>Daily Digest</option>
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Global Preference Alert -->
                    <div class="bg-primary/5 border border-primary/20 p-4 rounded-xl flex items-start gap-4">
                        <span class="material-symbols-outlined text-primary">settings_suggest</span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Browser Notifications</p>
                            <p class="text-xs text-slate-600 mt-1">Ensure your browser permissions allow notifications from asttrolok.com to receive instant desktop alerts.</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-6 pb-12">
                        <button type="reset" class="px-6 py-2 rounded-lg border border-primary/20 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-colors">Discard Changes</button>
                        <button type="submit" class="px-8 py-2 rounded-lg bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:scale-[1.02] transition-transform">Save Preferences</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
