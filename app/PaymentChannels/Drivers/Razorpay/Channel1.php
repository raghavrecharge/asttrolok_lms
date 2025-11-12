<?php

namespace App\PaymentChannels\Drivers\Razorpay;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use Cassandra\Numeric;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Api\User;
use App\Models\OrderItem;
class Channel implements IChannel
{
    protected $currency;
    protected $api_key;
    protected $api_secret;

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->api_key = env('RAZORPAY_API_KEY');
        $this->api_secret = env('RAZORPAY_API_SECRET');
    }

    public function paymentRequest(Order $order)
    {

    }

    private function makeCallbackUrl(Order $order)
    {

    }

    public function verify(Request $request)
    {
        $input = $request->all();
        $orderId = $request->input('order_id');
        $user = auth()->user();
        if(empty($user)){
              $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();
                $orders = Order::where('id', $orderId)
            ->first();
             $orders->update(['user_id' => $user->id]);
             
              $OrderItem = OrderItem::where('order_id', $orderId)
            ->first();
             $OrderItem->update(['user_id' => $user->id]);
             
        }
        
    //   print_r( $user);die;
       
        $api = new Api($this->api_key, $this->api_secret);
        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->with('user')
            ->first();
  
        if (count($input) and !empty($input['razorpay_payment_id'])) {

            $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount']));

            if (!empty($order)) {
                if ($response['status'] == 'captured') {
                    
                    $order->update(['status' => Order::$paying]);
                } else {
                    $order->update(['status' => Order::$pending]);
                }

                $order->payment_method = Order::$paymentChannel;
                $order->save();

                return $order;
            }
        }

        return $order;
    }
}
