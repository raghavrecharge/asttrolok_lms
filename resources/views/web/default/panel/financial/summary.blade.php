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

                                    <th class="text-center">Type</th>
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>

                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th width="120">{{ trans('admin/main.actions') }}</th>
                                </tr>
                                </thead>

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
