<?php

namespace App\Services;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use App\Models\PaymentEngine\UpeSupportAction;
use App\Models\PaymentEngine\UpeMentorBadge;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\InstallmentEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bridge between legacy AdminSupportController completion flow and the UPE system.
 *
 * When AdminSupportController marks a ticket as "completed", it creates legacy Sale
 * records. But AccessEngine ONLY reads from upe_sales. This bridge creates the
 * corresponding UPE records so AccessEngine can find them.
 *
 * RULES:
 *  - Never mutate existing UPE records
 *  - All operations are additive (new rows only)
 *  - Idempotent: safe to call twice for the same support request
 */
class SupportUpeBridge
{
    private PaymentLedgerService $ledger;
    private AccessEngine $access;

    public function __construct(PaymentLedgerService $ledger, AccessEngine $access)
    {
        $this->ledger = $ledger;
        $this->access = $access;
    }

    /**
     * Resolve UPE product_id from webinar_id.
     */
    public function resolveProductId(int $webinarId): ?int
    {
        $product = UpeProduct::where('external_id', $webinarId)
            ->where('product_type', 'course_video')
            ->first();

        return $product?->id;
    }

    /**
     * Get or create UPE product for a webinar.
     */
    public function getOrCreateProduct(int $webinarId): ?UpeProduct
    {
        $product = UpeProduct::where('external_id', $webinarId)
            ->where('product_type', 'course_video')
            ->first();

        if ($product) {
            return $product;
        }

        // Create product from webinar
        $webinar = \App\Models\Webinar::find($webinarId);
        if (!$webinar) {
            Log::error('SupportUpeBridge: Webinar not found', ['webinar_id' => $webinarId]);
            return null;
        }

        return UpeProduct::create([
            'product_type' => 'course_video',
            'external_id' => $webinarId,
            'base_fee' => $webinar->price ?? 0,
            'currency' => 'INR',
            'validity_days' => $webinar->access_days ?: null,
            'status' => 'active',
            'metadata' => ['slug' => $webinar->slug, 'legacy_id' => $webinarId, 'migrated_from' => 'webinars'],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  RELATIVE / FRIEND ACCESS — create UPE sale for the user
    // ══════════════════════════════════════════════════════════════

    public function grantRelativeAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        // Idempotency: check if UPE sale already exists for this support request
        $existing = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('support_request_id', $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Relative access UPE sale already exists', ['sale_id' => $existing->id]);
            return $existing;
        }

        $validFrom = now();
        $validUntil = $product->validity_days
            ? $validFrom->copy()->addDays($product->validity_days)
            : null;

        $sale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $product->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'currency' => 'INR',
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'support_request_id' => $supportRequestId,
            'approved_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_relative_access',
                'support_request_id' => $supportRequestId,
            ],
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Relative access UPE sale created', [
            'sale_id' => $sale->id, 'user_id' => $userId, 'product_id' => $product->id,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  MENTOR ACCESS — create UPE sale for the user + course
    // ══════════════════════════════════════════════════════════════

    public function grantMentorAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $existing = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('support_request_id', $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Mentor access UPE sale already exists', ['sale_id' => $existing->id]);
            return $existing;
        }

        $validFrom = now();
        $validUntil = $product->validity_days
            ? $validFrom->copy()->addDays($product->validity_days)
            : null;

        $sale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $product->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'currency' => 'INR',
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'support_request_id' => $supportRequestId,
            'approved_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_mentor_access',
                'support_request_id' => $supportRequestId,
            ],
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Mentor access UPE sale created', [
            'sale_id' => $sale->id, 'user_id' => $userId, 'product_id' => $product->id,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  TEMPORARY ACCESS — create UPE support action (not sale)
    // ══════════════════════════════════════════════════════════════

    public function grantTemporaryAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId, int $days = 7, int $percentage = 100): ?UpeSupportAction
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        // Idempotency
        $existing = UpeSupportAction::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('action_type', UpeSupportAction::ACTION_TEMPORARY_ACCESS)
            ->where('status', UpeSupportAction::STATUS_EXECUTED)
            ->where('metadata->support_request_id', (string) $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Temp access already exists', ['action_id' => $existing->id]);
            return $existing;
        }

        $expiresAt = now()->addDays($days);

        $action = UpeSupportAction::create([
            'uuid' => (string) Str::uuid(),
            'action_type' => UpeSupportAction::ACTION_TEMPORARY_ACCESS,
            'status' => UpeSupportAction::STATUS_EXECUTED,
            'user_id' => $userId,
            'product_id' => $product->id,
            'expires_at' => $expiresAt,
            'requested_by' => $adminId,
            'requested_at' => now(),
            'executed_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_temporary_access',
                'support_request_id' => $supportRequestId,
                'temporary_days' => $days,
                'percentage' => $percentage,
            ],
            'idempotency_key' => "admin_temp_{$supportRequestId}",
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Temporary access created', [
            'action_id' => $action->id, 'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        return $action;
    }

    // ══════════════════════════════════════════════════════════════
    //  COURSE EXTENSION — create child UPE sale
    // ══════════════════════════════════════════════════════════════

    public function grantCourseExtension(int $userId, int $webinarId, int $supportRequestId, int $adminId, int $days): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $existing = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('support_request_id', $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Extension UPE sale already exists', ['sale_id' => $existing->id]);
            return $existing;
        }

        // Find original sale to link as parent
        $parentSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->first();

        $validFrom = now();
        $validUntil = $validFrom->copy()->addDays($days);

