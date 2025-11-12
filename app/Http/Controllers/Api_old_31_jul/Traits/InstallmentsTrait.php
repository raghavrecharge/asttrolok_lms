<?php

namespace App\Http\Controllers\Api\Traits;

use App\Mixins\Installment\InstallmentPlans;
use App\Models\InstallmentOrder;
use App\Models\Product;
use App\Models\RegistrationPackage;
use App\Models\Subscribe;
use App\Models\SubscribeUse;
use App\Models\Webinar;
use App\Models\Sale;
use App\Models\WebinarChapter;
use App\Models\Installment;
use App\Models\InstallmentSpecificationItem;
use App\Models\InstallmentStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentChannel;
use Jenssegers\Agent\Agent;


trait InstallmentsTrait
{
    public function getInstallmentsByCourse(Request $request)
    {
        $slug = $request->input('slug');
        $user = null;

        if (auth()->check()) {
            $user = auth()->user();
        }


        $contentLimitation = $this->checkContentLimitation($user);
        if ($contentLimitation != "ok") {
            return $contentLimitation;
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!empty($course)) {
            $isPrivate = $course->private;
            $hasBought = $course->checkUserHasBought($user);

            if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin() or $hasBought)) {
                $isPrivate = false;
            }


            $canSale = ($course->canSale() and !$hasBought);

            if (!$isPrivate and $canSale and !empty($course->price) and $course->price > 0 and getInstallmentsSettings('status')) {
                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
               
                $itemPrice = $course->getPrice();
                $cash = $installments->sum('upfront');
                $plansCount = $installments->count();
                $minimumAmount = 0;

                foreach ($installments as $installment) {
                    if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                        $minimumAmount = $installment->totalPayments($itemPrice);
                    }
                }

                $paymentChannels = PaymentChannel::where('status', 'active')->get();
                
                $data = [
                    'pageTitle' => trans('update.select_an_installment_plan'),
                    'overviewTitle' => $course->title,
                    'installments' => $installments,
                    'itemPrice' => $itemPrice,
                    'itemId' => $course->id,
                    'itemType' => 'course',
                    'cash' => $cash,
                    'plansCount' => $plansCount,
                    'minimumAmount' => $minimumAmount,
                    'paymentChannels' => $paymentChannels,
                ];

