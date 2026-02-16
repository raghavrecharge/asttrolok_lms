<?php

namespace App\Jobs\PaymentEngine;

use App\Models\PaymentEngine\UpeSubscription;
use App\Services\PaymentEngine\SubscriptionEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireTrialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Expire trial subscriptions whose trial_ends_at has passed.
     * Transitions them to 'active' (first real billing) or 'expired' if no gateway.
     */
    public function handle(SubscriptionEngine $engine): void
    {
        Log::info('[UPE] ExpireTrialsJob started');

        $expiredTrials = UpeSubscription::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        $charged = 0;
        $failed = 0;

        foreach ($expiredTrials as $subscription) {
            try {
                // Attempt first real charge
                $result = $engine->charge($subscription);

                if ($result['success']) {
                    $charged++;
                } else {
                    $failed++;
                    // If no gateway configured, move to past_due so billing job retries
                    if ($subscription->fresh()->status === 'trial') {
                        $subscription->update(['status' => 'past_due']);
                        $failed++;
                    }
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('[UPE] ExpireTrialsJob error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[UPE] ExpireTrialsJob completed', [
            'total_expired_trials' => $expiredTrials->count(),
            'charged' => $charged,
            'failed' => $failed,
        ]);
    }
}
