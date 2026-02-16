@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item">UPE Sales</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Stats Cards --}}
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary"><i class="fas fa-shopping-cart"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Sales</h4></div>
                            <div class="card-body">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Active</h4></div>
                            <div class="card-body">{{ $stats['active'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Pending Payment</h4></div>
                            <div class="card-body">{{ $stats['pending'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger"><i class="fas fa-undo"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Refunded</h4></div>
                            <div class="card-body">{{ $stats['refunded'] }}</div>
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
                    <form method="get" action="{{ getAdminPanelUrl() }}/upe/sales">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Search</label>
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email, UUID...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All</option>
                                        @foreach(['active','pending_payment','completed','refunded','partially_refunded','expired','cancelled','suspended'] as $s)
                                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Pricing Mode</label>
                                    <select name="pricing_mode" class="form-control">
                                        <option value="">All</option>
                                        @foreach(['one_time','full','installment','subscription','free'] as $m)
                                            <option value="{{ $m }}" {{ request('pricing_mode') == $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>User ID</label>
                                    <input type="number" name="user_id" class="form-control" value="{{ request('user_id') }}" placeholder="User ID">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sales Table --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Valid Until</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->id }}</td>
                                        <td>
                                            @if($sale->user)
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $sale->user_id }}/edit" target="_blank">
                                                    {{ $sale->user->full_name ?? 'N/A' }}
                                                </a>
                                                <div class="text-muted small">{{ $sale->user->email ?? '' }}</div>
                                            @else
                                                User #{{ $sale->user_id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($sale->product)
                                                <span class="badge badge-light">{{ $sale->product->product_type }}</span>
                                                {{ $sale->product->name }}
                                            @else
                                                Product #{{ $sale->product_id }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $sale->pricing_mode }}</span>
                                        </td>
                                        <td>₹{{ number_format($sale->base_fee_snapshot, 2) }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'pending_payment' => 'warning',
                                                    'completed' => 'primary',
                                                    'refunded' => 'danger',
                                                    'partially_refunded' => 'warning',
                                                    'expired' => 'secondary',
                                                    'cancelled' => 'dark',
                                                    'suspended' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$sale->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($sale->valid_until)
                                                {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                                @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                                    <span class="text-danger small">(expired)</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Lifetime</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <a href="{{ getAdminPanelUrl() }}/upe/sales/{{ $sale->id }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