                 return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [$data]);
            }
        }

        abort(404);
    }

    public function getInstallmentsByProduct(Request $request, $slug)
    {
        $user = null;

        if (auth()->check()) {
            $user = auth()->user();
        }

        $product = Product::where('status', Product::$active)
            ->where('slug', $slug)
            ->first();

        if (!empty($product)) {
            if (!empty($product->price) and $product->price > 0 and getInstallmentsSettings('status')) {
                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('store_products', $product->id, $product->type, $product->category_id, $product->creator_id);

                $quantity = $request->get('quantity', 1);
                $itemPrice = $product->getPrice() * $quantity;
                $cash = $installments->sum('upfront');
                $plansCount = $installments->count();
                $minimumAmount = 0;

                foreach ($installments as $installment) {
                    if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                        $minimumAmount = $installment->totalPayments($itemPrice);
                    }
                }

                $data = [
                    'pageTitle' => trans('update.select_an_installment_plan'),
                    'overviewTitle' => $product->title,
                    'installments' => $installments,
                    'itemPrice' => $itemPrice,
                    'itemId' => $product->id,
                    'itemType' => 'product',
                    'cash' => $cash,
                    'plansCount' => $plansCount,
                    'minimumAmount' => $minimumAmount,
                ];

                $agent = new Agent();
                if ($agent->isMobile()){
                return view(getTemplate() . '.installment.plans', $data);
                }else{
                    return view('web.default2' . '.installment.plans', $data);
                }
                // return view('web.default.installment.plans', $data);
            }
        }

        abort(404);
    }

    public function getInstallmentsByRegistrationPackage($packageId)
    {
        $user = auth()->user();

        $package = RegistrationPackage::where('id', $packageId)
            ->where('status', 'active')
            ->first();

        if (!empty($package) and $package->price > 0 and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans('registration_packages', $package->id);

            $itemPrice = $package->getPrice();
            $cash = $installments->sum('upfront');
            $plansCount = $installments->count();
            $minimumAmount = 0;

            foreach ($installments as $installment) {
                if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                    $minimumAmount = $installment->totalPayments($itemPrice);
                }
            }

            $data = [
                'pageTitle' => trans('update.select_an_installment_plan'),
                'overviewTitle' => $package->title,
                'installments' => $installments,
                'itemPrice' => $itemPrice,
                'itemId' => $package->id,
                'itemType' => 'registration_package',
                'cash' => $cash,
                'plansCount' => $plansCount,
                'minimumAmount' => $minimumAmount,
            ];

            $agent = new Agent();
                if ($agent->isMobile()){
                return view(getTemplate() . '.installment.plans', $data);
                }else{
                    return view('web.default2' . '.installment.plans', $data);
                }
                // return view('web.default.installment.plans', $data);
        }

        abort(404);
    }

    public function getInstallmentsBySubscribe($subscribeId)
    {
        $user = auth()->user();

        $subscribe = Subscribe::where('id', $subscribeId)->first();

        if (!empty($subscribe) and $subscribe->price > 0 and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans('subscription_packages', $subscribe->id);

            $itemPrice = $subscribe->getPrice();
            $cash = $installments->sum('upfront');
            $plansCount = $installments->count();
            $minimumAmount = 0;

            foreach ($installments as $installment) {
                if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                    $minimumAmount = $installment->totalPayments($itemPrice);
                }
            }

            $data = [
                'pageTitle' => trans('update.select_an_installment_plan'),
                'overviewTitle' => $subscribe->title,
                'installments' => $installments,
                'itemPrice' => $itemPrice,
                'itemId' => $subscribe->id,
                'itemType' => 'subscribe',
                'cash' => $cash,
                'plansCount' => $plansCount,
                'minimumAmount' => $minimumAmount,
            ];

            $agent = new Agent();
                if ($agent->isMobile()){
                return view(getTemplate() . '.installment.plans', $data);
                }else{
                    return view('web.default2' . '.installment.plans', $data);
                }
                // return view('web.default.installment.plans', $data);
        }

        abort(404);
    }

    public function checkUserHasOverdueInstallment($user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $orders = collect();

        if (!empty($user)) {
            $time = time();
            $overdueIntervalDays = getInstallmentsSettings('overdue_interval_days') ?? 0; // days
            $overdueIntervalDays = $overdueIntervalDays * 86400;
            $time = $time - $overdueIntervalDays;
// DB::enableQueryLog();
            $orders = InstallmentOrder::query()
                ->join('installments', 'installment_orders.installment_id', 'installments.id')
                ->join('installment_steps', 'installments.id', 'installment_steps.installment_id')
                ->leftJoin('installment_order_payments', 'installment_order_payments.step_id', 'installment_steps.id')
                ->select('installment_orders.*', 'installment_steps.amount', 'installment_steps.amount_type',
                    DB::raw('((installment_steps.deadline * 86400) + installment_orders.created_at) as overdue_date')
                )
                ->where('user_id', $user->id)
                ->whereRaw("((installment_steps.deadline * 86400) + installment_orders.created_at) < {$time}")
                ->where(function ($query) { // Where Doesnt Have payment
                    $query->whereRaw("installment_order_payments.id < 1");
                    $query->orWhereRaw("installment_order_payments.id is null");
                })
                ->where('installment_orders.status', 'open')
                ->get();
            //   print_r( DB::getQueryLog());
            //     die();
        }

        return $orders;
    }

    public function installmentContentLimitation($user, $itemId = null, $itemName = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $installmentsSettings = getInstallmentsSettings();

        if (!empty($user) and !empty($installmentsSettings['status'])) {
            $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);
            $denied = false;

            if ($overdueInstallmentOrders->isNotEmpty() and $installmentsSettings['disable_all_courses_access_when_user_have_an_overdue_installment']) {
                $denied = true;
            }

            if (!empty($itemId) and !empty($itemName)) {
                $itemOrders = $overdueInstallmentOrders->where($itemName, $itemId);

                if ($itemOrders->isNotEmpty() and $installmentsSettings['disable_course_access_when_user_have_an_overdue_installment']) {
                    $denied = true;
                }

                /*****
                 * Check Subscribe For Items
                 * */
                $subscribeOrders = $overdueInstallmentOrders->whereNotNull('subscribe_id');
                if ($subscribeOrders->isNotEmpty()) {
                    foreach ($subscribeOrders as $subscribeOrder) {
                        $subscribeUse = SubscribeUse::query()->whereNotNull('sale_id')
                            ->where('user_id', $user->id)
                            ->where($itemName, $itemId)
                            ->where('installment_order_id', $subscribeOrder->id)
                            ->first();

                        if (!empty($subscribeUse)) {
                            $denied = true;
                        }
                    }
                }



            }

            if ($denied) {
                $data = [
                    'pageTitle' => trans('update.access_denied'),
                    'pageRobot' => getPageRobotNoIndex(),
                ];

                $agent = new Agent();
                if ($agent->isMobile()){
                return view(getTemplate() . '.course.access_denied', $data);
                }else{
                    return view('web.default2' . '.course.access_denied', $data);
                }
                // return view('web.default.course.access_denied', $data);
            }
        }

        return "ok";
    }
    
