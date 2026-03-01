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
                <div class="breadcrumb-item">{{ !empty($subAdmin) ? 'Edit' : 'Create' }}</div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ !empty($subAdmin) ? 'Edit Sub-Admin Details' : 'Create New Sub-Admin' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl() }}/sub-admins/{{ !empty($subAdmin) ? $subAdmin->id . '/update' : 'store' }}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                           value="{{ !empty($subAdmin) ? $subAdmin->full_name : old('full_name') }}"
                                           placeholder="Enter full name" required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ !empty($subAdmin) ? $subAdmin->email : old('email') }}"
                                           placeholder="Enter email address" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                           value="{{ !empty($subAdmin) ? $subAdmin->mobile : old('mobile') }}"
                                           placeholder="Enter mobile number">
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if(empty($subAdmin))
                                    <div class="form-group">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Minimum 6 characters" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control"
                                               placeholder="Confirm password" required>
                                    </div>
                                @endif

                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="fas fa-save mr-1"></i>
                                        {{ !empty($subAdmin) ? 'Update Sub-Admin' : 'Create Sub-Admin' }}
                                    </button>
                                    <a href="{{ getAdminPanelUrl() }}/sub-admins" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if(!empty($subAdmin))
                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    @can('admin_sub_admins_permissions')
                                        <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/permissions" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-key mr-1"></i> Manage Permissions
                                        </a>
                                    @endcan
                                    @can('admin_sub_admins_reset_password')
                                        <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/reset-password" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-lock mr-1"></i> Reset Password
                                        </a>
                                    @endcan
                                    @can('admin_sub_admins_toggle_status')
                                        <a href="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/toggle-status"
                                           class="btn btn-outline-{{ $subAdmin->ban ? 'success' : 'danger' }} btn-sm"
                                           onclick="return confirm('Are you sure?')">
                                            <i class="fas {{ $subAdmin->ban ? 'fa-check' : 'fa-ban' }} mr-1"></i>
                                            {{ $subAdmin->ban ? 'Activate' : 'Deactivate' }}
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
