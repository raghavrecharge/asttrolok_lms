<?php

namespace App\Services\PaymentEngine\Contracts;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;

interface AdjustmentPolicy
{
    /**
     * Calculate the transferable amount from source sale to target product.
     *
     * @param UpeSale    $sourceSale    The original sale
     * @param UpeProduct $targetProduct The product being upgraded/adjusted to
     * @param float      $sourceBalance Current ledger balance on source sale
     * @return array ['transferable' => float, 'remaining' => float, 'percent' => float]
     */
    public function calculate(UpeSale $sourceSale, UpeProduct $targetProduct, float $sourceBalance): array;

    /**
     * Whether this adjustment type is allowed between these products.
     */
    public function isEligible(UpeProduct $source, UpeProduct $target): bool;

    /**
     * Get the policy name for audit/snapshot.
     */
    public function name(): string;
}