//      public function installmentContentLimitation_limit($user, $itemId = null, $itemName = null)
//     {
//         $percent=0;
//         if (empty($user)) {
//             $user = auth()->user();
//         }
// $installmentsSettings = getInstallmentsSettings();

//         if (!empty($user) and !empty($installmentsSettings['status'])) {
           
//             $denied = false;
//               if (!empty($itemId) and !empty($itemName)) {
                   
//                   $installments1 =DB::table('installment_orders') 
//                   ->selectRaw(' * ')
//                   ->where('user_id', $user->id )
//              ->where('webinar_id', $itemId)
//             ->get();
//             if(count($installments1)>=1){
//              foreach ($installments1 as $installments) {
//                 $installment_id =  $installments->installment_id;

//             $installmentsz1 = DB::table('installments')
//     ->selectRaw(' * ')
//     ->where('id', $installment_id )
//     ->get();
//         foreach ($installmentsz1 as $installmentsz) {
//     if($installmentsz->upfront_type=='percent'){
//         $percent=$installmentsz->upfront;
//         $installment_steps = DB::table('installment_steps')
//     ->selectRaw(' * ')
//     ->where('installment_id', $installment_id )
//     ->get();
   
//     foreach ($installment_steps as $installment_stepsnew) {
//                   $amount_type=$installment_stepsnew->amount_type;
//                   if($amount_type=='percent'){
//              $installment_step_translations = DB::table('installment_order_payments')
//     ->selectRaw(' * ')
//     ->where('step_id', $installment_stepsnew->id )
//     ->where('installment_order_id', $installments->id )
//     ->where('status', 'paid' )
//     ->get();
//     // print_r($installment_step_translations);
//     foreach ($installment_step_translations as $installment_step_translations1) {
//         $percent += $installment_stepsnew->amount;
        
//     }
  
     
//               }     }
//                 }
    
//         // $upfront=$installmentsz->upfront;
//     }
//         }
//         return $final_percent=round($percent);   
//               }
//               return 100;
//               }
            
//         }

