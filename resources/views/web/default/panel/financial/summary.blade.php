@extends(getTemplate() .'.panel.layouts.panel_layout')


@section('content')
    @if($accountings->count() > 0)
        <section>
            <h2 class="section-title">{{ trans('financial.financial_documents') }}</h2>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('public.title') }}</th>
                                    <!--<th>{{ trans('public.description') }}</th>-->
                                    <th class="text-center">Type</th>
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    
                                    <!--<th class="text-center">{{ trans('public.creator') }}</th>-->
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th width="120">{{ trans('admin/main.actions') }}</th>
                                </tr>
                                </thead>
                                {{-- tbody>

                                @foreach($accountings as $accounting)
                                    <tr>
                                        <td class="text-left">
                                            <div class="d-flex flex-column">
                                                <div class="font-14 font-weight-500">
                                                    @if($accounting->is_cashback)
                                                        {{ trans('update.cashback') }}
                                                    @elseif(!empty($accounting->webinar_id) and !empty($accounting->webinar))
                                                        {{ $accounting->webinar->title }}
                                                    @elseif(!empty($accounting->bundle_id) and !empty($accounting->bundle))
                                                        {{ $accounting->bundle->title }}
                                                    @elseif(!empty($accounting->product_id) and !empty($accounting->product))
                                                        {{ $accounting->product->title }}
                                                    @elseif(!empty($accounting->meeting_time_id))
                                                        {{ trans('meeting.reservation_appointment') }}
                                                    @elseif(!empty($accounting->subscribe_id) and !empty($accounting->subscribe))
                                                        {{ $accounting->subscribe->title }}
                                                    @elseif(!empty($accounting->promotion_id) and !empty($accounting->promotion))
                                                        {{ $accounting->promotion->title }}
                                                    @elseif(!empty($accounting->registration_package_id) and !empty($accounting->registrationPackage))
                                                        {{ $accounting->registrationPackage->title }}
                                                    @elseif(!empty($accounting->installment_payment_id))
                                                        {{ trans('update.installment') }}
                                                    @elseif($accounting->store_type == \App\Models\Accounting::$storeManual)
                                                        {{ trans('financial.manual_document') }}
                                                    @elseif($accounting->type == \App\Models\Accounting::$addiction and $accounting->type_account == \App\Models\Accounting::$asset)
                                                        {{ trans('financial.charge_account') }}
                                                    @elseif($accounting->type == \App\Models\Accounting::$deduction and $accounting->type_account == \App\Models\Accounting::$income)
                                                        {{ trans('financial.payout') }}
                                                    @elseif($accounting->is_registration_bonus)
                                                        {{ trans('update.registration_bonus') }}
                                                    @else
                                                        ---
                                                    @endif
                                                </div>

                                                @if(!empty($accounting->gift_id) and !empty($accounting->gift))
                                                    <div class="text-gray font-12">{!! trans('update.a_gift_for_name_on_date',['name' => $accounting->gift->name, 'date' => dateTimeFormat($accounting->gift->date, 'j M Y H:i')]) !!}</div>
                                                @endif

                                                <div class="font-12 text-gray">
                                                    @if(!empty($accounting->webinar_id) and !empty($accounting->webinar))
                                                        #{{ $accounting->webinar->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->webinar->title : '' }}
                                                    @elseif(!empty($accounting->bundle_id) and !empty($accounting->bundle))
                                                        #{{ $accounting->bundle->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->bundle->title : '' }}
                                                    @elseif(!empty($accounting->product_id) and !empty($accounting->product))
                                                        #{{ $accounting->product->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->product->title : '' }}
                                                    @elseif(!empty($accounting->meeting_time_id) and !empty($accounting->meetingTime))
                                                        {{ $accounting->meetingTime->meeting->creator->full_name }}
                                                    @elseif(!empty($accounting->subscribe_id) and !empty($accounting->subscribe))
                                                        {{ $accounting->subscribe->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->subscribe->title : '' }}
                                                    @elseif(!empty($accounting->promotion_id) and !empty($accounting->promotion))
                                                        {{ $accounting->promotion->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->promotion->title : '' }}
                                                    @elseif(!empty($accounting->registration_package_id) and !empty($accounting->registrationPackage))
                                                        {{ $accounting->registrationPackage->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->registrationPackage->title : '' }}
                                                    @elseif(!empty($accounting->installment_payment_id))
                                                        @php
                                                            $installmentItemTitle = "--";
                                                            $installmentOrderPayment = $accounting->installmentOrderPayment;

                                                            if (!empty($installmentOrderPayment)) {
                                                                $installmentOrder = $installmentOrderPayment->installmentOrder;
                                                                if (!empty($installmentOrder)) {
                                                                    $installmentItem = $installmentOrder->getItem();
                                                                    if (!empty($installmentItem)) {
                                                                        $installmentItemTitle = $installmentItem->title;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $installmentItemTitle }}
                                                    @else
                                                        ---
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left ">
                                            <span class="font-weight-500 text-gray">{{ $accounting->description }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @switch($accounting->type)
                                                @case(\App\Models\Accounting::$addiction)
                                                    <span class="font-16 font-weight-bold text-primary">+{{ handlePrice($accounting->amount, false) }}</span>
                                                    @break;
                                                @case(\App\Models\Accounting::$deduction)
                                                    <span class="font-16 font-weight-bold text-danger">-{{ handlePrice($accounting->amount, false) }}</span>
                                                    @break;
                                            @endswitch
                                        </td>
                                        <td class="text-center align-middle">{{ trans('public.'.$accounting->store_type) }}</td>
                                        <td class="text-center align-middle">
                                            <span>{{ dateTimeFormat($accounting->created_at, 'j M Y') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody> --}}
                                
                                
                                <tbody>

                                @foreach($amount_paid as $amount_paid1)
                                    <tr>
                                         <td><span class="">#{{ $amount_paid1[3] }}</span></td>
                                        <td class="text-left align-middle">
                                            <span class="">{{ $amount_paid1[2] }}</span>
                                        </td>
                                        <td>
                                             @if($amount_paid1[5] == 'part')
                                               
                                              <span class="">Installment payment by part</span>
                                               @endif
                                                @if($amount_paid1[5] == 'course')
                                                @if($amount_paid1[6] == 'installment_payment')
                                                 <span class="">Installment payment</span>
                                                @else
                                                <span class="">Course</span>
                                                 @endif
                                               @endif
                                                @if($amount_paid1[5] == 'meeting')
                                               <span class="">Meeting</span>
                                               
                                               @endif
                                                @if($amount_paid1[5] == 'subscription')
                                               <span class="">Subscription</span>
                                               
                                               @endif
                                                @if($amount_paid1[5] == 'bundle')
                                               <span class="">Bundle</span>
                                               
                                               @endif
                                                @if($amount_paid1[5] == 'product')
                                               <span class="">Product</span>
                                               
                                               @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            
                                                    <span class="font-16 font-weight-bold text-primary">{{ handlePrice($amount_paid1[0], false) }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span>{{ dateTimeFormat($amount_paid1[1], 'j M Y') }}</span>
                                        </td>
                                        
                                         <td class="text-left ">
                                               @if($amount_paid1[5] == 'part')
                                                <a href="/panel/webinars/{{ $amount_paid1[4] }}/part/{{ $amount_paid1[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice-{{ $amount_paid1[2] }}"></a>
                                              
                                               @endif
                                                @if($amount_paid1[5] == 'course')
                                               
                                               <a href="/panel/webinars/{{ $amount_paid1[4] }}/sale/{{ $amount_paid1[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice-{{ $amount_paid1[2] }}"></a>
                                               
                                               @endif
                                                @if($amount_paid1[5] == 'meeting')
                                                <a href="/panel/webinars/{{ $amount_paid1[4] }}/meeting/{{ $amount_paid1[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice-{{ $amount_paid1[2] }}"></a>
                                              
                                               
                                               @endif
                                               @if($amount_paid1[5] == 'subscription')
                                                <a href="/panel/webinars/{{ $amount_paid1[4] }}/subscription/{{ $amount_paid1[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice-{{ $amount_paid1[2] }}"></a>
                                               @endif
                                               @if($amount_paid1[5] == 'bundle')
                                                <a href="/panel/webinars/{{ $amount_paid1[4] }}/bundle/{{ $amount_paid1[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy"  src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice-{{ $amount_paid1[2] }}"></a>
                                               @endif
                                                    <!--@if(!empty($sale->webinar_id))-->
                                                    <!--    <a href="{{ getAdminPanelUrl() }}/panel/webinars/{{ $sale->id }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><i class="fa fa-print" aria-hidden="true"></i></a>-->
                                                    <!--@endif-->
                                               

                                               
                                            </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    @else

        @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'financial.png',
            'title' => trans('financial.financial_summary_no_result'),
            'hint' => nl2br(trans('financial.financial_summary_no_result_hint')),
        ])
    @endif
    <div class="my-30">
        {{ $accountings->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection
