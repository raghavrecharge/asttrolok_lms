@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('financial.sales_statistics') }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/48.svg" width="64" height="64" alt="">
                        <strong class="font-30 font-weight-bold mt-5 text-dark-blue">{{ $studentCount }}</strong>
                        <span class="font-16 font-weight-500 text-gray">{{ trans('quiz.students') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/webinars.svg" width="64" height="64" alt="">
                        <strong class="font-30 font-weight-bold mt-5 text-dark-blue">{{ $webinarCount }}</strong>
                        <span class="font-16 font-weight-500 text-gray">{{ trans('panel.content_sales') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/sales.svg" width="64" height="64" alt="">
                        <strong class="font-30 font-weight-bold mt-5 text-dark-blue">{{ $meetingCount }}</strong>
                        <span class="font-16 font-weight-500 text-gray">{{ trans('panel.appointment_sales') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/download-sales.svg" width="64" height="64" alt="">
                        <strong class="font-30 font-weight-bold mt-5 text-dark-blue">{{ handlePrice($totalSales) }}</strong>
                        <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_sales') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="mt-25">
        <h2 class="section-title">{{ trans('financial.sales_report') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="" method="get">
                <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">
                    
                    {{-- From --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.from') }}
                        </label>
                        <div style="position:relative;width:130px;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:34px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="from" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:42px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('from',null) }}"/>
                        </div>
                    </div>

                    {{-- To --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.to') }}
                        </label>
                        <div style="position:relative;width:130px;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:34px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="to" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:42px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('to',null) }}"/>
                        </div>
                    </div>

                    {{-- Webinar --}}
                    <div style="flex:1 1 150px; min-width: 150px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="book" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('webinars.webinar') }}
                        </label>
                        <div style="position:relative;">
                            <select name="webinar_id" class="form-control select2">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($userWebinars as $webinar)
                                    <option value="{{ $webinar->id }}" @if(request()->get('webinar_id',null) == $webinar->id) selected @endif>{{ $webinar->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Student --}}
                    <div style="flex:1 1 150px; min-width: 150px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('quiz.student') }}
                        </label>
                        <div style="position:relative;">
                            <select name="student_id" class="form-control select2">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" @if(request()->get('student_id',null) == $student->id) selected @endif>{{ $student->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Type --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="layers" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.type') }}
                        </label>
                        <div style="position:relative;width:120px;">
                            <select name="type" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="all" @if(request()->get('type',null) == 'all') selected @endif>{{ trans('public.all') }}</option>
                                <option value="webinar" @if(request()->get('type',null) == 'webinar') selected @endif>{{ trans('webinars.webinar') }}</option>
                                <option value="meeting" @if(request()->get('type',null) == 'meeting') selected @endif>{{ trans('public.meeting') }}</option>
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div style="flex:0 0 auto;">
                    <button type="submit" style="height:40px;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                        <i data-feather="search" width="13" height="13"></i>
                        {{ trans('public.show_results') }}
                    </button>
                </div>

                </div>
            </form>
        </div>
    </section>

    @if(!empty($sales) and !$sales->isEmpty())
        <section class="mt-35">
            <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
                <h2 class="section-title">{{ trans('financial.sales_history') }}</h2>
            </div>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('quiz.student') }}</th>
                                    <th class="text-left">{{ trans('product.content') }}</th>
                                    <th class="text-center">{{ trans('public.price') }}</th>
                                    <th class="text-center">{{ trans('public.discount') }}</th>
                                    <th class="text-center">{{ trans('financial.total_amount') }}</th>
                                    <th class="text-center">{{ trans('financial.income') }}</th>
                                    <th class="text-center">{{ trans('public.type') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($sales as $sale)
                                    <tr>
                                        <td class="text-left">
                                            @if(!empty($sale->buyer))
                                                <div class="user-inline-avatar d-flex align-items-center">
                                                    <div class="avatar bg-gray200">
                                                        <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $sale->buyer->getAvatar() }}" class="img-cover" alt="">
                                                    </div>
                                                    <div class=" ml-5">
                                                        <span class="d-block">{{ $sale->buyer->full_name }}</span>
                                                        <span class="mt-5 font-12 text-gray d-block">{{ $sale->buyer->email }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-danger">{{ trans('update.deleted_user') }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="text-left">
                                                @php
                                                    $content = trans('update.deleted_item');
                                                    $contentId = null;

                                                    if(!empty($sale->webinar)) {
                                                        $content = $sale->webinar->title;
                                                        $contentId =$sale->webinar->id;
                                                    } elseif(!empty($sale->bundle)) {
                                                        $content = $sale->bundle->title;
                                                        $contentId =$sale->bundle->id;
                                                    } elseif(!empty($sale->productOrder) and !empty($sale->productOrder->product)) {
                                                        $content = $sale->productOrder->product->title;
                                                        $contentId =$sale->productOrder->product->id;
                                                    } elseif(!empty($sale->registrationPackage)) {
                                                        $content = $sale->registrationPackage->title;
                                                        $contentId =$sale->registrationPackage->id;
                                                    } elseif(!empty($sale->subscribe)) {
                                                        $content = $sale->subscribe->title;
                                                        $contentId =$sale->subscribe->id;
                                                    } elseif(!empty($sale->promotion)) {
                                                        $content = $sale->promotion->title;
                                                        $contentId =$sale->promotion->id;
                                                    } elseif (!empty($sale->meeting_id)) {
                                                        $content = trans('meeting.reservation_appointment');
                                                    }
                                                @endphp

                                                <span class="d-block">{{ $content }}</span>

                                                @if(!empty($contentId))
                                                    <span class="d-block font-12 text-gray">Id: {{ $contentId }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($sale->payment_method == \App\Models\Sale::$subscribe)
                                                <span class="">{{ trans('financial.subscribe') }}</span>
                                            @else
                                                <span>{{ handlePrice($sale->amount) }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ handlePrice($sale->discount ?? 0) }}</td>
                                        <td class="align-middle">
                                            @if($sale->payment_method == \App\Models\Sale::$subscribe)
                                                <span class="">{{ trans('financial.subscribe') }}</span>
                                            @else
                                                <span>{{ handlePrice($sale->total_amount) }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <span>{{ handlePrice($sale->getIncomeItem()) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            @switch($sale->type)
                                                @case(\App\Models\Sale::$webinar)
                                                @if(!empty($sale->webinar))
                                                    <span class="text-primary">{{ trans('webinars.'.$sale->webinar->type) }}</span>
                                                @else
                                                    <span class="text-danger">{{ trans('update.class') }}</span>
                                                @endif
                                                @break;
                                                @case(\App\Models\Sale::$meeting)
                                                <span class="text-dark-blue">{{ trans('meeting.appointment') }}</span>
                                                @break;
                                                @case(\App\Models\Sale::$subscribe)
                                                <span class="text-danger">{{ trans('financial.subscribe') }}</span>
                                                @break;
                                                @case(\App\Models\Sale::$promotion)
                                                <span class="text-warning">{{ trans('panel.promotion') }}</span>
                                                @break;
                                                @case(\App\Models\Sale::$registrationPackage)
                                                <span class="text-secondary">{{ trans('update.registration_package') }}</span>
                                                @break;
                                                @case(\App\Models\Sale::$bundle)
                                                <span class="text-primary">{{ trans('update.bundle') }}</span>
                                                @break;
                                                @case(\App\Models\Sale::$product)
                                                <span class="text-dark-blue">{{ trans('update.product') }}</span>
                                                @break;
                                            @endswitch
                                        </td>
                                        <td class="align-middle">
                                            <span>{{ dateTimeFormat($sale->created_at, 'j M Y H:i') }}</span>
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
                {{ $sales->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>

        </section>
    @else
        @include(getTemplate() . '.includes.no-result',[
              'file_name' => 'sales.png',
              'title' => trans('financial.sales_no_result'),
              'hint' => nl2br(trans('financial.sales_no_result_hint')),
          ])
    @endif

@endsection

@push('scripts_bottom')
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
@endpush
