@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item">Sub-Admin Management</div>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('msg') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="section-body">
            {{-- Stats Cards --}}
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary"><i class="fas fa-user-shield"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Sub-Admins</h4></div>
                            <div class="card-body">{{ $subAdmins->total() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Active</h4></div>
                            <div class="card-body">{{ $subAdmins->filter(fn($u) => !$u->ban)->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger"><i class="fas fa-ban"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Inactive</h4></div>
                            <div class="card-body">{{ $subAdmins->filter(fn($u) => $u->ban)->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-info"><i class="fas fa-key"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Permissions Assigned</h4></div>
                            <div class="card-body">{{ $permissionCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card">
                <div class="card-body">
                    <form action="" method="get" class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email or mobile...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ getAdminPanelUrl() }}/sub-admins" class="btn btn-secondary ml-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h4>Sub-Admins</h4>
                            @can('admin_sub_admins_create')
                                <a href="{{ getAdminPanelUrl() }}/sub-admins/create" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Create Sub-Admin
                                </a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="text-left">Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subAdmins as $subAdmin)
                                            <tr>
                                                <td>{{ $subAdmin->id }}</td>
                                                <td class="text-left">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-2" style="background: #e3eaef; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                                                            <i class="fas fa-user text-muted" style="font-size:14px;"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $subAdmin->full_name }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $subAdmin->email }}</td>
                                                <td>{{ $subAdmin->mobile ?? '-' }}</td>
                                                <td>
                                                    @if($subAdmin->ban)
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @else
                                                        <span class="badge badge-success">Active</span>
                                                    @endif
                                                </td>
                                                <td>{{ $subAdmin->created_at ? dateTimeFormat($subAdmin->created_at, 'j M Y') : '-' }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        @can('admin_sub_admins_permissions')
                                                            <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/permissions" class="btn btn-sm btn-outline-primary mr-1" title="Permissions">
                                                                <i class="fas fa-key"></i>
                                                            </a>
                                                        @endcan

                                                        @can('admin_sub_admins_edit')
                                                            <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/edit" class="btn btn-sm btn-outline-info mr-1" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endcan

                                                        @can('admin_sub_admins_toggle_status')
                                                            <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/toggle-status"
                                                               class="btn btn-sm {{ $subAdmin->ban ? 'btn-outline-success' : 'btn-outline-warning' }} mr-1"
                                                               title="{{ $subAdmin->ban ? 'Activate' : 'Deactivate' }}"
                                                               onclick="return confirm('Are you sure you want to {{ $subAdmin->ban ? 'activate' : 'deactivate' }} this sub-admin?')">
                                                                <i class="fas {{ $subAdmin->ban ? 'fa-check' : 'fa-ban' }}"></i>
                                                            </a>
                                                        @endcan

                                                        @can('admin_sub_admins_reset_password')
                                                            <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/reset-password" class="btn btn-sm btn-outline-secondary mr-1" title="Reset Password">
                                                                <i class="fas fa-lock"></i>
                                                            </a>
                                                        @endcan

                                                        @can('admin_sub_admins_delete')
                                                            <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/delete"
                                                               class="btn btn-sm btn-outline-danger"
                                                               title="Delete"
                                                               onclick="return confirm('Are you sure you want to permanently delete this sub-admin? This action cannot be undone.')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                                                    No sub-admins found. Create your first sub-admin to get started.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            {{ $subAdmins->appends(request()->input())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
