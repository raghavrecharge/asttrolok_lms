
@extends(getTemplate() .'.panel.layouts.panel_layout')
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/chartjs/chart.min.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/apexcharts/apexcharts.css"/>
    
     <!--<link rel="stylesheet" href="https://themewagon.github.io/stisla-1/assets/modules/bootstrap/css/bootstrap.min.css">-->
      <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl.carousel.min.css">
  <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl.theme.default.min.css">
   <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush
@section('content')

        <style>
/*        .owl-stage, .idebar-ads {*/
/*  flex: 1 1 auto;*/
/*}*/


.sidebar-ads {
  height:317px;
  text-align: center;
}
        .message {
    color: #000;
    clear: both;
    line-height: 18px;
    font-size: 15px;
    padding: 8px;
    position: relative;
    margin: 8px 0;
    max-width: 85%;
    word-wrap: break-word;
    
}
.metadata {
    display: inline-block;
    float: right;
    padding: 0 0 0 7px;
    position: relative;
    bottom: -4px;
}
.metadata .time {
    color: rgba(0, 0, 0, .45);
    font-size: 11px;
    display: inline-block;
}
.rdiv{
    justify-content: end;
    display: flex;

}
.ldiv{
    justify-content: start;
    display: flex;

}
.crntdate{
    justify-content: center;
    display: flex;
}
        .sent{
            
            background: #e1ffc7;
    border-radius: 5px 0px 5px 5px;
    /*float: right;*/
        }
        .message.received {
    background: #fff;
    border-radius: 0px 5px 5px 5px;
        /*float: left;*/
        }
        .finace::-webkit-scrollbar {
    width: 5px;
    background-color: lightgrey;
    box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
}
.no-finace::-webkit-scrollbar {
    width: 5px;
    background-color: lightgrey;
    box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
}
.finace::-webkit-scrollbar-thumb {
  background: linear-gradient(to right, #9effc1, lightgrey);
}
.no-finace::-webkit-scrollbar-thumb {
  background: linear-gradient(to right, #9effc1, lightgrey);
}
/*#slider2 .owl-item{*/
/*    width: 525px !important;*/
/*}*/
        .finace{
            min-height: 375px;
    max-height: 375px;
    overflow-x: hidden;
    overflow-y: scroll;
        }
        .no-finace{
           min-height: 400px;
    max-height: 400px;
    overflow-x: hidden;
    overflow-y: scroll; 
        }
        .dashboard-banner{
            width: 100%;
    height: 200px;
    background-color: #EBF3FE !important;
    box-shadow: 0 8px 23px 0 rgba(62, 73, 84, 0.15);
    transition: all 0.5s ease;
    border-radius: 0.625rem !important;
        }
        .imgs{
            width: 100%;
        }
        .image-banner{
                min-height: 200px;
    display: flex;
    align-items: flex-end;
        }
        .userprofile{
                padding: 30px;
        }
         .userprofile1{
                padding-left: 30px;
                flex-direction: column;
        }
        .userprofile .stat-icon {
    width: 50px !important;
    min-width: 50px !important;
    height: 50px !important;
    border-radius: 50%;
    background-color: #EBF3FE !important;
}
.userprofile img{
    border-radius: 50px;
}
.justify-content{
    justify-content: space-evenly;
}
.dashboard-stats span{
    /*font-size:15px;*/
}
.dashboard-stats .stat-icon {
    width: 75px !important;
    min-width: 75px !important;
    height: 75px !important;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
@media (max-width: 991px) {
    .image-banner{
        display:none;
    }
    
    
}
.conversations-list {
    height: 420px;
    overflow: auto;
}
.adds1{
height: 100%;
    border-radius: 0.9375rem !important;
    box-sizing: border-box;
}
.badge {
    font-size: 9.75px !important;
}
        </style>
            
           
    <section class="dashboard ">
        <div class="row">
            <div class="col-12 col-lg-9  d-none">
              <div class="dashboard-banner d-none">
                  <div class="row">
                  <div class="col-12 col-lg-7">
<div class="userprofile d-flex align-items-center">
                    <div class="stat-icon">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/default/avatar-1.png" alt="" class="imgs" >
                    </div>
                    <div class="ml-15">
                      
                        <span class="font-16 text-gray dash-box font-weight-500">Welcome {{ $authUser->full_name }}</span>
                        
                    </div>
                </div>
                <div class="d-flex justify-content">
                    <div>
                <div class="userprofile1 d-flex align-items-center">
                    <div class="">
                       <span class="font-30 text-secondary">{{ (!empty($openSupportsCount1) ? $openSupportsCount1 : 0) }}</span>
                    </div>
                    <a href="panel/support/tickets" class="font-14 font-weight-bold text-dark-blue"><span class="px-5  text-gray dash-box font-weight-500"  style="font-size: 12.5px;">Open Tickets</span></a>
             
                 </div>
                    </div>
                  <div>
                <div class="userprofile1 d-flex align-items-center">
                    <div class="">
                       <span class="font-30 text-secondary">{{ (!empty($closeSupportsCount1) ? $closeSupportsCount1 : 0) }}</span>
                    </div>
                      <a href="panel/support/tickets" class="font-14 font-weight-bold text-dark-blue"><span class=" text-gray dash-box font-weight-500" style="font-size: 12.5px;">Closed Tickets</span></a>
                 </div>
                
               </div>
                <div>
                <div class="userprofile1 d-flex align-items-center">
                    <div class="">
                       <span class="font-30 text-secondary">{{ (!empty(count($supports)) ? count($supports) : 0) }}</span>
                    </div>
                       <a href="panel/support" class="font-14 font-weight-bold text-dark-blue"><span class="text-gray dash-box font-weight-500"  style="font-size: 12.5px;">Courses Support</span></a>
                 </div>
                
               </div>
                </div>
                      </div>
                  <div class="col-12 col-lg-5">
                      <div class="image-banner"><img src="{{ config('app.js_css_url') }}/store/1/dashboards.png" alt="" class="imgs"></div>
                
                </div>
                </div>
            </div>
            </div>
         
            <div class="col-12 col-lg-3 homehide  d-none">
                <div class="dashboard-stats rounded-sm panel-shadow p-10 p-md-5 d-flex align-items-center">
                    <div class="stat-icon ">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/36.svg" alt="">
                    </div>
                    <div class="d-flex flex-column ml-1">
                        <span class="font-30 text-secondary">{{ handlePrice($authUser->getAccountingBalance()) }}</span>
                        <span class="font-14 text-gray dash-box font-weight-500"  style="min-height: 30px;">Wallet Balance</span>
                          <a href="/panel/financial/account" class="font-14 font-weight-bold text-dark-blue">{{ trans('financial.charge_account') }}</a>
                    </div>
                    <div class="border-gray300 align-items-center  justify-content-center ">
                       
                            
                       
                    </div>
                </div>

               <a href="@if($authUser->isUser()) /panel/financial/installments @else /panel/webinars/comments @endif" class="dashboard-stats rounded-sm panel-shadow p-10 p-md-5 d-flex align-items-center mt-15 mt-md-20">
                    <div class="stat-icon installments">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/129.png" alt="">
                    </div>
                    <div class="d-flex flex-column ml-1">
                        <span class="font-30 text-secondary">{{ !empty($openInstallmentsCount) ? $openInstallmentsCount : 0 }}</span>
                        <span class="font-14 text-gray dash-box font-weight-500" style="min-height: 42px;">{{ trans('update.open_installments') }}</span>
                        
                    </div>
                    
                </a>
            </div>
            </div>
             <div class="row">
            <div class="col-12 col-lg-8 mt-35">
                       <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('panel.my_purchases') }}</h2>
        </div>
        
        @if((!empty($sales) and !$sales->isEmpty()) || (!empty($orders) and !$orders->isEmpty()))
        <div class="row mt-30">
              <div class="owl-carousel owl-theme slider" id="slider1">
        @if(!empty($sales) and !$sales->isEmpty() )
         
            @foreach($sales as $sale)
            <div>
                @php
                    $item = !empty($sale->webinar) ? $sale->webinar : $sale->bundle;

                    $lastSession = !empty($sale->webinar) ? $sale->webinar->lastSession() : null;
                    $nextSession = !empty($sale->webinar) ? $sale->webinar->nextSession() : null;
                    $isProgressing = false;

                    if(!empty($sale->webinar) and $sale->webinar->start_date <= time() and !empty($lastSession) and $lastSession->date > time()) {
                        $isProgressing = true;
                    }
                @endphp

                @if(!empty($item))
                   
                        <div class="col-12">
                            <div class="webinar-card webinar-list">
                                 <div class="image-box" style="height:150px !important;     min-height: 150px !important;">
                                    <img src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" class="img-cover" alt=""></div>

                                <div class="webinar-card-body w-100 d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <a href="{{ $item->getUrl() }}">
                                            <h3 class="webinar-title font-weight-bold font-16 text-dark-blue" style="height: 75px;">
                                                {{ $item->title }}

                                                @if(!empty($item->access_days))
                                                    @if(!$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <span class="badge badge-outlined-danger ">{{ trans('update.access_days_expired') }}</span>
                                                    @else
                                                        <span class="badge badge-outlined-warning ">{{ trans('update.expired_on_date',['date' => dateTimeFormat($item->getExpiredAccessDays($sale->created_at, $sale->gift_id),'j M Y')]) }}</span>
                                                    @endif
                                                @endif

                                                @if($sale->payment_method == \App\Models\Sale::$subscribe and $sale->checkExpiredPurchaseWithSubscribe($sale->buyer_id, $item->id, !empty($sale->webinar) ? 'webinar_id' : 'bundle_id'))
                                                    <span class="badge badge-outlined-danger ">{{ trans('update.subscribe_expired') }}</span>
                                                @endif

                                                @if(!empty($sale->webinar))
                                                    <span class="badge badge-dark  status-badge-dark">{{ trans(''.$item->lang) }}</span>
                                                @endif

                                                @if(!empty($sale->gift_id))
                                                    <span class="badge badge-primary ">{{ trans('update.gift') }}</span>
                                                @endif
                                           

                                        
                                            <!--@include(getTemplate() . '.includes.webinar.rate',['rate' => $item->getRate()])-->

                                    <div class="webinar-price-box mt-15">
                                        @if($item->price > 0)
                                            @if($item->bestTicket() < $item->price)
                                                <span class="real">{{ handlePrice($item->bestTicket(), true, true, false, null, true) }}</span>
                                                <span class="off ml-10">{{ handlePrice($item->price, true, true, false, null, true) }}</span>
                                            @else
                                                <span class="real">{{ handlePrice($item->price, true, true, false, null, true) }}</span>
                                            @endif
                                        @else
                                            <span class="real">{{ trans('public.free') }}</span>
                                        @endif
                                    </div>
                                     </h3>
                                        </a>
                                        <div class="btn-group dropdown table-actions">
                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i data-feather="more-vertical" height="20"></i>
                                            </button>

                                            <div class="dropdown-menu">
                                                @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                                    <a href="/panel/webinars/{{ $item->id }}/sale/{{ $sale->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.invoice') }}</a>
                                                @else
                                                    @if(!empty($item->access_days) and !$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <a href="{{ $item->getUrl() }}" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.enroll_on_course') }}</a>
                                                    @elseif(!empty($sale->webinar))
                                                        <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block">{{ trans('update.learning_page') }}</a>

                                                        @if(!empty($item->start_date) and ($item->start_date > time() or ($item->isProgressing() and !empty($nextSession))))
                                                            <button type="button" data-webinar-id="{{ $item->id }}" class="join-purchase-webinar webinar-actions btn-transparent d-block mt-10">{{ trans('footer.join') }}</button>
                                                        @endif

                                                        <!--@if(!empty($item->downloadable) or (!empty($item->files) and count($item->files)))-->
                                                        <!--    <a href="{{ $item->getUrl() }}?tab=content" target="_blank" class="webinar-actions d-block mt-10">{{ trans('home.download') }}</a>-->
                                                        <!--@endif-->

                                                        @if($item->price > 0)
                                                            <a href="/panel/webinars/{{ $item->id }}/sale/{{ $sale->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.invoice') }}</a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $item->getUrl() }}?tab=reviews" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.feedback') }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                

                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">

                                        <!--@if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('update.gift_status') }}:</span>-->

                                        <!--        @if(!empty($sale->gift_date) and $sale->gift_date > time())-->
                                        <!--            <span class="stat-value text-warning">{{ trans('public.pending') }}</span>-->
                                        <!--        @else-->
                                        <!--            <span class="stat-value text-primary">{{ trans('update.sent') }}</span>-->
                                        <!--        @endif-->
                                        <!--    </div>-->
                                        <!--@else-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('public.item_id') }}:</span>-->
                                        <!--        <span class="stat-value">{{ $item->id }}</span>-->
                                        <!--    </div>-->
                                        <!--@endif-->

                                        <!--@if(!empty($sale->gift_id))-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('update.gift_receive_date') }}:</span>-->
                                        <!--        <span class="stat-value">{{ (!empty($sale->gift_date)) ? dateTimeFormat($sale->gift_date, 'j M Y H:i') : trans('update.instantly') }}</span>-->
                                        <!--    </div>-->
                                        <!--@else-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('public.category') }}:</span>-->
                                        <!--        <span class="stat-value">{{ !empty($item->category_id) ? $item->category->title : '' }}</span>-->
                                        <!--    </div>-->
                                        <!--@endif-->

                                        @if(!empty($sale->webinar) and $item->type == 'webinar')
                                            <!--@if($item->isProgressing() and !empty($nextSession))-->
                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('webinars.next_session_duration') }}:</span>-->
                                            <!--        <span class="stat-value">{{ convertMinutesToHourAndMinute($nextSession->duration) }} Hrs</span>-->
                                            <!--    </div>-->

                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('webinars.next_session_start_date') }}:</span>-->
                                            <!--        <span class="stat-value">{{ dateTimeFormat($nextSession->date,'j M Y') }}</span>-->
                                            <!--    </div>-->
                                            <!--@else-->
                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('public.duration') }}:</span>-->
                                            <!--        <span class="stat-value">{{ convertMinutesToHourAndMinute($item->duration) }} Hrs</span>-->
                                            <!--    </div>-->

                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('public.start_date') }}:</span>-->
                                            <!--        <span class="stat-value">{{ dateTimeFormat($item->start_date,'j M Y') }}</span>-->
                                            <!--    </div>-->
                                            <!--@endif-->
                                        @elseif(!empty($sale->bundle))
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                <span class="stat-value">{{ convertMinutesToHourAndMinute($item->getBundleDuration()) }} Hrs</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('update.receipt') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_recipient }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('public.instructor') }}:</span>
                                                <span class="stat-value">{{ $item->teacher->full_name }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id != $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_sender') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_sender }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                                <span class="stat-value">{{ dateTimeFormat($sale->created_at,'j M Y') }}</span>
                                            </div>
                                        @endif
                                        <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="btn btn-sm btn-primary my-10 w-100 mt-2 ">{{ trans('update.learning_page') }}</a>


                                    </div>
                                </div>
                            </div>
                        </div>
                   
                @endif
                </div>
            @endforeach
            <!--</div>-->
            <!--</div>-->
            @endif
            @if(!empty($orders) and !$orders->isEmpty())
            
            <!--<div class="row mt-30">-->
            <!--  <div class="owl-carousel owl-theme slider" id="slider1">-->
                 
              @foreach($orders as $sale)
            <div>
                @php
                
                    $item = !empty($sale->webinar) ? $sale->webinar : $sale->bundle;
           
                    $lastSession = !empty($sale->webinar) ? $sale->webinar->lastSession() : null;
                    $nextSession = !empty($sale->webinar) ? $sale->webinar->nextSession() : null;
                    $isProgressing = false;

                    if(!empty($sale->webinar) and $sale->webinar->start_date <= time() and !empty($lastSession) and $lastSession->date > time()) {
                        $isProgressing = true;
                    }
                     
                @endphp

                @if(!empty($item))
                   
                        <div class="col-12">
                            <div class="webinar-card webinar-list">
                                 <div class="image-box" style="height:150px !important;     min-height: 150px !important;">
                                    <img src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" class="img-cover" alt=""></div>

                                <div class="webinar-card-body w-100 d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between">
                                       <a href="{{ $item->getUrl() }}">
                                            <h3 class="webinar-title font-weight-bold font-16 text-dark-blue" style="height: 75px;">
                                                 {{ $item->title }} 
                                                <!--<div  style="display: flex; justify-content: space-between;align-items: center;">-->
                                                 @if(!empty($item->access_days))
                                                    @if(!$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <span class="badge badge-outlined-danger ">{{ trans('update.access_days_expired') }}</span>
                                                    @else
                                                        <span class="badge badge-outlined-warning ">{{ trans('update.expired_on_date',['date' => dateTimeFormat($item->getExpiredAccessDays($sale->created_at, $sale->gift_id),'j M Y')]) }}</span>
                                                    @endif
                                                @endif

                                                @if($sale->payment_method == \App\Models\Sale::$subscribe and $sale->checkExpiredPurchaseWithSubscribe($sale->buyer_id, $item->id, !empty($sale->webinar) ? 'webinar_id' : 'bundle_id'))
                                                    <span class="badge badge-outlined-danger ">{{ trans('update.subscribe_expired') }}</span>
                                                @endif

                                                @if(!empty($sale->webinar))
                                                    <span class="badge badge-dark  status-badge-dark">{{ trans(''.$item->lang) }}</span>
                                                @endif

                                                @if(!empty($sale->gift_id))
                                                    <span class="badge badge-primary ">{{ trans('update.gift') }}</span>
                                                @endif


                                                <!--@if(!empty($sale->gift_id))-->
                                                <!--    <span class="badge badge-primary ">{{ trans('update.gift') }}</span>-->
                                                <!--@endif-->
                                                <!--</div>-->
                                                <style>
                                                    .stars-card{ justify-content: space-between;}
                                                </style>
                                                              <!--@include(getTemplate() . '.includes.webinar.rate',['rate' => $item->getRate()])-->

                                    <div class="webinar-price-box mt-5" style="display: flex; justify-content: space-between;align-items: center;">
                                        @if($item->price > 0)
                                            @if($item->bestTicket() < $item->price)
                                                <span class="real ">{{ handlePrice($item->bestTicket(), true, true, false, null, true) }}</span>
                                                <span class="off ml-10">{{ handlePrice($item->price, true, true, false, null, true) }}</span>
                                            @else
                                                <span class="real ">{{ handlePrice($item->price, true, true, false, null, true) }}</span>
                                            @endif
                                        @else
                                            <span class="real">{{ trans('public.free') }}</span>
                                        @endif
                                        @php
                                        $isOverdue='0';
                                        
                                        @endphp
                                          @foreach($installment->steps as $step)
                                @php
                                    $stepPayment = $payments->where('step_id', $step->id)->where('status', 'paying')->first();
                            if(!($payments->where('step_id', $step->id)->where('status', 'paid')->first())){
                                    $dueAt = ($step->deadline * 86400) + $sale->created_at;
                                    
                                    $isOverdue = ($dueAt < time() and empty($stepPayment));
                                  }
                                  
                                @endphp
                                   @endforeach
                                    @if($isOverdue != '0')
                                   
                                      <a href="/panel/financial/installments/{{ $sale->id }}/details" target="_blank"  class="webinar-actions d-block mt-10 font-weight-normal"><span class="badge badge-danger ml-10" style="padding-top: 2px;  padding-bottom: 2px;">Unpaid (Overdue)</span></a>
                                       
                                    @endif
    
                      
                   
           
                                    </div>
                                            </h3></a>
                                            
                                        

                                        <div class="btn-group dropdown table-actions">
                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i data-feather="more-vertical" height="20"></i>
                                            </button>

                                            <div class="dropdown-menu">
                                                @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                                     <a href="/panel/financial/installments/{{ $sale->id }}/details" target="_blank" class="webinar-actions d-block mt-10">View Details</a>
                                                @else
                                                    @if(!empty($item->access_days) and !$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <a href="{{ $item->getUrl() }}" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.enroll_on_course') }}</a>
                                                    @elseif(!empty($sale->webinar))
                                                        <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block">{{ trans('update.learning_page') }}</a>

                                                        @if(!empty($item->start_date) and ($item->start_date > time() or ($item->isProgressing() and !empty($nextSession))))
                                                            <button type="button" data-webinar-id="{{ $item->id }}" class="join-purchase-webinar webinar-actions btn-transparent d-block mt-10">{{ trans('footer.join') }}</button>
                                                        @endif

                                                       

                                                        @if($item->price > 0)
                                                            <a href="/panel/financial/installments/{{ $sale->id }}/details" target="_blank" class="webinar-actions d-block mt-10">View Details</a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $item->getUrl() }}?tab=reviews" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.feedback') }}</a>
                                                @endif
                                     
                                            </div>
                                        </div>
                                        
                                    </div>

                                 

                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">

                                        <!--@if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('update.gift_status') }}:</span>-->

                                        <!--        @if(!empty($sale->gift_date) and $sale->gift_date > time())-->
                                        <!--            <span class="stat-value text-warning">{{ trans('public.pending') }}</span>-->
                                        <!--        @else-->
                                        <!--            <span class="stat-value text-primary">{{ trans('update.sent') }}</span>-->
                                        <!--        @endif-->
                                        <!--    </div>-->
                                        <!--@else-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('public.item_id') }}:</span>-->
                                        <!--        <span class="stat-value">{{ $item->id }}</span>-->
                                        <!--    </div>-->
                                        <!--@endif-->

                                        <!--@if(!empty($sale->gift_id))-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('update.gift_receive_date') }}:</span>-->
                                        <!--        <span class="stat-value">{{ (!empty($sale->gift_date)) ? dateTimeFormat($sale->gift_date, 'j M Y H:i') : trans('update.instantly') }}</span>-->
                                        <!--    </div>-->
                                        <!--@else-->
                                        <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                        <!--        <span class="stat-title">{{ trans('public.category') }}:</span>-->
                                        <!--        <span class="stat-value">{{ !empty($item->category_id) ? $item->category->title : '' }}</span>-->
                                        <!--    </div>-->
                                        <!--@endif-->

                                        @if(!empty($sale->webinar) and $item->type == 'webinar')
                                            <!--@if($item->isProgressing() and !empty($nextSession))-->
                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('webinars.next_session_duration') }}:</span>-->
                                            <!--        <span class="stat-value">{{ convertMinutesToHourAndMinute($nextSession->duration) }} Hrs</span>-->
                                            <!--    </div>-->

                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('webinars.next_session_start_date') }}:</span>-->
                                            <!--        <span class="stat-value">{{ dateTimeFormat($nextSession->date,'j M Y') }}</span>-->
                                            <!--    </div>-->
                                            <!--@else-->
                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('public.duration') }}:</span>-->
                                            <!--        <span class="stat-value">{{ convertMinutesToHourAndMinute($item->duration) }} Hrs</span>-->
                                            <!--    </div>-->

                                            <!--    <div class="d-flex align-items-start flex-column mt-20 mr-15">-->
                                            <!--        <span class="stat-title">{{ trans('public.start_date') }}:</span>-->
                                            <!--        <span class="stat-value">{{ dateTimeFormat($item->start_date,'j M Y') }}</span>-->
                                            <!--    </div>-->
                                            <!--@endif-->
                                        @elseif(!empty($sale->bundle))
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                <span class="stat-value">{{ convertMinutesToHourAndMinute($item->getBundleDuration()) }} Hrs</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('update.receipt') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_recipient }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('public.instructor') }}:</span>
                                                <span class="stat-value">{{ $item->teacher->full_name }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id != $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_sender') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_sender }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-10 mr-15">
                                                <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                                <span class="stat-value">{{ dateTimeFormat($sale->created_at,'j M Y') }}</span>
                                            </div>
                                        @endif
                                        <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="btn btn-sm btn-primary my-10 w-100 mt-2 ">{{ trans('update.learning_page') }}</a>


                                    </div>
                                </div>
                            </div>
                        </div>
                   
                @endif
                </div>
              
            @endforeach
             <!--</div>-->
             <!--</div>-->
            @endif
           </div>
             </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'student.png',
            'title' => trans('panel.no_result_purchases') ,
            'hint' => trans('panel.no_result_purchases_hint') ,
            'btn' => ['url' => '/classes?sort=newest','text' => trans('panel.start_learning')]
        ])
         
        @endif
            <!--<div class="card-body">-->
            <!--        <div class="owl-carousel owl-theme slider" id="slider1">-->
            <!--          <div><img alt="image" src="{{ config('app.js_css_url') }}/store/1/dashboards.png"></div>-->
            <!--          <div><img alt="image" src="{{ config('app.js_css_url') }}/store/1/dashboards.png"></div>-->
            <!--          <div><img alt="image" src="{{ config('app.js_css_url') }}/store/1/dashboards.png"></div>-->
            <!--          <div><img alt="image" src="{{ config('app.js_css_url') }}/store/1/dashboards.png"></div>-->
            <!--        </div>-->
            <!--      </div>-->
                </div>
             <!--   <div class="col-lg-4">-->
             <!--       <div class="rounded-lg sidebar-ads mt-40">-->
             <!--   <a href="">-->
             <!--       <img src="{{ config('app.js_css_url') }}/store\1\Home\Side-Banner.jpg" class="shadow-sm  shadow-sm mt-40 adds1 rounded-lg" alt="Reserve a meeting - Course page">-->
             <!--       </a>-->
             <!--</div>-->
                    
             <!--   </div>-->
                <div class="col-lg-4">
                    <div class="rounded-lg sidebar-ads mt-40">
                <a href="{{$sidebanner['studentdashboard']['link']}}">
                    <img src="{{ config('app.img_dynamic_url') }}{{$sidebanner['studentdashboard']['image']}}" class="shadow-sm  shadow-sm mt-40 adds1 rounded-lg" alt="Reserve a meeting - Course page">
                    </a>
             </div>
                    
                </div>
            </div>
            
            
            
            <!--##############################################   Support    ####################################################-->
            
    <section class="mt-25 homehide d-none">
        <h2 class="section-title">{{ trans('panel.message_filters') }}</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <form action="/panel/supports/" method="get">
                <div class="row">
                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.from') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class="text-white"></i>
                                        </span>
                                </div>
                                <input type="text" name="from" autocomplete="off" class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('from','') }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.to') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class="text-white"></i>
                                        </span>
                                </div>
                                <input type="text" name="to" autocomplete="off" class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('to','') }}"/>
                            </div>
                        </div>
                    </div>

                    @if(!$authUser->isUser())
                        <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.user_role') }}</label>
                                <select class="form-control" id="userRole" name="role">
                                    <option value="all">{{ trans('public.all_roles') }}</option>
                                    <option value="student" @if(request()->get('role') == 'student') selected @endif >{{ trans('quiz.student') }}</option>
                                    <option value="teacher" @if(request()->get('role') == 'teacher') selected @endif >{{ trans('panel.teacher') }}</option>
                                </select>
                            </div>
                        </div>

                        <div id="studentSelectInput" class="col-12 col-md-4 col-lg-2 @if(request()->get('role') != 'student' and (empty(request()->get('student'))  or request()->get('student') == 'all')) d-none @endif">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.students') }}</label>
                                <select name="student" class="form-control select2" data-placeholder="{{ trans('public.all') }}">
                                    <option value="all">{{ trans('public.all') }}</option>

                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" @if(request()->get('student') == $student->id) selected @endif>{{ $student->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div id="teacherSelectInput" class="col-12 col-md-4 col-lg-2 @if(!$authUser->isUser() and request()->get('role') != 'teacher' and (empty(request()->get('teacher')) or request()->get('teacher') == 'all')) d-none @endif">
                        <div class="form-group">
                            <label class="input-label">{{ trans('home.teachers') }}</label>
                            <select name="teacher" class="form-control select2" data-placeholder="{{ trans('public.all') }}">
                                <option value="all">{{ trans('public.all') }}</option>

                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @if(request()->get('teacher') == $teacher->id) selected @endif>{{ $teacher->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="form-group">
                            <label class="input-label">{{ trans('product.courses') }}</label>
                            <select name="webinar" class="form-control select2" data-placeholder="{{ trans('public.all') }}">
                                <option value="all">{{ trans('public.all') }}</option>

                                @foreach($webinars as $webinar)
                                    <option value="{{ $webinar->id }}" @if(request()->get('webinar') == $webinar->id) selected @endif>{{ $webinar->title }} @if(in_array($webinar->id,$purchasedWebinarsIds)) ({{ trans('panel.purchased') }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="all">{{ trans('public.all') }}</option>
                                <option value="open" @if(request()->get('status') == 'open') selected @endif >{{ trans('public.open') }}</option>
                                <option value="close" @if(request()->get('status') == 'close') selected @endif >{{ trans('public.close') }}</option>
                                <option value="replied" @if(request()->get('status') == 'replied') selected @endif >{{ trans('panel.replied') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-lg-2 d-flex align-items-center justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="mt-40 homehide d-none">
        <h2 class="section-title">{{ trans('panel.messages_history') }}</h2>

        @if(!empty($supports) and !$supports->isEmpty())

            <div class="bg-white shadow rounded-sm py-10 py-lg-25 px-15 px-lg-30 mt-25">
                <div class="row">
                    <div id="conversationsList" class="col-12 col-lg-6 conversations-list">
                        <div class="table-responsive">
                            <table class="table table-md">
                                <tr>
                                    <th class="text-left text-gray font-14 font-weight-500">{{ trans('navbar.contact') }}</th>
                                    <th class="text-left text-gray font-14 font-weight-500">{{ trans('public.title') }}</th>
                                    <th class="text-center text-gray font-14 font-weight-500">{{ trans('public.status') }}</th>
                                </tr>
                                <tbody>

                                @foreach($supports as $support)
                                    <tr class="@if(!empty($selectSupport) and $selectSupport->id == $support->id) selected-row @endif">
                                        <td class="text-left">
                                            <a href="/panel/supports/{{ $support->id }}" class="">
                                                <div class="user-inline-avatar d-flex align-items-center">
                                                    <div class="avatar bg-gray200">
                                                        <img src="{{ config('app.img_dynamic_url') }}{{ (!empty($support->webinar) and $support->webinar->teacher_id != $authUser->id) ? $support->webinar->teacher->getAvatar() : $support->user->getAvatar() }}" class="img-cover" alt="">
                                                    </div>
                                                    <div class="ml-10">
                                                        <span class="d-block font-14 text-dark-blue font-weight-500">{{ (!empty($support->webinar) and $support->webinar->teacher_id != $authUser->id) ? $support->webinar->teacher->full_name : $support->user->full_name }}</span>
                                                        <span class="mt-1 font-12 text-gray d-block">
                                                            {{ (!empty($support->webinar) and $support->webinar->teacher_id != $authUser->id) ? trans('panel.teacher') : ( ($support->user->isUser()) ? trans('quiz.student') : trans('panel.staff')) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>

                                        <td class="text-left">
                                            @if($authUser->isUser())
                                                <a href="/panel/supports/{{ $support->id }}/conversations" class="">
                                                    <span class="font-weight-500 font-14 text-dark-blue d-block">{{ $support->title }}</span>
                                                    <span class="mt-1 font-12 text-gray d-block">{{ truncate((!empty($support->webinar)) ? $support->webinar->title : '', 20) }} | {{ (!empty($support->conversations) and count($support->conversations)) ? dateTimeFormat($support->conversations->first()->created_at,'j M Y | H:i') : dateTimeFormat($support->created_at,'j M Y | H:i') }}</span>
                                                </a>
                                            @else
                                                <a href="/panel/supports/{{ $support->id }}/conversations" class="">
                                                    <span class="font-weight-500 font-14 text-dark-blue d-block">{{ $support->title }}</span>
                                                    <span class="mt-1 font-12 text-gray d-block">{{ (!empty($support->conversations) and count($support->conversations)) ? dateTimeFormat($support->conversations->first()->created_at,'j M Y | H:i') : dateTimeFormat($support->created_at,'j M Y | H:i') }}</span>
                                                </a>
                                            @endif
                                        </td>

                                        <td class="text-center align-middle">
                                            @if($support->status == 'close')
                                                <span class="text-danger font-weight-500 font-14">{{  trans('panel.closed') }}</span>
                                            @elseif($support->status == 'supporter_replied')
                                                <span class="text-primary font-weight-500 font-14">{{  trans('panel.replied') }}</span>
                                            @else
                                                <span class="text-warning font-weight-500 font-14">{{  trans('public.waiting') }}</span>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(!empty($selectSupport))
                        <div class="col-12 col-lg-6 border-left border-gray300">
                            <div class="conversation-box p-15 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="font-weight-500 font-14 text-dark-blue d-block">{{ $selectSupport->title }}</span>
                                    <span class="font-12 mt-1 text-gray d-block">{{ trans('public.created') }}: {{ dateTimeFormat($support->created_at,'j M Y | H:i') }}</span>

                                    @if(!empty($selectSupport->webinar))
                                        <span class="font-12 text-gray d-block mt-5">{{ trans('webinars.webinar') }}: {{ $selectSupport->webinar->title }}</span>
                                    @endif
                                </div>

                                @if($selectSupport->status != 'close')
                                    <a href="/panel/supports/{{ $selectSupport->id }}/close" class="btn btn-primary btn-sm">{{ trans('panel.close_request') }}</a>
                                @endif
                            </div>

                            <div id="conversationsCard" class="pt-15 conversations-card">

                                @if(!empty($selectSupport->conversations) and !$selectSupport->conversations->isEmpty())
                                @php
                                $datess= date("j M Y");
                                @endphp

                                    @foreach($selectSupport->conversations as $conversations)
                                        @php
                                       
                                       $msgdate=dateTimeFormat($conversations->created_at,'j M Y');
                                       @endphp
                                       @if($datess==$msgdate)
                                       
                                       
                                        @else
                                        @php
                                       $datess=$msgdate;
                                       @endphp
                                       <div class="crntdate">
                                       <span >{{ $datess }}</span></div>
                                       @endif
                                       
                                       
                                        
                                            @if(!empty($conversations->supporter))
                                            <div class="ldiv">
                                            <div class="rounded-sm mt-15 panel-shadow border p-15 message received">
                                             @else
                                             <div class="rdiv">
                                             <div class="rounded-sm mt-15 panel-shadow border p-15 message sent">
                                              
                                            @endif
                                            {{ nl2br($conversations->message) }}
                  <!--                          <div class="d-flex align-items-center justify-content-between  border-gray300">-->
                                                <!--<div class="user-inline-avatar d-flex align-items-center">-->
                                                    <!--<div class="avatar bg-gray200">-->
                                                    <!--    <img src="{{ (!empty($conversations->supporter)) ? $conversations->supporter->getAvatar() : $conversations->sender->getAvatar() }}" class="img-cover" alt="">-->
                                                    <!--</div>-->
                                                    <!--<div class="ml-10">-->
                                                    <!--    <span class="d-block text-dark-blue font-14 font-weight-500">{{ (!empty($conversations->supporter)) ? $conversations->supporter->full_name : $conversations->sender->full_name }}</span>-->
                                                        <!--<span class="mt-1 font-12 text-gray d-block">{{ (!empty($conversations->supporter)) ? trans('panel.staff') : $conversations->sender->role_name }}</span>-->
                                                    <!--</div>-->
                                                    <!--<p class="text-gray font-14 mt-15 font-weight-500">{{ nl2br($conversations->message) }}</p> -->
                                                <!--</div>-->

                  <!--                              <div class="d-flex flex-column align-items-end">-->
                  <!--                                  <span class="metadata">-->
                  <!--    <span class="time">{{ dateTimeFormat($conversations->created_at,'j M Y | H:i') }}</span>-->
                  <!--</span>-->
                                                   <!--<span class="font-12 text-gray"></span>-->
                                       

                  <!--                                  @if(!empty($conversations->attach))-->
                  <!--                                      <a href="{{ url($conversations->attach) }}" target="_blank" class="font-12 mt-10 text-danger"><i data-feather="paperclip" height="14"></i> {{ trans('panel.attach') }}</a>-->
                  <!--                                  @endif-->
                  <!--                              </div>-->
                  <!--                          </div>-->
                   <span class="metadata">
                      <span class="time">{{ dateTimeFormat($conversations->created_at,' H:i') }}</span>
                  </span>
                                                                        </div> </div>
                                    @endforeach

                                @endif

</div>
                            <div class="conversation-box mt-30 py-10 px-15">
                                <h3 class="font-14 text-dark-blue font-weight-bold">{{ trans('panel.reply_to_the_conversation') }}</h3>
                                <form action="/panel/supports/{{ $selectSupport->id }}" method="post" class="mt-5">
                                    {{ csrf_field() }}

                                    <div class="form-group mt-10">
                                        <!--<label class="input-label d-block">{{ trans('site.message') }}</label>-->
                                        <textarea name="message" class="form-control @error('message')  is-invalid @enderror" rows="2">{{ old('message') }}</textarea>
                                        @error('message')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="d-flex d-flex align-items-center">
                                        <div class="form-group">
                                            <label class="input-label">{{ trans('panel.attach_file') }}</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text panel-file-manager" data-input="attach" data-preview="holder">
                                                        <i data-feather="arrow-up" width="18" height="18" class="text-white"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="attach" id="attach" value="{{ old('attach') }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <button type="submit" class="form-control btn btn-primary btn-sm ml-40 mt-10">{{ trans('site.send_message') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="col-12 col-lg-6 border-left border-gray300">
                            @include(getTemplate() . '.includes.no-result',[
                                'file_name' => 'support.png',
                                'title' => trans('panel.select_support'),
                                'hint' => nl2br(trans('panel.select_support_hint')),
                            ])
                        </div>
                    @endif
                </div>
            </div>

        @else

            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'support.png',
                'title' => trans('panel.support_no_result'),
                'hint' => nl2br(trans('panel.support_no_result_hint')),
            ])

        @endif
    </section>


            
            
            <!--##############################################   financial    ####################################################-->
            @php
            $finc=count($accountings);
            
            @endphp
               <div class="row">
                   @if($finc>0)
                   <div class="col-12 col-lg-8 mt-35">
                   
                   @else
                   <div class="col-12 col-lg-12 mt-35">
                   @endif
            
                 <h2 class="section-title">You may also like </h2>
             <div class="row">
            <div class="owl-carousel owl-theme slider" id="slider2">
                 
                 @foreach($featureWebinars as $webinar)
              @php
              //$user = auth()->user();
              //if($user->id==1504){
              if($webinar->id==2069){
              
              $name= "Upcomming Course";
              //}
              }
              @endphp
               
                    <div>
                        <div class="col-12  mt-20">
<div class="webinar-card">
    <figure>
        <div class="image-box">
            @if(isset($name))
                <span class="badge badge-primary">{{ $name }}</span>
            @elseif($webinar->bestTicket() < $webinar->price)
                <span class="badge badge-danger">{{ trans('public.offer',['off' => number_format($webinar->bestTicket(true)['percent'])]) }}</span>
            @elseif(empty($isFeature) and !empty($webinar->feature))
                <span class="badge badge-warning">{{ trans('home.featured') }}</span>
            @elseif($webinar->type == 'webinar')
                @if($webinar->start_date > time())
                    <span class="badge badge-primary">{{  trans('panel.not_conducted') }}</span>
                @elseif($webinar->isProgressing())
                    <span class="badge badge-secondary">{{ trans('webinars.in_progress') }}</span>
                @else
                    <span class="badge badge-secondary">{{ trans('public.finished') }}</span>
                @endif
            @elseif(!empty($webinar->type))
                <span class="badge badge-primary">{{ trans('webinars.'.$webinar->type) }}</span>
            @endif

            <a href="{{ $webinar->getUrl() }}">
                <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImage() }}" class="img-cover" alt="{{ $webinar->title }}">
            </a>


            @if($webinar->checkShowProgress())
                <div class="progress">
                    <span class="progress-bar" style="width: {{ $webinar->getProgress() }}%"></span>
                </div>
            @endif

            @if($webinar->type == 'webinar')
                <a href="{{ $webinar->addToCalendarLink() }}" target="_blank" class="webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class="webinar-icon"></i>
                </a>
            @endif
        </div>

        <figcaption class="webinar-card-body">
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->teacher->getAvatar() }}" class="img-cover" alt="{{ $webinar->teacher->full_name }}">
                </div>
                <a href="{{ $webinar->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $webinar->teacher->full_name }}</a>
            </div>

            <a href="{{ $webinar->getUrl() }}">
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>

            @if(!empty($webinar->category))
                <span class="d-block font-14 mt-5">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $webinar->category->title }}</a></span>
            @endif

            @include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])

            <div class="d-flex justify-content-between mt-5">
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="15" height="15" class="webinar-icon"></i>
                    <span class="duration font-14 ml-5">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>
                </div>

                <div class="vertical-line mx-15"></div>

                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="15" height="15" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ dateTimeFormat(!empty($webinar->start_date) ? $webinar->start_date : $webinar->created_at,'j M Y') }}</span>
                </div>
            </div>

            <div class="webinar-price-box mt-5">
            @if(!empty($isRewardCourses) and !empty($webinar->points))
                    <span class="text-warning real font-14">{{ $webinar->points }} {{ trans('update.points') }}</span>
                @elseif(!empty($webinar->price) and $webinar->price > 0)
                    @if($webinar->bestTicket() < $webinar->price)
                        <span class="real">{{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}</span>
                        <span class="off ml-10">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @else
                        <span class="real">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @endif
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div></div>

                    </div>
                @endforeach
                
                
             </div>
            </div>
            </div>
             @if($finc>0)
            <div class="col-12 col-lg-4 mt-30">
                 <h2 class="section-title">{{ trans('financial.financial_documents') }}</h2>
                 <div class="panel-section-card   mt-25 finace">
            <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.title') }}</th>
                                    <!--<th>{{ trans('public.description') }}</th>-->
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    <!--<th class="text-center">{{ trans('public.creator') }}</th>-->
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($accountings as $accounting)
                                    <tr>
                                        <td class="text-left"  width="60%">
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
                                                   
                                                    
                                                    {{ $accounting->description }}
                                                </div>
                                            </div>
                                        </td>
                                        <!--<td class="text-left align-middle">-->
                                        <!--    <span class="font-weight-500 text-gray">{{ $accounting->description }}</span>-->
                                        <!--</td>-->
                                        <td class="text-center align-middle"  width="10%">
                                            @switch($accounting->type)
                                                @case(\App\Models\Accounting::$addiction)
                                                    <span class="font-16 font-weight-bold text-primary">+{{ handlePrice($accounting->amount, false) }}</span>
                                                    @break;
                                                @case(\App\Models\Accounting::$deduction)
                                                    <span class="font-16 font-weight-bold text-danger">-{{ handlePrice($accounting->amount, false) }}</span>
                                                    @break;
                                            @endswitch
                                        </td>
                                        <!--<td class="text-center align-middle">{{ trans('public.'.$accounting->store_type) }}</td>-->
                                        <td class="text-center align-middle"  width="30%">
                                            <span>{{ dateTimeFormat($accounting->created_at, 'j M Y') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            
            </div>
            
            </div>
            
                  
                   @endif
            </div>
            
                <!--##############################################   financial    ####################################################-->
            
            
            
            
            
            
            <!--<div class="col-12 col-lg-6 mt-35">-->
            <!--    <div class="bg-white monthly-sales-card rounded-sm panel-shadow py-10 py-md-20 px-15 px-md-30">-->
            <!--        <div class="d-flex align-items-center justify-content-between">-->
            <!--            <h3 class="font-16 text-dark-blue font-weight-bold">{{ ($authUser->isUser()) ? trans('panel.learning_statistics') : trans('panel.monthly_sales') }}</h3>-->

            <!--            <span class="font-16 font-weight-500 text-gray">{{ dateTimeFormat(time(),'M Y') }}</span>-->
            <!--        </div>-->

            <!--        <div class="monthly-sales-chart">-->
            <!--            <canvas id="myChart"></canvas>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
            
            
            
               <!--##############################################   end    ####################################################-->
            
        <div class="row">
            <div class="col-12 col-lg-3 mt-35">
             
                  

                    @php
                        $getFinancialSettings = getFinancialSettings();
                        $drawable = $authUser->getPayout();
                        $can_drawable = 0;
                    @endphp

                  
                
            </div>

        </div>

       
    </section>


    <div class="d-none" id="iNotAvailableModal">
        <div class="offline-modal">
            <h3 class="section-title after-line">{{ trans('panel.offline_title') }}</h3>
            <p class="mt-20 font-16 text-gray">{{ trans('panel.offline_hint') }}</p>

            <div class="form-group mt-15">
                <label>{{ trans('panel.offline_message') }}</label>
                <textarea name="message" rows="4" class="form-control ">{{ $authUser->offline_message }}</textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class="js-save-offline-toggle btn btn-primary btn-sm">{{ trans('public.save') }}</button>
                <button type="button" class="btn btn-danger ml-10 close-swl btn-sm">{{ trans('public.close') }}</button>
            </div>
        </div>
    </div>

    <div class="d-none" id="noticeboardMessageModal">
        <div class="text-center">
            <h3 class="modal-title font-20 font-weight-500 text-dark-blue"></h3>
            <span class="modal-time d-block font-12 text-gray mt-25"></span>
            <p class="modal-message font-weight-500 text-gray mt-4"></p>
        </div>
    </div>

@endsection

@push('scripts_bottom')
 <!--<script src="{{ config('app.js_css_url') }}/assets/default/vendors/jquery.min.js"></script>-->
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/chartjs/chart.min.js"></script>
<script src="{{ config('app.js_css_url') }}/assets/default/vendors/owl.carousel.min.js"></script>
  <script src="{{ config('app.js_css_url') }}/assets/default/vendors/modules-slider.js"></script>
  <!--<script src="{{ config('app.js_css_url') }}/assets/default/vendors/scripts.js"></script>-->
  
    <script>
        var offlineSuccess = '{{ trans('panel.offline_success') }}';
        var $chartDataMonths = @json($monthlyChart['months']);
        var $chartData = @json($monthlyChart['data']);
//         var divHeight = $('.owl-stage').height(); 
//         alert(divHeight);
// $('.sidebar-ads').css('max-height', divHeight+'px');
//  var divHeight = $('.sidebar-ads').height(); 
//         // alert(divHeight);
// $('#slider1').css('min-height', divHeight+'px');
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/dashboard.min.js"></script>
@endpush
@push('scripts_bottom')
    <script>
        var instructor_contact_information_lang = '{{ trans('panel.instructor_contact_information') }}';
        var student_contact_information_lang = '{{ trans('panel.student_contact_information') }}';
        var email_lang = '{{ trans('public.email') }}';
        var phone_lang = '{{ trans('public.phone') }}';
        var location_lang = '{{ trans('update.location') }}';
        var close_lang = '{{ trans('public.close') }}';
        var finishReserveHint = '{{ trans('meeting.finish_reserve_modal_hint') }}';
        var finishReserveConfirm = '{{ trans('meeting.finish_reserve_modal_confirm') }}';
        var finishReserveCancel = '{{ trans('meeting.finish_reserve_modal_cancel') }}';
        var finishReserveTitle = '{{ trans('meeting.finish_reserve_modal_title') }}';
        var finishReserveSuccess = '{{ trans('meeting.finish_reserve_modal_success') }}';
        var finishReserveSuccessHint = '{{ trans('meeting.finish_reserve_modal_success_hint') }}';
        var finishReserveFail = '{{ trans('meeting.finish_reserve_modal_fail') }}';
        var finishReserveFailHint = '{{ trans('meeting.finish_reserve_modal_fail_hint') }}';

    </script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/contact-info.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/reserve_meeting.min.js"></script>
@endpush

@if(!empty($giftModal))
    @push('scripts_bottom2')
        <script>
            (function () {
                "use strict";

                handleLimitedAccountModal('{!! $giftModal !!}', 40)
            })(jQuery)
        </script>
    @endpush
@endif

