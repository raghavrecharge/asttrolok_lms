@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
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
            <div class="mb-8">
                <h1 class="text-3xl font-black tracking-tight mb-6">SMS Alerts Configuration</h1>
                <!-- Main Tabs -->
                <div class="flex border-b border-slate-200 gap-8 overflow-x-auto whitespace-nowrap pb-1">
                    <a href="{{ route('admin.support_settings') }}" class="pb-4 text-sm font-semibold text-slate-500 hover:text-primary transition-colors">Support Person</a>
                    <a href="{{ route('admin.support_settings.notifications') }}" class="pb-4 text-sm font-semibold text-slate-500 hover:text-primary transition-colors border-b-2 border-transparent">Notifications</a>
                    <a href="{{ route('admin.support_settings.email') }}" class="pb-4 text-sm font-semibold text-slate-500 hover:text-primary transition-colors">Email Configuration</a>
                    <a href="{{ route('admin.support_settings.sms') }}" class="pb-4 text-sm font-bold text-primary border-b-2 border-primary active-tab">SMS Alerts</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <!-- Sub-tabs (Notification Channels) -->
                <div class="flex bg-slate-100 p-1 rounded-xl mb-8 max-w-sm">
                    <a href="{{ route('admin.support_settings.notifications') }}" class="flex-1 py-2 px-4 text-center text-sm font-medium rounded-lg text-slate-500 hover:text-slate-700">In-App</a>
                    <a href="{{ route('admin.support_settings.email') }}" class="flex-1 py-2 px-4 text-center text-sm font-medium rounded-lg text-slate-500 hover:text-slate-700">Email</a>
                    <button type="button" class="flex-1 py-2 px-4 text-sm font-bold bg-white text-slate-900 shadow-sm rounded-lg">SMS</button>
                </div>

                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-2">SMS Notification Preferences</h2>
                    <p class="text-slate-500 text-sm">Control which urgent alerts are sent to verifying mobile numbers.</p>
                </div>

                <!-- Notification Settings List -->
                <form action="{{ route('admin.support_settings.store') }}" method="post">
                    @csrf
                    <div class="space-y-0 border-t border-slate-100">
                        @php
                            $smsItems = [
                                ['id' => 'sms_critical_alert', 'title' => 'Critical System Alerts', 'desc' => 'Urgent notifications regarding support queue health.'],
                                ['id' => 'sms_urgent_mention', 'title' => 'Urgent Support Mentions', 'desc' => 'When a supervisor mentions you in a priority 1 ticket.'],
                                ['id' => 'sms_p1_ticket', 'title' => 'New P1 Tickets', 'desc' => 'Alert when a Critical priority ticket enters the system.'],
                            ];
                        @endphp

                        @foreach($smsItems as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between py-6 border-b border-slate-100 gap-4">
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800">{{ $item['title'] }}</h4>
                                <p class="text-sm text-slate-500">{{ $item['desc'] }}</p>
                            </div>
                            <div class="flex items-center gap-6">
                                <select name="value[freq_{{ $item['id'] }}]" class="form-select rounded-lg border-slate-200 text-sm focus:ring-primary focus:border-primary">
                                    <option value="instant" {{ ($settings['freq_'.$item['id']] ?? 'instant') == 'instant' ? 'selected' : '' }}>Instant</option>
                                    <option value="daily" {{ ($settings['freq_'.$item['id']] ?? '') == 'daily' ? 'selected' : '' }}>Daily Digest</option>
                                </select>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="value[{{ $item['id'] }}]" class="sr-only peer" {{ ($settings[$item['id']] ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Actions Footer -->
                    <div class="mt-10 flex items-center justify-end gap-4 pb-6">
                        <button type="reset" class="px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Discard Changes</button>
                        <button type="submit" class="px-8 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-md transition-all">Save Preferences</button>
                    </div>
                </form>
            </div>

            <div class="mt-8 p-4 bg-primary/5 rounded-xl border border-primary/20 flex items-start gap-4 mb-12">
                <span class="material-symbols-outlined text-primary mt-1">info</span>
                <div>
                    <p class="text-sm font-semibold text-primary">Phone Verification Required</p>
                    <p class="text-xs text-primary/80 mt-1">Changes to SMS settings will only apply after agents have verified their mobile numbers in their profile.</p>
                </div>
            </div>
        </main>
    </div>
@endsection
