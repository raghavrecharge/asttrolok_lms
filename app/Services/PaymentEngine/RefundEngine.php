<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\Contracts\RefundPolicy;
use Illuminate\Support\Facades\DB;

class RefundEngine
{
    private PaymentLedgerService $ledger;
    private AuditService $audit;

    public function __construct(PaymentLedgerService $ledger, AuditService $audit)
    {
        $this->ledger = $ledger;
        $this->audit = $audit;
    }

    /**
     * Calculate refund estimate without executing.
     */
    public function calculateRefund(UpeSale $sale, RefundPolicy $policy): array
    {
        $balance = $this->ledger->balance($sale->id);
        $maxRefundable = $policy->maxRefundable($sale, $balance);
        $totalRefunded = $this->ledger->totalRefunded($sale->id);

        return [
            'sale_id' => $sale->id,
            'current_balance' => $balance,
            'already_refunded' => $totalRefunded,
            'max_refundable' => $maxRefundable,
            'policy_name' => $policy->name(),
            'policy_percent' => $policy->refundPercent(),
            'allows_partial' => $policy->allowsPartial(),
        ];
    }

    /**
     * Process a refund. Creates an immutable debit ledger entry.
     *
     * @param UpeSale      $sale        The sale to refund
     * @param float        $amount      Amount to refund (must be <= max refundable)
     * @param RefundPolicy $policy      The refund policy to apply
     * @param string       $reason      Human-readable reason
     * @param int|null     $processedBy Admin who processed
     * @param string|null  $paymentMethod How refund is issued
     * @return UpeLedgerEntry The refund ledger entry
     *
     * @throws \RuntimeException if refund is invalid
     */
    public function processRefund(
        UpeSale $sale,
        float $amount,
        RefundPolicy $policy,
        string $reason,
        ?int $processedBy = null,
        ?string $paymentMethod = null,
        ?string $idempotencyKey = null
    ): UpeLedgerEntry {
        return DB::transaction(function () use ($sale, $amount, $policy, $reason, $processedBy, $paymentMethod, $idempotencyKey) {
            $locked = UpeSale::where('id', $sale->id)->lockForUpdate()->first();

            // Validate sale is refundable
            if ($locked->isTerminal() && $locked->status !== 'partially_refunded') {
                throw new \RuntimeException("Sale #{$locked->id} is in terminal status '{$locked->status}' and cannot be refunded.");
            }

            if (!in_array($locked->status, ['active', 'partially_refunded', 'completed'])) {
                throw new \RuntimeException("Sale #{$locked->id} status '{$locked->status}' is not eligible for refund.");
            }

            $balance = $this->ledger->balance($locked->id);
            $maxRefundable = $policy->maxRefundable($locked, $balance);

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Refund amount must be positive.');
            }

            if ($amount > $maxRefundable) {
                throw new \RuntimeException(
                    "Refund amount ({$amount}) exceeds maximum refundable ({$maxRefundable}) under policy '{$policy->name()}'."
                );
            }

            if (!$policy->allowsPartial() && $amount < $maxRefundable) {
                throw new \RuntimeException("Policy '{$policy->name()}' does not allow partial refunds. Must refund full amount: {$maxRefundable}");
            }

            // Create refund ledger entry
            $entry = $this->ledger->recordRefund(
                saleId: $locked->id,
                amount: $amount,
                paymentMethod: $paymentMethod ?? 'system',
                processedBy: $processedBy,
                description: "Refund: {$amount} — {$reason}",
                idempotencyKey: $idempotencyKey ?? "refund_{$locked->id}_" . time()
            );

            // Update sale status based on new balance
            $newBalance = $this->ledger->balance($locked->id);
            $oldStatus = $locked->status;

            if ($newBalance <= 0) {
                $locked->update(['status' => 'refunded']);
            } else {
                $locked->update(['status' => 'partially_refunded']);
            }

            // Handle installment plan if exists
            $this->handleInstallmentOnRefund($locked);

            // Audit
            if ($processedBy) {
                $this->audit->logRefund($processedBy, 'admin', $locked->id, [
                    'amount' => $amount,
                    'reason' => $reason,
                    'policy' => $policy->name(),
                    'old_status' => $oldStatus,
                    'new_status' => $locked->fresh()->status,
                    'new_balance' => $newBalance,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Handle installment plan when a refund occurs.
     * Waive remaining installments if fully refunded.
     */
    private function handleInstallmentOnRefund(UpeSale $sale): void
    {
        // Find the ACTIVE plan (hasOne may return old restructured plan)
        $plan = UpeInstallmentPlan::where('sale_id', $sale->id)->where('status', 'active')->first();
        if (!$plan) {
            return;
        }

        if ($sale->status === 'refunded') {
            // Full refund: waive all unpaid schedules, default the plan
            $plan->schedules()
                ->whereIn('status', ['upcoming', 'due', 'partial', 'overdue'])
                ->update(['status' => 'waived']);

            $plan->update(['status' => 'defaulted']);
        }
    }
}
