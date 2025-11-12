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
use App\Models\Sale;
use App\Models\TicketUser;
use App\PaymentChannels\ChannelManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Web\CartController;
use Illuminate\Support\Facades\Cookie;

class PaymentController extends Controller
{
    protected $order_session_key = 'payment.order_id';

    public function paymentRequest(Request $request)
    {
        $this->validate($request, [
            'gateway' => 'required'
        ]);
        
        
          $name = $request->input('name');
         $email = $request->input('email');
         $number = $request->input('number');
        //  die();
         $gateway = $request->input('gateway');
        $orderId = $request->input('order_id');
        
        $user = auth()->user();
        if($user){
        $uid1=$user->id;
        $uid12=$user->email;
        }
        
        if(!empty(User::where('email', $email)->orwhere('mobile', $number)->first())){
        $user = User::where('email', $email)->orwhere('mobile', $number)->first();
        
        
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
        }else{
            // echo "user not exist";
            
            
            $user->update([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $number ?? null,
                'email' => $email ?? null,
                'full_name' => $name,
                // 'status' => User::$pending,
                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(123456),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time()
                ]);
            
        //     $user = User::create([
        //     'role_name' => 'user',
        //     'role_id' => 1,
        //     'mobile' => $number ?? null,
        //     'email' => $email ?? null,
        //     'full_name' => $name,
        //     // 'status' => User::$pending,
        //     'status'=>'active',
        //     'access_content' => 1,
        //     'password' => Hash::make(123456),
        //     'affiliate' => 0,
        //     'timezone' => 'Asia/Kolkata' ?? null,
        //     'created_at' => time()
        // ]);
            
            
        }
        // print_r($user);
        
        
        
      
         

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
            'password' => Hash::make($input['number']),
            'affiliate' => 0,
            'timezone' => 'Asia/Kolkata' ?? null,
            'created_at' => time()
           ]);
            }
           //$user = User::where('id','1661')->first();
        }
        
        
        // $calculate =[];
        $orderId = $request->input('order_id');
        // $webinar_id = $request->input('webinar_id');
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


        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);
               
            $order = $channelManager->verify($request);

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
        if (!empty($order)) {
 
            if ($order->status == Order::$paying) {
                $this->setPaymentAccounting($order);

                $order->update(['status' => Order::$paid]);
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
    {  // print_r($order);
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

                    $this->updateInstallmentOrder($orderItem, $sale);
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


            return view('web.default.cart.status_pay', $data);
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
        $product = $orderItem->product;

        $status = ProductOrder::$waitingDelivery;

        if ($product and $product->isVirtual()) {
            $status = ProductOrder::$success;
        }

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

        if ($product and $product->getAvailability() < 1) {
            $notifyOptions = [
                '[p.title]' => $product->title,
            ];
            sendNotification('product_out_of_stock', $notifyOptions, $product->creator_id);
        }
    }

    private function updateInstallmentOrder($orderItem, $sale)
    {
        $installmentPayment = $orderItem->installmentPayment;

        if (!empty($installmentPayment)) {
            $installmentOrder = $installmentPayment->installmentOrder;

            $installmentPayment->update([
                'sale_id' => $sale->id,
                'status' => 'paid',
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

}
