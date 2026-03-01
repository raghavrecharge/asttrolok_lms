@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}/sub-admins">Sub-Admins</a></div>
                <div class="breadcrumb-item">Activity Logs</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Filters --}}
            <div class="card">
                <div class="card-body">
                    <form action="" method="get" class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Sub-Admin</label>
                                <select name="user_id" class="form-control">
                                    <option value="">All Sub-Admins</option>
                                    @foreach($subAdmins as $sa)
                                        <option value="{{ $sa->id }}" {{ request('user_id') == $sa->id ? 'selected' : '' }}>
                                            {{ $sa->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>Action</label>
                                <select name="action" class="form-control">
                                    <option value="">All Actions</option>
                                    <option value="sub_admin_created" {{ request('action') == 'sub_admin_created' ? 'selected' : '' }}>Created</option>
                                    <option value="sub_admin_updated" {{ request('action') == 'sub_admin_updated' ? 'selected' : '' }}>Updated</option>
                                    <option value="sub_admin_activated" {{ request('action') == 'sub_admin_activated' ? 'selected' : '' }}>Activated</option>
                                    <option value="sub_admin_deactivated" {{ request('action') == 'sub_admin_deactivated' ? 'selected' : '' }}>Deactivated</option>
                                    <option value="sub_admin_password_reset" {{ request('action') == 'sub_admin_password_reset' ? 'selected' : '' }}>Password Reset</option>
                                    <option value="permissions_updated" {{ request('action') == 'permissions_updated' ? 'selected' : '' }}>Permissions Updated</option>
                                    <option value="sub_admin_deleted" {{ request('action') == 'sub_admin_deleted' ? 'selected' : '' }}>Deleted</option>
                                    <option value="admin_action" {{ request('action') == 'admin_action' ? 'selected' : '' }}>Admin Action</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>From</label>
                                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>To</label>
                                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ getAdminPanelUrl() }}/sub-admins/activity-logs" class="btn btn-secondary ml-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Logs Table --}}
            <div class="card">
                <div class="card-header">
                    <h4>Activity Logs</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped font-14">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-left">Performed By</th>
                                    <th>Action</th>
                                    <th class="text-left">Description</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td class="text-left">
                                            @if($log->user)
                                                <strong>{{ $log->user->full_name }}</strong>
                                                <br><small class="text-muted">{{ $log->user->email }}</small>
                                            @else
                                                <span class="text-muted">Deleted User</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $actionColors = [
                                                    'sub_admin_created' => 'success',
                                                    'sub_admin_updated' => 'info',
                                                    'sub_admin_activated' => 'success',
                                                    'sub_admin_deactivated' => 'warning',
                                                    'sub_admin_password_reset' => 'secondary',
                                                    'permissions_updated' => 'primary',
                                                    'sub_admin_deleted' => 'danger',
                                                    'admin_action' => 'dark',
                                                ];
                                                $color = $actionColors[$log->action] ?? 'light';
                                                $label = ucwords(str_replace('_', ' ', $log->action));
                                            @endphp
                                            <span class="badge badge-{{ $color }}">{{ $label }}</span>
                                        </td>
                                        <td class="text-left" style="max-width: 300px;">
                                            {{ $log->description ?? '-' }}
                                        </td>
                                        <td>
                                            <small>{{ $log->ip_address ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $log->date }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                            No activity logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    {{ $logs->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
