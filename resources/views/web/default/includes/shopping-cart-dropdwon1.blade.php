<div class="dropdown dropdown-card" style="
    position: absolute;
    right: 0px;
    bottom: -12px;
    /* border: 0.1px solid black; */
    box-shadow: 0 5px 12px 0 rgba(0, 0, 0, 0.1);
">
    <button type="button" {{ (empty($userCarts) or count($userCarts) < 1) ? 'disabled' : '' }} class="btn btn-transparent dropdown-toggle" id="navbarShopingCart" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false" style="height: 30px ;">
        <i data-feather="shopping-cart" width="15" height="15" class="ml-5 mr-10 shoping-cart"></i>

        @if(!empty($userCarts) and count($userCarts))
         @if(!empty($webinar))
         @if(!empty($userCarts[0]['webinar']['id']))
                         @if($userCarts[0]['webinar']['id']==$webinar->id)
            <span class="badge badge-circle-primary d-flex align-items-center justify-content-center" style="
                position: absolute !important;
                right: 2px !important;
                bottom: -12px;
                top: 14px !important;
                font-size: 8px !important;
                padding: 0px !important;
                width: 12px;
                height: 12px;">
               
                {{ count($userCarts) }}
            
                </span>
                  @endif
                    @endif
              @else
                  <span class="badge badge-circle-primary d-flex align-items-center justify-content-center" style="
                position: absolute !important;
                right: 2px !important;
                bottom: -12px;
                top: 14px !important;
                font-size: 8px !important;
                padding: 0px !important;
                width: 12px;
                height: 12px;">
                  {{ count($userCarts) }}
                </span>
                @endif   
                
               
        @endif
    </button>

    <div class="dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class="d-md-none border-bottom mb-20 pb-10 text-right">
            <i class="close-dropdown" data-feather="x" width="32" height="32" class="mr-10"></i>
        </div>
        <div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar>
                @if(!empty($userCarts) and count($userCarts) > 0)
                    <div class="mb-auto">
                        @foreach($userCarts as $cart)
                            @php
                                $cartItemInfo = $cart->getItemInfo();
                            @endphp

                            @if(!empty($cartItemInfo))
                                <div class="navbar-cart-box d-flex align-items-center">

                                    <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank" class="navbar-cart-img">
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" alt="product title" class="img-cover"/>
                                    </a>

                                    <div class="navbar-cart-info">
                                        <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank">
                                            <h4>{{ $cartItemInfo['title'] }}</h4>
                                        </a>
                                        <div class="price mt-10">
                                            @if(!empty($cartItemInfo['discountPrice']))
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                                                <span class="off ml-15">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @else
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @endif

                                            @if(!empty($cartItemInfo['quantity']))
                                                <span class="font-12 text-warning font-weight-500 ml-10">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="navbar-cart-actions">
                        <div class="navbar-cart-total mt-15 border-top d-flex align-items-center justify-content-between">
                            <strong class="total-text">{{ trans('cart.total') }}</strong>
                            <strong class="text-primary font-weight-bold">{{ !empty($totalCartsPrice) ? handlePrice($totalCartsPrice, true, true, false, null, true) : 0 }}</strong>
                        </div>

                        <a href="/cart/" class="btn btn-sm btn-primary btn-block mt-50 mt-md-15">{{ trans('cart.go_to_cart') }}</a>
                    </div>
                @else
                    <div class="d-flex align-items-center text-center py-50">
                        <i data-feather="shopping-cart" width="20" height="20" class="mr-10 "></i>
                        <span class="">{{ trans('cart.your_cart_empty') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
