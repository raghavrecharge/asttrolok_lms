<?php

namespace App\PaymentChannels\Drivers\Razorpay;

use App\Models\Order;
use App\Models\TransactionHistory;
use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use App\Models\OrderAddress;
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
        // $orderId = session('order_id1');
        // session()->forget('order_id1');z
        
        
        $orderId = $request->input('order_id');
        if(!empty(session('order_id1'))){
            $orderId = session('order_id1');
            session()->forget('order_id1');
        } 
        
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
                    		'amount' => (int) round($payment['amount'] / 100, 0, PHP_ROUND_HALF_UP),
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
          if(empty('***REDACTED***')){
              
                 
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
              '951558' => '***REDACTED***',
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

} 
  }
  
    $gohighlevel= config('webhooks.gohighlevel.payment');
// Collection object
$type= (isset($order_item->webinar_id)?'course payment':(isset($order_item->reserve_meeting_id)?'meeting payment':(isset($order_item->installment_type)?'part payment':(isset($order_item->installment_payment_id)?'installment payment':'null'))));
$itemid= (isset($order_item->webinar_id)?$order_item->webinar_id:(isset($order_item->reserve_meeting_id)?$order_item->reserve_meeting_id:(isset($order_item->installment_type)?$order_item->installment_type:(isset($order_item->installment_payment_id)?$order_item->installment_payment_id:'null'))));
$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'user_email' => $user->email,
  'user_role' => $user->role_name,
  'user_password' => '***REDACTED***' ?? 'null',
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
    

    
   public function verifyApi(Request $request)
{
    // Initialize variables
    $input = $request->all();
   
    $orderId = $request->input('order_id');
    try {
        // Authenticate the user
        $user = apiAuth();
        if (empty($user)) {
            return response()->json(['error' => 'User authentication failed'], 401); // Unauthorized
        }

        // Fetch payment details using Razorpay API
        $api = new Api($this->api_key, $this->api_secret);
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        
        $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount']));
         

        // Check if payment is valid (either captured or authorized)
        if ($payment->status =='captured' || !in_array($payment->status, ['captured', 'authorized'])) {
            return response()->json(['error' => 'Invalid or unsuccessful payment'], 400); // Bad Request
        }

        // Fetch the order from the database
        $order = Order::where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404); // Not Found
        }

        // Fetch the associated order item
        $order_item = OrderItem::where('order_id', $order->id)->first();
        if (!$order_item) {
            return response()->json(['error' => 'Order item not found'], 404); // Not Found
        }
 $razorpay_payment = TransactionHistory::where('razorpay_payment_id', $input['razorpay_payment_id'])->first();
      
        if ($razorpay_payment) {
            return response()->json(['error' => 'Order item not found'], 404); // Not Found
        }
        // Process the payment and update order status
        $order->status = (in_array($payment->status, ['captured', 'authorized'])) ? Order::$paying : Order::$pending;
        $order->payment_method = Order::$paymentChannel;
        $order->save();

        // Create a new transaction history record
        $TransactionHistory = TransactionHistory::create([
            'user_id'              => $user->id,
            'order_id'             => $orderId,
            'razorpay_order_id'    => 0, // Use actual Razorpay order ID if available
            'razorpay_payment_id'  => $input['razorpay_payment_id'],
            'transaction_type'     => 'credit',
            'razorpay_signature'   => $input['razorpay_signature'] ?? null,
            'amount'               => $payment->amount / 100,  // Convert amount to INR (from paise)
            'currency'             => 'INR',
            'status'               => $order->status == Order::$paying ? 'successful' : 'failed',
            'payment_method'       => 'UPI',
            'international'        => $payment->international ?? false,
            'card_id'              => $payment->card_id ?? null,
            'bank'                 => $payment->bank ?? null,
            'wallet'               => $payment->wallet ?? null,
            'vpa'                  => $payment->vpa ?? $payment->upi->attributes['vpa'] ?? null,
            'error_description'    => $payment->error_description ?? null,
            'error_source'         => $payment->error_source ?? null,
            'error_step'           => $payment->error_step ?? null,
            'error_reason'         => $payment->error_reason ?? null,
            'acquirer_data'        => $payment->acquirer_data->attributes['rrn'] ?? null,
            'description'          => $input['webinar_id'] ?? $order_item->webinar_id ?? 1,
            'transaction_date'     => now(),
        ]);

        // Optional: Send data to external services like Vbout or GoHighLevel
        $this->sendToExternalServices($user, $payment, $order_item);

        // Return the payment details in a structured response
        $response = [
            'payment_id'           => $payment->id,
            'status'               => $payment->status,
            'amount'               => $payment->amount / 100,  // Convert from paise to INR
            'currency'             => $payment->currency,
            'payment_method'       => $payment->method,
            'description'          => $payment->description,
            'vpa'                  => $payment->vpa ?? $payment->upi->attributes['vpa'] ?? null,
            'contact'              => $payment->contact ?? null,
            'email'                => $payment->email ?? null,
            'created_at'           => date("Y-m-d H:i:s", $payment->created_at),
            'rrn'                  => $payment->acquirer_data->attributes['rrn'] ?? null,
        ];

        // Return success response with payment details
        // return response()->json([
        //     'message' => 'Transaction successfully processed',
        //     'order' => $order,
        //     'payment_details' => $response
        // ], 200); // OK
        return $order;
    } catch (\Exception $e) {
        // Log the error and return a server error response
        \Log::error('Error in verifyApi', ['error' => $e->getMessage()]);

        // return response()->json([
        //     'error' => 'An internal error occurred. Please try again later.',
        //     'details' => $e->getMessage()  // Optionally include error details for debugging
        // ], 500); // Internal Server Error
        return false;
    }
}

