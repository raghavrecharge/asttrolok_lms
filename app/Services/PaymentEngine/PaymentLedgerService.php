<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentLedgerService
{
    private AuditService $audit;

    public function __construct(AuditService $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Append an immutable entry to the ledger.
     *
     * @throws \RuntimeException if idempotency key already exists
     */
    public function append(
        int $saleId,
        string $entryType,
        string $direction,
        float $amount,
        ?string $paymentMethod = null,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $processedBy = null,
        ?string $idempotencyKey = null,
        string $currency = 'INR'
    ): UpeLedgerEntry {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Ledger entry amount must be positive. Got: ' . $amount);
        }

        if (!in_array($direction, [UpeLedgerEntry::DIR_CREDIT, UpeLedgerEntry::DIR_DEBIT])) {
            throw new \InvalidArgumentException("Invalid direction: {$direction}");
        }

        // Auto-generate idempotency key from gateway transaction if available
        if ($idempotencyKey === null && $gatewayTransactionId !== null && $paymentMethod !== null) {
            $idempotencyKey = "gateway_{$paymentMethod}_{$gatewayTransactionId}";
        }

        // Check idempotency before insert
        if ($idempotencyKey !== null) {
            $existing = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing; // Idempotent: return existing entry
            }
        }

        $entry = UpeLedgerEntry::create([
            'uuid' => (string) Str::uuid(),
            'sale_id' => $saleId,
            'entry_type' => $entryType,
            'direction' => $direction,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => $paymentMethod,
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_response' => $gatewayResponse,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'processed_by' => $processedBy,
            'idempotency_key' => $idempotencyKey,
        ]);

        // Audit trail
        if ($processedBy) {
            $this->audit->logLedgerEntry($processedBy, 'system', $entry->toArray());
        }

        return $entry;
    }

    // ── Convenience Methods ──

    public function recordPayment(
        int $saleId,
        float $amount,
        string $paymentMethod,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?int $processedBy = null,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $saleId,
            entryType: UpeLedgerEntry::TYPE_PAYMENT,
            direction: UpeLedgerEntry::DIR_CREDIT,
            amount: $amount,
            paymentMethod: $paymentMethod,
            gatewayTransactionId: $gatewayTransactionId,
            gatewayResponse: $gatewayResponse,
            description: $description ?? "Payment via {$paymentMethod}",
            processedBy: $processedBy,
            idempotencyKey: $idempotencyKey ?? ($gatewayTransactionId ? "payment_{$paymentMethod}_{$gatewayTransactionId}" : null)
        );
    }

    public function recordRefund(
        int $saleId,
        float $amount,
        ?string $paymentMethod = null,
        ?int $processedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $saleId,
            entryType: UpeLedgerEntry::TYPE_REFUND,
            direction: UpeLedgerEntry::DIR_DEBIT,
            amount: $amount,
            paymentMethod: $paymentMethod ?? 'system',
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: $description ?? "Refund of {$amount}",
            processedBy: $processedBy,
            idempotencyKey: $idempotencyKey
        );
    }

    public function recordDiscount(
        int $saleId,
        float $amount,
        int $discountId,
        ?int $processedBy = null,
        ?string $description = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $saleId,
            entryType: UpeLedgerEntry::TYPE_DISCOUNT,
            direction: UpeLedgerEntry::DIR_CREDIT,
            amount: $amount,
            paymentMethod: 'system',
            referenceType: 'discount',
            referenceId: $discountId,
            description: $description ?? "Discount applied: {$amount}",
            processedBy: $processedBy,
            idempotencyKey: "discount_{$saleId}_{$discountId}"
        );
    }

    public function recordInstallmentPayment(
        int $saleId,
        float $amount,
        int $scheduleId,
        string $paymentMethod,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?int $processedBy = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $saleId,
            entryType: UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
            direction: UpeLedgerEntry::DIR_CREDIT,
            amount: $amount,
            paymentMethod: $paymentMethod,
            gatewayTransactionId: $gatewayTransactionId,
            gatewayResponse: $gatewayResponse,
            referenceType: 'installment_schedule',
            referenceId: $scheduleId,
            description: "Installment payment for schedule #{$scheduleId}",
            processedBy: $processedBy,
            idempotencyKey: $gatewayTransactionId
                ? "installment_{$paymentMethod}_{$gatewayTransactionId}"
                : "installment_{$saleId}_{$scheduleId}_" . time()
        );
    }

    public function recordSubscriptionCharge(
        int $saleId,
        float $amount,
        int $cycleId,
        string $paymentMethod,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $saleId,
            entryType: UpeLedgerEntry::TYPE_SUBSCRIPTION_CHARGE,
            direction: UpeLedgerEntry::DIR_CREDIT,
            amount: $amount,
            paymentMethod: $paymentMethod,
            gatewayTransactionId: $gatewayTransactionId,
            gatewayResponse: $gatewayResponse,
            referenceType: 'subscription_cycle',
            referenceId: $cycleId,
            description: "Subscription charge for cycle #{$cycleId}",
            idempotencyKey: "sub_charge_{$cycleId}"
        );
    }

    public function recordAdjustmentOut(
        int $sourceSaleId,
        float $amount,
        int $adjustmentId,
        ?int $processedBy = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $sourceSaleId,
            entryType: UpeLedgerEntry::TYPE_ADJUSTMENT_OUT,
            direction: UpeLedgerEntry::DIR_DEBIT,
            amount: $amount,
            paymentMethod: 'system',
            referenceType: 'adjustment',
            referenceId: $adjustmentId,
            description: "Adjustment out: {$amount} transferred to new sale",
            processedBy: $processedBy,
            idempotencyKey: "adj_out_{$adjustmentId}"
        );
    }

    public function recordAdjustmentIn(
        int $targetSaleId,
        float $amount,
        int $adjustmentId,
        ?int $processedBy = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $targetSaleId,
            entryType: UpeLedgerEntry::TYPE_ADJUSTMENT_IN,
            direction: UpeLedgerEntry::DIR_CREDIT,
            amount: $amount,
            paymentMethod: 'system',
            referenceType: 'adjustment',
            referenceId: $adjustmentId,
            description: "Adjustment in: {$amount} received from previous sale",
            processedBy: $processedBy,
            idempotencyKey: "adj_in_{$adjustmentId}"
        );
    }

    public function recordReferralBonus(
        int $saleId,
        float $amount,
        int $referralId,
        ?int $processedBy = null
    ): UpeLedgerEntry {
        return $this->append(
            saleId: $saleId,
            entryType: UpeLedgerEntry::TYPE_REFERRAL_BONUS,
            direction: UpeLedgerEntry::DIR_CREDIT,
            amount: $amount,
            paymentMethod: 'system',
            referenceType: 'referral',
            referenceId: $referralId,
            description: "Referral bonus: {$amount}",
            processedBy: $processedBy,
            idempotencyKey: "referral_bonus_{$referralId}"
        );
    }

    // ── Query Methods ──

    /**
     * Net balance for a sale: sum(credits) - sum(debits)
     */
    public function balance(int $saleId): float
    {
        $result = DB::table('upe_ledger_entries')
            ->where('sale_id', $saleId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END), 0) AS net
            ")
            ->first();

        return round((float) ($result->net ?? 0), 2);
    }

    /**
     * Total credits (money in + discounts + adjustments in) for a sale.
     */
    public function totalCredits(int $saleId): float
    {
        return round((float) UpeLedgerEntry::forSale($saleId)->credits()->sum('amount'), 2);
    }

    /**
     * Total debits (refunds + adjustments out) for a sale.
     */
    public function totalDebits(int $saleId): float
    {
        return round((float) UpeLedgerEntry::forSale($saleId)->debits()->sum('amount'), 2);
    }

    /**
     * Total real money received (excludes discounts, adjustments, bonuses).
     */
    public function totalRealPayments(int $saleId): float
    {
        return round((float) UpeLedgerEntry::forSale($saleId)
            ->whereIn('entry_type', [
                UpeLedgerEntry::TYPE_PAYMENT,
                UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                UpeLedgerEntry::TYPE_SUBSCRIPTION_CHARGE,
            ])
            ->sum('amount'), 2);
    }

    /**
     * Total refunded amount for a sale.
     */
    public function totalRefunded(int $saleId): float
    {
        return round((float) UpeLedgerEntry::forSale($saleId)
            ->ofType(UpeLedgerEntry::TYPE_REFUND)
            ->sum('amount'), 2);
    }

    /**
     * Whether the sale has any payment recorded.
     */
    public function hasPayment(int $saleId): bool
    {
        return UpeLedgerEntry::forSale($saleId)
            ->whereIn('entry_type', [
                UpeLedgerEntry::TYPE_PAYMENT,
                UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                UpeLedgerEntry::TYPE_SUBSCRIPTION_CHARGE,
            ])
            ->exists();
    }

    /**
     * Get all ledger entries for a sale, ordered chronologically.
     */
    public function entries(int $saleId): \Illuminate\Database\Eloquent\Collection
    {
        return UpeLedgerEntry::forSale($saleId)->orderBy('created_at')->orderBy('id')->get();
    }

    /**
     * Get a summary breakdown for a sale.
     */
    public function summary(int $saleId): array
    {
        $entries = $this->entries($saleId);

        $summary = [
            'sale_id' => $saleId,
            'total_credits' => 0,
            'total_debits' => 0,
            'net_balance' => 0,
            'breakdown' => [],
            'entry_count' => $entries->count(),
        ];

        foreach ($entries as $entry) {
            $type = $entry->entry_type;
            if (!isset($summary['breakdown'][$type])) {
                $summary['breakdown'][$type] = ['count' => 0, 'total' => 0];
            }
            $summary['breakdown'][$type]['count']++;
            $summary['breakdown'][$type]['total'] += (float) $entry->amount;

            if ($entry->isCredit()) {
                $summary['total_credits'] += (float) $entry->amount;
            } else {
                $summary['total_debits'] += (float) $entry->amount;
            }
        }

        $summary['net_balance'] = round($summary['total_credits'] - $summary['total_debits'], 2);
        $summary['total_credits'] = round($summary['total_credits'], 2);
        $summary['total_debits'] = round($summary['total_debits'], 2);

        return $summary;
    }
}
