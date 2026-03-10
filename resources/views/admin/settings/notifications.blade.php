@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16A34A",
                        "primary-light": "#F0FDF4",
                        "accent": "#eab308",
                        "background-light": "#f8fafc",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"], "body": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .settings-page { font-family: 'Inter', sans-serif; }
        .settings-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
        
        .nav-tab-btn {
            @apply px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2 flex items-center gap-2 whitespace-nowrap !important;
        }
        .nav-tab-btn.active {
            @apply border-primary text-primary bg-primary/5 !important;
        }
        .nav-tab-btn:not(.active) {
            @apply border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50 !important;
        }

        /* Inner Form Overrides for Premium Look */
        form label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1 !important; }
        form .form-control, .select2-container--default .select2-selection--single { 
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 !important;
            height: auto !important;
        }
        form .btn-primary { 
            @apply px-6 py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all border-none !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            @apply leading-[48px] px-4 text-sm font-bold text-slate-700 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            @apply h-[48px] right-2 !important;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="settings-page bg-slate-50 text-slate-900 p-4 md:p-8 space-y-6">

    <div class="max-w-[1400px] mx-auto space-y-6">
        
        {{-- Breadcrumbs & Title --}}
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">
                    <a href="{{ getAdminPanelUrl() }}" class="hover:text-primary no-underline">Dashboard</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <a href="{{ getAdminPanelUrl('/settings') }}" class="hover:text-primary no-underline">Settings</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-slate-600">Notifications</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Notification Settings</h1>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[600px]">
            
            {{-- Horizontal Tabs --}}
            <div class="border-b border-slate-100 overflow-x-auto no-scrollbar bg-white sticky top-0 z-10">
                <div class="flex px-4">
                    @foreach(\App\Models\NotificationTemplate::$notificationTemplateAssignSetting as $section => $v)
                        <a class="nav-tab-btn {{ $loop->iteration == 1 ? ' active' : '' }}" id="{{ $section }}-tab" data-toggle="tab" href="#{{ $section }}" role="tab">
                            {{ trans('admin/main.notification_'.$section) }}
                        </a>
                    @endforeach
                </div>
            </div>

            @php
                $itemValue = (!empty($settings) and !empty($settings['notifications'])) ? $settings['notifications']->value : '';
                if (!empty($itemValue) and !is_array($itemValue)) {
                    $itemValue = json_decode($itemValue, true);
                }
            @endphp

            <div class="p-6 md:p-10">
                <div class="tab-content" id="myTabContent2">
                    @foreach(\App\Models\NotificationTemplate::$notificationTemplateAssignSetting as $tab => $items)
                        <div class="tab-pane fade {{ $loop->iteration == 1 ? ' show active' : '' }}" id="{{ $tab }}" role="tabpanel">
                            <div class="max-w-2xl">
                                <form action="{{ getAdminPanelUrl() }}/settings/notifications/store" method="post" class="space-y-8">
                                    {{ csrf_field() }}

                                    <div class="grid grid-cols-1 gap-6">
                                        @foreach($items as $item)
                                            <div class="space-y-2 group">
                                                <label>{{ trans('admin/main.notification_'.$item) }}</label>
                                                <select name="value[{{ $item }}]" class="form-control select2">
                                                    <option value="" selected disabled>Select Template</option>
                                                    @foreach($notificationTemplates as $template)
                                                        <option value="{{ $template->id }}" @if(!empty($itemValue) and !empty($itemValue[$item]) and $itemValue[$item] == $template->id) selected @endif>{{ $template->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="pt-4">
                                        <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            if (jQuery().select2) {
                $('.select2').select2({
                    width: '100%',
                    placeholder: 'Select Template',
                    allowClear: true
                });
            }
        });
    </script>
@endpush

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            if (jQuery().select2) {
                $('.select2').select2({
                    width: '100%',
                    placeholder: 'Select Template',
                    allowClear: true
                });
            }
        });
    </script>
@endpush

