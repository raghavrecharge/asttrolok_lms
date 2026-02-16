@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item">User Access Lookup</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Search --}}
            <div class="card">
                <div class="card-header"><h4>Lookup User</h4></div>
                <div class="card-body">
                    <form method="get" class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>User ID</label>
                                <input type="number" name="user_id" class="form-control" value="{{ request('user_id') }}" placeholder="Enter user ID" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-primary btn-block">Lookup</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($user)
                {{-- User Info --}}
                <div class="card">
                    <div class="card-header"><h4>User: {{ $user->full_name }}</h4></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Email:</strong> {{ $user->email }}<br>
                                <strong>Mobile:</strong> {{ $user->mobile }}<br>
                                <strong>ID:</strong> {{ $user->id }}
                            </div>
                            <div class="col-md-4">
                                <strong>Role:</strong> {{ $user->role_name }}<br>
                                <strong>Status:</strong> {{ $user->status }}<br>
                                <strong>Joined:</strong> {{ $user->created_at ? \Carbon\Carbon::createFromTimestamp($user->created_at)->format('d M Y') : '-' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Total UPE Sales:</strong> {{ $sales->count() }}<br>
                                <strong>Active:</strong> {{ $sales->where('status','active')->count() }}<br>
                                <strong>Pending:</strong> {{ $sales->where('status','pending_payment')->count() }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Grant Free Access --}}
                <div class="card">
                    <div class="card-header"><h4>Grant Free Access</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ getAdminPanelUrl() }}/upe/grant-free" class="row">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Product</label>
                                    <select name="product_id" class="form-control" required>
                                        <option value="">Select product...</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->product_type }}) — ₹{{ number_format($p->base_fee, 2) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Reason (optional)</label>
                                    <input type="text" name="reason" class="form-control" placeholder="e.g. Support compensation">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Grant free access to this user?')">
                                        <i class="fas fa-gift"></i> Grant
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Access Table --}}
                <div class="card">
                    <div class="card-header"><h4>Sales & Access Status</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Sale #</th>
                                        <th>Product</th>
                                        <th>Mode</th>
                                        <th>Amount</th>
                                        <th>Sale Status</th>
                                        <th>Access</th>
                                        <th>Valid Until</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sales as $sale)
                                        <tr>
                                            <td>{{ $sale->id }}</td>
                                            <td>
                                                @if($sale->product)
                                                    <span class="badge badge-light">{{ $sale->product->product_type }}</span>
                                                    {{ $sale->product->name }}
                                                @else
                                                    Product #{{ $sale->product_id }}
                                                @endif
                                            </td>
                                            <td><span class="badge badge-info">{{ $sale->pricing_mode }}</span></td>
                                            <td>₹{{ number_format($sale->base_fee_snapshot, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusColors = ['active'=>'success','pending_payment'=>'warning','completed'=>'primary','refunded'=>'danger','partially_refunded'=>'warning','expired'=>'secondary','cancelled'=>'dark','suspended'=>'danger'];
                                                @endphp
                                                <span class="badge badge-{{ $statusColors[$sale->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
                                            </td>
                                            <td>
                                                @if(isset($accessResults[$sale->id]))
                                                    @if($accessResults[$sale->id]->hasAccess)
                                                        <span class="badge badge-success"><i class="fas fa-check"></i> {{ $accessResults[$sale->id]->accessType }}</span>
                                                    @else
                                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Denied</span>
                                                        <div class="small text-muted">{{ $accessResults[$sale->id]->reason }}</div>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($sale->valid_until)
                                                    {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                                @else
                                                    <span class="text-muted">Lifetime</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ getAdminPanelUrl() }}/upe/sales/{{ $sale->id }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted">No UPE sales found for this user</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
