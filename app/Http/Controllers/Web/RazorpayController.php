<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\AdvertisingBanner;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\File;
use App\Models\QuizzesResult;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\TextLesson;
use App\Models\CourseLearning;
use App\Models\WebinarChapter;
use App\Models\WebinarReport;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



use App\Models\Accounting;
use App\Models\Api\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserZoomLink;
use App\Models\ReserveMeeting;
use Jenssegers\Agent\Agent;

use Razorpay\Api\Api;
use App\Models\TransactionsHistoryRazorpay;




class RazorpayController extends Controller
{


    public function index(Request $request)
    {
       
        $razorpay_payment_id = $request->input('razorpay_payment_id');
        $consult_id = $request->input('consult_id');
        if(empty($razorpay_payment_id) && empty($consult_id) ){
            
        // $agent = new Agent();
        // if ($agent->isMobile()){
        //     return view(getTemplate() . '.razorpay.index');
        // }else{
        //     return view('web.default2' . '.razorpay.index');
        // }
        // return view('web.default.razorpay.index');
        return redirect ('/');
        }else{
            
         $data1=$request->all();
         $name = $request->input('full_name');
         $email = $request->input('email');
         $contact = $request->input('mobile');

        session()->put('razorpay_payment_id', $data1['razorpay_payment_id']);
        session()->put('status_code', $data1['status_code']);
        session()->put('consult_id', $data1['consult_id']);
        session()->put('email', $data1['email']);
        session()->put('name', $data1['name']);
        session()->put('contact', $data1['contact']);
        session()->put('_token', $data1['_token']);
        // print_r($data1);die();
        // return redirect()->route('web',$data1['consult_id'],'/profile?tab=appointments');
        return redirect('/users/'.$data1['consult_id'].'/profile?tab=appointments');
        }
    }
    public function paynow(Request $request){
        
        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.razorpay.index');
        }else{
            return view('web.default2' . '.razorpay.index');
        }
        // return view('web.default.razorpay.index');
    }
     public function index1(Request $request)
    {
        
        $data1=$request->all();
        
         $name = $request->input('full_name');
         $email = $request->input('email');
         $contact = $request->input('mobile');
            
                
            //  if (empty($name) or empty($contact) or empty($email)) {
            
            
            // $toastData = [
            //                 'title' => trans('public.request_failed'),
            //                 'msg' => 'Please enter Name, Email and Contact Details',
            //                 'status' => 'error'
            //             ];
            //     return response()->json($toastData);
                        
            //  }
        
        
        session()->put('razorpay_payment_id', $data1['razorpay_payment_id']);
        session()->put('status_code', $data1['status_code']);
        session()->put('consult_id', $data1['consult_id']);
        session()->put('email', $data1['email']);
        session()->put('name', $data1['name']);
        session()->put('contact', $data1['contact']);
        session()->put('_token', $data1['_token']);
        // print_r($data1);die();
        // return redirect()->route('web',$data1['consult_id'],'/profile?tab=appointments');
        return redirect('/users/'.$data1['consult_id'].'/profile?tab=appointments');
         $order=Order::where('id', $data1['order_id'])
            ->first();
            
         if (!empty($order)) {
             
             $order->update(['payment_method' => 'payment_channel']);
            
            $orderItem=OrderItem::where('order_id', $data1['order_id'])->first();
            
            
            
            if ($order->status == Order::$paying) {
                
                $sale = Sale::createSales($orderItem, $order->payment_method);
                
                
                if (!empty($orderItem->reserve_meeting_id)) {
                    $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                    $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();
                    

                    $reserveMeeting->update([
                        'sale_id' => $sale->id,
                        'reserved_at' => time()
                    ]);
 
                $order->update(['status' => Order::$paid]);
                

                
                Accounting::createAccounting($orderItem, null);
                }
                
                
        //     } else {
        //         if ($order->type === Order::$meeting) {
        //             $orderItem = OrderItem::where('order_id', $order->id)->first();

        //             if ($orderItem && $orderItem->reserve_meeting_id) {
        //                 $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();

        //                 if ($reserveMeeting) {
        //                     $reserveMeeting->update(['locked_at' => null]);
        //                 }
        //             }
        //         }
            }

        //     session()->put($this->order_session_key, $order->id);

            // return redirect('/payments/status');
            
            
            $data['orderItem']=$orderItem;
            $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.razorpay.index',$data);
            }else{
                return view('web.default2' . '.razorpay.index',$data);
            }
            
            // return view('web.default.razorpay.index',$data);
            
        }
        
        
    }
    public function pay(Request $request)
    {
        $data1=$request->all();
        $contact = $request->input('mobile');
         $amount = $request->input('amount');
        $consult_id = $request->input('consult_id');
        if(empty($consult_id) ){
             $data = $request->validate([
            'full_name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|digits:10',
            'amount' => 'required|numeric|between:1,99999999999999'
        ]);
         $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'total' => $data1['amount'],
                    'full_name' => $data1['full_name'],
                    'email' => $data1['email'],
                    'contact' => $data1['mobile'],
                
                ];
       
            $agent = new Agent();
            if ($agent->isMobile()){
                return view(getTemplate() . '.razorpay.pay2', $data);
            }else{
                return view('web.default2' . '.razorpay.pay2', $data);
            }
        // return view('web.default.razorpay.pay2', $data);
        }else{
           
             $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'contact' => 'required|digits:10',
            // 'amount' => 'required|numeric|between:1,99999999999999'
        ]);
        $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'total' => $data1['amount'],
                    'full_name' => $data1['name'],
                    'email' => $data1['email'],
                    'contact' => $data1['contact'],
                    'consult_id' => $data1['consult_id'],
                    '_token' => $data1['_token'],
                ];
