<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeDiscount;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeReferral;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseEngine
{
    private PaymentLedgerService $ledger;
    private DiscountEngine $discountEngine;
    private AuditService $audit;

    public function __construct(
        PaymentLedgerService $ledger,
        DiscountEngine $discountEngine,
        AuditService $audit
    ) {
        $this->ledger = $ledger;
        $this->discountEngine = $discountEngine;
        $this->audit = $audit;
    }

    /**
     * Create a new Sale (commercial contract).
     * Does NOT process payment — only creates the intent.
     *
     * @param int         $userId       Buyer
     * @param UpeProduct  $product      Product to purchase
     * @param string      $pricingMode  full|installment|subscription|free
     * @param string      $saleType     paid|free|trial|referral|upgrade|adjustment
     * @param string|null $discountCode Optional coupon code
     * @param string|null $referralCode Optional referral code
     * @param int|null    $parentSaleId For upgrades/adjustments
     * @param int|null    $approvedBy   Admin who approved (for admin-initiated sales)
     * @return UpeSale
     */
    public function createSale(
        int $userId,
        UpeProduct $product,
        string $pricingMode = 'full',
        string $saleType = 'paid',
        ?string $discountCode = null,
        ?string $referralCode = null,
        ?int $parentSaleId = null,
        ?int $approvedBy = null,
        ?array $metadata = null
    ): UpeSale {
        if (!$product->isActive()) {
            throw new \RuntimeException('Product is not active.');
        }

        $status = ($saleType === 'free' || $pricingMode === 'free')
            ? 'active'
            : 'pending_payment';

        return DB::transaction(function () use (
            $userId, $product, $pricingMode, $saleType, $status,
            $discountCode, $referralCode, $parentSaleId, $approvedBy, $metadata
        ) {
            $sale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $product->id,
                'sale_type' => $saleType,
                'pricing_mode' => $pricingMode,
                'base_fee_snapshot' => $product->base_fee,
                'currency' => $product->currency,
                'status' => $status,
                'valid_from' => ($status === 'active') ? now() : null,
                'valid_until' => ($status === 'active' && $product->validity_days)
                    ? now()->addDays($product->validity_days)
                    : null,
                'parent_sale_id' => $parentSaleId,
                'approved_by' => $approvedBy,
                'executed_at' => ($status === 'active') ? now() : null,
                'metadata' => $metadata,
            ]);

            // Apply discount if provided
            if ($discountCode !== null) {
                $validation = $this->discountEngine->validate(
                    $discountCode, $userId, $product->id, (float) $product->base_fee
                );

                if ($validation['valid'] && $validation['discount']) {
                    $this->discountEngine->apply($sale, $validation['discount'], $approvedBy);
                }
                // Silently skip invalid discount at sale creation; caller can check effectivePrice
            }

            // Track referral if provided
            if ($referralCode !== null) {
                $referral = UpeReferral::byCode($referralCode)->pending()->first();
                if ($referral && !$referral->isSelfReferral($userId)) {
                    $referral->update([
                        'referred_user_id' => $userId,
                        'referred_sale_id' => $sale->id,
                    ]);
                    $sale->update(['referral_id' => $referral->id]);
                }
            }

            // Audit
            $actorId = $approvedBy ?? $userId;
            $this->audit->logSaleCreated($actorId, $approvedBy ? 'admin' : 'user', $sale->toArray());

            return $sale;
        });
    }

    /**
     * Process payment for a pending sale.
     * Transitions sale from pending_payment → active.
     *
     * @throws \RuntimeException if sale is not pending or already executed
     */
    public function processPayment(
        UpeSale $sale,
        float $amount,
        string $paymentMethod,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?int $processedBy = null
    ): UpeLedgerEntry {
        return DB::transaction(function () use ($sale, $amount, $paymentMethod, $gatewayTransactionId, $gatewayResponse, $processedBy) {
            $locked = UpeSale::where('id', $sale->id)->lockForUpdate()->first();

            if (!$locked->isPendingPayment()) {
                throw new \RuntimeException("Sale #{$locked->id} is not pending payment. Current status: {$locked->status}");
            }

            // Server-side price verification
            $effectivePrice = $this->discountEngine->effectivePrice($locked);

            if (abs($amount - $effectivePrice) > 0.01) {
                // Allow overpayment but not underpayment
                if ($amount < $effectivePrice) {
                    throw new \RuntimeException(
                        "Payment amount ({$amount}) is less than effective price ({$effectivePrice}). " .
                        "Server-side price calculation mismatch."
                    );
                }
            }

            // Record payment in ledger
            $entry = $this->ledger->recordPayment(
                saleId: $locked->id,
                amount: $amount,
                paymentMethod: $paymentMethod,
                gatewayTransactionId: $gatewayTransactionId,
                gatewayResponse: $gatewayResponse,
                processedBy: $processedBy,
                description: "Full payment: {$amount} via {$paymentMethod}"
            );

            // Activate sale
            $this->activateSale($locked);

            // Credit referral bonus if applicable
            $this->creditReferralBonus($locked);

            return $entry;
        });
    }

    /**
     * Process a free sale (no payment needed).
     */
    public function processFreeSale(
        int $userId,
        UpeProduct $product,
        ?int $approvedBy = null,
        ?string $reason = null
    ): UpeSale {
        $sale = $this->createSale(
            userId: $userId,
            product: $product,
            pricingMode: 'free',
            saleType: 'free',
            approvedBy: $approvedBy,
            metadata: $reason ? ['reason' => $reason] : null
        );

        return $sale;
    }

    /**
     * Activate a sale after payment confirmed.
     */
    private function activateSale(UpeSale $sale): void
    {
        $oldStatus = $sale->status;
        $product = $sale->product;

        $sale->update([
            'status' => 'active',
            'valid_from' => now(),
            'valid_until' => $product && $product->validity_days
                ? now()->addDays($product->validity_days)
                : null,
            'executed_at' => now(),
        ]);

        $this->audit->logSaleStatusChange(
            $sale->approved_by ?? $sale->user_id,
            'system',
            $sale->id,
            $oldStatus,
            'active'
        );
    }

    /**
     * Credit referral bonus after payment is confirmed.
     */
    private function creditReferralBonus(UpeSale $sale): void
    {
        if (!$sale->referral_id) {
            return;
        }

        $referral = UpeReferral::find($sale->referral_id);
        if (!$referral || !$referral->isPending() || $referral->bonus_amount <= 0) {
            return;
        }

        // Create a ledger entry on the REFERRER's most recent active sale
        // (or credit to wallet via the referral engine)
        $referral->update([
            'bonus_status' => 'credited',
            'credited_at' => now(),
        ]);
    }

    /**
     * Calculate effective price for a product with optional discount.
     */
    public function calculatePrice(UpeProduct $product, ?string $discountCode = null, ?int $userId = null): array
    {
        $basePrice = (float) $product->base_fee;
        $discountAmount = 0;
        $discountInfo = null;

        if ($discountCode !== null && $userId !== null) {
            $validation = $this->discountEngine->validate($discountCode, $userId, $product->id, $basePrice);
            if ($validation['valid']) {
                $discountAmount = $validation['amount'];
                $discountInfo = [
                    'code' => $discountCode,
                    'type' => $validation['discount']->discount_type,
                    'value' => $validation['discount']->value,
                    'applied_amount' => $discountAmount,
                ];
            }
        }

        return [
            'base_price' => $basePrice,
            'discount_amount' => $discountAmount,
            'effective_price' => max(0, round($basePrice - $discountAmount, 2)),
            'currency' => $product->currency,
            'discount' => $discountInfo,
        ];
    }
}
