<?php

namespace App\PaymentChannels\Drivers\Stripe;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class Channel implements IChannel
{
    protected $currency;
    protected $api_key;
    protected $api_secret;
    protected $order_session_key;

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->api_key = env('STRIPE_KEY');
        $this->api_secret = env('STRIPE_SECRET');

        $this->order_session_key = 'strip.payments.order_id';
    }

    public function paymentRequest(Order $order)
    {
        $price = $order->total_amount;
        $generalSettings = getGeneralSettings();
        $currency = currency();

        Stripe::setApiKey($this->api_secret);

        $checkout = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount_decimal' => $price * 100,
                    'product_data' => [
                        'name' => $generalSettings['site_name'] . ' payment',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->makeCallbackUrl('success'),
            'cancel_url' => $this->makeCallbackUrl('cancel'),
        ]);

        /*$order->update([
            'reference_id' => $checkout->id,
        ]);*/

        session()->put($this->order_session_key, $order->id);

        $Html = '<script src="https://js.stripe.com/v3/"></script>';
        $Html .= '<script type="text/javascript">let stripe = Stripe("' . $this->api_key . '");';
        $Html .= 'stripe.redirectToCheckout({ sessionId: "' . $checkout->id . '" }); </script>';

        echo $Html;
    }
    public function paymentRequest1($request)
    {
        
        $price = $request->input('price');
        
        $generalSettings = getGeneralSettings();
        $currency = currency();

        Stripe::setApiKey($this->api_secret);

        $checkout = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount_decimal' => $price * 100,
                    'product_data' => [
                        'name' => $generalSettings['site_name'] . ' payment',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->makeCallbackUrl1('success',$request),
            'cancel_url' => $this->makeCallbackUrl1('cancel',$request),
        ]);

        /*$order->update([
            'reference_id' => $checkout->id,
        ]);*/

        // session()->put($this->order_session_key, $order->id);

        $Html = '<script src="https://js.stripe.com/v3/"></script>';
        $Html .= '<script type="text/javascript">let stripe = Stripe("' . $this->api_key . '");';
        $Html .= 'stripe.redirectToCheckout({ sessionId: "' . $checkout->id . '" }); </script>';

        echo $Html;
    }
    private function makeCallbackUrl($status)
    {
        return url("/payments/verify/Stripe?status=$status&session_id={CHECKOUT_SESSION_ID}");
    }
    
     private function makeCallbackUrl1($status,$request)
    {
        $name = $request->input('name');
         $email = $request->input('email');
         $number = $request->input('number');
        $gateway = $request->input('gateway');
        $installment_id = $request->input('installment_id');
         $discountId = $request->input('discountId');
        $itemId = $request->get('item');
        $itemType = $request->get('item_type');
        return url("/installments/".$installment_id."/store?status=$status&session_id={CHECKOUT_SESSION_ID}&name=$name&email=$email&number=$number&gateway=$gateway&installmentId=$installment_id&itemId=$itemId&itemType=$itemType&discountId= $discountId");
    }

    public function verify(Request $request)
    {
        $data = $request->all();
        $status = $data['status'];

        $order_id = session()->get($this->order_session_key, null);
        session()->forget($this->order_session_key);

        $user = auth()->user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();

        if ($status == 'success' and !empty($request->session_id) and !empty($order)) {
            Stripe::setApiKey($this->api_secret);

            $session = Session::retrieve($request->session_id);

            if (!empty($session) and $session->payment_status == 'paid') {
                $order->update([
                    'status' => Order::$paying
                ]);

                return $order;
            }
        }

        // is fail

        if (!empty($order)) {
            $order->update(['status' => Order::$fail]);
        }

        return $order;
    }
}
