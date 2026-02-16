<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeSupportAction;
use App\Models\PaymentEngine\UpeMentorBadge;
use App\Models\PaymentEngine\UpeAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Executes support actions after eligibility is confirmed.
 *
 * Rules:
 *  - NEVER mutate historical Sale or Ledger records
 *  - All adjustments are ADDITIVE (new ledger entries)
 *  - Every action is idempotent via executed_at guard
 *  - Every action is audited
 */
class SupportActionService
{
    private PaymentLedgerService $ledger;
    private AccessEngine $access;
    private AuditService $audit;
    private SupportEligibilityResolver $resolver;

    public function __construct(
        PaymentLedgerService $ledger,
        AccessEngine $access,
        AuditService $audit,
        SupportEligibilityResolver $resolver
    ) {
        $this->ledger   = $ledger;
        $this->access   = $access;
        $this->audit    = $audit;
        $this->resolver = $resolver;
    }

    // ═══════════════════════════════════════════════════════════════
    //  1️⃣  Course Extension
    // ═══════════════════════════════════════════════════════════════

    public function executeExtension(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $eligibility = $this->resolver->canExtendCourse($action->user_id, $action->product_id, $action->id);
            $this->assertEligible($eligibility, 'extension');

            $meta       = $action->metadata ?? [];
            $days       = $meta['extension_days'] ?? 30;
            $amount     = (float) $action->amount;
            $isPaid     = $amount > 0;
            $product    = UpeProduct::findOrFail($action->product_id);
            $sourceSale = UpeSale::findOrFail($eligibility->context['sale_id']);

            // Create a NEW sale (child of original) — original sale is UNTOUCHED
            $newValidFrom  = now();
            $newValidUntil = $newValidFrom->copy()->addDays($days);

            $extensionSale = UpeSale::create([
                'uuid'              => (string) Str::uuid(),
                'user_id'           => $action->user_id,
                'product_id'        => $action->product_id,
                'sale_type'         => $isPaid ? 'paid' : 'free',
                'pricing_mode'      => $isPaid ? 'full' : 'free',
                'base_fee_snapshot' => $amount,
                'status'            => 'active',
                'valid_from'        => $newValidFrom,
                'valid_until'       => $newValidUntil,
                'parent_sale_id'    => $sourceSale->id,
                'support_request_id' => $action->id,
                'approved_by'       => $executorId,
                'executed_at'       => now(),
                'metadata'          => [
                    'source'          => 'support_extension',
                    'original_sale'   => $sourceSale->id,
                    'extension_days'  => $days,
                    'support_action'  => $action->id,
                ],
            ]);

            // Ledger: record payment if paid extension
            if ($isPaid) {
                $this->ledger->recordPayment(
                    saleId: $extensionSale->id,
                    amount: $amount,
                    paymentMethod: $action->payment_method ?? 'system',
                    processedBy: $executorId,
                    description: "Course extension payment ({$days} days)"
                );
            }

            $action->update(['target_sale_id' => $extensionSale->id]);
            $this->access->invalidate($action->user_id, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.extension.executed', 'support_action', $action->id, null, [
                'extension_sale_id' => $extensionSale->id,
                'days'              => $days,
                'amount'            => $amount,
            ]);

            return $extensionSale;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  2️⃣  Temporary Access
    // ═══════════════════════════════════════════════════════════════

    public function executeTemporaryAccess(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $eligibility = $this->resolver->canGrantTemporaryAccess($action->user_id, $action->product_id, $action->id);
            $this->assertEligible($eligibility, 'temporary_access');

            $meta = $action->metadata ?? [];
            $days = $meta['temporary_days'] ?? 7;

            $expiresAt = now()->addDays($days);
            $action->update(['expires_at' => $expiresAt]);

            // No sale created, no ledger entry — just the support action with expires_at
            // AccessEngine will check for active temporary_access support actions
            $this->access->invalidate($action->user_id, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.temporary_access.executed', 'support_action', $action->id, null, [
                'expires_at' => $expiresAt->toDateTimeString(),
                'days'       => $days,
            ]);

            return null;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  3️⃣  Mentor Access
    // ═══════════════════════════════════════════════════════════════

    public function executeMentorAccess(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $eligibility = $this->resolver->canGrantMentorAccess($action->user_id, $action->product_id);
            $this->assertEligible($eligibility, 'mentor_access');

            // No sale, no ledger — access resolved via mentor badge in AccessEngine
            $this->access->invalidate($action->user_id, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.mentor_access.executed', 'support_action', $action->id, null, [
                'product_id' => $action->product_id,
            ]);

            return null;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  4️⃣  Relative / Friends Access
    // ═══════════════════════════════════════════════════════════════

    public function executeRelativeAccess(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $beneficiaryId = $action->getEffectiveBeneficiaryId();
            $eligibility = $this->resolver->canGrantRelativeAccess($action->user_id, $beneficiaryId, $action->product_id, $action->id);
            $this->assertEligible($eligibility, 'relative_access');

            $product = UpeProduct::findOrFail($action->product_id);
            $amount  = (float) $action->amount;

            // Sale belongs to BENEFICIARY
            $validFrom  = now();
            $validUntil = $product->validity_days
                ? $validFrom->copy()->addDays($product->validity_days)
                : null;

            $beneficiarySale = UpeSale::create([
                'uuid'              => (string) Str::uuid(),
                'user_id'           => $beneficiaryId,
                'product_id'        => $action->product_id,
                'sale_type'         => 'paid',
                'pricing_mode'      => 'full',
                'base_fee_snapshot' => $amount,
                'status'            => 'active',
                'valid_from'        => $validFrom,
                'valid_until'       => $validUntil,
                'support_request_id' => $action->id,
                'approved_by'       => $executorId,
                'executed_at'       => now(),
                'metadata'          => [
                    'source'     => 'support_relative_access',
                    'payer_id'   => $action->user_id,
                    'support_action' => $action->id,
                ],
            ]);

            // Ledger entry linked to the beneficiary's sale, but description references payer
            if ($amount > 0) {
                $this->ledger->append(
                    saleId: $beneficiarySale->id,
                    entryType: UpeLedgerEntry::TYPE_PAYMENT,
                    direction: UpeLedgerEntry::DIR_CREDIT,
                    amount: $amount,
                    paymentMethod: $action->payment_method ?? 'razorpay',
                    description: "Relative access payment by user #{$action->user_id} for beneficiary #{$beneficiaryId}",
                    processedBy: $executorId,
                    idempotencyKey: "support_relative_{$action->id}"
                );
            }

            $action->update(['target_sale_id' => $beneficiarySale->id]);
            $this->access->invalidate($beneficiaryId, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.relative_access.executed', 'support_action', $action->id, null, [
                'payer_id'         => $action->user_id,
                'beneficiary_id'   => $beneficiaryId,
                'sale_id'          => $beneficiarySale->id,
                'amount'           => $amount,
            ]);

            return $beneficiarySale;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  5️⃣  Offline / Cash Payment
    // ═══════════════════════════════════════════════════════════════

    public function executeOfflinePayment(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $amount  = (float) $action->amount;
            $product = UpeProduct::findOrFail($action->product_id);

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Offline payment amount must be positive.');
            }

            // Find existing sale or create new one
            $sale = UpeSale::where('user_id', $action->user_id)
                ->where('product_id', $action->product_id)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->orderByDesc('id')
                ->first();

            if (!$sale) {
                // New purchase via offline
                $validFrom  = now();
                $validUntil = $product->validity_days
                    ? $validFrom->copy()->addDays($product->validity_days)
                    : null;

                $sale = UpeSale::create([
                    'uuid'              => (string) Str::uuid(),
                    'user_id'           => $action->user_id,
                    'product_id'        => $action->product_id,
                    'sale_type'         => 'paid',
                    'pricing_mode'      => 'full',
                    'base_fee_snapshot' => $product->base_fee,
                    'status'            => 'active',
                    'valid_from'        => $validFrom,
                    'valid_until'       => $validUntil,
                    'support_request_id' => $action->id,
                    'approved_by'       => $executorId,
                    'executed_at'       => now(),
                    'metadata'          => [
                        'source'         => 'support_offline_payment',
                        'support_action' => $action->id,
                    ],
                ]);
            } else {
                // Existing sale — activate if pending
                if ($sale->status === 'pending_payment') {
                    $sale->update(['status' => 'active', 'executed_at' => $sale->executed_at ?? now()]);
                }
            }

            // Ledger: record the offline payment
            $this->ledger->recordPayment(
                saleId: $sale->id,
                amount: $amount,
                paymentMethod: 'cash',
                processedBy: $executorId,
                description: "Offline/cash payment recorded via support action #{$action->id}"
            );

