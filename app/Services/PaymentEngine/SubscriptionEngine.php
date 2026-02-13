<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeSubscription;
use App\Models\PaymentEngine\UpeSubscriptionCycle;
use App\Services\PaymentEngine\Contracts\PaymentGatewayDriver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionEngine
{
    private PaymentLedgerService $ledger;
    private AuditService $audit;

    const MAX_RETRIES = 3;

    public function __construct(PaymentLedgerService $ledger, AuditService $audit)
    {
        $this->ledger = $ledger;
        $this->audit = $audit;
    }

    /**
     * Create a new subscription with optional trial period.
     */
    public function create(
        int $userId,
        UpeProduct $product,
        UpeSale $sale,
        float $billingAmount,
        string $billingInterval = 'monthly',
        int $trialDays = 0,
        int $gracePeriodDays = 3,
        ?string $gatewaySubscriptionId = null
    ): UpeSubscription {
        $existingActive = UpeSubscription::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->active()
            ->first();

        if ($existingActive) {
            throw new \RuntimeException("User already has an active subscription for product #{$product->id}.");
        }

        $now = now();
        $trialEndsAt = $trialDays > 0 ? $now->copy()->addDays($trialDays) : null;
        $periodStart = $now;
        $periodEnd = $trialDays > 0
            ? $trialEndsAt
            : $this->calculatePeriodEnd($now, $billingInterval);

        $subscription = UpeSubscription::create([
            'sale_id' => $sale->id,
            'user_id' => $userId,
            'product_id' => $product->id,
            'billing_amount' => $billingAmount,
            'billing_interval' => $billingInterval,
            'trial_ends_at' => $trialEndsAt,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'grace_period_days' => $gracePeriodDays,
            'status' => $trialDays > 0 ? 'trial' : 'active',
            'gateway_subscription_id' => $gatewaySubscriptionId,
        ]);

        $this->audit->logSubscriptionEvent($userId, 'user', $subscription->id, 'created', [
            'trial_days' => $trialDays,
            'billing_amount' => $billingAmount,
            'billing_interval' => $billingInterval,
        ]);

        return $subscription;
    }

    /**
     * Process billing for a subscription cycle.
     * Called by the billing job for each subscription due for renewal.
     *
     * @return array ['success' => bool, 'cycle' => UpeSubscriptionCycle, 'entry' => ?UpeLedgerEntry]
     */
    public function charge(UpeSubscription $subscription, ?PaymentGatewayDriver $gateway = null): array
    {
        return DB::transaction(function () use ($subscription, $gateway) {
            $sub = UpeSubscription::where('id', $subscription->id)->lockForUpdate()->first();

            if (!in_array($sub->status, ['trial', 'active', 'past_due', 'grace'])) {
                throw new \RuntimeException("Subscription #{$sub->id} status '{$sub->status}' is not billable.");
            }

            // Determine cycle number
            $lastCycle = $sub->cycles()->orderByDesc('cycle_number')->first();
            $cycleNumber = $lastCycle ? $lastCycle->cycle_number + 1 : 1;

            $periodStart = $sub->current_period_end;
            $periodEnd = $this->calculatePeriodEnd($periodStart, $sub->billing_interval);

            // Create cycle record
            $cycle = UpeSubscriptionCycle::create([
                'subscription_id' => $sub->id,
                'cycle_number' => $cycleNumber,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'amount' => $sub->billing_amount,
                'status' => 'pending',
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]);

            // Attempt payment
            $paymentSuccess = false;
            $gatewayTxnId = null;
            $gatewayResponse = null;

            if ($gateway && $sub->gateway_subscription_id) {
                try {
                    $result = $gateway->initiate(
                        (float) $sub->billing_amount,
                        'INR',
                        ['subscription_id' => $sub->gateway_subscription_id, 'cycle' => $cycleNumber]
                    );
                    $paymentSuccess = !empty($result['transaction_id']);
                    $gatewayTxnId = $result['transaction_id'] ?? null;
                    $gatewayResponse = $result['raw_response'] ?? null;
                } catch (\Exception $e) {
                    Log::error('Subscription charge failed', [
                        'subscription_id' => $sub->id,
                        'cycle' => $cycleNumber,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                // No gateway: mark as pending manual payment
                $paymentSuccess = false;
            }

            if ($paymentSuccess) {
                $entry = $this->ledger->recordSubscriptionCharge(
                    saleId: $sub->sale_id,
                    amount: (float) $sub->billing_amount,
                    cycleId: $cycle->id,
                    paymentMethod: $gateway ? $gateway->getMethod() : 'system',
                    gatewayTransactionId: $gatewayTxnId,
                    gatewayResponse: $gatewayResponse
                );

                $cycle->update([
                    'status' => 'paid',
                    'ledger_entry_id' => $entry->id,
                ]);

                $sub->update([
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                    'status' => 'active',
                ]);

                $this->audit->logSubscriptionEvent(0, 'system', $sub->id, 'charged', [
                    'cycle' => $cycleNumber,
                    'amount' => $sub->billing_amount,
                ]);

                return ['success' => true, 'cycle' => $cycle, 'entry' => $entry];
            }

            // Payment failed
            $cycle->update(['status' => 'failed']);
            $this->handleFailedCharge($sub, $cycle);

            return ['success' => false, 'cycle' => $cycle, 'entry' => null];
        });
    }

    /**
     * Cancel a subscription. No more billing.
     * Access continues until current_period_end.
     */
    public function cancel(UpeSubscription $subscription, ?int $cancelledBy = null): void
    {
        if (in_array($subscription->status, ['cancelled', 'expired'])) {
            throw new \RuntimeException("Subscription is already {$subscription->status}.");
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->audit->logSubscriptionEvent(
            $cancelledBy ?? $subscription->user_id,
            $cancelledBy ? 'admin' : 'user',
            $subscription->id,
            'cancelled'
        );
    }

    /**
     * Revoke a subscription immediately. Access ends now.
     */
    public function revoke(UpeSubscription $subscription, ?int $revokedBy = null): void
    {
        $subscription->update([
            'status' => 'expired',
            'current_period_end' => now(),
        ]);

        // NOTE: We do NOT update sale.status here.
        // AccessEngine derives access from subscription.status independently.
        // Mutating both subscription AND sale creates redundant coupling.
        // The sale remains in its current status; access is denied because
        // subscription.hasAccess() returns false when status='expired'.

        $this->audit->logSubscriptionEvent(
            $revokedBy ?? 0,
            $revokedBy ? 'admin' : 'system',
            $subscription->id,
            'revoked'
        );
    }

    /**
     * Get all subscriptions due for billing.
     */
    public function getDueBilling(): \Illuminate\Database\Eloquent\Collection
    {
        return UpeSubscription::dueBilling()->get();
    }

    /**
     * Check and expire subscriptions past grace period.
     * Called by scheduled job.
     */
    public function expireGracePeriods(): int
    {
        $count = 0;
        $graceSubscriptions = UpeSubscription::where('status', 'grace')->get();

        foreach ($graceSubscriptions as $sub) {
            $graceEnd = $sub->graceExpiresAt();
            if ($graceEnd && $graceEnd->isPast()) {
                $this->revoke($sub);
                $count++;
            }
        }

        return $count;
    }

    // ── Private Helpers ──

    private function handleFailedCharge(UpeSubscription $sub, UpeSubscriptionCycle $cycle): void
    {
        $totalFailedAttempts = $sub->cycles()
            ->where('status', 'failed')
            ->sum('attempts');

        if ($totalFailedAttempts < self::MAX_RETRIES) {
            $sub->update(['status' => 'past_due']);
        } else {
            // Check grace period
            $graceEnd = $sub->graceExpiresAt();
            if ($graceEnd && $graceEnd->isFuture()) {
                $sub->update(['status' => 'grace']);
            } else {
                $this->revoke($sub);
            }
        }

        Log::warning('Subscription charge failed', [
            'subscription_id' => $sub->id,
            'cycle_id' => $cycle->id,
            'total_attempts' => $totalFailedAttempts,
            'new_status' => $sub->fresh()->status,
        ]);
    }

    private function calculatePeriodEnd($start, string $interval): \Carbon\Carbon
    {
        $start = $start instanceof \Carbon\Carbon ? $start->copy() : \Carbon\Carbon::parse($start);

        return match ($interval) {
            'monthly' => $start->addMonth(),
            'quarterly' => $start->addMonths(3),
            'yearly' => $start->addYear(),
            default => $start->addMonth(),
        };
    }
}
