@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .req-stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 22px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            height: 100%;
            transition: all 0.3s ease;
        }
        .req-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-color: #1f3b64;
        }
        .req-stat-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 14px; flex-shrink: 0;
        }
        .req-stat-label { font-size: 12px; color: #6c757d; font-weight: 500; display: block; }
        .req-stat-value { font-size: 22px; font-weight: 800; color: #1f3b64; display: block; }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.15); color: #e6a800; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.12); color: #43d477; }
        .bg-glass-danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

        .req-table-container {
            background: #fff;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f8f8f8;
        }
        .req-table {
            min-width: 680px;
        }
        .req-table thead th {
            background: #f8faff;
            border: none;
            padding: 13px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #1f3b64;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .req-table tbody td {
            padding: 18px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f4f4f4;
            font-size: 13px;
            color: #1f3b64;
        }
        .req-table tbody tr:hover { background: #fafbff; }
        .req-type-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f0f3ff; color: #1f3b64; padding: 5px 12px;
            border-radius: 8px; font-size: 11px; font-weight: 600;
            white-space: nowrap;
        }
        .req-status-badge {
            padding: 6px 14px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            display: inline-block; white-space: nowrap;
        }
        .req-status-badge.pending { background: #fff8e1; color: #e6a800; }
        .req-status-badge.verified { background: #e3f2fd; color: #1565c0; }
        .req-status-badge.approved { background: #e8f5e9; color: #2e7d32; }
        .req-status-badge.executed { background: #e8f5e9; color: #2e7d32; }
        .req-status-badge.rejected { background: #ffebee; color: #c62828; }
        .req-product-name {
            font-weight: 600; color: #1f3b64;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .req-detail-info {
            font-size: 12px; display: flex; align-items: center; gap: 4px;
        }
        .req-empty {
            text-align: center; padding: 50px 20px;
        }
        .req-empty-icon {
            width: 70px; height: 70px; border-radius: 50%;
            background: #f0f3ff; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 16px;
        }
        .req-empty-icon i { color: #1f3b64; }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">My Requests</h2>

        @php
            $totalCount = $requests->total();
            $pendingCount = 0; $completedCount = 0; $rejectedCount = 0;
            foreach($requests as $r) {
                if($r->status === 'pending' || $r->status === 'verified' || $r->status === 'approved') $pendingCount++;
                elseif($r->status === 'executed') $completedCount++;
                elseif($r->status === 'rejected') $rejectedCount++;
            }
        @endphp

        <div class="mt-25">
            <div class="row">
                <div class="col-6 col-md-3 mb-15">
                    <div class="req-stat-card">
                        <div class="req-stat-icon bg-glass-primary">
                            <i data-feather="layers" width="22" height="22"></i>
                        </div>
                        <div>
                            <span class="req-stat-value">{{ $totalCount }}</span>
                            <span class="req-stat-label">Total</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-15">
                    <div class="req-stat-card">
                        <div class="req-stat-icon bg-glass-warning">
                            <i data-feather="clock" width="22" height="22"></i>
                        </div>
                        <div>
                            <span class="req-stat-value">{{ $pendingCount }}</span>
                            <span class="req-stat-label">In Progress</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-15">
                    <div class="req-stat-card">
                        <div class="req-stat-icon bg-glass-success">
                            <i data-feather="check-circle" width="22" height="22"></i>
                        </div>
                        <div>
                            <span class="req-stat-value">{{ $completedCount }}</span>
                            <span class="req-stat-label">Completed</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-15">
                    <div class="req-stat-card">
                        <div class="req-stat-icon bg-glass-danger">
                            <i data-feather="x-circle" width="22" height="22"></i>
                        </div>
                        <div>
                            <span class="req-stat-value">{{ $rejectedCount }}</span>
                            <span class="req-stat-label">Rejected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-20 req-table-container">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table text-center req-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th class="text-left">Product</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Submitted</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $req->id }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $typeIcons = [
                                                'installment_restructure' => 'refresh-cw',
                                                'refund' => 'rotate-ccw',
                                                'extension' => 'calendar',
                                                'coupon' => 'tag',
                                            ];
                                            $icon = $typeIcons[$req->request_type] ?? 'file-text';
                                        @endphp
                                        <span class="req-type-badge">
                                            <i data-feather="{{ $icon }}" width="12" height="12"></i>
                                            {{ ucfirst(str_replace('_',' ',$req->request_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-left">
                                        @if($req->sale && $req->sale->product)
                                            <span class="req-product-name">{{ $req->sale->product->name }}</span>
                                        @elseif($req->sale_id)
                                            <span class="text-gray">Sale #{{ $req->sale_id }}</span>
                                        @else
                                            <span class="text-gray">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusLabels = [
                                                'pending' => 'Under Review',
                                                'verified' => 'Verified',
                                                'approved' => 'Approved',
                                                'executed' => 'Completed',
                                                'rejected' => 'Rejected',
                                            ];
                                            $statusText = $statusLabels[$req->status] ?? ucfirst($req->status);
                                        @endphp
                                        <span class="req-status-badge {{ $req->status }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="d-block font-weight-bold" style="font-size:13px;">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}</span>
                                        <small class="text-gray">{{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($req->status === 'rejected' && $req->rejected_reason)
                                            <span class="req-detail-info text-danger" title="{{ $req->rejected_reason }}">
                                                <i data-feather="alert-circle" width="14" height="14"></i>
                                                {{ \Illuminate\Support\Str::limit($req->rejected_reason, 25) }}
                                            </span>
                                        @elseif($req->status === 'executed' && $req->execution_result)
                                            <span class="req-detail-info" style="color:#2e7d32;">
                                                <i data-feather="check-circle" width="14" height="14"></i> Done
                                            </span>
                                        @elseif($req->payload)
                                            @if(!empty($req->payload['amount']))
                                                <span class="font-weight-bold" style="font-size:13px;">{{ handlePrice($req->payload['amount']) }}</span>
                                            @endif
                                            @if(!empty($req->payload['reason']))
                                                <span class="d-block text-gray" style="font-size:11px;">{{ \Illuminate\Support\Str::limit($req->payload['reason'], 25) }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-20">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="req-empty">
                    <div class="req-empty-icon">
                        <i data-feather="inbox" width="30" height="30"></i>
                    </div>
                    <h3 style="font-size:18px;font-weight:700;color:#1f3b64;">No Requests Yet</h3>
                    <p class="text-gray mt-10" style="font-size:13px;">Your payment requests will appear here once submitted.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