        $sale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $product->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'currency' => 'INR',
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'parent_sale_id' => $parentSale?->id,
            'support_request_id' => $supportRequestId,
            'approved_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_course_extension',
                'extension_days' => $days,
                'support_request_id' => $supportRequestId,
            ],
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Course extension UPE sale created', [
            'sale_id' => $sale->id, 'days' => $days,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  OFFLINE PAYMENT V2 — full validation, coupon, installment allocation
    // ══════════════════════════════════════════════════════════════

    /**
     * Process an offline/cash payment with full UPE validation.
     *
     * Handles both non-installment (full payment) and installment-based courses.
     * Validates coupon, blocks underpayment for non-installment, allocates
     * payment sequentially for installment courses.
     *
     * @return array{success: bool, message: string, sale: ?UpeSale, plan: ?UpeInstallmentPlan,
     *               price_breakdown: array, allocation: array, access_granted: bool}
     */
    public function processOfflinePayment(
        int $userId,
        int $webinarId,
        int $supportRequestId,
        int $adminId,
        float $cashAmount,
        ?string $couponCode = null,
        ?int $installmentId = null
    ): array {
        // ── 1. Resolve product & course ──
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) {
            return $this->offlineResult(false, 'Course not found or product could not be created.');
        }

        $webinar = \App\Models\Webinar::find($webinarId);
        if (!$webinar) {
            return $this->offlineResult(false, 'Course not found.');
        }

        $originalPrice = (float) $webinar->getPrice();

        // ── 2. Validate & apply coupon ──
        $discountAmount = 0;
        $discountId = null;
        $couponError = null;

        if ($couponCode) {
            $discount = \App\Models\Discount::where('code', $couponCode)->first();
            if (!$discount) {
                $couponError = "Coupon '{$couponCode}' not found.";
            } elseif (!empty($discount->expired_at) && $discount->expired_at < time()) {
                $couponError = "Coupon '{$couponCode}' has expired.";
            } elseif ($discount->discountRemain() <= 0) {
                $couponError = "Coupon '{$couponCode}' has been fully used.";
            } else {
                // Check course-specific coupon restrictions
                if ($discount->source === 'course') {
                    $validCourseIds = $discount->discountCourses()->pluck('course_id')->toArray();
                    if (!empty($validCourseIds) && !in_array($webinarId, $validCourseIds)) {
                        $couponError = "Coupon '{$couponCode}' is not valid for this course.";
                    }
                }

                // Calculate discount amount (only if no error so far)
                if (!$couponError) {
                    $discountId = $discount->id;
                    if ($discount->discount_type === \App\Models\Discount::$discountTypeFixedAmount) {
                        $discountAmount = min((float) $discount->amount, $originalPrice);
                    } else {
                        $discountAmount = round($originalPrice * (float) $discount->percent / 100, 2);
                    }
                    $discountAmount = round($discountAmount, 0, PHP_ROUND_HALF_UP);
                }
            }

            if ($couponError) {
                return $this->offlineResult(false, $couponError, [
                    'original_price' => $originalPrice,
                    'discount_amount' => 0,
                    'final_payable' => $originalPrice,
                    'cash_amount' => $cashAmount,
                    'remaining' => max(0, $originalPrice - $cashAmount),
                ]);
            }
        }

        $finalPayable = max(0, $originalPrice - $discountAmount);
        $priceBreakdown = [
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'coupon_code' => $couponCode,
            'final_payable' => $finalPayable,
            'cash_amount' => $cashAmount,
            'remaining' => max(0, $finalPayable - $cashAmount),
        ];

        // ── 3. Idempotency check ──
        $idempotencyKey = "admin_offline_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();
        if ($existingEntry) {
            $existingSale = UpeSale::find($existingEntry->sale_id);
            return $this->offlineResult(true, 'Payment already processed (idempotent).', $priceBreakdown, $existingSale);
        }

        // ── 4. Branch: non-installment vs installment ──
        // Auto-detect installment plans if none selected
        if (!$installmentId) {
            $studentUser = \App\User::find($userId);
            $plansFinder = new \App\Mixins\Installment\InstallmentPlans($studentUser);
            $availablePlans = $plansFinder->getPlans(
                'courses', $webinar->id, $webinar->type, $webinar->category_id, $webinar->teacher_id
            );

            if ($availablePlans->isNotEmpty()) {
                // Course has installment plans — auto-select first and use installment flow
                $installmentId = $availablePlans->first()->id;
            } else {
                // No installment plans — require full payment
                return $this->processOfflineFullPayment(
                    $userId, $webinarId, $product, $webinar, $supportRequestId, $adminId,
                    $cashAmount, $finalPayable, $discountAmount, $discountId, $couponCode, $priceBreakdown
                );
            }
        }

        return $this->processOfflineInstallmentPayment(
            $userId, $webinarId, $product, $webinar, $supportRequestId, $adminId,
            $cashAmount, $finalPayable, $discountAmount, $discountId, $couponCode,
            $installmentId, $priceBreakdown
        );
    }

    /**
     * Non-installment full payment: cash must cover final payable amount.
     */
    private function processOfflineFullPayment(
        int $userId, int $webinarId, UpeProduct $product, $webinar,
        int $supportRequestId, int $adminId,
        float $cashAmount, float $finalPayable, float $discountAmount,
        ?int $discountId, ?string $couponCode, array $priceBreakdown
    ): array {
        // Validate: cash must cover full price (with ₹1 tolerance) — only for non-installment courses
        if ($cashAmount < $finalPayable - 1) {
            return $this->offlineResult(
                false,
                "For full payment, cash (₹" . number_format($cashAmount, 0) .
                ") must cover the entire course price (₹" . number_format($finalPayable, 0) .
                "). This course does not have an installment plan.",
                $priceBreakdown
            );
        }

        return DB::transaction(function () use (
            $userId, $webinarId, $product, $webinar, $supportRequestId, $adminId,
            $cashAmount, $finalPayable, $discountAmount, $discountId, $couponCode, $priceBreakdown
        ) {
            // Create UPE sale
            $validFrom = now();
            $validUntil = $product->validity_days
                ? $validFrom->copy()->addDays($product->validity_days)
                : null;

            $sale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $product->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'full',
                'base_fee_snapshot' => $product->base_fee,
                'currency' => 'INR',
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'support_request_id' => $supportRequestId,
                'approved_by' => $adminId,
                'executed_at' => now(),
                'metadata' => [
                    'source' => 'admin_offline_full_payment',
                    'support_request_id' => $supportRequestId,
                    'cash_amount' => $cashAmount,
                    'discount_amount' => $discountAmount,
                    'coupon_code' => $couponCode,
                ],
            ]);

            // Wallet-mediated flow: credit full cash → debit course price
            $walletService = app(\App\Services\PaymentEngine\WalletService::class);
            $excessAmount = $cashAmount - $finalPayable;

            // Step 1: Credit full cash amount to wallet
            $walletService->creditOfflinePayment(
                $userId,
                $cashAmount,
                $sale->id,
                "Offline cash payment of ₹{$cashAmount} for {$webinar->title} (support #{$supportRequestId})"
            );

            // Step 2: Debit course price from wallet
            $walletService->purchaseFromWallet(
                $userId,
                $finalPayable,
                null,
                "Purchase for Sale #{$sale->id} — {$webinar->title} (offline)"
            );

            // Record payment in UPE ledger
            $idempotencyKey = "admin_offline_{$supportRequestId}";
            $this->ledger->recordPayment(
                saleId: $sale->id,
                amount: $finalPayable,
                paymentMethod: 'cash',
                processedBy: $adminId,
                description: "Offline/cash full payment via wallet — support #{$supportRequestId}",
                idempotencyKey: $idempotencyKey
            );

            // Record coupon discount in ledger if applied
            if ($discountId && $discountAmount > 0) {
                $this->ledger->recordDiscount(
                    saleId: $sale->id,
                    amount: $discountAmount,
                    discountId: $discountId,
                    processedBy: $adminId,
                    description: "Coupon '{$couponCode}' applied to offline payment #{$supportRequestId}"
                );
            }

            // Legacy dual-write: Sale
            \App\Models\Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $webinar->creator_id ?? 1,
                'webinar_id' => $webinarId,
                'type' => \App\Models\Order::$webinar,
                'payment_method' => 'credit',
                'amount' => $cashAmount,
                'tax' => 0,
                'commission' => 0,
                'discount' => $discountAmount,
                'total_amount' => $cashAmount,
                'created_at' => time(),
            ]);

            // Legacy dual-write: Accounting
            \App\Models\Accounting::create([
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'amount' => $finalPayable,
                'type' => \App\Models\Accounting::$addiction,
                'description' => "Admin Offline Payment: {$webinar->title}",
                'created_at' => time(),
            ]);

            $this->access->invalidate($userId, $product->id);

            Log::info('SupportUpeBridge: Offline full payment via wallet', [
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'cash_amount' => $cashAmount,
                'discount' => $discountAmount,
                'final_payable' => $finalPayable,
                'excess_in_wallet' => $excessAmount,
                'admin_id' => $adminId,
                'support_request_id' => $supportRequestId,
            ]);

            $message = "₹{$cashAmount} credited to wallet → ₹{$finalPayable} debited for purchase.";
            if ($excessAmount > 0.01) {
                $message .= " ₹" . number_format($excessAmount, 2) . " remains in wallet.";
            }
            return $this->offlineResult(true, $message, $priceBreakdown, $sale, null, [], true);
        });
    }

    /**
     * Installment payment: create plan + schedules, allocate cash sequentially.
     */
    private function processOfflineInstallmentPayment(
        int $userId, int $webinarId, UpeProduct $product, $webinar,
        int $supportRequestId, int $adminId,
        float $cashAmount, float $finalPayable, float $discountAmount,
        ?int $discountId, ?string $couponCode,
        int $installmentId, array $priceBreakdown
    ): array {
        $installment = \App\Models\Installment::find($installmentId);
        if (!$installment || !$installment->enable) {
            return $this->offlineResult(false, 'Invalid or disabled installment plan.', $priceBreakdown);
        }

        return DB::transaction(function () use (
            $userId, $webinarId, $product, $webinar, $supportRequestId, $adminId,
            $cashAmount, $finalPayable, $discountAmount, $discountId, $couponCode,
            $installment, $installmentId, $priceBreakdown
        ) {
            // Find existing UPE sale or create new one
            $sale = UpeSale::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->where('pricing_mode', 'installment')
                ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
                ->first();

            $isNewSale = false;

            if (!$sale) {
                $isNewSale = true;
                $validFrom = now();
                $validUntil = $product->validity_days
                    ? $validFrom->copy()->addDays($product->validity_days)
                    : null;

                $sale = UpeSale::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'sale_type' => 'paid',
                    'pricing_mode' => 'installment',
                    'base_fee_snapshot' => $product->base_fee,
                    'currency' => 'INR',
                    'status' => 'pending_payment',
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil,
                    'support_request_id' => $supportRequestId,
                    'approved_by' => $adminId,
                    'executed_at' => now(),
                    'metadata' => [
                        'source' => 'admin_offline_installment_payment',
                        'support_request_id' => $supportRequestId,
                        'installment_id' => $installmentId,
                        'coupon_code' => $couponCode,
                    ],
                ]);
            }

            // Find or create UPE installment plan
            $plan = UpeInstallmentPlan::where('sale_id', $sale->id)
                ->whereIn('status', ['active', 'completed'])
                ->first();

            if (!$plan) {
                // Build schedule amounts from legacy Installment config
                $scheduleAmounts = [];
                $upfrontAmount = round($installment->getUpfront($finalPayable), 0, PHP_ROUND_HALF_UP);
                $scheduleAmounts[] = ['amount' => $upfrontAmount, 'deadline_days' => 0];

                $steps = $installment->steps()->orderBy('order')->get();
                foreach ($steps as $step) {
                    $stepAmount = round($step->getPrice($finalPayable), 0, PHP_ROUND_HALF_UP);
                    $scheduleAmounts[] = ['amount' => $stepAmount, 'deadline_days' => (int) $step->deadline];
                }

                $totalAmount = round(array_sum(array_column($scheduleAmounts, 'amount')), 2);

                $plan = UpeInstallmentPlan::create([
                    'sale_id' => $sale->id,
                    'total_amount' => $totalAmount,
                    'num_installments' => count($scheduleAmounts),
                    'plan_type' => 'standard',
                    'status' => 'active',
                ]);

                foreach ($scheduleAmounts as $i => $sched) {
                    UpeInstallmentSchedule::create([
                        'plan_id' => $plan->id,
                        'sequence' => $i + 1,
                        'due_date' => now()->addDays($sched['deadline_days']),
                        'amount_due' => $sched['amount'],
                        'amount_paid' => 0,
                        'status' => ($i === 0) ? 'due' : 'upcoming',
                    ]);
                }
            }

            // Allocate cash sequentially via InstallmentEngine
            $engine = app(InstallmentEngine::class);
            $engineResult = $engine->recordPayment(
                $plan, $cashAmount, 'cash', null, null, $adminId
            );

            // Record coupon discount in ledger if applied
            if ($discountId && $discountAmount > 0 && $isNewSale) {
                $this->ledger->recordDiscount(
                    saleId: $sale->id,
                    amount: $discountAmount,
                    discountId: $discountId,
                    processedBy: $adminId,
                    description: "Coupon '{$couponCode}' applied to offline installment #{$supportRequestId}"
                );
            }

            // Determine access: granted if upfront (seq 1) is paid
            $upfrontSchedule = $plan->schedules()->where('sequence', 1)->first();
            $accessGranted = $upfrontSchedule && in_array($upfrontSchedule->status, ['paid']);

            if ($accessGranted && $sale->status === 'pending_payment') {
                $sale->update(['status' => 'active']);
            }

            // Legacy dual-write: Sale
            \App\Models\Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $webinar->creator_id ?? 1,
                'webinar_id' => $webinarId,
                'type' => \App\Models\Order::$installmentPayment,
                'payment_method' => 'credit',
                'amount' => $cashAmount,
                'tax' => 0,
                'commission' => 0,
                'discount' => $discountAmount,
                'total_amount' => $cashAmount,
                'status' => 'part',
                'created_at' => time(),
            ]);

            // Legacy dual-write: WebinarPartPayment (only if installment_id is available)
            \App\Models\WebinarPartPayment::create([
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'installment_id' => $installmentId,
                'amount' => (int) round($cashAmount, 0, PHP_ROUND_HALF_UP),
                'created_at' => now(),
            ]);

            // Legacy dual-write: Accounting
            \App\Models\Accounting::create([
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'amount' => $cashAmount,
                'type' => \App\Models\Accounting::$addiction,
                'description' => "Admin Offline Installment Payment: {$webinar->title}",
                'created_at' => time(),
            ]);

            $this->access->invalidate($userId, $product->id);

            // Build allocation breakdown for admin display
            $allocation = [];
            $plan->refresh();
            foreach ($plan->schedules as $s) {
                $label = $s->sequence === 1 ? 'Upfront' : 'EMI ' . ($s->sequence - 1);
                $allocation[] = [
                    'label' => $label,
                    'amount_due' => (float) $s->amount_due,
                    'amount_paid' => (float) $s->amount_paid,
                    'status' => $s->status,
                    'due_date' => $s->due_date,
                ];
            }

            // Handle overpayment: credit excess cash to wallet
            $overpaymentAmount = $engineResult['overpayment'] ?? 0;
            if ($overpaymentAmount > 0.01) { // More than 1 paisa excess
                try {
                    $walletService = app(\App\Services\PaymentEngine\WalletService::class);
                    $walletService->credit(
                        $userId,
                        $overpaymentAmount,
                        \App\Models\PaymentEngine\WalletTransaction::TXN_OVERPAYMENT_REFUND,
                        "Excess cash from offline installment payment for {$webinar->title}",
                        'support_request',
                        $supportRequestId,
                        null,
                        [
                            'sale_id' => $sale->id,
                            'plan_id' => $plan->id,
                            'cash_amount' => $cashAmount,
                            'overpayment_amount' => $overpaymentAmount,
                        ]
                    );
                    
                    Log::info('SupportUpeBridge: Excess installment cash credited to wallet', [
                        'user_id' => $userId,
                        'overpayment_amount' => $overpaymentAmount,
                        'sale_id' => $sale->id,
                        'plan_id' => $plan->id,
                        'support_request_id' => $supportRequestId,
                    ]);
                } catch (\Exception $walletErr) {
                    Log::error('SupportUpeBridge: Failed to credit excess installment cash to wallet', [
                        'user_id' => $userId,
                        'overpayment_amount' => $overpaymentAmount,
                        'error' => $walletErr->getMessage(),
                    ]);
                }
            }

            // Update price breakdown with installment-specific info
            $priceBreakdown['installment_total'] = (float) $plan->total_amount;
            $priceBreakdown['upfront_amount'] = $upfrontSchedule ? (float) $upfrontSchedule->amount_due : 0;

            Log::info('SupportUpeBridge: Offline installment payment processed', [
                'sale_id' => $sale->id,
                'plan_id' => $plan->id,
                'user_id' => $userId,
                'cash_amount' => $cashAmount,
                'discount' => $discountAmount,
                'access_granted' => $accessGranted,
                'engine_result' => $engineResult,
                'overpayment_amount' => $overpaymentAmount,
                'admin_id' => $adminId,
                'support_request_id' => $supportRequestId,
            ]);

            $msg = $accessGranted
                ? 'Installment payment processed. Upfront covered — access granted.' . ($overpaymentAmount > 0.01 ? " Excess ₹" . number_format($overpaymentAmount, 2) . " credited to wallet." : '')
                : 'Installment payment recorded. Upfront NOT fully covered — access NOT granted yet.' . ($overpaymentAmount > 0.01 ? " Excess ₹" . number_format($overpaymentAmount, 2) . " credited to wallet." : '');

            return $this->offlineResult(true, $msg, $priceBreakdown, $sale, $plan, $allocation, $accessGranted);
        });
    }

    /**
     * Build a standardized result array for offline payment processing.
     */
    private function offlineResult(
        bool $success,
        string $message,
        array $priceBreakdown = [],
        ?UpeSale $sale = null,
        ?UpeInstallmentPlan $plan = null,
        array $allocation = [],
        bool $accessGranted = false
    ): array {
        return [
            'success' => $success,
            'message' => $message,
            'sale' => $sale,
            'plan' => $plan,
            'price_breakdown' => $priceBreakdown,
            'allocation' => $allocation,
            'access_granted' => $accessGranted,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  OFFLINE PAYMENT (LEGACY) — simple UPE sale + ledger entry
    // ══════════════════════════════════════════════════════════════

    /** @deprecated Use processOfflinePayment() instead */
    public function recordOfflinePayment(int $userId, int $webinarId, int $supportRequestId, int $adminId, float $cashAmount): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        // Find existing UPE sale or create new one
        $sale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if (!$sale) {
            $validFrom = now();
            $validUntil = $product->validity_days
                ? $validFrom->copy()->addDays($product->validity_days)
                : null;

            $sale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $product->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'full',
                'base_fee_snapshot' => $product->base_fee,
                'currency' => 'INR',
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'support_request_id' => $supportRequestId,
                'approved_by' => $adminId,
                'executed_at' => now(),
                'metadata' => [
                    'source' => 'admin_support_offline_payment',
                    'support_request_id' => $supportRequestId,
                    'cash_amount' => $cashAmount,
                ],
            ]);
        }

        // Record the ACTUAL cash amount in the ledger (not base_fee)
        $idempotencyKey = "admin_offline_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();

        if (!$existingEntry) {
            $this->ledger->recordPayment(
                saleId: $sale->id,
                amount: $cashAmount,
                paymentMethod: 'cash',
                processedBy: $adminId,
                description: "Offline/cash payment via support request #{$supportRequestId}",
                idempotencyKey: $idempotencyKey
            );
        }

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Offline payment recorded', [
            'sale_id' => $sale->id, 'cash_amount' => $cashAmount,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  REFUND — create UPE refund ledger entry + update sale status
    // ══════════════════════════════════════════════════════════════

    public function recordRefund(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $sale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if (!$sale) {
            Log::warning('SupportUpeBridge: No UPE sale found for refund', [
                'user_id' => $userId, 'product_id' => $product->id,
            ]);
            return null;
        }

        $balance = $this->ledger->balance($sale->id);
        if ($balance <= 0) {
            // Free course or zero balance — just mark as refunded
            $sale->update(['status' => 'refunded']);
            $this->access->invalidate($userId, $product->id);
            return $sale;
        }

        $idempotencyKey = "admin_refund_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();

        if (!$existingEntry) {
            $this->ledger->recordRefund(
                saleId: $sale->id,
                amount: $balance,
                paymentMethod: 'bank_transfer',
                processedBy: $adminId,
                referenceType: 'support_request',
                referenceId: $supportRequestId,
                description: "Refund via admin support #{$supportRequestId}",
                idempotencyKey: $idempotencyKey
            );

            // Credit refund amount to user's wallet
            try {
                $walletService = app(\App\Services\PaymentEngine\WalletService::class);
                $walletService->refundToWallet(
                    $userId,
                    $balance,
                    $sale->id,
                    "Refund for support request #{$supportRequestId}"
                );
                Log::info('SupportUpeBridge: Refund credited to wallet', [
                    'user_id' => $userId, 'amount' => $balance, 'sale_id' => $sale->id,
                ]);
            } catch (\Exception $walletErr) {
                Log::error('SupportUpeBridge: Failed to credit refund to wallet (non-fatal)', [
                    'user_id' => $userId, 'amount' => $balance, 'error' => $walletErr->getMessage(),
                ]);
            }
        }

        $sale->update(['status' => 'refunded']);
        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Refund recorded', [
            'sale_id' => $sale->id, 'refund_amount' => $balance,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  WRONG COURSE CORRECTION — revoke old + grant new in UPE
    // ══════════════════════════════════════════════════════════════

    /**
     * Handle wrong course correction with full wallet-mediated purchase flow.
     *
     * Returns array: ['success', 'message', 'access_granted', 'sale_id',
     *                 'amount_refunded', 'amount_charged', 'wallet_balance', 'required_amount']
     *
     * @param string $purchaseType  'full' | 'installment' | 'quick_pay'
     * @param int|null $installmentId  legacy Installment model id (required when purchaseType='installment')
     * @param float $quickPayAmount   amount to charge now (required when purchaseType='quick_pay')
     */
    public function handleWrongCourseCorrection(
        int $userId,
        int $wrongWebinarId,
        int $correctWebinarId,
        int $supportRequestId,
        int $adminId,
        ?int $discountId = null,
        ?string $couponCode = null,
        string $purchaseType = 'full',
        ?int $installmentId = null,
        float $quickPayAmount = 0
    ): array {
        $wrongProduct  = $this->getOrCreateProduct($wrongWebinarId);
        $correctProduct = $this->getOrCreateProduct($correctWebinarId);

        if (!$wrongProduct || !$correctProduct) {
            return $this->wccResult(false, 'Could not resolve course products in UPE. Please check product mapping.', false);
        }

        // ── Step 1: Revoke wrong course UPE sales + compute actual amount paid ──
        // Use legacy Sale SUM as source of truth (immune to upe_ledger sync duplicates).
        // UPE ledger may have duplicate entries from upe:sync-part-payments; legacy Sale has one row per payment.
        $amountPaid = (float) \App\Models\Sale::where('buyer_id', $userId)
            ->where('webinar_id', $wrongWebinarId)
            ->whereNull('refund_at')
            ->whereIn('type', ['webinar', 'installment_payment', 'course_video', 'bundle'])
            ->sum('amount');

        // Fallback to UPE ledger only if no legacy Sale rows exist
        $wrongSales = UpeSale::where('user_id', $userId)
            ->where('product_id', $wrongProduct->id)
            ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
            ->get();

        if ($amountPaid <= 0 && $wrongSales->isNotEmpty()) {
            $wrongSale  = $wrongSales->sortByDesc('id')->first();
            $amountPaid = $this->ledger->actualAmountPaid($wrongSale->id);
        }

        foreach ($wrongSales as $wrongSale) {
            Log::info('SupportUpeBridge: WCC — revoking wrong course sale', [
                'user_id'         => $userId,
                'sale_id'         => $wrongSale->id,
                'amount_refunded' => $amountPaid,
                'wrong_course_id' => $wrongWebinarId,
            ]);
            $wrongSale->update(['status' => 'refunded']);
            $this->access->invalidate($userId, $wrongProduct->id);
        }

        // ── Step 2: Compute correct course price after coupon ──
        $correctCoursePrice = (float) $correctProduct->base_fee;
        $discountAmount = 0;
        if ($discountId && $couponCode) {
            $discountAmount = $this->computeCouponDiscountForWcc(
                $discountId, $couponCode, $correctCoursePrice, $correctWebinarId, $userId
            );
        }
        $finalPrice = max(0, $correctCoursePrice - $discountAmount);

        // ── Step 3: Credit refund amount to wallet ──
        $walletService = app(\App\Services\PaymentEngine\WalletService::class);
        if ($amountPaid > 0) {
            $walletService->creditCourseSwitch(
                $userId,
                $amountPaid,
                $wrongSales->first()?->id,
                "Course correction refund: ₹{$amountPaid} from wrong course #{$wrongWebinarId} (support #{$supportRequestId})"
            );
        }

        Log::info('SupportUpeBridge: WCC — wallet credited, dispatching to purchase handler', [
            'user_id'           => $userId,
            'wrong_course_id'   => $wrongWebinarId,
            'correct_course_id' => $correctWebinarId,
            'amount_refunded'   => $amountPaid,
            'course_price'      => $correctCoursePrice,
            'discount_amount'   => $discountAmount,
            'final_price'       => $finalPrice,
            'purchase_type'     => $purchaseType,
            'support_request_id' => $supportRequestId,
        ]);

        // ── Step 4: Dispatch to purchase type handler ──
        switch ($purchaseType) {
            case 'installment':
                return $this->wccInstallmentPayment(
                    $userId, $correctWebinarId, $correctProduct, $supportRequestId, $adminId,
                    $walletService, $finalPrice, $discountAmount, $discountId, $couponCode,
                    $installmentId, $amountPaid
                );
            case 'quick_pay':
                return $this->wccQuickPayment(
                    $userId, $correctWebinarId, $correctProduct, $supportRequestId, $adminId,
                    $walletService, $finalPrice, $discountAmount, $discountId, $couponCode,
                    $quickPayAmount, $amountPaid
                );
            default:
                return $this->wccFullPayment(
                    $userId, $correctWebinarId, $correctProduct, $supportRequestId, $adminId,
                    $walletService, $finalPrice, $discountAmount, $discountId, $couponCode,
                    $amountPaid
                );
        }
    }

    // ── Private: compute coupon discount for WCC (does NOT depend on Cart context) ──
    private function computeCouponDiscountForWcc(
        int $discountId, string $couponCode, float $coursePrice, int $webinarId, int $userId
    ): float {
        try {
            $discount = \App\Models\Discount::where('id', $discountId)->where('status', 'active')->first();
            if (!$discount) return 0;

            if ($discount->expire_at && now()->timestamp > $discount->expire_at) return 0;
            if ($discount->max_use_id != 0 && $discount->discountRemain() <= 0) return 0;

            if ($discount->source !== 'all') {
                $applies = \App\Models\DiscountCourse::where('discount_id', $discount->id)
                    ->where('course_id', $webinarId)->exists();
                if (!$applies) return 0;
            }

            if ($discount->discount_type == 'fixed_amount') {
                return min((float) $discount->amount, $coursePrice);
            }

            $percent = (float) ($discount->percent ?? 0);
            $calc    = round($coursePrice * $percent / 100, 2);
            if (!empty($discount->max_amount) && $calc > (float) $discount->max_amount) {
                $calc = (float) $discount->max_amount;
            }
            return $calc;
        } catch (\Exception $e) {
            Log::error('SupportUpeBridge: WCC coupon discount calculation failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    // ── Private: Full payment handler ──
    private function wccFullPayment(
        int $userId, int $correctWebinarId, UpeProduct $correctProduct,
        int $supportRequestId, int $adminId,
        $walletService, float $finalPrice, float $discountAmount,
        ?int $discountId, ?string $couponCode, float $amountRefunded
    ): array {
        $currentBalance = $walletService->balance($userId);

        if ($currentBalance < $finalPrice - 1) {
            return $this->wccResult(
                false,
                "Insufficient wallet balance for full payment. " .
                "Available: ₹" . number_format($currentBalance, 0) . ", " .
                "Required: ₹" . number_format($finalPrice, 0) . ". " .
                "Please add funds to the wallet.",
                false,
                null, $amountRefunded, $finalPrice, $currentBalance, $finalPrice
            );
        }

        return DB::transaction(function () use (
            $userId, $correctWebinarId, $correctProduct, $supportRequestId, $adminId,
            $walletService, $finalPrice, $discountAmount, $discountId, $couponCode, $amountRefunded
        ) {
            $validFrom  = now();
            $validUntil = $correctProduct->validity_days
                ? $validFrom->copy()->addDays($correctProduct->validity_days) : null;

            $sale = UpeSale::create([
                'uuid'               => (string) Str::uuid(),
                'user_id'            => $userId,
                'product_id'         => $correctProduct->id,
                'sale_type'          => 'paid',
                'pricing_mode'       => 'full',
                'base_fee_snapshot'  => $correctProduct->base_fee,
                'currency'           => 'INR',
                'status'             => 'active',
                'valid_from'         => $validFrom,
                'valid_until'        => $validUntil,
                'support_request_id' => $supportRequestId,
                'approved_by'        => $adminId,
                'executed_at'        => now(),
                'metadata'           => [
                    'source'             => 'wcc_full_payment',
                    'support_request_id' => $supportRequestId,
                    'discount_amount'    => $discountAmount,
                    'coupon_code'        => $couponCode,
                ],
            ]);

            $walletService->purchaseFromWallet(
                $userId, $finalPrice, null,
                "Wrong course correction — full purchase of course #{$correctWebinarId} (support #{$supportRequestId})" .
                ($discountAmount > 0 ? " — coupon discount: ₹{$discountAmount}" : "")
            );

            $this->ledger->recordPayment(
                saleId: $sale->id,
                amount: $finalPrice,
                paymentMethod: 'wallet',
                processedBy: $adminId,
                description: "Wrong course correction full payment via wallet (support #{$supportRequestId})",
                idempotencyKey: "wcc_full_{$supportRequestId}"
            );

            if ($discountId && $discountAmount > 0) {
                $this->ledger->recordDiscount(
                    saleId: $sale->id,
                    amount: $discountAmount,
                    discountId: $discountId,
                    processedBy: $adminId,
                    description: "Coupon '{$couponCode}' for wrong course correction (support #{$supportRequestId})"
                );
            }

            $this->access->invalidate($userId, $correctProduct->id);

            Log::info('SupportUpeBridge: WCC full payment completed', [
                'sale_id'            => $sale->id,
                'user_id'            => $userId,
                'correct_course_id'  => $correctWebinarId,
                'amount_charged'     => $finalPrice,
                'discount_amount'    => $discountAmount,
                'amount_refunded'    => $amountRefunded,
                'support_request_id' => $supportRequestId,
            ]);

            return $this->wccResult(
                true,
                "Course correction complete. ₹" . number_format($amountRefunded, 0) . " refunded from wrong course. " .
                "₹" . number_format($finalPrice, 0) . " charged for correct course. Access granted.",
                true, $sale->id, $amountRefunded, $finalPrice, $walletService->balance($userId)
            );
        });
    }

    // ── Private: Installment payment handler ──
    private function wccInstallmentPayment(
        int $userId, int $correctWebinarId, UpeProduct $correctProduct,
        int $supportRequestId, int $adminId,
        $walletService, float $finalPrice, float $discountAmount,
        ?int $discountId, ?string $couponCode,
        ?int $installmentId, float $amountRefunded
    ): array {
        if (!$installmentId) {
            return $this->wccResult(false, 'Installment plan ID is required for installment purchase.', false);
        }

        $installment = \App\Models\Installment::find($installmentId);
        if (!$installment || !$installment->enable) {
            return $this->wccResult(false, 'Selected installment plan is invalid or disabled.', false);
        }

        $upfrontAmount = round($installment->getUpfront($finalPrice), 0, PHP_ROUND_HALF_UP);
        $currentBalance = $walletService->balance($userId);

        if ($currentBalance < $upfrontAmount - 1) {
            return $this->wccResult(
                false,
                "Insufficient wallet balance for first installment. " .
                "Available: ₹" . number_format($currentBalance, 0) . ", " .
                "First installment required: ₹" . number_format($upfrontAmount, 0) . ". " .
                "Please add funds to the wallet.",
                false,
                null, $amountRefunded, $upfrontAmount, $currentBalance, $upfrontAmount
            );
        }

        return DB::transaction(function () use (
            $userId, $correctWebinarId, $correctProduct, $supportRequestId, $adminId,
            $walletService, $finalPrice, $discountAmount, $discountId, $couponCode,
            $installment, $installmentId, $upfrontAmount, $amountRefunded
        ) {
            $validFrom  = now();
            $validUntil = $correctProduct->validity_days
                ? $validFrom->copy()->addDays($correctProduct->validity_days) : null;

            $sale = UpeSale::create([
                'uuid'               => (string) Str::uuid(),
                'user_id'            => $userId,
                'product_id'         => $correctProduct->id,
                'sale_type'          => 'paid',
                'pricing_mode'       => 'installment',
                'base_fee_snapshot'  => $correctProduct->base_fee,
                'currency'           => 'INR',
                'status'             => 'active',
                'valid_from'         => $validFrom,
                'valid_until'        => $validUntil,
                'support_request_id' => $supportRequestId,
                'approved_by'        => $adminId,
                'executed_at'        => now(),
                'metadata'           => [
                    'source'             => 'wcc_installment_payment',
                    'support_request_id' => $supportRequestId,
                    'installment_id'     => $installmentId,
                    'coupon_code'        => $couponCode,
                ],
            ]);

            // Build and create UPE installment plan + schedules from legacy Installment config
            $scheduleAmounts   = [];
            $scheduleAmounts[] = ['amount' => $upfrontAmount, 'deadline_days' => 0];

            $steps = $installment->steps()->orderBy('order')->get();
            foreach ($steps as $step) {
                $scheduleAmounts[] = [
                    'amount'        => round($step->getPrice($finalPrice), 0, PHP_ROUND_HALF_UP),
                    'deadline_days' => (int) $step->deadline,
                ];
            }

            $totalAmount = round(array_sum(array_column($scheduleAmounts, 'amount')), 2);

            $plan = UpeInstallmentPlan::create([
                'sale_id'           => $sale->id,
                'total_amount'      => $totalAmount,
                'num_installments'  => count($scheduleAmounts),
                'plan_type'         => 'standard',
                'status'            => 'active',
            ]);

            foreach ($scheduleAmounts as $i => $sched) {
                UpeInstallmentSchedule::create([
                    'plan_id'      => $plan->id,
                    'sequence'     => $i + 1,
                    'due_date'     => now()->addDays($sched['deadline_days']),
                    'amount_due'   => $sched['amount'],
                    'amount_paid'  => 0,
                    'status'       => ($i === 0) ? 'due' : 'upcoming',
                ]);
            }

            // Debit upfront from wallet + record in installment engine
            $walletService->purchaseFromWallet(
                $userId, $upfrontAmount, null,
                "Wrong course correction — installment upfront for course #{$correctWebinarId} (support #{$supportRequestId})"
            );

            $engine = app(InstallmentEngine::class);
            $engine->recordPayment($plan, $upfrontAmount, 'wallet', null, null, $adminId);

            if ($discountId && $discountAmount > 0) {
                $this->ledger->recordDiscount(
                    saleId: $sale->id,
                    amount: $discountAmount,
                    discountId: $discountId,
                    processedBy: $adminId,
                    description: "Coupon '{$couponCode}' for wrong course correction installment (support #{$supportRequestId})"
                );
            }

            $this->access->invalidate($userId, $correctProduct->id);

            Log::info('SupportUpeBridge: WCC installment payment completed', [
                'sale_id'            => $sale->id,
                'plan_id'            => $plan->id,
                'user_id'            => $userId,
                'correct_course_id'  => $correctWebinarId,
                'upfront_charged'    => $upfrontAmount,
                'total_plan_amount'  => $totalAmount,
                'amount_refunded'    => $amountRefunded,
                'support_request_id' => $supportRequestId,
            ]);

            return $this->wccResult(
                true,
                "Course correction complete (installment). ₹" . number_format($amountRefunded, 0) . " refunded from wrong course. " .
                "First installment of ₹" . number_format($upfrontAmount, 0) . " charged. Access granted. " .
                "Remaining " . ($plan->num_installments - 1) . " EMI(s) due as per schedule.",
                true, $sale->id, $amountRefunded, $upfrontAmount, $walletService->balance($userId)
            );
        });
    }

    // ── Private: Quick Pay handler ──
    private function wccQuickPayment(
        int $userId, int $correctWebinarId, UpeProduct $correctProduct,
        int $supportRequestId, int $adminId,
        $walletService, float $finalPrice, float $discountAmount,
        ?int $discountId, ?string $couponCode,
        float $quickPayAmount, float $amountRefunded
    ): array {
        if ($quickPayAmount <= 0) {
            return $this->wccResult(false, 'Quick pay amount must be greater than zero.', false);
        }

        $currentBalance = $walletService->balance($userId);

        if ($currentBalance < $quickPayAmount - 1) {
            return $this->wccResult(
                false,
                "Insufficient wallet balance for quick pay. " .
                "Available: ₹" . number_format($currentBalance, 0) . ", " .
                "Requested: ₹" . number_format($quickPayAmount, 0) . ". " .
                "Please add funds to the wallet.",
                false,
                null, $amountRefunded, $quickPayAmount, $currentBalance, $quickPayAmount
            );
        }

        return DB::transaction(function () use (
            $userId, $correctWebinarId, $correctProduct, $supportRequestId, $adminId,
            $walletService, $finalPrice, $discountAmount, $discountId, $couponCode,
            $quickPayAmount, $amountRefunded
        ) {
            $validFrom  = now();
            $validUntil = $correctProduct->validity_days
                ? $validFrom->copy()->addDays($correctProduct->validity_days) : null;

            $sale = UpeSale::create([
                'uuid'               => (string) Str::uuid(),
                'user_id'            => $userId,
                'product_id'         => $correctProduct->id,
                'sale_type'          => 'paid',
                'pricing_mode'       => 'full',
                'base_fee_snapshot'  => $correctProduct->base_fee,
                'currency'           => 'INR',
                'status'             => 'active',
                'valid_from'         => $validFrom,
                'valid_until'        => $validUntil,
                'support_request_id' => $supportRequestId,
                'approved_by'        => $adminId,
                'executed_at'        => now(),
                'metadata'           => [
                    'source'             => 'wcc_quick_pay',
                    'support_request_id' => $supportRequestId,
                    'quick_pay_amount'   => $quickPayAmount,
                    'full_price'         => $finalPrice,
                    'discount_amount'    => $discountAmount,
                    'coupon_code'        => $couponCode,
                ],
            ]);

            $walletService->purchaseFromWallet(
                $userId, $quickPayAmount, null,
                "Wrong course correction — quick pay ₹{$quickPayAmount} for course #{$correctWebinarId} (support #{$supportRequestId})"
            );

            $this->ledger->recordPayment(
                saleId: $sale->id,
                amount: $quickPayAmount,
                paymentMethod: 'wallet',
                processedBy: $adminId,
                description: "Wrong course correction quick pay ₹{$quickPayAmount} (full price ₹{$finalPrice}) (support #{$supportRequestId})",
                idempotencyKey: "wcc_quick_{$supportRequestId}"
            );

            if ($discountId && $discountAmount > 0) {
                $this->ledger->recordDiscount(
                    saleId: $sale->id,
                    amount: $discountAmount,
                    discountId: $discountId,
                    processedBy: $adminId,
                    description: "Coupon '{$couponCode}' for wrong course correction quick pay (support #{$supportRequestId})"
                );
            }

            $this->access->invalidate($userId, $correctProduct->id);

            $remaining = max(0, $finalPrice - $quickPayAmount);

            Log::info('SupportUpeBridge: WCC quick pay completed', [
                'sale_id'            => $sale->id,
                'user_id'            => $userId,
                'correct_course_id'  => $correctWebinarId,
                'quick_pay_amount'   => $quickPayAmount,
                'remaining_amount'   => $remaining,
                'amount_refunded'    => $amountRefunded,
                'support_request_id' => $supportRequestId,
            ]);

            $msg = "Course correction complete (quick pay). ₹" . number_format($amountRefunded, 0) . " refunded from wrong course. " .
                "₹" . number_format($quickPayAmount, 0) . " charged now. Access granted.";
            if ($remaining > 0.01) {
                $msg .= " Remaining balance of ₹" . number_format($remaining, 0) . " can be paid via normal payment flow.";
            }

            return $this->wccResult(true, $msg, true, $sale->id, $amountRefunded, $quickPayAmount, $walletService->balance($userId));
        });
    }

    // ── Private: Standardized result array for WCC ──
    private function wccResult(
        bool $success,
        string $message,
        bool $accessGranted,
        ?int $saleId = null,
        float $amountRefunded = 0,
        float $amountCharged = 0,
        float $walletBalance = 0,
        float $requiredAmount = 0
    ): array {
        return [
            'success'         => $success,
            'message'         => $message,
            'access_granted'  => $accessGranted,
            'sale_id'         => $saleId,
            'amount_refunded' => $amountRefunded,
            'amount_charged'  => $amountCharged,
            'wallet_balance'  => $walletBalance,
            'required_amount' => $requiredAmount,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  FREE COURSE GRANT — batch create UPE sales
    // ══════════════════════════════════════════════════════════════

    public function grantFreeCourseAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        return $this->grantRelativeAccess($userId, $webinarId, $supportRequestId, $adminId);
    }

    // ══════════════════════════════════════════════════════════════
    //  POST-PURCHASE COUPON — record discount in UPE ledger
    // ══════════════════════════════════════════════════════════════

    public function recordCouponDiscount(int $userId, int $webinarId, int $supportRequestId, int $adminId, float $discountAmount, string $couponCode, int $discountId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $sale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if (!$sale) {
            Log::warning('SupportUpeBridge: No UPE sale found for coupon discount', [
                'user_id' => $userId, 'product_id' => $product->id,
            ]);
            return null;
        }

        // Idempotency: check for existing ledger entry
        $idempotencyKey = "admin_coupon_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();

        if (!$existingEntry) {
            $this->ledger->recordDiscount(
                saleId: $sale->id,
                amount: $discountAmount,
                discountId: $discountId,
                processedBy: $adminId,
                description: "Post-purchase coupon '{$couponCode}' via support #{$supportRequestId}"
            );
        }

        Log::info('SupportUpeBridge: Coupon discount recorded', [
            'sale_id' => $sale->id, 'discount_amount' => $discountAmount, 'coupon_code' => $couponCode,
        ]);

        return $sale;
    }
}
