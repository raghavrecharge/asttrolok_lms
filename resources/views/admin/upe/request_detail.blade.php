@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/upe/requests">Payment Requests</a></div>
                <div class="breadcrumb-item">Request #{{ $paymentRequest->id }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h4>Request Details</h4></div>
                        <div class="card-body">
                            @php
                                $reqStatusColors = ['pending'=>'warning','verified'=>'info','approved'=>'primary','executed'=>'success','rejected'=>'danger'];
                            @endphp
                            <table class="table table-sm">
                                <tr><th width="35%">ID</th><td>{{ $paymentRequest->id }}</td></tr>
                                <tr><th>UUID</th><td><code class="small">{{ $paymentRequest->uuid }}</code></td></tr>
                                <tr><th>Type</th><td><span class="badge badge-info">{{ ucfirst(str_replace('_',' ',$paymentRequest->request_type)) }}</span></td></tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="badge badge-{{ $reqStatusColors[$paymentRequest->status] ?? 'secondary' }}">{{ ucfirst($paymentRequest->status) }}</span></td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($paymentRequest->user)
                                            <a href="{{ getAdminPanelUrl() }}/users/{{ $paymentRequest->user_id }}/edit" target="_blank">
                                                {{ $paymentRequest->user->full_name }}
                                            </a>
                                            <div class="text-muted small">{{ $paymentRequest->user->email }} | {{ $paymentRequest->user->mobile }}</div>
                                        @else
                                            User #{{ $paymentRequest->user_id }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sale</th>
                                    <td>
                                        @if($paymentRequest->sale_id)
                                            <a href="{{ getAdminPanelUrl() }}/upe/sales/{{ $paymentRequest->sale_id }}">Sale #{{ $paymentRequest->sale_id }}</a>
                                            @if($paymentRequest->sale && $paymentRequest->sale->product)
                                                <div class="small text-muted">{{ $paymentRequest->sale->product->name }}</div>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>Created</th><td>{{ \Carbon\Carbon::parse($paymentRequest->created_at)->format('d M Y H:i') }}</td></tr>
                                @if($paymentRequest->verified_by)
                                    <tr><th>Verified By</th><td>User #{{ $paymentRequest->verified_by }} at {{ $paymentRequest->verified_at ? \Carbon\Carbon::parse($paymentRequest->verified_at)->format('d M Y H:i') : '-' }}</td></tr>
                                @endif
                                @if($paymentRequest->approved_by)
                                    <tr><th>Approved By</th><td>User #{{ $paymentRequest->approved_by }} at {{ $paymentRequest->approved_at ? \Carbon\Carbon::parse($paymentRequest->approved_at)->format('d M Y H:i') : '-' }}</td></tr>
                                @endif
                                @if($paymentRequest->executed_at)
                                    <tr><th>Executed At</th><td>{{ \Carbon\Carbon::parse($paymentRequest->executed_at)->format('d M Y H:i') }}</td></tr>
                                @endif
                                @if($paymentRequest->rejected_reason)
                                    <tr><th>Rejected Reason</th><td class="text-danger">{{ $paymentRequest->rejected_reason }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Payload --}}
                    <div class="card">
                        <div class="card-header"><h4>Request Payload</h4></div>
                        <div class="card-body">
                            @if($paymentRequest->payload)
                                <pre class="bg-light p-3 rounded small">{{ json_encode($paymentRequest->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <p class="text-muted">No payload data</p>
                            @endif
                        </div>
                    </div>

                    @if($paymentRequest->execution_result)
                        <div class="card">
                            <div class="card-header"><h4>Execution Result</h4></div>
                            <div class="card-body">
                                <pre class="bg-light p-3 rounded small">{{ json_encode($paymentRequest->execution_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Actions Panel --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h4>Actions</h4></div>
                        <div class="card-body">
                            @if($paymentRequest->status === 'pending')
                                <div class="mb-3">
                                    <form method="POST" action="{{ getAdminPanelUrl() }}/upe/requests/{{ $paymentRequest->id }}/verify" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-info" onclick="return confirm('Verify this request?')">
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                    </form>
                                </div>
                                <hr>
                                <form method="POST" action="{{ getAdminPanelUrl() }}/upe/requests/{{ $paymentRequest->id }}/reject">
                                    @csrf
                                    <div class="form-group">
                                        <label>Reject Reason</label>
                                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            @elseif($paymentRequest->status === 'verified')
                                <div class="mb-3">
                                    <form method="POST" action="{{ getAdminPanelUrl() }}/upe/requests/{{ $paymentRequest->id }}/approve" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" onclick="return confirm('Approve this request?')">
                                            <i class="fas fa-thumbs-up"></i> Approve
                                        </button>
                                    </form>
                                </div>
                                <hr>
                                <form method="POST" action="{{ getAdminPanelUrl() }}/upe/requests/{{ $paymentRequest->id }}/reject">
                                    @csrf
                                    <div class="form-group">
                                        <label>Reject Reason</label>
                                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            @elseif($paymentRequest->status === 'approved')
                                <form method="POST" action="{{ getAdminPanelUrl() }}/upe/requests/{{ $paymentRequest->id }}/execute">
                                    @csrf
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> This request is approved and ready to execute.
                                        Executing will perform the actual financial operation ({{ str_replace('_',' ',$paymentRequest->request_type) }}).
                                    </div>
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Execute this request? This action is irreversible.')">
                                        <i class="fas fa-play"></i> Execute Now
                                    </button>
                                </form>
                            @elseif($paymentRequest->status === 'executed')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> This request has been executed successfully.
                                </div>
                            @elseif($paymentRequest->status === 'rejected')
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle"></i> This request was rejected.
                                    @if($paymentRequest->rejected_reason)
                                        <br><strong>Reason:</strong> {{ $paymentRequest->rejected_reason }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Ledger Summary for the associated sale --}}
                    @if($ledgerSummary)
                        <div class="card">
                            <div class="card-header"><h4>Sale Ledger Summary</h4></div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-muted small">Credits</div>
                                        <div class="h5 text-success">₹{{ number_format($ledgerSummary['total_credits'], 2) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Debits</div>
                                        <div class="h5 text-danger">₹{{ number_format($ledgerSummary['total_debits'], 2) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Net Balance</div>
                                        <div class="h5">₹{{ number_format($ledgerSummary['net_balance'], 2) }}</div>
                                    </div>
                                </div>
                                @if(!empty($ledgerSummary['breakdown']))
                                    <hr>
                                    <table class="table table-sm">
                                        @foreach($ledgerSummary['breakdown'] as $type => $data)
                                            <tr><td>{{ ucfirst(str_replace('_',' ',$type)) }}</td><td>{{ $data['count'] }}x</td><td>₹{{ number_format($data['total'], 2) }}</td></tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
