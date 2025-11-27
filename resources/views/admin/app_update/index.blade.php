@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>App Version Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form action="{{ getAdminPanelUrl() }}/app-update/update" method="POST">
                                @csrf

                                {{-- Android Version Section --}}
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fab fa-android"></i> Android Settings</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Latest Version (Android)</label>
                                            <input type="text" name="latest_version_android"
                                                   class="form-control @error('latest_version_android') is-invalid @enderror"
                                                   value="{{ old('latest_version_android', $updateSettings->latest_version_android ?? '1.0.0') }}"
                                                   placeholder="1.0.3" required/>
                                            @error('latest_version_android')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="force_update_android" value="1"
                                                       class="custom-control-input" id="forceUpdateAndroid"
                                                       {{ old('force_update_android', $updateSettings->force_update_android ?? false) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="forceUpdateAndroid">
                                                    Force Update (Android)
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Play Store URL</label>
                                            <input type="url" name="playstore_url"
                                                   class="form-control @error('playstore_url') is-invalid @enderror"
                                                   value="{{ old('playstore_url', $updateSettings->playstore_url ?? '') }}"
                                                   placeholder="https://play.google.com/store/apps/details?id=com.yourapp"/>
                                            @error('playstore_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- iOS Version Section --}}
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fab fa-apple"></i> iOS Settings</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Latest Version (iOS)</label>
                                            <input type="text" name="latest_version_ios"
                                                   class="form-control @error('latest_version_ios') is-invalid @enderror"
                                                   value="{{ old('latest_version_ios', $updateSettings->latest_version_ios ?? '1.0.0') }}"
                                                   placeholder="2.2.0" required/>
                                            @error('latest_version_ios')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="force_update_ios" value="1"
                                                       class="custom-control-input" id="forceUpdateIos"
                                                       {{ old('force_update_ios', $updateSettings->force_update_ios ?? false) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="forceUpdateIos">
                                                    Force Update (iOS)
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>App Store URL</label>
                                            <input type="url" name="appstore_url"
                                                   class="form-control @error('appstore_url') is-invalid @enderror"
                                                   value="{{ old('appstore_url', $updateSettings->appstore_url ?? '') }}"
                                                   placeholder="https://apps.apple.com/app/idXXXXXXXXX"/>
                                            @error('appstore_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Optional Update Section --}}
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-bell"></i> Optional Update Settings</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="optional_update" value="1"
                                                       class="custom-control-input" id="optionalUpdate"
                                                       {{ old('optional_update', $updateSettings->optional_update ?? false) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="optionalUpdate">
                                                    Enable Optional Update
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Show update notification to users without forcing them
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label>Popup Delay (Seconds)</label>
                                            <input type="number" name="delay_seconds"
                                                   class="form-control @error('delay_seconds') is-invalid @enderror"
                                                   value="{{ old('delay_seconds', $updateSettings->delay_seconds ?? 3) }}"
                                                   min="0" max="60" required/>
                                            @error('delay_seconds')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Delay before showing update popup (0-60 seconds)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Messages Section --}}
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-comment-alt"></i> Update Messages</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Force Update Message</label>
                                            <textarea name="force_update_message" rows="3"
                                                      class="form-control @error('force_update_message') is-invalid @enderror"
                                                      placeholder="Enter message for force update">{{ old('force_update_message', $updateSettings->force_update_message ?? 'Please update the app to continue using.') }}</textarea>
                                            @error('force_update_message')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>Optional Update Message</label>
                                            <textarea name="optional_update_message" rows="3"
                                                      class="form-control @error('optional_update_message') is-invalid @enderror"
                                                      placeholder="Enter message for optional update">{{ old('optional_update_message', $updateSettings->optional_update_message ?? 'A new version is available. Update now for better experience.') }}</textarea>
                                            @error('optional_update_message')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Settings
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                {{-- Preview Section --}}
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-eye"></i> Preview</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fab fa-android"></i> Android</h6>
                                <p class="mb-1"><strong>Version:</strong> {{ $updateSettings->latest_version_android ?? '1.0.0' }}</p>
                                <p class="mb-0"><strong>Force Update:</strong> 
                                    <span class="badge badge-{{ ($updateSettings->force_update_android ?? false) ? 'success' : 'danger' }}">
                                        {{ ($updateSettings->force_update_android ?? false) ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </p>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fab fa-apple"></i> iOS</h6>
                                <p class="mb-1"><strong>Version:</strong> {{ $updateSettings->latest_version_ios ?? '1.0.0' }}</p>
                                <p class="mb-0"><strong>Force Update:</strong> 
                                    <span class="badge badge-{{ ($updateSettings->force_update_ios ?? false) ? 'success' : 'danger' }}">
                                        {{ ($updateSettings->force_update_ios ?? false) ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </p>
                            </div>

                            <div class="alert alert-warning">
                                <h6><i class="fas fa-bell"></i> Optional Update</h6>
                                <p class="mb-0">
                                    <span class="badge badge-{{ ($updateSettings->optional_update ?? false) ? 'success' : 'secondary' }}">
                                        {{ ($updateSettings->optional_update ?? false) ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection