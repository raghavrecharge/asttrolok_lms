<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeSupportAction;
use App\Models\PaymentEngine\UpeMentorBadge;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\Webinar;
use Illuminate\Support\Collection;

/**
 * Central eligibility resolver for all 8 support action scenarios.
 *
 * Each method returns a SupportEligibility DTO with:
 *   - eligible (bool)
 *   - reason (string)
 *   - context (array) — data needed by the UI/executor
 *
 * Visibility rule: a UI option is rendered ONLY when eligible === true.
 */
class SupportEligibilityResolver
{
    private PaymentLedgerService $ledger;
    private AccessEngine $access;

    public function __construct(PaymentLedgerService $ledger, AccessEngine $access)
    {
        $this->ledger = $ledger;
        $this->access = $access;
    }

    // ═══════════════════════════════════════════════════════════════
    //  1️⃣  Course Extension
    // ═══════════════════════════════════════════════════════════════

    public function canExtendCourse(int $userId, int $productId, ?int $excludeActionId = null): SupportEligibility
    {
        $sale = $this->findLatestSale($userId, $productId);

        if (!$sale) {
            return SupportEligibility::ineligible('Course was never purchased.');
        }

        // Must be expired — active courses cannot be extended
        if ($sale->hasValidAccess()) {
            return SupportEligibility::ineligible('Course access is currently active. Extension is not applicable.');
        }

        // Must have been active at some point (not cancelled before use)
        if (in_array($sale->status, ['cancelled', 'refunded'])) {
            return SupportEligibility::ineligible("Sale status '{$sale->status}' is not eligible for extension.");
        }

        // Check validity has actually expired
        if ($sale->valid_until === null) {
            return SupportEligibility::ineligible('Course has unlimited validity. Extension not applicable.');
        }

        if (!$sale->valid_until->isPast()) {
            return SupportEligibility::ineligible('Course validity has not expired yet.');
        }

        // Check for duplicate pending extension
        if ($this->hasPendingAction($userId, $productId, UpeSupportAction::ACTION_EXTENSION, $excludeActionId)) {
            return SupportEligibility::ineligible('An extension request is already pending.');
        }

        return SupportEligibility::eligible('Course is expired. Extension is available.', [
            'sale_id'      => $sale->id,
            'expired_at'   => $sale->valid_until->toDateTimeString(),
            'days_expired'  => (int) $sale->valid_until->diffInDays(now()),
            'original_validity_days' => $sale->product?->validity_days,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  2️⃣  Temporary Access (Pending Payment)
    // ═══════════════════════════════════════════════════════════════

    public function canGrantTemporaryAccess(int $userId, int $productId, ?int $excludeActionId = null): SupportEligibility
    {
        $sale = $this->findLatestSale($userId, $productId);

        if (!$sale) {
            return SupportEligibility::ineligible('Course not purchased.');
        }

        // Must have outstanding due
        $balance = $this->ledger->balance($sale->id);
        $baseFee = (float) $sale->base_fee_snapshot;

        // For installment mode: check if there are unpaid schedules
        if ($sale->pricing_mode === 'installment') {
            $plan = $sale->installmentPlan;
            if (!$plan) {
                return SupportEligibility::ineligible('Installment plan not found.');
            }
            $unpaidSchedules = $plan->schedules()->whereIn('status', ['due', 'upcoming', 'overdue', 'partial'])->count();
            if ($unpaidSchedules === 0) {
                return SupportEligibility::ineligible('All installments are paid. No outstanding dues.');
            }
            $outstanding = $plan->total_amount - $plan->totalPaid();
        } else {
            // For full payment mode: base_fee minus balance
            if ($baseFee <= 0) {
                return SupportEligibility::ineligible('Course is free. No outstanding dues.');
            }
            $outstanding = $baseFee - $balance;
            if ($outstanding <= 0) {
                return SupportEligibility::ineligible('Course is fully paid. No outstanding dues.');
            }
        }

        // Must not already have active temporary access
        $activeTemp = UpeSupportAction::where('user_id', $userId)
            ->where('product_id', $productId)
            ->temporaryAccessActive()
            ->exists();

        if ($activeTemp) {
            return SupportEligibility::ineligible('Temporary access is already active.');
        }

        // Must not have active regular access
        $accessResult = $this->access->computeAccess($userId, $productId);
        if ($accessResult->hasAccess) {
            return SupportEligibility::ineligible('User already has active access.');
        }

        return SupportEligibility::eligible('Outstanding dues exist. Temporary access available.', [
            'sale_id'     => $sale->id,
            'outstanding' => round($outstanding, 2),
            'balance'     => round($balance, 2),
            'base_fee'    => $baseFee,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  3️⃣  Mentor Access
    // ═══════════════════════════════════════════════════════════════

    public function canGrantMentorAccess(int $userId, ?int $productId = null): SupportEligibility
    {
        if (!UpeMentorBadge::hasBadge($userId)) {
            return SupportEligibility::ineligible('User does not have a Mentor Badge.');
        }

        // Mentor can access ANY course — no product-specific restriction
        // If product specified, just verify it exists
        if ($productId !== null) {
            $product = UpeProduct::find($productId);
            if (!$product || $product->status !== 'active') {
                return SupportEligibility::ineligible('Product not found or inactive.');
            }
        }

        return SupportEligibility::eligible('Mentor Badge active. Access available for any course.', [
            'badge_status' => 'active',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  4️⃣  Relative / Friends Access
    // ═══════════════════════════════════════════════════════════════

    public function canGrantRelativeAccess(int $payerUserId, int $beneficiaryUserId, int $productId, ?int $excludeActionId = null): SupportEligibility
    {
        if ($payerUserId === $beneficiaryUserId) {
            return SupportEligibility::ineligible('Payer and beneficiary cannot be the same person.');
        }

        $product = UpeProduct::find($productId);
        if (!$product || $product->status !== 'active') {
            return SupportEligibility::ineligible('Product not found or inactive.');
        }

        // Beneficiary must NOT already have this course
        $beneficiarySale = UpeSale::where('user_id', $beneficiaryUserId)
            ->where('product_id', $productId)
            ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
            ->first();

        if ($beneficiarySale) {
            return SupportEligibility::ineligible('Beneficiary already has access to this course.');
        }

        // Check for duplicate pending action
        $pending = UpeSupportAction::where('user_id', $payerUserId)
            ->where('beneficiary_id', $beneficiaryUserId)
            ->where('product_id', $productId)
            ->where('action_type', UpeSupportAction::ACTION_RELATIVE_ACCESS)
            ->whereIn('status', [UpeSupportAction::STATUS_PENDING, UpeSupportAction::STATUS_APPROVED])
            ->when($excludeActionId, fn($q) => $q->where('id', '!=', $excludeActionId))
            ->exists();

        if ($pending) {
            return SupportEligibility::ineligible('A relative access request is already pending for this combination.');
        }

        return SupportEligibility::eligible('Relative access eligible.', [
            'product_name' => $product->name,
            'base_fee'     => (float) $product->base_fee,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  5️⃣  Offline / Cash Payment
    // ═══════════════════════════════════════════════════════════════

    public function canRecordOfflinePayment(int $userId, int $productId): SupportEligibility
    {
        $product = UpeProduct::find($productId);
        if (!$product || $product->status !== 'active') {
            return SupportEligibility::ineligible('Product not found or inactive.');
        }

        $sale = $this->findLatestSale($userId, $productId);

        $context = [
            'product_name' => $product->name,
            'base_fee'     => (float) $product->base_fee,
            'tab'          => 'not_purchased', // default
        ];

        if ($sale) {
            $balance = $this->ledger->balance($sale->id);
            $outstanding = (float) $sale->base_fee_snapshot - $balance;
            $context['tab']         = 'purchased';
            $context['sale_id']     = $sale->id;
            $context['balance']     = round($balance, 2);
            $context['outstanding'] = round(max(0, $outstanding), 2);
            $context['sale_status'] = $sale->status;
        }

        // Always eligible — admin decides amount and which tab
        return SupportEligibility::eligible('Offline payment can be recorded.', $context);
    }

    // ═══════════════════════════════════════════════════════════════
    //  6️⃣  Refund Payment
    // ═══════════════════════════════════════════════════════════════

    public function canRefund(int $userId, int $productId, ?int $excludeActionId = null): SupportEligibility
    {
        $sale = $this->findLatestSale($userId, $productId);

        if (!$sale) {
            return SupportEligibility::ineligible('No purchase found.');
        }

        if ($sale->status === 'refunded') {
            return SupportEligibility::ineligible('Sale is already fully refunded.');
        }

        if (!in_array($sale->status, ['active', 'partially_refunded'])) {
            return SupportEligibility::ineligible("Sale status '{$sale->status}' is not eligible for refund.");
        }

        // Check refund eligibility window from product/webinar metadata
        $refundDeadline = $this->getRefundDeadline($sale);

        if ($refundDeadline !== null && now()->gt($refundDeadline)) {
            return SupportEligibility::ineligible(
                'Refund window expired on ' . $refundDeadline->toDateString() . '.'
            );
        }

        $balance = $this->ledger->balance($sale->id);
        $totalRefunded = $this->ledger->totalRefunded($sale->id);
        $refundable = max(0, $balance);

        if ($refundable <= 0) {
            return SupportEligibility::ineligible('No refundable balance available.');
        }

        // Check for pending refund
        if ($this->hasPendingAction($userId, $productId, UpeSupportAction::ACTION_REFUND, $excludeActionId)) {
            return SupportEligibility::ineligible('A refund request is already pending.');
        }

        return SupportEligibility::eligible('Refund eligible.', [
            'sale_id'          => $sale->id,
            'balance'          => round($balance, 2),
            'already_refunded' => round($totalRefunded, 2),
            'max_refundable'   => round($refundable, 2),
            'refund_deadline'  => $refundDeadline?->toDateTimeString(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  7️⃣  Wrong Payment / Payment Migration
    // ═══════════════════════════════════════════════════════════════

    public function canMigratePayment(int $userId, int $sourceProductId, int $targetProductId, ?int $excludeActionId = null): SupportEligibility
    {
        if ($sourceProductId === $targetProductId) {
            return SupportEligibility::ineligible('Source and target courses cannot be the same.');
        }

        $sourceSale = $this->findLatestSale($userId, $sourceProductId);
        if (!$sourceSale) {
            return SupportEligibility::ineligible('No payment found for source course.');
        }

        // Source must have positive balance
        $sourceBalance = $this->ledger->balance($sourceSale->id);
        if ($sourceBalance <= 0) {
            return SupportEligibility::ineligible('Source course has no transferable balance.');
        }

        // Cannot migrate if refund deadline on source has passed
        $refundDeadline = $this->getRefundDeadline($sourceSale);
        if ($refundDeadline !== null && now()->gt($refundDeadline)) {
            return SupportEligibility::ineligible('Refund/migration window for source course has expired.');
        }

        $targetProduct = UpeProduct::find($targetProductId);
        if (!$targetProduct || $targetProduct->status !== 'active') {
            return SupportEligibility::ineligible('Target product not found or inactive.');
        }

        // Check if user already has target course
        $existingTarget = UpeSale::where('user_id', $userId)
            ->where('product_id', $targetProductId)
            ->whereIn('status', ['active', 'partially_refunded'])
            ->first();

        // Check for pending migration
        $pending = UpeSupportAction::where('user_id', $userId)
            ->where('source_product_id', $sourceProductId)
            ->where('product_id', $targetProductId)
            ->where('action_type', UpeSupportAction::ACTION_PAYMENT_MIGRATION)
            ->whereIn('status', [UpeSupportAction::STATUS_PENDING, UpeSupportAction::STATUS_APPROVED])
            ->when($excludeActionId, fn($q) => $q->where('id', '!=', $excludeActionId))
            ->exists();

        if ($pending) {
            return SupportEligibility::ineligible('A migration request is already pending for this combination.');
        }

        return SupportEligibility::eligible('Payment migration eligible.', [
            'source_sale_id'    => $sourceSale->id,
            'source_balance'    => round($sourceBalance, 2),
            'target_product_id' => $targetProductId,
            'target_base_fee'   => (float) $targetProduct->base_fee,
            'has_target_sale'   => (bool) $existingTarget,
            'max_transferable'  => round($sourceBalance, 2),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  8️⃣  Post-Purchase Coupon Apply
    // ═══════════════════════════════════════════════════════════════

    public function canApplyCoupon(int $userId, int $productId, ?string $couponCode = null): SupportEligibility
    {
        $sale = $this->findLatestSale($userId, $productId);

        if (!$sale) {
            return SupportEligibility::ineligible('Course not purchased.');
        }

        // Must be active (not expired, not upcoming)
        if (!in_array($sale->status, ['active', 'partially_refunded'])) {
            return SupportEligibility::ineligible("Sale status '{$sale->status}' does not allow coupon application.");
        }

        // Validity must not be expired
        if ($sale->valid_until !== null && $sale->valid_until->isPast()) {
            return SupportEligibility::ineligible('Course access has expired. Coupon cannot be applied.');
        }

        // Course must be ongoing (valid_from must be in the past or present)
        if ($sale->valid_from !== null && $sale->valid_from->isFuture()) {
            return SupportEligibility::ineligible('Course has not started yet. Coupon cannot be applied.');
        }

        // Check outstanding balance exists (coupon only reduces payable, not refunds)
        $balance = $this->ledger->balance($sale->id);
        $baseFee = (float) $sale->base_fee_snapshot;
        $outstanding = $baseFee - $balance;

        if ($outstanding <= 0) {
            return SupportEligibility::ineligible('Course is fully paid. No outstanding amount to apply coupon against.');
        }

        // Check for prior coupon application on this sale
        $priorCoupon = UpeSupportAction::where('source_sale_id', $sale->id)
            ->where('action_type', UpeSupportAction::ACTION_COUPON_APPLY)
            ->where('status', UpeSupportAction::STATUS_EXECUTED)
            ->first();

        $context = [
            'sale_id'     => $sale->id,
            'base_fee'    => $baseFee,
            'balance'     => round($balance, 2),
            'outstanding' => round(max(0, $outstanding), 2),
            'prior_coupon_applied' => (bool) $priorCoupon,
        ];

        // If coupon code provided, validate it
        if ($couponCode !== null) {
            $couponResult = $this->validateCoupon($couponCode, $sale, $userId);
            if (!$couponResult['valid']) {
                return SupportEligibility::ineligible($couponResult['reason']);
            }
            $context['coupon_discount'] = $couponResult['discount_amount'];
            $context['coupon_id']       = $couponResult['discount_id'];
        }

        return SupportEligibility::eligible('Coupon can be applied.', $context);
    }

    // ═══════════════════════════════════════════════════════════════
    //  VISIBILITY MATRIX — resolve all visible actions for a user+product
    // ═══════════════════════════════════════════════════════════════

    /**
     * Returns a map of action_type => SupportEligibility for a given user+product.
     * Only eligible actions should be shown in the UI.
     */
    public function resolveVisibleActions(int $userId, int $productId): array
    {
        return [
            UpeSupportAction::ACTION_EXTENSION        => $this->canExtendCourse($userId, $productId),
            UpeSupportAction::ACTION_TEMPORARY_ACCESS  => $this->canGrantTemporaryAccess($userId, $productId),
            UpeSupportAction::ACTION_MENTOR_ACCESS     => $this->canGrantMentorAccess($userId, $productId),
            UpeSupportAction::ACTION_OFFLINE_PAYMENT   => $this->canRecordOfflinePayment($userId, $productId),
            UpeSupportAction::ACTION_REFUND            => $this->canRefund($userId, $productId),
            UpeSupportAction::ACTION_COUPON_APPLY      => $this->canApplyCoupon($userId, $productId),
            // relative_access and payment_migration require additional params → not in single-product matrix
        ];
    }

    /**
     * Full visibility matrix for admin: all products a user has interacted with.
     */
    public function resolveUserMatrix(int $userId): array
    {
        $productIds = UpeSale::where('user_id', $userId)->distinct()->pluck('product_id')->toArray();
        $matrix = [];
        foreach ($productIds as $pid) {
            $actions = $this->resolveVisibleActions($userId, $pid);
            $visible = array_filter($actions, fn(SupportEligibility $e) => $e->eligible);
            if (!empty($visible)) {
                $product = UpeProduct::find($pid);
                $matrix[] = [
                    'product_id'   => $pid,
                    'product_name' => $product?->name,
                    'actions'      => array_map(fn(SupportEligibility $e) => $e->toArray(), $visible),
                ];
            }
        }
        return $matrix;
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function findLatestSale(int $userId, int $productId): ?UpeSale
    {
        return UpeSale::where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->first();
    }

    private function hasPendingAction(int $userId, int $productId, string $actionType, ?int $excludeActionId = null): bool
    {
        return UpeSupportAction::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('action_type', $actionType)
            ->whereIn('status', [UpeSupportAction::STATUS_PENDING, UpeSupportAction::STATUS_APPROVED])
            ->when($excludeActionId, fn($q) => $q->where('id', '!=', $excludeActionId))
            ->exists();
    }

    /**
     * Derive refund deadline from product/webinar metadata.
     * Returns null if no deadline is set (always refundable by policy).
     */
    private function getRefundDeadline(UpeSale $sale): ?\Carbon\Carbon
    {
        // Check metadata first
        $meta = $sale->metadata ?? [];
        if (!empty($meta['refund_deadline'])) {
            return \Carbon\Carbon::parse($meta['refund_deadline']);
        }

        // Derive from product's linked webinar
        $product = $sale->product;
        if ($product && $product->external_id) {
            $webinar = Webinar::find($product->external_id);
            if ($webinar) {
                // Use webinar's refund_days if defined
                $refundDays = $webinar->refund_days ?? null;
                if ($refundDays !== null && $sale->valid_from) {
                    return $sale->valid_from->copy()->addDays($refundDays);
                }
            }
        }

        return null; // No deadline set
    }

    /**
     * Validate a coupon code against a sale.
     * Returns ['valid' => bool, 'reason' => string, 'discount_amount' => float, 'discount_id' => int|null]
     */
    private function validateCoupon(string $couponCode, UpeSale $sale, int $userId): array
    {
        $discount = \App\Models\PaymentEngine\UpeDiscount::where('code', $couponCode)
            ->where('status', 'active')
            ->first();

        if (!$discount) {
            return ['valid' => false, 'reason' => 'Invalid or expired coupon code.', 'discount_amount' => 0, 'discount_id' => null];
        }

        // Check coupon usage limits
        if (!$discount->isActive()) {
            return ['valid' => false, 'reason' => 'Coupon is expired or inactive.', 'discount_amount' => 0, 'discount_id' => null];
        }

        if (!$discount->hasUsesRemaining()) {
            return ['valid' => false, 'reason' => 'Coupon usage limit reached.', 'discount_amount' => 0, 'discount_id' => null];
        }

        // Check per-user limit
        if ($discount->max_uses_per_user !== null) {
            $userUsed = UpeLedgerEntry::where('reference_type', 'discount')
                ->where('reference_id', $discount->id)
                ->whereHas('sale', fn($q) => $q->where('user_id', $userId))
                ->count();
            if ($userUsed >= $discount->max_uses_per_user) {
                return ['valid' => false, 'reason' => 'Coupon already used by this user.', 'discount_amount' => 0, 'discount_id' => null];
            }
        }

        // Check product scope
        if (!$discount->appliesToProduct($sale->product_id)) {
            return ['valid' => false, 'reason' => 'Coupon does not apply to this course.', 'discount_amount' => 0, 'discount_id' => null];
        }

        // Calculate discount amount
        $outstanding = (float) $sale->base_fee_snapshot - $this->ledger->balance($sale->id);
        $discountAmount = $discount->calculateDiscount($outstanding);

        return [
            'valid'           => true,
            'reason'          => 'Coupon valid.',
            'discount_amount' => max(0, $discountAmount),
            'discount_id'     => $discount->id,
        ];
    }
}

// ─────────────────────────────────────────────────────────────────
//  Value Object: SupportEligibility
// ─────────────────────────────────────────────────────────────────

class SupportEligibility
{
    public bool $eligible;
    public string $reason;
    public array $context;

    private function __construct(bool $eligible, string $reason, array $context = [])
    {
        $this->eligible = $eligible;
        $this->reason   = $reason;
        $this->context  = $context;
    }

    public static function eligible(string $reason, array $context = []): self
    {
        return new self(true, $reason, $context);
    }

    public static function ineligible(string $reason): self
    {
        return new self(false, $reason);
    }

    public function toArray(): array
    {
        return [
            'eligible' => $this->eligible,
            'reason'   => $this->reason,
            'context'  => $this->context,
        ];
    }
}
