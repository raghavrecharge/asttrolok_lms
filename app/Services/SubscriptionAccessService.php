<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionAccess;
use App\Models\SubscriptionPayments;
use Illuminate\Support\Facades\Log;

/**
 * SubscriptionAccessService
 *
 * Single write layer for all SubscriptionAccess updates.
 *
 * This service is the ONE place where the renewal rule is enforced.
 * All payment flows (manual pay, auto-pay first, auto-pay recurring,
 * part-payment) must call syncAccessAfterPayment() rather than
 * duplicating this logic inline.
 *
 * ┌──────────────────────────────────────────────────────────────────┐
 * │ RENEWAL RULE                                                     │
 * │                                                                  │
 * │  1. Record a SubscriptionPayments row for this payment.          │
 * │  2. Count ALL payments by this user for this subscription.       │
 * │  3. access_content_count = video_count × payment_count           │
 * │  4. access_till_date     = max(now + days, existing_end + days)  │
 * │       Early renewal  → extends from existing end date.           │
 * │       Late renewal   → starts fresh from now.                    │
 * │  5. paid_no_of_subscriptions = payment_count                     │
 * └──────────────────────────────────────────────────────────────────┘
 */
class SubscriptionAccessService
{
    /**
     * Record a successful payment and synchronise SubscriptionAccess.
     *
     * Call this inside the payment-processing transaction BEFORE any UPE calls.
     * The SubscriptionPayments row is created here — do NOT create it beforehand.
     *
     * @param  int    $userId
     * @param  int    $subscriptionId
     * @param  float  $amount   Payment amount stored in SubscriptionPayments.
     * @return SubscriptionAccess   The updated or newly created access record.
     */
    public function syncAccessAfterPayment(int $userId, int $subscriptionId, float $amount): SubscriptionAccess
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        // 1. Record this payment
        SubscriptionPayments::create([
            'user_id'         => $userId,
            'subscription_id' => $subscriptionId,
            'amount'          => $amount,
            'created_at'      => time(),
        ]);

        // 2. Count all payments for this user/subscription (includes the one just created)
        $paymentCount = SubscriptionPayments::where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->count();

        // 3. access_content_count = items_per_payment (video_count) × payment_count
        //    NOTE: video_count is a legacy field name; business meaning = flat content items
        //    unlocked per payment cycle. See SubscriptionAccessResolver for details.
        $accessContentCount = $subscription->video_count * $paymentCount;

        // 4. Renewal rule: max(now + days, existing_end + days)
        $cycleSeconds   = (int) $subscription->access_days * 24 * 60 * 60;
        $freshEnd       = time() + $cycleSeconds;

        $existingAccess = SubscriptionAccess::where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->first();

        if ($existingAccess) {
            // Extend from existing end — handles early renewal correctly.
            // If user renews before expiry, they don't lose remaining days.
            $extendedEnd    = $existingAccess->access_till_date + $cycleSeconds;
            $accessTillDate = max($freshEnd, $extendedEnd);
        } else {
            $accessTillDate = $freshEnd;
        }

        // 5. Update or create SubscriptionAccess
        if ($existingAccess) {
            $existingAccess->update([
                'access_till_date'         => $accessTillDate,
                'access_content_count'     => $accessContentCount,
                'paid_no_of_subscriptions' => $paymentCount,
            ]);

            Log::info('SubscriptionAccessService: access updated after payment', [
                'user_id'              => $userId,
                'subscription_id'      => $subscriptionId,
                'payment_count'        => $paymentCount,
                'access_content_count' => $accessContentCount,
                'access_till_date'     => date('Y-m-d H:i:s', $accessTillDate),
            ]);

            return $existingAccess->fresh();
        }

        $newAccess = SubscriptionAccess::create([
            'user_id'                  => $userId,
            'subscription_id'          => $subscriptionId,
            'access_till_date'         => $accessTillDate,
            'access_content_count'     => $accessContentCount,
            'paid_no_of_subscriptions' => $paymentCount,
            'created_at'               => time(),
        ]);

        Log::info('SubscriptionAccessService: access created after payment', [
            'user_id'              => $userId,
            'subscription_id'      => $subscriptionId,
            'payment_count'        => $paymentCount,
            'access_content_count' => $accessContentCount,
            'access_till_date'     => date('Y-m-d H:i:s', $accessTillDate),
        ]);

        return $newAccess;
    }

    /**
     * Grant free-enrollment access (no payment).
     *
     * Sets access_content_count = 0 so the resolver surfaces only
     * free_video_count items. Called from SubscriptionController::free().
     *
     * Idempotent: if the user already has an access record this is a no-op
     * (will not downgrade a paid user back to free).
     *
     * @param  int  $userId
     * @param  int  $subscriptionId
     * @param  int  $accessTillDate  Unix timestamp of expiry.
     * @return SubscriptionAccess
     */
    public function grantFreeAccess(int $userId, int $subscriptionId, int $accessTillDate): SubscriptionAccess
    {
        $existing = SubscriptionAccess::where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->first();

        if ($existing) {
            // Already enrolled — do not overwrite paid access.
            return $existing;
        }

        $access = SubscriptionAccess::create([
            'user_id'                  => $userId,
            'subscription_id'          => $subscriptionId,
            'access_till_date'         => $accessTillDate,
            'access_content_count'     => 0, // free: resolver adds free_video_count on top
            'paid_no_of_subscriptions' => 0,
            'created_at'               => time(),
        ]);

        Log::info('SubscriptionAccessService: free access granted', [
            'user_id'         => $userId,
            'subscription_id' => $subscriptionId,
            'access_till_date' => date('Y-m-d H:i:s', $accessTillDate),
        ]);

        return $access;
    }
}
