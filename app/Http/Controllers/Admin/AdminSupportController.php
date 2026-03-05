<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewSupportForAsttrolok;
use App\Models\NewSupportForAsttrolokLog;
use App\Models\User;
use App\Models\Sale;
use App\Models\Role;
use Carbon\Carbon;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentStep;
use App\Models\InstallmentRestructureRequest;
use App\Models\Webinar;
use Illuminate\Http\Request;
use App\Models\WebinarAccessControl;
use App\Models\WebinarPartPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SubStepInstallment;
use App\Models\SupportCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Accounting;
use App\Models\Refund;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Services\PaymentEngine\InstallmentEngine;
use App\Models\Transaction;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\Models\File;
use App\Models\SupportAuditLog;
use App\Services\AdminCoursePurchaseService;
use App\Services\SupportUpeBridge;
use Illuminate\Support\Facades\Storage;

class AdminSupportController extends Controller
{
     public function index(Request $request)
    {
        $this->authorize('admin_support_manage');

        $query = NewSupportForAsttrolok::with(['user', 'webinar.creator']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by scenario
        if ($request->filled('scenario')) {
            $query->where('support_scenario', $request->scenario);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $supportRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => NewSupportForAsttrolok::count(),
            'pending' => NewSupportForAsttrolok::where('status', 'pending')->count(),
            'in_review' => NewSupportForAsttrolok::where('status', 'in_review')->count(),
            'approved' => NewSupportForAsttrolok::where('status', 'approved')->count(),
            'rejected' => NewSupportForAsttrolok::where('status', 'rejected')->count(),
        ];

        $data = [
            'pageTitle' => 'Support Tickets Management',
            'supportRequests' => $supportRequests,
            'stats' => $stats
        ];

        return view('admin.supports.index', $data);
    }

    public function show($id)
    {
        $this->authorize('admin_support_manage');

        $supportRequest = NewSupportForAsttrolok::with(['user', 'webinar.creator'])
            ->findOrFail($id);

        $data = [
            'pageTitle' => 'Support Ticket - ' . $supportRequest->ticket_number,
            'supportRequest' => $supportRequest
        ];

        // For installment_restructure tickets, load UPE plan + schedules for admin
        if ($supportRequest->support_scenario === 'installment_restructure') {
            $restructureData = null;
            $executionResult = is_string($supportRequest->execution_result)
                ? json_decode($supportRequest->execution_result, true)
                : $supportRequest->execution_result;

            if ($executionResult && isset($executionResult['plan_id'])) {
                $plan = \App\Models\PaymentEngine\UpeInstallmentPlan::with(['schedules', 'sale.product'])
                    ->find($executionResult['plan_id']);

                if ($plan) {
                    $targetSchedule = isset($executionResult['schedule_id'])
                        ? \App\Models\PaymentEngine\UpeInstallmentSchedule::find($executionResult['schedule_id'])
                        : null;

                    $restructureData = [
                        'plan' => $plan,
                        'schedules' => $plan->schedules->sortBy('due_date'),
                        'target_schedule' => $targetSchedule,
                        'is_upfront' => $executionResult['is_upfront'] ?? false,
                        'schedule_amount' => $executionResult['schedule_amount'] ?? 0,
                        'schedule_remaining' => $executionResult['schedule_remaining'] ?? 0,
                        'upe_payment_request_id' => $executionResult['upe_payment_request_id'] ?? null,
                    ];
                }
            }

            // Fallback: if no execution_result, try to find UPE plan from webinar_id + user_id
            if (!$restructureData && $supportRequest->webinar_id && $supportRequest->user_id) {
                try {
                    $upeProduct = \App\Models\PaymentEngine\UpeProduct::where('external_id', $supportRequest->webinar_id)
                        ->whereIn('product_type', ['course_video', 'webinar'])
                        ->first();

                    if ($upeProduct) {
                        $plan = \App\Models\PaymentEngine\UpeInstallmentPlan::whereHas('sale', function ($q) use ($supportRequest, $upeProduct) {
                                $q->where('user_id', $supportRequest->user_id)->where('product_id', $upeProduct->id);
                            })
                            ->whereIn('status', ['active', 'completed'])
                            ->with(['schedules', 'sale.product'])
                            ->first();

                        if ($plan) {
                            $unpaidSchedules = $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue']);
                            $targetSchedule = $unpaidSchedules->sortBy('due_date')->first();
                            $isUpfront = $targetSchedule ? ($targetSchedule->sequence <= 1) : false;

                            $restructureData = [
                                'plan' => $plan,
                                'schedules' => $plan->schedules->sortBy('due_date'),
                                'target_schedule' => $targetSchedule,
                                'is_upfront' => $isUpfront,
                                'schedule_amount' => $targetSchedule ? (float) $targetSchedule->amount_due : 0,
                                'schedule_remaining' => $targetSchedule ? $targetSchedule->remainingAmount() : 0,
                                'upe_payment_request_id' => null,
                            ];

                            // Backfill execution_result on the ticket so future views don't need fallback
                            if ($targetSchedule) {
                                $supportRequest->update([
                                    'execution_result' => [
                                        'plan_id' => $plan->id,
                                        'schedule_id' => $targetSchedule->id,
                                        'schedule_sequence' => $targetSchedule->sequence,
                                        'schedule_amount' => (float) $targetSchedule->amount_due,
                                        'schedule_remaining' => $targetSchedule->remainingAmount(),
                                        'is_upfront' => $isUpfront,
                                    ],
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Fallback UPE plan lookup failed for restructure ticket', ['error' => $e->getMessage()]);
                }
            }

            $data['restructureData'] = $restructureData;
        }

        // For offline_cash_payment tickets, load price breakdown + available installment plans
        if ($supportRequest->support_scenario === 'offline_cash_payment' && $supportRequest->webinar_id) {
            $webinar = $supportRequest->webinar;
            $coursePrice = $webinar ? $webinar->getPrice() : 0;
            $cashAmount = (float) ($supportRequest->cash_amount ?? 0);

            $data['offlinePaymentData'] = [
                'course_price' => $coursePrice,
                'cash_amount' => $cashAmount,
                'remaining' => max(0, $coursePrice - $cashAmount),
                'is_underpaid' => $cashAmount < ($coursePrice - 1),
                'is_installment_available' => false,
                'installment_plans' => [],
            ];

            // Load available installment plans for this course (using student's context, not admin's)
            $studentUser = $supportRequest->user_id ? \App\User::find($supportRequest->user_id) : null;
            $showInstallments = $webinar
                && !empty($webinar->price) && $webinar->price > 0
                && getInstallmentsSettings('status')
                && (empty($studentUser) || !empty($studentUser->enable_installments));

            if ($showInstallments) {
                $installmentPlans = new \App\Mixins\Installment\InstallmentPlans($studentUser);
                $installments = $installmentPlans->getPlans(
                    'courses',
                    $webinar->id,
                    $webinar->type,
                    $webinar->category_id,
                    $webinar->teacher_id
                );
                $installments->loadCount('steps');

                if ($installments->isNotEmpty()) {
                    $data['offlinePaymentData']['is_installment_available'] = true;
                    $plans = [];
                    foreach ($installments as $inst) {
                        $upfront = round($inst->getUpfront($coursePrice), 0, PHP_ROUND_HALF_UP);
                        $plans[] = [
                            'id' => $inst->id,
                            'title' => $inst->title ?? ('Plan #' . $inst->id),
                            'upfront' => $upfront,
                            'steps_count' => $inst->steps_count,
                            'total_emis' => $inst->steps_count + 1,
                        ];
                    }
                    $data['offlinePaymentData']['installment_plans'] = $plans;
                }
            }
        }

        // ── Load data for "Process / Take Action" form ──
        $isFinalStatus = in_array($supportRequest->status, ['completed', 'executed', 'closed', 'rejected']);
        if (!$isFinalStatus) {
            // All active webinars
            $data['allWebinars'] = Webinar::where('status', 'active')
                ->select('id', 'creator_id')
                ->get()
                ->map(fn($w) => ['id' => $w->id, 'title' => $w->title ?? 'Course #' . $w->id]);

            $studentUserId = $supportRequest->user_id;
            if ($studentUserId) {
                $studentUser = \App\User::find($studentUserId);

                // Student's purchased courses (from UPE) - EXCLUDE NON-PAID ACCESS
                $purchasedIds = $studentUser ? $studentUser->getPurchasedCoursesIds() : [];
                $allPurchasedWebinars = !empty($purchasedIds)
                    ? Webinar::whereIn('id', $purchasedIds)->where('status', 'active')->get()
                    : collect();
                
                $filteredPurchases = collect();
                foreach ($allPurchasedWebinars as $webinar) {
                    // Check if this is non-paid access (exclude from refundable courses)
                    $accessEngine = app(\App\Services\PaymentEngine\AccessEngine::class);
                    
                    // Find the UPE product for this webinar
                    $productTypes = ['course_video', 'webinar'];
                    $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
                        ->where('external_id', $webinar->id)
                        ->first();
                    
                    if ($upeProduct) {
                        $accessResult = $accessEngine->computeAccess($studentUserId, $upeProduct->id);
                        
                        // Exclude courses accessed for free, through mentor, or temporary access
                        if ($accessResult->hasAccess && in_array($accessResult->accessType, ['free', 'mentor', 'temporary'])) {
                            continue; // Skip non-paid access courses
                        }
                    }
                    
                    $filteredPurchases->push(['id' => $webinar->id, 'title' => $webinar->title ?? 'Course #' . $webinar->id]);
                }
                
                $data['studentPurchases'] = $filteredPurchases;

                // Expired courses (for extension scenario)
                $expiredList = [];
                try {
                    $expiredUpeSales = \App\Models\PaymentEngine\UpeSale::where('user_id', $studentUserId)
                        ->whereIn('status', ['active', 'partially_refunded', 'pending_payment', 'completed'])
                        ->whereNotNull('valid_until')
                        ->where('valid_until', '<', now())
                        ->with('product')
                        ->get();
                    foreach ($expiredUpeSales as $es) {
                        if (!$es->product || !in_array($es->product->product_type, ['course_video', 'webinar'])) continue;
                        $wId = $es->product->external_id;
                        $w = Webinar::find($wId);
                        if ($w) $expiredList[] = ['id' => $w->id, 'title' => $w->title ?? 'Course #' . $w->id];
                    }
                } catch (\Exception $e) {}
                $data['expiredCourses'] = collect($expiredList)->unique('id')->values();

                // Installment courses
                $installmentList = [];
                try {
                    $upePlans = \App\Models\PaymentEngine\UpeInstallmentPlan::whereHas('sale', fn($q) => $q->where('user_id', $studentUserId))
                        ->whereIn('status', ['active', 'completed'])
                        ->with(['sale.product'])
                        ->get();
                    foreach ($upePlans as $plan) {
                        $product = $plan->sale->product ?? null;
                        if (!$product || !in_array($product->product_type, ['course_video', 'webinar'])) continue;
                        $w = Webinar::find($product->external_id);
                        if ($w) $installmentList[] = ['id' => $w->id, 'title' => $w->title ?? 'Course #' . $w->id];
                    }
                } catch (\Exception $e) {}
                $data['installmentCourses'] = collect($installmentList)->unique('id')->values();

                // Refundable courses
                $refundableList = [];
                try {
                    $paidSales = \App\Models\PaymentEngine\UpeSale::where('user_id', $studentUserId)
                        ->whereIn('status', ['active', 'partially_refunded'])
                        ->where('base_fee_snapshot', '>', 0)
                        ->with('product')
                        ->get();
                    
                    foreach ($paidSales as $s) {
                        if (!$s->product || !in_array($s->product->product_type, ['course_video', 'webinar'])) continue;
                        
                        // Check if this is non-paid access (exclude from refundable courses)
                        $accessEngine = app(\App\Services\PaymentEngine\AccessEngine::class);
                        $accessResult = $accessEngine->computeAccess($studentUserId, $s->product->id);
                        
                        // Exclude courses accessed for free, through mentor, or temporary access
                        if ($accessResult->hasAccess && in_array($accessResult->accessType, ['free', 'mentor', 'temporary'])) {
                            continue; // Skip non-paid access courses
                        }
                        
                        $w = Webinar::find($s->product->external_id);
                        if ($w) $refundableList[] = ['id' => $w->id, 'title' => $w->title ?? 'Course #' . $w->id];
                    }
                } catch (\Exception $e) {}
                $data['refundableCourses'] = collect($refundableList)->unique('id')->values();
            } else {
                $data['studentPurchases'] = collect();
                $data['expiredCourses'] = collect();
                $data['installmentCourses'] = collect();
                $data['refundableCourses'] = collect();
            }
        }

        return view('admin.supports.show', $data);
    }

    // public function updateStatus(Request $request, $id)
    // {
    //     $supportRequest = NewSupportForAsttrolok::findOrFail($id);
    //     // Custom validation based on role and field visibility
    //     $rules = [
    //         'status' => 'required|in:pending,in_review,approved,completed,rejected,executed,closed',
    //         'admin_remarks' => 'required_if:status,completed|string',
    //         'rejection_reason' => 'required_if:status,rejected|string|nullable',
    //     ];
        
    //     // Support remarks required for both Support Role and Admin (since both can see the field)
    //     if (Auth::user()->role_name === 'Support Role' || Auth::user()->role_name === 'admin') {
    //         $rules['support_remarks'] = 'required|string';
    //     }
        
    //     $validated = $request->validate($rules);

    //     // Block Support users from setting completed status
    //     if (Auth::user()->role_name === 'Support Role' && $validated['status'] === 'completed') {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Support users cannot mark tickets as completed. Only Admin can set completed status.'
    //         ], 403);
    //     }

    //     if (
    //     $validated['status'] === 'approved' &&
    //     $supportRequest->support_scenario === 'temporary_access' &&
    //     Auth::user()->role_name === 'admin'
    //  ) {
    //     $request->validate([
    //         'temporary_access_percentage' => 'required|integer|min:1|max:100',
    //     ], [
    //         'temporary_access_percentage.required' =>
    //             'Approve karne se pehle percentage dalna mandatory hai',
    //     ]);
    //  }


    //     try {
    //         \Log::info('updateStatus called', [
    //             'request_data' => $request->all(),
    //             'support_id' => $id
    //         ]);
    //         $supportRequest = NewSupportForAsttrolok::findOrFail($id);

    //         if(Auth::user()->role_name === 'Support Role'){

    //         if($supportRequest->status == 'approved' || $supportRequest->status == 'executed' || $supportRequest->status == 'closed' || $validated['status'] == 'pending'){
    //                     $toastData = [
    //                         'title' => 'user action not allowed',
    //                         'msg' => 'already approved or rejected or executed or closed tickets cannot be changed or status cannot be changed to pending',
    //                         'status' => 'error'
    //                     ];
    
    //                     return redirect()->back()->with(['toast' => $toastData]);
    //                 }
    //         }

    //         $oldStatus = $supportRequest->status;
    //         $updateData = [
    //             'status' => $validated['status'],
    //         ];
    //         if ($validated['status'] === 'in_review') {
    //             $updateData['support_handler_id'] = Auth::id();
    //         }
    //         if ($validated['status'] === 'approved') {
    //             // Support user approval - store support handler ID, no course access
    //             if (Auth::user()->role_name === 'Support Role') {
    //                 $updateData['support_handler_id'] = Auth::id();
    //                 $updateData['support_remarks'] = $request->remarks;
    //                 \Log::info('Support user approved - no course access granted', [
    //                     'support_request_id' => $id,
    //                     'support_handler_id' => Auth::id(),
    //                     'remarks' => $request->remarks,
    //                     'updateData' => $updateData
    //                 ]);
    //             } else {
    //                 // Admin approval - NO course access granted at this stage
    //                 $updateData['sub_admin_id'] = Auth::id();
    //                 $updateData['approved_at'] = now();
    //                 $updateData['approval_remarks'] = $request->remarks;
    //                 $webinarId = $supportRequest->webinar_id;
    //                 $userId = $supportRequest->user_id;
    //                 $scenario = $supportRequest->support_scenario;
    //                 \Log::info('Admin approved - course access will be granted on completion only', [
    //                     'webinar_id' => $webinarId,
    //                     'user_id' => $userId,
    //                     'support_scenario' => $scenario
    //                 ]);
    //             }


                
                
    //             if(auth()->user()->role == 'admin'){
    //                 $updateData['sub_admin_id'] = Auth::id();
    //             }else{
    //                 $updateData['support_handler_id'] = Auth::id();
    //             }
    //             $updateData['approved_at'] = now();
    //             $updateData['support_remarks'] = $request->support_remarks;
    //             $updateData['approval_remarks'] = $request->admin_remarks;
    //         }

    //         if ($validated['status'] === 'completed') {

    //             if($supportRequest->support_scenario == 'installment_restructure'){
                    
    //                $this->createInstallmentRestructureRequestFromSupport($supportRequest);
    //                $this->adminApproveRestructure($supportRequest);
    //             }

                
                
    //             if(auth()->user()->role == 'admin'){
    //                 $updateData['sub_admin_id'] = Auth::id();
    //             }else{
    //                 $updateData['support_handler_id'] = Auth::id();
    //             }
    //             $updateData['approved_at'] = now();
    //             $updateData['support_remarks'] = $request->support_remarks;
    //             $updateData['approval_remarks'] = $request->admin_remarks;
    //         }
    //         if ($validated['status'] === 'rejected') {
    //             // For rejected status, set sub_admin_id (admin who rejected) and clear support_handler_id
    //             $updateData['sub_admin_id'] = Auth::id();
    //             $updateData['support_handler_id'] = null; // Clear support handler for admin rejection
    //             if(auth()->user()->role == 'admin'){
    //                 $updateData['sub_admin_id'] = Auth::id();
    //             }else{
    //                 $updateData['support_handler_id'] = Auth::id();
    //             }
                
    //             $updateData['support_handler_id'] = Auth::id();
    //             $updateData['rejected_at'] = now();
    //             $updateData['rejection_reason'] = $validated['rejection_reason'];
    //         }
    //         if ($validated['status'] === 'completed') {
    //             // Admin completion - grant course access
    //             if (Auth::user()->role_name !== 'admin') {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Only Admin can mark tickets as completed.'
    //                 ], 403);
    //             }
    //             // For completed status, set sub_admin_id (admin who completed) and clear support_handler_id
    //             $updateData['sub_admin_id'] = Auth::id();
    //             $updateData['support_handler_id'] = null; // Clear support handler for admin completion
    //             $updateData['approved_at'] = now();
    //             $updateData['approval_remarks'] = $request->admin_remarks;
    //             $webinarId = $supportRequest->webinar_id;
    //             $userId = $supportRequest->user_id;
    //             $scenario = $supportRequest->support_scenario;
    //             \Log::info('Admin completed - granting course access', [
    //                 'webinar_id' => $webinarId,
    //                 'user_id' => $userId,
    //                 'support_scenario' => $scenario
    //             ]);
    //              if ($supportRequest->support_scenario === 'relatives_friends_access' &&  $validated['status'] === 'completed') {
    //             // Grant course access logic here (same as admin approval)
    //             if ($webinarId) {
    //                 $webinar = \App\Models\Webinar::find($webinarId);
    //                 $accessDays = (int)($webinar->access_days ?? 0);
    //                 $usersToGrant = [];
                    
    //                 // Always add main user
    //                 if ($userId) {
    //                     $mainUser = \App\User::find($userId);
    //                     if ($mainUser) {
    //                         $usersToGrant[] = $mainUser;
    //                     }
    //                 }
                    
    //                 // For relative access, only main user gets access (no relative user creation)
    //                 // Relative details are now only in description field
                    
    //                 // Grant course to all users in array
    //                 foreach ($usersToGrant as $grantUser) {
    //                     if ($grantUser && $webinar) {
    //                         $sale = new \App\Models\Sale();
    //                         $sale->buyer_id = $grantUser->id;
    //                         $sale->seller_id = $webinar->creator_id;
    //                         $sale->webinar_id = $webinar->id;
    //                         $sale->type = \App\Models\Sale::$webinar;
    //                         $sale->manual_added = true;
    //                         $sale->payment_method = \App\Models\Sale::$credit;
    //                         $sale->amount = 0;
    //                         $sale->total_amount = 0;
    //                         $sale->access_to_purchased_item = 1;
    //                         $sale->created_at = time();
    //                         $sale->save();
    //                         \Log::info('Sale created for user (completed status)', [
    //                             'sale_id' => $sale->id,
    //                             'buyer_id' => $sale->buyer_id,
    //                             'webinar_id' => $sale->webinar_id
    //                         ]);
    //                     }
    //                 }
                    
    //                 // Set purchase/expiry dates on support request (for main user only)
    //                 $updateData['course_purchased_at'] = now();
    //                 $updateData['purchase_status'] = 'purchased';
    //                 if ($accessDays > 0) {
    //                     $expiry = now()->addDays($accessDays);
    //                     $updateData['course_expires_at'] = $expiry;
    //                 } else {
    //                     $updateData['course_expires_at'] = null;
    //                 }
    //             }

    //         }
                
    //             // If scenario is mentor_access, set user as mentor (only on admin completion)
    //             if ($scenario === 'mentor_access' && $userId) {
    //                 $mainUser = \App\User::find($userId);
    //                 if ($mainUser) {
    //                     // Create mentor access request entry
    //                     \App\Models\MentorAccessRequest::create([
    //                         'user_id' => $userId,
    //                         'webinar_id' => $webinarId,
    //                         'requested_mentor_id' => $supportRequest->requested_mentor_id,
    //                         'mentor_change_reason' => $supportRequest->description,
    //                         'status' => 'completed',
    //                         'admin_notes' => $request->admin_remarks,
    //                         'approved_by' => Auth::id(),
    //                         'approved_at' => now(),
    //                     ]);
                        
    //                     \Log::info('Mentor access request created in table', [
    //                         'user_id' => $userId,
    //                         'webinar_id' => $webinarId,
    //                         'requested_mentor_id' => $supportRequest->requested_mentor_id,
    //                         'approved_by' => Auth::id()
    //                     ]);
                        
    //                     // Keep user as student, don't change role to teacher
    //                     // $mainUser->role_name = \App\Models\Role::$teacher;
    //                     // $mainUser->role_id = \App\Models\Role::getTeacherRoleId();
    //                     // $mainUser->save();
                        
    //                     \Log::info('Mentor access completed - user remains as student', [
    //                         'user_id' => $mainUser->id,
    //                         'role_name' => $mainUser->role_name,
    //                         'webinar_id' => $webinarId
    //                     ]);
    //                 }
    //             }
                
    //             // If scenario is free_course_grant, grant free access to all users
    //             if ($scenario === 'free_course_grant') {
    //                 $sourceCourseId = $supportRequest->source_course_id;
    //                 $targetCourseId = $supportRequest->target_course_id;
                    
    //                 if ($sourceCourseId && $targetCourseId) {
    //                     $sourceWebinar = \App\Models\Webinar::find($sourceCourseId);
    //                     $targetWebinar = \App\Models\Webinar::find($targetCourseId);
                        
    //                     // Get all users who have access to source course
    //                     $sourceUsers = \App\Models\Sale::where('webinar_id', $sourceCourseId)
    //                         ->where('access_to_purchased_item', 1)
    //                         ->pluck('buyer_id')
    //                         ->unique();
                        
    //                     $grantedCount = 0;
    //                     $alreadyHasAccess = 0;
                        
    //                     foreach ($sourceUsers as $sourceUserId) {
    //                         $user = \App\User::find($sourceUserId);
    //                         if (!$user) continue;
                            
    //                         // Check if user already has access to target course
    //                         $existingSale = \App\Models\Sale::where('buyer_id', $sourceUserId)
    //                             ->where('webinar_id', $targetCourseId)
    //                             ->where('access_to_purchased_item', 1)
    //                             ->first();
                            
    //                         if ($existingSale) {
    //                             $alreadyHasAccess++;
    //                             continue;
    //                         }
                            
    //                         // Grant free access to target course
    //                         $sale = new \App\Models\Sale();
    //                         $sale->buyer_id = $sourceUserId;
    //                         $sale->seller_id = $targetWebinar->creator_id;
    //                         $sale->webinar_id = $targetCourseId;
    //                         $sale->type = \App\Models\Sale::$webinar;
    //                         $sale->manual_added = true;
    //                         $sale->payment_method = \App\Models\Sale::$credit;
    //                         $sale->amount = 0;
    //                         $sale->total_amount = 0;
    //                         $sale->access_to_purchased_item = 1;
    //                         $sale->created_at = time();
    //                         $sale->save();
                            
    //                         $grantedCount++;
    //                     }
                        
    //                     \Log::info('Quick Support Form - Free Access Granted on Completion', [
    //                         'admin_id' => Auth::id(),
    //                         'support_request_id' => $id,
    //                         'source_course' => $sourceWebinar->slug,
    //                         'target_course' => $targetWebinar->slug,
    //                         'total_users' => $sourceUsers->count(),
    //                         'granted_access' => $grantedCount,
    //                         'already_had_access' => $alreadyHasAccess
    //                     ]);
                        
    //                     // Update request with results
    //                     $updateData['course_purchased_at'] = now();
    //                     $updateData['purchase_status'] = 'completed';
    //                     $updateData['granted_users_count'] = $grantedCount;
    //                     $updateData['already_had_access_count'] = $alreadyHasAccess;
    //                 }
    //             }
    //         }
    //         // Final update - remarks already set in status logic above
    //         \Log::info('Before final update - admin_remarks: ' . ($updateData['approval_remarks'] ?? 'NOT SET'));
    //         \Log::info('Request admin_remarks: ' . ($request->admin_remarks ?? 'NOT SET'));
    //         $supportRequest->update($updateData);
            
    //         \Log::info('Update completed', [
    //             'support_request_id' => $id,
    //             'updateData' => $updateData,
    //             'final_approval_remarks' => $supportRequest->fresh()->approval_remarks,
    //             'final_support_handler_id' => $supportRequest->fresh()->support_handler_id,
    //             'final_support_remarks' => $supportRequest->fresh()->support_remarks
    //         ]);
    //         // 
    //     if (
    //         $validated['status'] === 'approved' &&
    //         $supportRequest->support_scenario === 'temporary_access' &&
    //         Auth::user()->role_name === 'admin'
    //     ) {
    //         $percentage = (int) $request->temporary_access_percentage;

    //                 $supportRequestModel = NewSupportForAsttrolok::find($supportRequest->id);
    //                 $supportRequestModel->update($updateData);

        
    //         if ($percentage < 1 || $percentage > 100) {
    //             throw new \Exception('Invalid percentage value');
    //         }


    //     $expireDate = now()->addDays(7);

    //     WebinarAccessControl::create([
    //         'user_id'    => $supportRequest->user_id,
    //         'webinar_id' => $supportRequest->webinar_id,
    //         'percentage' => $percentage,
    //         'expire'     => $expireDate,
    //     ]);
    //  }

     
    //     // 
    //     if (
    //         $validated['status'] === 'approved' &&
    //         $supportRequest->support_scenario === 'course_extension'
    //     ) {
    //         $extensionDays = (int) ($supportRequest->extension_days ?? 0);

    //         if (in_array($extensionDays, [7, 15, 30])) {


    //             $newExpireDate = now()->addDays($extensionDays);

            
    //             WebinarAccessControl::where('user_id', $supportRequest->user_id)
    //                 ->where('webinar_id', $supportRequest->webinar_id)
    //                 ->delete();

    //             WebinarAccessControl::create([
    //                 'user_id'    => $supportRequest->user_id,
    //                 'webinar_id' => $supportRequest->webinar_id,
    //                 'percentage' => 100,
    //                 'expire'     => $newExpireDate,
    //             ]);
    //         }
    //     }

    //     // 
    //                 // Create log
    //                 NewSupportForAsttrolokLog::create([
    //                     'support_request_id' => $supportRequest->id,
    //                     'user_id' => Auth::id(),
    //                     'action' => 'status_changed',
    //                     'remarks' => "Status changed from {$oldStatus} to {$validated['status']}. " . ($request->remarks ?? ''),
    //                     'old_data' => ['status' => $oldStatus],
    //                     'new_data' => ['status' => $validated['status']],
    //                     'ip_address' => $request->ip(),
    //                 ]);


    //                 $toastData = [
    //                     'title' => 'Success',
    //                     'msg' => 'Status updated successfully',
    //                     'status' => 'success'
    //                 ];
    //                 return redirect()->back()->with(['toast' => $toastData]);
    //             } catch (\Exception $e) {
    //                 DB::rollBack();
    //                 \Log::error('Support status update failed: ' . $e->getMessage(), [
    //                     'exception' => $e,
    //                     'request_data' => $request->all()
    //                 ]);
    //                 $toastData = [
    //                     'title' => 'Error',
    //                     'msg' => 'Failed to update status',
    //                     'status' => 'error'
    //                 ];
    //                 return back()->with(['toast' => $toastData]);
    //                 Log::error('Error getting user pending step: ' . $e->getMessage());
                    
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Error: ' . $e->getMessage()
    //                 ], 500);
    //             }
    // }

    // ── Role helpers ──────────────────────────────────────────────
    private function isAdminRole($user): bool
    {
        return $user->isAdmin() || in_array($user->role_name, ['admin', 'super_admin']);
    }

    private function isSupportRole($user): bool
    {
        return in_array($user->role_name, ['Support Role', 'support', 'Support']);
    }
    // ─────────────────────────────────────────────────────────────

    public function updateStatus(Request $request, $id)
    {
        $this->authorize('admin_support_manage');

        DB::beginTransaction();

        try {

            $supportRequest = NewSupportForAsttrolok::findOrFail($id);
            $oldStatus = $supportRequest->status;
            $user = Auth::user();

            /* ================= VALIDATION ================= */

            $rules = [
                'status' => 'required|in:pending,in_review,approved,completed,rejected,executed,closed',
                'admin_remarks' => 'required_if:status,completed|string',
                'rejection_reason' => 'required_if:status,rejected|string|nullable',
            ];

            if ($this->isSupportRole($user)) {
                $rules['support_remarks'] = 'required|string';
            } elseif ($this->isAdminRole($user)) {
                $rules['support_remarks'] = 'nullable|string';
            }

            // Only require temporary_access_percentage on approval (not rejection)
            if (
                $request->status === 'approved' &&
                $supportRequest->support_scenario === 'temporary_access' &&
                $this->isAdminRole($user)
            ) {
                $rules['temporary_access_percentage'] = 'required|integer|min:1|max:100';
            }

            // On rejection, support_remarks is optional
            if ($request->status === 'rejected') {
                $rules['support_remarks'] = 'nullable|string';
                $rules['admin_remarks'] = 'nullable|string';
            }

            $validated = $request->validate($rules);

            /* ================= SUPPORT ROLE BLOCK ================= */

            if (
                $this->isSupportRole($user) &&
                $validated['status'] === 'completed'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support users cannot mark tickets as completed.'
                ], 403);
            }

            if ($this->isSupportRole($user)) {
                if (
                    in_array($supportRequest->status, ['approved', 'executed', 'closed']) ||
                    $validated['status'] === 'pending'
                ) {
                    return back()->with([
                        'toast' => [
                            'title' => 'Not Allowed',
                            'msg' => 'Ticket cannot be changed',
                            'status' => 'error'
                        ]
                    ]);
                }
            }

            /* ================= BASE UPDATE ================= */

            $updateData = [
                'status' => $validated['status']
            ];

            /* ================= IN REVIEW ================= */

            if ($validated['status'] === 'in_review') {
                $updateData['support_handler_id'] = $user->id;
            }

            /* ================= APPROVED ================= */

            if ($validated['status'] === 'approved') {

                if ($this->isAdminRole($user)) {
                    $updateData['sub_admin_id'] = $user->id;
                } else {
                    $updateData['support_handler_id'] = $user->id;
                }

                if ($supportRequest->support_scenario === 'post_purchase_coupon') {
                    if ($this->isSupportRole($user)) {
                        $couponCode = strtoupper(trim($request->input('coupon_code')));
                    
                        if (empty($couponCode)) {
                            return back()->with([
                                'toast' => [
                                    'title' => 'apply coupon code',
                                    'msg' => 'Please enter a coupon code',
                                    'status' => 'error'
                                ]
                            ]);
                        }
                        $updateData['coupon_code'] = $request->coupon_code;
                    }

                    // LMS-039 FIX: Auto-execute coupon on admin approval.
                    // Check both the persisted coupon_code AND any just-set coupon_code from admin input.
                    if ($this->isAdminRole($user)) {
                        // Admin can also supply coupon_code directly at approval time
                        $adminCoupon = trim($request->input('coupon_code', ''));
                        if (!empty($adminCoupon)) {
                            $updateData['coupon_code'] = strtoupper($adminCoupon);
                            $supportRequest->coupon_code = strtoupper($adminCoupon);
                        }

                        if (!empty($supportRequest->coupon_code)) {
                            try {
                                $this->ApplyCouponCode($supportRequest);
                            } catch (\RuntimeException $e) {
                                DB::rollBack();
                                return back()->with([
                                    'toast' => [
                                        'title' => 'Coupon Error',
                                        'msg'   => $e->getMessage(),
                                        'status' => 'error',
                                    ]
                                ]);
                            }
                            $updateData['status'] = 'completed';
                            $updateData['executed_at'] = now();
                            $updateData['sub_admin_id'] = $user->id;
                        }
                    }
                }
               

                $updateData['approved_at'] = now();
                $updateData['support_remarks'] = $request->support_remarks;
                $updateData['approval_remarks'] = $request->admin_remarks;


                if ($supportRequest->support_scenario === 'temporary_access') {
                    $percentage = (int) $request->temporary_access_percentage;
                    if ($percentage < 1 || $percentage > 100) {
                            throw new \Exception('Invalid percentage value');
                        }
                  $updateData['temporary_access_percentage'] =  $percentage;
                  
                }
            }

            /* ================= REJECTED ================= */

            if ($validated['status'] === 'rejected') {

                if ($this->isAdminRole($user)) {
                    $updateData['sub_admin_id'] = $user->id;
                } else {
                    $updateData['support_handler_id'] = $user->id;
                }

                $updateData['rejected_at'] = now();
                $updateData['rejection_reason'] = $validated['rejection_reason'];

                // Also reject the linked UpePaymentRequest so the student can create a new ticket
                $this->rejectLinkedUpeRequest($supportRequest, $validated['rejection_reason'] ?? 'Rejected by admin');
            }

            /* ================= COMPLETED (ADMIN ONLY) ================= */

            if ($validated['status'] === 'completed') {

                if (!$this->isAdminRole($user)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Admin can mark completed.'
                    ], 403);
                }

                // IDEMPOTENCY GUARD: Prevent double-execution of completed tickets
                if ($supportRequest->executed_at !== null) {
                    return back()->with([
                        'toast' => [
                            'title' => 'Already Executed',
                            'msg' => 'This ticket has already been executed. Cannot re-execute.',
                            'status' => 'error'
                        ]
                    ]);
                }

                $updateData['sub_admin_id'] = $user->id;
                $updateData['support_handler_id'] = null;
                $updateData['approved_at'] = now();
                $updateData['approval_remarks'] = $request->admin_remarks;
                $updateData['executed_at'] = now();

                /* ---------- INSTALLMENT RESTRUCTURE ---------- */
                if ($supportRequest->support_scenario === 'installment_restructure') {
                    $this->adminApproveRestructure($supportRequest);
                }

                /* ---------- RELATIVE / FRIEND ACCESS ---------- */
                if ($supportRequest->support_scenario === 'relatives_friends_access') {

                    $webinar = \App\Models\Webinar::find($supportRequest->webinar_id);
                    $userMain = \App\User::find($supportRequest->user_id);

                    if ($webinar && $userMain) {
                        $sale = new \App\Models\Sale();
                        $sale->buyer_id = $userMain->id;
                        $sale->seller_id = $webinar->creator_id;
                        $sale->webinar_id = $webinar->id;
                        $sale->type = \App\Models\Sale::$webinar;
                        $sale->manual_added = true;
                        $sale->payment_method = \App\Models\Sale::$credit;
                        $sale->amount = 0;
                        $sale->total_amount = 0;
                        $sale->access_to_purchased_item = 1;
                        $sale->created_at = time();
                        $sale->save();

                        // UPE: Create UPE sale so AccessEngine grants access
                        $bridge = app(SupportUpeBridge::class);
                        $bridge->grantRelativeAccess($userMain->id, $webinar->id, $supportRequest->id, $user->id);

                        $updateData['course_purchased_at'] = now();
                        $updateData['purchase_status'] = 'purchased';
                    }
                }

                /* ---------- MENTOR ACCESS ---------- */
                if ($supportRequest->support_scenario === 'mentor_access') {
                    // First, grant course access to the user
                    $webinar = \App\Models\Webinar::find($supportRequest->webinar_id);
                    $userMain = \App\User::find($supportRequest->user_id);

                    if ($webinar && $userMain) {
                        // UPE: Create UPE sale so AccessEngine grants access
                        $bridge = app(SupportUpeBridge::class);
                        $bridge->grantMentorAccess($userMain->id, $webinar->id, $supportRequest->id, $user->id);

                        // Check if user already has access
                        $existingSale = \App\Models\Sale::where('buyer_id', $userMain->id)
                            ->where('webinar_id', $webinar->id)
                            ->where('access_to_purchased_item', 1)
                            ->first();

                        if (!$existingSale) {
                            // Create Sale record to grant access
                            $sale = new \App\Models\Sale();
                            $sale->buyer_id = $userMain->id;
                            $sale->seller_id = $webinar->creator_id;
                            $sale->webinar_id = $webinar->id;
                            $sale->type = \App\Models\Sale::$webinar;
                            $sale->manual_added = true;
                            $sale->payment_method = \App\Models\Sale::$credit;
                            $sale->amount = 0;
                            $sale->total_amount = 0;
                            $sale->access_to_purchased_item = 1;
                            $sale->created_at = time();
                            $sale->save();

                            $updateData['course_purchased_at'] = now();
                            $updateData['purchase_status'] = 'purchased';
                        }
                    }

                    // Create MentorAccessRequest record
                    \App\Models\MentorAccessRequest::create([
                        'user_id' => $supportRequest->user_id,
                        'webinar_id' => $supportRequest->webinar_id,
                        'requested_mentor_id' => $supportRequest->requested_mentor_id,
                        'mentor_change_reason' => $supportRequest->description,
                        'status' => 'completed',
                        'admin_notes' => $request->admin_remarks,
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]);
                }

                /* ---------- free_course_grant ---------- */
                if ($supportRequest->support_scenario === 'free_course_grant') {

                    $sourceCourseId = $supportRequest->source_course_id;
                    $targetCourseId = $supportRequest->target_course_id ?? $supportRequest->webinar_id;
                    $bridge = app(SupportUpeBridge::class);

                    if ($sourceCourseId && $targetCourseId) {
                        // ── Batch mode: grant to ALL users of source course ──
                        $sourceWebinar = \App\Models\Webinar::find($sourceCourseId);
                        $targetWebinar = \App\Models\Webinar::find($targetCourseId);

                        $sourceUserIds = \App\Models\Sale::where('webinar_id', $sourceCourseId)
                            ->where('access_to_purchased_item', 1)
                            ->pluck('buyer_id')
                            ->unique()
                            ->toArray();

                        $sourceProduct = $bridge->resolveProductId($sourceCourseId);
                        if ($sourceProduct) {
                            $upeSourceUserIds = \App\Models\PaymentEngine\UpeSale::where('product_id', $sourceProduct)
                                ->whereNotIn('status', ['cancelled', 'refunded'])
                                ->pluck('user_id')
                                ->unique()
                                ->toArray();
                            $sourceUserIds = array_unique(array_merge($sourceUserIds, $upeSourceUserIds));
                        }

                        $grantedCount = 0;
                        $alreadyHasAccess = 0;
                        $targetProduct = $bridge->resolveProductId($targetCourseId);

                        foreach ($sourceUserIds as $sourceUserId) {
                            $userObj = \App\User::find($sourceUserId);
                            if (!$userObj) continue;

                            $existingSale = \App\Models\Sale::where('buyer_id', $sourceUserId)
                                ->where('webinar_id', $targetCourseId)
                                ->where('access_to_purchased_item', 1)
                                ->first();

                            $existingUpe = $targetProduct
                                ? \App\Models\PaymentEngine\UpeSale::where('user_id', $sourceUserId)
                                    ->where('product_id', $targetProduct)
                                    ->whereNotIn('status', ['cancelled', 'refunded'])
                                    ->exists()
                                : false;

                            if ($existingSale || $existingUpe) {
                                $alreadyHasAccess++;
                                continue;
                            }

                            $sale = new \App\Models\Sale();
                            $sale->buyer_id = $sourceUserId;
                            $sale->seller_id = $targetWebinar->creator_id;
                            $sale->webinar_id = $targetCourseId;
                            $sale->type = \App\Models\Sale::$webinar;
                            $sale->manual_added = true;
                            $sale->payment_method = \App\Models\Sale::$credit;
                            $sale->amount = 0;
                            $sale->total_amount = 0;
                            $sale->access_to_purchased_item = 1;
                            $sale->created_at = time();
                            $sale->save();

                            $bridge->grantFreeCourseAccess($sourceUserId, $targetCourseId, $supportRequest->id, Auth::id());
                            $grantedCount++;
                        }

                        $updateData['course_purchased_at'] = now();
                        $updateData['purchase_status'] = 'completed';
                        $updateData['granted_users_count'] = $grantedCount;
                        $updateData['already_had_access_count'] = $alreadyHasAccess;

                    } elseif ($targetCourseId) {
                        // ── Individual mode: grant only the ticket submitter ──
                        $targetWebinar = \App\Models\Webinar::find($targetCourseId);
                        $grantUserId   = $supportRequest->user_id;

                        if ($targetWebinar && $grantUserId) {
                            $existingSale = \App\Models\Sale::where('buyer_id', $grantUserId)
                                ->where('webinar_id', $targetCourseId)
                                ->where('access_to_purchased_item', 1)
                                ->first();

                            $targetProduct = $bridge->resolveProductId($targetCourseId);
                            $existingUpe   = $targetProduct
                                ? \App\Models\PaymentEngine\UpeSale::where('user_id', $grantUserId)
                                    ->where('product_id', $targetProduct)
                                    ->whereNotIn('status', ['cancelled', 'refunded'])
                                    ->exists()
                                : false;

                            if (!$existingSale && !$existingUpe) {
                                $sale = new \App\Models\Sale();
                                $sale->buyer_id = $grantUserId;
                                $sale->seller_id = $targetWebinar->creator_id;
                                $sale->webinar_id = $targetCourseId;
                                $sale->type = \App\Models\Sale::$webinar;
                                $sale->manual_added = true;
                                $sale->payment_method = \App\Models\Sale::$credit;
                                $sale->amount = 0;
                                $sale->total_amount = 0;
                                $sale->access_to_purchased_item = 1;
                                $sale->created_at = time();
                                $sale->save();

                                $bridge->grantFreeCourseAccess($grantUserId, $targetCourseId, $supportRequest->id, Auth::id());
                            }

                            $updateData['course_purchased_at'] = now();
                            $updateData['purchase_status'] = 'completed';
                        }
                    }
                }
               /* ---------- temporary_access ---------- */
                if ($supportRequest->support_scenario === 'temporary_access' && $this->isAdminRole(Auth::user())) {

                        $expireDate = now()->addDays(7);
                        $percentage = $supportRequest->temporary_access_percentage ?? 100;

                        WebinarAccessControl::create([
                            'user_id'    => $supportRequest->user_id,
                            'webinar_id' => $supportRequest->webinar_id,
                            'percentage' => $percentage,
                            'expire'     => $expireDate,
                        ]);

                        // UPE: Create temporary access support action so AccessEngine grants access
                        $bridge = app(SupportUpeBridge::class);
                        $bridge->grantTemporaryAccess(
                            $supportRequest->user_id,
                            $supportRequest->webinar_id,
                            $supportRequest->id,
                            $user->id,
                            7,
                            $percentage
                        );
                    }

     
               
                    if ($supportRequest->support_scenario === 'course_extension') {
                    $extensionDays = (int) ($supportRequest->extension_days ?? 0);

                    if (in_array($extensionDays, [7, 15, 30])) {

                        $newExpireDate = now()->addDays($extensionDays);

                        // Replace existing access record with new expiry
                        WebinarAccessControl::where('user_id', $supportRequest->user_id)
                            ->where('webinar_id', $supportRequest->webinar_id)
                            ->delete();

                        WebinarAccessControl::create([
                            'user_id'    => $supportRequest->user_id,
                            'webinar_id' => $supportRequest->webinar_id,
                            'percentage' => 100,
                            'expire'     => $newExpireDate,
                        ]);

                        // UPE: Create extension sale so AccessEngine grants access
                        $bridge = app(SupportUpeBridge::class);
                        $bridge->grantCourseExtension(
                            $supportRequest->user_id,
                            $supportRequest->webinar_id,
                            $supportRequest->id,
                            $user->id,
                            $extensionDays
                        );
                    }
                }

                 if ($supportRequest->support_scenario === 'post_purchase_coupon') {
                     try {
                         $this->ApplyCouponCode($supportRequest);
                     } catch (\RuntimeException $e) {
                         DB::rollBack();
                         return back()->with([
                             'toast' => [
                                 'title' => 'Coupon Error',
                                 'msg'   => $e->getMessage(),
                                 'status' => 'error',
                             ]
                         ]);
                     }
                 }
                 /* ---------- offline_cash_payment ---------- */
                if ($supportRequest->support_scenario === 'offline_cash_payment') {
                     $cashAmount = (float) ($supportRequest->cash_amount ?? 0);

                     if ($cashAmount > 0 && $supportRequest->webinar_id) {
                         $bridge = app(SupportUpeBridge::class);
                         $couponCode = $request->input('offline_coupon_code') ?: ($supportRequest->coupon_code ?? null);
                         $installmentId = $request->input('offline_installment_id') ? (int) $request->input('offline_installment_id') : null;

                         $offlineResult = $bridge->processOfflinePayment(
                             $supportRequest->user_id,
                             $supportRequest->webinar_id,
                             $supportRequest->id,
                             $user->id,
                             $cashAmount,
                             $couponCode,
                             $installmentId
                         );

                         if (!$offlineResult['success']) {
                             DB::rollBack();
                             return back()->with([
                                 'toast' => [
                                     'title' => 'Payment Failed',
                                     'msg' => $offlineResult['message'],
                                     'status' => 'error',
                                 ],
                             ]);
                         }

                         // Store serializable audit data (strip Eloquent objects)
                         $supportRequest->update([
                             'execution_result' => [
                                 'success' => $offlineResult['success'],
                                 'message' => $offlineResult['message'],
                                 'sale_id' => $offlineResult['sale']?->id,
                                 'plan_id' => $offlineResult['plan']?->id,
                                 'price_breakdown' => $offlineResult['price_breakdown'],
                                 'allocation' => $offlineResult['allocation'],
                                 'access_granted' => $offlineResult['access_granted'],
                             ],
                         ]);
                     }
                }
                 /* ---------- refund_payment ---------- */
                if ($supportRequest->support_scenario === 'refund_payment') {
                     $this->refundPayment($supportRequest);
                }
            }

            /* ================= FINAL UPDATE ================= */

            $supportRequest->update($updateData);

            /* ================= AUDIT LOG ================= */
            SupportAuditLog::log(
                $supportRequest->id,
                $user->id,
                $validated['status'],
                $user->role_name ?? 'unknown',
                $oldStatus,
                $validated['status'],
                array_filter([
                    'scenario' => $supportRequest->support_scenario,
                    'admin_remarks' => $request->admin_remarks ?? null,
                    'support_remarks' => $request->support_remarks ?? null,
                    'rejection_reason' => $validated['rejection_reason'] ?? null,
                ]),
                $request->ip()
            );

            DB::commit();

            return back()->with([
                'toast' => [
                    'title' => 'Success',
                    'msg' => 'Status updated successfully',
                    'status' => 'success'
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Support ticket update failed', [
                'support_request_id' => $id,
                'scenario' => $supportRequest->support_scenario ?? 'unknown',
                'target_status' => $validated['status'] ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMsg = 'Failed to update: ' . class_basename($e) . ' — ' . \Str::limit($e->getMessage(), 200);

            return back()->with([
                'toast' => [
                    'title' => 'Error',
                    'msg' => $errorMsg,
                    'status' => 'error'
                ]
            ]);
        }
    }



    /**
     * Secure status update using SupportRequestService with strict 3-step workflow.
     * Status transitions: pending → verified (Support), verified → executed (Admin)
     * This replaces the legacy updateStatus flow with enforced role-based state machine.
     */
    public function updateStatusSecure(Request $request, $id)
    {
        $this->authorize('admin_support_manage');

        $validated = $request->validate([
            'status' => 'required|in:verified,executed,rejected',
            'support_remarks' => 'nullable|string',
            'admin_remarks' => 'nullable|string',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
            'temporary_access_percentage' => 'nullable|integer|min:1|max:100',
            'verified_amount' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string',
            'credit_to_wallet' => 'nullable|boolean',
            'correct_course_id' => 'nullable|exists:webinars,id',
            'service_type' => 'nullable|string',
            'service_id' => 'nullable|integer',
            'end_date' => 'nullable|date|after:today',
        ]);

        try {
            $supportRequest = NewSupportForAsttrolok::findOrFail($id);
            $user = Auth::user();

            $service = app(\App\Services\SupportRequestService::class);
            $service->transition($supportRequest, $validated['status'], $validated, $user);

            return back()->with([
                'toast' => [
                    'title' => 'Success',
                    'msg' => "Ticket status updated to '{$validated['status']}' successfully.",
                    'status' => 'success'
                ]
            ]);

        } catch (\InvalidArgumentException $e) {
            return back()->with([
                'toast' => [
                    'title' => 'Validation Error',
                    'msg' => $e->getMessage(),
                    'status' => 'error'
                ]
            ]);
        } catch (\RuntimeException $e) {
            return back()->with([
                'toast' => [
                    'title' => 'Error',
                    'msg' => $e->getMessage(),
                    'status' => 'error'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Secure update failed', [
                'support_request_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with([
                'toast' => [
                    'title' => 'Error',
                    'msg' => 'Failed to update status. Please try again.',
                    'status' => 'error'
                ]
            ]);
        }
    }

    /**
     * Create InstallmentRestructureRequest from support ticket
     */
    private function createInstallmentRestructureRequestFromSupport($supportRequest)
    {
        try {
            // Check if restructure request already exists
            $existingRequest = InstallmentRestructureRequest::where('support_ticket_id', $supportRequest->id)
                ->first();
            
            if ($existingRequest) {
                Log::info('Restructure request already exists', [
                    'support_ticket_id' => $supportRequest->id,
                    'restructure_request_id' => $existingRequest->id
                ]);
                return $existingRequest;
            }
            
            // Get user's installment order
            $installmentOrder = InstallmentOrder::where('user_id', $supportRequest->user_id)
                ->where('webinar_id', $supportRequest->webinar_id)
                ->whereIn('status', ['open', 'paying'])
                ->first();
            
            if (!$installmentOrder) {
                Log::error('No active installment order found for user', [
                    'user_id' => $supportRequest->user_id,
                    'webinar_id' => $supportRequest->webinar_id
                ]);
                return null;
            }
            
            // Get the next unpaid step
            $steps = InstallmentStep::where('installment_id', $installmentOrder->installment_id)
                ->orderBy('id')
                ->get();
            
            $targetStep = null;
            foreach ($steps as $step) {
                $payment = InstallmentOrderPayment::where('installment_order_id', $installmentOrder->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();
                
                if (!$payment) {
                    $targetStep = $step;
                    break;
                }
            }
            
            if (!$targetStep) {
                Log::error('No unpaid step found for installment', [
                    'installment_order_id' => $installmentOrder->id
                ]);
                return null;
            }
            
            // Calculate step amount
            $itemPrice = $installmentOrder->getItemPrice();
            if ($targetStep->amount_type == 'percent') {
                $stepAmount = ($itemPrice * $targetStep->amount) / 100;
            } else {
                $stepAmount = $targetStep->amount;
            }
            
            // Calculate deadline
            $originalDeadline = strtotime($installmentOrder->created_at) + ($targetStep->deadline * 86400);
            
            // Create the restructure request
            $restructureRequest = InstallmentRestructureRequest::create([
                'installment_order_id' => $installmentOrder->id,
                'installment_step_id' => $targetStep->id,
                'user_id' => $supportRequest->user_id,
                'webinar_id' => $supportRequest->webinar_id,
                'original_amount' => $stepAmount,
                'original_deadline' => $originalDeadline,
                'number_of_sub_steps' => 2,
                'status' => InstallmentRestructureRequest::STATUS_PENDING,
                'support_ticket_id' => $supportRequest->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Installment restructure request created from support ticket', [
                'support_ticket_id' => $supportRequest->id,
                'restructure_request_id' => $restructureRequest->id,
                'installment_order_id' => $installmentOrder->id,
                'installment_step_id' => $targetStep->id,
                'step_amount' => $stepAmount
            ]);
            
            return $restructureRequest;
            
        } catch (\Exception $e) {
            Log::error('Error creating restructure request from support: ' . $e->getMessage(), [
                'support_ticket_id' => $supportRequest->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return null;
        }
    }

     /**
      * Admin approves installment restructure request
     * This creates 2 sub-steps (50-50 split) automatically
     */
    /**
     * NOTE: Called from within updateStatus() which already has an open
     * DB::beginTransaction(). Do NOT open a nested transaction here.
     */
    public function adminApproveRestructure($supportRequest)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => null
        ];

        try {
            Log::info('=== STARTING INSTALLMENT RESTRUCTURE APPROVAL (UPE) ===', [
                'support_request_id' => $supportRequest->id,
                'user_id' => $supportRequest->user_id,
                'webinar_id' => $supportRequest->webinar_id,
            ]);

            // 1. Extract UPE data from the support ticket's execution_result
            $executionResult = $supportRequest->execution_result;
            if (is_string($executionResult)) {
                $executionResult = json_decode($executionResult, true);
            }

            $planId = $executionResult['plan_id'] ?? null;
            $scheduleId = $executionResult['schedule_id'] ?? null;

            // Allow form override (admin might have adjusted via the split form)
            $request = request();
            if ($request->filled('restructure_plan_id')) {
                $planId = (int) $request->input('restructure_plan_id');
            }
            if ($request->filled('restructure_schedule_id')) {
                $scheduleId = (int) $request->input('restructure_schedule_id');
            }

            if (!$planId || !$scheduleId) {
                throw new \RuntimeException('Missing UPE plan_id or schedule_id. Cannot restructure without UPE data.');
            }

            // 2. Load UPE plan and target schedule
            $plan = \App\Models\PaymentEngine\UpeInstallmentPlan::findOrFail($planId);
            $schedule = \App\Models\PaymentEngine\UpeInstallmentSchedule::findOrFail($scheduleId);

            if (!$plan->isActive()) {
                throw new \RuntimeException("Plan #{$plan->id} is not active.");
            }
            if ($schedule->isPaid()) {
                throw new \RuntimeException("Schedule #{$schedule->id} is already paid.");
            }

            // 3. Parse admin-defined sub-schedules from form
            $subSchedulesJson = $request->input('restructure_sub_schedules');
            $subSchedules = $subSchedulesJson ? json_decode($subSchedulesJson, true) : null;

            // Debug logging
            Log::info('Restructure form data received', [
                'restructure_sub_schedules_raw' => $subSchedulesJson,
                'restructure_sub_schedules_decoded' => $subSchedules,
                'restructure_schedule_id' => $request->input('restructure_schedule_id'),
                'restructure_plan_id' => $request->input('restructure_plan_id'),
                'all_request_data' => $request->all()
            ]);

            if (!$subSchedules || !is_array($subSchedules) || count($subSchedules) < 2) {
                // Fallback: equal 2-way split with 30-day interval
                $remaining = $schedule->remainingAmount();
                $half = round($remaining / 2, 2);
                $subSchedules = [
                    ['amount' => $half, 'due_date' => now()->format('Y-m-d')],
                    ['amount' => round($remaining - $half, 2), 'due_date' => now()->addDays(30)->format('Y-m-d')],
                ];
                Log::warning('Using default 2-way equal split (no admin input received)', [
                    'reason' => !$subSchedules ? 'No subSchedules data' : (count($subSchedules) < 2 ? 'Less than 2 parts' : 'Invalid array'),
                    'received_json' => $subSchedulesJson
                ]);
            }

            Log::info('Restructure sub-schedules', [
                'plan_id' => $plan->id,
                'schedule_id' => $schedule->id,
                'num_sub_schedules' => count($subSchedules),
                'sub_schedules' => $subSchedules,
            ]);

            // 4. Call InstallmentEngine::splitSchedule
            $engine = app(\App\Services\PaymentEngine\InstallmentEngine::class);
            $createdSchedules = $engine->splitSchedule($plan, $schedule, $subSchedules, Auth::id());

            // 5. Build execution notes
            $parts = [];
            foreach ($createdSchedules as $i => $cs) {
                $parts[] = sprintf(
                    'Part %d: ₹%s due %s',
                    $i + 1,
                    number_format($cs->amount_due, 2),
                    \Carbon\Carbon::parse($cs->due_date)->format('d M Y')
                );
            }

            $executionNotes = sprintf(
                'EMI #%d (₹%s) split into %d sub-installments: %s',
                $schedule->sequence,
                number_format($schedule->amount_due, 2),
                count($createdSchedules),
                implode(' | ', $parts)
            );

            // 6. Update support ticket
            $supportRequest->update([
                'executed_at' => now(),
                'execution_notes' => $executionNotes,
            ]);

            // 7. Update linked UpePaymentRequest if exists
            $upeRequestId = $executionResult['upe_payment_request_id'] ?? null;
            $upeRequest = null;
            if ($upeRequestId) {
                $upeRequest = \App\Models\PaymentEngine\UpePaymentRequest::find($upeRequestId);
            }
            // Fallback: find by sale_id + request_type if not linked in execution_result
            if (!$upeRequest) {
                $upeRequest = \App\Models\PaymentEngine\UpePaymentRequest::where('sale_id', $plan->sale_id)
                    ->where('request_type', 'installment_restructure')
                    ->whereNotIn('status', ['executed', 'rejected'])
                    ->latest()
                    ->first();
            }
            if ($upeRequest && !in_array($upeRequest->status, ['executed', 'rejected'])) {
                $upeRequest->update([
                    'status' => 'executed',
                    'executed_at' => now(),
                    'execution_result' => [
                        'new_schedule_ids' => array_map(fn($s) => $s->id, $createdSchedules),
                        'original_schedule_id' => $schedule->id,
                        'split_count' => count($createdSchedules),
                    ],
                ]);
            }

            Log::info('=== INSTALLMENT RESTRUCTURE COMPLETED (UPE) ===', [
                'support_request_id' => $supportRequest->id,
                'plan_id' => $plan->id,
                'original_schedule_id' => $schedule->id,
                'new_schedule_count' => count($createdSchedules),
                'new_schedule_ids' => array_map(fn($s) => $s->id, $createdSchedules),
            ]);

            $result = [
                'success' => true,
                'message' => $executionNotes,
                'data' => [
                    'plan_id' => $plan->id,
                    'original_schedule_id' => $schedule->id,
                    'new_schedules' => array_map(fn($s) => [
                        'id' => $s->id,
                        'amount' => (float) $s->amount_due,
                        'due_date' => $s->due_date->format('Y-m-d'),
                    ], $createdSchedules),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error in installment restructure approval (UPE)', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $result = [
                'success' => false,
                'message' => 'Installment restructure failed: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        return $result;
    }

    /**
     * Process a ticket: Admin/Support selects scenario + fills scenario fields.
     * This saves the scenario data to the ticket, then delegates to updateStatus('completed')
     * to trigger the existing business logic execution.
     */
    public function processTicket(Request $request, $id)
    {
        $this->authorize('admin_support_manage');

        $supportRequest = NewSupportForAsttrolok::findOrFail($id);
        $user = Auth::user();

        // Allow both admin and Support Role to process tickets
        if (!$this->isAdminRole($user) && !$this->isSupportRole($user)) {
            return back()->with(['toast' => [
                'title' => 'Not Allowed',
                'msg' => 'You do not have permission to process this ticket.',
                'status' => 'error'
            ]]);
        }

        // Prevent re-processing
        if (in_array($supportRequest->status, ['completed', 'executed', 'closed'])) {
            return back()->with(['toast' => [
                'title' => 'Already Processed',
                'msg' => 'This ticket has already been processed.',
                'status' => 'error'
            ]]);
        }

        // Idempotency guard
        if ($supportRequest->executed_at !== null) {
            return back()->with(['toast' => [
                'title' => 'Already Executed',
                'msg' => 'This ticket has already been executed. Cannot re-process.',
                'status' => 'error'
            ]]);
        }

        // Validate scenario is provided
        $request->validate([
            'support_scenario' => 'required|string|in:course_extension,temporary_access,mentor_access,relatives_friends_access,free_course_grant,offline_cash_payment,installment_restructure,refund_payment,post_purchase_coupon',
        ]);

        $scenario = $request->support_scenario;

        // Scenario-specific validation
        $scenarioRules = [];
        switch ($scenario) {
            case 'course_extension':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['extension_days'] = 'required|integer|in:7,15,30';
                $scenarioRules['extension_reason'] = 'nullable|string';
                break;
            case 'temporary_access':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['temporary_access_days'] = 'required|integer|in:7,15';
                $scenarioRules['temporary_access_percentage'] = 'required|integer|min:1|max:100';
                break;
            case 'mentor_access':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['mentor_change_reason'] = 'nullable|string';
                break;
            case 'relatives_friends_access':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['relative_description'] = 'nullable|string';
                break;
            case 'free_course_grant':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['free_course_reason'] = 'nullable|string';
                break;
            case 'offline_cash_payment':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['cash_amount'] = 'required|numeric|min:0';
                $scenarioRules['payment_date'] = 'nullable|string';
                $scenarioRules['payment_location'] = 'nullable|string';
                $scenarioRules['payment_receipt_number'] = 'nullable|string|max:100';
                break;
            case 'installment_restructure':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['restructure_reason'] = 'nullable|string';
                break;
            case 'refund_payment':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['refund_reason'] = 'nullable|string';
                $scenarioRules['credit_to_wallet'] = 'nullable|boolean';
                break;
            case 'post_purchase_coupon':
                $scenarioRules['webinar_id'] = 'required|exists:webinars,id';
                $scenarioRules['coupon_code'] = 'nullable|string';
                $scenarioRules['coupon_apply_reason'] = 'nullable|string';
                break;
        }

        $scenarioRules['admin_remarks'] = 'nullable|string';
        $validated = $request->validate($scenarioRules);

        DB::beginTransaction();

        try {
            // Step 1: Save scenario + scenario fields to the ticket
            $updateData = [
                'support_scenario' => $scenario,
                'support_handler_id' => $user->id,
                'sub_admin_id' => $user->id,
            ];

            // Determine webinar_id
            $updateData['webinar_id'] = $request->webinar_id;

            // Determine flow type from the selected course + student
            $webinarId = $updateData['webinar_id'] ?? null;
            if ($webinarId && $supportRequest->user_id) {
                $webinar = Webinar::find($webinarId);
                if ($webinar) {
                    $updateData['flow_type'] = $this->determineFlowTypeForUser($webinarId, $supportRequest->user_id);
                }
            }

            // Save scenario-specific fields
            $scenarioFields = [
                'extension_days', 'extension_reason', 'temporary_access_days', 'temporary_access_percentage',
                'temporary_access_reason', 'mentor_change_reason', 'relative_description',
                'free_course_reason', 'cash_amount', 'payment_date', 'payment_location',
                'payment_receipt_number', 'restructure_reason', 'refund_reason', 'credit_to_wallet',
                'coupon_code', 'coupon_apply_reason',
                'correction_purchase_type', 'correction_installment_id', 'correction_quick_pay_amount',
            ];

            foreach ($scenarioFields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->$field;
                }
            }

            $supportRequest->update($updateData);

            // Force fresh instance to ensure all relations are reloaded or data is current
            $supportRequest = $supportRequest->fresh();

            // Special handling for installment_restructure: find and link UPE plan data
            if ($scenario === 'installment_restructure') {
                $this->linkUpePlanForRestructure($supportRequest);
                // Refresh again to get the updated execution_result
                $supportRequest = $supportRequest->fresh();
            }

            // Step 2: Update status based on role
            // Support Role: set as approved (needs admin final action)
            // Admin: set as completed (final execution) - EXCEPT for installment_restructure which needs split interface
            if ($this->isSupportRole($user)) {
                $status = 'approved';
                $remarks = $request->admin_remarks ?? 'Processed by Support team';
            } else {
                if ($scenario === 'installment_restructure') {
                    // For installment restructure, set to approved first so admin can see the restructure interface
                    $status = 'approved';
                    $remarks = $request->admin_remarks ?? 'Installment restructure ready for split definition';
                } else {
                    $status = 'completed';
                    $remarks = $request->admin_remarks ?? 'Processed by Admin';
                }
            }

            // Sync remarks to the request so updateStatus picks them up
            $request->merge([
                'status' => $status,
                'admin_remarks' => $remarks,
                'support_remarks' => $remarks,
            ]);

            DB::commit();

            // IMPORTANT: Call updateStatus which executes specific scenario business logic
            // (e.g. creating sales, extending courses etc.)
            return $this->updateStatus($request, $id);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('processTicket failed', [
                'support_request_id' => $id,
                'scenario' => $scenario,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with(['toast' => [
                'title' => 'Error',
                'msg' => 'Failed to process ticket: ' . \Str::limit($e->getMessage(), 200),
                'status' => 'error'
            ]]);
        }
    }

    /**
     * Find and link UPE plan data for installment restructure scenario
     * This populates the execution_result with plan_id and schedule_id needed by adminApproveRestructure
     */
    private function linkUpePlanForRestructure($supportRequest)
    {
        if (!$supportRequest->webinar_id || !$supportRequest->user_id) {
            throw new \RuntimeException('webinar_id and user_id are required for installment restructure');
        }

        try {
            // Find UPE product for the webinar
            $upeProduct = \App\Models\PaymentEngine\UpeProduct::where('external_id', $supportRequest->webinar_id)
                ->whereIn('product_type', ['course_video', 'webinar'])
                ->first();

            if (!$upeProduct) {
                throw new \RuntimeException('No UPE product found for webinar_id: ' . $supportRequest->webinar_id);
            }

            // Find active installment plan for this user and product
            $plan = \App\Models\PaymentEngine\UpeInstallmentPlan::whereHas('sale', function ($q) use ($supportRequest, $upeProduct) {
                    $q->where('user_id', $supportRequest->user_id)->where('product_id', $upeProduct->id);
                })
                ->whereIn('status', ['active', 'completed'])
                ->with(['schedules', 'sale.product'])
                ->first();

            if (!$plan) {
                throw new \RuntimeException('No active installment plan found for user_id: ' . $supportRequest->user_id . ' and product_id: ' . $upeProduct->id);
            }

            // Find unpaid schedules
            $unpaidSchedules = $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue']);
            $targetSchedule = $unpaidSchedules->sortBy('due_date')->first();

            if (!$targetSchedule) {
                throw new \RuntimeException('No unpaid schedules found for restructuring');
            }

            $isUpfront = $targetSchedule ? ($targetSchedule->sequence <= 1) : false;

            // Populate execution_result with UPE plan data
            $executionResult = [
                'plan_id' => $plan->id,
                'schedule_id' => $targetSchedule->id,
                'schedule_sequence' => $targetSchedule->sequence,
                'schedule_amount' => (float) $targetSchedule->amount_due,
                'schedule_remaining' => $targetSchedule->remainingAmount(),
                'is_upfront' => $isUpfront,
                'upe_payment_request_id' => null,
            ];

            $supportRequest->update([
                'execution_result' => $executionResult
            ]);

            Log::info('UPE plan data linked for restructure', [
                'support_request_id' => $supportRequest->id,
                'plan_id' => $plan->id,
                'schedule_id' => $targetSchedule->id,
                'schedule_sequence' => $targetSchedule->sequence,
                'schedule_remaining' => $targetSchedule->remainingAmount(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to link UPE plan for restructure', [
                'support_request_id' => $supportRequest->id,
                'webinar_id' => $supportRequest->webinar_id,
                'user_id' => $supportRequest->user_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Determine flow type for a given user + webinar (used by processTicket).
     */
    private function determineFlowTypeForUser($webinarId, $userId)
    {
        $webinar = Webinar::find($webinarId);
        if (!$webinar) return 'flow_a';

        // Check UPE access
        $productTypes = ['course_video', 'webinar'];
        $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
            ->where('external_id', $webinarId)
            ->first();

        if ($upeProduct) {
            $activeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->first();

            if ($activeSale) {
                if ($activeSale->valid_until && $activeSale->valid_until->isPast()) {
                    return 'flow_b'; // expired
                }
                return 'flow_c'; // active
            }

            // Any non-cancelled sale means they had it before
            $anySale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($anySale) return 'flow_b';
        }

        return 'flow_a'; // never purchased
    }

    public function quickSupportForm()
    {
        $this->authorize('admin_support_manage');

        // Fetch all active webinars with proper titles
        $webinars = Webinar::where('status', 'active')
            ->select('id', 'creator_id')
            ->with(['creator' => function($query) {
                $query->select('id', 'full_name');
            }])
            ->orderBy('id', 'asc')
            ->get();

        // Map webinars with proper titles from translatable attributes
        $webinarsFormatted = $webinars->map(function($webinar) {
            return [
                'id' => $webinar->id,
                'title' => $webinar->title ?? 'Course #' . $webinar->id,
                'creator_name' => $webinar->creator->full_name ?? 'Unknown',
                'display_name' => ($webinar->title ?? 'Course #' . $webinar->id) . ' - ' . ($webinar->creator->full_name ?? 'Unknown'),
            ];
        });

        $data = [
            'pageTitle' => 'Quick Support Form - Free Access',
            'sourceWebinars' => $webinarsFormatted,
            'targetWebinars' => $webinarsFormatted
        ];

        return view('admin.supports.quick_support_form', $data);
    }

    public function grantQuickAccess(Request $request)
    {
        $this->authorize('admin_support_manage');

        $validated = $request->validate([
            'support_subject' => 'required|string|max:255',
            'support_scenario' => 'required|string|in:free_course_grant',
            'source_course_id' => 'required|exists:webinars,id',
            'target_course_id' => 'required|exists:webinars,id|different:source_course_id',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $sourceWebinar = Webinar::find($validated['source_course_id']);
            $targetWebinar = Webinar::find($validated['target_course_id']);

            // Use proper webinar titles instead of converting slugs
            $sourceTitle = $sourceWebinar->title ?? ('Course #' . $sourceWebinar->id);
            $targetTitle = $targetWebinar->title ?? ('Course #' . $targetWebinar->id);

            // Get all users who have access to source course
            $sourceUsers = Sale::where('webinar_id', $validated['source_course_id'])
                ->where('access_to_purchased_item', 1)
                ->pluck('buyer_id')
                ->unique();

            // Verify webinars exist
            if (!$sourceWebinar || !$targetWebinar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid course selection. Please ensure both courses exist and are active.'
                ], 400);
            }

            // Create support request for tracking
            $supportRequest = new NewSupportForAsttrolok();
            // Set the current logged-in user as requester
            $supportRequest->user_id = Auth::id(); // Current user who is filling the form
            $supportRequest->webinar_id = $validated['target_course_id'];
            $supportRequest->support_scenario = $validated['support_scenario'];
            $supportRequest->title = $validated['support_subject'];
            $supportRequest->description = "Quick Support: Grant free access to all users of '{$sourceTitle}' for '{$targetTitle}'";
            $supportRequest->flow_type = 'flow_a';
            $supportRequest->purchase_status = 'never_purchased';
            $supportRequest->status = 'pending';
            $supportRequest->ticket_number = 'AST-' . time() . rand(1000, 9999);
            $supportRequest->source_course_id = $validated['source_course_id'];
            $supportRequest->target_course_id = $validated['target_course_id'];
            $supportRequest->total_users_count = $sourceUsers->count();
            $supportRequest->support_remarks = $validated['remarks'] ?? null;
            $supportRequest->save();

            // Log the request creation
            \Log::info('Quick Support Form - Request Created', [
                'admin_id' => Auth::id(),
                'support_request_id' => $supportRequest->id,
                'ticket_number' => $supportRequest->ticket_number,
                'support_subject' => $validated['support_subject'],
                'source_course' => $sourceTitle,
                'target_course' => $targetTitle,
                'total_users' => $sourceUsers->count(),
                'remarks' => $validated['remarks'] ?? null
            ]);

            $message = "Support request created successfully! Request ID: {$supportRequest->ticket_number}. Please complete the request to grant free access to {$sourceUsers->count()} users.";

            return redirect()->route('admin.support.quickSupportForm')
                ->with('success', $message)
                ->with('ticket_number', $supportRequest->ticket_number)
                ->with('source_course', $sourceTitle)
                ->with('target_course', $targetTitle)
                ->with('total_users', $sourceUsers->count());

        } catch (\Exception $e) {
            \Log::error('Quick Support Form - Error creating request', [
                'error' => $e->getMessage(),
                'request_data' => $validated
            ]);

            return redirect()->route('admin.support.quickSupportForm')
                ->with('error', 'Error occurred while creating request: ' . $e->getMessage());
        }
    }
    /**
     * Send notification to user about restructure approval
     */
    private function sendRestructureApprovalNotification($supportRequest, $subStep1, $subStep2, $installmentStep)
    {
        try {
            $user = $supportRequest->user;
            $webinar = $supportRequest->webinar;
            
            if ($user && $webinar) {
                $notificationData = [
                    'title' => 'Installment Restructure Approved',
                    'message' => sprintf(
                        'Your request to split %s has been approved. Payment is now split into 2 parts: Part 1 (₹%s due %s) and Part 2 (₹%s due %s)',
                        $installmentStep->title,
                        number_format($subStep1->price, 2),
                        date('d M Y', $subStep1->due_date),
                        number_format($subStep2->price, 2),
                        date('d M Y', $subStep2->due_date)
                    ),
                    'user_id' => $user->id,
                    'sender_id' => Auth::id(),
                    'type' => 'installment_restructure_approved',
                    'webinar_id' => $webinar->id,
                ];
                
                \App\Models\Notification::create($notificationData);
                
                Log::info('Restructure approval notification sent to user', [
                    'user_id' => $user->id,
                    'notification_type' => 'installment_restructure_approved'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending restructure approval notification: ' . $e->getMessage());
        }
    }

    /**
     * Get user's pending installment step for restructure
     */
    public function getUserPendingStep(Request $request)
    {
        $this->authorize('admin_support_manage');

        try {
            $userId = $request->user_id;
            $webinarId = $request->webinar_id;

            Log::info('Getting user pending step', [
                'user_id' => $userId,
                'webinar_id' => $webinarId,
            ]);

            // Find user's installment order
            $installmentOrder = \App\Models\InstallmentOrder::where('user_id', $userId)
                ->where('webinar_id', $webinarId)
                ->whereIn('status', ['open', 'paying'])
                ->with(['installment.steps'])
                ->first();

            if (!$installmentOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active installment found'
                ]);
            }

            // Get payments for this order
            $payments = \App\Models\InstallmentOrderPayment::where('installment_order_id', $installmentOrder->id)
                ->where('status', 'paid')
                ->get();

            $paidStepIds = $payments->pluck('step_id')->toArray();

            // Find the first unpaid step
            $pendingStep = null;
            $previousStepPaid = true;

            foreach ($installmentOrder->installment->steps as $step) {
                $isStepPaid = in_array($step->id, $paidStepIds);
                
                if (!$isStepPaid && $previousStepPaid) {
                    // This is the next unpaid step after paid steps
                    $pendingStep = $step;
                    break;
                }
                
                $previousStepPaid = $isStepPaid;
            }

            if (!$pendingStep) {
                return response()->json([
                    'success' => false,
                    'message' => 'No eligible step found for restructure. Either all steps are paid or previous step is unpaid.'
                ]);
            }

            // Calculate step amount
            $webinar = \App\Models\Webinar::find($webinarId);
            $webinarPrice = $webinar->price ?? 0;

            if ($pendingStep->amount_type == 'percent') {
                $stepAmount = ($webinarPrice * $pendingStep->amount) / 100;
            } else {
                $stepAmount = $pendingStep->amount;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'step_id' => $pendingStep->id,
                    'step_title' => $pendingStep->title,
                    'step_amount' => $stepAmount,
                    'amount_type' => $pendingStep->amount_type,
                    'deadline' => $pendingStep->deadline,
                    'order' => $pendingStep->order,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user pending step: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject the linked UpePaymentRequest when a support ticket is rejected.
     * This allows the student to create a new ticket for the same scenario.
     */
    private function rejectLinkedUpeRequest($supportRequest, $reason = 'Rejected by admin')
    {
        try {
            // Try to find UPE request via execution_result linkage
            $executionResult = $supportRequest->execution_result ?? [];
            $upeRequestId = $executionResult['upe_payment_request_id'] ?? null;

            $upeRequest = null;
            if ($upeRequestId) {
                $upeRequest = \App\Models\PaymentEngine\UpePaymentRequest::find($upeRequestId);
            }

            // Fallback: find by user_id + sale matching + request_type
            if (!$upeRequest && $supportRequest->support_scenario === 'installment_restructure') {
                $upeRequest = \App\Models\PaymentEngine\UpePaymentRequest::where('user_id', $supportRequest->user_id)
                    ->where('request_type', 'installment_restructure')
                    ->whereNotIn('status', ['rejected', 'executed'])
                    ->latest()
                    ->first();
            }

            if ($upeRequest && !in_array($upeRequest->status, ['rejected', 'executed'])) {
                $upeRequest->update([
                    'status' => 'rejected',
                    'rejected_reason' => $reason,
                ]);
                Log::info('Linked UpePaymentRequest rejected', [
                    'upe_request_id' => $upeRequest->id,
                    'support_request_id' => $supportRequest->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to reject linked UpePaymentRequest', [
                'support_request_id' => $supportRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle installment restructure request from support creation
     */
    public function handleInstallmentRestructure($support)
    {
        try {
            Log::info('handleInstallmentRestructure called', [
                'support_id' => $support->id,
                'user_id' => $support->user_id,
                'webinar_id' => $support->webinar_id
            ]);

            $userId = $support->user_id;
            $webinarId = $support->webinar_id;

            // Find eligible unpaid step where previous step is paid
            $pendingStep = $this->getEligiblePendingStep($userId, $webinarId);

            if (!$pendingStep) {
                Log::warning('No eligible step found for restructure', [
                    'user_id' => $userId,
                    'webinar_id' => $webinarId
                ]);
                return false;
            }

            // Calculate step amount and deadline
            $webinar = \App\Models\Webinar::find($webinarId);
            $webinarPrice = $webinar->price ?? 0;

            if ($pendingStep->amount_type == 'percent') {
                $totalStepAmount = ($webinarPrice * $pendingStep->amount) / 100;
            } else {
                $totalStepAmount = $pendingStep->amount;
            }

            // Create installment restructure request
            $restructureRequest = \App\Models\InstallmentRestructureRequest::create([
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'installment_step_id' => $pendingStep->id,
                'support_ticket_id' => $support->id,
                'original_amount' => $totalStepAmount,
                'original_deadline' => $pendingStep->deadline,
                'status' => \App\Models\InstallmentRestructureRequest::STATUS_PENDING,
                'request_notes' => $support->description ?? 'User requested installment restructure',
            ]);

            Log::info('Installment restructure request created', [
                'restructure_request_id' => $restructureRequest->id,
                'user_id' => $userId,
                'step_id' => $pendingStep->id,
                'amount' => $totalStepAmount
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error handling installment restructure: ' . $e->getMessage(), [
                'support_id' => $support->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return false;
        }
    }

    /**
         * Validate coupon code via AJAX
        */
        public function validateCoupon(Request $request)
        {
            $this->authorize('admin_support_manage');

            $couponCode = strtoupper(trim($request->input('coupon_code')));
            $courseId = $request->input('webinar_id'); // Get selected course
            
            if (empty($couponCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a coupon code'
                ]);
            }
            
            // Find discount by code
            $discount = \App\Models\Discount::where('code', $couponCode)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', time());
                })
                ->first();
            
            if (!$discount) {
                // V-19 FIX: Do NOT expose available coupon codes
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon code.'
                ]);
            }
            
            // Check if discount is still valid (has remaining count)
            $remainingCount = $discount->discountRemain();
            if ($remainingCount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon has been fully used. No more uses remaining.'
                ]);
            }
            
            // Check if coupon is valid for selected course
            if ($courseId) {
                $discountWebinarsIds = $discount->discountCourses()->pluck('course_id')->toArray();
                
                // If discount has specific courses, check if selected course is included
                if (!empty($discountWebinarsIds) && !in_array($courseId, $discountWebinarsIds)) {
                    // Get course names for which this coupon is valid
                    $validCourses = \App\Models\Webinar::whereIn('id', $discountWebinarsIds)
                        ->with(['translations' => function($query) {
                            $query->where('locale', app()->getLocale());
                        }])
                        ->get()
                        ->map(function($course) {
                            return $course->title ?? 'Course #' . $course->id;
                        })
                        ->take(3)
                        ->toArray();
                    
                    $courseList = implode(', ', $validCourses);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'This coupon is not valid for this course. Valid for: ' . $courseList
                    ]);
                }
            }
            
            // Validate discount type and value
            $discountType = $discount->percent > 0 ? 'percentage' : 'fixed_amount';
            $discountValue = $discount->percent > 0 ? $discount->percent : $discount->amount;
            
            if ($discountType === 'percentage') {
                $message = sprintf(
                    'Valid coupon! You will get %.2f%% discount on your course.',
                    $discountValue
                );
            } else {
                $message = sprintf(
                    'Valid coupon! You will get ₹%s discount on your course.',
                    number_format($discountValue, 2)
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'coupon_title' => $discount->title,
                'remaining_uses' => $remainingCount
            ]);
        }

        /**
         * AJAX: Validate coupon + recalculate price breakdown for offline cash payment.
         * Returns original price, discount, final payable, cash comparison, and installment breakdown if applicable.
         */
        public function validateOfflineCoupon(Request $request)
        {
            $this->authorize('admin_support_manage');

            $couponCode = strtoupper(trim($request->input('coupon_code', '')));
            $webinarId = (int) $request->input('webinar_id');
            $cashAmount = (float) $request->input('cash_amount', 0);
            $installmentId = $request->input('installment_id') ? (int) $request->input('installment_id') : null;

            if (empty($couponCode)) {
                return response()->json(['success' => false, 'message' => 'Please enter a coupon code.']);
            }

            $webinar = \App\Models\Webinar::find($webinarId);
            if (!$webinar) {
                return response()->json(['success' => false, 'message' => 'Course not found.']);
            }

            $originalPrice = (float) $webinar->getPrice();

            // Validate coupon (direct checks — no cart dependency)
            $discount = \App\Models\Discount::where('code', $couponCode)->first();
            if (!$discount) {
                return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
            }

            if (!empty($discount->expired_at) && $discount->expired_at < time()) {
                return response()->json(['success' => false, 'message' => 'This coupon has expired.']);
            }

            if ($discount->discountRemain() <= 0) {
                return response()->json(['success' => false, 'message' => 'This coupon has been fully used.']);
            }

            // Check course-specific coupon restrictions
            if ($discount->source === 'course') {
                $discountWebinarIds = $discount->discountCourses()->pluck('course_id')->toArray();
                if (!empty($discountWebinarIds) && !in_array($webinarId, $discountWebinarIds)) {
                    return response()->json(['success' => false, 'message' => 'This coupon is not valid for this course.']);
                }
            }

            // Calculate discount amount
            $discountAmount = 0;
            if ($discount->discount_type === \App\Models\Discount::$discountTypeFixedAmount) {
                $discountAmount = min((float) $discount->amount, $originalPrice);
            } else {
                $discountAmount = round($originalPrice * (float) $discount->percent / 100, 2);
            }
            $discountAmount = round($discountAmount, 0, PHP_ROUND_HALF_UP);

            $finalPayable = max(0, $originalPrice - $discountAmount);

            $result = [
                'success' => true,
                'message' => "Coupon applied! Discount: ₹" . number_format($discountAmount, 0),
                'original_price' => $originalPrice,
                'discount_amount' => $discountAmount,
                'final_payable' => $finalPayable,
                'cash_amount' => $cashAmount,
                'remaining' => max(0, $finalPayable - $cashAmount),
                'is_sufficient' => $cashAmount >= ($finalPayable - 1),
            ];

            // If installment plan selected, show schedule breakdown with discounted price
            if ($installmentId) {
                $installment = \App\Models\Installment::find($installmentId);
                if ($installment && $installment->enable) {
                    $upfront = round($installment->getUpfront($finalPayable), 0, PHP_ROUND_HALF_UP);
                    $steps = $installment->steps()->orderBy('order')->get();
                    $schedules = [['label' => 'Upfront', 'amount' => $upfront]];
                    foreach ($steps as $step) {
                        $schedules[] = [
                            'label' => 'EMI ' . $step->order,
                            'amount' => round($step->getPrice($finalPayable), 0, PHP_ROUND_HALF_UP),
                        ];
                    }
                    $result['installment_schedules'] = $schedules;
                    $result['installment_total'] = array_sum(array_column($schedules, 'amount'));
                    $result['upfront_amount'] = $upfront;
                    $result['is_sufficient'] = $cashAmount >= ($upfront - 1);
                    $result['message'] .= " | Upfront: ₹" . number_format($upfront, 0);
                }
            }

            return response()->json($result);
        }

    /**
     * AJAX: Return price breakdown, wallet projection, and installment plans
     * for the wrong course correction scenario.
     *
     * POST params: support_request_id, correct_course_id, coupon_code (optional), purchase_type (optional)
     */
    public function getWrongCourseInfo(Request $request)
    {
        $this->authorize('admin_support_manage');

        $request->validate([
            'support_request_id' => 'required|integer|exists:new_support_for_asttrolok,id',
            'correct_course_id'  => 'required|integer|exists:webinars,id',
        ]);

        $supportRequest  = \App\Models\NewSupportForAsttrolok::findOrFail($request->support_request_id);
        $userId          = $supportRequest->user_id;
        $wrongCourseId   = $request->wrong_course_id ?: $supportRequest->wrong_course_id ?: $supportRequest->webinar_id;
        $correctCourseId = (int) $request->correct_course_id;
        $couponCode      = strtoupper(trim($request->input('coupon_code', '')));

        $correctWebinar  = \App\Models\Webinar::findOrFail($correctCourseId);
        $coursePrice     = (float) $correctWebinar->getPrice();

        // ── Compute discount ──
        $discountAmount = 0;
        $discountMsg    = '';
        $discountId     = null;
        if ($couponCode) {
            $discount = \App\Models\Discount::where('code', $couponCode)->where('status', 'active')->first();
            if (!$discount) {
                return response()->json(['success' => false, 'message' => 'Invalid or inactive coupon code.']);
            }
            if (!empty($discount->expire_at) && now()->timestamp > $discount->expire_at) {
                return response()->json(['success' => false, 'message' => 'This coupon has expired.']);
            }
            if ($discount->max_use_id != 0 && $discount->discountRemain() <= 0) {
                return response()->json(['success' => false, 'message' => 'This coupon has been fully used.']);
            }
            if ($discount->source === 'course') {
                $applies = \App\Models\DiscountCourse::where('discount_id', $discount->id)
                    ->where('course_id', $correctCourseId)->exists();
                if (!$applies) {
                    return response()->json(['success' => false, 'message' => 'This coupon is not valid for this course.']);
                }
            }
            $discountId = $discount->id;
            if ($discount->discount_type === 'fixed_amount') {
                $discountAmount = min((float) $discount->amount, $coursePrice);
            } else {
                $discountAmount = round($coursePrice * (float) ($discount->percent ?? 0) / 100, 2);
                if (!empty($discount->max_amount) && $discountAmount > $discount->max_amount) {
                    $discountAmount = (float) $discount->max_amount;
                }
            }
            $discountAmount = round($discountAmount, 0, PHP_ROUND_HALF_UP);
            $discountMsg    = "Coupon applied! Discount: ₹" . number_format($discountAmount, 0);
        }

        $finalPrice = max(0, $coursePrice - $discountAmount);

        // ── Compute actual refund from wrong course ──
        // PRIMARY: legacy Sale SUM (one row per actual payment, no sync duplicates)
        // FALLBACK: UPE ledger (for purchases with no legacy Sale rows)
        $actualRefund = 0;
        $refundSource = 'none';
        if ($userId && $wrongCourseId) {
            // ── Primary: sum all unrefunded Sale rows for this user + wrong course ──
            try {
                $legacyPaid = (float) \App\Models\Sale::where('buyer_id', $userId)
                    ->where('webinar_id', $wrongCourseId)
                    ->whereNull('refund_at')
                    ->whereIn('type', ['webinar', 'installment_payment', 'course_video', 'bundle'])
                    ->sum('amount');

                if ($legacyPaid > 0) {
                    $actualRefund = $legacyPaid;
                    $refundSource = 'legacy';
                }
            } catch (\Exception $e) {
                \Log::warning('getWrongCourseInfo: Legacy Sale refund lookup failed', ['error' => $e->getMessage()]);
            }

            // ── Fallback: UPE ledger (only when no legacy Sale rows exist) ──
            if ($actualRefund <= 0) {
                try {
                    $bridge      = app(\App\Services\SupportUpeBridge::class);
                    $wrongProduct = $bridge->getOrCreateProduct((int) $wrongCourseId);
                    if ($wrongProduct) {
                        $wrongSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
                            ->where('product_id', $wrongProduct->id)
                            ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
                            ->orderByRaw("FIELD(status,'pending_payment','partially_refunded','active')")
                            ->orderByDesc('id')
                            ->first();
                        if ($wrongSale) {
                            $ledger       = app(\App\Services\PaymentEngine\PaymentLedgerService::class);
                            $actualRefund = $ledger->actualAmountPaid($wrongSale->id);
                            if ($actualRefund > 0) {
                                $refundSource = 'upe';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('getWrongCourseInfo: UPE refund lookup failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // ── Current wallet balance + projected after refund ──
        $walletService     = app(\App\Services\PaymentEngine\WalletService::class);
        $currentBalance    = $userId ? $walletService->balance($userId) : 0;
        $projectedBalance  = $currentBalance + $actualRefund;

        $isSufficientFull  = $projectedBalance >= ($finalPrice - 1);

        // ── Installment plans for correct course ──
        $studentUser    = $userId ? \App\User::find($userId) : null;
        $installmentPlans = [];
        $isInstallmentAvailable = false;

        $showInstallments = $correctWebinar
            && !empty($correctWebinar->price) && $correctWebinar->price > 0
            && getInstallmentsSettings('status')
            && (empty($studentUser) || !empty($studentUser->enable_installments));

        if ($showInstallments) {
            $installmentMixin = new \App\Mixins\Installment\InstallmentPlans($studentUser);
            $plans = $installmentMixin->getPlans(
                'courses', $correctWebinar->id, $correctWebinar->type,
                $correctWebinar->category_id, $correctWebinar->teacher_id
            );
            $plans->loadCount('steps');

            if ($plans->isNotEmpty()) {
                $isInstallmentAvailable = true;
                foreach ($plans as $plan) {
                    $upfront = round($plan->getUpfront($finalPrice), 0, PHP_ROUND_HALF_UP);
                    $isSufficientInstallment = $projectedBalance >= ($upfront - 1);
                    $steps = $plan->steps()->orderBy('order')->get();
                    $schedules = [['label' => 'Upfront (EMI 1)', 'amount' => $upfront, 'deadline_days' => 0]];
                    foreach ($steps as $step) {
                        $schedules[] = [
                            'label'         => 'EMI ' . ($step->order + 1),
                            'amount'        => round($step->getPrice($finalPrice), 0, PHP_ROUND_HALF_UP),
                            'deadline_days' => (int) $step->deadline,
                        ];
                    }
                    $installmentPlans[] = [
                        'id'                       => $plan->id,
                        'title'                    => $plan->title ?? ('Plan #' . $plan->id),
                        'upfront'                  => $upfront,
                        'steps_count'              => $plan->steps_count,
                        'total_emis'               => $plan->steps_count + 1,
                        'schedules'                => $schedules,
                        'is_sufficient_installment' => $isSufficientInstallment,
                    ];
                }
            }
        }

        $shortfall = max(0, $finalPrice - $projectedBalance);

        return response()->json([
            'success'                  => true,
            'message'                  => $discountMsg ?: 'Course info loaded.',
            'course_price'             => $coursePrice,
            'discount_amount'          => $discountAmount,
            'final_price'              => $finalPrice,
            'actual_refund'            => $actualRefund,
            'refund_source'            => $refundSource,
            'current_wallet_balance'   => $currentBalance,
            'projected_wallet_balance' => $projectedBalance,
            'shortfall'                => $shortfall,
            'is_sufficient_full'       => $isSufficientFull,
            'is_installment_available' => $isInstallmentAvailable,
            'installment_plans'        => $installmentPlans,
        ]);
    }

    public function ApplyCouponCode($supportRequest)
        {

            // Inputs
            $couponCode     = strtoupper(trim($supportRequest->coupon_code));
            $webinarId      = $supportRequest->webinar_id;
            $installmentId  = $supportRequest->installment_id;
            $originalAmount = $supportRequest->amount;
            $userId         = $supportRequest->user_id;

            //  Empty coupon
            if (!$couponCode) {
                throw new \RuntimeException('Please enter a coupon code.');
            }

            //  Find coupon
            $discount = \App\Models\Discount::where('code', $couponCode)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>', time());
                })
                ->first();

            //  Invalid coupon
            if (!$discount) {
                throw new \RuntimeException('This coupon code is not valid or expired.');
            }

            //  Coupon usage limit
            if ($discount->discountRemain() <= 0) {
                throw new \RuntimeException('This coupon has reached its usage limit.');
            }

            //  Course validation
            $allowedCourses = $discount->discountCourses()->pluck('course_id')->toArray();
            if (!empty($allowedCourses) && !in_array($webinarId, $allowedCourses)) {
                throw new \RuntimeException('This coupon is not applicable for this course.');
            }
            $validCourses = \App\Models\Webinar::where('id', $webinarId)
                        ->with(['translations' => function($query) {
                            $query->where('locale', app()->getLocale());
                        }])
                        ->first();

            if (!$validCourses) {
                throw new \RuntimeException('Course not found for this support request.');
            }

               if ($validCourses) {

                    // ── 1. Check UPE installment plan (primary path for new purchases) ──
                    $upeProduct = UpeProduct::where('external_id', $webinarId)
                        ->whereIn('product_type', ['course_video', 'webinar', 'course_live'])
                        ->first();

                    $upeInstallmentPlan = null;
                    $upeSaleForInstallment = null;

                    if ($upeProduct) {
                        $upeSaleForInstallment = UpeSale::where('user_id', $userId)
                            ->where('product_id', $upeProduct->id)
                            ->where('pricing_mode', 'installment')
                            ->whereNotIn('status', ['cancelled', 'refunded', 'expired'])
                            ->orderByDesc('id')
                            ->first();

                        if ($upeSaleForInstallment) {
                            $upeInstallmentPlan = UpeInstallmentPlan::where('sale_id', $upeSaleForInstallment->id)
                                ->where('status', 'active')
                                ->first();
                        }
                    }

                    if ($upeInstallmentPlan) {
                        // ── UPE installment: apply discount by crediting against outstanding schedules ──
                        $originalAmount = $upeInstallmentPlan->total_amount ?? 0;

                        if ($discount->percent > 0) {
                            $discountAmount = ($originalAmount * $discount->percent) / 100;
                        } else {
                            $discountAmount = $discount->amount;
                        }
                        $finalAmount = min($discountAmount, $originalAmount);

                        if ($finalAmount > 0) {
                            $engine = app(InstallmentEngine::class);
                            $engine->recordPayment(
                                $upeInstallmentPlan,
                                $finalAmount,
                                'coupon',
                                null,
                                ['coupon_code' => $couponCode, 'support_id' => $supportRequest->id],
                                Auth::id()
                            );
                        }

                        // Legacy dual-write: WebinarPartPayment for backward compat
                        \App\Models\WebinarPartPayment::create([
                            'user_id'        => $userId,
                            'installment_id' => null,
                            'webinar_id'     => $webinarId,
                            'amount'         => $finalAmount,
                            'created_at'     => now(),
                        ]);

                    } else {
                        // ── 2. Check legacy InstallmentOrder ──
                        $order = InstallmentOrder::where([
                            'user_id' => $userId,
                            'webinar_id' => $webinarId,
                            'status' => 'open',
                        ])->first();

                        if ($order) {
                            // ── Legacy installment: apply discount as part-payment credit ──
                            $originalAmount = $order->item_price ?? 0;

                            if ($discount->percent > 0) {
                                $discountAmount = ($originalAmount * $discount->percent) / 100;
                            } else {
                                $discountAmount = $discount->amount;
                            }
                            $finalAmount = min($discountAmount, $originalAmount);

                            \App\Models\WebinarPartPayment::create([
                                'user_id'        => $userId,
                                'installment_id' => $order->installment_id,
                                'webinar_id'     => $webinarId,
                                'amount'         => $finalAmount,
                                'created_at'     => now(),
                            ]);

                        } else {
                            // ── 3. Regular (non-installment) purchase: credit wallet via Accounting ──
                            $legacySale = \App\Models\Sale::where('buyer_id', $userId)
                                ->where('webinar_id', $webinarId)
                                ->whereNull('refund_at')
                                ->orderByDesc('id')
                                ->first();

                            $originalAmount = $legacySale ? ($legacySale->total_amount ?? 0) : ($supportRequest->amount ?? 0);

                            if ($discount->percent > 0) {
                                $discountAmount = ($originalAmount * $discount->percent) / 100;
                            } else {
                                $discountAmount = $discount->amount;
                            }
                            $finalAmount = min($discountAmount, $originalAmount);

                            if ($finalAmount > 0) {
                                \App\Models\Accounting::create([
                                    'user_id'      => $userId,
                                    'amount'       => $finalAmount,
                                    'type'         => \App\Models\Accounting::$addiction,
                                    'type_account' => \App\Models\Accounting::$asset,
                                    'description'  => "Post-purchase coupon credit: {$couponCode} - Support #{$supportRequest->ticket_number}",
                                    'created_at'   => time(),
                                ]);
                            }
                        }
                    }

                        // UPE: Record coupon discount in UPE ledger
                        try {
                            $bridge = app(SupportUpeBridge::class);
                            $bridge->recordCouponDiscount(
                                $userId, $webinarId, $supportRequest->id, Auth::id(),
                                $finalAmount, $couponCode, $discount->id
                            );
                        } catch (\Exception $e) {
                            \Log::warning('UPE coupon bridge failed', ['error' => $e->getMessage()]);
                        }
                }

            return true;
        }

        public function offlineCashPayment($supportRequest)
        {
            $purchaseService = new AdminCoursePurchaseService();
            
            // Get course and user details
            $courseId = $supportRequest->webinar_id;
            $userId = $supportRequest->user_id;
            $installmentId = $supportRequest->installment_id ?? null;
            $couponCode = $supportRequest->coupon_code ?? null;
            
            // Find discount by coupon code if provided
            $discountId = null;
            if ($couponCode) {
                $discount = \App\Models\Discount::where('code', $couponCode)->first();
                if ($discount && $discount->checkValidDiscount() == 'ok') {
                    $discountId = $discount->id;
                }
            }
            
            // Validate purchase request first
            $purchaseType = $installmentId ? 'installment' : 'direct';
            $validation = $purchaseService->validatePurchaseRequest(
                $courseId, 
                $userId, 
                $purchaseType,
                $installmentId
            );
            
            if (!$validation['valid']) {
                \Log::error('Offline cash payment validation failed', [
                    'support_request_id' => $supportRequest->id,
                    'course_id' => $courseId,
                    'user_id' => $userId,
                    'error' => $validation['message']
                ]);
                
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            
            // Process purchase based on installment availability
            if ($installmentId) {
                // Installment purchase
                $result = $purchaseService->purchaseCourseWithInstallment(
                    $courseId,
                    $userId,
                    $installmentId,
                    $discountId,
                    auth()->id()
                );
            } else {
                // Direct purchase
                $result = $purchaseService->purchaseCourseDirectly(
                    $courseId,
                    $userId,
                    $discountId,
                    auth()->id()
                );
            }
            
            // Log the result
            if ($result['success']) {
                \Log::info('Offline cash payment processed successfully', [
                    'support_request_id' => $supportRequest->id,
                    'course_id' => $courseId,
                    'user_id' => $userId,
                    'purchase_type' => $purchaseType,
                    'order_id' => $result['order_id'] ?? null,
                    'amount' => $result['amount'] ?? 0
                ]);
            } else {
                \Log::error('Offline cash payment failed', [
                    'support_request_id' => $supportRequest->id,
                    'course_id' => $courseId,
                    'user_id' => $userId,
                    'error' => $result['message']
                ]);
            }
            
            return $result;
    }

    /**
     * Process refund payment - matches SupportRequestService functionality
     */
    public function refundPayment($supportRequest)
    {
        $userId = $supportRequest->user_id;
        $courseId = $supportRequest->webinar_id;

        \Log::info('Processing refund (admin)', [
            'user_id' => $userId,
            'webinar_id' => $courseId,
            'support_request_id' => $supportRequest->id,
        ]);

        if (empty($userId) || empty($courseId)) {
            throw new \RuntimeException('Refund failed: missing user_id or webinar_id.');
        }

        // 1. Find active sale
        $sale = Sale::where('buyer_id', $userId)
            ->where('webinar_id', $courseId)
            ->whereNull('refund_at')
            ->where('access_to_purchased_item', 1)
            ->lockForUpdate()
            ->first();

        if (!$sale) {
            throw new \RuntimeException('No active purchase found for this user and course.');
        }

        // 2. Update sale (soft-revoke)
        $sale->update([
            'refund_at' => time(),
            'access_to_purchased_item' => 0,
        ]);

        // 3. Calculate refund amount
        $actualAmountPaid = $sale->total_amount - ($sale->discount ?? 0);
        $refundAmount = $supportRequest->verified_amount ?? $actualAmountPaid;

        // 4. Create accounting entry
        Accounting::create([
            'user_id' => $sale->buyer_id,
            'amount' => -1 * abs($refundAmount),
            'type' => 'deduction',
            'type_account' => Accounting::$asset,
            'description' => "Refund: Support #{$supportRequest->ticket_number} - Course #{$courseId}",
            'created_at' => time(),
        ]);

        // 5. Create refund record
        Refund::create([
            'user_id' => $sale->buyer_id,
            'sale_id' => $sale->id,
            'support_request_id' => $supportRequest->id,
            'refund_amount' => $refundAmount,
            'refund_method' => 'wallet_credit', // Always wallet credit
            'processed_by' => Auth::id(),
            'status' => 'pending', // Set to pending for manual processing
        ]);

        // 6. Credit refund to wallet system ONLY if explicitly requested
        $creditToWallet = request()->input('credit_to_wallet', false);
        
        if ($creditToWallet) {
            try {
                $walletService = app(\App\Services\PaymentEngine\WalletService::class);
                $walletService->credit(
                    $sale->buyer_id,
                    $refundAmount,
                    'refund',
                    "Refund for course #{$courseId} (Support #{$supportRequest->ticket_number})",
                    'support_request',
                    $supportRequest->id
                );

                // Update refund status to processed
                $refundRecord = Refund::where('support_request_id', $supportRequest->id)->first();
                if ($refundRecord) {
                    $refundRecord->update(['status' => 'processed']);
                }

                \Log::info('Refund credited to wallet', [
                    'user_id' => $sale->buyer_id,
                    'amount' => $refundAmount,
                    'support_request_id' => $supportRequest->id,
                ]);

            } catch (\Exception $e) {
                \Log::warning('Failed to credit refund to wallet', [
                    'user_id' => $sale->buyer_id,
                    'amount' => $refundAmount,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if wallet credit fails
            }
        } else {
            \Log::info('Refund created but not credited to wallet (manual processing required)', [
                'user_id' => $sale->buyer_id,
                'amount' => $refundAmount,
                'credit_to_wallet' => $creditToWallet,
                'support_request_id' => $supportRequest->id,
            ]);
        }

        // 7. Handle installment orders - COMPLETE CANCELLATION
        InstallmentOrder::where('user_id', $userId)
            ->where('webinar_id', $courseId)
            ->whereIn('status', ['open', 'paying'])
            ->update(['status' => 'refunded']);

        // 8. COMPLETE UPE SYSTEM UPDATES - Critical for dashboard display
        try {
            $this->completeUpeRefund($userId, $courseId, $supportRequest->id, Auth::id(), $refundAmount);
        } catch (\Exception $upeError) {
            \Log::error('UPE refund update failed', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'error' => $upeError->getMessage(),
            ]);
        }

        // 9. Revoke WebinarAccessControl
        WebinarAccessControl::where('user_id', $userId)
            ->where('webinar_id', $courseId)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // 10. UPE: Record refund in UPE ledger + revoke UPE sale (backup)
        $bridge = app(SupportUpeBridge::class);
        $bridge->recordRefund($userId, $courseId, $supportRequest->id, Auth::id());

        \Log::info('Refund completed (admin) - COMPLETE SYSTEM UPDATE', [
            'user_id' => $userId,
            'webinar_id' => $courseId,
            'refund_amount' => $refundAmount,
            'support_request_id' => $supportRequest->id,
            'credit_to_wallet' => $creditToWallet,
        ]);
    }

    /**
     * Complete UPE system updates for refund - ensures course disappears from dashboard
     */
    private function completeUpeRefund(int $userId, int $webinarId, int $supportRequestId, int $adminId, float $refundAmount): void
    {
        // Get UPE product
        $product = \App\Models\PaymentEngine\UpeProduct::where('external_id', $webinarId)
            ->where('product_type', 'course_video')
            ->first();

        if (!$product) {
            \Log::warning('UPE product not found for refund', ['webinar_id' => $webinarId]);
            return;
        }

        // Find and update UPE sale
        $upeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if ($upeSale) {
            // Update sale status to refunded
            $upeSale->update(['status' => 'refunded']);
            
            // Cancel all installment schedules
            \App\Models\PaymentEngine\UpeInstallmentSchedule::where('sale_id', $upeSale->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->update(['status' => 'cancelled']);

            // Cancel installment plan if exists
            \App\Models\PaymentEngine\UpeInstallmentPlan::where('sale_id', $upeSale->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->update(['status' => 'cancelled']);

            // Invalidate access immediately
            $accessEngine = app(\App\Services\PaymentEngine\AccessEngine::class);
            $accessEngine->invalidate($userId, $product->id);

            \Log::info('UPE refund completed', [
                'sale_id' => $upeSale->id,
                'product_id' => $product->id,
                'user_id' => $userId,
                'refund_amount' => $refundAmount,
            ]);
        } else {
            \Log::warning('No active UPE sale found for refund', [
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);
        }
    }
}