            $action->update([
                'source_sale_id' => $sale->id,
                'target_sale_id' => $sale->id,
            ]);
            $this->access->invalidate($action->user_id, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.offline_payment.executed', 'support_action', $action->id, null, [
                'sale_id' => $sale->id,
                'amount'  => $amount,
            ]);

            return $sale;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  6️⃣  Refund Payment
    // ═══════════════════════════════════════════════════════════════

    public function executeRefund(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $eligibility = $this->resolver->canRefund($action->user_id, $action->product_id, $action->id);
            $this->assertEligible($eligibility, 'refund');

            $sale   = UpeSale::findOrFail($eligibility->context['sale_id']);
            $amount = (float) $action->amount;
            $maxRefundable = (float) $eligibility->context['max_refundable'];

            if ($amount > $maxRefundable) {
                throw new \InvalidArgumentException("Refund amount ({$amount}) exceeds max refundable ({$maxRefundable}).");
            }

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Refund amount must be positive.');
            }

            // Negative ledger entry (debit)
            $this->ledger->recordRefund(
                saleId: $sale->id,
                amount: $amount,
                paymentMethod: $action->payment_method ?? 'system',
                processedBy: $executorId,
                referenceType: 'support_action',
                referenceId: $action->id,
                description: "Refund via support action #{$action->id}",
                idempotencyKey: "support_refund_{$action->id}"
            );

            // Recalculate sale status — original sale is NOT mutated (only status changes)
            $newBalance = $this->ledger->balance($sale->id);
            if ($newBalance <= 0) {
                $sale->update(['status' => 'refunded']);
            } else {
                $sale->update(['status' => 'partially_refunded']);
            }

            $action->update(['source_sale_id' => $sale->id]);
            $this->access->invalidate($action->user_id, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.refund.executed', 'support_action', $action->id, null, [
                'sale_id'     => $sale->id,
                'amount'      => $amount,
                'new_balance' => $newBalance,
                'new_status'  => $sale->fresh()->status,
            ]);

            return null;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  7️⃣  Wrong Payment / Payment Migration
    // ═══════════════════════════════════════════════════════════════

    public function executePaymentMigration(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            $sourceProductId = $action->source_product_id;
            $targetProductId = $action->product_id;

            $eligibility = $this->resolver->canMigratePayment($action->user_id, $sourceProductId, $targetProductId, $action->id);
            $this->assertEligible($eligibility, 'payment_migration');

            $sourceSale    = UpeSale::findOrFail($eligibility->context['source_sale_id']);
            $targetProduct = UpeProduct::findOrFail($targetProductId);
            $amount        = (float) $action->amount;
            $maxTransferable = (float) $eligibility->context['max_transferable'];

            if ($amount > $maxTransferable) {
                throw new \InvalidArgumentException("Migration amount ({$amount}) exceeds max transferable ({$maxTransferable}).");
            }

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Migration amount must be positive.');
            }

            // Step 1: Debit adjustment on source course
            $debitEntry = $this->ledger->recordAdjustmentOut(
                sourceSaleId: $sourceSale->id,
                amount: $amount,
                adjustmentId: $action->id,
                processedBy: $executorId
            );

            // Step 2: Find or create target sale
            $targetSale = UpeSale::where('user_id', $action->user_id)
                ->where('product_id', $targetProductId)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->first();

            if (!$targetSale) {
                $validFrom  = now();
                $validUntil = $targetProduct->validity_days
                    ? $validFrom->copy()->addDays($targetProduct->validity_days)
                    : null;

                $targetSale = UpeSale::create([
                    'uuid'              => (string) Str::uuid(),
                    'user_id'           => $action->user_id,
                    'product_id'        => $targetProductId,
                    'sale_type'         => 'adjustment',
                    'pricing_mode'      => 'full',
                    'base_fee_snapshot' => $targetProduct->base_fee,
                    'status'            => 'active',
                    'valid_from'        => $validFrom,
                    'valid_until'       => $validUntil,
                    'parent_sale_id'    => $sourceSale->id,
                    'support_request_id' => $action->id,
                    'approved_by'       => $executorId,
                    'executed_at'       => now(),
                    'metadata'          => [
                        'source'             => 'support_payment_migration',
                        'source_sale_id'     => $sourceSale->id,
                        'source_product_id'  => $sourceProductId,
                        'support_action'     => $action->id,
                    ],
                ]);
            }

            // Step 3: Credit adjustment on target course
            $creditEntry = $this->ledger->recordAdjustmentIn(
                targetSaleId: $targetSale->id,
                amount: $amount,
                adjustmentId: $action->id,
                processedBy: $executorId
            );

            // Step 4: Recalculate source sale status
            $sourceBalance = $this->ledger->balance($sourceSale->id);
            if ($sourceBalance <= 0) {
                $sourceSale->update(['status' => 'refunded']);
            }

            $action->update([
                'source_sale_id' => $sourceSale->id,
                'target_sale_id' => $targetSale->id,
            ]);

            // Invalidate both products
            $this->access->invalidate($action->user_id, $sourceProductId);
            $this->access->invalidate($action->user_id, $targetProductId);

            $this->audit->log($executorId, 'admin', 'support.payment_migration.executed', 'support_action', $action->id, null, [
                'source_sale_id' => $sourceSale->id,
                'target_sale_id' => $targetSale->id,
                'amount'         => $amount,
                'source_new_balance' => $sourceBalance,
            ]);

            return $targetSale;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  8️⃣  Post-Purchase Coupon Apply
    // ═══════════════════════════════════════════════════════════════

    public function executeCouponApply(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return $this->executeInTransaction($action, $executorId, function () use ($action, $executorId) {
            // Check sale-level eligibility first (without coupon code validation)
            $eligibility = $this->resolver->canApplyCoupon(
                $action->user_id,
                $action->product_id,
                null // Validate coupon separately — allows admin-set amounts without a real coupon code
            );
            $this->assertEligible($eligibility, 'coupon_apply');

            // If coupon code provided, validate it; otherwise use admin-set amount
            $discountAmount = (float) $action->amount;
            $discountId     = $action->discount_id;

            if ($action->coupon_code) {
                $couponElig = $this->resolver->canApplyCoupon(
                    $action->user_id, $action->product_id, $action->coupon_code
                );
                if ($couponElig->eligible && isset($couponElig->context['coupon_discount'])) {
                    $discountAmount = (float) $couponElig->context['coupon_discount'];
                    $discountId     = $couponElig->context['coupon_id'] ?? $discountId;
                }
            }

            $sale = UpeSale::findOrFail($eligibility->context['sale_id']);

            if ($discountAmount <= 0) {
                throw new \InvalidArgumentException('Coupon discount amount must be positive.');
            }

            // Outstanding check — coupon cannot reduce below 0
            $outstanding = (float) $sale->base_fee_snapshot - $this->ledger->balance($sale->id);
            $effectiveDiscount = min($discountAmount, max(0, $outstanding));

            if ($effectiveDiscount <= 0) {
                throw new \InvalidArgumentException('No outstanding amount to apply coupon against.');
            }

            // Create discount ledger entry (credit — reduces payable)
            $this->ledger->recordDiscount(
                saleId: $sale->id,
                amount: $effectiveDiscount,
                discountId: $discountId ?? 0,
                processedBy: $executorId,
                description: "Post-purchase coupon '{$action->coupon_code}' applied via support action #{$action->id}"
            );

            $action->update([
                'source_sale_id' => $sale->id,
                'amount'         => $effectiveDiscount,
                'discount_id'    => $discountId,
            ]);

            $this->access->invalidate($action->user_id, $action->product_id);

            $this->audit->log($executorId, 'admin', 'support.coupon_apply.executed', 'support_action', $action->id, null, [
                'sale_id'         => $sale->id,
                'coupon_code'     => $action->coupon_code,
                'discount_amount' => $effectiveDiscount,
                'new_balance'     => $this->ledger->balance($sale->id),
            ]);

            return null;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  Dispatch by type
    // ═══════════════════════════════════════════════════════════════

    public function execute(UpeSupportAction $action, int $executorId): UpeSupportAction
    {
        return match ($action->action_type) {
            UpeSupportAction::ACTION_EXTENSION         => $this->executeExtension($action, $executorId),
            UpeSupportAction::ACTION_TEMPORARY_ACCESS  => $this->executeTemporaryAccess($action, $executorId),
            UpeSupportAction::ACTION_MENTOR_ACCESS     => $this->executeMentorAccess($action, $executorId),
            UpeSupportAction::ACTION_RELATIVE_ACCESS   => $this->executeRelativeAccess($action, $executorId),
            UpeSupportAction::ACTION_OFFLINE_PAYMENT   => $this->executeOfflinePayment($action, $executorId),
            UpeSupportAction::ACTION_REFUND            => $this->executeRefund($action, $executorId),
            UpeSupportAction::ACTION_PAYMENT_MIGRATION => $this->executePaymentMigration($action, $executorId),
            UpeSupportAction::ACTION_COUPON_APPLY      => $this->executeCouponApply($action, $executorId),
            default => throw new \InvalidArgumentException("Unknown action type: {$action->action_type}"),
        };
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Wraps execution in a DB transaction with idempotency guard.
     */
    private function executeInTransaction(UpeSupportAction $action, int $executorId, callable $fn): UpeSupportAction
    {
        // Idempotency: already executed
        if ($action->executed_at !== null) {
            return $action;
        }

        if (!$action->canBeExecuted()) {
            throw new \RuntimeException("Support action #{$action->id} cannot be executed (status: {$action->status}).");
        }

        DB::transaction(function () use ($action, $executorId, $fn) {
            // Lock row
            $action = UpeSupportAction::lockForUpdate()->findOrFail($action->id);

            // Double-check idempotency inside transaction
            if ($action->executed_at !== null) {
                return;
            }

            $fn();

            $action->update([
                'status'      => UpeSupportAction::STATUS_EXECUTED,
                'executed_by' => $executorId,
                'executed_at' => now(),
            ]);
        });

        return $action->fresh();
    }

    private function assertEligible(SupportEligibility $eligibility, string $actionType): void
    {
        if (!$eligibility->eligible) {
            throw new \RuntimeException(
                "Support action '{$actionType}' is not eligible: {$eligibility->reason}"
            );
        }
    }
}
