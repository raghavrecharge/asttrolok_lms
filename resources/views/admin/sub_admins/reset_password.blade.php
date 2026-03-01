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
                <div class="breadcrumb-item">Reset Password</div>
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
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h4>Reset Password</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3" style="background: #6777ef; border-radius:50%; width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-user-shield text-white"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $subAdmin->full_name }}</strong><br>
                                        <small class="text-muted">{{ $subAdmin->email }}</small>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/reset-password" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label>New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Minimum 6 characters" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                           placeholder="Confirm new password" required>
                                </div>

                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-warning mr-2" onclick="return confirm('Are you sure you want to reset this sub-admin\'s password?')">
                                        <i class="fas fa-lock mr-1"></i> Reset Password
                                    </button>
                                    <a href="{{ getAdminPanelUrl() }}/sub-admins" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
