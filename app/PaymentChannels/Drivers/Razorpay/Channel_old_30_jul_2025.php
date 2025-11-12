<?php

namespace App\PaymentChannels\Drivers\Razorpay;

use App\Models\Order;
use App\Models\TransactionHistory;
use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use Cassandra\Numeric;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Api\User;
use App\Models\OrderItem;
use App\Vbout\VboutService;
use Illuminate\Support\Facades\Log;

class Channel implements IChannel
{
    protected $vboutService;
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
        // $orderId = $request->input('order_id');
        $orderId = session('order_id1');
        session()->forget('order_id1');
        
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
            ->first();
            $order_item = OrderItem::where(
                    'order_id' , $order->id
                )->first();
            
            
  
        if (count($input) and !empty($input['razorpay_payment_id'])) {
//echo "<pre>";print_r($payment);die;
            $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount']));

            if (!empty($order)) {
                if ($response['status'] == 'captured') {
                    
                    $order->update(['status' => Order::$paying]);
                } else {
                    $order->update(['status' => Order::$pending]);
                }

                $order->payment_method = Order::$paymentChannel;
                $order->save();
                date_default_timezone_set('Asia/Kolkata');
                
                
                $description= isset($input['webinar_id']) ?$input['webinar_id']: (isset($order_item->webinar_id)?$order_item->webinar_id : (isset($order_item->installment_payment_id)?$order_item->installment_payment_id: 1) );
                 
                 $TransactionHistory = TransactionHistory::create([
		                    'user_id'	=> $user->id,
                    		'order_id'=> $orderId,
                    		'razorpay_order_id'	=> 0,
                    		'razorpay_payment_id'=> $input['razorpay_payment_id'],
                    		'transaction_type'	=> 'credit',
                    		'razorpay_signature'=> $input['razorpay_signature'] ?? null,	
                    		'amount' => $payment['amount']/100,
                    	    'currency'=> 'INR',
                    		'status'=> $order->status ==Order::$paying ? 'successful': 'failed',
                    	    'payment_method'=> 'UPI',
                    	    'international'	=> $payment['international'],
                    		'card_id'	=> $payment['card_id'],
                    		'bank'=> $payment['bank'],
                    		'wallet'=> $payment['wallet'],
                    		'vpa'	=> $payment['vpa'],
                    		'notes'=> $payment['notes']['item'] ?? ($order_item->installment_type != null?$order_item->installment_type:'installment'),
                    		'error_description'	=> $payment['error_description'],
                    		'error_source'	=> $payment['error_source'],
                    		'error_step'	=> $payment['error_step'],
                    		'error_reason'	=> $payment['error_reason'],
                    		'acquirer_data'=> $payment['acquirer_data']['rrn'],
                    		'description'	=> $description,
                    		'transaction_date'=> date("Y/m/d H:i:s"),
                    ]);
          if(empty($user->pwd_hint)){
              
                 
         try {
 $vboutService1 = new VboutService();
        $listId1 = '143046';
        
        $contactData1 = [
            'email' => $user->email,
            'fields' => [
            '941988' => $user->full_name,
             '941989' => $payment['description'],
              '941991' => $payment['amount']/100,
              '941992' => dateTimeFormat(time(), 'j M Y H:i'),
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

}
  } else{
             try {
 $vboutService1 = new VboutService();
        $listId1 = '146283';
        
        $contactData1 = [
            'email' => $user->email,
            'fields' => [
            '951553' => $user->full_name,
             '951554' => $payment['description'],
              '951555' => $payment['amount']/100,
              '951556' => dateTimeFormat(time(), 'j M Y H:i'),
              '951557' => $user->email,
              '951558' => $user->pwd_hint,
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

} 
  }
  
    $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/01b4e1c3-dd79-41a9-b383-cc485d4b917b';
// Collection object
$type= (isset($order_item->webinar_id)?'course payment':(isset($order_item->reserve_meeting_id)?'meeting payment':(isset($order_item->installment_type)?'part payment':(isset($order_item->installment_payment_id)?'installment payment':'null'))));
$itemid= (isset($order_item->webinar_id)?$order_item->webinar_id:(isset($order_item->reserve_meeting_id)?$order_item->reserve_meeting_id:(isset($order_item->installment_type)?$order_item->installment_type:(isset($order_item->installment_payment_id)?$order_item->installment_payment_id:'null'))));
$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'user_email' => $user->email,
  'user_role' => $user->role_name,
  'user_password' => $user->pwd_hint ?? 'null',
  'course' => $itemid,
  'description' => $payment['description'],
  'payment_type' => $type,
  'amount' => $payment['amount']/100,
  'create_at' => date("Y/m/d H:i")
];
                    
             
$gohighlevelcurl = curl_init($gohighlevel);
// Set the CURLOPT_RETURNTRANSFER option to true
curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);
// Set the CURLOPT_POST option to true for POST request
curl_setopt($gohighlevelcurl, CURLOPT_POST, true);
// Set the request data as JSON using json_encode function
// curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));
// Set custom headers for RapidAPI Auth and Content-Type header
curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json', // Ensure JSON data is being sent
    'Accept: application/json' // Accept JSON response if needed
]);
// Execute cURL request with all previous settings
$gohighlevelresponse = curl_exec($gohighlevelcurl); 

                return $order;
            }
        }

