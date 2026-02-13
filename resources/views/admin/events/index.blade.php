@extends('admin.layouts.app')

@section('title', 'Events Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18" style="color: #fffefeff;">Events Management</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm" style="background-color: #1b8007ff;">
                            <i class="fas fa-plus me-2"></i>Create Event
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Event Date</th>
                                        <th>Registrations</th>
                                        <th>Revenue</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($events as $event)
                                        <tr>
                                            <td>{{ $event->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($event->image)
                                                        <img src="{{ asset($event->image) }}" alt="{{ $event->title }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $event->title }}</h6>
                                                        <small class="text-muted">{{ Str::limit($event->description, 50) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $event->category ?? 'N/A' }}</td>
                                            <td>₹{{ number_format($event->price, 2) }}</td>
                                            <td>{{ $event->event_date->format('M j, Y') }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $event->registered_count }}/{{ $event->max_participants }}</span>
                                            </td>
                                            <td>₹{{ number_format($event->total_revenue, 2) }}</td>
                                            <td>
                                                @switch($event->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Active</span>
                                                        @break
                                                    @case('closed')
                                                        <span class="badge bg-danger">Closed</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-secondary">Completed</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-warning">{{ $event->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton{{ $event->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $event->id }}">
                                                        <li>
                                                            <a href="{{ route('admin.events.show', $event->id) }}" class="dropdown-item">
                                                                <i class="fas fa-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.events.edit', $event->id) }}" class="dropdown-item">
                                                                <i class="fas fa-edit me-2"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.events.toggle-status', $event->id) }}" class="dropdown-item">
                                                                <i class="fas fa-power-off me-2"></i>{{ $event->status === 'active' ? 'Close' : 'Activate' }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.events.regenerate-link', $event->id) }}" class="dropdown-item">
                                                                <i class="fas fa-link me-2"></i>Regenerate Link
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a href="{{ route('admin.events.delete', $event->id) }}" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this event?')">
                                                                <i class="fas fa-trash me-2"></i>Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <h5>No Events Found</h5>
                                                <p class="text-muted">Create your first event to get started.</p>
                                                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Create Event
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($events->hasPages())
                            <div class="d-flex justify-content-between">
                                <div class="text-muted">
                                    Showing {{ $events->firstItem() }} to {{ $events->lastItem() }} of {{ $events->total() }} entries
                                </div>
                                {{ $events->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