public function verifyApi1($input)
{
    // Initialize variables
    // $input = $request->all();
    $orderId = $input['order_id'];

    try {
        // Authenticate the user
        $user = apiAuth();
        if (empty($user)) {
            return response()->json(['error' => 'User authentication failed'], 401); // Unauthorized
        }

        // Fetch payment details using Razorpay API
        $api = new Api($this->api_key, $this->api_secret);
        $payment = $api->payment->fetch($input['razorpay_payment_id']); 

        // Check if payment is valid (either captured or authorized)
        if (!$payment || !in_array($payment->status, ['captured', 'authorized'])) {
            return response()->json(['error' => 'Invalid or unsuccessful payment'], 400); // Bad Request
        }

        // Fetch the order from the database
        $order = Order::where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404); // Not Found
        }

        // Fetch the associated order item
        $order_item = OrderItem::where('order_id', $order->id)->first();
        if (!$order_item) {
            return response()->json(['error' => 'Order item not found'], 404); // Not Found
        }
       

        // Process the payment and update order status
        $order->status = (in_array($payment->status, ['captured', 'authorized'])) ? Order::$paying : Order::$pending;
        $order->payment_method = Order::$paymentChannel;
        $order->save();

        // Create a new transaction history record
        $TransactionHistory = TransactionHistory::create([
            'user_id'              => $user->id,
            'order_id'             => $orderId,
            'razorpay_order_id'    => 0, // Use actual Razorpay order ID if available
            'razorpay_payment_id'  => $input['razorpay_payment_id'],
            'transaction_type'     => 'credit',
            'razorpay_signature'   => $input['razorpay_signature'] ?? null,
            'amount'               => $payment->amount / 100,  // Convert amount to INR (from paise)
            'currency'             => 'INR',
            'status'               => $order->status == Order::$paying ? 'successful' : 'failed',
            'payment_method'       => 'UPI',
            'international'        => $payment->international ?? false,
            'card_id'              => $payment->card_id ?? null,
            'bank'                 => $payment->bank ?? null,
            'wallet'               => $payment->wallet ?? null,
            'vpa'                  => $payment->vpa ?? $payment->upi->attributes['vpa'] ?? null,
            'error_description'    => $payment->error_description ?? null,
            'error_source'         => $payment->error_source ?? null,
            'error_step'           => $payment->error_step ?? null,
            'error_reason'         => $payment->error_reason ?? null,
            'acquirer_data'        => $payment->acquirer_data->attributes['rrn'] ?? null,
            'description'          => $input['webinar_id'] ?? $order_item->webinar_id ?? 1,
            'transaction_date'     => now(),
        ]);

        // Optional: Send data to external services like Vbout or GoHighLevel
        $this->sendToExternalServices($user, $payment, $order_item);

        // Return the payment details in a structured response
        $response = [
            'payment_id'           => $payment->id,
            'status'               => $payment->status,
            'amount'               => $payment->amount / 100,  // Convert from paise to INR
            'currency'             => $payment->currency,
            'payment_method'       => $payment->method,
            'description'          => $payment->description,
            'vpa'                  => $payment->vpa ?? $payment->upi->attributes['vpa'] ?? null,
            'contact'              => $payment->contact ?? null,
            'email'                => $payment->email ?? null,
            'created_at'           => date("Y-m-d H:i:s", $payment->created_at),
            'rrn'                  => $payment->acquirer_data->attributes['rrn'] ?? null,
        ];

        // Return success response with payment details
        // return response()->json([
        //     'message' => 'Transaction successfully processed',
        //     'order' => $order,
        //     'payment_details' => $response
        // ], 200); // OK
        return $order;
    } catch (\Exception $e) {
        // Log the error and return a server error response
        \Log::error('Error in verifyApi', ['error' => $e->getMessage()]);

        return response()->json([
            'error' => 'An internal error occurred. Please try again later.',
            'details' => $e->getMessage()  // Optionally include error details for debugging
        ], 500); // Internal Server Error
    }
}


