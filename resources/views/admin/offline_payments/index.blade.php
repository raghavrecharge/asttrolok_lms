@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Offline Payment Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Offline Payments</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Statistics Cards --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                    <p class="text-muted mb-0">Total Payments</p>
                                </div>
                                <div class="avatar avatar-md bg-primary">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                    <p class="text-muted mb-0">Pending</p>
                                </div>
                                <div class="avatar avatar-md bg-warning">
                                    <i class="fa fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                                    <p class="text-muted mb-0">Approved</p>
                                </div>
                                <div class="avatar avatar-md bg-success">
                                    <i class="fa fa-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $stats['rejected'] + $stats['failed'] }}</h4>
                                    <p class="text-muted mb-0">Failed/Rejected</p>
                                </div>
                                <div class="avatar avatar-md bg-danger">
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card">
                <div class="card-header">
                    <h4>Filters</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.offline_payments.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="reject" {{ request('status') == 'reject' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Search</label>
                                    <input type="text" name="search" class="form-control" 
                                           value="{{ request('search') }}" 
                                           placeholder="Search by UTR, user name, or email">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search mr-1"></i>Search
                                        </button>
                                        <a href="{{ route('admin.offline_payments.index') }}" class="btn btn-secondary">
                                            <i class="fa fa-refresh mr-1"></i>Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Payments Table --}}
            <div class="card">
                <div class="card-header">
                    <h4>Offline Payments</h4>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Course</th>
                                        <th>Amount</th>
                                        <th>UTR Number</th>
                                        <th>Bank</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <span class="badge badge-secondary">#{{ $payment->id }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $payment->user->full_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $payment->webinar->title }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($payment->webinar->description, 50) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">{{ $payment->getFormattedAmount() }}</span>
                                            </td>
                                            <td>
                                                <code>{{ $payment->getUtrNumber() }}</code>
                                            </td>
                                            <td>{{ $payment->bank }}</td>
                                            <td>
                                                <span class="btn btn-{{ $payment->getStatusBadgeClass() }} btn-sm">
                                                    {{ $payment->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $payment->created_at->format('j M Y') }}</small>
                                                <br>
                                                <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.offline_payments.show', $payment->id) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($payment->canBeApproved())
                                                        <button type="button" class="btn btn-success approve-btn" 
                                                                data-id="{{ $payment->id }}" title="Approve">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger reject-btn" 
                                                                data-id="{{ $payment->id }}" title="Reject">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($payment->getAttachmentUrl())
                                                        <a href="{{ $payment->getAttachmentUrl() }}" target="_blank" 
                                                           class="btn btn-outline-info" title="View Screenshot">
                                                            <i class="fa fa-image"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-credit-card fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Offline Payments Found</h4>
                            <p class="text-muted">No payment submissions match your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve this payment? This will create an order and grant course access to the user.</p>
                        <div class="form-group">
                            <label>Admin Remark (Optional)</label>
                            <textarea name="admin_remark" class="form-control" rows="3" 
                                      placeholder="Add any remarks about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check mr-1"></i>Approve Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject this payment? The user will need to resubmit payment details.</p>
                        <div class="form-group">
                            <label>Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="admin_remark" class="form-control" rows="3" required
                                      placeholder="Please specify the reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-times mr-1"></i>Reject Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let currentPaymentId = null;

            // Approve button click
            $('.approve-btn').click(function() {
                currentPaymentId = $(this).data('id');
                $('#approveModal').modal('show');
            });

            // Reject button click
            $('.reject-btn').click(function() {
                currentPaymentId = $(this).data('id');
                $('#rejectModal').modal('show');
            });

            // Approve form submission
            $('#approveForm').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: `/admin/offline-payments/${currentPaymentId}/approve`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#approveModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Approved!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error approving payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });

            // Reject form submission
            $('#rejectForm').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: `/admin/offline-payments/${currentPaymentId}/reject`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#rejectModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Rejected!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error rejecting payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });
        });
    </script>
@endpush
