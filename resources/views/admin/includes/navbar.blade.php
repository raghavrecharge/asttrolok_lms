<button type="button" class="sidebar-close">
    <i class="fa fa-times"></i>
</button>

<div class="navbar-bg"></div>

<nav class="navbar navbar-expand-lg main-navbar">

    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">

    <li class="dropdown dropdown-list-toggle">
                <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg">
                    <i class="fa fa-info-circle"></i>
                </a>
                <div class="dropdown-menu dropdown-list dropdown-menu-right">
                    <div class="dropdown-list-icons mb-0" height="150px">
                            <a href="#" class="dropdown-item">
                                <div class="dropdown-item-icon bg-info text-white d-flex align-items-center justify-content-center">
                                    <i class="fa fa-info"></i>
                                </div>
                                <div class="dropdown-item-desc">
                                   Asttrolok
                                   <div class="time text-primary">All rights reserved for Asttrolok</div>
                                </div>
                            </a>
                    </div>
                </div>
            </li>

        {{-- Export Downloads Tray --}}
        <li class="dropdown dropdown-list-toggle" id="exportDownloadsTray" style="display: none;">
            <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg beep">
                <i class="fas fa-download"></i>
            </a>

            <div class="dropdown-menu dropdown-list dropdown-menu-right">
                <div class="dropdown-header">Active & Recent Exports</div>
                <div class="dropdown-list-content dropdown-list-icons" id="exportDownloadsList">
                    <!-- Export items will be injected here via JS -->
                </div>
            </div>
        </li>

        @can('admin_notifications_list')
            <li class="dropdown dropdown-list-toggle">
                <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg @if(!empty($unreadNotifications) and count($unreadNotifications)) beep @else disabled @endif">
                    <i class="far fa-bell"></i>
                </a>

                <div class="dropdown-menu dropdown-list dropdown-menu-right">
                    <div class="dropdown-header">{{ trans('admin/main.notifications') }}
                        <div class="float-right">
                            @can('admin_notifications_markAllRead')
                                <a href="{{ getAdminPanelUrl() }}/notifications/mark_all_read">{{ trans('admin/main.mark_all_read') }}</a>
                            @endcan
                        </div>
                    </div>

                    <div class="dropdown-list-content dropdown-list-icons">
                        @foreach($unreadNotifications as $unreadNotification)
                            <a href="{{ getAdminPanelUrl() }}/notifications" class="dropdown-item">
                                <div class="dropdown-item-icon bg-info text-white d-flex align-items-center justify-content-center">
                                    <i class="far fa-user"></i>
                                </div>
                                <div class="dropdown-item-desc">
                                    {{ $unreadNotification->title }}
                                    <div class="time text-primary">{{ dateTimeFormat($unreadNotification->created_at,'Y M j | H:i') }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="dropdown-footer text-center">
                        <a href="{{ getAdminPanelUrl() }}/notifications">{{ trans('admin/main.view_all') }} <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </li>
        @endcan

        <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="https://storage.googleapis.com/astrolok/webp/store/1/astrologer_mobile/Alok Sir.webp" class="rounded-circle mr-1" style="width: 30px; height: 30px; object-fit: cover;">
                <div class="d-sm-none d-lg-inline-block">{{ $authUser->full_name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">

                 <a href="/" class="dropdown-item has-icon">
                    <i class="fas fa-globe"></i> {{ trans('admin/main.show_website') }}
                </a>

                <a href="{{ getAdminPanelUrl() }}/users/{{ $authUser->id }}/edit" class="dropdown-item has-icon">
                    <i class="fas fa-cog"></i> {{ trans('admin/main.change_password') }}
                </a>

                <div class="dropdown-divider"></div>
                <a href="{{ getAdminPanelUrl() }}/logout" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> {{ trans('admin/main.logout') }}
                </a>
            </div>
        </li>
    </ul>
</nav>

@push('scripts_bottom')
<script>
    (function($) {
        "use strict";

        let exportPollingInterval = null;

        function updateExportTray() {
            $.get(adminPanelPrefix + '/get-export-statuses', function(response) {
                if (response.success && response.exports) {
                    const $tray = $('#exportDownloadsTray');
                    const $list = $('#exportDownloadsList');
                    let activeCount = 0;
                    let html = '';

                    if (response.exports.length > 0) {
                        $tray.show();
                        
                        response.exports.forEach(function(exportItem) {
                            let statusClass = 'bg-info';
                            let iconClass = 'fas fa-spinner fa-spin';
                            let progressHtml = '';

                            if (exportItem.status === 'processing') {
                                activeCount++;
                                progressHtml = `
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${exportItem.percentage}%" aria-valuenow="${exportItem.percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                `;
                            } else if (exportItem.status === 'completed') {
                                statusClass = 'bg-success';
                                iconClass = 'fas fa-check';
                            } else if (exportItem.status === 'failed') {
                                statusClass = 'bg-danger';
                                iconClass = 'fas fa-exclamation-triangle';
                            }

                            const url = exportItem.download_url ? exportItem.download_url : '#';
                            const target = exportItem.download_url ? '_blank' : '_self';

                            html += `
                                <a href="${url}" target="${target}" class="dropdown-item">
                                    <div class="dropdown-item-icon ${statusClass} text-white d-flex align-items-center justify-content-center">
                                        <i class="${iconClass}"></i>
                                    </div>
                                    <div class="dropdown-item-desc">
                                        <b>${exportItem.title}</b>
                                        <div class="time text-primary">${exportItem.status.charAt(0).toUpperCase() + exportItem.status.slice(1)} ${exportItem.status === 'processing' ? '(' + exportItem.percentage + '%)' : ''}</div>
                                        ${progressHtml}
                                    </div>
                                </a>
                            `;
                        });

                        $list.html(html);

                        if (activeCount > 0) {
                            $tray.find('.nav-link').addClass('beep');
                            if (!exportPollingInterval) {
                                startPolling();
                            }
                        } else {
                            $tray.find('.nav-link').removeClass('beep');
                            // If no active exports, we can slow down or stop polling, 
                            // but keep it for a bit to show completion
                        }
                    } else {
                        $tray.hide();
                    }
                }
            });
        }

        function startPolling() {
            if (exportPollingInterval) clearInterval(exportPollingInterval);
            exportPollingInterval = setInterval(updateExportTray, 3000);
        }

        // Initial check
        updateExportTray();
        // Always poll at least every 10 seconds even if none active to catch new ones
        setInterval(updateExportTray, 10000);

        // Listen for global event to speed up polling when a new export starts
        $(document).on('exportStarted', function() {
            updateExportTray();
            startPolling();
        });

    })(jQuery);
</script>
@endpush
