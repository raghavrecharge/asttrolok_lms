@extends('admin.layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Event</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Events
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.events.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $event->title) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $event->description) }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <input type="text" class="form-control" id="category" name="category" value="{{ old('category', $event->category) }}" placeholder="e.g. Workshop, Seminar, Webinar">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $event->price) }}" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_participants" class="form-label">Max Participants <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="max_participants" name="max_participants" value="{{ old('max_participants', $event->max_participants) }}" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="location" class="form-label">Location</label>
                                                <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $event->location) }}" placeholder="e.g. Online, Delhi, Mumbai">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="event_date" class="form-label">Event Date & Time <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control" id="event_date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d\TH:i')) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="registration_deadline" class="form-label">Registration Deadline <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control" id="registration_deadline" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline->format('Y-m-d\TH:i')) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Event Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        @if($event->image)
                                            <div class="mt-2">
                                                <img src="{{ asset($event->image) }}" alt="Current image" class="img-thumbnail" style="max-width: 200px; height: auto;">
                                                <div class="form-text">Current image shown above. Upload new image to replace.</div>
                                            </div>
                                        @endif
                                        <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 2MB</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Event Settings</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="draft" {{ $event->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="active" {{ $event->status === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="closed" {{ $event->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                                </select>
                                            </div>

                                            <div class="alert alert-info">
                                                <h6><i class="fas fa-info-circle me-2"></i>Payment Link</h6>
                                                <p class="mb-0 small">After updating the event, the existing payment link will remain active. You can regenerate a new payment link if needed.</p>
                                            </div>

                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                                                <ul class="mb-0 small">
                                                    <li>Registration deadline must be before event date</li>
                                                    <li>Payment link will expire on registration deadline</li>
                                                    <li>Participants can register until deadline</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary me-2">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Event
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum datetime to current time
    const now = new Date();
    const localDateTime = now.toISOString().slice(0, 16);
    
    document.getElementById('event_date').min = localDateTime;
    document.getElementById('registration_deadline').min = localDateTime;
    
    // Update registration deadline max when event date changes
    document.getElementById('event_date').addEventListener('change', function() {
        document.getElementById('registration_deadline').max = this.value;
    });
    
    // Update event date min when registration deadline changes
    document.getElementById('registration_deadline').addEventListener('change', function() {
        document.getElementById('event_date').min = this.value;
    });
});
</script>
@endpush
