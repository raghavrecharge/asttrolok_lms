<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeAdjustment;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\Contracts\AdjustmentPolicy;
use Illuminate\Support\Facades\DB;

class AdjustmentEngine
{
    private PaymentLedgerService $ledger;
    private PurchaseEngine $purchaseEngine;
    private AuditService $audit;

    public function __construct(
        PaymentLedgerService $ledger,
        PurchaseEngine $purchaseEngine,
        AuditService $audit
    ) {
        $this->ledger = $ledger;
        $this->purchaseEngine = $purchaseEngine;
        $this->audit = $audit;
    }

    /**
     * Calculate adjustment estimate without executing.
     *
     * @return array ['transferable' => float, 'remaining' => float, 'target_base_fee' => float, 'percent' => float]
     */
    public function calculate(
        UpeSale $sourceSale,
        UpeProduct $targetProduct,
        AdjustmentPolicy $policy
    ): array {
        $sourceBalance = $this->ledger->balance($sourceSale->id);

        if ($sourceBalance <= 0) {
            throw new \RuntimeException("Source sale #{$sourceSale->id} has no positive balance to transfer.");
        }

        if (!$policy->isEligible($sourceSale->product, $targetProduct)) {
            throw new \RuntimeException('Adjustment is not eligible between these products per policy.');
        }

        $result = $policy->calculate($sourceSale, $targetProduct, $sourceBalance);

        return [
            'source_sale_id' => $sourceSale->id,
            'source_balance' => $sourceBalance,
            'target_product_id' => $targetProduct->id,
            'target_base_fee' => (float) $targetProduct->base_fee,
            'transferable' => $result['transferable'],
            'remaining_to_pay' => $result['remaining'],
            'adjustment_percent' => $result['percent'],
            'policy_name' => $policy->name(),
        ];
    }

    /**
     * Execute an adjustment: transfer payment from source sale to a new target sale.
     *
     * - Creates target sale (status = pending_payment if remaining > 0, active if fully covered)
     * - Creates adjustment_out ledger entry on source
     * - Creates adjustment_in ledger entry on target
     * - Source sale marked completed
     * - Returns Adjustment record
     *
     * @throws \RuntimeException on invalid state or policy violation
     */
    public function execute(
        UpeSale $sourceSale,
        UpeProduct $targetProduct,
        AdjustmentPolicy $policy,
        string $adjustmentType,
        int $approvedBy,
        ?int $userId = null
    ): array {
        return DB::transaction(function () use ($sourceSale, $targetProduct, $policy, $adjustmentType, $approvedBy, $userId) {
            // Lock source sale
            $lockedSource = UpeSale::where('id', $sourceSale->id)->lockForUpdate()->first();

            if (!in_array($lockedSource->status, ['active', 'partially_refunded'])) {
                throw new \RuntimeException("Source sale #{$lockedSource->id} status '{$lockedSource->status}' is not eligible for adjustment.");
            }

            $sourceBalance = $this->ledger->balance($lockedSource->id);
            if ($sourceBalance <= 0) {
                throw new \RuntimeException('Source sale has no positive balance.');
            }

            if (!$policy->isEligible($lockedSource->product, $targetProduct)) {
                throw new \RuntimeException('Adjustment not eligible per policy.');
            }

            $calc = $policy->calculate($lockedSource, $targetProduct, $sourceBalance);
            $transferable = $calc['transferable'];
            $remaining = $calc['remaining'];

            // Create target sale
            $targetSaleType = ($adjustmentType === 'wrong_course') ? 'adjustment' : 'upgrade';
            $targetStatus = ($remaining <= 0.01) ? 'active' : 'pending_payment';

            $targetSale = $this->purchaseEngine->createSale(
                userId: $userId ?? $lockedSource->user_id,
                product: $targetProduct,
                pricingMode: ($remaining <= 0.01) ? 'full' : 'full',
                saleType: $targetSaleType,
                parentSaleId: $lockedSource->id,
                approvedBy: $approvedBy,
                metadata: [
                    'adjustment_type' => $adjustmentType,
                    'source_sale_id' => $lockedSource->id,
                ]
            );

            // Create adjustment record (need ledger entry IDs, create them first)
            $sourceEntry = $this->ledger->recordAdjustmentOut(
                sourceSaleId: $lockedSource->id,
                amount: $transferable,
                adjustmentId: 0, // Placeholder, will be updated
                processedBy: $approvedBy
            );

            $targetEntry = $this->ledger->recordAdjustmentIn(
                targetSaleId: $targetSale->id,
                amount: $transferable,
                adjustmentId: 0, // Placeholder
                processedBy: $approvedBy
            );

            $adjustment = UpeAdjustment::create([
                'source_sale_id' => $lockedSource->id,
                'target_sale_id' => $targetSale->id,
                'adjustment_type' => $adjustmentType,
                'source_amount' => $transferable,
                'target_amount' => $transferable,
                'adjustment_percent' => $calc['percent'],
                'policy_snapshot' => [
                    'name' => $policy->name(),
                    'percent' => $calc['percent'],
                    'source_balance' => $sourceBalance,
                    'transferable' => $transferable,
                    'remaining' => $remaining,
                ],
                'source_ledger_entry_id' => $sourceEntry->id,
                'target_ledger_entry_id' => $targetEntry->id,
                'approved_by' => $approvedBy,
            ]);

            // Mark source sale as completed (access ends)
            $lockedSource->update([
                'status' => 'completed',
                'valid_until' => now(),
            ]);

            // If target is fully covered, activate it
            if ($remaining <= 0.01) {
                $targetSale->update([
                    'status' => 'active',
                    'valid_from' => now(),
                    'valid_until' => $targetProduct->validity_days
                        ? now()->addDays($targetProduct->validity_days)
                        : null,
                    'executed_at' => now(),
                ]);
            }

            $this->audit->logAdjustment($approvedBy, 'admin', $adjustment->id, [
                'type' => $adjustmentType,
                'source_sale_id' => $lockedSource->id,
                'target_sale_id' => $targetSale->id,
                'transferable' => $transferable,
                'remaining' => $remaining,
            ]);

            return [
                'adjustment' => $adjustment,
                'target_sale' => $targetSale,
                'source_entry' => $sourceEntry,
                'target_entry' => $targetEntry,
                'remaining_to_pay' => $remaining,
            ];
        });
    }
}
