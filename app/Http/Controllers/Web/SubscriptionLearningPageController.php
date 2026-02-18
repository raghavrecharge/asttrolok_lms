<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

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
        try {
            $webinarController = new WebinarController();
            $requestData = $request->all();

            echo $installmentLimitation_check = $webinarController->installmentContentLimitation_check($slug);
        } catch (\Exception $e) {
            \Log::error('inst error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
public function inststep(Request $request, $slug){
        try {
            $webinarController = new WebinarController();
            $requestData = $request->all();
            $userid= $requestData['uid'];
            $instid= $requestData['instid'];
            echo $installmentLimitation_check_step = $webinarController->installmentContentLimitation_check_step($userid, $instid, $slug);
        } catch (\Exception $e) {
            \Log::error('inststep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function index(Request $request, $slug)
    {
        try {
            $requestData = $request->all();

            $subscriptionController = new SubscriptionController();

            $data = $subscriptionController->subscription($slug, true);

            $subscription = $data['subscription'];
            $user = $data['user'];
             $subscription_pricess = $subscription->price;
              $cchapt=count($data['chapterItems']);

             $Access = SubscriptionAccess ::where('subscription_id', $subscription->id)
                ->where('user_id', $user->id)
                ->first();

            $access_content_count = 0;

             if($Access){

                //  if($Access->access_till_date > time()){

                //      if($Access->access_content_count > 0){
                //          $access_content_count =$Access->access_content_count + $subscription ->free_video_count;;
                //      }
                //      $data["duedate"]=$Access->access_till_date;
                //  }else{
                //      $access_content_count=0;
                //  }
                
                if($Access->access_till_date > time()){
                 if($Access->access_content_count > 0){
                     $access_content_count = $Access->access_content_count;
                 }
                 if($subscription ->free_video_count){
                     $access_content_count = $access_content_count + $subscription ->free_video_count;
                 }
                 $data["duedate"]=$Access->access_till_date;
             }else{
                 $access_content_count=0;
             }
             
             }
             if($user->id == 1){
             $access_content_count =$cchapt;
             }

             $data['limit']=$access_content_count;

             $data['install_url']='/subscriptions/direct-payment/'.$subscription->slug;

            if ((!$data or !$data['hasBought'])) {
                $baseUrl = config('app.manual_base_url');

               $installUrl = $baseUrl.'/subscriptions/direct-payment/'.$subscription->slug;
                return response()->view('errors.403', compact('installUrl'), 403);
            }

            $webinars = Webinar::where('webinars.status', 'active')
            ->where('private', false)

            ->get();

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

            return view('web.default.subscription.learningPage.index', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
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