<?php

namespace App\Services\PaymentEngine\Policies;

use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\Contracts\RefundPolicy;

class StandardRefundPolicy implements RefundPolicy
{
    private float $percent;

    public function __construct(float $percent = 100.0)
    {
        $this->percent = $percent;
    }

    public function maxRefundable(UpeSale $sale, float $currentBalance): float
    {
        return round($currentBalance * ($this->percent / 100), 2);
    }

    public function refundPercent(): float
    {
        return $this->percent;
    }

    public function allowsPartial(): bool
    {
        return true;
    }

    public function name(): string
    {
        return "standard_refund_{$this->percent}pct";
    }
}
