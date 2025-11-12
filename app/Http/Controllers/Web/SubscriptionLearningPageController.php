<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Web\traits\LearningPageForumTrait;
use App\Http\Controllers\Web\traits\SubscriptionLearningPageItemInfoTrait;
use App\Http\Controllers\Web\traits\LearningPageMixinsTrait;
use App\Http\Controllers\Web\traits\LearningPageNoticeboardsTrait;
use App\Models\Certificate;
use App\Models\CourseNoticeboard;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentOrder;
use App\Models\SubscriptionAccess;
use App\Models\Webinar;
use App\Models\WebinarAccessControl;
use Illuminate\Http\Request;

class SubscriptionLearningPageController extends Controller
{
    use LearningPageMixinsTrait, LearningPageAssignmentTrait, SubscriptionLearningPageItemInfoTrait,
        LearningPageNoticeboardsTrait, LearningPageForumTrait;
        
public function inst(Request $request, $slug){
    $webinarController = new WebinarController();
    $requestData = $request->all();
    // echo $requestData['uid'];
     echo $installmentLimitation_check = $webinarController->installmentContentLimitation_check($slug);
    //   print_r($installmentLimitation_check);
}
public function inststep(Request $request, $slug){
    $webinarController = new WebinarController();
    $requestData = $request->all();
     $userid= $requestData['uid'];
      $instid= $requestData['instid'];
     echo $installmentLimitation_check_step = $webinarController->installmentContentLimitation_check_step($userid, $instid, $slug);
    //   print_r($installmentLimitation_check);
}

    public function index(Request $request, $slug)
    {
         $requestData = $request->all();
//  print_r($requestData);
        $subscriptionController = new SubscriptionController();
        // $user = null;

        // if (auth()->check()) {
        //     $user = auth()->user();
        // }
        $data = $subscriptionController->subscription($slug, true);
        // $data['directAccess']=0;
// print_r($data['course']);die();
        $subscription = $data['subscription'];
        $user = $data['user'];
         $subscription_pricess = $subscription->price;
          $cchapt=count($data['chapterItems']);
          
        //  $installmentLimitation_limit = $webinarController->installmentContentLimitation_limit($user, $subscription->id, 'webinar_id');
        //  $installmentLimitation_limit1 = $webinarController->installmentContentLimitation_limit1($user, $subscription->id, 'webinar_id');
         
         $Access = SubscriptionAccess ::where('subscription_id', $subscription->id)
            ->where('user_id', $user->id)
            ->first();
        //  print($installmentLimitation_limit);
        $access_content_count = 0;
        // $data["duedate"]=time();
         if($Access){
             
             
             if($Access->access_till_date > time()){
                 
                
                 
                //  if($directAccess->percentage > $installmentLimitation_limit){
                //      $installmentLimitation_limit =$directAccess->percentage;
                //      $data['directAccess']=1;
                //  } 
                 if($Access->access_content_count > 0){
                     $access_content_count =$Access->access_content_count + $subscription ->free_video_count;;
                 }
                 $data["duedate"]=$Access->access_till_date;
             }else{
                 $access_content_count=0;
             }
         }
         if($user->id == 1){
         $access_content_count =$cchapt;
         }
         
        //   echo '<pre>';
        //     //  print_r($Access);
        //     //  print_r(date("Y-m-d h:i:sa", time()));
        //      print_r( $access_content_count);
        //      die();
        //  print($installmentLimitation_limit);
        //  $installmentLimitation_limit=100;
        // $P1 = ($installmentLimitation_limit/100)*$cchapt;
    //   echo $pchapters1= number_format((float)$P1, 0, '.', '');
        //  $pchapters1=round($P1);
        //  if($pchapters1==0){
        //      $pchapters1=100;
        //  }
         $data['limit']=$access_content_count;
        //  $data['install_url']='/panel/financial/installments/'.$installmentLimitation_limit1.'/pay_upcoming_part';
         $data['install_url']='/subscriptions/direct-payment/'.$subscription->slug;
        //   echo $pchapters=round($pchapters1);
        // $installmentLimitation = $webinarController->installmentContentLimitation($user, $subscription->id, 'webinar_id');
        // if ($installmentLimitation != "ok") {
        //     //return $installmentLimitation;
        // }


        if ((!$data or !$data['hasBought'])) {
            
            abort(403);
        }

//         if (!empty($requestData['type']) and $requestData['type'] == 'assignment' and !empty($requestData['item'])) {
// // print_r($requestData);
//             $assignmentData = $this->getAssignmentData($subscription, $requestData);

//             $data = array_merge($data, $assignmentData);
//         }

        // if ($subscription->creator_id != $user->id and $subscription->teacher_id != $user->id and !$user->isAdmin()) {
        //     $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $subscription->id)
        //         ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
        //             $query->where('user_id', $user->id);
        //         })
        //         ->count();

        //     if ($unReadCourseNoticeboards) {
        //         $url = $subscription->getNoticeboardsPageUrl();
        //         return redirect($url);
        //     }
        // }

        // if ($subscription->certificate) {
        //     $data["courseCertificate"] = Certificate::where('type', 'subscription')
        //         ->where('student_id', $user->id)
        //         ->where('webinar_id', $subscription->id)
        //         ->first();
        // }
        
        // $user = auth()->user();

        
        

        
        
    // if($course->id == 2036){
    $webinars = Webinar::where('webinars.status', 'active')
    ->where('private', false)
    // ->whereIn('id', [2069, 2038, 2050])
    ->get();
        // print_r($webinars);
        // $data["webinars"] =$webinars;
        // die();
        $hasBought[]=0;
    foreach($webinars as $webinar){
        if($webinar->checkUserHasBought($user, true, true))
        {
            $hasBought[] = $webinar->id;
        }
    }
    $webinars = Webinar::where('webinars.status', 'active')
    ->where('private', false)
    ->whereNotIn('id', $hasBought)
    ->orderBy('order', 'asc')
    ->limit(5)
    ->get();
    $data["webinars"] =$webinars;
    // }
        // echo $data['directAccess'];
        return view('web.default.subscription.learningPage.index', $data);
    }


  private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $dueAt = ($step->deadline * 86400) + $order->created_at;

            if ($dueAt < $time) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();

                if (empty($payment)) {
                    $total += 1;
                    $amount += $step->getPrice($itemPrice);
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }
    
    
    private function getRemainedInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->first();

            if (empty($payment)) {
                $total += 1;
                $amount += $step->getPrice($itemPrice);
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }
    
    
    
}