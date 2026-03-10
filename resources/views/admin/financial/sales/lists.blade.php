@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16A34A",
                        "primary-light": "#F0FDF4",
                        "background-light": "#F8FAFC",
                        "background-dark": "#0F172A",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "body": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        .payments-page { font-family: 'Inter', sans-serif; }
        .section-header { display: none !important; }
        .section-body > .row:first-child { display: none !important; } {{-- Hide old stats --}}
        .section-body > section.card { display: none !important; } {{-- Hide old filter --}}
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.sales') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.sales') }}</div>
            </div>
        </div>

        <div class="section-body">

        <div class="payments-page px-6 py-8">
            {{-- Header --}}
            @include('admin.includes.filter_header', [
                'title' => trans('admin/main.sales'),
                'subtitle' => 'Track your revenue, sales trends and transaction history.',
                'filters' => [
                    'status' => [
                        'name' => 'status',
                        'label' => 'All Status',
                        'options' => [
                            'success' => 'Success',
                            'refund' => 'Refund',
                            'blocked' => 'Blocked'
                        ]
                    ]
                ],
                'actions' => [
                    '<a href="' . getAdminPanelUrl() . '/financial/sales/export" class="flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white rounded-2xl text-xs font-bold hover:bg-slate-800 transition-all shadow-sm"><span class="material-symbols-outlined text-lg">download</span>Export XLS</a>'
                ]
            ])

            {{-- KPI Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    $stats = [
                        ['label' => 'Total Sales', 'value' => $totalSales['count'] . ' (' . handlePrice($totalSales['amount']) . ')', 'icon' => 'payments', 'color' => 'emerald'],
                        ['label' => 'Classes Sales', 'value' => $classesSales['count'] . ' (' . handlePrice($classesSales['amount']) . ')', 'icon' => 'menu_book', 'color' => 'blue'],
                        ['label' => 'Appointments', 'value' => $appointmentSales['count'] . ' (' . handlePrice($appointmentSales['amount']) . ')', 'icon' => 'calendar_month', 'color' => 'amber'],
                        ['label' => 'Failed Sales', 'value' => $failedSales, 'icon' => 'error_outline', 'color' => 'rose'],
                    ];
                @endphp
                @foreach($stats as $stat)
                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 transition-all hover:shadow-md">
                    <div class="size-12 rounded-2xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">{{ $stat['icon'] }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                        <p class="text-sm font-black text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_sales_export')
                                <a href="{{ getAdminPanelUrl() }}/financial/sales/export" class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-left">{{ trans('admin/main.student') }}</th>
                                        <th class="text-left">{{ trans('admin/main.instructor') }}</th>
                                        <th>{{ trans('admin/main.paid_amount') }}</th>
                                        <th>{{ trans('admin/main.discount') }}</th>
                                        <th>{{ trans('admin/main.tax') }}</th>
                                        <th class="text-left">{{ trans('admin/main.item') }}</th>
                                        <th>{{ trans('admin/main.sale_type') }}</th>
                                        <th>{{ trans('admin/main.date') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach($sales as $sale)
                                        <tr>
                                            <td>{{ $sale['id' ]}}</td>

                                            <td class="text-left">
                                                {{ !empty($sale['buyer']) ? $sale['buyer']['full_name'] : '' }}
                                                <div class="text-primary text-small font-600-bold">ID : {{  !empty($sale['buyer']) ? $sale['buyer']['id']: '' }}</div>
                                            </td>

                                            <td class="text-left">
                                                 @if(!empty($sale['item_seller']))
                                                {{ $sale['item_seller'] }}
                                                <div class="text-primary text-small font-600-bold">ID : {{  $sale['seller_id'] }}</div>
                                                 @else

                                                <div class="text-primary text-small font-600-bold"></div>
                                                     @endif
                                            </td>

                                            <td>
                                                @if($sale['payment_method'] == \App\Models\Sale::$subscribe)
                                                    <span class="">{{ trans('admin/main.subscribe') }}</span>
                                                @else
                                                    @if(!empty($sale->total_amount))
                                                        <span class="">{{ handlePrice($sale->total_amount ?? 0) }}</span>
                                                    @else
                                                      @if(!empty($sale->amount))
                                                      <span class="">{{ handlePrice($sale->amount ?? 0) }}</span>
                                                       @else
                                                        <span class="">{{ trans('public.free') }}</span>
                                                        @endif
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <span class="">{{ handlePrice($sale->discount ?? 0) }}</span>
                                            </td>
                                            <td>
                                                <span class="">{{ handlePrice($sale->tax ?? 0) }}</span>
                                            </td>
                                            <td class="text-left">
                                                <div class="media-body">
                                                     @if(!empty($sale->item_id))
                                                       <div>{{ $sale->item_title }}</div>
                                                    <div class="text-primary text-small font-600-bold">ID : {{ $sale->item_id }}</div>
                                                    @else

                                                      <div>{{ $sale->webinar->title }}</div>
                                                    <div class="text-primary text-small font-600-bold">ID : {{ $sale->webinar_id }}</div>

                                                    @endif

                                                </div>
                                            </td>

                                            <td>
                                                <span class="font-weight-bold">
                                                     @if(!empty($sale->item_id))
                                                    @if($sale->type == \App\Models\Sale::$registrationPackage)
                                                        {{ trans('update.registration_package') }}
                                                    @elseif($sale->type == \App\Models\Sale::$product)
                                                        {{ trans('update.product') }}
                                                    @elseif($sale->type == \App\Models\Sale::$bundle)
                                                        {{ trans('update.bundle') }}
                                                    @elseif($sale->type == \App\Models\Sale::$gift)
                                                        {{ trans('update.gift') }}
                                                    @elseif($sale->type == \App\Models\Sale::$installmentPayment)
                                                        {{ trans('update.installment_payment') }}
                                                    @else
                                                        {{ trans('admin/main.'.$sale->type) }}
                                                    @endif
                                                    @else
                                                    Part Payment
                                                    @endif
                                                </span>
                                            </td>

                                            <td>
                                                {{ dateTimeFormat($sale->created_at, 'j F Y H:i') }}
                                                </td>

                                            <td>
                                                 @if(!empty($sale->item_id))
                                                @if(!empty($sale->refund_at))
                                                    <span class="text-warning">{{ trans('admin/main.refund') }}</span>
                                                @elseif(!$sale->access_to_purchased_item)
                                                    <span class="text-danger">{{ trans('update.access_blocked') }}</span>
                                                @else
                                                    <span class="text-success">{{ trans('admin/main.success') }}</span>
                                                @endif
                                                  @else
                                                     <span class="text-success">{{ trans('admin/main.success') }}</span>
                                                    @endif
                                            </td>

                                            <td>
                                                 @if(!empty($sale->item_id))
                                                @can('admin_sales_invoice')
                                               @if(!empty($sale->webinar_id) || !empty($sale->subscription_id) || !empty($sale->bundle_id))
                                                        <a href="{{ getAdminPanelUrl() }}/financial/sales/{{ $sale->id }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><i class="fa fa-print" aria-hidden="true"></i></a>
                                                    @endif
                                                @endcan

                                                @can('admin_sales_refund')
                                                    @if(empty($sale->refund_at) and $sale->payment_method != \App\Models\Sale::$subscribe)
                                                        @include('admin.includes.delete_button',[
                                                                'url' => getAdminPanelUrl().'/financial/sales/'. $sale->id .'/refund',
                                                                'tooltip' => trans('admin/main.refund'),
                                                                'btnIcon' => 'fa-times-circle'
                                                            ])
                                                    @endif
                                                @endcan
                                                 @else
                                                      <a href="#"  title="{{ trans('admin/main.invoice') }}"><i class="fa fa-print" aria-hidden="true"></i></a>

                                                    @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $sales->links('pagination::bootstrap-4') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

