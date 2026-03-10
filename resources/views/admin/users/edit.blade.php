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
                        "primary": "#16A34A", // Vibrant Emerald Green
                        "primary-light": "#F0FDF4",
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
        .edit-page { font-family: 'Inter', sans-serif; }
        .edit-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
        
        .nav-tab-btn {
            @apply px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap;
        }
        .nav-tab-btn.active {
            @apply border-primary text-primary bg-primary/5;
        }
        .nav-tab-btn:not(.active) {
            @apply border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50;
        }

        .tab-content-wrapper {
            @apply animate-in fade-in slide-in-from-bottom-2 duration-300;
        }

        /* Support for existing tab layouts if they use .row etc */
        .tab-content-wrapper .row { @apply mt-0; }
        
        /* Custom Scrollbar for top tabs on mobile */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="edit-page bg-slate-50 min-h-full p-4 md:p-8">
    
    <div class="max-w-[1400px] mx-auto space-y-6">
        
        {{-- Breadcrumbs & Title Piece --}}
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">
                    <a href="{{ getAdminPanelUrl() }}" class="hover:text-primary no-underline">Dashboard</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <a href="{{ getAdminPanelUrl('/users/students') }}" class="hover:text-primary no-underline">Users</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-slate-600">Edit</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit User</h1>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400">User ID:</span>
                <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-black text-slate-700 shadow-sm">#{{ $user->id }}</span>
            </div>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            
            {{-- Horizontal Tabs --}}
            <div class="border-b border-slate-100 overflow-x-auto no-scrollbar bg-white sticky top-0 z-10">
                <div class="flex">
                    @php
                        $tabList = [
                            'general' => 'General',
                            'images' => 'Images',
                            'financial' => 'Financial',
                            'occupations' => 'Skills',
                            'badges' => 'Badges',
                            'purchased_courses' => 'Purchased Courses',
                            'purchased_bundles' => 'Purchased Bundles',
                            'purchased_products' => 'Purchased Products',
                            'topics' => 'Forum topics',
                            'meeting_settings' => 'Meeting Settings',
                            'become_instructor' => 'Become Instructor',
                            'registration_package' => 'Registration Package'
                        ];
                        $activeTab = 'general';
                    @endphp

                    @foreach($tabList as $key => $label)
                        <button type="button" 
                                onclick="switchTab('{{ $key }}')" 
                                id="tab-btn-{{ $key }}" 
                                class="nav-tab-btn {{ $key == $activeTab ? 'active' : '' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab Content Container --}}
            <div class="p-8 md:p-12">
                <div id="tab-container" class="tab-content-wrapper">
                    <div id="content-general" class="tab-pane">
                        @include('admin.users.editTabs.general')
                    </div>
                    <div id="content-images" class="tab-pane hidden">
                        @include('admin.users.editTabs.images')
                    </div>
                    <div id="content-financial" class="tab-pane hidden">
                        @include('admin.users.editTabs.financial')
                    </div>
                    <div id="content-occupations" class="tab-pane hidden">
                        @include('admin.users.editTabs.occupations')
                    </div>
                    <div id="content-badges" class="tab-pane hidden">
                        @include('admin.users.editTabs.badges')
                    </div>
                    <div id="content-purchased_courses" class="tab-pane hidden">
                        @include('admin.users.editTabs.purchased_courses')
                    </div>
                    <div id="content-purchased_bundles" class="tab-pane hidden">
                        @include('admin.users.editTabs.purchased_bundles')
                    </div>
                    <div id="content-purchased_products" class="tab-pane hidden">
                        @include('admin.users.editTabs.purchased_products')
                    </div>
                    <div id="content-topics" class="tab-pane hidden">
                        @include('admin.users.editTabs.topics')
                    </div>
                    <div id="content-meeting_settings" class="tab-pane hidden">
                        @include('admin.users.editTabs.meeting_settings')
                    </div>
                    <div id="content-become_instructor" class="tab-pane hidden">
                        @include('admin.users.editTabs.become_instructor')
                    </div>
                    <div id="content-registration_package" class="tab-pane hidden">
                        @include('admin.users.editTabs.registration_package')
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        function switchTab(tabKey) {
            // Update buttons
            document.querySelectorAll('.nav-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById('tab-btn-' + tabKey).classList.add('active');

            // Hide all content panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });

            // Show active content pane
            const activePane = document.getElementById('content-' + tabKey);
            if(activePane) {
                activePane.classList.remove('hidden');
                
                // Also update any internal tab-content refs if the sub-tabs use them
                const internalTabId = tabKey + '-tab';
                const internalTab = document.getElementById(internalTabId);
                if(internalTab) {
                    // Make sure all sibling tabs are hidden if they share a container
                    const parent = internalTab.parentElement;
                    if(parent) {
                        Array.from(parent.children).forEach(child => {
                            if(child.classList.contains('tab-content')) {
                                child.classList.add('hidden');
                            }
                        });
                        internalTab.classList.remove('hidden');
                    }
                }
            }
        }

        // Initialize Select2 if needed
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endpush
