<?php

namespace App\Jobs\PaymentEngine;

use App\Services\PaymentEngine\SubscriptionEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubscriptionBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SubscriptionEngine $engine): void
    {
        Log::info('[UPE] SubscriptionBillingJob started');

        $dueSubscriptions = $engine->getDueBilling();
        $charged = 0;
        $failed = 0;

        foreach ($dueSubscriptions as $subscription) {
            try {
                $result = $engine->charge($subscription);
                if ($result['success']) {
                    $charged++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('[UPE] Subscription billing error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Expire subscriptions past grace period
        $expired = $engine->expireGracePeriods();

        Log::info('[UPE] SubscriptionBillingJob completed', [
            'total_due' => $dueSubscriptions->count(),
            'charged' => $charged,
            'failed' => $failed,
            'grace_expired' => $expired,
        ]);
    }
}
