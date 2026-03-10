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
        .rp-page { font-family: 'Inter', sans-serif; }
        .rp-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
        .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: #e2e8f0; border-radius: 9999px; transition: 0.3s; }
        .toggle-slider:before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 9999px; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .toggle-switch input:checked + .toggle-slider { background-color: #32A128; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
        .role-item { cursor: pointer; transition: all 0.2s; }
        .role-item.active { background: linear-gradient(135deg, #32A128, #22c55e); color: white; }
        .role-item.active .role-desc { color: rgba(255,255,255,0.8); }
        .role-item.active .role-icon-bg { background: rgba(255,255,255,0.2); }
        .role-item.active .role-check { display: flex; }
        .role-check { display: none; }
    </style>
@endpush

@section('content')
<div class="rp-page bg-background-light text-slate-900 p-4 md:p-8 space-y-6 h-full">

    {{-- Header --}}
    <header>
        <h1 class="text-2xl font-black tracking-tight text-slate-800">Security Settings</h1>
        <p class="text-sm text-slate-400 mt-1">Easily configure what each team member can see and do on the platform.</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Roles List --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Available Roles</h3>
                @can('admin_roles_create')
                <a href="{{ getAdminPanelUrl('/roles/create') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 no-underline">
                    <span class="material-symbols-outlined text-sm">add</span> Add New
                </a>
                @endcan
            </div>

            <div class="space-y-2" id="roleList">
                @foreach($roles as $index => $role)
                <div class="role-item px-4 py-4 rounded-xl flex items-center gap-3 {{ $index === 0 ? 'active' : 'hover:bg-slate-50' }}"
                     data-role-id="{{ $role->id }}" onclick="selectRole(this, {{ $role->id }}, '{{ $role->caption }}')">
                    <div class="role-icon-bg size-10 rounded-xl {{ $index === 0 ? '' : 'bg-slate-100' }} flex items-center justify-center shrink-0">
                        @if($role->is_admin)
                            <span class="material-symbols-outlined {{ $index === 0 ? 'text-white' : 'text-primary' }}" style="font-variation-settings: 'FILL' 1">shield</span>
                        @else
                            <span class="material-symbols-outlined {{ $index === 0 ? 'text-white' : 'text-slate-400' }}">person</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-black">{{ $role->caption }}</p>
                        <p class="role-desc text-[10px] {{ $index === 0 ? '' : 'text-slate-400' }}">
                            @if($role->is_admin) Full management capabilities @else Day-to-day operations focus @endif
                        </p>
                    </div>
                    <div class="role-check size-6 rounded-full bg-white/20 items-center justify-center shrink-0 {{ $index === 0 ? '' : '' }}">
                        <span class="material-symbols-outlined text-white text-sm" style="font-variation-settings: 'FILL' 1">check_circle</span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Quick Guide --}}
            <div class="mt-6 p-4 bg-primary/5 rounded-xl border border-primary/10">
                <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-2 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1">info</span> Quick Guide
                </p>
                <p class="text-xs text-slate-600 leading-relaxed">
                    <strong>Access:</strong> Allows the user to view the module content.<br>
                    <strong>Manage:</strong> Allows the user to create, edit, or delete items.
                </p>
            </div>
        </div>

        {{-- RIGHT: Permissions Panel --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-800">Configure Permissions</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Editing capabilities for the <strong class="text-primary" id="activeRoleName">{{ $roles->first()->caption ?? 'Role' }}</strong> role</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Auto-save Changes</span>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <form id="permissionsForm" method="POST" action="{{ getAdminPanelUrl('/roles/') }}{{ $roles->first()->id ?? '' }}/update" class="p-6 space-y-2">
                @csrf
                <input type="hidden" name="caption" value="{{ $roles->first()->caption ?? '' }}">
                <input type="hidden" name="name" value="{{ $roles->first()->name ?? '' }}">

                @php
                    $permissionModules = [
                        ['icon' => 'dashboard', 'title' => 'Main Dashboard', 'desc' => 'Platform overview, statistics, and real-time performance metrics.', 'access_key' => 'admin_general_dashboard', 'manage_key' => 'admin_general_dashboard_manage'],
                        ['icon' => 'group', 'title' => 'Users Management', 'desc' => 'Manage students, teachers, and other administrative staff profiles.', 'access_key' => 'admin_users', 'manage_key' => 'admin_users_edit'],
                        ['icon' => 'menu_book', 'title' => 'Courses & Content', 'desc' => 'Upload curriculum, edit video lessons, and manage course pricing.', 'access_key' => 'admin_webinars', 'manage_key' => 'admin_webinars_edit'],
                        ['icon' => 'payments', 'title' => 'Payments & Refunds', 'desc' => 'View transaction logs, process refunds, and generate tax invoices.', 'access_key' => 'admin_sales_list', 'manage_key' => 'admin_sales_refund'],
                        ['icon' => 'support_agent', 'title' => 'Support Tickets', 'desc' => 'Respond to student inquiries and resolve technical issues.', 'access_key' => 'admin_supports', 'manage_key' => 'admin_supports_reply'],
                        ['icon' => 'loyalty', 'title' => 'Marketing Coupons', 'desc' => 'Create, manage, and expire promotional discount codes.', 'access_key' => 'admin_discount_codes', 'manage_key' => 'admin_discount_codes_edit'],
                    ];
                @endphp

                @foreach($permissionModules as $mod)
                <div class="p-5 rounded-xl hover:bg-slate-50/50 transition-colors border border-transparent hover:border-slate-100">
                    <div class="flex items-start gap-4">
                        <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-slate-500 text-xl">{{ $mod['icon'] }}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-black text-slate-800">{{ $mod['title'] }}</h4>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $mod['desc'] }}</p>
                        </div>
                        <div class="flex items-center gap-8">
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Allow Access</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="permissions[]" value="{{ $mod['access_key'] }}" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Allow Management</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="permissions[]" value="{{ $mod['manage_key'] }}">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </form>

            {{-- Footer Actions --}}
            <div class="p-6 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[10px] text-slate-400">* Management permissions automatically include access permissions.</p>
                <div class="flex items-center gap-3">
                    <button class="flex items-center gap-1.5 px-4 py-2 text-slate-500 text-xs font-bold hover:bg-slate-50 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-sm">settings_backup_restore</span> Reset to Default
                    </button>
                    <button type="submit" form="permissionsForm" class="flex items-center gap-2 px-6 py-3 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-lg">shield</span>
                        Update <span id="updateBtnLabel">{{ $roles->first()->caption ?? 'Role' }}</span> Permissions
                    </button>
                </div>
            </div>

            {{-- Security Note --}}
            <div class="mx-6 mb-6 p-4 bg-amber-50 rounded-xl border border-amber-100 flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-500 text-lg shrink-0 mt-0.5" style="font-variation-settings: 'FILL' 1">warning</span>
                <div>
                    <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Security Note</p>
                    <p class="text-xs text-amber-600 mt-1">Changes made to role permissions will affect all users currently assigned to the "<strong id="securityNoteRole">{{ $roles->first()->caption ?? 'Role' }}</strong>" role instantly. Ensure you have reviewed all critical access points.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectRole(el, roleId, roleName) {
    document.querySelectorAll('.role-item').forEach(item => {
        item.classList.remove('active');
        item.querySelector('.role-check').style.display = 'none';
    });
    el.classList.add('active');
    el.querySelector('.role-check').style.display = 'flex';
    
    document.getElementById('activeRoleName').textContent = roleName;
    document.getElementById('updateBtnLabel').textContent = roleName;
    document.getElementById('securityNoteRole').textContent = roleName;
    
    // Update form action
    const form = document.getElementById('permissionsForm');
    form.action = '{{ getAdminPanelUrl("/roles/") }}' + roleId + '/update';
}
</script>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/roles.min.js"></script>
@endpush
