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

    //             if ($supportRequest->support_scenario === 'wrong_course_correction' &&  $validated['status'] === 'completed') {
    //                     $this->handleWrongCourseCorrection($supportRequest);
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

            if ($user->role_name === 'Support Role') {
                $rules['support_remarks'] = 'required|string';
            } elseif ($user->role_name === 'admin') {
                // Admin: support_remarks optional on completion/execution, required on review/approval
                if (in_array($request->status, ['completed', 'executed', 'closed', 'rejected'])) {
                    $rules['support_remarks'] = 'nullable|string';
                } else {
                    $rules['support_remarks'] = 'nullable|string';
                }
            }

            // Only require temporary_access_percentage on approval (not rejection)
            if (
                $request->status === 'approved' &&
                $supportRequest->support_scenario === 'temporary_access' &&
                $user->role_name === 'admin'
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
                $user->role_name === 'Support Role' &&
                $validated['status'] === 'completed'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support users cannot mark tickets as completed.'
                ], 403);
            }

            if ($user->role_name === 'Support Role') {
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

                if ($user->role_name === 'admin') {
                    $updateData['sub_admin_id'] = $user->id;
                } else {
                    $updateData['support_handler_id'] = $user->id;
                }

                if ($supportRequest->support_scenario === 'post_purchase_coupon') {
                    if ($user->role_name === 'Support Role') {
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
                    if ($user->role_name === 'admin') {
                        // Admin can also supply coupon_code directly at approval time
                        $adminCoupon = trim($request->input('coupon_code', ''));
                        if (!empty($adminCoupon)) {
                            $updateData['coupon_code'] = strtoupper($adminCoupon);
                            $supportRequest->coupon_code = strtoupper($adminCoupon);
                        }

                        if (!empty($supportRequest->coupon_code)) {
                            $this->ApplyCouponCode($supportRequest);
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

                if ($user->role_name === 'admin') {
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

                if ($user->role_name !== 'admin') {
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

                /* ---------- WRONG COURSE ---------- */
                if ($supportRequest->support_scenario === 'wrong_course_correction') {
                    $this->handleWrongCourseCorrection($supportRequest);
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
                    $targetCourseId = $supportRequest->target_course_id;
                    
                    if ($sourceCourseId && $targetCourseId) {
                        $sourceWebinar = \App\Models\Webinar::find($sourceCourseId);
                        $targetWebinar = \App\Models\Webinar::find($targetCourseId);
                        
                        // Get all users who have access to source course from BOTH legacy Sale and UPE
                        $sourceUserIds = \App\Models\Sale::where('webinar_id', $sourceCourseId)
                            ->where('access_to_purchased_item', 1)
                            ->pluck('buyer_id')
                            ->unique()
                            ->toArray();

                        $bridge = app(SupportUpeBridge::class);
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
                        
                        foreach ($sourceUserIds as $sourceUserId) {
                            $userObj = \App\User::find($sourceUserId);
                            if (!$userObj) continue;
                            
                            // Check if user already has access to target course (legacy + UPE)
                            $existingSale = \App\Models\Sale::where('buyer_id', $sourceUserId)
                                ->where('webinar_id', $targetCourseId)
                                ->where('access_to_purchased_item', 1)
                                ->first();

                            $existingUpe = false;
                            $targetProduct = $bridge->resolveProductId($targetCourseId);
                            if ($targetProduct) {
                                $existingUpe = \App\Models\PaymentEngine\UpeSale::where('user_id', $sourceUserId)
                                    ->where('product_id', $targetProduct)
                                    ->whereNotIn('status', ['cancelled', 'refunded'])
                                    ->exists();
                            }
                            
                            if ($existingSale || $existingUpe) {
                                $alreadyHasAccess++;
                                continue;
                            }
                            
                            // Grant free access to target course
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

                            // UPE: Create UPE sale so AccessEngine grants access
                            $bridge->grantFreeCourseAccess($sourceUserId, $targetCourseId, $supportRequest->id, Auth::id());
                            
                            $grantedCount++;
                        }
                        
                        // Update request with results
                        $updateData['course_purchased_at'] = now();
                        $updateData['purchase_status'] = 'completed';
                        $updateData['granted_users_count'] = $grantedCount;
                        $updateData['already_had_access_count'] = $alreadyHasAccess;
                    }
                 
                }
               /* ---------- temporary_access ---------- */
                if ($supportRequest->support_scenario === 'temporary_access' && Auth::user()->role_name === 'admin') {

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
                     $this->ApplyCouponCode($supportRequest);
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

                     // UPE: Record refund in UPE ledger
                     if ($supportRequest->webinar_id) {
                         $bridge = app(SupportUpeBridge::class);
                         $bridge->recordRefund(
                             $supportRequest->user_id,
                             $supportRequest->webinar_id,
                             $supportRequest->id,
                             $user->id
                         );
                     }
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
            'refund_method' => 'nullable|string|in:bank_transfer,wallet_credit,original_method',
            'bank_account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'account_holder_name' => 'nullable|string',
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

            if (!$subSchedules || !is_array($subSchedules) || count($subSchedules) < 2) {
                // Fallback: equal 2-way split with 30-day interval
                $remaining = $schedule->remainingAmount();
                $half = round($remaining / 2, 2);
                $subSchedules = [
                    ['amount' => $half, 'due_date' => now()->format('Y-m-d')],
                    ['amount' => round($remaining - $half, 2), 'due_date' => now()->addDays(30)->format('Y-m-d')],
                ];
                Log::info('Using default 2-way equal split (no admin input received)');
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

            return [
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
            
            return [
                'success' => false,
                'message' => 'Error approving restructure: ' . $e->getMessage()
            ];
        }
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
     * Handle wrong course correction - update all tables when approved
    */
    /**
     * V-13 FIX: Rewritten handleWrongCourseCorrection() — PRESERVE HISTORY pattern.
     * NO webinar_id overwrites. Original records are preserved.
     * Creates soft-revoke on wrong course + new grant for correct course.
     */
    private function handleWrongCourseCorrection($supportRequest)
    {
        try {
            $userId = $supportRequest->user_id;
            $wrongCourseId = $supportRequest->wrong_course_id;
            $correctCourseId = $supportRequest->correct_course_id;

            \Log::info('Processing wrong course correction (preserve-history)', [
                'user_id' => $userId,
                'wrong_course_id' => $wrongCourseId,
                'correct_course_id' => $correctCourseId,
                'support_request_id' => $supportRequest->id,
            ]);

            $correctCourse = Webinar::findOrFail($correctCourseId);

            // 1. Soft-revoke Sale for wrong course (NO webinar_id overwrite)
            $oldSale = Sale::where('buyer_id', $userId)
                ->where('webinar_id', $wrongCourseId)
                ->whereNull('refund_at')
                ->where('access_to_purchased_item', 1)
                ->first();

            if ($oldSale) {
                $oldSale->update([
                    'refund_at' => time(),
                    'access_to_purchased_item' => 0,
                ]);
            }

            // 2. Create reversal Accounting entry for wrong course (NO DELETE)
            $originalAmount = $oldSale ? $oldSale->amount : 0;
            Accounting::create([
                'user_id' => $userId,
                'amount' => -1 * abs($originalAmount),
                'type' => 'deduction',
                'type_account' => Accounting::$asset,
                'description' => "Wrong course reversal: Course #{$wrongCourseId} → #{$correctCourseId} - Support #{$supportRequest->ticket_number}",
                'created_at' => time(),
            ]);

            // 3. Check user doesn't already have correct course
            $existingCorrect = Sale::where('buyer_id', $userId)
                ->where('webinar_id', $correctCourseId)
                ->whereNull('refund_at')
                ->where('access_to_purchased_item', 1)
                ->first();

            if (!$existingCorrect) {
                // 4. Create NEW Sale for correct course (preserves original)
                Sale::create([
                    'buyer_id' => $userId,
                    'seller_id' => $correctCourse->creator_id,
                    'webinar_id' => $correctCourseId,
                    'type' => Sale::$webinar,
                    'payment_method' => $oldSale ? $oldSale->payment_method : Sale::$credit,
                    'amount' => $originalAmount,
                    'total_amount' => $oldSale ? $oldSale->total_amount : 0,
                    'access_to_purchased_item' => 1,
                    'manual_added' => true,
                    'support_request_id' => $supportRequest->id,
                    'granted_by_admin_id' => Auth::id(),
                    'created_at' => time(),
                ]);

                // 5. Create Accounting entry for correct course
                Accounting::create([
                    'user_id' => $userId,
                    'amount' => $originalAmount,
                    'type' => Accounting::$addiction,
                    'type_account' => Accounting::$asset,
                    'description' => "Wrong course correction: Course #{$correctCourseId} - Support #{$supportRequest->ticket_number}",
                    'created_at' => time(),
                ]);
            }

            // 6. Handle InstallmentOrder transfer (NO webinar_id overwrite)
            $oldInstallment = InstallmentOrder::where('user_id', $userId)
                ->where('webinar_id', $wrongCourseId)
                ->whereIn('status', ['open', 'paying'])
                ->first();

            if ($oldInstallment) {
                $oldInstallment->update(['status' => 'transferred']);

                $newInstallment = InstallmentOrder::create([
                    'installment_id' => $oldInstallment->installment_id,
                    'user_id' => $userId,
                    'webinar_id' => $correctCourseId,
                    'item_price' => $correctCourse->price,
                    'status' => 'open',
                    'parent_order_id' => $oldInstallment->id,
                    'created_at' => time(),
                ]);

                $oldInstallment->update(['transferred_to_order_id' => $newInstallment->id]);
            }

            // 7. Revoke old WebinarAccessControl (NO webinar_id overwrite)
            try {
                WebinarAccessControl::where('user_id', $userId)
                    ->where('webinar_id', $wrongCourseId)
                    ->where('status', 'active')
                    ->update(['status' => 'revoked']);
            } catch (\Exception $e) {
                // Legacy table may lack 'status' column — delete the row instead
                WebinarAccessControl::where('user_id', $userId)
                    ->where('webinar_id', $wrongCourseId)
                    ->delete();
                Log::warning('webinar_access_control missing status column, deleted row instead', [
                    'user_id' => $userId, 'webinar_id' => $wrongCourseId, 'error' => $e->getMessage()
                ]);
            }

            // UPE: Revoke wrong course + grant correct course in UPE
            $bridge = app(SupportUpeBridge::class);
            $bridge->handleWrongCourseCorrection(
                $userId,
                $wrongCourseId,
                $correctCourseId,
                $supportRequest->id,
                Auth::id()
            );

            \Log::info('Wrong course correction completed (all original records preserved)', [
                'user_id' => $userId,
                'wrong_course_id' => $wrongCourseId,
                'correct_course_id' => $correctCourseId,
                'support_request_id' => $supportRequest->id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in wrong course correction: ' . $e->getMessage());
            throw $e;
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
                return response()->json([
                    'success' => false,
                    'toast' => [
                        'title'  => 'Coupon Required',
                        'msg'    => 'Please enter a coupon code',
                        'status' => 'error'
                    ]
                ], 422);
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
                return response()->json([
                    'success' => false,
                    'toast' => [
                        'title'  => 'Invalid Coupon',
                        'msg'    => 'This coupon code is not valid or expired',
                        'status' => 'error'
                    ]
                ], 404);
            }

            //  Coupon usage limit
            if ($discount->discountRemain() <= 0) {
                return response()->json([
                    'success' => false,
                    'toast' => [
                        'title'  => 'Coupon Used',
                        'msg'    => 'This coupon has reached its usage limit',
                        'status' => 'error'
                    ]
                ], 409);
            }

            //  Course validation
            $allowedCourses = $discount->discountCourses()->pluck('course_id')->toArray();
            if (!empty($allowedCourses) && !in_array($webinarId, $allowedCourses)) {
                return response()->json([
                    'success' => false,
                    'toast' => [
                        'title'  => 'Not Applicable',
                        'msg'    => 'This coupon is not applicable for this course',
                        'status' => 'error'
                    ]
                ], 403);
            }
            $validCourses = \App\Models\Webinar::where('id', $webinarId)
                        ->with(['translations' => function($query) {
                            $query->where('locale', app()->getLocale());
                        }])
                        ->first();

               if ($validCourses) {

                 $order = InstallmentOrder:: where([
                        'user_id' => $userId,
                        'webinar_id' => $webinarId,
                        'status' => 'open',
                    ])->first();

                    // print_r( $order);die;

                   $originalAmount = $order->item_price;
                        //  Calculate discount
                        if ($discount->percent > 0) {
                            $discountAmount = ($originalAmount * $discount->percent) / 100;
                        } else {
                            $discountAmount =$discount->amount;
                        }

                        $finalAmount = $discountAmount;

                        //  Save payment
                        \App\Models\WebinarPartPayment::create([
                            'user_id'         => $userId,
                            'installment_id' => $order->installment_id,
                            'webinar_id'      => $webinarId,
                            'amount'          => $finalAmount,
                            'created_at'      => now(),
                        ]);

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

            //  Success response
            return response()->json([
                'success' => true,
                'toast' => [
                    'title'  => 'Coupon Applied',
                    'msg'    => 'Discount applied successfully',
                    'status' => 'success'
                ],
                'data' => [
                    'original_amount' => $originalAmount,
                    'discount_amount' => round($discountAmount, 2),
                    'final_amount'    => round($finalAmount, 2),
                    'discount_type'   => $discount->percent > 0 ? 'percentage' : 'fixed'
                ]
            ]);
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
         * V-12 FIX: Rewritten refundPayment() — SOFT-REVOKE pattern.
         * NO hard deletes. All original records are preserved.
         * Creates reversal Accounting entries and a Refund record.
         *
         * NOTE: Called from within updateStatus() which already has an open
         * DB::beginTransaction(). Do NOT open a nested transaction here.
         */
        public function refundPayment($supportRequest)
        {
            $userId = $supportRequest->user_id;
            $courseId = $supportRequest->webinar_id;

            \Log::info('Processing refund (soft-revoke)', [
                'user_id' => $userId,
                'webinar_id' => $courseId,
                'support_request_id' => $supportRequest->id,
            ]);

            if (empty($userId) || empty($courseId)) {
                throw new \RuntimeException('Refund failed: missing user_id or webinar_id.');
            }

            // 1. Soft-revoke Sale (NO DELETE)
            $sale = Sale::where('buyer_id', $userId)
                ->where('webinar_id', $courseId)
                ->whereNull('refund_at')
                ->where('access_to_purchased_item', 1)
                ->lockForUpdate()
                ->first();

            if ($sale) {
                $sale->update([
                    'refund_at' => time(),
                    'access_to_purchased_item' => 0,
                ]);
            }

            // 2. Create reversal Accounting entry (NO DELETE of originals)
            $refundAmount = $sale ? $sale->amount : 0;
            Accounting::create([
                'user_id' => $userId,
                'amount' => -1 * abs($refundAmount),
                'type' => 'deduction',
                'type_account' => Accounting::$asset,
                'description' => "Refund: Support #{$supportRequest->ticket_number} - Course #{$courseId}",
                'created_at' => time(),
            ]);

            // 3. Soft-close InstallmentOrder (NO DELETE)
            InstallmentOrder::where('user_id', $userId)
                ->where('webinar_id', $courseId)
                ->whereIn('status', ['open', 'paying'])
                ->update(['status' => 'refunded']);

            // 4. Revoke WebinarAccessControl (NO DELETE)
            WebinarAccessControl::where('user_id', $userId)
                ->where('webinar_id', $courseId)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);

            // 5. Create Refund record for audit trail
            \App\Models\Refund::create([
                'user_id' => $userId,
                'sale_id' => $sale ? $sale->id : null,
                'support_request_id' => $supportRequest->id,
                'refund_amount' => $refundAmount,
                'refund_method' => 'bank_transfer',
                'processed_by' => Auth::id(),
                'status' => 'pending',
            ]);

            // 6. UPE: Record refund in UPE ledger + revoke UPE sale
            if ($courseId) {
                $bridge = app(SupportUpeBridge::class);
                $bridge->recordRefund($userId, $courseId, $supportRequest->id, Auth::id());
            }

            \Log::info('Refund completed (soft-revoke, all records preserved)', [
                'user_id' => $userId,
                'webinar_id' => $courseId,
                'refund_amount' => $refundAmount,
                'support_request_id' => $supportRequest->id,
            ]);
        }

    
}