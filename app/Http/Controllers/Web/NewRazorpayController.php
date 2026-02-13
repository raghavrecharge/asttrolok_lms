<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

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

class NewRazorpayController extends Controller
{

    public function index(Request $request)
    {
        try {
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

                }

                $data['orderItem']=$orderItem;
                return view('web.default.razorpay.index',$data);

            }
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function pay(Request $request)
    {
        try {
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

            return view('web.default.razorpay.pay', $data);
        } catch (\Exception $e) {
            \Log::error('pay error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function bookmeeting(Request $request)
    {
        try {
            $data1=$request->all();

            $data = [
                        'pageTitle' => trans('public.cart_page_title'),
                        'total' => $data1['amount'],
                        'full_name' => $data1['name'],
                        'email' => $data1['email'],
                        'contact' => $data1['contact'],
                        'orderid' => $data1['orderid'],

                    ];

            return view('web.default.razorpay.bookmeeting', $data);
        } catch (\Exception $e) {
            \Log::error('bookmeeting error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function consultationdetailsshow(Request $request)
    {
        try {
            $data1=$request->all();

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

                }

                $data['orderItem']=$orderItem;
                $data['contact_no']=$data1['contact_no'];
                return view('web.default.razorpay.consultationdetailsshow',$data);

            }
        } catch (\Exception $e) {
            \Log::error('consultationdetailsshow error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function consultationdetails(Request $request)
    {
        try {
            $data1=$request->all();

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

            $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY5MDYzNjA0M2Q1MjY4NTUzMDUxMzMi_pc';

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
            'create_at' => date("Y/m/d H:i")

            ];

            $webhookcurl = curl_init($webhookurl);

            curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($webhookcurl, CURLOPT_POST, true);

            curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

            $webhookresponse = curl_exec($webhookcurl);

            curl_close($webhookcurl);

            return view('web.default.razorpay.thankyou');
        } catch (\Exception $e) {
            \Log::error('consultationdetails error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
