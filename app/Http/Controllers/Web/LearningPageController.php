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
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\AccessEngine;
use Illuminate\Http\Request;

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

            $data['directAccess'] = 0;

            $course = $data['course'] ?? null;

            if (!$course) {
                return back()->with('error', 'Course not found!');
            }
            $user = $data['user'];
            $cchapt = count($course->chapters);

            // ── UPE: Find product and sale ──
            $upeProduct = UpeProduct::where('external_id', $course->id)
                ->whereIn('product_type', ['course_video', 'webinar', 'course_live'])
                ->first();

            $upeSale = null;
            $installmentPlan = null;

            if ($upeProduct) {
                $upeSale = UpeSale::where('user_id', $user->id)
                    ->where('product_id', $upeProduct->id)
                    ->whereNotIn('status', ['refunded', 'cancelled', 'expired'])
                    ->orderByRaw("FIELD(status, 'active', 'completed', 'partially_refunded', 'pending_payment') ASC")
                    ->with(['installmentPlan.schedules'])
                    ->first();

                if ($upeSale && $upeSale->pricing_mode === 'installment') {
                    $installmentPlan = $upeSale->installmentPlan;
                }
            }

            // ── UPE: Access check via AccessEngine ──
            $accessEngine = app(AccessEngine::class);
            $accessResult = $upeProduct
                ? $accessEngine->computeAccess($user->id, $upeProduct->id)
                : null;

            // ── UPE: Content gating % (replaces installmentContentLimitation_limit) ──
            $contentPercent = 100;

            if ($installmentPlan && $installmentPlan->total_amount > 0) {
                $totalPaid = $installmentPlan->schedules->sum('amount_paid');
                $contentPercent = min(100, round(($totalPaid / $installmentPlan->total_amount) * 100));
            }

            // If user has full access (non-installment, completed plan, or special access types)
            if ($accessResult && $accessResult->hasAccess) {
                if (!$installmentPlan || $installmentPlan->status === 'completed') {
                    $contentPercent = 100;
                    $data['directAccess'] = 1;
                }
                if ($installmentPlan && in_array($accessResult->accessType, ['temporary', 'mentor', 'free'])) {
                    $contentPercent = 100;
                    $data['directAccess'] = 1;
                }
            }

            // Calculate accessible chapters
            $data['limit'] = round(($contentPercent / 100) * $cchapt);

            // ── UPE: Installment URL (replaces installmentContentLimitation_limit1) ──
            $data['install_url'] = $installmentPlan
                ? '/panel/upe/installments/' . $installmentPlan->id
                : '/panel/upe/purchases';

            // ── UPE: Access gate (replaces legacy hasBought + getInstallmentOrder) ──
            $hasUpeAccess = $accessResult && $accessResult->hasAccess;
            $hasInstallmentSale = $upeSale && $upeSale->pricing_mode === 'installment';

            if (!$hasUpeAccess && !$hasInstallmentSale && !$data['hasBought'] && $data['directAccess'] == 0) {
                if ($user->id != $course->creator_id && $user->id != $course->teacher_id && !$user->isAdmin()) {
                    abort(403);
                }
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

            // ── UPE: Due date for content dripping (replaces InstallmentOrder loop) ──
            $data['duedate'] = time();

            if ($installmentPlan) {
                $overdueSchedule = $installmentPlan->schedules
                    ->filter(function ($s) {
                        return in_array($s->status, ['due', 'partial', 'overdue'])
                            && $s->due_date
                            && $s->due_date->isPast();
                    })
                    ->sortBy('due_date')
                    ->first();

                if ($overdueSchedule) {
                    $data['duedate'] = $overdueSchedule->due_date->timestamp;
                }
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

            return view('web.default.course.learningPage.index', $data);
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