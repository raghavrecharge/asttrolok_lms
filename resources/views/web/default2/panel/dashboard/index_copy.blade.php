
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
            min-height: 200px;
    max-height: 200px;
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
        </style>
            
           
    <section class="dashboard">
        <div class="row">
            <div class="col-12 col-lg-9">
              <div class="dashboard-banner">
                  <div class="row">
                  <div class="col-12 col-lg-6">
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
                       <span class="font-30 text-secondary">{{ !empty($pendingAppointments) ? $pendingAppointments : (!empty($webinarsCount) ? $webinarsCount : 0) }}</span>
                    </div>
                    <span class="px-5 font-16 text-gray dash-box font-weight-500">My Courses</span>
             
                 </div>
                    </div>
                  <div>
                <div class="userprofile1 d-flex align-items-center">
                    <div class="">
                       <span class="font-30 text-secondary">{{ convertMinutesToHourAndMinute($hours) }}</span>
                    </div>
                      <span class="font-16 text-gray dash-box font-weight-500">Learning {{ trans('home.hours') }}</span>
                 </div>
                
               </div>
                </div>
                      </div>
                  <div class="col-12 col-lg-6">
                      <div class="image-banner"><img src="{{ config('app.js_css_url') }}/store/1/dashboards.png" alt="" class="imgs"></div>
                
                </div>
                </div>
            </div>
            </div>
         
            <div class="col-12 col-lg-3">
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
            <div class="col-12 col-lg-12 mt-35">
                       <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('panel.my_purchases') }}</h2>
        </div>
        
        @if((!empty($sales) and !$sales->isEmpty()) || (!empty($orders) and !$orders->isEmpty()))
        @if(!empty($sales) and !$sales->isEmpty() )
         <div class="row mt-30">
              <div class="owl-carousel owl-theme slider" id="slider1">
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
                            <div class="webinar-card webinar-list d-flex">
                                <div class="image-box" style="height:auto !important;">
                                    <img src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" class="img-cover" alt="">

                                    @if(!empty($sale->webinar))
                                        @if($item->type == 'webinar')
                                            @if($item->start_date > time())
                                                <span class="badge badge-primary">{{  trans('panel.not_conducted') }}</span>
                                            @elseif($item->isProgressing())
                                                <span class="badge badge-secondary">{{ trans('webinars.in_progress') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ trans('public.finished') }}</span>
                                            @endif
                                        @elseif(!empty($item->downloadable))
                                            <span class="badge badge-secondary">{{ trans('home.downloadable') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ trans('webinars.'.$item->type) }}</span>
                                        @endif

                                        @php
                                            $percent = $item->getProgress();

                                            if($item->isWebinar()){
                                                if($item->isProgressing()) {
                                                    $progressTitle = trans('public.course_learning_passed',['percent' => $percent]);
                                                } else {
                                                    $progressTitle = $item->sales_count .'/'. $item->capacity .' '. trans('quiz.students');
                                                }
                                            } else {
                                                   $progressTitle = trans('public.course_learning_passed',['percent' => $percent]);
                                            }
                                        @endphp

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            {{--  --}}
                                        @else
                                            <div class="progress cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ $progressTitle }}">
                                                <span class="progress-bar" style="width: {{ $percent }}%"></span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">{{ trans('update.bundle') }}</span>
                                    @endif
                                </div>

                                <div class="webinar-card-body w-100 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a href="{{ $item->getUrl() }}">
                                            <h3 class="webinar-title font-weight-bold font-16 text-dark-blue">
                                                {{ $item->title }}

                                                @if(!empty($item->access_days))
                                                    @if(!$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <span class="badge badge-outlined-danger ml-10">{{ trans('update.access_days_expired') }}</span>
                                                    @else
                                                        <span class="badge badge-outlined-warning ml-10">{{ trans('update.expired_on_date',['date' => dateTimeFormat($item->getExpiredAccessDays($sale->created_at, $sale->gift_id),'j M Y')]) }}</span>
                                                    @endif
                                                @endif

                                                @if($sale->payment_method == \App\Models\Sale::$subscribe and $sale->checkExpiredPurchaseWithSubscribe($sale->buyer_id, $item->id, !empty($sale->webinar) ? 'webinar_id' : 'bundle_id'))
                                                    <span class="badge badge-outlined-danger ml-10">{{ trans('update.subscribe_expired') }}</span>
                                                @endif

                                                @if(!empty($sale->webinar))
                                                    <span class="badge badge-dark ml-10 status-badge-dark">{{ trans(''.$item->lang) }}</span>
                                                @endif

                                                @if(!empty($sale->gift_id))
                                                    <span class="badge badge-primary ml-10">{{ trans('update.gift') }}</span>
                                                @endif
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

                                                        @if(!empty($item->downloadable) or (!empty($item->files) and count($item->files)))
                                                            <a href="{{ $item->getUrl() }}?tab=content" target="_blank" class="webinar-actions d-block mt-10">{{ trans('home.download') }}</a>
                                                        @endif

                                                        @if($item->price > 0)
                                                            <a href="/panel/webinars/{{ $item->id }}/sale/{{ $sale->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.invoice') }}</a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $item->getUrl() }}?tab=reviews" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.feedback') }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @include(getTemplate() . '.includes.webinar.rate',['rate' => $item->getRate()])

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

                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_status') }}:</span>

                                                @if(!empty($sale->gift_date) and $sale->gift_date > time())
                                                    <span class="stat-value text-warning">{{ trans('public.pending') }}</span>
                                                @else
                                                    <span class="stat-value text-primary">{{ trans('update.sent') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.item_id') }}:</span>
                                                <span class="stat-value">{{ $item->id }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id))
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_receive_date') }}:</span>
                                                <span class="stat-value">{{ (!empty($sale->gift_date)) ? dateTimeFormat($sale->gift_date, 'j M Y H:i') : trans('update.instantly') }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.category') }}:</span>
                                                <span class="stat-value">{{ !empty($item->category_id) ? $item->category->title : '' }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->webinar) and $item->type == 'webinar')
                                            @if($item->isProgressing() and !empty($nextSession))
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('webinars.next_session_duration') }}:</span>
                                                    <span class="stat-value">{{ convertMinutesToHourAndMinute($nextSession->duration) }} Hrs</span>
                                                </div>

                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('webinars.next_session_start_date') }}:</span>
                                                    <span class="stat-value">{{ dateTimeFormat($nextSession->date,'j M Y') }}</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                    <span class="stat-value">{{ convertMinutesToHourAndMinute($item->duration) }} Hrs</span>
                                                </div>

                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('public.start_date') }}:</span>
                                                    <span class="stat-value">{{ dateTimeFormat($item->start_date,'j M Y') }}</span>
                                                </div>
                                            @endif
                                        @elseif(!empty($sale->bundle))
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                <span class="stat-value">{{ convertMinutesToHourAndMinute($item->getBundleDuration()) }} Hrs</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.receipt') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_recipient }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.instructor') }}:</span>
                                                <span class="stat-value">{{ $item->teacher->full_name }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id != $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_sender') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_sender }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                                <span class="stat-value">{{ dateTimeFormat($sale->created_at,'j M Y') }}</span>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                   
                @endif
                </div>
            @endforeach
            </div>
            </div>
            @endif
            @if(!empty($orders) and !$orders->isEmpty())
            <div class="row mt-30">
              <div class="owl-carousel owl-theme slider" id="slider1">
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
                            <div class="webinar-card webinar-list d-flex">
                                <div class="image-box" style="height:auto !important;">
                                    <img src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" class="img-cover" alt="">

                                    @if(!empty($sale->webinar))
                                        @if($item->type == 'webinar')
                                            @if($item->start_date > time())
                                                <span class="badge badge-primary">{{  trans('panel.not_conducted') }}</span>
                                            @elseif($item->isProgressing())
                                                <span class="badge badge-secondary">{{ trans('webinars.in_progress') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ trans('public.finished') }}</span>
                                            @endif
                                        @elseif(!empty($item->downloadable))
                                            <span class="badge badge-secondary">{{ trans('home.downloadable') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ trans('webinars.'.$item->type) }}</span>
                                        @endif

                                        @php
                                            $percent = $item->getProgress();

                                            if($item->isWebinar()){
                                                if($item->isProgressing()) {
                                                    $progressTitle = trans('public.course_learning_passed',['percent' => $percent]);
                                                } else {
                                                    $progressTitle = $item->sales_count .'/'. $item->capacity .' '. trans('quiz.students');
                                                }
                                            } else {
                                                   $progressTitle = trans('public.course_learning_passed',['percent' => $percent]);
                                            }
                                        @endphp

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            {{--  --}}
                                        @else
                                            <div class="progress cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ $progressTitle }}">
                                                <span class="progress-bar" style="width: {{ $percent }}%"></span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">{{ trans('update.bundle') }}</span>
                                    @endif
                                </div>

                                <div class="webinar-card-body w-100 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a href="{{ $item->getUrl() }}">
                                            <h3 class="webinar-title font-weight-bold font-16 text-dark-blue">
                                                {{ $item->title }}

                                                @if(!empty($item->access_days))
                                                    @if(!$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <span class="badge badge-outlined-danger ml-10">{{ trans('update.access_days_expired') }}</span>
                                                    @else
                                                        <span class="badge badge-outlined-warning ml-10">{{ trans('update.expired_on_date',['date' => dateTimeFormat($item->getExpiredAccessDays($sale->created_at, $sale->gift_id),'j M Y')]) }}</span>
                                                    @endif
                                                @endif

                                                @if($sale->payment_method == \App\Models\Sale::$subscribe and $sale->checkExpiredPurchaseWithSubscribe($sale->buyer_id, $item->id, !empty($sale->webinar) ? 'webinar_id' : 'bundle_id'))
                                                    <span class="badge badge-outlined-danger ml-10">{{ trans('update.subscribe_expired') }}</span>
                                                @endif

                                                @if(!empty($sale->webinar))
                                                    <span class="badge badge-dark ml-10 status-badge-dark">{{ trans(''.$item->lang) }}</span>
                                                @endif

                                                @if(!empty($sale->gift_id))
                                                    <span class="badge badge-primary ml-10">{{ trans('update.gift') }}</span>
                                                @endif
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

                                                        @if(!empty($item->downloadable) or (!empty($item->files) and count($item->files)))
                                                            <a href="{{ $item->getUrl() }}?tab=content" target="_blank" class="webinar-actions d-block mt-10">{{ trans('home.download') }}</a>
                                                        @endif

                                                        @if($item->price > 0)
                                                            <a href="/panel/webinars/{{ $item->id }}/sale/{{ $sale->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.invoice') }}</a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $item->getUrl() }}?tab=reviews" target="_blank" class="webinar-actions d-block mt-10">{{ trans('public.feedback') }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @include(getTemplate() . '.includes.webinar.rate',['rate' => $item->getRate()])

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

                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_status') }}:</span>

                                                @if(!empty($sale->gift_date) and $sale->gift_date > time())
                                                    <span class="stat-value text-warning">{{ trans('public.pending') }}</span>
                                                @else
                                                    <span class="stat-value text-primary">{{ trans('update.sent') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.item_id') }}:</span>
                                                <span class="stat-value">{{ $item->id }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id))
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_receive_date') }}:</span>
                                                <span class="stat-value">{{ (!empty($sale->gift_date)) ? dateTimeFormat($sale->gift_date, 'j M Y H:i') : trans('update.instantly') }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.category') }}:</span>
                                                <span class="stat-value">{{ !empty($item->category_id) ? $item->category->title : '' }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->webinar) and $item->type == 'webinar')
                                            @if($item->isProgressing() and !empty($nextSession))
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('webinars.next_session_duration') }}:</span>
                                                    <span class="stat-value">{{ convertMinutesToHourAndMinute($nextSession->duration) }} Hrs</span>
                                                </div>

                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('webinars.next_session_start_date') }}:</span>
                                                    <span class="stat-value">{{ dateTimeFormat($nextSession->date,'j M Y') }}</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                    <span class="stat-value">{{ convertMinutesToHourAndMinute($item->duration) }} Hrs</span>
                                                </div>

                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('public.start_date') }}:</span>
                                                    <span class="stat-value">{{ dateTimeFormat($item->start_date,'j M Y') }}</span>
                                                </div>
                                            @endif
                                        @elseif(!empty($sale->bundle))
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.duration') }}:</span>
                                                <span class="stat-value">{{ convertMinutesToHourAndMinute($item->getBundleDuration()) }} Hrs</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.receipt') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_recipient }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('public.instructor') }}:</span>
                                                <span class="stat-value">{{ $item->teacher->full_name }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($sale->gift_id) and $sale->buyer_id != $authUser->id)
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.gift_sender') }}:</span>
                                                <span class="stat-value">{{ $sale->gift_sender }}</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                                <span class="stat-value">{{ dateTimeFormat($sale->created_at,'j M Y') }}</span>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                   
                @endif
                </div>
            @endforeach
             </div>
             </div>
            @endif
           
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
            </div>
            
            
            
            <!--##############################################   METING    ####################################################-->
            
            <div class="row  mt-35">
                <div class="col-12 col-lg-12  mt-35">
           
              <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
              <h2 class="section-title">{{ trans('panel.meeting_list') }}</h2>

            <form action="/panel/?{{ http_build_query(request()->all()) }}" class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                <label class="cursor-pointer mb-0 mr-10 text-gray font-14 font-weight-500" for="openMeetingResult">{{ trans('panel.show_only_open_meetings') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="open_meetings" class="js-panel-list-switch-filter custom-control-input" id="openMeetingResult" {{ (request()->get('open_meetings', '') == 'on') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="openMeetingResult"></label>
                </div>
            </form>
        </div>

        <div class="panel-section-card py-20 px-25 mt-20">
            <form action="/panel/" method="get" class="row">
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
                                           aria-describedby="dateInputGroupPrepend" value="{{ request()->get('from','') }}"/>
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
                                           aria-describedby="dateInputGroupPrepend" value="{{ request()->get('to','') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.day') }}</label>
                                <select class="form-control" id="day" name="day">
                                    <option value="all">{{ trans('public.all_days') }}</option>
                                    <option value="saturday" {{ (request()->get('day') === "saturday") ? 'selected' : '' }}>{{ trans('public.saturday') }}</option>
                                    <option value="sunday" {{ (request()->get('day') === "sunday") ? 'selected' : '' }}>{{ trans('public.sunday') }}</option>
                                    <option value="monday" {{ (request()->get('day') === "monday") ? 'selected' : '' }}>{{ trans('public.monday') }}</option>
                                    <option value="tuesday" {{ (request()->get('day') === "tuesday") ? 'selected' : '' }}>{{ trans('public.tuesday') }}</option>
                                    <option value="wednesday" {{ (request()->get('day') === "wednesday") ? 'selected' : '' }}>{{ trans('public.wednesday') }}</option>
                                    <option value="thursday" {{ (request()->get('day') === "thursday") ? 'selected' : '' }}>{{ trans('public.thursday') }}</option>
                                    <option value="friday" {{ (request()->get('day') === "friday") ? 'selected' : '' }}>{{ trans('public.friday') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-lg-8">
                            <div class="row">
                                <div class="col-12 col-lg-8">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('public.instructor') }}</label>
                                        <select name="instructor_id" class="form-control select2 ">
                                            <option value="all">{{ trans('webinars.all_instructors') }}</option>

                                            @foreach($instructors as $instructor)
                                                <option value="{{ $instructor->id }}" @if(request()->get('instructor_id') == $instructor->id) selected @endif>{{ $instructor->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('public.status') }}</label>
                                        <select class="form-control" id="status" name="status">
                                            <option>{{ trans('public.all') }}</option>
                                            <option value="open" {{ (request()->get('status') === "open") ? 'selected' : '' }}>{{ trans('public.open') }}</option>
                                            <option value="finished" {{ (request()->get('status') === "finished") ? 'selected' : '' }}>{{ trans('public.finished') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                </div>
            </form>
        </div>
            
    <section class="mt-35">
       

        @if($reserveMeetings->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive" style="min-height:400px;">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.instructor') }}</th>
                                    <th class="text-center">{{ trans('update.meeting_type') }}</th>
                                    <th class="text-center">{{ trans('public.day') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th class="text-center">{{ trans('public.time') }}</th>
                                    <th class="text-center">{{ trans('public.paid_amount') }}</th>
                                    <th class="text-center">{{ trans('update.students_count') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($reserveMeetings as $ReserveMeeting)
                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar bg-gray200">
                                                    <img src="{{ config('app.img_dynamic_url') }}{{ $ReserveMeeting->meeting->creator->getAvatar() }}" class="img-cover" alt="">
                                                </div>
                                                <div class=" ml-5">
                                                    <span class="d-block font-weight-500">{{ $ReserveMeeting->meeting->creator->full_name }}</span>
                                                    <span class="mt-5 font-12 text-gray d-block">{{ $ReserveMeeting->meeting->creator->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ trans('update.'.$ReserveMeeting->meeting_type) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ dateTimeFormat($ReserveMeeting->start_at, 'D') }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span>{{ dateTimeFormat($ReserveMeeting->start_at, 'j M Y') }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-inline-flex align-items-center rounded bg-gray200 py-5 px-15 font-14 font-weight-500">
                                                <span class="">{{ dateTimeFormat($ReserveMeeting->start_at, 'H:i') }}</span>
                                                <span class="mx-1">-</span>
                                                <span class="">{{ dateTimeFormat($ReserveMeeting->end_at, 'H:i') }}</span>
                                            </div>
                                        </td>
                                        <td class="align-middle font-weight-500">
                                            @if(!empty($ReserveMeeting->sale) and !empty($ReserveMeeting->sale->total_amount) and $ReserveMeeting->sale->total_amount > 0)
                                                {{ handlePrice($ReserveMeeting->sale->total_amount) }}

                                            @else
                                                {{ trans('public.free') }}
                                            @endif
                                        </td>
                                        <td class="align-middle font-weight-500">
                                            {{ $ReserveMeeting->student_count ?? 1 }}
                                        </td>
                                        <td class="align-middle">
                                            @switch($ReserveMeeting->status)
                                                @case(\App\Models\ReserveMeeting::$pending)
                                                    <span class="text-warning font-weight-500">{{ trans('public.pending') }}</span>
                                                    @break
                                                @case(\App\Models\ReserveMeeting::$open)
                                                    <span class="text-gray font-weight-500">{{ trans('public.open') }}</span>
                                                    @break
                                                @case(\App\Models\ReserveMeeting::$finished)
                                                    <span class="font-weight-500 text-primary">{{ trans('public.finished') }}</span>
                                                    @break
                                                @case(\App\Models\ReserveMeeting::$canceled)
                                                    <span class="text-danger font-weight-500">{{ trans('public.canceled') }}</span>
                                                    @break
                                            @endswitch
                                        </td>


                                        <td class="align-middle text-right">
                                            @if($ReserveMeeting->status != \App\Models\ReserveMeeting::$finished)

                                                <input type="hidden" class="js-meeting-password-{{ $ReserveMeeting->id }}" value="{{ $ReserveMeeting->password }}">
                                                <input type="hidden" class="js-meeting-link-{{ $ReserveMeeting->id }}" value="{{ $ReserveMeeting->link }}">


                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle"
                                                            data-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button>
                                                    <div class="dropdown-menu menu-lg">

                                                        @if(getFeaturesSettings('agora_for_meeting') and $ReserveMeeting->meeting_type != 'in_person' and $ReserveMeeting->status == \App\Models\ReserveMeeting::$open)
                                                            @if(!empty($ReserveMeeting->session))
                                                                <button type="button" data-item-id="{{ $ReserveMeeting->id }}" data-date="{{ dateTimeFormat($ReserveMeeting->start_at, 'j M Y H:i') }}" data-link="{{ $ReserveMeeting->session->getJoinLink() }}"
                                                                        class="js-join-meeting-session btn-transparent webinar-actions d-block mt-10 text-primary">{{ trans('update.join_to_session') }}</button>
                                                            @endif
                                                        @endif

                                                        @if($ReserveMeeting->link and $ReserveMeeting->status == \App\Models\ReserveMeeting::$open)
                                                            <button type="button" data-reserve-id="{{ $ReserveMeeting->id }}"
                                                                    class="js-join-reserve btn-transparent webinar-actions d-block mt-10">{{ trans('footer.join') }}</button>
                                                        @endif

                                                        <a href="{{ $ReserveMeeting->addToCalendarLink() }}" target="_blank"
                                                           class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('public.add_to_calendar') }}</a>

                                                        <button type="button"
                                                                data-user-id="{{ $ReserveMeeting->meeting->creator_id }}"
                                                                data-item-id="{{ $ReserveMeeting->id }}"
                                                                data-user-type="instructor"
                                                                class="contact-info btn-transparent webinar-actions d-block mt-10">{{ trans('panel.contact_instructor') }}</button>

                                                        <button type="button" data-id="{{ $ReserveMeeting->id }}" class="webinar-actions js-finish-meeting-reserve d-block btn-transparent mt-10 font-weight-normal">{{ trans('panel.finish_meeting') }}</button>
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
                {{ $reserveMeetings->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'meeting.png',
                'title' => trans('panel.meeting_no_result'),
                'hint' => nl2br(trans('panel.meeting_no_result_hint')),
            ])
        @endif
    </section>
 @include('web.default.panel.meeting.join_modal')
    @include('web.default.panel.meeting.meeting_create_session_modal')
            </div>
            </div>
            
            
                <!--##############################################   financial    ####################################################-->
            
               <div class="row">
            <div class="col-12 col-lg-7 mt-35">
            
            <section class="">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('update.my_installments') }}</h2>
        </div>

        @if(!empty($orders) and count($orders))
        @php
        $fnc_cls="finace";
        @endphp
         <div class="row mt-30">
             <div class="owl-carousel owl-theme slider" id="slider2">
            @foreach($orders as $order)
              <div>
                @php
                    $orderItem = $order->getItem();
                    $itemType = $order->getItemType();
                    $itemPrice = $order->getItemPrice();
                @endphp

                @if(!empty($orderItem))
                 
                        <div class="col-lg-12">
                            <div class="webinar-card webinar-list panel-installment-card d-flex">
                                <div class="image-box" style="height:auto !important;">
                                    @if(in_array($itemType, ['course', 'bundle']))
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $orderItem->getImage() }}" class="img-cover" alt="">
                                    @elseif($itemType == 'product')
                                        <img src="{{ $orderItem->thumbnail }}" class="img-cover" alt="">
                                    @elseif($itemType == "subscribe")
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                            <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/installment/subscribe_default.svg" alt="">
                                        </div>
                                    @elseif($itemType == "registrationPackage")
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                            <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/installment/reg_package_default.svg" alt="">
                                        </div>
                                    @endif

                                    @if($order->isCompleted())
                                        <span class="badge badge-secondary">{{ trans('update.completed') }}</span>
                                    @elseif($order->status == "open")
                                        <span class="badge badge-primary">{{  trans('public.open') }}</span>
                                    @elseif($order->status == "rejected")
                                        <span class="badge badge-danger">{{  trans('public.rejected') }}</span>
                                    @elseif($order->status == "canceled")
                                        <span class="badge badge-danger">{{  trans('public.canceled') }}</span>
                                    @elseif($order->status == "pending_verification")
                                        <span class="badge badge-warning">{{  trans('update.pending_verification') }}</span>
                                    @elseif($order->status == "refunded")
                                        <span class="badge badge-secondary">{{  trans('update.refunded') }}</span>
                                    @endif
                                </div>

                                <div class="webinar-card-body w-100 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <h3 class="font-16 text-dark-blue font-weight-bold">{{ $orderItem->title }}</h3>

                                            @if($order->has_overdue)
                                                <span class="badge badge-outlined-danger ml-10">{{  trans('update.overdue') }}</span>
                                            @endif
                                        </div>

                                        @if(!in_array($order->status, ['refunded', 'canceled']) or $order->isCompleted())
                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu ">

                                                    @if($order->status == "open")
                                                        <a href="/panel/financial/installments/{{ $order->id }}/pay_upcoming_part" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.pay_upcoming_part') }}</a>
                                                    @endif

                                                    @if(!in_array($order->status, ['refunded', 'canceled']))
                                                        <a href="/panel/financial/installments/{{ $order->id }}/details" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.view_details') }}</a>
                                                    @endif

                                                    @if($itemType == "course" and ($order->isCompleted() or $order->status == "open"))
                                                        <a href="{{ $orderItem->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.learning_page') }}</a>
                                                    @endif

                                                    {{--@if($order->isCompleted() or $order->status == "open")
                                                        <a href="/panel/financial/installments/{{ $order->id }}/refund" class="webinar-actions d-block mt-10 delete-action">{{ trans('update.refund') }}</a>
                                                    @endif--}}

                                                    @if($order->status == "pending_verification" and getInstallmentsSettings("allow_cancel_verification"))
                                                        <a href="/panel/financial/installments/{{ $order->id }}/cancel" class="webinar-actions d-block mt-10 text-danger delete-action" data-title="{{ trans('public.deleteAlertHint') }}" data-confirm="{{ trans('update.yes_cancel') }}">{{ trans('public.cancel') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                        <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('update.item_type') }}:</span>
                                            <span class="stat-value">{{ trans('update.item_type_'.$itemType) }}</span>
                                        </div>

                                        <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                            <span class="stat-value">{{ dateTimeFormat($order->created_at, 'j M Y H:i') }}</span>
                                        </div>

                                        <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('update.upfront') }}:</span>
                                            <span class="stat-value">{{ !empty($order->installment->upfront) ? handlePrice($order->installment->getUpfront($itemPrice)) : '-' }}</span>
                                        </div>

                                        <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('update.total_installments') }}:</span>
                                            <span class="stat-value">{{ trans('update.total_parts_count', ['count' => $order->installment->steps_count]) }} ({{ handlePrice($order->installment->totalPayments($itemPrice, false)) }})</span>
                                        </div>

                                        @if($order->status == "open" or $order->status == "pending_verification")
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span class="stat-title">{{ trans('update.remained_installments') }}:</span>
                                                <span class="stat-value">{{ trans('update.total_parts_count', ['count' => $order->remained_installments_count]) }} ({{ handlePrice($order->remained_installments_amount) }})</span>
                                            </div>

                                            @if(!empty($order->upcoming_installment))
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('update.upcoming_installment') }}:</span>
                                                    <span class="stat-value">{{ dateTimeFormat((($order->upcoming_installment->deadline * 86400) + $order->created_at), 'j M Y') }} ({{ handlePrice($order->upcoming_installment->getPrice($itemPrice)) }})</span>
                                                </div>
                                            @endif

                                            @if($order->has_overdue)
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span class="stat-title">{{ trans('update.overdue_installments') }}:</span>
                                                    <span class="stat-value">{{ $order->overdue_count }} ({{ handlePrice($order->overdue_amount) }})</span>
                                                </div>
                                            @endif
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                    
                @endif
                </div>
            @endforeach
</div>
            <div class="my-30">
                {{ $orders->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
        @php
        $fnc_cls="no-finace";
        @endphp
            @include('web.default.includes.no-result',[
                    'file_name' => 'webinar.png',
                    'title' => trans('update.you_not_have_any_installment'),
                    'hint' =>  trans('update.you_not_have_any_installment_hint'),
                ])
        @endif
    </section>
            
            
            </div>
            <div class="col-12 col-lg-5 mt-30">
                 <h2 class="section-title">{{ trans('financial.financial_documents') }}</h2>
                 <div class="panel-section-card   mt-35 <?php echo $fnc_cls; ?>">
            <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.title') }}</th>
                                    <th>{{ trans('public.description') }}</th>
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    <!--<th class="text-center">{{ trans('public.creator') }}</th>-->
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>

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
                                        <td class="text-left align-middle">
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
                                        <!--<td class="text-center align-middle">{{ trans('public.'.$accounting->store_type) }}</td>-->
                                        <td class="text-center align-middle">
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

