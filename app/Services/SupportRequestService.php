<?php

namespace App\Services;

use App\Models\NewSupportForAsttrolok;
use App\Models\SupportAuditLog;
use App\Models\Sale;
use App\Models\WebinarAccessControl;
use App\Models\MentorAccessRequest;
use App\Models\Webinar;
use App\Models\Refund;
use App\Models\CouponCredit;
use App\Models\ServiceAccess;
use App\Models\Accounting;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentRestructureRequest;
use App\Models\InstallmentStep;
use App\Models\SubStepInstallment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Discount;
use App\Services\AdminCoursePurchaseService;
use App\Services\SupportUpeBridge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportRequestService
{
    /**
     * Valid status transitions enforced by the state machine.
     * Format: 'from_status' => ['to_status' => 'required_role']
     */
    const TRANSITIONS = [
        'pending' => [
            'verified' => 'support',
            'rejected' => 'support',
        ],
        'verified' => [
            'executed' => 'admin',
            'rejected' => 'admin',
        ],
    ];

    /**
     * Validate and execute a status transition.
     */
    public function transition(NewSupportForAsttrolok $request, string $newStatus, array $data, $user)
    {
        $oldStatus = $request->status;

        $this->validateTransition($oldStatus, $newStatus, $user);

        DB::transaction(function () use ($request, $oldStatus, $newStatus, $data, $user) {
            $lockedRequest = NewSupportForAsttrolok::where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if ($lockedRequest->status !== $oldStatus) {
                throw new \RuntimeException('Request status has changed concurrently. Please refresh and try again.');
            }

            if ($newStatus === 'executed' && $lockedRequest->executed_at !== null) {
                throw new \RuntimeException('This request has already been executed.');
            }

            switch ($newStatus) {
                case 'verified':
                    $this->handleVerification($lockedRequest, $data, $user);
                    break;

                case 'executed':
                    $this->handleExecution($lockedRequest, $data, $user);
                    break;

                case 'rejected':
                    $this->handleRejection($lockedRequest, $data, $user);
                    break;
            }

            SupportAuditLog::log(
                $lockedRequest->id,
                $user->id,
                $newStatus,
                $this->getUserRole($user),
                $oldStatus,
                $newStatus,
                $data,
                request()->ip()
            );
        });
    }

    /**
     * Validate that the transition is allowed for this user's role.
     */
    private function validateTransition(string $from, string $to, $user)
    {
        if (!isset(self::TRANSITIONS[$from])) {
            throw new \InvalidArgumentException("No transitions allowed from status '{$from}'.");
        }

        if (!isset(self::TRANSITIONS[$from][$to])) {
            throw new \InvalidArgumentException("Transition from '{$from}' to '{$to}' is not allowed.");
        }

        $requiredRole = self::TRANSITIONS[$from][$to];
        $userRole = $this->getUserRole($user);

        if ($requiredRole === 'support' && !in_array($userRole, ['support', 'admin'])) {
            throw new \RuntimeException('Only Support role can perform this action.');
        }

        if ($requiredRole === 'admin' && $userRole !== 'admin') {
            throw new \RuntimeException('Only Admin can perform this action.');
        }
    }

    /**
     * Map the user to a simplified role for state machine purposes.
     */
    private function getUserRole($user): string
    {
        if ($user->isAdmin() || $user->role_name === 'admin') {
            return 'admin';
        }

        if ($user->role_name === 'Support Role' || $user->role_name === 'support') {
            return 'support';
        }

        return 'user';
    }

    /**
     * Handle the verification step (Support role only).
     */
    private function handleVerification(NewSupportForAsttrolok $request, array $data, $user)
    {
        $updateData = [
            'status' => 'verified',
            'verified_by' => $user->id,
            'verified_at' => now(),
        ];

        if (isset($data['support_remarks'])) {
            $updateData['support_remarks'] = $data['support_remarks'];
        }

        if (isset($data['verified_amount'])) {
            $updateData['verified_amount'] = $data['verified_amount'];
        }

        if (isset($data['temporary_access_percentage'])) {
            $updateData['temporary_access_percentage'] = $data['temporary_access_percentage'];
        }

        $request->update($updateData);
    }

    /**
     * Handle rejection (Support for pending, Admin for verified).
     */
    private function handleRejection(NewSupportForAsttrolok $request, array $data, $user)
    {
        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);
    }

    /**
     * Handle execution — dispatch to the appropriate scenario handler.
     */
    private function handleExecution(NewSupportForAsttrolok $request, array $data, $user)
    {
        $scenario = $request->support_scenario;

        $handler = $this->getScenarioHandler($scenario);
        $handler($request, $data, $user);

        $request->update([
            'status' => 'executed',
            'executed_by' => $user->id,
            'executed_at' => now(),
            'admin_remarks' => $data['admin_remarks'] ?? null,
        ]);
    }

    /**
     * Get the handler function for a given scenario.
     */
    private function getScenarioHandler(string $scenario): callable
    {
        $handlers = [
            'course_extension'         => [$this, 'executeCourseExtension'],
            'temporary_access'         => [$this, 'executeTemporaryAccess'],
            'mentor_access'            => [$this, 'executeMentorAccess'],
            'relatives_friends_access' => [$this, 'executeRelativesFriendsAccess'],
            'free_course_grant'        => [$this, 'executeFreeCourseGrant'],
            'offline_cash_payment'     => [$this, 'executeOfflineCashPayment'],
            'installment_restructure'  => [$this, 'executeInstallmentRestructure'],
            'new_service_access'       => [$this, 'executeNewServiceAccess'],
            'refund_payment'           => [$this, 'executeRefundPayment'],
            'post_purchase_coupon'     => [$this, 'executePostPurchaseCoupon'],
            'wrong_course_correction'  => [$this, 'executeWrongCourseCorrection'],
        ];

        if (!isset($handlers[$scenario])) {
            throw new \InvalidArgumentException("Unknown scenario: {$scenario}");
        }

        return $handlers[$scenario];
    }

    // =========================================================================
    // SCENARIO HANDLERS
    // =========================================================================

    /**
     * Scenario 1: Course Extension
     */
    private function executeCourseExtension(NewSupportForAsttrolok $request, array $data, $user)
    {
        $existingAccess = WebinarAccessControl::where('user_id', $request->user_id)
            ->where('webinar_id', $request->webinar_id)
            ->where('status', 'active')
            ->first();

        if ($existingAccess) {
            $existingAccess->update(['status' => 'replaced']);
        }

        $newAccess = new WebinarAccessControl();
        $newAccess->user_id = $request->user_id;
        $newAccess->webinar_id = $request->webinar_id;
        $newAccess->percentage = 100;
        $newAccess->expire = now()->addDays($request->extension_days);
        $newAccess->status = 'active';
        $newAccess->save();

        // UPE: Create extension sale so AccessEngine grants access
        app(SupportUpeBridge::class)->grantCourseExtension(
            $request->user_id, $request->webinar_id, $request->id, $user->id, $request->extension_days
        );

        Log::info('Course extension granted', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'webinar_id' => $request->webinar_id,
            'extension_days' => $request->extension_days,
            'new_expire' => $newAccess->expire,
        ]);
    }

    /**
     * Scenario 2: Temporary Access (Pending Payment)
     */
    private function executeTemporaryAccess(NewSupportForAsttrolok $request, array $data, $user)
    {
        $percentage = $request->temporary_access_percentage ?? 50;

        $access = new WebinarAccessControl();
        $access->user_id = $request->user_id;
        $access->webinar_id = $request->webinar_id;
        $access->percentage = $percentage;
        $access->expire = now()->addDays(7);
        $access->status = 'active';
        $access->save();

        // UPE: Create temporary access support action so AccessEngine grants access
        app(SupportUpeBridge::class)->grantTemporaryAccess(
            $request->user_id, $request->webinar_id, $request->id, $user->id, 7, $percentage
        );

        Log::info('Temporary access granted', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'webinar_id' => $request->webinar_id,
            'percentage' => $percentage,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Scenario 3: Mentor Access
     */
    private function executeMentorAccess(NewSupportForAsttrolok $request, array $data, $user)
    {
        $existing = MentorAccessRequest::where('user_id', $request->user_id)
            ->where('webinar_id', $request->webinar_id)
            ->where('support_request_id', $request->id)
            ->whereIn('status', ['approved', 'completed'])
            ->first();

        if ($existing) {
            throw new \RuntimeException('Mentor access already granted for this support request.');
        }

        MentorAccessRequest::create([
            'user_id' => $request->user_id,
            'webinar_id' => $request->webinar_id,
            'requested_mentor_id' => $request->requested_mentor_id,
            'mentor_change_reason' => $request->mentor_change_reason ?? 'Via support ticket',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'support_request_id' => $request->id,
            'admin_notes' => 'Approved via secure support path',
        ]);

        Log::info('Mentor access granted', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'webinar_id' => $request->webinar_id,
            'requested_mentor_id' => $request->requested_mentor_id,
        ]);
    }

    /**
     * Scenario 4: Relatives / Friends Access
     */
    private function executeRelativesFriendsAccess(NewSupportForAsttrolok $request, array $data, $user)
    {
        $webinar = Webinar::findOrFail($request->webinar_id);
        $beneficiaryUserId = $request->user_id;

        $existingSale = Sale::where('buyer_id', $beneficiaryUserId)
            ->where('webinar_id', $request->webinar_id)
            ->whereNull('refund_at')
            ->where('access_to_purchased_item', 1)
            ->first();

        if ($existingSale) {
            throw new \RuntimeException('User already has access to this course.');
        }

        Sale::create([
            'buyer_id' => $beneficiaryUserId,
            'seller_id' => $webinar->creator_id,
            'webinar_id' => $webinar->id,
            'type' => Sale::$webinar,
            'manual_added' => true,
            'payment_method' => Sale::$credit,
            'amount' => 0,
            'total_amount' => 0,
            'access_to_purchased_item' => 1,
            'support_request_id' => $request->id,
            'granted_by_admin_id' => $user->id,
            'created_at' => time(),
        ]);

        // UPE: Create UPE sale so AccessEngine grants access
        app(SupportUpeBridge::class)->grantRelativeAccess(
            $beneficiaryUserId, $request->webinar_id, $request->id, $user->id
        );

        Log::info('Relatives/friends access granted', [
            'support_request_id' => $request->id,
            'beneficiary_user_id' => $beneficiaryUserId,
            'webinar_id' => $request->webinar_id,
        ]);
    }

    /**
     * Scenario 5: Free Course Grant
     */
    private function executeFreeCourseGrant(NewSupportForAsttrolok $request, array $data, $user)
    {
        $sourceCourseId = $request->source_course_id;
        $targetCourseId = $request->target_course_id ?? $request->webinar_id;
        $targetWebinar = Webinar::findOrFail($targetCourseId);

        $sourceUsers = Sale::where('webinar_id', $sourceCourseId)
            ->where('access_to_purchased_item', 1)
            ->whereNull('refund_at')
            ->pluck('buyer_id')
            ->unique();

        $grantedCount = 0;
        $skippedCount = 0;

        foreach ($sourceUsers as $userId) {
            $existing = Sale::where('buyer_id', $userId)
                ->where('webinar_id', $targetCourseId)
                ->whereNull('refund_at')
                ->first();

            if ($existing) {
                $skippedCount++;
                continue;
            }

            Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $targetWebinar->creator_id,
                'webinar_id' => $targetCourseId,
                'type' => Sale::$webinar,
                'manual_added' => true,
                'payment_method' => Sale::$credit,
                'amount' => 0,
                'total_amount' => 0,
                'access_to_purchased_item' => 1,
                'support_request_id' => $request->id,
                'granted_by_admin_id' => $user->id,
                'created_at' => time(),
            ]);

            // UPE: Create UPE sale so AccessEngine grants access
            app(SupportUpeBridge::class)->grantFreeCourseAccess(
                $userId, $targetCourseId, $request->id, $user->id
            );

            $grantedCount++;
        }

        Log::info('Free course grant completed', [
            'support_request_id' => $request->id,
            'source_course_id' => $sourceCourseId,
            'target_course_id' => $targetCourseId,
            'granted_count' => $grantedCount,
            'skipped_count' => $skippedCount,
        ]);
    }

    /**
     * Scenario 6: Offline / Cash Payment
     */
    private function executeOfflineCashPayment(NewSupportForAsttrolok $request, array $data, $user)
    {
        $purchaseService = new AdminCoursePurchaseService();

        if (!empty($request->installment_id)) {
            $result = $purchaseService->purchaseCourseWithInstallment(
                $request->webinar_id,
                $request->user_id,
                $request->installment_id,
                null,
                $user->id
            );
        } else {
            $result = $purchaseService->purchaseCourseDirectly(
                $request->webinar_id,
                $request->user_id,
                null,
                $user->id
            );
        }

        if (!($result['success'] ?? false)) {
            throw new \RuntimeException('Offline payment failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        if (isset($result['sale_id'])) {
            Sale::where('id', $result['sale_id'])->update([
                'support_request_id' => $request->id,
                'granted_by_admin_id' => $user->id,
            ]);
        }

        Log::info('Offline cash payment processed', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'webinar_id' => $request->webinar_id,
            'result' => $result['success'] ?? false,
        ]);
    }

    /**
     * Scenario 7: Installment Restructure
     */
    private function executeInstallmentRestructure(NewSupportForAsttrolok $request, array $data, $user)
    {
        $userId = $request->user_id;
        $webinarId = $request->webinar_id;

        // 1. Lock the installment order
        $installmentOrder = InstallmentOrder::where('user_id', $userId)
            ->where('webinar_id', $webinarId)
            ->whereIn('status', ['open', 'paying'])
            ->lockForUpdate()
            ->first();

        if (!$installmentOrder) {
            throw new \RuntimeException('No active installment order found for this user and course.');
        }

        // 2. Find pending restructure request (try both FK column names for compatibility)
        $restructureRequest = InstallmentRestructureRequest::where(function ($q) use ($request, $userId) {
                $q->where('support_request_id', $request->id)
                  ->orWhere('support_ticket_id', $request->id);
            })
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$restructureRequest) {
            throw new \RuntimeException('No pending restructure request found for support ticket #' . $request->id);
        }

        // 3. Find the installment step to restructure
        $installmentStep = InstallmentStep::find($restructureRequest->installment_step_id);
        if (!$installmentStep) {
            throw new \RuntimeException('Installment step not found: ' . $restructureRequest->installment_step_id);
        }

        // 4. Course mismatch check
        if ($installmentOrder->webinar_id != $webinarId) {
            throw new \RuntimeException('Course mismatch - installment order belongs to different course.');
        }

        // 5. Calculate step amount (server-side only, never client-supplied)
        $stepPayment = InstallmentOrderPayment::where('installment_order_id', $installmentOrder->id)
            ->where('step_id', $installmentStep->id)
            ->where(function ($q) {
                $q->where('status', 'paying')->orWhere('status', 'pending');
            })
            ->first();

        if ($stepPayment) {
            $totalStepAmount = $stepPayment->amount;
        } else {
            $itemPrice = $installmentOrder->getItemPrice();
            $totalStepAmount = ($installmentStep->amount_type == 'percent')
                ? ($itemPrice * $installmentStep->amount) / 100
                : $installmentStep->amount;
        }

        // 6. Calculate 50-50 split
        $subStep1Amount = $totalStepAmount / 2;
        $subStep2Amount = $totalStepAmount / 2;

        // 7. Calculate due dates
        $orderCreatedAt = $installmentOrder->created_at;
        $originalDueDate = strtotime($orderCreatedAt) + ($installmentStep->deadline * 86400);
        $subStep2DueDate = $originalDueDate + (30 * 86400);

        // 8. Approve the restructure request
        $restructureRequest->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'admin_notes' => 'Approved - Creating 2 sub-steps with 50-50 split',
            'number_of_sub_steps' => 2,
        ]);

        // 9. Soft-cancel existing sub-steps (no hard deletes)
        SubStepInstallment::where('user_id', $userId)
            ->where('installment_step_id', $installmentStep->id)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->update(['status' => 'cancelled']);

        // 10. Create Sub-Step 1 (50%) - due on original date
        $subStep1 = SubStepInstallment::create([
            'user_id' => $userId,
            'order_id' => $installmentOrder->id,
            'webinar_id' => $webinarId,
            'installment_order_id' => $installmentOrder->id,
            'installment_step_id' => $installmentStep->id,
            'sub_step_number' => 1,
            'price' => $subStep1Amount,
            'due_date' => $originalDueDate,
            'status' => SubStepInstallment::STATUS_APPROVED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 11. Create Sub-Step 2 (50%) - due 30 days later
        $subStep2 = SubStepInstallment::create([
            'user_id' => $userId,
            'order_id' => $installmentOrder->id,
            'webinar_id' => $webinarId,
            'installment_order_id' => $installmentOrder->id,
            'installment_step_id' => $installmentStep->id,
            'sub_step_number' => 2,
            'price' => $subStep2Amount,
            'due_date' => $subStep2DueDate,
            'status' => SubStepInstallment::STATUS_APPROVED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 12. Store sub_steps_data on restructure request
        $restructureRequest->update([
            'sub_steps_data' => [
                ['sub_step_id' => $subStep1->id, 'amount' => $subStep1Amount, 'deadline' => $originalDueDate, 'order' => 1, 'status' => 'approved'],
                ['sub_step_id' => $subStep2->id, 'amount' => $subStep2Amount, 'deadline' => $subStep2DueDate, 'order' => 2, 'status' => 'approved'],
            ]
        ]);

        Log::info('Installment restructure executed', [
            'support_request_id' => $request->id,
            'installment_order_id' => $installmentOrder->id,
            'user_id' => $userId,
            'total_step_amount' => $totalStepAmount,
            'sub_step_1_id' => $subStep1->id,
            'sub_step_2_id' => $subStep2->id,
        ]);
    }

    /**
     * Scenario 8: New Service / Event Access
     */
    private function executeNewServiceAccess(NewSupportForAsttrolok $request, array $data, $user)
    {
        $endDate = isset($data['end_date']) ? $data['end_date'] : now()->addDays(30);

        ServiceAccess::create([
            'user_id' => $request->user_id,
            'service_type' => $data['service_type'] ?? 'event',
            'service_id' => $data['service_id'] ?? null,
            'start_date' => now(),
            'end_date' => $endDate,
            'status' => 'active',
            'support_request_id' => $request->id,
            'granted_by' => $user->id,
        ]);

        Log::info('New service access granted', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'service_type' => $data['service_type'] ?? 'event',
        ]);
    }

    /**
     * Scenario 9: Refund Payment (SOFT-REVOKE, no hard deletes)
     */
    private function executeRefundPayment(NewSupportForAsttrolok $request, array $data, $user)
    {
        $sale = Sale::where('buyer_id', $request->user_id)
            ->where('webinar_id', $request->webinar_id)
            ->whereNull('refund_at')
            ->where('access_to_purchased_item', 1)
            ->lockForUpdate()
            ->first();

        if (!$sale) {
            throw new \RuntimeException('No active purchase found for this user and course.');
        }

        $sale->update([
            'refund_at' => time(),
            'access_to_purchased_item' => 0,
        ]);

        Accounting::create([
            'user_id' => $sale->buyer_id,
            'amount' => -1 * abs($sale->amount),
            'type' => 'deduction',
            'type_account' => Accounting::$asset,
            'description' => "Refund: Support #{$request->ticket_number} - Course #{$request->webinar_id}",
            'created_at' => time(),
        ]);

        $refundAmount = $request->verified_amount ?? $sale->amount;

        Refund::create([
            'user_id' => $sale->buyer_id,
            'sale_id' => $sale->id,
            'support_request_id' => $request->id,
            'refund_amount' => $refundAmount,
            'refund_method' => $data['refund_method'] ?? 'bank_transfer',
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'account_holder_name' => $data['account_holder_name'] ?? null,
            'processed_by' => $user->id,
            'status' => 'pending',
        ]);

        $accessControl = WebinarAccessControl::where('user_id', $request->user_id)
            ->where('webinar_id', $request->webinar_id)
            ->where('status', 'active')
            ->get();

        foreach ($accessControl as $ac) {
            $ac->update(['status' => 'revoked']);
        }

        // UPE: Record refund in UPE ledger
        app(SupportUpeBridge::class)->recordRefund(
            $request->user_id, $request->webinar_id, $request->id, $user->id
        );

        Log::info('Refund payment processed (soft-revoke)', [
            'support_request_id' => $request->id,
            'sale_id' => $sale->id,
            'user_id' => $request->user_id,
            'refund_amount' => $refundAmount,
        ]);
    }

    /**
     * Scenario 10: Post-Purchase Coupon Apply
     */
    private function executePostPurchaseCoupon(NewSupportForAsttrolok $request, array $data, $user)
    {
        $couponCode = $request->coupon_code ?? $data['coupon_code'] ?? null;

        if (!$couponCode) {
            throw new \RuntimeException('No coupon code provided.');
        }

        $discount = Discount::where('code', strtoupper(trim($couponCode)))
            ->where('status', 'active')
            ->first();

        if (!$discount) {
            throw new \RuntimeException('Invalid or expired coupon code.');
        }

        $sale = Sale::where('buyer_id', $request->user_id)
            ->where('webinar_id', $request->webinar_id)
            ->whereNull('refund_at')
            ->first();

        $originalAmount = $sale ? $sale->total_amount : 0;
        $discountAmount = 0;

        if ($discount->discount_type === 'percentage') {
            $percent = $discount->percent ?? 0;
            $discountAmount = $originalAmount * $percent / 100;
        } else {
            $discountAmount = min($discount->amount, $originalAmount);
        }

        $creditAmount = min($discountAmount, $originalAmount);

        CouponCredit::create([
            'user_id' => $request->user_id,
            'sale_id' => $sale ? $sale->id : null,
            'discount_id' => $discount->id,
            'coupon_code' => $couponCode,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'credit_amount' => $creditAmount,
            'support_request_id' => $request->id,
            'processed_by' => $user->id,
        ]);

        if ($creditAmount > 0) {
            Accounting::create([
                'user_id' => $request->user_id,
                'amount' => $creditAmount,
                'type' => Accounting::$addiction,
                'type_account' => Accounting::$asset,
                'description' => "Post-purchase coupon credit: {$couponCode} - Support #{$request->ticket_number}",
                'created_at' => time(),
            ]);
        }

        // P1 FIX: Decrement coupon usage count to prevent unlimited reuse
        $discount->increment('used_count');

        Log::info('Post-purchase coupon applied', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'coupon_code' => $couponCode,
            'discount_amount' => $discountAmount,
            'credit_amount' => $creditAmount,
        ]);
    }

    /**
     * Scenario 11: Wrong Course Correction (PRESERVE HISTORY, no webinar_id overwrite)
     */
    private function executeWrongCourseCorrection(NewSupportForAsttrolok $request, array $data, $user)
    {
        $wrongCourseId = $request->webinar_id;
        $correctCourseId = $request->correct_course_id ?? $data['correct_course_id'] ?? null;

        if (!$correctCourseId) {
            throw new \RuntimeException('Correct course ID is required.');
        }

        $correctCourse = Webinar::findOrFail($correctCourseId);

        $oldSale = Sale::where('buyer_id', $request->user_id)
            ->where('webinar_id', $wrongCourseId)
            ->whereNull('refund_at')
            ->where('access_to_purchased_item', 1)
            ->lockForUpdate()
            ->first();

        if (!$oldSale) {
            throw new \RuntimeException('No active purchase found for the wrong course.');
        }

        $existingCorrect = Sale::where('buyer_id', $request->user_id)
            ->where('webinar_id', $correctCourseId)
            ->whereNull('refund_at')
            ->first();

        if ($existingCorrect) {
            throw new \RuntimeException('User already has access to the correct course.');
        }

        $oldSale->update([
            'refund_at' => time(),
            'access_to_purchased_item' => 0,
        ]);

        Accounting::create([
            'user_id' => $oldSale->buyer_id,
            'amount' => -1 * abs($oldSale->amount),
            'type' => 'deduction',
            'type_account' => Accounting::$asset,
            'description' => "Wrong course reversal: Course #{$wrongCourseId} - Support #{$request->ticket_number}",
            'created_at' => time(),
        ]);

        $newSale = Sale::create([
            'buyer_id' => $request->user_id,
            'seller_id' => $correctCourse->creator_id,
            'webinar_id' => $correctCourseId,
            'type' => Sale::$webinar,
            'payment_method' => $oldSale->payment_method,
            'amount' => $oldSale->amount,
            'total_amount' => $oldSale->total_amount,
            'access_to_purchased_item' => 1,
            'support_request_id' => $request->id,
            'granted_by_admin_id' => $user->id,
            'created_at' => time(),
        ]);

        Accounting::create([
            'user_id' => $request->user_id,
            'amount' => $oldSale->amount,
            'type' => Accounting::$addiction,
            'type_account' => Accounting::$asset,
            'description' => "Wrong course correction: Course #{$correctCourseId} - Support #{$request->ticket_number}",
            'created_at' => time(),
        ]);

        $oldInstallment = InstallmentOrder::where('user_id', $request->user_id)
            ->where('webinar_id', $wrongCourseId)
            ->whereIn('status', ['open', 'paying'])
            ->first();

        if ($oldInstallment) {
            $oldInstallment->update([
                'status' => 'transferred',
                'transferred_to_order_id' => null,
            ]);

            $newInstallment = InstallmentOrder::create([
                'installment_id' => $oldInstallment->installment_id,
                'user_id' => $request->user_id,
                'webinar_id' => $correctCourseId,
                'item_price' => $correctCourse->getPrice(),
                'status' => 'open',
                'parent_order_id' => $oldInstallment->id,
                'created_at' => time(),
            ]);

            $oldInstallment->update(['transferred_to_order_id' => $newInstallment->id]);
        }

        $oldAccessControls = WebinarAccessControl::where('user_id', $request->user_id)
            ->where('webinar_id', $wrongCourseId)
            ->where('status', 'active')
            ->get();

        foreach ($oldAccessControls as $ac) {
            $ac->update(['status' => 'revoked']);
        }

        // UPE: Revoke wrong course + grant correct course in UPE
        app(SupportUpeBridge::class)->handleWrongCourseCorrection(
            $request->user_id, $wrongCourseId, $correctCourseId, $request->id, $user->id
        );

        Log::info('Wrong course correction completed', [
            'support_request_id' => $request->id,
            'user_id' => $request->user_id,
            'wrong_course_id' => $wrongCourseId,
            'correct_course_id' => $correctCourseId,
            'old_sale_id' => $oldSale->id,
            'new_sale_id' => $newSale->id,
        ]);
    }
}
