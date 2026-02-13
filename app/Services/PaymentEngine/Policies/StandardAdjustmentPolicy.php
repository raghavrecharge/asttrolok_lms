<?php

namespace App\Services\PaymentEngine\Policies;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\Contracts\AdjustmentPolicy;

class StandardAdjustmentPolicy implements AdjustmentPolicy
{
    private float $percent;

    public function __construct(float $percent = 80.0)
    {
        $this->percent = $percent;
    }

    public function calculate(UpeSale $sourceSale, UpeProduct $targetProduct, float $sourceBalance): array
    {
        $transferable = round($sourceBalance * ($this->percent / 100), 2);
        $targetFee = (float) $targetProduct->base_fee;
        $remaining = max(0, round($targetFee - $transferable, 2));

        return [
            'transferable' => $transferable,
            'remaining' => $remaining,
            'percent' => $this->percent,
        ];
    }

    public function isEligible(UpeProduct $source, UpeProduct $target): bool
    {
        if (!$source->adjustment_eligible) {
            return false;
        }

        // Cannot adjust to the same product
        if ($source->id === $target->id) {
            return false;
        }

        return $target->isActive();
    }

    public function name(): string
    {
        return "standard_adjustment_{$this->percent}pct";
    }
}
