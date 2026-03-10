<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <style>
            .main-sidebar {
                background: #fff !important;
                box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
                width: 260px !important;
                border-right: 1px solid #f1f5f9;
            }
            #sidebar-wrapper {
                height: 100vh;
                overflow-y: auto;
            }
            .sidebar-brand {
                padding: 30px 25px !important;
                height: auto !important;
                line-height: normal !important;
                display: flex !important;
                align-items: center !important;
                margin-bottom: 20px !important;
            }
            .sidebar-logo-icon {
                background: #3bb24e;
                width: 38px;
                height: 38px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 1.2rem;
                margin-right: 12px;
                box-shadow: 0 4px 10px rgba(59, 178, 78, 0.2);
            }
            .sidebar-brand a {
                text-decoration: none !important;
                display: flex !important;
                align-items: center !important;
                color: #1e293b !important;
                font-weight: 800 !important;
                font-size: 1.4rem !important;
                letter-spacing: -0.5px !important;
            }
            
            .sidebar-menu {
                padding: 0 15px !important;
            }
            .sidebar-menu .menu-header {
                padding: 25px 15px 10px !important;
                color: #94a3b8 !important;
                font-size: 11px !important;
                font-weight: 800 !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
                height: auto !important;
                line-height: normal !important;
            }
            .sidebar-menu li {
                margin-bottom: 4px !important;
            }
            .sidebar-menu li a {
                display: flex !important;
                align-items: center !important;
                padding: 10px 18px !important;
                color: #334155 !important;
                font-size: 14px !important;
                font-weight: 800 !important;
                border-radius: 14px !important;
                transition: all 0.2s ease !important;
                text-decoration: none !important;
                border: 2px solid transparent !important;
                background: transparent !important;
                height: auto !important;
            }
            .sidebar-menu li a:hover:not(.active) {
                background: #f8fafc !important;
                color: #0f172a !important;
            }
            .sidebar-menu li a i, .sidebar-menu li a .material-symbols-outlined {
                font-size: 22px !important;
                margin-right: 8px !important; /* Reduced from 12px for extra tight feel as requested */
                width: 24px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #64748b !important;
                height: 24px !important;
            }
            .sidebar-menu li.active a {
                background: #F0FDF4 !important; /* light green #f0fdf4 */
                color: #16a34a !important; /* green text #16a34a */
                font-weight: 800 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .sidebar-menu li.active a i, .sidebar-menu li.active a .material-symbols-outlined {
                color: #16a34a !important;
            }
            
            /* Tighten up the gap as requested - global override */
            .mr-3 {
                margin-right: 8px !important;
            }
            
            /* Hide dropdown arrows as per image which looks simplified */
            .sidebar-menu li a.has-dropdown:after {
                display: none !important;
            }
            .sidebar-menu li a.has-dropdown i.chevron {
                display: none !important;
            }
            
            /* Sm sidebar adjustments */
            .sidebar-mini .main-sidebar {
                width: 80px !important;
            }
            .sidebar-mini .sidebar-brand {
                padding: 20px 0 !important;
                justify-content: center !important;
            }
            .sidebar-mini .sidebar-logo-icon {
                margin-right: 0;
            }
        </style>

        <div class="sidebar-brand">
            <a href="/">
                <div class="sidebar-logo-icon">
                    <span class="material-symbols-outlined" style="font-size: 24px;">grid_view</span>
                </div>
                <span>
                    @if(!empty($generalSettings['site_name']))
                        {{ $generalSettings['site_name'] }}
                    @else
                        Asttrolok
                    @endif
                </span>
            </a>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">MAIN MENU</li>

            @can('admin_general_dashboard_show')
                <li class="{{ (request()->is(getAdminPanelUrl('/'))) ? 'active' : '' }}">
                    <a href="/admin" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                </li>
            @endcan

            @can('admin_users')
                <li class="{{ (request()->is(getAdminPanelUrl('/students')) or request()->is(getAdminPanelUrl('/instructors')) or request()->is(getAdminPanelUrl('/staffs'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/students" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">group</span>
                        <span>Users</span>
                    </a>
                </li>
            @endcan

            @can('admin_webinars')
                <li class="{{ (request()->is(getAdminPanelUrl('/webinars*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/webinars?type=course" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">menu_book</span>
                        <span>Courses</span>
                    </a>
                </li>
            @endcan

            @can('admin_sales_list')
                <li class="{{ (request()->is(getAdminPanelUrl('/financial/sales*')) or request()->is(getAdminPanelUrl('/upe/sales*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/financial/sales" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">payments</span>
                        <span>Payments</span>
                    </a>
                </li>
            @endcan
            
            <li class="{{ (request()->is(getAdminPanelUrl('/financial/installments')) or request()->is(getAdminPanelUrl('/financial/installments/create')) or request()->is(getAdminPanelUrl('/financial/installments/*/edit'))) ? 'active' : '' }}">
                <a href="{{ getAdminPanelUrl() }}/financial/installments" class="nav-link d-flex align-items-center">
                    <span class="material-symbols-outlined mr-3">calendar_month</span>
                    <span>Installment Plan</span>
                </a>
            </li>

            <li class="{{ (request()->is(getAdminPanelUrl('/financial/installments/purchases'))) ? 'active' : '' }}">
                <a href="{{ getAdminPanelUrl() }}/financial/installments/purchases" class="nav-link d-flex align-items-center">
                    <span class="material-symbols-outlined mr-3">quick_reference_all</span>
                    <span>Installment Overdue</span>
                </a>
            </li>

            @can('admin_supports')
                <li class="{{ (request()->is(getAdminPanelUrl('/supports*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/supports" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">confirmation_number</span>
                        <span>Support Tickets</span>
                    </a>
                </li>
            @endcan

            @can('admin_documents')
                <li class="{{ (request()->is(getAdminPanelUrl('/financial/documents*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/financial/documents" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">account_balance_wallet</span>
                        <span>Wallet</span>
                    </a>
                </li>
            @endcan

            @can('admin_discount_codes')
                <li class="{{ (request()->is(getAdminPanelUrl('/financial/discounts*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/financial/discounts" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">sell</span>
                        <span>Coupons</span>
                    </a>
                </li>
            @endcan

            <li class="menu-header">ADMINISTRATION</li>

            @can('admin_reports')
                <li class="{{ (request()->is(getAdminPanelUrl('/reports*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/reports/analytics" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">bar_chart</span>
                        <span>Reports</span>
                    </a>
                </li>
            @endcan

            @can('admin_roles')
                <li class="{{ (request()->is(getAdminPanelUrl('/roles*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/roles" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">admin_panel_settings</span>
                        <span>Roles & Permissions</span>
                    </a>
                </li>
            @endcan

            @can('admin_settings')
                <li class="{{ (request()->is(getAdminPanelUrl('/settings*'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl() }}/settings" class="nav-link d-flex align-items-center">
                        <span class="material-symbols-outlined mr-3">settings</span>
                        <span>Settings</span>
                    </a>
                </li>
            @endcan
        </ul>

        {{-- User profile card at the bottom --}}
        <div class="sidebar-footer" style="padding: 20px 25px; margin-top: auto; border-top: 1px solid #f1f5f9;">
            <a href="{{ getAdminPanelUrl() }}/users/{{ auth()->user()->id }}/edit" class="d-flex align-items-center bg-slate-50 p-3 rounded-xl no-underline group" style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 16px; transition: all 0.3s ease;">
                <div class="h-10 w-10 rounded-full bg-slate-200 mr-3 flex items-center justify-center font-bold text-slate-500 group-hover:ring-4 group-hover:ring-primary/10 transition-all" style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #64748b;">
                    @php
                        $user = auth()->user();
                        $nm = $user->full_name ?? 'User';
                        $parts = explode(' ', trim($nm));
                        echo strtoupper(substr($parts[0],0,1) . (count($parts)>1?substr($parts[count($parts)-1],0,1):''));
                    @endphp
                </div>
                <div class="overflow-hidden">
                    <p style="margin: 0; font-size: 13px; font-weight: 800; color: #1e293b; line-height: 1.2;" class="text-truncate group-hover:text-primary transition-colors">{{ $nm }}</p>
                    <p style="margin: 0; font-size: 11px; font-weight: 500; color: #94a3b8;" class="text-truncate">{{ $user->email }}</p>
                </div>
            </a>
        </div>
    </aside>
</div>
