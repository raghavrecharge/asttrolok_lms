<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ReserveMeeting;
use App\Models\Ticket;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\UserSession;
use App\User;

class CartManagerController extends Controller
{
    public $cookieKey = 'carts';

    public function getCarts()
    {
        try {
            $carts = null;

            if (auth()->check()) {
                $user = auth()->user();

                $user->carts()
                    ->whereNotNull('product_order_id')
                    ->where(function ($query) {
                        $query->whereDoesntHave('productOrder');
                        $query->orWhereDoesntHave('productOrder.product');
                    })
                    ->delete();

                $carts = $user->carts()
                    ->with([
                        'webinar',
                        'bundle',
                        'installmentPayment',
                        'productOrder' => function ($query) {
                            $query->with(['product']);
                        }
                    ])
                    ->get();
            } else {

                 if (session('cart_id')) {

                 $carts = Cart::where('id',session('cart_id'))->orwhere('cart_id',session('cart_id'))->get();

                 }

            }

            return $carts;
        } catch (\Exception $e) {
            \Log::error('getCarts error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeCookieCartsToDB()
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();
                $carts = Cookie::get($this->cookieKey);

                if (!empty($carts)) {
                    $carts = json_decode($carts, true);

                    if (!empty($carts)) {
                        foreach ($carts as $cart) {
                            if (!empty($cart['item_name']) and !empty($cart['item_id'])) {

                                if ($cart['item_name'] == 'webinar_id') {
                                    $this->storeUserWebinarCart($user, $cart);
                                } elseif ($cart['item_name'] == 'product_id') {
                                    $this->storeUserProductCart($user, $cart);
                                } elseif ($cart['item_name'] == 'bundle_id') {
                                    $this->storeUserBundleCart($user, $cart);
                                }
                            }
                        }
                    }

                    Cookie::queue($this->cookieKey, null, 0);
                }
            }
        } catch (\Exception $exception) {

        }
    }

    public function storeUserWebinarCart($user, $data)
    {
        try {
            $webinar_id = $data['item_id'];
            $ticket_id = $data['ticket_id'] ?? null;

            $webinar = Webinar::where('id', $webinar_id)
                ->where('private', false)
                ->where('status', 'active')
                ->first();

            if (!empty($webinar) and !empty($user)) {
                $checkCourseForSale = checkCourseForSale($webinar, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                $activeSpecialOffer = $webinar->activeSpecialOffer();

                 if(empty(session('cart_id'))){

               $cart= Cart::Create([
                    'creator_id' => $user->id,
                    'webinar_id' => $webinar_id,
                    'ticket_id' => $ticket_id,
                    'special_offer_id' => !empty($activeSpecialOffer) ? $activeSpecialOffer->id : null,
                    'created_at' => time(),
                    'cart_id' => NULL
                ]);
                  session(['cart_id' => $cart->id]);
                }else{
                    $cart = Cart::where('cart_id', session('cart_id'))->where('webinar_id', $webinar_id)
                    ->first();
                    $cart1 = Cart::where('id', session('cart_id'))->where('webinar_id', $webinar_id)
                    ->first();
                     if(!empty($cart1)){

                        $toastData = [
                            'title' => 'add to cart',
                            'msg' => 'Course has already been added to cart',
                            'status' => 'warning'
                        ];
                        return back()->with(['toast' => $toastData]);

                    }
                    if(!empty($cart)){

                        $toastData = [
                            'title' => 'add to cart',
                            'msg' => 'Course has already been added to cart',
                            'status' => 'warning'
                        ];
                        return back()->with(['toast' => $toastData]);

                    }else{

                            $cart= Cart::Create([
                                'creator_id' => $user->id,
                                'webinar_id' => $webinar_id,
                                'ticket_id' => $ticket_id,
                                'special_offer_id' => !empty($activeSpecialOffer) ? $activeSpecialOffer->id : null,
                                'created_at' => time(),
                                'cart_id' => session('cart_id')
                            ]);
                    }

                }

                return 'ok';
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('cart.course_not_found'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('storeUserWebinarCart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeUserBundleCart($user, $data)
    {
        try {
            $bundle_id = $data['item_id'];
            $ticket_id = $data['ticket_id'] ?? null;

            $bundle = Bundle::where('id', $bundle_id)
                ->where('status', 'active')
                ->first();

            if (!empty($bundle) and !empty($user)) {
                $checkCourseForSale = checkCourseForSale($bundle, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                $activeSpecialOffer = $bundle->activeSpecialOffer();

                if(empty(session('cart_id'))){

               $cart= Cart::Create([
                    'creator_id' => $user->id,
                    'bundle_id' => $bundle_id,
                    'ticket_id' => $ticket_id,
                    'special_offer_id' => !empty($activeSpecialOffer) ? $activeSpecialOffer->id : null,
                    'created_at' => time(),
                    'cart_id' => NULL
                ]);
                  session(['cart_id' => $cart->id]);
                }else{
                    $cart = Cart::where('cart_id', session('cart_id'))->where('bundle_id', $bundle_id)
                    ->first();
                    $cart1 = Cart::where('id', session('cart_id'))->where('bundle_id', $bundle_id)
                    ->first();
                     if(!empty($cart1)){

                        $toastData = [
                            'title' => 'add to cart',
                            'msg' => 'Course has already been added to cart',
                            'status' => 'warning'
                        ];
                        return back()->with(['toast' => $toastData]);

                    }
                    if(!empty($cart)){

                        $toastData = [
                            'title' => 'add to cart',
                            'msg' => 'Course has already been added to cart',
                            'status' => 'warning'
                        ];
                        return back()->with(['toast' => $toastData]);

                    }else{

                            $cart= Cart::Create([
                                'creator_id' => $user->id,
                                'bundle_id' => $bundle_id,
                                'ticket_id' => $ticket_id,
                                'special_offer_id' => !empty($activeSpecialOffer) ? $activeSpecialOffer->id : null,
                                'created_at' => time(),
                                'cart_id' => session('cart_id')
                            ]);
                    }

                }
                return 'ok';
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('cart.course_not_found'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('storeUserBundleCart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeUserProductCart($user, $data)
    {
        try {
            $product_id = $data['item_id'];
            $specifications = $data['specifications'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            $product = Product::where('id', $product_id)
                ->where('status', 'active')
                ->first();

            if (!empty($product) and !empty($user)) {
                $checkProductForSale = checkProductForSale($product, $user);

                if ($checkProductForSale != 'ok') {
                    return $checkProductForSale;
                }

                $activeDiscount = $product->getActiveDiscount();

                $productOrder = ProductOrder::updateOrCreate([
                    'product_id' => $product->id,
                    'seller_id' => $product->creator_id,
                    'buyer_id' => $user->id,
                    'sale_id' => null,
                    'status' => 'pending',
                ], [
                    'specifications' => $specifications ? json_encode($specifications) : null,
                    'quantity' => $quantity,
                    'discount_id' => !empty($activeDiscount) ? $activeDiscount->id : null,
                    'created_at' => time()
                ]);

                if(empty(session('cart_id'))){

               $cart= Cart::Create([
                    'creator_id' => $user->id,
                    'product_order_id' => $productOrder->id,
                    'product_discount_id' => !empty($activeDiscount) ? $activeDiscount->id : null,
                    'created_at' => time(),
                    'cart_id' => NULL
                ]);
                  session(['cart_id' => $cart->id]);
                }else{
                    $cart = Cart::where('cart_id', session('cart_id'))->where('product_order_id', $productOrder->id)
                    ->first();
                    $cart1 = Cart::where('id', session('cart_id'))->where('product_order_id', $productOrder->id)
                    ->first();
                     if(!empty($cart1)){

                        $toastData = [
                            'title' => 'add to cart',
                            'msg' => 'Product has already been added to cart',
                            'status' => 'warning'
                        ];
                        return back()->with(['toast' => $toastData]);

                    }
                    if(!empty($cart)){

                        $toastData = [
                            'title' => 'add to cart',
                            'msg' => 'Product has already been added to cart',
                            'status' => 'warning'
                        ];
                        return back()->with(['toast' => $toastData]);

                    }else{

                            $cart= Cart::Create([
                                'creator_id' => $user->id,
                                'product_order_id' => $productOrder->id,
                                'product_discount_id' => !empty($activeDiscount) ? $activeDiscount->id : null,
                                'created_at' => time(),
                                'cart_id' => session('cart_id')
                            ]);
                    }

                }

                return 'ok';
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('cart.course_not_found'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('storeUserProductCart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeCookieCart($data)
    {
        try {
            $carts = Cookie::get($this->cookieKey);

            if (!empty($carts)) {
                $carts = json_decode($carts, true);
            } else {
                $carts = [];
            }

            $item_id = $data['item_id'];
            $item_name = $data['item_name'];

            if (empty($data['quantity'])) {
                $data['quantity'] = 1;
            }

            $carts[$item_name . '_' . $item_id] = $data;

            Cookie::queue($this->cookieKey, json_encode($carts), 30 * 24 * 60);
        } catch (\Exception $e) {
            \Log::error('storeCookieCart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if(empty($user)){
               $user = User::where('id','2550')->first();
            }

            $this->validate($request, [
                'item_id' => 'required',
                'item_name' => 'nullable',
            ]);

            $data = $request->except('_token');
            $item_name = $data['item_name'];

            if (!empty($user)) {
                $result = null;

                if ($item_name == 'webinar_id') {
                    $result = $this->storeUserWebinarCart($user, $data);
                } elseif ($item_name == 'product_id') {
                    $result = $this->storeUserProductCart($user, $data);
                } elseif ($item_name == 'bundle_id') {
                    $result = $this->storeUserBundleCart($user, $data);

                }

                if ($result != 'ok') {
                    return $result;
                }
            } else {

            }

            session(['addtocart' => 'popup']);
            $toastData = [
                'title' => trans('cart.cart_add_success_title'),
                'msg' => trans('cart.cart_add_success_msg'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            if (auth()->check()) {
                 $user_id = auth()->id();
            }else{
                $user = User::where('id','2550')->first();
                 $user_id =  $user->id;
            }

                $cart = Cart::where('id', $id)
                    ->where('creator_id', $user_id)
                    ->first();

                if (!empty($cart)) {
                    if (!empty($cart->reserve_meeting_id)) {
                        $reserve = ReserveMeeting::where('id', $cart->reserve_meeting_id)
                            ->where('user_id', $user_id)
                            ->first();

                        if (!empty($reserve)) {
                            $reserve->delete();
                        }
                    } elseif (!empty($cart->installment_payment_id)) {
                        $installmentPayment = $cart->installmentPayment;

                        if (!empty($installmentPayment) and $installmentPayment->status == 'paying') {
                            $installmentOrder = $installmentPayment->installmentOrder;

                            $installmentPayment->delete();

                            if (!empty($installmentOrder) and $installmentOrder->status == 'paying') {
                                $installmentOrder->delete();
                            }
                        }
                    }

                    $cart->delete();
                }

            return response()->json([
                'code' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
