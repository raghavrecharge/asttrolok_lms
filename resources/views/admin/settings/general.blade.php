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
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        /* Hide old admin layout elements */
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { 
            display: none !important; 
        }
        
        /* Page Container - Force override */
        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
        }
        
        .page-container {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
        }
        
        /* Force remove any white backgrounds */
        .settings-page, .bg-white, [class*="bg-white"] {
            background: transparent !important;
        }
        
        /* Remove rounded containers and shadows */
        .rounded-3xl, .rounded-2xl, .rounded-xl {
            border-radius: 0 !important;
        }
        
        .shadow-sm, .shadow, .shadow-md, .shadow-lg, .shadow-xl {
            box-shadow: none !important;
        }
        
        .border-slate-200, .border, [class*="border-"] {
            border: none !important;
        }
        
        /* Tab Navigation - Horizontal Design */
        .tab-nav {
            display: flex;
            align-items: center;
            gap: 32px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 12px;
            margin-bottom: 24px;
            background: #FFFFFF;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .tab-nav {
                gap: 24px;
                padding-bottom: 8px;
            }
        }
        
        .tab-button {
            font-size: 16px;
            font-weight: 500;
            color: #64748B;
            cursor: pointer;
            padding-bottom: 8px;
            position: relative;
            transition: color 0.2s ease;
            background: none;
            border: none;
            white-space: nowrap;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-button:hover {
            color: #1F2937;
        }
        
        .tab-button.active {
            color: #16A34A;
            font-weight: 600;
            border-bottom: 3px solid #16A34A;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block !important;
            padding: 0;
            box-shadow: none;
            max-width: 100%;
        }
        
        /* Force all content in active tabs to be visible */
        .tab-content.active * {
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Handle Bootstrap tab-pane within our custom tabs */
        .tab-content.active .tab-pane {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: static !important;
        }
        
        /* Tab Container */
        .tab-container {
            background: #ffffff;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            max-width: 100%;
        }
        
        /* Card Component */
        .card {
            background: #ffffff;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Inner Form Overrides for Premium Look */
        form .form-label, form label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1 !important; }
        form .form-control { 
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 !important;
            height: auto !important;
        }
        form .btn-primary { 
            @apply px-6 py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all border-none !important;
        }
        
        .custom-switch-indicator { @apply bg-slate-200 border-none !important; }
        .custom-switch-input:checked ~ .custom-switch-indicator { @apply bg-primary !important; }
        
        .select2-container--default .select2-selection--multiple, 
        .select2-container--default .select2-selection--single { 
            @apply bg-slate-50 border-slate-100 rounded-2xl !important; 
            min-height: 48px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            @apply leading-[48px] px-4 text-sm font-bold text-slate-700 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            @apply h-[48px] right-2 !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single {
            @apply border-primary ring-4 ring-primary/10 !important;
        }

        .input-group-text { @apply bg-slate-100 border-slate-100 rounded-l-2xl px-4 text-slate-500 !important; }
        .input-group .form-control { @apply rounded-l-none !important; }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="page-wrapper">

    <div class="page-container">
        <!-- Breadcrumbs & Title -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">
                    <a href="{{ getAdminPanelUrl() }}" class="hover:text-primary no-underline">Dashboard</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <a href="{{ getAdminPanelUrl('/settings') }}" class="hover:text-primary no-underline">Settings</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-slate-600">General Configuration</span>
                </nav>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">General Settings</h1>
            </div>
        </div>

        <!-- Tabbed Interface -->
        <div class="tab-container">
            <!-- Tab Navigation -->
            <div class="tab-nav">
                <button type="button" class="tab-button {{ empty($social) ? 'active' : '' }}" onclick="switchTab('basic')" data-tab="basic">
                    <span class="material-symbols-outlined">tune</span>
                    Basic Information
                </button>
                <button type="button" class="tab-button {{ !empty($social) ? 'active' : '' }}" onclick="switchTab('socials')" data-tab="socials">
                    <span class="material-symbols-outlined">share</span>
                    Social Media
                </button>
                <button type="button" class="tab-button" onclick="switchTab('features')" data-tab="features">
                    <span class="material-symbols-outlined">extension</span>
                    Features
                </button>
                <button type="button" class="tab-button" onclick="switchTab('reminders')" data-tab="reminders">
                    <span class="material-symbols-outlined">notifications_active</span>
                    Reminders
                </button>
                <button type="button" class="tab-button" onclick="switchTab('security')" data-tab="security">
                    <span class="material-symbols-outlined">security</span>
                    Security
                </button>
                <button type="button" class="tab-button" onclick="switchTab('general_options')" data-tab="general_options">
                    <span class="material-symbols-outlined">manufacturing</span>
                    Options
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content {{ empty($social) ? 'active' : '' }}" id="basic-tab" data-tab-content="basic">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">tune</span>
                    Basic Configuration
                </h2>
                @include('admin.settings.general.basic',['itemValue' => (!empty($settings) and !empty($settings['general'])) ? $settings['general']->value : ''])
            </div>

            <div class="tab-content {{ !empty($social) ? 'active' : '' }}" id="socials-tab" data-tab-content="socials">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">share</span>
                    Social Media Settings
                </h2>
                @include('admin.settings.general.socials',['itemValue' => (!empty($settings) and !empty($settings['socials'])) ? $settings['socials']->value : ''])
            </div>

            <div class="tab-content" id="features-tab" data-tab-content="features">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">extension</span>
                    Features Configuration
                </h2>
                @include('admin.settings.general.features',['itemValue' => (!empty($settings) and !empty($settings['features'])) ? $settings['features']->value : ''])
            </div>

            <div class="tab-content" id="reminders-tab" data-tab-content="reminders">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">notifications_active</span>
                    Reminders Settings
                </h2>
                @include('admin.settings.general.reminders',['itemValue' => (!empty($settings) and !empty($settings['reminders'])) ? $settings['reminders']->value : ''])
            </div>

            <div class="tab-content" id="security-tab" data-tab-content="security">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">security</span>
                    Security Configuration
                </h2>
                @include('admin.settings.general.security',['itemValue' => (!empty($settings) and !empty($settings['security'])) ? $settings['security']->value : ''])
            </div>

            <div class="tab-content" id="general_options-tab" data-tab-content="general_options">
                <h2 class="card-title">
                    <span class="material-symbols-outlined">manufacturing</span>
                    General Options
                </h2>
                @include('admin.settings.general.options',['itemValue' => (!empty($settings) and !empty($settings['general_options'])) ? $settings['general_options']->value : ''])
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/js/admin/settings/general.min.js"></script>
    
    <script>
        // Tab Switching Function
        function switchTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) {
                selectedTab.classList.add('active');
                
                // Force all child elements to be visible
                const allChildren = selectedTab.querySelectorAll('*');
                allChildren.forEach(child => {
                    child.style.display = '';
                    child.style.visibility = 'visible';
                    child.style.opacity = '1';
                });
                
                // Specifically handle Bootstrap tab-pane elements
                const tabPanes = selectedTab.querySelectorAll('.tab-pane');
                tabPanes.forEach(pane => {
                    pane.style.display = 'block !important';
                    pane.style.visibility = 'visible !important';
                    pane.style.opacity = '1 !important';
                    pane.classList.add('show', 'active');
                });
            }
            
            // Add active class to clicked button
            const clickedButton = document.querySelector(`[data-tab="${tabName}"]`);
            if (clickedButton) {
                clickedButton.classList.add('active');
            }
            
            // Store active tab in localStorage for persistence
            localStorage.setItem('activeGeneralSettingsTab', tabName);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Restore active tab from localStorage if available
            const savedTab = localStorage.getItem('activeGeneralSettingsTab');
            
            // Determine which tab should be active
            let activeTab = 'basic'; // default
            
            if (savedTab) {
                activeTab = savedTab;
            } else if ('{{ !empty($social) ? "socials" : "basic" }}') {
                activeTab = '{{ !empty($social) ? "socials" : "basic" }}';
            }
            
            // Switch to the appropriate tab
            switchTab(activeTab);
        });
    </script>
@endpush
