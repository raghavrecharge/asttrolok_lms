@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-bolt"></i>
                        {{ $pageTitle }}
                    </h4>
                    <p class="text-muted mb-0">
                        Grant free access to all users of a source course for a target course
                    </p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <h4 class="alert-heading">
                                <i class="fas fa-check-circle"></i> Request Sent Successfully!
                            </h4>
                            <p class="mb-2">{{ session('success') }}</p>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Request Details:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Request ID:</strong> {{ session('ticket_number') ?? 'N/A' }}</li>
                                        <li><strong>Source Course:</strong> {{ session('source_course') ?? 'N/A' }}</li>
                                        <li><strong>Target Course:</strong> {{ session('target_course') ?? 'N/A' }}</li>
                                        <li><strong>Total Users:</strong> {{ session('total_users') ?? 'N/A' }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Current Status:</h6>
                                    <div class="mb-2">
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                        <p class="text-muted small mt-1">Waiting for admin completion</p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-0">
                                <a href="{{ route('admin.support.quickSupportForm') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create New Request
                                </a>
                            </p>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h4 class="alert-heading">
                                <i class="fas fa-exclamation-triangle"></i> Error
                            </h4>
                            <p class="mb-0">{{ session('error') }}</p>
                            <hr>
                            <a href="{{ route('admin.support.quickSupportForm') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Try Again
                            </a>
                        </div>
                    @endif

                    @if(!session('success'))
                    <form id="quickSupportForm" method="POST" action="{{ route('admin.support.grantQuickAccess') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="support_subject" class="form-label">
                                        Support Subject <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="support_subject" 
                                           name="support_subject" 
                                           placeholder="Enter support subject..."
                                           required>
                                    <small class="form-text text-muted">
                                        Brief description of why this access is being granted
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="source_course_id" class="form-label">
                                        Source Course <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" 
                                            id="source_course_id" 
                                            name="source_course_id" 
                                            required>
                                        <option value="">Select source course...</option>
                                        @foreach($sourceWebinars as $webinar)
                                            <option value="{{ $webinar['id'] }}" 
                                                    data-creator="{{ $webinar['creator_name'] }}">
                                                {{ $webinar['display_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Course whose users will get free access
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_course_id" class="form-label">
                                        Target Course <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" 
                                            id="target_course_id" 
                                            name="target_course_id" 
                                            required>
                                        <option value="">Select target course...</option>
                                        @foreach($targetWebinars as $webinar)
                                            <option value="{{ $webinar['id'] }}" 
                                                    data-creator="{{ $webinar['creator_name'] }}">
                                                {{ $webinar['display_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Course to grant free access for
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="support_scenario" class="form-label">
                                        Scenario <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" 
                                            id="support_scenario" 
                                            name="support_scenario" 
                                            required>
                                        <option value="">Select scenario...</option>
                                        <option value="free_course_grant">Free Course Grant</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Select the scenario type
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="remarks" class="form-label">
                                        Remarks
                                    </label>
                                    <textarea class="form-control" 
                                              id="remarks" 
                                              name="remarks" 
                                              rows="3"
                                              placeholder="Additional remarks " required></textarea>
                                    <small class="form-text text-muted">
                                        Any additional notes or comments
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> How it works:</h6>
                                    <ol class="mb-0">
                                        <li>Select a <strong>source course</strong> - all users who have access to this course will be identified</li>
                                        <li>Select a <strong>target course</strong> - free access will be granted for this course</li>
                                        <li>System will automatically grant free access to all users of the source course for the target course</li>
                                        <li>Users who already have access to the target course will be skipped</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-paper-plane"></i>
                                    Submit
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="resetForm()">
                                    <i class="fas fa-redo"></i>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    .alert-info {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #495057;
    }
    .alert-info ol {
        padding-left: 1.2rem;
    }
    .alert-info li {
        margin-bottom: 0.5rem;
        color: #495057;
    }
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    .badge {
        font-weight: 500;
    }
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Prevent same course selection
    $('#source_course_id, #target_course_id').on('change', function() {
        var sourceId = $('#source_course_id').val();
        var targetId = $('#target_course_id').val();
        
        if (sourceId && targetId && sourceId === targetId) {
            alert('Source and target courses cannot be the same!');
            $(this).val('');
        }
    });
});

function resetForm() {
    $('#quickSupportForm')[0].reset();
}
</script>
@endpush
