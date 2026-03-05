<?php

namespace App\Http\Controllers\Api\Panel;
use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\ReserveMeeting;
use App\Models\Sale;
use App\Models\TicketUser;
use App\PaymentChannels\ChannelManager;
use App\Models\DiscountCourse;
use App\Models\Discount;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\URL;
use App\Jobs\BuyNowProcessJob;
use App\Models\Gift;

use App\Mixins\Cashback\CashbackAccounting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Web\CartController;
use Illuminate\Support\Facades\Cookie;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderAttachment;
use App\Models\InstallmentOrderPayment;
use App\Http\Controllers\Web\InstallmentsController;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;

class PaymentsController extends Controller
{
    protected $order_session_key;

    public function __construct()
    {
        $this->order_session_key = 'payment.order_id';
    }

    public function paymentByCredit(Request $request)
    {
        try {
            validateParam($request->all(), [
                'order_id' => ['required',
                Rule::exists('orders', 'id')->where('status', Order::$pending),

            ],
            ]);

            $user = apiAuth();
            $orderId = $request->input('order_id');

            $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

            if ($order->type === Order::$meeting) {
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
            $reserveMeeting->update(['locked_at' => time()]);
            }

            if ($user->getAccountingCharge() < $order->amount) {
            $order->update(['status' => Order::$fail]);

                return apiResponse2(0, 'not_enough_credit', trans('api.payment.not_enough_credit'));

            }

            $order->update([
                'payment_method' => Order::$credit
            ]);

            $this->setPaymentAccounting($order, 'credit');

            $order->update([
                'status' => Order::$paid
            ]);

            return apiResponse2(1, 'paid', trans('api.payment.paid'));
        } catch (\Exception $e) {
            \Log::error('paymentByCredit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function paymentRequest(Request $request)
    {
        $user = apiAuth();

        validateParam($request->all(), [
            'gateway_id' => ['required',
            Rule::exists('payment_channels', 'id')
        ],
        'order_id' => ['required',
        Rule::exists('orders', 'id')->where('status', Order::$pending)
        ->where('user_id', $user->id),

        ],

    ]);

    $gateway = $request->input('gateway_id');
    $orderId = $request->input('order_id');

    $order = Order::where('id', $orderId)
    ->where('user_id', $user->id)
    ->first();

     if ($order->type === Order::$meeting) {
        $orderItem = OrderItem::where('order_id', $order->id)->first();
                $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                $reserveMeeting->update(['locked_at' => time()]);
            }

        $paymentChannel = PaymentChannel::where('id', $gateway)
        ->where('status', 'active')
        ->first();

        if (!$paymentChannel) {
            return apiResponse2(0, 'disabled_gateway', trans('api.payment.disabled_gateway'));
        }

        $order->payment_method = Order::$paymentChannel;
        $order->save();

        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);
            $redirect_url = $channelManager->paymentRequest($order);

            if (in_array($paymentChannel->class_name, ['Paytm', 'Payu', 'Zarinpal', 'Stripe', 'Paysera', 'Cashu', 'Iyzipay', 'MercadoPago'])) {

             return   $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=&gM&orderId=ORDER12345";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $response = curl_exec($ch);
            curl_close($ch);

            return  $response;

                return $redirect_url;
            }
            return $redirect_url;
            return Redirect::away($redirect_url);

        } catch (\Exception $exception) {

            if (!$paymentChannel) {
                return apiResponse2(0, 'gateway_error', trans('api.payment.gateway_error'));
            }

        }
    }

   public function paymentVerifysss(Request $request)
{
    $user = apiAuth();
        validateParam($request->all(), [
                'gateway_id' => ['required',
                Rule::exists('payment_channels', 'id')
            ],
            'order_id' => ['required',
            Rule::exists('orders', 'id')->where('status', Order::$pending)
            ->where('user_id', $user->id),

            ],

        ]);

    $gateway = $request->input('gateway_id');
    $orderId = $request->input('order_id');

    $paymentChannel = PaymentChannel::where('id', $gateway)
        ->where('status', 'active')
        ->first();

    if (!$paymentChannel) {
        return response()->json([
            'error' => 'Payment gateway not found or inactive.'
        ], 404);
    }

    try {

        $channelManager = ChannelManager::makeChannel($paymentChannel);
        $order = $channelManager->verifyApi($request);

        if ($order) {
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $reserveMeeting = null;

            if ($orderItem && $orderItem->reserve_meeting_id) {
                $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
            }

            if ($order->status == Order::$paying) {
                $this->setPaymentAccounting($order);
                $order->update(['status' => Order::$paid]);
            } else {

                if ($order->type === Order::$meeting && $reserveMeeting) {
                    $reserveMeeting->update(['locked_at' => null]);
                }
            }

            return response()->json([
                'status' => 1,
                'message' => 'Cart payment successfully paid',
                'order_status' => 'Paid',
            ], 200);
        }

        return response()->json([
            'status' => 0,
            'message' => trans('cart.fail_purchase'),
            'error' => 'Payment gateway error',
        ], 400);

    } catch (\Exception $exception) {

        Log::error('Payment verification failed', ['error' => $exception->getMessage()]);

        return response()->json([
            'status' => 0,
            'message' => trans('cart.fail_purchase'),
            'error' => 'dublicate order entry',
            'details' => $exception->getMessage(),
        ], 409);
    }
}

  public function paymentVerify(Request $request)
    {
        $user = apiAuth();

        validateParam($request->all(), [
            'gateway_id' => [
                'required',
                Rule::exists('payment_channels', 'id')
            ],
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where(function ($query) use ($user) {
                    $query->where('status', Order::$pending)
                          ->where('user_id', $user->id);
                })
            ],
        ]);

        $gatewayId = $request->input('gateway_id');
        $orderId = $request->input('order_id');

        $paymentChannel = PaymentChannel::where('id', $gatewayId)
            ->where('status', 'active')
            ->first();

        if (!$paymentChannel) {
            return response()->json([
                'status' => false,
                'message' => 'Payment gateway not found or inactive.'
            ], 404);
        }

        try {
            $channelManager = ChannelManager::makeChannel($paymentChannel);

            $order = $channelManager->verifyApi($request);

            if ($order) {
                $orderItem = OrderItem::where('order_id', $order->id)->first();
                $reserveMeeting = null;

                if ($orderItem && $orderItem->reserve_meeting_id) {
                    $reserveMeeting = ReserveMeeting::find($orderItem->reserve_meeting_id);
                }

                if ($order->status == Order::$paying) {
                    $this->setPaymentAccounting($order);
                    $order->update(['status' => Order::$paid]);
                } else {
                    if ($order->type === Order::$meeting && $reserveMeeting) {
                        $reserveMeeting->update(['locked_at' => null]);
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Cart payment successfully paid',
                    'order_status' => 'Paid',
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => trans('cart.fail_purchase'),
                'error' => 'Payment verification failed or order mismatch',
            ], 422);

        } catch (\Exception $exception) {
            \Log::error('Payment verification failed', [
                'user_id' => $user->id,
                'error' => $exception->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => trans('cart.fail_purchase'),
                'error' => 'Duplicate or invalid order entry',
                'details' => app()->environment('production') ? 'Internal error' : $exception->getMessage(),
            ], 409);
        }
    }

    public function setPaymentAccounting($order, $type = null)
    {
        try {
            if ($order->is_charge_account) {
                Accounting::charge($order);
            } else {
                foreach ($order->orderItems as $orderItem) {
                    $sale = Sale::createSales($orderItem, $order->payment_method);

                    if (!empty($orderItem->reserve_meeting_id)) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                        $reserveMeeting->update([
                            'sale_id' => $sale->id,
                            'reserved_at' => time()
                        ]);
                    }

                    if (!empty($orderItem->subscribe_id)) {
                        Accounting::createAccountingForSubscribe($orderItem, $type);
                    } elseif (!empty($orderItem->promotion_id)) {
                        Accounting::createAccountingForPromotion($orderItem, $type);
                    } elseif (!empty($orderItem->installment_payment_id)) {
                        Accounting::createAccountingForInstallmentPayment($orderItem, $type);

                        $this->updateInstallmentOrder($orderItem, $sale,$order->status);
                    } else {

                        Accounting::createAccounting($orderItem, $type);
                        TicketUser::useTicket($orderItem);
                    }
                }
            }

            Cart::emptyCart($order->user_id);
        } catch (\Exception $e) {
            \Log::error('setPaymentAccounting error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function payStatus(Request $request)
    {
        try {
            $orderId = $request->get('order_id', null);

            if (!empty(session()->get($this->order_session_key, null))) {
                $orderId = session()->get($this->order_session_key, null);
                session()->forget($this->order_session_key);
            }

            $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

            if (!empty($order)) {
                $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'order' => $order,
                ];

                return view('web.default.cart.status_pay', $data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('payStatus error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function webChargeGenerator(Request $request)
    {
        try {
            return apiResponse2(1, 'generated', trans('api.link.generated'),
            [
                'link' => URL::signedRoute('my_api.web.charge', [apiAuth()->id])
                ]
            );
        } catch (\Exception $e) {
            \Log::error('webChargeGenerator error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function webChargeRender(User $user)
    {
        try {
            Auth::login($user);
            return redirect('/panel/financial/account');
        } catch (\Exception $e) {
            \Log::error('webChargeRender error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function charge(Request $request)
    {
        try {
            validateParam($request->all(), [
                'amount' => 'required|numeric',
                'gateway_id' => ['required',
                Rule::exists('payment_channels', 'id')->where('status', 'active')
                ]
                ,
            ]);

            $gateway_id = $request->input('gateway_id');
            $amount = $request->input('amount');

            $userAuth = apiAuth();

            $paymentChannel = PaymentChannel::find($gateway_id);

            $order = Order::create([
                'user_id' => $userAuth->id,
                'status' => Order::$pending,
                'payment_method' => Order::$paymentChannel,
                'is_charge_account' => true,
                'total_amount' => $amount,
                'amount' => $amount,
                'created_at' => time(),
                'type' => Order::$charge,
            ]);

            OrderItem::updateOrCreate([
                'user_id' => $userAuth->id,
                'order_id' => $order->id,
            ], [
                'amount' => $amount,
                'total_amount' => $amount,
                'tax' => 0,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'created_at' => time(),
            ]);

            if ($paymentChannel->class_name == 'Razorpay') {

                return response()->json([
                    'status' => 1,
                    'message' => 'Wallet Recharge',
                    'order_status' => 'pending',
                    'data' => json_decode($order),
                ], 200);
            } else {
                $paymentController = new PaymentsController();

                $paymentRequest = new Request();
                $paymentRequest->merge([
                    'gateway_id' => $paymentChannel->id,
                    'order_id' => $order->id
                ]);

                return $paymentController->paymentRequest($paymentRequest);
            }
        } catch (\Exception $e) {
            \Log::error('charge error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function echoRozerpayForm($order)
    {
        $generalSettings = getGeneralSettings();

        echo '<form action="/payments/verify/Razorpay" method="get">
        <input type="hidden" name="order_id" value="' . $order->id . '">

        <script src="/assets/default/js/app.js"></script>
            <script src="https://checkout.razorpay.com/v1/checkout.js"
            data-key="' . env('RAZORPAY_API_KEY') . '"
            data-amount="' . ((int) round((float) $order->total_amount, 0, PHP_ROUND_HALF_UP) * 100) . '"
            data-buttontext="product_price"
            data-description="Rozerpay"
            data-currency="' . currency() . '"
            data-image="' . $generalSettings['logo'] . '"
            data-prefill.name="' . $order->user->full_name . '"
            data-prefill.email="' . $order->user->email . '"
            data-theme.color="#43d477">
            </script>

            <style>
            .razorpay-payment-button {
                opacity: 0;
                visibility: hidden;
                }
            </style>

            <script>
                $(document).ready(function() {
                    $(".razorpay-payment-button").trigger("click");
                    })
                    </script>
                    </form>';
                    return '';
                }

    public function paymentOrderAfterVerify($order)
    {
        try {
            if (!empty($order)) {

                    if ($order->status == Order::$paying) {

                    $this->setPaymentAccounting($order);
                    $order->update(['status' => Order::$paid]);

                }elseif ($order->status == 'part') {
                    $this->setPaymentAccounting($order);
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
                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.gateway_error'),
                    'status' => 'error'
                ];
               return false;
            }
        } catch (\Exception $e) {
            \Log::error('paymentOrderAfterVerify error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
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

    private function updateInstallmentOrder($orderItem, $sale ,$order_status)
    {

        $installmentPayment = $orderItem->installmentPayment;

        if (!empty($installmentPayment)) {
            $installmentOrder = $installmentPayment->installmentOrder;

            $installmentPayment->update([
                'sale_id' => $sale->id,
                'status' => $order_status == 'part'?$order_status:'paid',
            ]);

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
                        sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);
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
                sendNotification("paid_installment_step_for_admin", $notifyOptions, 1);
            }

        }
    }

     public function BuyNowProccess(Request $request)
    {

        $data = $request->all();
        try {

           if(!empty($data['razorpay_payment_id'])){

            BuyNowProcessJob::dispatch($data) ->delay(now());
             return apiResponse2(1, 'success', 'order purchase is successfully submitted');

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

    public function BuyNowProccessApi(Request $request)
    {

         validateParam($request->all(), [
            'webinar_id' => 'required|numeric',
        ]);
        $input = $request->all();
        $user = apiAuth();
        $webinar_id = $input['webinar_id'];
        $gateway = $input['gateway'];
        $discount_id = $input['discount_id'] ?? 0;

        $item = Webinar::where('id', $webinar_id)
        ->where('status', 'active')
        ->first();
            if(empty($item)){
                return response()->json([
                'status' => 0,
                'message' => 'wabinar id not found',
                'data' => [],
            ], 200);
            }

        $itemPrice = $item->getPrice();
        $price = $item->price;

        if(!empty(session('discountCouponId'))){
        $discountId=session('discountCouponId') ?? $input['discount_id'];
        $discountCoupon = Discount::where('id', $discountId)->first();
         $percent = $discountCoupon->percent ?? 0;
        $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
        $itemPrice1=$itemPrice-$totalDiscount;
        }else{
             $discountId=$input['discount_id'];
             if($discountId){
                 $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 0;
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

                if ($gateway === 'credit') {

                if ($user->getAccountingCharge() < $order_main_table->total_amount) {
                    $order_main_table->update(['status' => Order::$fail]);

                    session()->put($this->order_session_key, $order_main_table->id);

                    return apiResponse2(0, 'failed', 'insufficient wallet amount');
                }

                $order_main_table->update([
                    'payment_method' => Order::$credit
                ]);

                $this->setPaymentAccounting($order_main_table, 'credit');

                $order_main_table->update([
                    'status' => Order::$paid
                ]);

                return response()->json([
                    'status' => 1,
                    'message' => 'payment successfully paid',
                    'order_status' => 'Paid',
                ], 200);
            }

            $paymentChannel = PaymentChannel::where('class_name', $gateway)
            ->where('status', 'active')
            ->first();

            $channelManager = ChannelManager::makeChannel($paymentChannel);

            $order = $channelManager->verifyApi1($input);

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
            $peyment111= $this->paymentOrderAfterVerifyBackgroundProccess($order);

             if($peyment111){
             return response()->json([
                'status' => 1,
                'message' => 'payment successfully paid',
                'order_status' => 'Paid',
            ], 200);
            }else{
                return response()->json([
                'status' => 0,
                'message' => 'payment Failed',
                'order_status' => 'Failed',
            ], 200);
            }

        } catch (\Exception $exception) {

             return response()->json([
                'status' => 0,
                'message' => 'payment Failed',
                'order_status' => 'Failed',
            ], 200);
            return false;
        }
    }
    public function directPayment(Request $request)
    {
        try {
            $user = apiAuth();
                $data = $request->except('_token');

                $discountCouponId=0;
            if (!empty($data['discountCouponId'])) {
                $discountCouponId = $data['discountCouponId'];
            }

                $webinarId = $data['item_id'];

                $webinar = Webinar::where('id', $webinarId)
                    ->where('private', false)
                    ->where('status', 'active')
                    ->first();

                $Discount = null;

                $item = $this->getItem($webinarId, 'course');

                $itemPrice = $item->getPrice();
                $price = $item->price;
                if($discountCouponId != 0){
                $discountId=$discountCouponId;
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 0;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                $paymentChannels = PaymentChannel::where('status', 'active')
            ->get();
                    $data1 = [

                        'paymentChannels' => $paymentChannels,

                        'totalDiscount' => $totalDiscount,

                        'discount' => $Discount ?? null,
                        'webinar' => $webinar,
                        'total' => $itemPrice1 ?? $itemPrice,
                    ];

            return apiResponse2(1, 'payning', $data1);
        } catch (\Exception $e) {
            \Log::error('directPayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
      public function getItem($itemId, $itemType)
    {
        try {
            if ($itemType == 'course') {
                $course = Webinar::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                    return $course;

            }

            return null;
        } catch (\Exception $e) {
            \Log::error('getItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

   public function payPartPaymentProccess(Request $request)
   {
        $data = $request->all();

        try {
            if (!empty($data['razorpay_payment_id'])) {
                Log::error('payPartPaymentProccess', $data);
                $response = $this->paymentVerifyBackgroundProccess($data);

                if ($response) {
                    return apiResponse2(1, 'success', 'order purchase is successfully submitted');
                }
            }

            return apiResponse2(0, trans('cart.fail_purchase'), trans('cart.gateway_error'));

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   public function paymentVerifyBackgroundProccess($input)
   {
                Log::error('paymentVerifyBackgroundProccess', $input);
    $user = apiAuth();
    $orderId = $input['order_id'] ?? null;
    $webinar_id = $input['webinar_id'] ?? null;
    $gateway = $input['gateway'] ?? null;

    $paymentChannel = PaymentChannel::where('class_name', $gateway)
        ->where('status', 'active')
        ->first();

    if ($orderId == 1) {
        $item = Webinar::where('id', $webinar_id)
            ->where('status', 'active')
            ->first();

        $itemPrice = $item->getPrice();
        $price = $item->price;
        $discountId = session('discountCouponId') ?? $input['discount_id'] ?? null;
        $discountCoupon = !empty($discountId) ? Discount::where('id', $discountId)->first() : null;

        $percent = $discountCoupon->percent ?? 0;
        $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
        $itemPrice1 = $itemPrice - $totalDiscount;

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

        if ($order_main_table) {
            $order_item = OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order_main_table->id,
                'webinar_id' => $webinar_id ?? null,
                'bundle_id' => null,
                'product_id' => null,
                'product_order_id' => null,
                'reserve_meeting_id' => null,
                'subscribe_id' => null,
                'promotion_id' => null,
                'gift_id' => null,
                'installment_payment_id' => $installmentPayment->id ?? null,
                'ticket_id' => null,
                'discount_id' => $discountId ?? ($discountCoupon ? $discountCoupon->id : null),
                'amount' => $itemPrice,
                'total_amount' => $itemPrice1,
                'tax' => 0,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'product_delivery_fee' => 0,
                'discount' => $totalDiscount,
                'created_at' => time(),
            ]);

            session()->put('order_id1', $order_main_table->id);
            $input['order_id'] = $order_main_table->id;
        }
    }

    $channelManager = ChannelManager::makeChannel($paymentChannel);
    $order = $channelManager->verifyBackgroundProccess($input);

Log::error('paymentVerifyBackgroundProccess', [$order]);
    try {
        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->installment_payment_id) {
                $cart = Cart::where('installment_payment_id', $orderItem->installment_payment_id)->first();
                if ($cart && $cart->extra_amount != null) {
                    OrderItem::where('id', $orderItem->id)
                        ->update(['total_amount' => ($orderItem->total_amount - $cart->extra_amount)]);
                    $orderItem->total_amount = $orderItem->total_amount - $cart->extra_amount;
                }
            }
        }

        return $this->paymentOrderAfterVerifyBackgroundProccess($order);

    } catch (\Exception $exception) {
        return false;
    }
}
   public function paymentOrderAfterVerifyBackgroundProccess($order)
   {
        try {
            if (!empty($order)) {
            if ($order->status == Order::$paying) {
                $this->setPaymentAccountingBackgroundProccess($order);
                $order->update(['status' => Order::$paid]);

            } elseif ($order->status == 'part') {
                $this->setPaymentAccountingBackgroundProccess($order);

                $orderItem = OrderItem::where('order_id', $order->id)->first();
                $installmentPayment = InstallmentOrderPayment::where('id', $orderItem->installment_payment_id)->first();

                if ($order->total_amount >= $installmentPayment->amount) {
                    $order->update(['status' => Order::$paid]);
                    $installmentPayment->update(['status' => 'paid']);
                }
            } else {
                if ($order->type === Order::$meeting) {
                    $orderItem = OrderItem::where('order_id', $order->id)->first();
                    if ($orderItem && $orderItem->reserve_meeting_id) {
                        $reserveMeeting = ReserveMeeting::find($orderItem->reserve_meeting_id);
                        if ($reserveMeeting) {
                            $reserveMeeting->update(['locked_at' => null]);
                        }
                    }
                }
            }

            return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('paymentOrderAfterVerifyBackgroundProccess error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function setPaymentAccountingBackgroundProccess($order, $type = null)
   {
        try {
            $cashbackAccounting = new CashbackAccounting();

            if ($order->is_charge_account) {
            Accounting::charge($order);
            $cashbackAccounting->rechargeWallet($order);
            } else {
            foreach ($order->orderItems as $orderItem) {
                $sale = Sale::createSales($orderItem, $order->payment_method);

                if (!empty($orderItem->reserve_meeting_id)) {
                    $reserveMeeting = ReserveMeeting::find($orderItem->reserve_meeting_id);
                    $creator = User::find($orderItem->reserveMeeting->meeting->creator_id);
                    $reserveMeeting->update(['sale_id' => $sale->id, 'reserved_at' => time()]);
                    $reserver = $reserveMeeting->user;
                    if ($reserver) {
                        $this->handleMeetingReserveReward($reserver);
                    }
                }

                if (!empty($orderItem->gift_id)) {
                    $gift = $orderItem->gift;
                    $gift->update(['status' => 'active']);
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
                            ->update(['package_id' => $orderItem->registration_package_id]);
                    }
                } elseif (!empty($orderItem->installment_payment_id)) {
                    Accounting::createAccountingForInstallmentPayment($orderItem, $type);
                    $this->updateInstallmentOrder($orderItem, $sale, $order->status);
                } else {
                    Accounting::createAccounting($orderItem, $type);
                    TicketUser::useTicket($orderItem);

                    if (!empty($orderItem->product_id)) {
                        $this->updateProductOrder($sale, $orderItem);
                    }
                }
            }

            $cashbackAccounting->setAccountingForOrderItems($order->orderItems);
            }

            Cart::emptyCart($order->user_id);
        } catch (\Exception $e) {
            \Log::error('setPaymentAccountingBackgroundProccess error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function verifyPayment(Request $request)
{
    $data1 = $request->all();

    if (!isset($data1['razorpay_payment_id'])) {
        return response()->json(['error' => 'Invalid payment details'], 400);
    }

       $gateway = $data1['gateway'];

        $paymentChannel = PaymentChannel::where('class_name', $gateway)
            ->where('status', 'active')
            ->first();

    try {

      $channelManager = ChannelManager::makeChannel($paymentChannel);

            $order = $channelManager->verifywalletPayment($data1);

        $order = Order::where('id', $data1['order_id'])->first();

        if (!empty($order)) {
            $order->update([
                'payment_method' => 'payment_channel',
                'status' => Order::$paid
            ]);

            $orderItem = OrderItem::where('order_id', $order->id)->first();
            if ($orderItem) {
                $sale = Sale::createSales($orderItem, $order->payment_method);
                Accounting::charge($order);
            }

           return response()->json([
                'status' => 1,
                'message' => 'Wallet balance updated successfully',
            ], 200);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Payment verification failed: ' . $e->getMessage()], 400);
    }
}
     public function invoice($webinarId, $saleId)
    {
        try {
            $user = apiAuth();

            $giftIds = Gift::query()
                ->where(function ($query) use ($user) {
                    $query->where('email', $user->email);
                    $query->orWhere('user_id', $user->id);
                })
                ->where('status', 'active')
                ->where('webinar_id', $webinarId)
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')->toArray();

            $sale = Sale::query()
                ->where('id', $saleId)
                ->where(function ($query) use ($webinarId, $user, $giftIds) {
                    $query->where(function ($query) use ($webinarId, $user) {
                        $query->where('buyer_id', $user->id);
                        $query->where('webinar_id', $webinarId);
                    });

                    if (!empty($giftIds)) {
                        $query->orWhereIn('gift_id', $giftIds);
                    }
                })
                ->whereNull('refund_at')
                ->with([
                    'order',
                    'buyer' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                ])
                ->first();

            if (!empty($sale)) {

                if (!empty($sale->gift_id)) {
                    $gift = $sale->gift;

                    $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
                }

                $webinar = Webinar::where('status', 'active')
                    ->where('id', $webinarId)
                    ->with([
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'creator' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'webinarPartnerTeacher' => function ($query) {
                            $query->with([
                                'teacher' => function ($query) {
                                    $query->select('id', 'full_name');
                                },
                            ]);
                        }
                    ])
                    ->first();

                if (!empty($webinar)) {
                    $data = [
                        'pageTitle' => trans('webinars.invoice_page_title'),
                        'sale' => $sale,
                        'webinar' => $webinar,
                        "address" => "312, 3rd Floor, Vikram Urbane, 25-A Mechanic Nagar Extn. Sch# 54, Indore(MP) 452010"
                    ];
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'invoice' => $data
                ]);

                }
            }

            return apiResponse2(0, 'invoice', 'Data Not Found');
        } catch (\Exception $e) {
            \Log::error('invoice error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}