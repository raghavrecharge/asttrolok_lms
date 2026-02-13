<?php

namespace App\Services\PaymentEngine\Contracts;

use App\Models\PaymentEngine\UpeSale;

interface RefundPolicy
{
    /**
     * Calculate the maximum refundable amount for a sale.
     *
     * @param UpeSale $sale           The sale to refund
     * @param float   $currentBalance Current ledger balance for the sale
     * @return float Maximum refundable amount
     */
    public function maxRefundable(UpeSale $sale, float $currentBalance): float;

    /**
     * Get the refund percentage allowed by this policy.
     */
    public function refundPercent(): float;

    /**
     * Whether partial refunds are allowed.
     */
    public function allowsPartial(): bool;

    /**
     * Get the policy name for audit trail.
     */
    public function name(): string;
}
