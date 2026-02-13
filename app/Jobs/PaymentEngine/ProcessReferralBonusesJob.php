<?php

namespace App\Jobs\PaymentEngine;

use App\Services\PaymentEngine\ReferralEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReferralBonusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ReferralEngine $engine): void
    {
        $credited = $engine->processPendingBonuses();
        Log::info('[UPE] ProcessReferralBonusesJob completed', ['credited' => $credited]);
    }
}
