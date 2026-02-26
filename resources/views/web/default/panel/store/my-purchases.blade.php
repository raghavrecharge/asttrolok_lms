@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <style>
        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            height: 100%;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-color: #1f3b64;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .stat-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
            display: block;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #1f3b64;
            display: block;
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.1); color: #43d477; }
        .bg-glass-danger { background: rgba(239, 102, 110, 0.1); color: #ef666e; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }

        .custom-table thead th {
            border-top: none;
            background-color: #fcfcfc;
            color: #1f3b64;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            padding: 18px 15px;
        }
        .custom-table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            color: #1f3b64;
            font-weight: 500;
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('update.purchases_statistics') }}</h2>

        <div class="mt-25">
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="shopping-bag" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $totalOrders }}</span>
                            <span class="stat-label">{{ trans('update.total_orders') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="clock" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $pendingOrders }}</span>
                            <span class="stat-label">{{ trans('update.pending_orders') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-20 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-danger">
                            <i data-feather="x-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $canceledOrders }}</span>
                            <span class="stat-label">{{ trans('update.canceled_orders') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-20 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="dollar-sign" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ !empty($totalPurchase) ? handlePrice($totalPurchase) : 0 }}</span>
                            <span class="stat-label">{{ trans('update.total_purchase') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <h2 class="section-title">{{ trans('update.purchases_report') }}</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <form action="" method="get" class="row">
                <div class="col-12 col-lg-4">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.from') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class="text-white"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="from" autocomplete="off" class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                                           aria-describedby="dateInputGroupPrepend"
                                           value="{{  request()->get('from',null)  }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.to') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class="text-white"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                                           aria-describedby="dateInputGroupPrepend"
                                           value="{{  request()->get('to',null)  }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="row">
                        <div class="col-12 col-lg-5">
                            <div class="form-group">
                                <label class="input-label">{{ trans('update.seller') }}</label>

                                <select name="seller_id" class="form-control select2" data-allow-clear="false">
                                    <option value="all">{{ trans('public.all') }}</option>

                                    @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}" @if(request()->get('seller_id',null) == $seller->id) selected @endif>{{ $seller->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-lg-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.type') }}</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="all"
                                            @if(request()->get('type',null) == 'all') selected="selected" @endif>{{ trans('public.all') }}</option>

                                    @foreach(\App\Models\Product::$productTypes as $productType)
                                        <option value="{{ $productType }}"
                                                @if(request()->get('type',null) == $productType) selected="selected" @endif>{{ trans('update.product_type_'.$productType) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-lg-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.status') }}</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="all"
                                            @if(request()->get('status',null) == 'all') selected="selected" @endif>{{ trans('public.all') }}</option>

                                    @foreach(\App\Models\ProductOrder::$status as $orderStatus)
                                        @if($orderStatus != 'pending')
                                            <option value="{{ $orderStatus }}"
                                                    @if(request()->get('status',null) == $orderStatus) selected="selected" @endif>{{ trans('update.product_order_status_'.$orderStatus) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                </div>
            </form>
        </div>
    </section>

    @if(!empty($orders) and !$orders->isEmpty())
        <section class="mt-35">
            <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
                <h2 class="section-title">{{ trans('update.purchases_history') }}</h2>
            </div>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class=" text-left">{{ trans('update.order_id') }}</th>
                                    <th>Product</th>
                                    <th>{{ trans('update.seller') }}</th>
                                    <th class="text-center">{{ trans('public.price') }}</th>
                                    <th class="text-center">{{ trans('public.discount') }}</th>
                                    <th class="text-center">{{ trans('cart.tax') }}</th>
                                    <th class="text-center">{{ trans('update.delivery_fee') }}</th>
                                    <th class="text-center">{{ trans('financial.total_amount') }}</th>
                                    <th class="text-center">{{ trans('public.type') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($orders as $order)

                                    <tr>
                                        <td class=" text-left">
                                            <span class="d-block font-weight-500 text-dark-blue font-16">{{ $order->sale->order_id ?? '-' }}</span>
                                            @if($order->bundle_id)
                                            <span class="d-block font-weight-500 text-dark-blue font-16">Bundle {{ $order->bundle_id }}</span>
                                            @endif

                                        </td>
                                        <td class=" text-left">
                                            <span class="d-block font-weight-500 text-dark-blue font-16">{{ $order->product->title }}</span>
                                            <span class="d-block font-12 text-gray">{{ $order->quantity }} {{ trans('update.product') }}</span>
                                        </td>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar bg-gray200">
                                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ !empty($order->seller) ? $order->seller->getAvatar() : '' }}" class="img-cover" alt="">
                                                </div>

                                            </div>
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ handlePrice($order->sale->amount) }}</span>
                                        </td>

                                        <td class="align-middle">
                                            @if(!empty($order->sale->discount) and (int)$order->sale->discount > 0)
                                                {{ handlePrice($order->sale->discount ?? 0) }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @if(!empty($order->sale->tax))
                                                {{ handlePrice($order->sale->tax) }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @if(!empty($order->sale->product_delivery_fee))
                                                {{ handlePrice($order->sale->product_delivery_fee) }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ handlePrice($order->sale->total_amount) }}</span>
                                        </td>

                                        <td class="align-middle">
                                            @if(!empty($order) and !empty($order->product))
                                                <span>{{ trans('update.product_type_'.$order->product->type) }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @php
                                                $statusClass = 'gray';
                                                $statusText = '';
                                                if($order->status == \App\Models\ProductOrder::$waitingDelivery) {
                                                    $statusClass = 'warning';
                                                    $statusText = trans('update.product_order_status_waiting_delivery');
                                                } elseif($order->status == \App\Models\ProductOrder::$success) {
                                                    $statusClass = 'success';
                                                    $statusText = trans('update.product_order_status_success');
                                                } elseif($order->status == \App\Models\ProductOrder::$shipped) {
                                                    $statusClass = 'primary';
                                                    $statusText = trans('update.product_order_status_shipped');
                                                } elseif($order->status == \App\Models\ProductOrder::$canceled) {
                                                    $statusClass = 'danger';
                                                    $statusText = trans('update.product_order_status_canceled');
                                                }
                                            @endphp
                                            <span class="status-badge bg-glass-{{ $statusClass }} text-{{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-gray font-13">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i data-feather="calendar" width="14" height="14" class="mr-5"></i>
                                                {{ dateTimeFormat($order->created_at, 'j M Y') }}
                                            </div>
                                            <div class="font-11 text-gray mt-5">{{ dateTimeFormat($order->created_at, 'H:i') }}</div>
                                        </td>

                                        <td class="text-center align-middle">
                                            @if(!empty($order) and $order->status != \App\Models\ProductOrder::$canceled)
                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button>
                                                    <div class="dropdown-menu font-weight-normal">
                                                        <a href="/panel/store/purchases/{{ $order->sale_id }}/productOrder/{{ $order->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10 text-primary font-weight-bold">
                                                            <i data-feather="file-text" width="14" height="14" class="mr-8"></i>
                                                            {{ trans('public.invoice') }}
                                                        </a>

                                                        @if(!empty($order->product) and $order->status == \App\Models\ProductOrder::$success)
                                                            <a href="{{ $order->product->getUrl() }}" class="webinar-actions d-block mt-10" target="_blank">
                                                                <i data-feather="message-square" width="14" height="14" class="mr-8"></i>
                                                                {{ trans('public.feedback') }}
                                                            </a>
                                                        @endif

                                                        @if($order->status == \App\Models\ProductOrder::$shipped)
                                                            <button type="button" data-sale-id="{{ $order->sale_id }}" data-product-order-id="{{ $order->id }}" class="js-view-tracking-code webinar-actions btn-transparent d-block mt-10">
                                                                <i data-feather="map-pin" width="14" height="14" class="mr-8"></i>
                                                                {{ trans('update.view_tracking_code') }}
                                                            </button>

                                                            <button type="button" data-sale-id="{{ $order->sale_id }}" data-product-order-id="{{ $order->id }}" class="js-got-the-parcel webinar-actions btn-transparent d-block mt-10">
                                                                <i data-feather="check-square" width="14" height="14" class="mr-8"></i>
                                                                {{ trans('update.i_got_the_parcel') }}
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-30">
                {{ $orders->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>

        </section>
    @else
        @include(getTemplate() . '.includes.no-result',[
              'file_name' => 'sales.png',
              'title' => trans('update.product_purchases_no_result'),
              'hint' => nl2br(trans('update.product_purchases_no_result_hint')),
          ])
    @endif

@endsection

@push('scripts_bottom')
    <script>
        var viewTrackingCodeModalTitleLang = '{{ trans('update.view_tracking_code') }}';
        var trackingCodeLang = '{{ trans('update.tracking_code') }}';
        var closeLang = '{{ trans('public.close') }}';
        var confirmLang = '{{ trans('update.confirm') }}';
        var gotTheParcelLang = '{{ trans('update.i_got_the_parcel') }}';
        var gotTheParcelConfirmTextLang = '{{ trans('update.i_got_the_parcel_confirm') }}';
        var gotTheParcelSaveSuccessLang = '{{ trans('update.i_got_the_parcel_success_save') }}';
        var gotTheParcelSaveErrorLang = '{{ trans('update.i_got_the_parcel_error_save') }}';
        var shippingTrackingUrlLang = '{{ trans('update.track_shipping') }}';
        var addressLang = '{{ trans('update.address') }}';
    </script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/store/my-purchase.min.js"></script>
@endpush