//  print_r($data);
        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.razorpay.pay', $data);
        }else{
            return view('web.default2' . '.razorpay.pay', $data);
        }     
        // return view('web.default.razorpay.pay', $data);
        }
       
    }
    public function pay1(Request $request)
    {
        $data1=$request->all();
       
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'contact' => 'required|digits:10'
        ]);
        $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'total' => $data1['amount'],
                    'full_name' => $data1['name'],
                    'email' => $data1['email'],
                    'contact' => $data1['contact'],
                    'consult_id' => $data1['consult_id'],
                    '_token' => $data1['_token'],
                ];
//  print_r($data);
        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.razorpay.pay', $data);
        }else{
            return view('web.default2' . '.razorpay.pay', $data);
        }     
        // return view('web.default.razorpay.pay', $data);
    }
    
    public function bookmeeting(Request $request)
    {
        $data1=$request->all();
        
        $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'total' => $data1['amount'],
                    'full_name' => $data1['name'],
                    'email' => $data1['email'],
                    'contact' => $data1['contact'],
                    'orderid' => $data1['orderid'],
                    'astrologer'=>$data1['astrologer'],
                    'slotTime'=>$data1['slotTime']
                
                ];
//  print_r($data);
             
        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.razorpay.bookmeeting', $data);
        }else{
            return view('web.default2' . '.razorpay.bookmeeting', $data);
        }
            
        // return view('web.default.razorpay.bookmeeting', $data);
    }
    
    public function consultationdetailsshow(Request $request)
    {
        
        $data1=$request->all();
        
        if(!empty($data1['razorpay_payment_id'])){
               
               // Fetch full payment data from Razorpay
            $api = new Api(env('RAZORPAY_API_KEY'), env('RAZORPAY_API_SECRET'));
            $payment = $api->payment->fetch($data1['razorpay_payment_id']);

            // Insert into your DB
            TransactionsHistoryRazorpay::create([
                'user_id' => auth()->id() ?? null,
                'name' => $data1['name'] ?? 'Guest', // if you stored name in notes
                'number' => $payment->contact ?? null,
                'email' => $payment->email ?? null,
                'amount' => $payment->amount / 100 ?? null,
                'razorpay_payment_id' => $payment->id ?? null,
                'razorpay_description' => $payment->description ?? 'Razorpay Payment',
            ]);
           }
        
        // print_r($data1);
        
         $order=Order::where('id', $data1['order_id'])
            ->first();
            
         if (!empty($order)) {
             
             $order->update(['payment_method' => 'payment_channel']);
            
            $orderItem=OrderItem::where('order_id', $data1['order_id'])->first();
            
            
            
            if ($order->status == Order::$paying) {
                
                $sale = Sale::createSales($orderItem, $order->payment_method);
                
                
                if (!empty($orderItem->reserve_meeting_id)) {
                    $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                    $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();
                    

                    $reserveMeeting->update([
                        'sale_id' => $sale->id,
                        'reserved_at' => time()
                    ]);
 
                $order->update(['status' => Order::$paid]);
                

                
                Accounting::createAccounting($orderItem, null);
                }
                
                
        //     } else {
        //         if ($order->type === Order::$meeting) {
        //             $orderItem = OrderItem::where('order_id', $order->id)->first();

        //             if ($orderItem && $orderItem->reserve_meeting_id) {
        //                 $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();

        //                 if ($reserveMeeting) {
        //                     $reserveMeeting->update(['locked_at' => null]);
        //                 }
        //             }
        //         }
            }

        //     session()->put($this->order_session_key, $order->id);

            // return redirect('/payments/status');
            
            
            $data['orderItem']=$orderItem;
            $data['contact_no']=$data1['contact_no'];
            $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.razorpay.consultationdetailsshow', $data);
            }else{
                return view('web.default2' . '.razorpay.consultationdetailsshow', $data);
            }
            
            // return view('web.default.razorpay.consultationdetailsshow',$data);
            
        }
        
        
    }

 

