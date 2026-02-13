<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeDiscount;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Support\Facades\DB;

class DiscountEngine
{
    private PaymentLedgerService $ledger;
    private AuditService $audit;

    public function __construct(PaymentLedgerService $ledger, AuditService $audit)
    {
        $this->ledger = $ledger;
        $this->audit = $audit;
    }

    /**
     * Validate a coupon code against a user and product.
     *
     * @return array ['valid' => bool, 'discount' => ?UpeDiscount, 'amount' => float, 'reason' => ?string]
     */
    public function validate(string $code, int $userId, int $productId, float $baseAmount): array
    {
        $discount = UpeDiscount::byCode($code)->first();

        if (!$discount) {
            return ['valid' => false, 'discount' => null, 'amount' => 0, 'reason' => 'Coupon not found.'];
        }

        if (!$discount->isActive()) {
            return ['valid' => false, 'discount' => $discount, 'amount' => 0, 'reason' => 'Coupon is inactive or expired.'];
        }

        if ($discount->isExpired()) {
            return ['valid' => false, 'discount' => $discount, 'amount' => 0, 'reason' => 'Coupon has expired.'];
        }

        if (!$discount->hasUsesRemaining()) {
            return ['valid' => false, 'discount' => $discount, 'amount' => 0, 'reason' => 'Coupon usage limit reached.'];
        }

        if (!$discount->appliesToProduct($productId)) {
            return ['valid' => false, 'discount' => $discount, 'amount' => 0, 'reason' => 'Coupon does not apply to this product.'];
        }

        if ($discount->min_order_amount !== null && $baseAmount < (float) $discount->min_order_amount) {
            return [
                'valid' => false,
                'discount' => $discount,
                'amount' => 0,
                'reason' => "Minimum order amount is {$discount->min_order_amount}.",
            ];
        }

        // Per-user usage check
        if ($discount->max_uses_per_user !== null) {
            $userUses = UpeLedgerEntry::where('reference_type', 'discount')
                ->where('reference_id', $discount->id)
                ->whereHas('sale', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->count();

            if ($userUses >= $discount->max_uses_per_user) {
                return ['valid' => false, 'discount' => $discount, 'amount' => 0, 'reason' => 'You have already used this coupon the maximum number of times.'];
            }
        }

        $discountAmount = $discount->calculateDiscount($baseAmount);

        return [
            'valid' => true,
            'discount' => $discount,
            'amount' => $discountAmount,
            'reason' => null,
        ];
    }

    /**
     * Apply a discount to a sale. Creates an immutable ledger entry.
     *
     * @throws \RuntimeException if discount is invalid or stacking violated
     */
    public function apply(UpeSale $sale, UpeDiscount $discount, ?int $processedBy = null): UpeLedgerEntry
    {
        // Re-validate at application time (time-bound auto-expiry check)
        if (!$discount->isActive()) {
            throw new \RuntimeException('Discount is no longer active.');
        }

        if (!$discount->hasUsesRemaining()) {
            throw new \RuntimeException('Discount usage limit reached.');
        }

        // Stacking check
        if (!$this->checkStacking($sale, $discount)) {
            throw new \RuntimeException('This discount cannot be combined with existing discounts on this sale.');
        }

        $discountAmount = $discount->calculateDiscount((float) $sale->base_fee_snapshot);

        if ($discountAmount <= 0) {
            throw new \RuntimeException('Discount amount resolves to zero.');
        }

        // Ensure total discounts don't exceed base fee
        $existingDiscounts = UpeLedgerEntry::forSale($sale->id)
            ->ofType(UpeLedgerEntry::TYPE_DISCOUNT)
            ->sum('amount');

        $maxAllowable = (float) $sale->base_fee_snapshot - (float) $existingDiscounts;
        if ($discountAmount > $maxAllowable) {
            $discountAmount = $maxAllowable;
        }

        if ($discountAmount <= 0) {
            throw new \RuntimeException('Sale already has maximum discount applied.');
        }

        $entry = $this->ledger->recordDiscount(
            saleId: $sale->id,
            amount: $discountAmount,
            discountId: $discount->id,
            processedBy: $processedBy,
            description: $discount->code
                ? "Coupon {$discount->code}: -{$discountAmount}"
                : "Discount applied: -{$discountAmount}"
        );

        // NOTE: used_count is NOT incremented in-place.
        // Usage is derived from ledger: COUNT(ledger entries WHERE reference_type='discount' AND reference_id=discount.id)

        // Audit
        if ($processedBy) {
            $this->audit->logDiscountApplied($processedBy, 'system', $sale->id, [
                'discount_id' => $discount->id,
                'code' => $discount->code,
                'type' => $discount->discount_type,
                'value' => $discount->value,
                'applied_amount' => $discountAmount,
            ]);
        }

        return $entry;
    }

    /**
     * Check if a new discount can be stacked on an existing sale.
     */
    public function checkStacking(UpeSale $sale, UpeDiscount $newDiscount): bool
    {
        if ($newDiscount->stackable) {
            return true;
        }

        // Check if sale already has a non-stackable discount
        $existingNonStackable = UpeLedgerEntry::forSale($sale->id)
            ->ofType(UpeLedgerEntry::TYPE_DISCOUNT)
            ->where('reference_type', 'discount')
            ->get()
            ->filter(function ($entry) {
                $d = UpeDiscount::find($entry->reference_id);
                return $d && !$d->stackable;
            });

        return $existingNonStackable->isEmpty();
    }

    /**
     * Check role-based discount caps.
     *
     * @param string $userRole  The role of the user applying the discount
     * @param float  $amount    The discount amount
     * @param float  $basePrice The product base price
     * @return bool
     */
    public function checkRoleCaps(string $userRole, float $amount, float $basePrice): bool
    {
        $maxPercents = [
            'user' => 0,        // Users cannot create manual discounts
            'support' => 20,    // Support can apply up to 20%
            'admin' => 100,     // Admin has no cap
        ];

        $maxPercent = $maxPercents[$userRole] ?? 0;
        $actualPercent = ($basePrice > 0) ? ($amount / $basePrice) * 100 : 0;

        return $actualPercent <= $maxPercent;
    }

    /**
     * Calculate effective price after all discounts for a sale.
     */
    public function effectivePrice(UpeSale $sale): float
    {
        $totalDiscount = UpeLedgerEntry::forSale($sale->id)
            ->ofType(UpeLedgerEntry::TYPE_DISCOUNT)
            ->sum('amount');

        return max(0, round((float) $sale->base_fee_snapshot - (float) $totalDiscount, 2));
    }
}