private function sendToExternalServices($user, $payment, $order_item)
{
    try {
        // Example for VboutService integration
        $vboutService = new VboutService();
        $listId = empty('***REDACTED***') ? '143046' : '146283'; // Choose list ID based on condition
        $contactData = [
            'email' => $user->email,
            'fields' => [
                '941988' => $user->full_name,
                '941989' => $payment->description,
                '941991' => $payment->amount / 100,
                '941992' => now()->format('j M Y H:i'),
            ]
        ];

        $vboutService->addContactToList($listId, $contactData);
    } catch (\Exception $e) {
        \Log::error('Failed to send to Vbout service', ['error' => $e->getMessage()]);
    }

    try {
        // Send data to GoHighLevel (webhook)
        $gohighlevelUrl = config('webhooks.gohighlevel.payment');
        $type = isset($order_item->webinar_id) ? 'course payment' : (isset($order_item->reserve_meeting_id) ? 'meeting payment' : 'null');
        $webhookData = [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'user_mobile' => $user->mobile,
            'user_email' => $user->email,
            'course' => $payment->description,
            'payment_type' => $type,
            'amount' => $payment->amount / 100,
            'create_at' => now()->format('Y/m/d H:i')
        ];

        $this->sendWebhookRequest($gohighlevelUrl, $webhookData);
    } catch (\Exception $e) {
        \Log::error('Failed to send webhook to GoHighLevel', ['error' => $e->getMessage()]);
    }
}