public function consultationdetails(Request $request)
    {
        $data1=$request->all();
        
        // $data = [
        //             'pageTitle' => trans('public.cart_page_title'),
        //             'total' => $data1['amount'],
        //             'full_name' => $data1['name'],
        //             'email' => $data1['email'],
        //             'contact' => $data1['contact'],
        //             'orderid' => $data1['orderid'],
                
        //         ];
        
        
        $orderItem=OrderItem::where('order_id', $data1['order_id'])->first();
        $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();
        $createrzoom = UserZoomLink::where('user_id', $orderItem->reserveMeeting->meeting->creator_id)->first();
        
        
                           date_default_timezone_set('Asia/Kolkata');
  
 $mail_befor_1_hour=-1;
 $mail_befor_1_day=-1;
  if(!empty($createrzoom)){
      $start = strtotime(date("m/d/Y h:i"));
$stop = strtotime(date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at));
$diff = ($stop - $start);
$mail_befor_1_day = ($diff/60)-1440;                   
$mail_befor_1_hour = ($diff/60)-60;                   
                    
  } 
  
 $value = $data1['mobile'];

$mobileregex = "/^[0-9]{10}$/";
if(preg_match($mobileregex, $value)===0){
   $value1= preg_replace('/[^0-9]/', '', $value);
    $len = strlen($value1);
    // print_r($len);
    
            if($len==13) {
    preg_match( '/^(\d{3})(\d{10})$/', $value1,  $matches );
    $result =$matches[2];
    $data1['mobile']= $result;
} 
    
    if($len==12) {
    preg_match( '/^(\d{2})(\d{10})$/', $value1,  $matches );
    $result =$matches[2];
    $data1['mobile']= $result;
}    
    if($len==11) {
    preg_match( '/^(\d{1})(\d{10})$/', $value1,  $matches );
    $result =$matches[2];
    $data1['mobile']= $result;
}

}  

 $value2 = $creater->mobile;
$value23 = $creater->mobile;
$mobileregex = "/^[0-9]{10}$/";
if(preg_match($mobileregex, $value2)===0){
   $value21= preg_replace('/[^0-9]/', '', $value2);
    $len = strlen($value21);
    // print_r($len);
    
        if($len==13) {
    preg_match( '/^(\d{3})(\d{10})$/', $value21,  $matches );
    $result =$matches[2];
    $value23= $result;
}  
    
    if($len==12) {
    preg_match( '/^(\d{2})(\d{10})$/', $value21,  $matches );
    $result =$matches[2];
    $value23= $result;
}    
    if($len==11) {
    preg_match( '/^(\d{1})(\d{10})$/', $value21,  $matches );
    $result =$matches[2];
    $value23= $result;
}

}
  
  
  
  
  
  
// 	  $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY5MDYzNjA0M2Q1MjY4NTUzMDUxMzMi_pc';
	   $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/6bcc434d-8597-4cce-ae5d-8110cf5dbff7';
// Collection object
$webhookdata = [
  'student_id' => $orderItem->user_id,
  'student_name' => $data1['full_name'],
  'student_mobile' => '91'.$data1['mobile'],
  'student_email' => $data1['email'],
  'consultant_id' => $creater->id,
  'consultant_name' => $creater->full_name,
  'consultant_mobile' => $value23,
  'consultant_email' => $creater->email,
  'consultant_zoom_user' => !empty($createrzoom)?$createrzoom->gmail:'null',
  'consultant_zoom_pwd' => !empty($createrzoom)?$createrzoom->zoom_pwd:'null',
  'birth_date' => $data1['birthdate'],
  'birth_time' => $data1['birthtime'],
  'birth_place' => $data1['birthplace'],
  'meeting_start_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at),
  'meeting_end_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->end_at),
  'meeting_link' => !empty($createrzoom)?$createrzoom->zoom_link:'null',
  'mail_befor_1_hour' => $mail_befor_1_hour>0?$mail_befor_1_hour:'null',
  'mail_befor_1_day' => $mail_befor_1_day>0?$mail_befor_1_day:'null',
  'paid_amount' => $orderItem->reserveMeeting->paid_amount,
  'status' => 'paid',
  'create_at' => date("Y/m/d H:i")
  
  
];
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
        
   
        
        
        
        
        

        
// print_r($webhookdata['meeting_start_at']);

// $value = $webhookdata["consultant_mobile"];




// print_r($webhookdata);


             
        // return view('web.default.razorpay.index',$data);
        
        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.razorpay.thankyou');
        }else{
            return view('web.default2' . '.razorpay.thankyou');
        }
                    
        // return view('web.default.razorpay.thankyou');
    }
    
public function thankyou()
    {
        
        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.razorpay.thankyou');
        }else{
            return view('web.default2' . '.razorpay.thankyou');
        }
                    
    }

 

  

 



 

 









}
