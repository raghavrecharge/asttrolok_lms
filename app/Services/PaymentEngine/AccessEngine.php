<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeSubscription;
use App\Models\PaymentEngine\UpeMentorBadge;
use App\Models\PaymentEngine\UpeSupportAction;
use Illuminate\Support\Facades\Cache;

class AccessEngine
{
    private PaymentLedgerService $ledger;

    const CACHE_TTL = 300; // 5 minutes
    const CACHE_PREFIX = 'upe_access_';

    public function __construct(PaymentLedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * Check if a user has access to a product.
     * Access is DERIVED — never stored as truth.
     *
     * Checks in order:
     * 1. Active sale exists for (user, product)?
     * 2. Sale status allows access?
     * 3. Ledger balance meets threshold (installments)?
     * 4. Subscription is active/trial/grace?
     * 5. Sale validity period not expired?
     *
     * @return AccessResult
     */
    public function hasAccess(int $userId, int $productId): AccessResult
    {
        $cacheKey = self::CACHE_PREFIX . "{$userId}_{$productId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $productId) {
            return $this->computeAccess($userId, $productId);
        });
    }

    /**
     * Get access type without caching (for admin views).
     */
    public function computeAccess(int $userId, int $productId): AccessResult
    {
        // Find all sales for this user+product, newest first
        $sales = UpeSale::where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
            ->orderByDesc('id')
            ->get();

        // 1. Check sale-based access
        if ($sales->isNotEmpty()) {
            foreach ($sales as $sale) {
                $result = $this->evaluateSale($sale);
                if ($result->hasAccess) {
                    return $result;
                }
            }
        }

        // 2. Check subscription access (may not be tied to same sale)
        $subscription = UpeSubscription::where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->first();

        if ($subscription && $subscription->hasAccess()) {
            return AccessResult::granted('subscription', $sales->first(), $subscription);
        }

        // 3. Check temporary access via support actions
        $tempAccess = UpeSupportAction::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('action_type', UpeSupportAction::ACTION_TEMPORARY_ACCESS)
            ->where('status', UpeSupportAction::STATUS_EXECUTED)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($tempAccess) {
            return AccessResult::granted('temporary', $sales->first(), null, [
                'expires_at'        => $tempAccess->expires_at?->toDateTimeString(),
                'support_action_id' => $tempAccess->id,
            ]);
        }

        // 4. Check mentor badge — grants access to ANY product without sale
        if (UpeMentorBadge::hasBadge($userId)) {
            return AccessResult::granted('mentor', null, null, ['badge' => true]);
        }

        $reason = $sales->isEmpty()
            ? 'No sale found for this user and product.'
            : 'No active access found. Sales exist but do not grant access.';

        return AccessResult::denied($reason);
    }

    /**
     * Evaluate a single sale for access.
     */
    private function evaluateSale(UpeSale $sale): AccessResult
    {
        // Check sale status — installment sales may be 'pending_payment' and still grant access
        $allowedStatuses = ['active', 'partially_refunded'];
        if ($sale->pricing_mode === 'installment') {
            $allowedStatuses[] = 'pending_payment';
        }
        if (!in_array($sale->status, $allowedStatuses)) {
            return AccessResult::denied("Sale #{$sale->id} status '{$sale->status}' does not grant access.");
        }

        // Check validity period
        if ($sale->valid_until !== null && $sale->valid_until->isPast()) {
            return AccessResult::denied("Sale #{$sale->id} validity expired at {$sale->valid_until}.");
        }

        // Check pricing mode specifics
        switch ($sale->pricing_mode) {
            case 'installment':
                return $this->evaluateInstallmentAccess($sale);

            case 'subscription':
                return $this->evaluateSubscriptionAccess($sale);

            case 'free':
                return AccessResult::granted('free', $sale);

            case 'full':
            default:
                // For full payment, balance must be positive
                $balance = $this->ledger->balance($sale->id);
                if ($balance > 0) {
                    return AccessResult::granted('paid', $sale);
                }
                return AccessResult::denied("Sale #{$sale->id} has zero or negative balance.");
        }
    }

    /**
     * Evaluate access for installment-based sales.
     * Access is granted if the installment plan is active (even if not fully paid).
     */
    private function evaluateInstallmentAccess(UpeSale $sale): AccessResult
    {
        $plan = $sale->installmentPlan;

        if (!$plan) {
            return AccessResult::denied("Sale #{$sale->id} is installment-mode but has no plan.");
        }

        if (in_array($plan->status, ['active', 'completed'])) {
            // Check for severe overdue (e.g., > 2 overdue installments → suspend)
            $overdueCount = $plan->schedules()->where('status', 'overdue')->count();
            if ($overdueCount >= 3) {
                return AccessResult::denied("Installment plan has {$overdueCount} overdue payments. Access suspended.");
            }

            return AccessResult::granted('paid', $sale, null, [
                'plan_status' => $plan->status,
                'overdue_count' => $overdueCount,
            ]);
        }

        return AccessResult::denied("Installment plan status '{$plan->status}' does not grant access.");
    }

    /**
     * Evaluate access for subscription-based sales.
     */
    private function evaluateSubscriptionAccess(UpeSale $sale): AccessResult
    {
        $subscription = $sale->subscription;

        if (!$subscription) {
            return AccessResult::denied("Sale #{$sale->id} is subscription-mode but has no subscription.");
        }

        if ($subscription->hasAccess()) {
            return AccessResult::granted('subscription', $sale, $subscription);
        }

        return AccessResult::denied("Subscription status '{$subscription->status}' does not grant access.");
    }

    /**
     * Invalidate access cache for a user+product.
     */
    public function invalidate(int $userId, int $productId): void
    {
        Cache::forget(self::CACHE_PREFIX . "{$userId}_{$productId}");
    }

    /**
     * Invalidate all access cache for a user.
     */
    public function invalidateUser(int $userId): void
    {
        // Get all products this user has sales for
        $productIds = UpeSale::where('user_id', $userId)->distinct()->pluck('product_id');
        foreach ($productIds as $productId) {
            $this->invalidate($userId, $productId);
        }
    }

    /**
     * Invalidate cache for all users of a product.
     */
    public function invalidateProduct(int $productId): void
    {
        $userIds = UpeSale::where('product_id', $productId)->distinct()->pluck('user_id');
        foreach ($userIds as $userId) {
            $this->invalidate($userId, $productId);
        }
    }

    /**
     * Bulk check access for a user across multiple products.
     */
    public function bulkCheck(int $userId, array $productIds): array
    {
        $results = [];
        foreach ($productIds as $pid) {
            $results[$pid] = $this->hasAccess($userId, $pid);
        }
        return $results;
    }
}

/**
 * Value object representing an access check result.
 */
class AccessResult
{
    public bool $hasAccess;
    public string $accessType; // paid, free, temporary, subscription, none
    public ?UpeSale $sale;
    public ?UpeSubscription $subscription;
    public string $reason;
    public array $metadata;

    private function __construct(
        bool $hasAccess,
        string $accessType,
        ?UpeSale $sale,
        ?UpeSubscription $subscription,
        string $reason,
        array $metadata = []
    ) {
        $this->hasAccess = $hasAccess;
        $this->accessType = $accessType;
        $this->sale = $sale;
        $this->subscription = $subscription;
        $this->reason = $reason;
        $this->metadata = $metadata;
    }

    public static function granted(
        string $type,
        ?UpeSale $sale = null,
        ?UpeSubscription $subscription = null,
        array $metadata = []
    ): self {
        return new self(true, $type, $sale, $subscription, 'Access granted.', $metadata);
    }

    public static function denied(string $reason): self
    {
        return new self(false, 'none', null, null, $reason);
    }

    public function toArray(): array
    {
        return [
            'has_access' => $this->hasAccess,
            'access_type' => $this->accessType,
            'sale_id' => $this->sale?->id,
            'subscription_id' => $this->subscription?->id,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
        ];
    }
}
