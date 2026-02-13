<?php

namespace App\Jobs\PaymentEngine;

use App\Services\PaymentEngine\InstallmentEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InstallmentOverdueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(InstallmentEngine $engine): void
    {
        $count = $engine->markOverdue();
        Log::info('[UPE] InstallmentOverdueJob completed', ['marked_overdue' => $count]);
    }
}
