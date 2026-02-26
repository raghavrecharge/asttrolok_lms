@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        body {
            background-color: #f4f7fa; /* Light, premium blue-gray background */
        }
        .purchase-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .purchase-card:hover {
            box-shadow: 0 20px 50px rgba(31, 59, 100, 0.12);
            transform: translateY(-8px);
            border-color: #1f3b64;
        }
        .purchase-card:hover .info-item i {
            transform: scale(1.2);
            color: #1f3b64;
        }
        .info-item i {
            transition: all 0.3s ease;
        }
        .card-header-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #f1f4f8 100%);
            padding: 22px 25px;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }
        .card-header-gradient::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25px;
            right: 25px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(31, 59, 100, 0.1), transparent);
        }
        .card-body-content {
            padding: 25px;
            flex-grow: 1;
        }
        .card-footer-actions {
            padding: 18px 25px;
            background: #fbfcfe;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 10px;
            color: #8c98a4;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            font-weight: 600;
        }
        .info-value {
            font-size: 15px;
            font-weight: 700;
            color: #1f3b64;
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .type-tag {
            font-size: 9px;
            background: rgba(67, 212, 119, 0.15);
            color: #28a745;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 700;
            margin-right: 8px;
            letter-spacing: 0.5px;
        }
        .pricing-tag {
            font-size: 9px;
            background: rgba(31, 59, 100, 0.08);
            color: #1f3b64;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        /* Pagination Refined Styling */
        .pagination-container {
            background: #fff;
            padding: 15px 25px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        .pagination-container nav {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .pagination-container nav > div:first-child {
            display: none !important;
        }
        .pagination-container nav > div:last-child {
            display: flex !important;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .pagination-container nav div:last-child div:first-child {
            display: none !important;
        }
        .pagination-container nav div:last-child div:last-child {
            display: flex !important;
            box-shadow: none !important;
            border: none !important;
            background: transparent !important;
        }
        .pagination-container nav a, 
        .pagination-container nav span {
            border-radius: 12px !important;
            margin: 4px;
            border: 1px solid #eee !important;
            min-width: 40px;
            height: 40px;
            display: flex !important;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            transition: all 0.3s ease;
            color: #1f3b64;
            background: #fff;
            padding: 0 10px !important;
            font-size: 14px;
            text-decoration: none !important;
        }
        .pagination-container nav a:hover {
            background: #f8f9fb;
            border-color: #1f3b64 !important;
        }
        .pagination-container nav [aria-current="page"] span {
            background-color: #1f3b64 !important;
            color: #fff !important;
            border: none !important;
            width: 100%;
            height: 100%;
        }
        .pagination-container svg {
            width: 18px !important;
            height: 18px !important;
            stroke-width: 3;
        }
        
        /* Learning Page Button Animation */
        .btn-learning {
            background: linear-gradient(135deg, #43d477 0%, #28a745 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(67, 212, 119, 0.3);
            transition: all 0.3s ease;
            color: white !important;
        }
        .btn-learning:hover {
            box-shadow: 0 6px 20px rgba(67, 212, 119, 0.4);
            transform: scale(1.02);
            background: linear-gradient(135deg, #28a745 0%, #43d477 100%);
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between mb-20">
            <h2 class="section-title mb-0">My Purchases</h2>
        </div>

        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row align-items-center">
                <div class="col-12 col-md-8">
                    <form method="get" class="d-flex align-items-center">
                        <div class="position-relative mr-15" style="width: 200px;">
                            <i data-feather="grid" width="16" height="16" class="text-gray" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10;"></i>
                            <select name="type" class="form-control" style="border-radius: 12px; padding-left: 42px; height: 48px; background-color: #f8f9fb; border: 1px solid #eee;">
                                <option value="course" {{ request('type', 'course') == 'course' ? 'selected' : '' }}>Courses</option>
                                <option value="meeting" {{ request('type') == 'meeting' ? 'selected' : '' }}>Meetings</option>
                                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                            </select>
                        </div>

                        <div class="position-relative w-100">
                            <i data-feather="filter" width="16" height="16" class="text-gray" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10;"></i>
                            <select name="status" class="form-control" style="border-radius: 12px; padding-left: 42px; height: 48px; background-color: #f8f9fb; border: 1px solid #eee;">
                                <option value="">All Statuses</option>
                                @foreach(['active','pending_payment','completed','refunded','partially_refunded','expired'] as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary ml-15 px-30" style="border-radius: 12px; height: 48px; font-weight: 600;">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <div class="row">
            @forelse($sales as $sale)
                <div class="col-12 col-lg-6 mt-20">
                    <div class="purchase-card">
                        <div class="card-header-gradient">
                            <div class="d-flex align-items-start justify-content-between">
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center mb-5">
                                        @if($sale->product)
                                            <span class="type-tag text-uppercase">{{ str_replace('_', ' ', $sale->product->product_type) }}</span>
                                        @endif
                                        <span class="pricing-tag text-uppercase">{{ $sale->pricing_mode }}</span>
                                    </div>
                                    <h3 class="font-16 font-weight-bold text-dark-blue line-height-1" style="max-width: 90%;">
                                        @if($sale->product)
                                            {{ $sale->product->name }}
                                        @else
                                            Product #{{ $sale->product_id }}
                                        @endif
                                    </h3>
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
                                        <div class="status-badge badge-primary mb-5">Active</div>
                                        <div class="status-badge badge-warning">EMI Due</div>
                                    @elseif($sale->status === 'active' && $sale->pricing_mode === 'installment' && $sale->installmentPlan)
                                        <div class="status-badge badge-primary">Completed (EMI)</div>
                                    @else
                                        <div class="status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i data-feather="dollar-sign" width="12" class="mr-5"></i> Amount Paid
                                    </div>
                                    <div class="info-value d-flex align-items-center">
                                        @php
                                            $displayPrice = $sale->installmentPlan
                                                ? $sale->installmentPlan->total_amount
                                                : ($balances[$sale->id] ?? $sale->base_fee_snapshot);
                                        @endphp
                                        <span class="{{ $sale->base_fee_snapshot > $displayPrice ? 'text-primary' : '' }} font-16">{{ handlePrice($displayPrice) }}</span>
                                        @if($sale->base_fee_snapshot > $displayPrice)
                                            <span class="font-10 text-gray ml-2" style="text-decoration: line-through;">{{ handlePrice($sale->base_fee_snapshot) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-label">
                                        <i data-feather="database" width="12" class="mr-5"></i> Balance
                                    </div>
                                    <div class="info-value {{ ($balances[$sale->id] ?? 0) > 0 ? 'text-danger' : 'text-primary' }} font-16">
                                        {{ handlePrice($balances[$sale->id] ?? 0) }}
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-label">
                                        <i data-feather="shield" width="12" class="mr-5"></i> Access
                                    </div>
                                    <div class="info-value">
                                        @if(isset($accessResults[$sale->id]))
                                            @if($accessResults[$sale->id]->hasAccess)
                                                <span class="text-primary font-14 d-flex align-items-center">
                                                    <i data-feather="check-circle" width="14" class="mr-5"></i> 
                                                    <span>{{ ucfirst($accessResults[$sale->id]->accessType) }}</span>
                                                </span>
                                            @else
                                                <span class="text-danger font-14 d-flex align-items-center">
                                                    <i data-feather="x-circle" width="14" class="mr-5"></i> 
                                                    <span>No Access</span>
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-label">
                                        <i data-feather="calendar" width="12" class="mr-5"></i> Validity
                                    </div>
                                    <div class="info-value font-14 text-dark-blue">
                                        @if($sale->valid_until)
                                            {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                            @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                                <span class="text-danger ml-2 font-11">(Expired)</span>
                                            @endif
                                        @else
                                            Lifetime
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer-actions">
                            <a href="/panel/upe/purchases/{{ $sale->id }}" class="btn btn-sm btn-outline-primary px-20 btn-hover-shadow" style="border-radius: 10px; font-weight: 600;">
                                <i data-feather="info" width="14" class="mr-5"></i> Details
                            </a>
                            @if($sale->product && in_array($sale->product->product_type, ['course_video', 'webinar', 'course_live']) && isset($accessResults[$sale->id]) && $accessResults[$sale->id]->hasAccess)
                                @php $webinarForLink = \App\Models\Webinar::find($sale->product->external_id); @endphp
                                @if($webinarForLink && $webinarForLink->slug)
                                    <a href="/course/learning/{{ $webinarForLink->slug }}" target="_blank" class="btn btn-sm btn-primary ml-10 d-flex align-items-center px-18 btn-learning" style="border-radius: 10px; font-weight: 600;">
                                        <i data-feather="play-circle" width="16" class="mr-8"></i> Learning Page
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 mt-15">
                    <div class="panel-section-card py-60 px-25 text-center">
                        <div class="summary-icon mx-auto mb-15" style="width: 60px; height: 60px; font-size: 24px;">
                            <i data-feather="shopping-bag" width="30" height="30"></i>
                        </div>
                        <h3 class="font-18 font-weight-bold text-dark-blue">No purchases found</h3>
                        <p class="text-gray mt-5">You haven't purchased any courses or materials yet.</p>
                        <a href="/courses" class="btn btn-primary mt-20 px-30">Browse Courses</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-40 pagination-container">
            {{ $sales->appends(request()->query())->links() }}
        </div>
    </section>
@endsection

@push('styles_bottom')
    <style>
        .pagination-container nav {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .pagination-container svg {
            width: 20px !important;
            height: 20px !important;
        }
        .pagination-container .relative.inline-flex.items-center {
            padding: 10px 15px;
            border-radius: 8px;
            margin: 0 5px;
        }
        /* Fix for potential giant arrows in some pagination templates */
        .pagination-container nav div:first-child {
            margin-bottom: 10px;
        }
    </style>
@endpush

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endpush

