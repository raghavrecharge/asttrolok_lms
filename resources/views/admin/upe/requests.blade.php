@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item">Payment Requests</div>
            </div>
        </div>

        <div class="section-body">
            @if($pendingCount > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>{{ $pendingCount }}</strong> request(s) pending review.
                </div>
            @endif

            {{-- Filters --}}
            <div class="card">
                <div class="card-body">
                    <form method="get" class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach(['pending','verified','approved','executed','rejected'] as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="request_type" class="form-control">
                                <option value="">All Types</option>
                                @foreach(['offline_payment','refund','adjustment','restructure','manual_discount','subscription_cancel'] as $t)
                                    <option value="{{ $t }}" {{ request('request_type') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Requests Table --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Sale</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        <td>{{ $req->id }}</td>
                                        <td><span class="badge badge-info">{{ ucfirst(str_replace('_',' ',$req->request_type)) }}</span></td>
                                        <td>
                                            @if($req->user)
                                                {{ $req->user->full_name }}
                                                <div class="text-muted small">{{ $req->user->email }}</div>
                                            @else
                                                User #{{ $req->user_id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->sale_id)
                                                <a href="{{ getAdminPanelUrl() }}/upe/sales/{{ $req->sale_id }}">#{{ $req->sale_id }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $reqStatusColors = ['pending'=>'warning','verified'=>'info','approved'=>'primary','executed'=>'success','rejected'=>'danger'];
                                            @endphp
                                            <span class="badge badge-{{ $reqStatusColors[$req->status] ?? 'secondary' }}">{{ ucfirst($req->status) }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <a href="{{ getAdminPanelUrl() }}/upe/requests/{{ $req->id }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No requests found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $requests->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection
