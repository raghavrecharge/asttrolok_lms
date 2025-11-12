<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Cashback\CashbackAccounting;
use App\Models\Accounting;
use App\Models\BecomeInstructor;
use App\Models\Cart;
use App\Models\Api\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ReserveMeeting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Webinar;
use App\Models\Discount;
use App\Models\Sale;
use App\Models\TicketUser;
use App\Models\SubscriptionPayments;
use App\Models\SubscriptionAccess;
use App\PaymentChannels\ChannelManager;
use Illuminate\Http\Request;
use App\Models\OrderAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Web\CartController;
use Illuminate\Support\Facades\Cookie;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderAttachment;
use App\Models\InstallmentOrderPayment;
use App\Http\Controllers\Web\InstallmentsController;
use Jenssegers\Agent\Agent;
use App\Jobs\BuyNowProcessJob;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use App\Models\TransactionsHistoryRazorpay;
use App\Helpers\LocationHelper;
use Illuminate\Support\Facades\DB;
class PaymentController_old extends Controller
{
    protected $order_session_key = 'payment.order_id';

    public function paymentRequest(Request $request)
    {
        
       
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'number' => 'required',
            'gateway' => 'required'
        ]);
      
        $name = $request->input('name');
        $email = $request->input('email');
        $number = $request->input('number');
        $gateway = $request->input('gateway');
        $orderId = $request->input('order_id');
        $webinarId = $request->input('webinar_id');
        $installmentId = $request->input('installment_id');
        $itemId = $request->get('item');
        $itemType = $request->get('item_type');
        $price = $request->input('price');
        
        
        if($gateway=='27' && $installmentId){
             $paymentChannel = PaymentChannel::where('id', $gateway)
            ->where('status', 'active')
            ->first();

            $intallments= new InstallmentsController();
              $item = $intallments->getItem($itemId, $itemType, null);
             $channelManager = ChannelManager::makeChannel($paymentChannel);
             
            $redirect_url = $channelManager->paymentRequest1($request);

            if (in_array($paymentChannel->class_name, PaymentChannel::$gatewayIgnoreRedirect)) {
                return $redirect_url;
            }

            return Redirect::away($redirect_url);
        }
        
        $user = auth()->user();
        if($user){
        $uid1=$user->id;
        $uid12=$user->email;
        }
        
        if(empty(User::where('email', $email)->orwhere('mobile', $number)->first())){
            $user = User::create([
            'role_name' => 'user',
            'role_id' => 1,
            'mobile' => $number ?? null,
            'email' => $email ?? null,
            'full_name' => $name,
            // 'status' => User::$pending,
            'status'=>'active',
            'access_content' => 1,
            'password' => Hash::make(123456),
            'pwd_hint' => 123456,
            'affiliate' => 0,
            'timezone' => 'Asia/Kolkata' ?? null,
            'created_at' => time()
        ]);
        }else{
             $user = User::where('email', $email)->orwhere('mobile', $number)->first();
        }
        
       
         if($user){
        $uid1=$user->id;
        $uid12=$user->email;
        }
        
        Accounting::where('user_id', $uid1)
            ->update([
                'user_id' => $user->id,
            ]);
            
        ReserveMeeting::where('user_id', $uid1)
            ->update([
                'user_id' => $user->id,
            ]);
            
        Order::where('id', $orderId)
            ->update([
                'user_id' => $user->id,
            ]);
            
            OrderItem::where('order_id', $orderId)
            ->update([
                'user_id' => $user->id,
            ]);
            
        // $u1->update(['full_name' => "mayank yadav"]);
        // session()->get('consult1')='no';
        session()->put('consult1',"no");
        session()->put('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d',$user->id);
        
        
        $data123=str_split($uid12,4);
        // print_r($data);
        
        if(in_array("demo", $data123)){

        User::where('id', $uid1)->first()->delete();
        }
        
        // echo "user exist";
        // }else{
        //     // echo "user not exist";
            
            
        //     $user->update([
        //         'role_name' => 'user',
        //         'role_id' => 1,
        //         'mobile' => $number ?? null,
        //         'email' => $email ?? null,
        //         'full_name' => $name,
        //         // 'status' => User::$pending,
        //         'status'=>'active',
        //         'access_content' => 1,
        //         'password' => Hash::make(123456),
        //         'affiliate' => 0,
        //         'timezone' => 'Asia/Kolkata' ?? null,
        //         'created_at' => time()
        //         ]);
            
            
            
            
        // }
      
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();
            
        //  print_r( $request->session());
            
        //       die();

        if ($order->type === Order::$meeting) {
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
            $reserveMeeting->update(['locked_at' => time()]);
        }

        if ($gateway === 'credit') {

            if ($user->getAccountingCharge() < $order->total_amount) {
                $order->update(['status' => Order::$fail]);

                session()->put($this->order_session_key, $order->id);

                return redirect('/payments/status');
            }

            $order->update([
                'payment_method' => Order::$credit
            ]);

            $this->setPaymentAccounting($order, 'credit');

            $order->update([
                'status' => Order::$paid
            ]);

            session()->put($this->order_session_key, $order->id);
            

            return redirect('/payments/status');
        }

        $paymentChannel = PaymentChannel::where('id', $gateway)
            ->where('status', 'active')
            ->first();

        if (!$paymentChannel) {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('public.channel_payment_disabled'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        $order->payment_method = Order::$paymentChannel;
        $order->save();


        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);
            $redirect_url = $channelManager->paymentRequest($order);

            if (in_array($paymentChannel->class_name, PaymentChannel::$gatewayIgnoreRedirect)) {
                return $redirect_url;
            }

            return Redirect::away($redirect_url);

        } catch (\Exception $exception) {

            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }

    public function paymentVerify(Request $request, $gateway)
    {
        // if($webinarId)
        
        // print_r($request->all());
        // die();
        
       if(!($request->input('razorpay_payment_id'))){
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => 'your transaction could not be completed.',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        
        $input = $request->all();
        $user = auth()->user();
            
        if(empty($user)){
            
            $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();
               
            if(empty($user)){
             $user = User::create([
            'role_name' => 'user',
            'role_id' => 1,
            'mobile' => $input['number'],
            'email' => $input['email'],
            'full_name' => $input['name'],
            // 'status' => User::$pending,
            'status'=>'active',
            'access_content' => 1,
            'password' => Hash::make(123456),
            'pwd_hint' => 123456,
            'affiliate' => 0,
            'timezone' => 'Asia/Kolkata' ?? null,
            'created_at' => time()
           ]);
            }
           //$user = User::where('id','1661')->first();
        }
        
        // $calculate =[];
        $orderId = $request->input('order_id');
        $webinar_id = $request->input('webinar_id');
        // $sub_total = $request->input('sub_total');
        // $totalAmount = $request->input('totalAmount');
        // $total_discount = $request->input('total_discount');
        // $discountCoupon=$request->input('discountCoupon');
        // $tax_price = $request->input('tax_price');
        // $product_delivery_fee = $request->input('product_delivery_fee');
        
         
        //   print_r(session('cart_id'));die;
        $paymentChannel = PaymentChannel::where('class_name', $gateway)
            ->where('status', 'active')
            ->first();
            if($orderId == 1){
                // print_r($webinar_id);
                $item = Webinar::where('id', $webinar_id)
                ->where('status', 'active')
                ->first();
        // die('hi');
        $itemPrice = $item->getPrice();
            $price = $item->price;
            if(!empty(session('discountCouponId'))){
            $discountId=session('discountCouponId');
            $discountCoupon = Discount::where('id', $discountId)->first();
             $percent = $discountCoupon->percent ?? 1;
            $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
            $itemPrice1=$itemPrice-$totalDiscount;
            }else{
                $totalDiscount = 0;
                $itemPrice1=$itemPrice-$totalDiscount;
            }
        
    

        
        $order_main_table = Order::create([
                        'user_id' => $user->id,
                        'status' => Order::$paying,
                        'amount' => $itemPrice,
                        'tax' => 0,
                        'total_discount' => $totalDiscount,
                        'total_amount' => $itemPrice1,
                        'product_delivery_fee' => null,
                        'created_at' => time(),
                    ]);
                    
                    if(isset($discountId))
                     $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
            $discountCoupon = null;
        }
                    
            if($order_main_table){
                            
               $order_item = OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order_main_table->id,
                    'webinar_id' => $webinar_id ?? null,
                    'bundle_id' => null,
                    'product_id' =>  null,
                    'product_order_id' =>null,
                    'reserve_meeting_id' => null,
                    'subscribe_id' =>null,
                    'promotion_id' => null,
                    'gift_id' =>null,
                    'installment_payment_id' => $installmentPayment->id ?? null,
                    'ticket_id' => null,
                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                    'amount' =>  $itemPrice,
                    'total_amount' =>  $itemPrice1,
                    'tax' => 0,
                    'tax_price' => 0,
                    'commission' => 0,
                    'commission_price' => 0,
                    'product_delivery_fee' => 0,
                    'discount' => $totalDiscount,
                    'created_at' => time(),
                ]);  
                
                session()->put('order_id1', $order_main_table->id);
                session()->put('order_amount', $order_main_table->total_amount);
                }
                // $sales_account=new PaymentController();
            //   return $this->paymentOrderAfterVerify($order_main_table);
    
            }
            else{
                session()->put('order_id1', $orderId);
            }


       
            $channelManager = ChannelManager::makeChannel($paymentChannel);
               
            $order = $channelManager->verify($request);
             try {
            foreach ($order->orderItems as $orderItem) {
                
                if($orderItem->installment_payment_id){
                    $cart = Cart::where('installment_payment_id', $orderItem->installment_payment_id)
                    ->first();
                    if($cart->extra_amount != null){
                        OrderItem::where('id',$orderItem->id)
                        ->update(['total_amount'=>($orderItem->total_amount-$cart->extra_amount)]);
                        $orderItem->total_amount= $orderItem->total_amount-$cart->extra_amount;
                    }
                
                }
            }
            
//             echo "<pre>";
//             print_r($order);
// die("paymentVerify");

             return $this->paymentOrderAfterVerify($order);

        } catch (\Exception $exception) {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
             return redirect('cart')->with(['toast' => $toastData]);
            
            // return $exception;
        }
    }

    /*
     * | this methode only run for payku.result
     * */
    public function paykuPaymentVerify(Request $request, $id)
    {
        $paymentChannel = PaymentChannel::where('class_name', PaymentChannel::$payku)
            ->where('status', 'active')
            ->first();

        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);

            $request->request->add(['transaction_id' => $id]);

            $order = $channelManager->verify($request);

            return $this->paymentOrderAfterVerify($order);

        } catch (\Exception $exception) {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return redirect('cart')->with(['toast' => $toastData]);
        }
    }

    public function paymentOrderAfterVerify($order)
    {
        // echo $price;
        // die("paymentOrderAfterVerify");
        if (!empty($order)) {
            
 
            if ($order->status == Order::$paying) {
                $this->setPaymentAccounting($order);

                $order->update(['status' => Order::$paid]);
            }elseif ($order->status == 'part') {
                $this->setPaymentAccounting($order);
                $orderItem = OrderItem::where('order_id', $order->id)->first();
                $InstallmentOrderPayment = InstallmentOrderPayment :: where('id', $orderItem->installment_payment_id)->first();
// die($order->status);
if($order->total_amount >= $InstallmentOrderPayment->amount){
                $order->update(['status' => Order::$paid]);
                InstallmentOrderPayment :: where('id', $orderItem->installment_payment_id)->update([
                'status'=> 'paid']);
}
// print_r($order);die();
            } else {
                if ($order->type === Order::$meeting) {
                    $orderItem = OrderItem::where('order_id', $order->id)->first();

                    if ($orderItem && $orderItem->reserve_meeting_id) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();

                        if ($reserveMeeting) {
                            $reserveMeeting->update(['locked_at' => null]);
                        }
                    }
                }
            }

            session()->put($this->order_session_key, $order->id);


            return redirect('/payments/status');
        } else {
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
 die('failed');
            return redirect('cart')->with($toastData);
        }
    }

    public function setPaymentAccounting($order, $type = null)
    {  
        // die("setPaymentAccounting");
        
        
        // print_r($order);
        $cashbackAccounting = new CashbackAccounting();

        if ($order->is_charge_account) {
            Accounting::charge($order);

            $cashbackAccounting->rechargeWallet($order);
        } else {
            foreach ($order->orderItems as $orderItem) {
                
               
                $sale = Sale::createSales($orderItem, $order->payment_method);

                if (!empty($orderItem->reserve_meeting_id)) {
                    $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                    $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();
                    
                    // print_r($orderItem->user_id);
                    // print_r($orderItem->created_at);
                    date_default_timezone_set('Asia/Kolkata');
                    // print_r(date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at));
                    // print_r(date('m/d/Y H:i:s', $orderItem->reserveMeeting->end_at));
                    // print_r($orderItem->reserveMeeting->end_at);
                    // print_r($orderItem->reserveMeeting->paid_amount);
                    // print_r($orderItem->reserveMeeting->meeting->creator_id);
                    // print_r($orderItem->user->full_name);
                    // print_r($orderItem->user->mobile);
                    // print_r($orderItem->user->email);
                    // print_r($creater);
                    // print_r('1');
                    // die();
                    
                    
// // 	  $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZmMDYzMTA0MzU1MjY4NTUzNjUxMzAi_pc';
// // Collection object
// $webhookdata = [
//   'student_id' => $orderItem->user_id,
//   'student_name' => $orderItem->user->full_name,
//   'student_mobile' => $orderItem->user->mobile,
//   'student_email' => $orderItem->user->email,
//   'consultant_id' => $creater->id,
//   'consultant_name' => $creater->full_name,
//   'consultant_mobile' => $creater->mobile,
//   'consultant_email' => $creater->email,
//   'meeting_start_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at),
//   'meeting_end_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->end_at),
//   'paid_amount' => $orderItem->reserveMeeting->paid_amount,
//   'create_at' => date("Y/m/d H:i")
  
  
// ];
// // Initializes a new cURL session
// $webhookcurl = curl_init($webhookurl);
// // Set the CURLOPT_RETURNTRANSFER option to true
// curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);
// // Set the CURLOPT_POST option to true for POST request
// curl_setopt($webhookcurl, CURLOPT_POST, true);
// // Set the request data as JSON using json_encode function
// curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
// // Set custom headers for RapidAPI Auth and Content-Type header

// // Execute cURL request with all previous settings
// $webhookresponse = curl_exec($webhookcurl);
// // Close cURL session
// curl_close($webhookcurl);
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    // die();
                    $reserveMeeting->update([
                        'sale_id' => $sale->id,
                        'reserved_at' => time()
                    ]);
                    // mayank

                    $reserver = $reserveMeeting->user;

                    if ($reserver) {
                        $this->handleMeetingReserveReward($reserver);
                    }
                }

                if (!empty($orderItem->gift_id)) {
                    $gift = $orderItem->gift;

                    $gift->update([
                        'status' => 'active'
                    ]);

                    $gift->sendNotificationsWhenActivated($orderItem->total_amount);
                }

                if (!empty($orderItem->subscribe_id)) {
                    Accounting::createAccountingForSubscribe($orderItem, $type);
                } elseif (!empty($orderItem->promotion_id)) {
                    Accounting::createAccountingForPromotion($orderItem, $type);
                } elseif (!empty($orderItem->registration_package_id)) {
                    Accounting::createAccountingForRegistrationPackage($orderItem, $type);

                    if (!empty($orderItem->become_instructor_id)) {
                        BecomeInstructor::where('id', $orderItem->become_instructor_id)
                            ->update([
                                'package_id' => $orderItem->registration_package_id
                            ]);
                    }
                } elseif (!empty($orderItem->installment_payment_id)) {
                    Accounting::createAccountingForInstallmentPayment($orderItem, $type);

                    $this->updateInstallmentOrder($orderItem, $sale,$order->status);
                } else {
                    // webinar and meeting and product and bundle

                    Accounting::createAccounting($orderItem, $type);
                    
                    TicketUser::useTicket($orderItem);

                    if (!empty($orderItem->product_id)) {
                        $this->updateProductOrder($sale, $orderItem);
                    }
                }
            }

            // Set Cashback Accounting For All Order Items
            $cashbackAccounting->setAccountingForOrderItems($order->orderItems);
        }

        Cart::emptyCart($order->user_id);
    }

    public function payStatus(Request $request)
    {
        // die("payStatus");
        $orderId = $request->get('order_id', null);

        if (!empty(session()->get($this->order_session_key, null))) {
            $orderId = session()->get($this->order_session_key, null);
            session()->forget($this->order_session_key);
        }
           if(!empty($id=auth()->id())){
               
        $order = Order::where('id', $orderId)
            ->where('user_id', $id)
            ->first();
           }else{
              $order = Order::where('id', $orderId)
            ->first(); 
           }

        if (!empty($order)) {
            $data = [
                'pageTitle' => trans('public.cart_page_title'),
                'order' => $order,
            ];

            $agent = new Agent();
            if ($agent->isMobile()){
                return view(getTemplate().'.cart.status_pay', $data);
            }else{
                return view('web.default2'.'.cart.status_pay', $data);
            }
            // return view('web.default.cart.status_pay', $data);
        }
        
        return redirect('/panel');
    }

    private function handleMeetingReserveReward($user)
    {
        if ($user->isUser()) {
            $type = Reward::STUDENT_MEETING_RESERVE;
        } else {
            $type = Reward::INSTRUCTOR_MEETING_RESERVE;
        }

        $meetingReserveReward = RewardAccounting::calculateScore($type);

        RewardAccounting::makeRewardAccounting($user->id, $meetingReserveReward, $type);
    }

    private function updateProductOrder($sale, $orderItem)
    {
        Log::info('updateProductOrder');
        $product = $orderItem->product;

        $status = ProductOrder::$waitingDelivery;

        if ($product and $product->isVirtual()) {
            $status = ProductOrder::$success;
        }
        $user = auth()->user();
        if(empty($user)){
            
            ProductOrder::where('product_id', $orderItem->product_id)
            ->where(function ($query) use ($orderItem) {
                $query->where(function ($query) use ($orderItem) {
                    $query->whereNotNull('buyer_id');
                    $query->where('buyer_id', 2550);
                });

                $query->orWhere(function ($query) use ($orderItem) {
                    $query->whereNotNull('gift_id');
                    $query->where('gift_id', $orderItem->gift_id);
                });
            })
            ->update([
                'sale_id' => $sale->id,
                'status' => $status,
                'buyer_id' =>  $orderItem->user_id,
            ]);
            
        }else{

        ProductOrder::where('product_id', $orderItem->product_id)
            ->where(function ($query) use ($orderItem) {
                $query->where(function ($query) use ($orderItem) {
                    $query->whereNotNull('buyer_id');
                    $query->where('buyer_id', $orderItem->user_id);
                });

                $query->orWhere(function ($query) use ($orderItem) {
                    $query->whereNotNull('gift_id');
                    $query->where('gift_id', $orderItem->gift_id);
                });
            })
            ->update([
                'sale_id' => $sale->id,
                'status' => $status,
            ]);
}
        

        if ($product and $product->getAvailability() < 1) {
            $notifyOptions = [
                '[p.title]' => $product->title,
            ];
            sendNotification('product_out_of_stock', $notifyOptions, $product->creator_id);
        }
    }

    private function updateInstallmentOrder($orderItem, $sale ,$order_status)
    {
        // die("updateInstallmentOrder");
        $installmentPayment = $orderItem->installmentPayment;

        if (!empty($installmentPayment)) {
            $installmentOrder = $installmentPayment->installmentOrder;

            $installmentPayment->update([
                'sale_id' => $sale->id,
                'status' => $order_status == 'part'?$order_status:'paid',
            ]);

            /* Notification Options */
            $notifyOptions = [
                '[u.name]' => $installmentOrder->user->full_name,
                '[installment_title]' => $installmentOrder->installment->main_title,
                '[time.date]' => dateTimeFormat(time(), 'j M Y - H:i'),
                '[amount]' => handlePrice($installmentPayment->amount),
            ];

            if ($installmentOrder and $installmentOrder->status == 'paying' and $installmentPayment->type == 'upfront') {
                $installment = $installmentOrder->installment;

                if ($installment) {
                    if ($installment->needToVerify()) {
                        $status = 'pending_verification';

                        sendNotification("installment_verification_request_sent", $notifyOptions, $installmentOrder->user_id);
                        sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1); // Admin
                    } else {
                        $status = 'open';

                        sendNotification("paid_installment_upfront", $notifyOptions, $installmentOrder->user_id);
                    }

                    $installmentOrder->update([
                        'status' => $status
                    ]);

                    if ($status == 'open' and !empty($installmentOrder->product_id) and !empty($installmentOrder->product_order_id)) {
                        $productOrder = ProductOrder::query()->where('installment_order_id', $installmentOrder->id)
                            ->where('id', $installmentOrder->product_order_id)
                            ->first();

                        $product = Product::query()->where('id', $installmentOrder->product_id)->first();

                        if (!empty($product) and !empty($productOrder)) {
                            $productOrderStatus = ProductOrder::$waitingDelivery;

                            if ($product->isVirtual()) {
                                $productOrderStatus = ProductOrder::$success;
                            }

                            $productOrder->update([
                                'status' => $productOrderStatus
                            ]);
                        }
                    }
                }
            }


            if ($installmentPayment->type == 'step') {
                sendNotification("paid_installment_step", $notifyOptions, $installmentOrder->user_id);
                sendNotification("paid_installment_step_for_admin", $notifyOptions, 1); // For Admin
            }

        }
    }
    
     public function BuyNowProccess(Request $request, $gateway)
    {
       Log::info('BuyNowProccess testing', $request->all());
        $data = $request->all();
        $data['gateway'] =$gateway;
        try {
        
           if(!empty($data['razorpay_payment_id'])){
               
               // Fetch full payment data from Razorpay
            $api = new Api(env('RAZORPAY_API_KEY'), env('RAZORPAY_API_SECRET'));
            $payment = $api->payment->fetch($data['razorpay_payment_id']);
             $order = Order::find($data['order_id']); 
    
//           OrderAddress::create([
//     'order_id'      => $request->input('order_id'),
//     'RecipientName' => $request->input('name') ?? 'Guest',
//     'PhoneNumber'   => $request->input('number') ?? '0000000000',
//     'Address'       => $request->input('address'),
//     'StreetAddress' => $request->input('address'),
//     'City'          => $request->input('City'),
//     'StateProvince' => $request->input('StateProvince'),
//     'PostalCode'    => $request->input('pin_code'),
//     'Country'       => $request->input('Country'),
//     'message'       => $request->input('message'),
// ]);
            // Insert into your DB
            TransactionsHistoryRazorpay::create([
                'user_id' => auth()->id() ?? null,
                'name' => $data['name'] ?? 'Guest', // if you stored name in notes
                'number' => $payment->contact ?? null,
                'email' => $payment->email ?? null,
                'amount' => $payment->amount / 100 ?? null,
                'razorpay_payment_id' => $payment->id ?? null,
                'razorpay_description' => $payment->description ?? 'Razorpay Payment',
            ]);
           }
            
            if(!empty($data['razorpay_payment_id'])){
               
            BuyNowProcessJob::dispatch($data) ->delay(now());
             return redirect('/payment/success?payment_status=buyNonw');
             
           }else{
                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.gateway_error'),
                    'status' => 'error'
                ];
                return redirect('cart')->with($toastData);
           }
            
        } catch (Exception $e) {
    
            throw $e->getMessage();
        }
       
    }
    
    public function paymentVerifyBackgroundProccess($input)
    {
        Log::info('paymentVerifyBackgroundProccess');
        // return true;
 
        $user = auth()->user();
        if(empty($user)){
            
            $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();
               
            if(empty($user)){
             $user = User::create([
            'role_name' => 'user',
            'role_id' => 1,
            'mobile' => $input['number'],
            'email' => $input['email'],
            'full_name' => $input['name'],
            'status'=>'active',
            'access_content' => 1,
            'password' => Hash::make(123456),
            'pwd_hint' => 123456,
            'affiliate' => 0,
            'timezone' => 'Asia/Kolkata' ?? null,
            'created_at' => time()
           ]);
            }
        
        }
        if (!empty($input['Country'])) {  
                OrderAddress::create([
                    'order_id' => $input['order_id'],
                    'RecipientName' => $input['name'] ?? null,
                    'City' => $input['City'] ?? null,           
                    'StateProvince' => $input['StateProvince'] ?? null,  
                    'PostalCode' => $input['pin_code'] ?? null,      
                    'Country' => $input['Country'] ?? null,   
                    'PhoneNumber' => $input['number'] ?? null,
                    'Address' => $input['address'] ?? null,
                    'message' => $input['message'] ?? null,
                ]);
            }
            
       
        $orderId =$input['order_id'];
        if(!empty($input['webinar_id'])){
            $webinar_id = $input['webinar_id'];
        }
        if(!empty($input['subscription_id'])){
            $subscription_id = $input['subscription_id'];
        }
        $gateway = $input['gateway'];
        $paymentChannel = PaymentChannel::where('class_name', $gateway)
            ->where('status', 'active')
            ->first();
            if($orderId == 1){
                $item = Webinar::where('id', $webinar_id)
                ->where('status', 'active')
                ->first();
        $itemPrice = $item->getPrice();
            $price = $item->price;
            if(!empty(session('discountCouponId'))){
            $discountId=session('discountCouponId') ?? $input['discount_id'];
            $discountCoupon = Discount::where('id', $discountId)->first();
             $percent = $discountCoupon->percent ?? 1;
            $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
            $itemPrice1=$itemPrice-$totalDiscount;
            }else{
                 $discountId=$input['discount_id'];
                 if($discountId){
                     $discountCoupon = Discount::where('id', $discountId)->first();
                     $percent = $discountCoupon->percent ?? 1;
                     $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                     $itemPrice1=$itemPrice-$totalDiscount;
                 }else
                     {
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                 }
            }
        
        $order_main_table = Order::create([
                        'user_id' => $user->id,
                        'status' => Order::$paying,
                        'amount' => $itemPrice,
                        'tax' => 0,
                        'total_discount' => $totalDiscount,
                        'total_amount' => $itemPrice1,
                        'product_delivery_fee' => null,
                        'created_at' => time(),
                    ]);
                    
                    if(isset($discountId))
                     $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
            $discountCoupon = null;
        }
                    
            if($order_main_table){
                            
               $order_item = OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order_main_table->id,
                    'webinar_id' => $webinar_id ?? null,
                    'bundle_id' => null,
                    'product_id' =>  null,
                    'product_order_id' =>null,
                    'reserve_meeting_id' => null,
                    'subscribe_id' =>null,
                    'promotion_id' => null,
                    'gift_id' =>null,
                    'installment_payment_id' => $installmentPayment->id ?? null,
                    'ticket_id' => null,
                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                    'amount' =>  $itemPrice,
                    'total_amount' =>  $itemPrice1,
                    'tax' => 0,
                    'tax_price' => 0,
                    'commission' => 0,
                    'commission_price' => 0,
                    'product_delivery_fee' => 0,
                    'discount' => $totalDiscount,
                    'created_at' => time(),
                ]);  
                
                session()->put('order_id1', $order_main_table->id);
                $input['order_id'] =$order_main_table->id;
                }
             
            }
            
            if($orderId == 2){
                $item = Subscription::where('id', $subscription_id)
                ->where('status', 'active')
                ->first();
        $itemPrice = $item->getPrice();
            $price = $item->price;
            // if(!empty(session('discountCouponId'))){
            // $discountId=session('discountCouponId') ?? $input['discount_id'];
            // $discountCoupon = Discount::where('id', $discountId)->first();
            //  $percent = $discountCoupon->percent ?? 1;
            // $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
            // $itemPrice1=$itemPrice-$totalDiscount;
            // }else{
            //      $discountId=$input['discount_id'];
            //      if($discountId){
            //          $discountCoupon = Discount::where('id', $discountId)->first();
            //          $percent = $discountCoupon->percent ?? 1;
            //          $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
            //          $itemPrice1=$itemPrice-$totalDiscount;
            //      }else
            //          {
            //         $totalDiscount = 0;
            //         $itemPrice1=$itemPrice-$totalDiscount;
            //      }
            // }
        
        $order_main_table = Order::create([
                        'user_id' => $user->id,
                        'status' => Order::$paying,
                        'amount' => $itemPrice,
                        'tax' => 0,
                        'total_discount' => 0,
                        'total_amount' => $itemPrice,
                        'created_at' => time(),
                    ]);
                    
                    // if(isset($discountId))
                    //  $discountCoupon = Discount::where('id', $discountId)->first();

        // if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
        //     $discountCoupon = null;
        // }
                    
            if($order_main_table){
                            
               $order_item = OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order_main_table->id,
                    'webinar_id' => null,
                    'bundle_id' => null,
                    'product_id' =>  null,
                    'product_order_id' =>null,
                    'subscription_id' =>$subscription_id ?? null,
                    'reserve_meeting_id' => null,
                    'subscribe_id' =>null,
                    'promotion_id' => null,
                    'gift_id' =>null,
                    'installment_payment_id' =>null,
                    'ticket_id' => null,
                    'discount_id' =>  null,
                    'amount' =>  $itemPrice,
                    'total_amount' =>  $itemPrice,
                    'tax' => 0,
                    'tax_price' => 0,
                    'commission' => 0,
                    'commission_price' => 0,
                    'product_delivery_fee' => 0,
                    'discount' => 0,
                    'created_at' => time(),
                ]);  
                
                session()->put('order_id1', $order_main_table->id);
                $input['order_id'] =$order_main_table->id;
                }
             
            }

            $channelManager = ChannelManager::makeChannel($paymentChannel);
         
               
            $order = $channelManager->verifyBackgroundProccess($input);
           
            try {
            foreach ($order->orderItems as $orderItem) {
                
                if($orderItem->installment_payment_id){
                    $cart = Cart::where('installment_payment_id', $orderItem->installment_payment_id)
                    ->first();
                    if($cart->extra_amount != null){
                        OrderItem::where('id',$orderItem->id)
                        ->update(['total_amount'=>($orderItem->total_amount-$cart->extra_amount)]);
                        $orderItem->total_amount= $orderItem->total_amount-$cart->extra_amount;
                    }
                
                }
            }
             return $this->paymentOrderAfterVerifyBackgroundProccess($order);

        } catch (\Exception $exception) {
            // $toastData = [
            //     'title' => trans('cart.fail_purchase'),
            //     'msg' => trans('cart.gateway_error'),
            //     'status' => 'error'
            // ];
            //  return redirect('cart')->with(['toast' => $toastData]);
            
            return false;
        }
    }
    
    public function paymentOrderAfterVerifyBackgroundProccess($order)
    { 
        Log::info('paymentOrderAfterVerifyBackgroundProccess');
        
        if (!empty($order)) {
            
            if ($order->status == Order::$paying) {
                $this->setPaymentAccountingBackgroundProccess($order);

                $order->update(['status' => Order::$paid]);
            }elseif ($order->status == 'part') {
                $this->setPaymentAccountingBackgroundProccess($order);
                $orderItem = OrderItem::where('order_id', $order->id)->first();
                $InstallmentOrderPayment = InstallmentOrderPayment :: where('id', $orderItem->installment_payment_id)->first();

            if($order->total_amount >= $InstallmentOrderPayment->amount){
                            $order->update(['status' => Order::$paid]);
                            InstallmentOrderPayment :: where('id', $orderItem->installment_payment_id)->update([
                            'status'=> 'paid']);
            }

            } else {
                if ($order->type === Order::$meeting) {
                    $orderItem = OrderItem::where('order_id', $order->id)->first();

                    if ($orderItem && $orderItem->reserve_meeting_id) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();

                        if ($reserveMeeting) {
                            $reserveMeeting->update(['locked_at' => null]);
                        }
                    }
                }
            }

            session()->put($this->order_session_key, $order->id);


            return true;
        } else {
         
            return false;
        }
    }
    
     public function setPaymentAccountingBackgroundProccess($order, $type = null)
    {  Log::info('setPaymentAccountingBackgroundProccess');
       
        $cashbackAccounting = new CashbackAccounting();

        if ($order->is_charge_account) {
            Accounting::charge($order);

            $cashbackAccounting->rechargeWallet($order);
        } else {
            foreach ($order->orderItems as $orderItem) {
                
            //   if (empty($orderItem->bundle_id)) {
                $sale = Sale::createSales($orderItem, $order->payment_method);
            //   }
Log::info('For only createSales payment');
                if (!empty($orderItem->reserve_meeting_id)) {
                    $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                    $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();
                    date_default_timezone_set('Asia/Kolkata');
                   
                    $reserveMeeting->update([
                        'sale_id' => $sale->id,
                        'reserved_at' => time()
                    ]);
                    $reserver = $reserveMeeting->user;

                    if ($reserver) {
                        $this->handleMeetingReserveReward($reserver);
                    }
                }

                if (!empty($orderItem->gift_id)) {
                    $gift = $orderItem->gift;

                    $gift->update([
                        'status' => 'active'
                    ]);

                    $gift->sendNotificationsWhenActivated($orderItem->total_amount);
                }

                if (!empty($orderItem->subscribe_id)) {
                    Accounting::createAccountingForSubscribe($orderItem, $type);
                } elseif (!empty($orderItem->promotion_id)) {
                    Accounting::createAccountingForPromotion($orderItem, $type);
                } elseif (!empty($orderItem->registration_package_id)) {
                    Accounting::createAccountingForRegistrationPackage($orderItem, $type);

                    if (!empty($orderItem->become_instructor_id)) {
                        BecomeInstructor::where('id', $orderItem->become_instructor_id)
                            ->update([
                                'package_id' => $orderItem->registration_package_id
                            ]);
                    }
                } elseif (!empty($orderItem->installment_payment_id)) {
                    Accounting::createAccountingForInstallmentPayment($orderItem, $type);

                    $this->updateInstallmentOrder($orderItem, $sale,$order->status);
                } elseif (!empty($orderItem->bundle_id)) {
                    Log::info('For only bundle payment');
                    
                    // For only bundle payment
                    Accounting::createAccounting($orderItem, $type);
                    
                    TicketUser::useTicket($orderItem);
                    
                    // For bundle has product
                    
                    if($orderItem->bundle->bundleWebinars){
                        // Log::info('OrderItem',[$orderItem->bundle->bundleWebinars]);
                        Log::info('For only bundleWebinars payment');
                        foreach ($orderItem->bundle->bundleWebinars as $bundleWebinar){
                            if($bundleWebinar->product_id){
                                Log::info('For only product payment');
                                $product = $bundleWebinar->product;
                                
                                $productOrder = ProductOrder::updateOrCreate([
                                    'product_id' => $product->id,
                                    'seller_id' => $product->creator_id,
                                    'buyer_id' => $orderItem->user_id,
                                    'bundle_id' => $orderItem->bundle_id,
                                    'sale_id' => null,
                                    'status' => 'pending',
                                ], [
                                    'specifications' => null,
                                    'quantity' => 1,
                                    'discount_id' => null,
                                    'created_at' => time()
                                ]);
                                
                                Log::info('productOrder ceated');
                                
                                $order1 = Order::create([
                                    'user_id' => $orderItem->user_id,
                                    'status' => 'paid',
                                    'payment_method' => $order->payment_method,
                                    'amount' => 0,
                                    'tax' => 0,
                                    'total_discount' => 0,
                                    'total_amount' => 0,
                                    'product_delivery_fee' => null,
                                    'created_at' => time(),
                                ]);
                                
                                $OrderItem1 = OrderItem::create([
                                    'user_id' => $orderItem->user_id,
                                    'order_id' => $order1->id,
                                    'webinar_id' => null,
                                    'bundle_id' =>  null,
                                    'product_id' => $product->id,
                                    'product_order_id' => $productOrder->id,
                                    'reserve_meeting_id' =>null,
                                    'subscribe_id' => null,
                                    'promotion_id' =>  null,
                                    'gift_id' => null,
                                    'installment_payment_id' =>  null,
                                    'ticket_id' =>  null,
                                    'discount_id' =>null,
                                    'amount' => 0,
                                    'total_amount' => 0,
                                    'tax' => 0,
                                    'tax_price' => 0,
                                    'commission' => 0,
                                    'commission_price' => 0,
                                    'product_delivery_fee' => 0,
                                    'discount' => 0,
                                    'created_at' => time(),
                                ]);
            
            
                                $seller_id = OrderItem::getSeller($orderItem);
                                $sale = Sale::create([
                                    'buyer_id' => $orderItem->user_id,
                                    'seller_id' => $seller_id,
                                    'order_id' => $OrderItem1->order_id,
                                    'webinar_id' => null,
                                    'bundle_id' => null,
                                    'meeting_id' => null,
                                    'meeting_time_id' =>  null,
                                    'subscribe_id' => null,
                                    'promotion_id' => null,
                                    'registration_package_id' => null,
                                    'product_order_id' => $productOrder->id,
                                    'installment_payment_id' => null,
                                    'status' =>  null,
                                    'gift_id' => null,
                                    'type' => 'product',
                                    'payment_method' => $order->payment_method,
                                    'amount' => 0,
                                    'tax' => 0,
                                    'via_payment' => null,
                                    'commission' => 0,
                                    'discount' => 0,
                                    'total_amount' => 0,
                                    'product_delivery_fee' => 0,
                                    'created_at' => time(),
                                ]);
                                Log::info('sale created');
                                $status = ProductOrder::$waitingDelivery;

                                if ($product and $product->isVirtual()) {
                                    $status = ProductOrder::$success;
                                }
                        
                                ProductOrder::where('id', $productOrder->id)
                                    ->where(function ($query) use ($orderItem) {
                                        $query->where(function ($query) use ($orderItem) {
                                            $query->whereNotNull('buyer_id');
                                            $query->where('buyer_id', $orderItem->user_id);
                                        });
                                    })
                                    ->update([
                                        'sale_id' => $sale->id,
                                        'status' => $status,
                                    ]);
            
                        
                                OrderAddress::where('order_id', $order->id)
                                    ->update([
                                        'order_id' => $order1->id,
                                    ]);
                                    
                                Log::info('Done');
                            }
                        }
                    }
                    // print_r($orderItem);
                    // die();
                    
                    // $this->updateProductOrder1($sale, $orderItem);
                    

                    // if (!empty($orderItem->product_id)) {
                    //     $this->updateProductOrder($sale, $orderItem);
                    // }
                } elseif (!empty($orderItem->subscription_id)) {
                    Log::info('For only subscription_id payment');
                    
                    // For only bundle payment
                    Accounting::createAccounting($orderItem, $type);
                    
                    SubscriptionPayments::create([
                        'user_id' => $orderItem->user_id,
                        'subscription_id' => $orderItem->subscription_id,
                        'amount' => $orderItem->total_amount,
                        'created_at' => time()
                        ]);
                        
                    $SubscriptionPayments = SubscriptionPayments::where('subscription_id' , $orderItem->subscription_id)
                                            ->where('user_id' , $orderItem->user_id)
                                            ->get();
                                            
                                            Log::info('SubscriptionPayments');
                        
                    $Subscription = Subscription::where('id' , $orderItem->subscription_id)
                                            ->first();
                                            
                                            Log::info('Subscription',$Subscription->toArray());
                                            
                    $SubscriptionAccess = SubscriptionAccess::where('subscription_id' , $orderItem->subscription_id)
                                            ->where('user_id' , $orderItem->user_id)
                                            ->first();
                                            
                        Log::info('SubscriptionAccess');
                                            
                    $access_till_date = time() + ($Subscription->access_days * 24 * 60 * 60);
                    $paid_no_of_subscriptions = $SubscriptionPayments->count();
                    $access_content_count = $Subscription->video_count * $SubscriptionPayments->count();
                    
                    
                    Log::info('data');
                    
                    // print_r($access_till_date);
                    // print_r($access_content_count);
                    // print_r($paid_no_of_subscriptions);
                    
                    if(!empty($SubscriptionAccess->subscription_id)){
                        Log::info('if SubscriptionAccess');
                        $access_till_date1 = $SubscriptionAccess->access_till_date + ($Subscription->access_days * 24 * 60 * 60);
                        
                        $access_till_date = $access_till_date >= $access_till_date1 ? $access_till_date : $access_till_date1;
                        
                        $SubscriptionAccess->update([
                            'access_till_date' => $access_till_date,
                            'access_content_count' => $access_content_count,
                            'paid_no_of_subscriptions' => $paid_no_of_subscriptions
                            ]);
                        
                    }else{
                    Log::info('else SubscriptionAccess');
                        SubscriptionAccess::create([
                            'user_id' => $orderItem->user_id,
                            'subscription_id' => $Subscription->id,
                            'access_till_date' => $access_till_date,
                            'access_content_count' => $access_content_count,
                            'paid_no_of_subscriptions' => $paid_no_of_subscriptions,
                            'created_at' => time()
                            ]);
                        
                    }
                    
                    TicketUser::useTicket($orderItem);
                    
                    // For bundle has product
                    
                    // if($orderItem->bundle->bundleWebinars){
                    //     // Log::info('OrderItem',[$orderItem->bundle->bundleWebinars]);
                    //     Log::info('For only bundleWebinars payment');
                    //     foreach ($orderItem->bundle->bundleWebinars as $bundleWebinar){
                    //         if($bundleWebinar->product_id){
                    //             Log::info('For only product payment');
                    //             $product = $bundleWebinar->product;
                                
                    //             $productOrder = ProductOrder::updateOrCreate([
                    //                 'product_id' => $product->id,
                    //                 'seller_id' => $product->creator_id,
                    //                 'buyer_id' => $orderItem->user_id,
                    //                 'bundle_id' => $orderItem->bundle_id,
                    //                 'sale_id' => null,
                    //                 'status' => 'pending',
                    //             ], [
                    //                 'specifications' => null,
                    //                 'quantity' => 1,
                    //                 'discount_id' => null,
                    //                 'created_at' => time()
                    //             ]);
                                
                    //             Log::info('productOrder ceated');
                                
                    //             $order1 = Order::create([
                    //                 'user_id' => $orderItem->user_id,
                    //                 'status' => 'paid',
                    //                 'payment_method' => $order->payment_method,
                    //                 'amount' => 0,
                    //                 'tax' => 0,
                    //                 'total_discount' => 0,
                    //                 'total_amount' => 0,
                    //                 'product_delivery_fee' => null,
                    //                 'created_at' => time(),
                    //             ]);
                                
                    //             $OrderItem1 = OrderItem::create([
                    //                 'user_id' => $orderItem->user_id,
                    //                 'order_id' => $order1->id,
                    //                 'webinar_id' => null,
                    //                 'bundle_id' =>  null,
                    //                 'product_id' => $product->id,
                    //                 'product_order_id' => $productOrder->id,
                    //                 'reserve_meeting_id' =>null,
                    //                 'subscribe_id' => null,
                    //                 'promotion_id' =>  null,
                    //                 'gift_id' => null,
                    //                 'installment_payment_id' =>  null,
                    //                 'ticket_id' =>  null,
                    //                 'discount_id' =>null,
                    //                 'amount' => 0,
                    //                 'total_amount' => 0,
                    //                 'tax' => 0,
                    //                 'tax_price' => 0,
                    //                 'commission' => 0,
                    //                 'commission_price' => 0,
                    //                 'product_delivery_fee' => 0,
                    //                 'discount' => 0,
                    //                 'created_at' => time(),
                    //             ]);
            
            
                    //             $seller_id = OrderItem::getSeller($orderItem);
                    //             $sale = Sale::create([
                    //                 'buyer_id' => $orderItem->user_id,
                    //                 'seller_id' => $seller_id,
                    //                 'order_id' => $OrderItem1->order_id,
                    //                 'webinar_id' => null,
                    //                 'bundle_id' => null,
                    //                 'meeting_id' => null,
                    //                 'meeting_time_id' =>  null,
                    //                 'subscribe_id' => null,
                    //                 'promotion_id' => null,
                    //                 'registration_package_id' => null,
                    //                 'product_order_id' => $productOrder->id,
                    //                 'installment_payment_id' => null,
                    //                 'status' =>  null,
                    //                 'gift_id' => null,
                    //                 'type' => 'product',
                    //                 'payment_method' => $order->payment_method,
                    //                 'amount' => 0,
                    //                 'tax' => 0,
                    //                 'via_payment' => null,
                    //                 'commission' => 0,
                    //                 'discount' => 0,
                    //                 'total_amount' => 0,
                    //                 'product_delivery_fee' => 0,
                    //                 'created_at' => time(),
                    //             ]);
                    //             Log::info('sale created');
                    //             $status = ProductOrder::$waitingDelivery;

                    //             if ($product and $product->isVirtual()) {
                    //                 $status = ProductOrder::$success;
                    //             }
                        
                    //             ProductOrder::where('id', $productOrder->id)
                    //                 ->where(function ($query) use ($orderItem) {
                    //                     $query->where(function ($query) use ($orderItem) {
                    //                         $query->whereNotNull('buyer_id');
                    //                         $query->where('buyer_id', $orderItem->user_id);
                    //                     });
                    //                 })
                    //                 ->update([
                    //                     'sale_id' => $sale->id,
                    //                     'status' => $status,
                    //                 ]);
            
                        
                    //             OrderAddress::where('order_id', $order->id)
                    //                 ->update([
                    //                     'order_id' => $order1->id,
                    //                 ]);
                                    
                    //             Log::info('Done');
                    //         }
                    //     }
                    // }
                    // print_r($orderItem);
                    // die();
                    
                    // $this->updateProductOrder1($sale, $orderItem);
                    

                    // if (!empty($orderItem->product_id)) {
                    //     $this->updateProductOrder($sale, $orderItem);
                    // }
                } else {
                    // webinar and meeting and product and bundle

                    Accounting::createAccounting($orderItem, $type);
                    
                    TicketUser::useTicket($orderItem);

                    if (!empty($orderItem->product_id)) {
                        $this->updateProductOrder($sale, $orderItem);
                    }
                }
            }

            // Set Cashback Accounting For All Order Items
            $cashbackAccounting->setAccountingForOrderItems($order->orderItems);
        }

        Cart::emptyCart($order->user_id);
    }
    
    public function directaccess123456(Request $request)
      {
    // Form se data fetch karo
    $name = $request->input('name');
    $email = $request->input('email');
    $mobile = $request->input('mobile');
    $subscriptionId = 5;
    $orderId = 2;
    $discountId = $request->input('discount_id');
    
    // Get reference sale ID (4443 se data lena hai)
    $referenceSaleId = 4443;
    
    // Reference sale se data fetch karo
    $referenceSale = DB::table('sales')->where('buyer_id', $referenceSaleId)->first();
    
    
    // User find karo ya create karo by email
    
       $user = User::where('email',$email)->orwhere('mobile', $mobile)->first();
               
            if(empty($user)){
             $user = User::create([
            'role_name' => 'user',
            'role_id' => 1,
            'mobile' => $mobile,
            'email' => $email,
            'full_name' => $name,
            // 'status' => User::$pending,
            'status'=>'active',
            'access_content' => 1,
            'password' => Hash::make(123456),
            'pwd_hint' => 123456,
            'affiliate' => 0,
            'timezone' => 'Asia/Kolkata' ?? null,
            'created_at' => time()
           ]);
            }
   
    
    $buyerId = $user->id;
    
    // Current timestamp
    $currentTimestamp = time();
    
    // Calculate access_till_date (30 days from now)
    $accessTillDate = strtotime('+30 days', $currentTimestamp);
    
    try {
        DB::beginTransaction();
        
        // 1. Sales table mein insert karo
        $saleId = DB::table('sales')->insert([
            'seller_id' => $referenceSale->seller_id,
            'buyer_id' => $buyerId,
            'order_id' => $orderId,
            'webinar_id' => null,
            'remedy_id' => null,
            'bundle_id' => null,
            'meeting_id' => null,
            'meeting_time_id' => null,
            'subscribe_id' => null,
            'subscription_id' => $subscriptionId,
            'ticket_id' => null,
            'promotion_id' => null,
            'product_order_id' => null,
            'registration_package_id' => null,
            'installment_payment_id' => null,
            'status' => null,
            'gift_id' => null,
            'payment_method' => 'payment_channel',
            'type' => 'subscription',
            'via_payment' => null,
            'amount' => $referenceSale->amount,
            'tax' => '0.00',
            'commission' => '0.00',
            'discount' => $discountId ? $referenceSale->discount : '0.00',
            'total_amount' => $referenceSale->total_amount,
            'product_delivery_fee' => '0.00',
            'manual_added' => 0,
            'access_to_purchased_item' => 1,
            'created_at' => $currentTimestamp,
            'refund_at' => null
        ]);
        
        // 2. Subscription_access table mein insert karo
        DB::table('subscription_access')->insert([
            'user_id' => $buyerId,
            'subscription_id' => $subscriptionId,
            'access_till_date' => $accessTillDate,
            'access_content_count' => 7,
            'paid_no_of_subscriptions' => 1,
            'created_at' => $currentTimestamp
        ]);;
        
        DB::commit();
        
        
        
        return redirect('/subscriptions/direct-payment1/asttrolok-pathshala')
                 ->with('success', 'Payment successful! Access granted.');

        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'error' => 'Error inserting data: ' . $e->getMessage()
        ], 500);
    }
}
    
}