        return $order;
    }
    
     public function verifyBackgroundProccess($input)
    {
        
        $orderId =$input['order_id'];
        Log::info('i am in verifyBackgroundProccess function with orderid '.$orderId);
        
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
 
        $api = new Api($this->api_key, $this->api_secret);
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->with('user')
            ->first();
            $order_item = OrderItem::where('order_id', $orderId)
            ->first();
             $razorpay_signature =NULL;
            if(!empty($input['razorpay_signature'])){
                $razorpay_signature =$input['razorpay_signature'];
            }
            
           
            $description= isset($input['webinar_id']) ?$input['webinar_id']: (isset($order_item->webinar_id)?$order_item->webinar_id : (isset($order_item->installment_payment_id)?$order_item->installment_payment_id: 1) );
                 
        if (count($input) and !empty($input['razorpay_payment_id'])) {
            $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount']));

            if (!empty($order)) {
                if ($response['status'] == 'captured') {
                    if($order->status != 'part')
                    $order->update(['status' => Order::$paying]);
                } else {
                    $order->update(['status' => Order::$pending]);
                }

                $order->payment_method = Order::$paymentChannel;
                $order->save();
                date_default_timezone_set('Asia/Kolkata');
                 $TransactionHistory = TransactionHistory::create([
		                    'user_id'	=> $user->id,
                    		'order_id'=> $orderId,
                    		'razorpay_order_id'	=> 0,
                    		'razorpay_payment_id'=> $input['razorpay_payment_id'],
                    		'transaction_type'	=> 'credit',
                    		'razorpay_signature'=> $razorpay_signature,	
                    		'amount' => $payment['amount']/100,
                    	    'currency'=> 'INR',
                    		'status'=> $order->status ==Order::$paying ? 'successful': 'failed',
                    	    'payment_method'=> 'UPI',
                    	    'international'	=> $payment['international'],
                    		'card_id'	=> $payment['card_id'],
                    		'bank'=> $payment['bank'],
                    		'wallet'=> $payment['wallet'],
                    		'vpa'	=> $payment['vpa'],
                    		'notes'=> $payment['notes']['item'] ?? ($order_item->installment_type != null?$order_item->installment_type:'installment'),
                    		'error_description'	=> $payment['error_description'],
                    		'error_source'	=> $payment['error_source'],
                    		'error_step'	=> $payment['error_step'],
                    		'error_reason'	=> $payment['error_reason'],
                    		'acquirer_data'=> $payment['acquirer_data']['rrn'],
                    		'description'	=> $description,
                    		'transaction_date'=> date("Y/m/d H:i:s"),
                    ]);
                    
        if(empty($user->pwd_hint)){
              
                 
         try {
 $vboutService1 = new VboutService();
        $listId1 = '143046';
        
        $contactData1 = [
            'email' => $user->email,
            'fields' => [
            '941988' => $user->full_name,
             '941989' => $payment['description'],
              '941991' => $payment['amount']/100,
              '941992' => dateTimeFormat(time(), 'j M Y H:i'),
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

}
  } else{
             try {
 $vboutService1 = new VboutService();
        $listId1 = '146283';
        
        $contactData1 = [
            'email' => $user->email,
            'fields' => [
            '951553' => $user->full_name,
             '951554' => $payment['description'],
              '951555' => $payment['amount']/100,
              '951556' => dateTimeFormat(time(), 'j M Y H:i'),
              '951557' => $user->email,
              '951558' => $user->pwd_hint,
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

} 
  }
  $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();
  $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/01b4e1c3-dd79-41a9-b383-cc485d4b917b';
// Collection object
$type= (isset($order_item->webinar_id)?'course payment':(isset($order_item->reserve_meeting_id)?'meeting payment':(isset($order_item->installment_type)?'part payment':(isset($order_item->installment_payment_id)?'installment payment':'null'))));
$itemid= (isset($order_item->webinar_id)?$order_item->webinar_id:(isset($order_item->reserve_meeting_id)?$order_item->reserve_meeting_id:(isset($order_item->installment_type)?$order_item->installment_type:(isset($order_item->installment_payment_id)?$order_item->installment_payment_id:'null'))));
$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'user_email' => $user->email,
  'user_role' => $user->role_name,
  'user_password' => $user->pwd_hint ?? 'null',
  'course' => $itemid,
  'description' => $payment['description'],
  'payment_type' => $type,
  'amount' => $payment['amount']/100,
  'create_at' => date("Y/m/d H:i")
];
                    
             
$gohighlevelcurl = curl_init($gohighlevel);
// Set the CURLOPT_RETURNTRANSFER option to true
curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);
// Set the CURLOPT_POST option to true for POST request
curl_setopt($gohighlevelcurl, CURLOPT_POST, true);
// Set the request data as JSON using json_encode function
// curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));
// Set custom headers for RapidAPI Auth and Content-Type header
curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json', // Ensure JSON data is being sent
    'Accept: application/json' // Accept JSON response if needed
]);
// Execute cURL request with all previous settings
$gohighlevelresponse = curl_exec($gohighlevelcurl);  

                return $order;
            }
        }

        return $order;
    }
}
