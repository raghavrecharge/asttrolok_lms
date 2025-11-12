<!--<div class="modal fade" id="myModal" role="dialog">-->
<!--    <div class="modal-dialog">-->
    
      <!-- Modal content-->
<!--      <div class="modal-content">-->
<!--        <div class="modal-header">-->
<!--          <button type="button" class="close" data-dismiss="modal">&times;</button>-->
<!--          <h4 class="modal-title">Modal Header</h4>-->
<!--        </div>-->
<!--        <div class="modal-body">-->
<!--          <p>Some text in the modal.</p>-->
<!--        </div>-->
<!--        <div class="modal-footer">-->
<!--          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
<!--        </div>-->
<!--      </div>-->
      
<!--    </div>-->
<!--  </div>-->
  <div class="modal fade" id="buynow_modal" tabindex="-1" aria-labelledby="buynow_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class=" position-relative">
                

                

                <div class="modal-video-lists mt-15">

                  
                                    {{-- <div class="d-flex justify-content-between align-items-center my-15 px-20">
                                        <h3 class="section-title after-line">Please login to access the content.</h3>
                                    </div> --}}

                                        <div class="rounded-lg col-12 col-lg-4 mt-25 mt-lg-0" style="margin-right: auto;margin-left: auto;">
                                            
                                            <div class="px-80 pb-30">
                        <form action="/cart/store" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="item_id" value="{{ $course->id }}">
            <input type="hidden" name="item_name" value="webinar_id">

            @if(!empty($course->tickets))
                @foreach($course->tickets as $ticket)

                    <div class="form-check mt-20">
                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio" data-discount="{{ $ticket->discount }}" value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                               name="ticket_id"
                               id="courseOff{{ $ticket->id }}">
                        <label class="form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                            <span class="font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                @endif</span>
                            <span class="font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                        </label>
                    </div>
                @endforeach
            @endif

            @if($course->price > 0)
                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                    <div class="text-center">
                        @php
                            $realPrice = handleCoursePagePrice($course->price);
                        @endphp
                        <span id="realPrice" data-value="{{ $course->price }}"
                              data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : ''}}"
                              class=" @if(!empty($activeSpecialOffer)) font-20 text-gray text-decoration-line-through @else font-30 text-primary @endif">
                            {{ $realPrice['price'] }}/-
                        </span>

                        @if(!empty($realPrice['tax']) and empty($activeSpecialOffer))
                            <span class=" font-14 text-gray">+ {{ $realPrice['tax'] }} {{ trans('cart.tax') }}</span>
                        @endif
                    </div>

                    @if(!empty($activeSpecialOffer))
                        <div class="text-center">
                            @php
                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                            @endphp
                            <span id="priceWithDiscount"
                                  class=" font-30 text-primary">
                                {{ $priceWithDiscount['price'] }}/-
                            </span>

                            @if(!empty($priceWithDiscount['tax']))
                                <span class="font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} {{ trans('cart.tax') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <div class="d-flex align-items-center justify-content-center mt-20">
                    <span class="font-36 text-primary">{{ trans('public.free') }}</span>
                </div>
            @endif

            @php
                $canSale = ($course->canSale() and !$hasBought);
            @endphp

            <div class=" d-flex flex-column">
                @if(!$canSale and $course->canJoinToWaitlist())
                    <button type="button" data-slug="{{ $course->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                @elseif($hasBought or !empty($course->getInstallmentOrder()))
                    <a href="{{ $course->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
                @elseif($course->price > 0)
                    

                    @if($canSale and $course->subscribe)
                        <a href="/subscribes/apply/{{ $course->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                    @endif

                    @if($canSale and !empty($course->points))
                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                            {!! trans('update.buy_with_n_points',['points' => $course->points]) !!}
                        </a>
                    @endif

                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                        <button type="button" class="btn btn-outline-danger buy_now mt-20 js-course-direct-payment">
                          
<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 10px !important;">
<path d="M15.7143 11.985V14.8386C15.7143 15.4692 15.212 15.98 14.5918 15.98H1.12245C0.502296 15.98 0 15.4692 0 14.8386V11.985H1.68367V14.2679H14.0306V11.985H15.7143Z" fill="white"/>
<path d="M11.6667 8.68171L7.85714 12.5557L4.04755 8.68171C3.79668 8.4266 3.97459 7.99 4.32929 7.99H6.73469V1.14143C6.73469 0.510789 7.23699 0 7.85714 0C8.4773 0 8.97959 0.510789 8.97959 1.14143V7.99H11.385C11.7397 7.99 11.9176 8.4266 11.6667 8.68171Z" fill="white"/>
</svg>
  {{ trans('update.buy_now') }}
                        </button>
                    @endif

                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                        <a href="/course/{{ $course->slug }}/installments" class="btn btn-outline-primary mt-20" >
                         
<svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 10px !important;">
<path d="M12.25 1.88125H11.6667V0.627045C11.6667 0.28097 11.4053 0 11.0833 0H10.5C10.178 0 9.9167 0.28097 9.9167 0.627045V1.88125H4.0833V0.627045C4.0833 0.28097 3.82204 0 3.5 0H2.9167C2.59467 0 2.3333 0.28097 2.3333 0.627045V1.88125H1.75C0.785172 1.88125 0 2.72531 0 3.7625V13.1687C0 14.2059 0.785172 15.05 1.75 15.05H12.25C13.2148 15.05 14 14.2059 14 13.1687V3.7625C14 2.72531 13.2148 1.88125 12.25 1.88125ZM12.8333 13.1687C12.8333 13.5143 12.5714 13.7958 12.25 13.7958H1.75C1.4286 13.7958 1.1667 13.5143 1.1667 13.1687V6.29594H12.8333V13.1687Z" fill="white"/>
<path d="M6.67978 13.0189C6.76256 13.1142 6.87866 13.1687 7 13.1687C7.12134 13.1687 7.23798 13.1142 7.32086 13.0189L9.21666 10.8241C9.33512 10.6867 9.36545 10.4873 9.2972 10.3155C9.22777 10.1443 9.07022 10.0333 8.8958 10.0333H7.5833V7.525C7.5833 7.17881 7.32204 6.89795 7 6.89795C6.67796 6.89795 6.4167 7.17881 6.4167 7.525V10.0333H5.1042C4.93031 10.0333 4.77287 10.1443 4.70281 10.3155C4.67888 10.3764 4.6667 10.4397 4.6667 10.5036C4.6667 10.6203 4.70686 10.7351 4.78334 10.8241L6.67978 13.0189Z" fill="white"/>
</svg>
   {{ trans('update.pay_with_installments') }}
                        </a>
                    @endif
                     <button type="button" class="mt-20 btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($course->cantSaleStatus($hasBought) .' disabled ') }}" >
                        @if(!$canSale)
                            {{ trans('update.disabled_add_to_cart') }}
                        @else
                           
<svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 10px !important;">
<path d="M0.51269 0.999969H1.71843C1.94374 0.999969 2.14099 1.14107 2.20809 1.35104L5.02306 10.1459C4.50056 10.3858 4.1357 10.9005 4.1357 11.5C4.1357 12.3271 4.82564 12.9997 5.67377 12.9997H6.28089C6.22344 13.1568 6.18646 13.3239 6.18646 13.5C6.18646 14.3271 6.8764 15 7.72453 15C8.57265 15 9.2626 14.3271 9.2626 13.5C9.2626 13.3239 9.22561 13.1568 9.16816 12.9997H11.4078C11.3503 13.1568 11.3134 13.3235 11.3134 13.4997C11.3134 14.3268 12.0033 15 12.8514 15C13.6996 15 14.3895 14.3271 14.3895 13.5C14.3895 13.3239 14.3525 13.1572 14.2951 13H14.9022C15.1856 13 15.4149 12.7764 15.4149 12.5C15.4149 12.2236 15.1856 12 14.9022 12H5.67377C5.39138 12 5.16108 11.7759 5.16108 11.5C5.16108 11.2241 5.39138 11 5.67377 11H14.1353C14.8422 11 15.4561 10.5327 15.6283 9.86376L17.4848 2.62107C17.5228 2.47167 17.4888 2.31344 17.3916 2.19237C17.2945 2.0708 17.1453 2 16.9871 2H3.48985L3.18743 1.05274C2.98515 0.422868 2.39436 0 1.71843 0H0.51269C0.229309 0 0 0.223634 0 0.500001C0 0.776368 0.229309 0.999969 0.51269 0.999969ZM11.3735 9.99999H9.20244L8.81792 6.99998H11.758L11.3735 9.99999ZM13.304 2.99997H16.3302L15.5612 5.99998H12.9194L13.304 2.99997ZM12.7913 6.99998H15.3049L14.633 9.62109C14.5759 9.84422 14.3716 9.99999 14.1353 9.99999H12.4068L12.7913 6.99998ZM12.2707 2.99997L11.8862 5.99998H8.68975L8.30523 2.99997H12.2707ZM8.1692 9.99999H6.05128L5.09087 6.99998H7.78468L8.1692 9.99999ZM7.27199 2.99997L7.65651 5.99998H4.77068L3.81028 2.99997H7.27199Z" fill="white"/>
</svg>
 {{ trans('public.add_to_cart') }}
                        @endif
                    </button>
                @else
                    @if($course->slug == 'learn-free-vedic-astrology-course-online' )
                    <a href="/register-free" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                    @else
                    <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                    @endif
                @endif
               
            </div>

        </form>

                       

                        
                    

                    
                </div>
                          
                </div>
            </div>
        </div>
    </div>
</div>
