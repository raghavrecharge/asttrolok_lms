<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Web\traits\LearningPageForumTrait;
use App\Http\Controllers\Web\traits\LearningPageItemInfoTrait;
use App\Http\Controllers\Web\traits\LearningPageMixinsTrait;
use App\Http\Controllers\Web\traits\LearningPageNoticeboardsTrait;
use App\Models\Certificate;
use App\Models\CourseNoticeboard;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentOrder;
use App\Models\Webinar;
use App\Models\WebinarAccessControl;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
class LearningPageController extends Controller
{
    use LearningPageMixinsTrait, LearningPageAssignmentTrait, LearningPageItemInfoTrait,
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

            $webinarController = new WebinarController();

            $data = $webinarController->course($slug, true);

            $data['directAccess']=0;

            $course = $data['course'] ?? null;

            if (!$course) {
                return back()->with('error', 'Course not found!');
            }
            $user = $data['user'];
             $course_pricess = $course->price;
              $cchapt=count($course->chapters);

             $installmentLimitation_limit = $webinarController->installmentContentLimitation_limit($user, $course->id, 'webinar_id');
             $installmentLimitation_limit1 = $webinarController->installmentContentLimitation_limit1($user, $course->id, 'webinar_id');

             $directAccess = WebinarAccessControl ::where('webinar_id', $course->id)
                ->where('user_id', $user->id)
                ->first();

             if($directAccess){

                 if(strtotime($directAccess->expire) > time()){

                     if($directAccess->percentage > $installmentLimitation_limit){
                         $installmentLimitation_limit =$directAccess->percentage;
                         $data['directAccess']=1;
                     }
                     if($directAccess->percentage==100){
                         $installmentLimitation_limit =$directAccess->percentage;
                         $data['directAccess']=1;
                     }
                 }
             }

            $P1 = ($installmentLimitation_limit/100)*$cchapt;

             $pchapters1=round($P1);

             $data['limit']=$pchapters1;

             $data['install_url']='/panel/financial/installments/'.$installmentLimitation_limit1.'/details';

            $installmentLimitation = $webinarController->installmentContentLimitation($user, $course->id, 'webinar_id');
            if ($installmentLimitation != "ok") {

            }

            if ((!$data or (!$data['hasBought'] and empty($course->getInstallmentOrder()))) and $data['directAccess']==0) {

                abort(403);
            }

            if (!empty($requestData['type']) and $requestData['type'] == 'assignment' and !empty($requestData['item'])) {

                $assignmentData = $this->getAssignmentData($course, $requestData);

                $data = array_merge($data, $assignmentData);
            }

            if ($course->creator_id != $user->id and $course->teacher_id != $user->id and !$user->isAdmin()) {
                $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $course->id)
                    ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();

                if ($unReadCourseNoticeboards) {
                    $url = $course->getNoticeboardsPageUrl();
                    return redirect($url);
                }
            }

            if ($course->certificate) {
                $data["courseCertificate"] = Certificate::where('type', 'course')
                    ->where('student_id', $user->id)
                    ->where('webinar_id', $course->id)
                    ->first();
            }

            $user = auth()->user();

            $order = InstallmentOrder::query()
                ->where('webinar_id', $course->id)
                ->where('user_id', $user->id)
                ->with([
                    'installment' => function ($query) {
                        $query->with([
                            'steps' => function ($query) {
                                $query->orderBy('deadline', 'asc');
                            }
                        ]);
                    }
                ])
                ->first();

            if (!empty($order) and !in_array($order->status, ['refunded', 'canceled'])) {

                $getRemainedInstallments = $this->getRemainedInstallments($order);
                $getOverdueOrderInstallments = $this->getOverdueOrderInstallments($order);

                $totalParts = $order->installment->steps->count();
                $remainedParts = $getRemainedInstallments['total'];
                $remainedAmount = $getRemainedInstallments['amount'];
                $overdueAmount = $getOverdueOrderInstallments['amount'];

            }

            $data["duedate"]=time();
            if(isset($order->installment)){
                $count=count($order->installment->steps);
                $paid=0;

              foreach($order->installment->steps as $step){

                                        $stepPayment = $order->payments->where('step_id', $step->id)->where('status', 'paid')->first();

                                        $dueAt = ($step->deadline * 86400) + $order->created_at;
                                        $isOverdue = ($dueAt < time() and empty($stepPayment));

                                     $duedate= dateTimeFormat($dueAt, 'j M Y');

                                     if($isOverdue==1)
                                     break;

                                     $paid++;

              }
              if($count!=$paid)
               $data["duedate"] =$dueAt;

            }

            $webinars = Webinar::where('webinars.status', 'active')
            ->where('private', false)

            ->get();

            $hasBought[]=false;
            foreach($webinars as $webinar){
            if($webinar->checkUserHasBought($user, true, true))
            {
                $hasBought[] = $webinar->id;
            }
            }

            $webinars = Webinar::where('webinars.status', 'active')
            ->where('private', false)
            ->whereNotIn('id', $hasBought)
            ->when($slug === 'learn-free-astrology-course-english', function ($q) {
            $q->whereIn('id', [2034,2099,2100]);
            })
            ->orderBy('order', 'asc')
            ->limit(5)
            ->get();

            $data["webinars"] =$webinars;
            $agent = new Agent();
            if ($agent->isMobile()){
                return view(getTemplate() . '.course.learningPage.index', $data);
            }else{
                return view('web.default2' . '.course.learningPage.index', $data);
            }
            // return view('web.default.course.learningPage.index', $data);
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