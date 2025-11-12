@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')

    <section class="mt-25">
       <div class="mt-30 mb-30 d-flex justify-content-center align-items-center">
    <h2 class="section-title text-center">Bundle List</h2>
</div>
        <div class="d-flex justify-content-between align-items-center">
         <h2 class="section-title after-line">{{ trans('product.courses') }}</h2>
        </div>
        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row">
                <div class="col-12 ">
                    <div class="table-responsive">
                        <table class="table text-center custom-table">
                            <thead>
                            <tr>
                                <th>{{ trans('public.title') }}</th>
                                <th class="text-center">Learning Link</th>
                                <th class=""></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($bundle->bundleWebinars as $webinar)
                                @if(!empty($webinar->webinar))
                                    <tr>
                                        <td class="text-left">
                                            <div class="d-block font-16 font-weight-500 text-dark-blue">
                                             <a href="/course/{{ $webinar->webinar->slug }}" 
                                               target="_blank"  >
                                                    {{ $webinar->webinar->title }}
                                                    </a>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="/course/learning/{{ $webinar->webinar->slug }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary my-10 w-100 mt-2">
                                               {{ trans('update.learning_page') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
 <!--      Products -->
<div class="mt-30 d-flex justify-content-between align-items-center">
        <h2 class="section-title after-line">{{ trans('product.products') }}</h2>
</div>
         <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row">
                <div class="col-12 ">
                    <div class="table-responsive">
                        <table class="table text-center custom-table">
                            <thead>
                            <tr>
                                <th>{{ trans('public.title') }}</th>
                                <th class="text-center">View Product Details</th>
                                <th class=""></th>
                            </tr>
                            </thead>
                            <tbody>
                           @foreach($bundle->bundleWebinars as $product)
                                @if(!empty($product->product))
                                    <tr>
                                        <td class="text-left">
                                            <div class="d-block font-16 font-weight-500 text-dark-blue">
                                             <a href="/products/{{ $product->product->slug }}" 
                                               target="_blank"  >
                                                 {{ $product->product->title }}
                                               </a>
                                            </div>
                                        </td>
                                          <td class="text-center">
                                            <a href="/panel/store/purchases" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary my-10 w-100 mt-2">
                                               {{ trans('public.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<!--Consultation-->
<div class="mt-30 d-flex justify-content-between align-items-center">
        <h2 class="section-title after-line">{{ trans('product.consultations') }}</h2>
</div>
@php
//$firstBundleWebinarId = $bundle->bundleWebinars->first()->id ?? null;
@endphp
 <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row">
                <div class="col-12 ">
                    <div class="table-responsive">
                        <table class="table text-center custom-table">
                            <thead>
                            <tr>
                                <th>{{ trans('public.title') }}</th>
                                <th>Consultant</th>
                                <th>Price</th>
                                <th class="text-center">Time</th>
                                <th class=""></th>
                            </tr>
                            </thead>
                            <tbody>
                            {{-- Consultations --}}
                            @foreach($bundle->bundleWebinars as $bundleWebinar)
                                @if(!empty($bundleWebinar->consultation_type))
                                    <tr>
                                       <td class="text-left">
                                            <div class="d-block font-16 font-weight-500 text-dark-blue">
                                                {{ ucfirst($bundleWebinar->consultation_type) }}
                                                <!--<span class="badge badge-info ml-2">Consultation</span>-->
                                            </div>
                                        </td>
                                    
                                        {{-- Consultant Name --}}
                                        <td class="text-left">
                                            @if($bundleWebinar->consultation_type == 'specific')
                                                <span class="text-muted">{{ optional($userConsultants->firstWhere('id', $bundleWebinar->consultant_id))->full_name }}</span>
                                            @else
                                                <span class="text-muted">Any Consultant</span>
                                            @endif
                                        </td>
                                    
                                        {{-- Price --}}
                                        <td class="text-left">
                                            @php
                                                $amount = optional(optional($userConsultants->firstWhere('id', $bundleWebinar->consultant_id))->meeting)->amount;
                                            @endphp
                                    
                                            @if($bundleWebinar->consultation_type == 'specific' && $bundleWebinar->slot_time)
                                                @if($bundleWebinar->slot_time == 'both')
                                                    ₹{{ $amount / 2 }} - ₹{{ $amount }} /-
                                                @elseif($bundleWebinar->slot_time == 15)
                                                    ₹{{ $amount / 2 }} /-
                                                @elseif($bundleWebinar->slot_time == 30)
                                                    ₹{{ $amount }} /-
                                                @endif
                                            @elseif($bundleWebinar->consultation_type == 'range')
                                                ₹{{ $bundleWebinar->starting_price }} - ₹{{ $bundleWebinar->ending_price }}
                                            @elseif($bundleWebinar->consultation_type == 'all')
                                                Any price
                                            @endif
                                        </td>
                                    
                                        {{-- Slot Time --}}
                                        <td class="text-center">
                                            @if($bundleWebinar->slot_time == 'both')
                                                15 min , 30 min
                                            @else
                                                {{ $bundleWebinar->slot_time }} min
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(\App\Models\Api\ReserveMeeting::where('bundle_webinar_id', $bundleWebinar->id)
                                                ->where('bundle_id', $bundle->id)
                                                ->where('user_id', auth()->user()->id)
                                                ->first())
                                                <a href="#" class="btn btn-primary btn-block disable">
                                                    Booked
                                                </a>
                                                @else
                                                <a href="{{ route('bundle.consultation.consultants', [
                                                    'slug' => $bundle->slug,
                                                    'type' => $bundleWebinar->consultation_type,
                                                    'time' => $bundleWebinar->slot_time,
                                                    'bundle_id' => $bundle->id,
                                                    'bundle_webinar_id' => $bundleWebinar->id,
                                                ]) }}" class="btn btn-primary btn-block">
                                                    Book Now
                                                </a>
                                                @endif
                                       
                                    </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