//         return 100;
//     }
    
    
    
    
    
    
      public function installmentContentLimitation_limit($user, $itemId = null, $itemName = null)
    {
    //       $webinars = DB::table('webinars')
    // ->selectRaw(' * ')
    // ->where('id', $itemId )
    // ->first();
    //     $pric=$webinars->price;
     
    //   ($installmentLimitation_limit/100)*$cchapt;
        $percent=0;
        if (empty($user)) {
            $user = auth()->user();
        }
$installmentsSettings = getInstallmentsSettings();

        if (!empty($user) and !empty($installmentsSettings['status'])) {
            $denied = false;
              if (!empty($itemId) and !empty($itemName)) {
                  $installments1 =DB::table('installment_orders') 
                   ->selectRaw(' * ')
                  ->where('user_id', $user->id )
             ->where('webinar_id', $itemId)
             ->where('status', 'open')
            ->get();
            // echo $user->id;
            if(count($installments1)>=1){
          foreach ($installments1 as $installments) {
              $pric=$installments->item_price;
             $installment_step_translations = DB::table('installment_order_payments')
    ->selectRaw(' * ')
    ->where('installment_order_id', $installments->id )
    ->where('status', 'paid' )
    ->get();
    foreach ($installment_step_translations as $installment_step_translations1) {
        $percent += $installment_step_translations1->amount;
    }
     $final_percent1=($percent/$pric) * 100;

          }
             
        return $final_percent=round($final_percent1);   
              }
              return 100;
              }
            
        }

        return 100;
    }
    
     public function installmentContentLimitation_check($itemId = null)
    {
        // print_r($_REQUEST['uid']);die();
        $pratul='';
          $check='';
          $percent_webinar_id ='';
         $installments1 =DB::table('installment_specification_items') 
                   ->selectRaw('installment_translations.main_title,installment_translations.installment_id')
                    ->join('installment_translations', 'installment_specification_items.installment_id', 'installment_translations.installment_id')
                    ->join('installments', 'installments.id', 'installment_translations.installment_id')
             ->where('installment_specification_items.webinar_id', $itemId)
             ->where('installments.enable', 1)
            ->get();
            if(count($installments1)>=1){
        foreach ($installments1 as $installments) {
       $title =$installments->main_title;
       $id =$installments->installment_id;
       
      $sales1 = Sale::where('buyer_id', $_REQUEST['uid'])
         ->get();
         foreach ($sales1 as $sales2) {
             if($sales2->installment_payment_id){
                $installment_step_translations = DB::table('installment_order_payments')
    ->selectRaw(' * ')
    ->where('id', $sales2->installment_payment_id )
    ->get();
    // print_r($sales2->installment_payment_id);die();
    foreach ($installment_step_translations as $installment_step_translations1) {
        $percent = $installment_step_translations1->installment_order_id;
    }
     $installment_step_translations1 = DB::table('installment_orders')
    ->selectRaw(' * ')
    ->where('id', $percent)
    ->get();
    $percent_webinar_id ='';
    foreach ($installment_step_translations1 as $installment_step_translations11) {
        
        // print_r($installment_step_translations11);die();
        
        $percent_webinar_id .='~'.$installment_step_translations11->installment_id;
    }
     if($percent_webinar_id != ''){
          $percent_webinar_id1=explode("~",$percent_webinar_id); 
     }
         }
             
         } 
        $check='';
         if($percent_webinar_id != ''){
       if(in_array($id, $percent_webinar_id1)){
          $check="Checked"; 
       }
         }
        $pratul .='<label class="custom-switch"><input type="radio" name="installmenttitles" '.$check.' value="'.$id.'" class="custom-switch-input indtalradio" onchange="installmentgetstep(this);"><span class="custom-switch-indicator"></span> <span class="custom-switch-description">'.$title.'</span></label>';
    }
            }else{
                $pratul ='<label class="custom-switch"> <span class="custom-switch-description">No installment available</span></label>';
            }
           return  $pratul;
    }
    public function installmentContentLimitation_check_step($userid = null, $instid = null, $webid = null)
    {
        $pratul='';
        $percent_webinar_id ='';
        $check='';
        $payid='';
        $payid1='';
        $payidz='';
         $percent_webinar_idz ='';
        $checkz='';
        $percentz='';
        $installments3 =DB::table('installments') 
                    ->selectRaw('upfront,upfront_type')
                    ->where('id', $instid)
                    ->get();
        foreach ($installments3 as $installments2) {
            $upfront =$installments2->upfront;
            $upfront_type =$installments2->upfront_type;
        $sales1 = Sale::where('buyer_id', $_REQUEST['uid'])
                    ->get();
         
            foreach ($sales1 as $sales2) {
                // return $sales2->installment_payment_id;
                if($sales2->installment_payment_id){
                    $installment_step_translations = DB::table('installment_order_payments')
                                                ->selectRaw(' * ')
                                                ->where('id', $sales2->installment_payment_id )
                                                ->where('type','upfront')
                                                ->get();
                                                
                    foreach ($installment_step_translations as $installment_step_translations1) {
                        $payid1='';
                        $percent = $installment_step_translations1->installment_order_id;
                        $payid1 = $installment_step_translations1->id;
                        
                    }
    if($percent){
        // return $percent;
                    $installment_step_translations1 = DB::table('installment_orders')
                                                ->selectRaw(' * ')
                                                ->where('id', $percent)
                                                ->get();
    
                    //   return $installment_step_translations1;
                    $percent_webinar_id ='';
                    foreach ($installment_step_translations1 as $installment_step_translations11) {
                        
                        // print_r($installment_step_translations11);die();
                        
                          $percent_webinar_id .='~'.$installment_step_translations11->webinar_id;
    }
    // return $percent_webinar_id;
    if($percent_webinar_id != ''){
          $percent_webinar_idw=explode("~",$percent_webinar_id); 
    
          
                    // $percent_webinar_id1=explode("~",$percent_webinar_id); 
                    // return $percent_webinar_id;
          $check='';
          $payid='';
             if(in_array($webid, $percent_webinar_idw)){
                $check="Checked"; 
               $payid=$payid1;
            }
    }
    }
             
            
           
                }
            }
            $pratul .='<div class="checkbox-button mr-15 mt-10"><input type="checkbox"  '.$check.'  name="occupations[]" id="checkbox'.$instid.'" value="upfront`'.$upfront.'`'.$upfront_type.'`'.$payid.'"> <label for="checkbox'.$instid.'">'.$upfront.'('.$upfront_type.') Upfront</label></div>';
      
             
            
        } 
        
        
         $installments1 =DB::table('installment_steps') 
                   ->selectRaw('installment_step_translations.title,installment_step_translations.installment_step_id,installment_steps.amount,installment_steps.amount_type')
                    ->join('installment_step_translations', 'installment_steps.id', 'installment_step_translations.installment_step_id')
             ->where('installment_steps.installment_id', $instid)
            ->get();
            if(count($installments1)>=1){
        foreach ($installments1 as $installments) {
       $title =$installments->title;
       $id =$installments->installment_step_id;
       $amount=$installments->amount;
       $amount_type=$installments->amount_type;
       
      
      
      
      
      $sales1z = Sale::where('buyer_id', $_REQUEST['uid'])
                    ->get();
         
            foreach ($sales1z as $sales2z) {
                // return $sales2;
                $percent_webinar_idwz=[];
                if($sales2z->installment_payment_id){
                    $installment_step_translationsz = DB::table('installment_order_payments')
                                                ->selectRaw(' * ')
                                                ->where('id', $sales2z->installment_payment_id )
                                                ->where('type','step')
                                                ->where('step_id',$id)
                                                ->get();
                                                $percentz='';
                                                //  $checkz='';
                    foreach ($installment_step_translationsz as $installment_step_translations1z) {
                        $payid1z='';
                        
                        $payid1z = $installment_step_translations1z->id;
                        $percentz = $installment_step_translations1z->installment_order_id;
                        // $percent = $installment_step_translations1->step_id;
                        
                    }
    if($percentz){
        // return $percent;
                    $installment_step_translations1z = DB::table('installment_orders')
                                                ->selectRaw(' * ')
                                                ->where('id', $percentz)
                                                ->where('webinar_id',$webid)
                                                ->get();
    
                    //   return $installment_step_translations1;
                    $percent_webinar_idz ='';
                    foreach ($installment_step_translations1z as $installment_step_translations11z) {
                        
                        // print_r($installment_step_translations11);die();
                       
                        
                          $percent_webinar_idz .='~'.$installment_step_translations11z->webinar_id;
    }
    // return $percent_webinar_id;
    if($percent_webinar_idz != ''){
          $percent_webinar_idwz=explode("~",$percent_webinar_idz); 
    
          
                    // $percent_webinar_id1=explode("~",$percent_webinar_id); 
                    // return $percent_webinar_id;
        //   $checkz='';
          $payidz='';
        //   return in_array($webid, $percent_webinar_idwz);
             if(in_array($webid, $percent_webinar_idwz)){
                $checkz="Checked"; 
                $payidz=$payid1z;
                // return $checkz;
            }
    }
    }
             
            
           
                }
            }
      
      
      

      
    $pratul .='<div class="checkbox-button mr-15 mt-10"><input type="checkbox"  '.$checkz.'  name="occupations[]" id="checkbox'.$id.'" value="'.$id.'`'.$amount.'`'.$amount_type.'`'.$payidz.'"> <label for="checkbox'.$id.'">'.$amount.'('.$amount_type.') '.$title.'</label></div>';
         $payidz=''; $checkz='';     }
            }else{
                $pratul ='<label class="custom-switch"> <span class="custom-switch-description">No installment steps available</span></label>';
            }
            
            // print_r($pratul);
            return  $pratul;
    }
    
     public function installmentContentLimitation_limit1($user, $itemId = null, $itemName = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }
$installmentsSettings = getInstallmentsSettings();

        if (!empty($user) and !empty($installmentsSettings['status'])) {
            $denied = false;
              if (!empty($itemId) and !empty($itemName)) {
                  $installments1 =DB::table('installment_orders') 
                   ->selectRaw(' * ')
                  ->where('user_id', $user->id )
             ->where('webinar_id', $itemId)
             ->where('status', 'open')
            ->get();
            // echo $user->id;
            if(count($installments1)>=1){
          foreach ($installments1 as $installments) {
           $installmentsid=   $installments->id;
   
    }
    return $installmentsid;

          }   
              }
              }
        return 0;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
