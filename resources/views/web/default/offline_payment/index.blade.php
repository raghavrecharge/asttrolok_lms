@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">My Offline Payments</h2>
            <a href="{{ route('webinars') }}" class="btn btn-sm btn-primary mt-3 mt-md-0">
                <i class="fa fa-plus mr-1"></i>
                Browse Courses
            </a>
        </div>

        <div class="mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-white">
            
            @if($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>UTR Number</th>
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
                                            <strong>{{ $payment->webinar->title }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $payment->webinar->creator->full_name }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-success font-weight-bold">{{ $payment->getFormattedAmount() }}</span>
                                    </td>
                                    <td>
                                        <code>{{ $payment->getUtrNumber() }}</code>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $payment->getStatusBadgeClass() }}">
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
                                            <a href="{{ route('offline_payment.show', $payment->id) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            
                                            @if($payment->status === \App\Models\OfflinePayment::$approved && $payment->sale_id)
                                                <a href="{{ route('webinar', $payment->webinar_id) }}" 
                                                   class="btn btn-success" title="Start Course">
                                                    <i class="fa fa-play"></i>
                                                </a>
                                            @endif
                                            
                                            @if($payment->status === \App\Models\OfflinePayment::$failed)
                                                <a href="{{ route('offline_payment.create', $payment->webinar_id) }}" 
                                                   class="btn btn-warning" title="Resubmit Payment">
                                                    <i class="fa fa-redo"></i>
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
                    <p class="text-muted">You haven't submitted any offline payment details yet.</p>
                    <div class="mt-4">
                        <a href="{{ route('webinars') }}" class="btn btn-primary">
                            <i class="fa fa-search mr-2"></i>Browse Courses
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if($payments->count() > 0)
    <section class="mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">{{ $payments->count() }}</h3>
                        <p class="text-muted mb-0">Total Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">{{ $payments->where('status', \App\Models\OfflinePayment::$pending)->count() }}</h3>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">{{ $payments->where('status', \App\Models\OfflinePayment::$approved)->count() }}</h3>
                        <p class="text-muted mb-0">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger">{{ $payments->whereIn('status', [\App\Models\OfflinePayment::$failed, \App\Models\OfflinePayment::$reject])->count() }}</h3>
                        <p class="text-muted mb-0">Failed/Rejected</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
@endsection