private function sendWebhookRequest($url, $data)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        \Log::error('Webhook failed', ['error' => curl_error($curl)]);
    }
    curl_close($curl);
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
            
               if (!empty($input['Country'])) {  
                OrderAddress::create([
                    'order_id' => $order->id,
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
            
            \Log::info('orderAddress check ' . ($input['Country'] ?? 'city_id not set'));
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
                    		'amount' => (int) round($payment['amount'] / 100, 0, PHP_ROUND_HALF_UP),
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
                    
        if(empty('***REDACTED***')){
              
                 
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
              '951558' => '***REDACTED***',
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

} 
  }
  $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();
  $gohighlevel= config('webhooks.gohighlevel.payment');
// Collection object
$type= (isset($order_item->webinar_id)?'course payment':(isset($order_item->reserve_meeting_id)?'meeting payment':(isset($order_item->installment_type)?'part payment':(isset($order_item->installment_payment_id)?'installment payment':'null'))));
$itemid= (isset($order_item->webinar_id)?$order_item->webinar_id:(isset($order_item->reserve_meeting_id)?$order_item->reserve_meeting_id:(isset($order_item->installment_type)?$order_item->installment_type:(isset($order_item->installment_payment_id)?$order_item->installment_payment_id:'null'))));
$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'user_email' => $user->email,
  'user_role' => $user->role_name,
  'user_password' => '***REDACTED***' ?? 'null',
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
    
    public function verifyBackgroundProccessAPI($input)
   {
    $orderId = $input['order_id'];
    $user = auth()->user() ?? apiAuth();
    
    if (empty($user)) {
        $user = User::where('email', $input['email'])->orWhere('mobile', $input['number'])->first();
        if ($user) {
            Order::where('id', $orderId)->update(['user_id' => $user->id]);
            OrderItem::where('order_id', $orderId)->update(['user_id' => $user->id]);
        }
    }

    $order = Order::with('user')->where('id', $orderId)->where('user_id', $user->id)->first();
    // print_r($user->id);
    $order_item = OrderItem::where('order_id', $orderId)->first();
// print_r($order);die;
    $razorpay_signature = $input['razorpay_signature'] ?? null;
    $description = $input['webinar_id'] ?? ($order_item->webinar_id ?? ($order_item->installment_payment_id ?? 1));

    $api = new Api($this->api_key, $this->api_secret);

    try {
        if (!empty($input['razorpay_payment_id'])) {
            $payment = $api->payment->fetch($input['razorpay_payment_id']);

            // Handle capture only if payment is authorized
            if ($payment['status'] === 'authorized') {
                $response = $payment->capture(['amount' => $payment['amount']]);
            } elseif ($payment['status'] === 'captured') {
                $response = $payment;
            } else {
                return response()->json(['message' => 'Payment is not in a capturable state.'], 400);
            }

            // Order status update
            
            if ($order) {
                $orderStatus = $response['status'] === 'captured' ? Order::$paying : Order::$pending;
                $order->update([
                    'status' => $orderStatus,
                    'payment_method' => Order::$paymentChannel,
                ]);
            }

            // Store transaction
            date_default_timezone_set('Asia/Kolkata');
            TransactionHistory::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'razorpay_order_id' => 0,
                'razorpay_payment_id' => $input['razorpay_payment_id'],
                'transaction_type' => 'credit',
                'razorpay_signature' => $razorpay_signature ?? null,
                'amount' => $payment['amount'] / 100,
                'currency' => 'INR',
                'status' => $orderStatus === Order::$paying ? 'successful' : 'failed',
                'payment_method' => 'UPI',
                'international' => $payment['international'],
                'card_id' => $payment['card_id'],
                'bank' => $payment['bank'],
                'wallet' => $payment['wallet'],
                'vpa' => $payment['vpa'],
                'notes' => $payment['notes']['item'] ?? ($order_item->installment_type ?? 'installment'),
                'error_description' => $payment['error_description'],
                'error_source' => $payment['error_source'],
                'error_step' => $payment['error_step'],
                'error_reason' => $payment['error_reason'],
                'acquirer_data' => $payment['acquirer_data']['rrn'] ?? null,
                'description' => $description,
                'transaction_date' => now(),
            ]);

            // VBout integration
            try {
                $vboutService = new VboutService();
                $listId = empty('***REDACTED***') ? '143046' : '146283';
                $fields = empty('***REDACTED***') ? [
                    '941988' => $user->full_name,
                    '941989' => $payment['description'],
                    '941991' => $payment['amount'] / 100,
                    '941992' => dateTimeFormat(time(), 'j M Y H:i'),
                ] : [
                    '951553' => $user->full_name,
                    '951554' => $payment['description'],
                    '951555' => $payment['amount'] / 100,
                    '951556' => dateTimeFormat(time(), 'j M Y H:i'),
                    '951557' => $user->email,
                    '951558' => '***REDACTED***',
                ];

                $vboutService->addContactToList($listId, [
                    'email' => $user->email,
                    'fields' => $fields,
                ]);
            } catch (\Exception $e) {
                // Silently fail VBout
            }

            // GoHighLevel Webhook
            try {
                $type = $order_item->webinar_id ? 'course payment'
                    : ($order_item->reserve_meeting_id ? 'meeting payment'
                    : ($order_item->installment_type ? 'part payment'
                    : ($order_item->installment_payment_id ? 'installment payment' : 'null')));

                $webhookdata = [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_mobile' => $user->mobile,
                    'user_email' => $user->email,
                    'user_role' => $user->role_name,
                    'user_password' => '***REDACTED***' ?? '',
                    'course' => $payment['description'],
                    'payment_type' => $type,
                    'amount' => $payment['amount'] / 100,
                    'create_at' => now()->format('Y/m/d H:i'),
                ];

                $ch = curl_init(config('webhooks.gohighlevel.payment'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookdata));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                curl_exec($ch);
                curl_close($ch);
            } catch (\Exception $e) {
                // Webhook fail silent
            }

            return $order;
        }

    } catch (\Razorpay\Api\Errors\BadRequestError $e) {
        return response()->json(['error' => 'Razorpay: ' . $e->getMessage()], 400);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }

    return response()->json(['error' => 'Payment ID is required.'], 422);
}
    
        public function verifywalletPayment($input)
{
    $orderId = $input['order_id'];
    $Authuser = apiAuth();
    $user = User::where('id', $Authuser->id)
                    ->first();

    if (empty($user)) {
       
        return response()->json([
            'error' => 'invalid user',
            'details' =>'failed' 
        ], 401); 
    }

    $api = new Api($this->api_key, $this->api_secret);
    $payment = $api->payment->fetch($input['razorpay_payment_id']);

    $order = Order::where('id', $orderId)
                  ->where('user_id', $user->id)
                  ->with('user')
                  ->first();

    $orderItem = OrderItem::where('order_id', $orderId)->first();
    $razorpaySignature = $input['razorpay_signature'] ?? null;

    if (!empty($input['razorpay_payment_id'])) {
        $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount']]);

        if (!empty($order)) {
            $order->status = (in_array($payment->status, ['captured', 'authorized'])) ? Order::$paying : Order::$pending;
        $order->payment_method = Order::$paymentChannel;
        $order->save();

            date_default_timezone_set('Asia/Kolkata');
            $TransactionHistory = TransactionHistory::create([
            'user_id'              => $user->id,
            'order_id'             => $orderId,
            'razorpay_order_id'    => 0, // Use actual Razorpay order ID if available
            'razorpay_payment_id'  => $input['razorpay_payment_id'],
            'transaction_type'     => 'credit',
            'razorpay_signature'   => $input['razorpay_signature'] ?? null,
            'amount'               => $payment->amount / 100,  // Convert amount to INR (from paise)
            'currency'             => 'INR',
            'status'               => $order->status == Order::$paying ? 'successful' : 'failed',
            'payment_method'       => 'UPI',
            'international'        => $payment->international ?? false,
            'card_id'              => $payment->card_id ?? null,
            'bank'                 => $payment->bank ?? null,
            'wallet'               => $payment->wallet ?? null,
            'vpa'                  => $payment->vpa ?? $payment->upi->attributes['vpa'] ?? null,
            'error_description'    => $payment->error_description ?? null,
            'error_source'         => $payment->error_source ?? null,
            'error_step'           => $payment->error_step ?? null,
            'error_reason'         => $payment->error_reason ?? null,
            'acquirer_data'        => $payment->acquirer_data->attributes['rrn'] ?? null,
            'description'          => "Order Payment",
            'transaction_date'     => now(),
        ]);
        }

        return $order;
    }

    return false;
}
}
