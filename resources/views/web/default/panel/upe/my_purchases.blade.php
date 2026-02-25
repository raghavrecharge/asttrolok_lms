@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class="section-title">My Purchases</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <form method="get" class="d-flex">
                        <select name="status" class="form-control mr-10">
                            <option value="">All Statuses</option>
                            @foreach(['active','pending_payment','completed','refunded','partially_refunded','expired'] as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <div class="row">
            @forelse($sales as $sale)
                <div class="col-12 col-lg-6 mt-15">
                    <div class="panel-section-card py-20 px-25">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h3 class="font-16 font-weight-bold text-dark-blue">
                                    @if($sale->product)
                                        {{ $sale->product->name }}
                                    @else
                                        Product #{{ $sale->product_id }}
                                    @endif
                                </h3>
                                <span class="font-12 text-gray">
                                    @if($sale->product)
                                        <span class="badge badge-circle-white px-5 py-2 font-10">{{ ucfirst($sale->product->product_type) }}</span>
                                    @endif
                                    <span class="badge badge-circle-white px-5 py-2 font-10">{{ ucfirst($sale->pricing_mode) }}</span>
                                </span>
                            </div>
                            <div class="text-right">
                                @php
                                    $statusColors = [
                                        'active' => 'primary',
                                        'pending_payment' => 'warning',
                                        'refunded' => 'danger',
                                        'partially_refunded' => 'warning',
                                        'completed' => 'secondary',
                                    ];
                                    $statusClass = 'badge-' . ($statusColors[$sale->status] ?? 'secondary');
                                    $hasEmiDue = $sale->pricing_mode === 'installment'
                                        && $sale->installmentPlan
                                        && $sale->installmentPlan->schedules->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])->count() > 0;
                                @endphp
                                @if($sale->status === 'active' && $hasEmiDue)
                                    <span class="badge badge-primary px-10 py-5">Active</span>
                                    <span class="badge badge-warning px-10 py-5 ml-5">EMI Due</span>
                                @elseif($sale->status === 'active' && $sale->pricing_mode === 'installment' && $sale->installmentPlan)
                                    <span class="badge badge-primary px-10 py-5">Fully Paid</span>
                                @else
                                    <span class="badge {{ $statusClass }} px-10 py-5">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-15 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="font-14 text-gray">Amount:</span>
                                @php
                                    $displayPrice = $sale->installmentPlan
                                        ? $sale->installmentPlan->total_amount
                                        : ($balances[$sale->id] ?? $sale->base_fee_snapshot);
                                @endphp
                                <span class="font-16 font-weight-bold {{ $sale->base_fee_snapshot > $displayPrice ? 'text-primary' : '' }}">{{ handlePrice($displayPrice) }}</span>
                                @if($sale->base_fee_snapshot > $displayPrice)
                                    <span class="font-12 text-gray" style="text-decoration: line-through;">{{ handlePrice($sale->base_fee_snapshot) }}</span>
                                    <span class="badge badge-warning font-10 ml-5">Coupon Applied</span>
                                @endif
                            </div>
                            <div>
                                <span class="font-14 text-gray">Balance:</span>
                                <span class="font-16 font-weight-bold {{ ($balances[$sale->id] ?? 0) > 0 ? 'text-primary' : 'text-danger' }}">
                                    {{ handlePrice($balances[$sale->id] ?? 0) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-10 d-flex align-items-center justify-content-between">
                            <div>
                                @if(isset($accessResults[$sale->id]))
                                    @if($accessResults[$sale->id]->hasAccess)
                                        <span class="text-primary font-12"><i data-feather="check-circle" width="14" height="14"></i> Access: {{ ucfirst($accessResults[$sale->id]->accessType) }}</span>
                                    @else
                                        <span class="text-danger font-12"><i data-feather="x-circle" width="14" height="14"></i> No Access</span>
                                    @endif
                                @endif
                            </div>
                            <div>
                                @if($sale->valid_until)
                                    <span class="font-12 text-gray">
                                        Valid until: {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                        @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                            <span class="text-danger">(expired)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="font-12 text-gray">Lifetime access</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-15 d-flex">
                            <a href="/panel/upe/purchases/{{ $sale->id }}" class="btn btn-sm btn-primary mr-10">View Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 mt-15">
                    <div class="panel-section-card py-40 px-25 text-center">
                        <p class="text-gray font-16">No purchases found.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-20">
            {{ $sales->appends(request()->query())->links() }}
        </div>
    </section>
@endsection
