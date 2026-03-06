<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionAccess;
use App\User;

/**
 * SubscriptionAccessResolver
 *
 * Single source of truth for subscription content-access computation.
 * Controllers and views should consume this resolver instead of doing
 * inline access math.
 *
 * ┌──────────────────────────────────────────────────────────────────┐
 * │ CONTENT UNLOCK RULE (production Path A)                          │
 * │                                                                  │
 * │  unlockedItemCount = access_content_count + free_video_count     │
 * │                                                                  │
 * │  where:                                                          │
 * │    access_content_count = video_count × paid_payment_count       │
 * │    free_video_count     = Subscription.free_video_count (config) │
 * │                                                                  │
 * │  The user sees the first `unlockedItemCount` items from the      │
 * │  flat ordered SubscriptionWebinarChapterItems list (order ASC).  │
 * │  Items beyond that index are locked.                             │
 * │                                                                  │
 * │  Access is valid only while access_till_date > now().            │
 * │  On expiry: paid items = 0, only free items remain accessible.   │
 * └──────────────────────────────────────────────────────────────────┘
 *
 * NAMING NOTE:
 *   The DB field `video_count` is a legacy name. In production it controls
 *   how many flat SubscriptionWebinarChapterItems are unlocked per payment
 *   cycle — not specifically "videos". Think of it as `items_per_payment`.
 *
 * SOURCE OF TRUTH DESIGN:
 *   - UPE stack  (UpeSubscription, UpeSale)   = billing truth
 *   - Legacy stack (SubscriptionAccess)        = derived content-access cache
 *   - This resolver                            = computation layer over legacy cache
 */
class SubscriptionAccessResolver
{
    private Subscription $subscription;
    private ?SubscriptionAccess $access;
    private ?User $user;

    /**
     * Total flat item count — used only for the admin bypass (user id=1).
     * Pass count($chapterItems) from the controller.
     */
    private int $totalItemCount;

    public function __construct(
        Subscription $subscription,
        ?SubscriptionAccess $access,
        ?User $user,
        int $totalItemCount = 0
    ) {
        $this->subscription   = $subscription;
        $this->access         = $access;
        $this->user           = $user;
        $this->totalItemCount = $totalItemCount;
    }

    // ── Admin bypass ──────────────────────────────────────────────

    /**
     * User id=1 (admin) bypasses all access checks and sees all items.
     * This preserves the existing admin-bypass behaviour.
     */
    public function isAdminBypass(): bool
    {
        // Use (int) cast to match original loose == 1 behaviour across all Laravel versions.
        return $this->user && (int) $this->user->id === 1;
    }

    // ── Access state ──────────────────────────────────────────────

    /**
     * Returns true if the user has an active (non-expired) subscription record.
     */
    public function hasActiveAccess(): bool
    {
        if ($this->isAdminBypass()) {
            return true;
        }
        if (!$this->access) {
            return false;
        }
        return $this->access->access_till_date > time();
    }

    /**
     * Returns true if the user is enrolled at all (free or paid, active or expired).
     */
    public function isEnrolled(): bool
    {
        return $this->access !== null || $this->isAdminBypass();
    }

    // ── Item counts ───────────────────────────────────────────────

    /**
     * How many content items does each successful payment unlock?
     *
     * Source: Subscription.video_count
     * Business meaning: flat content items added to the unlocked list per payment cycle.
     */
    public function itemsPerPayment(): int
    {
        return max(0, (int) ($this->subscription->video_count ?? 0));
    }

    /**
     * How many free items are always accessible regardless of payment state?
     *
     * Source: Subscription.free_video_count
     */
    public function freeItemCount(): int
    {
        return max(0, (int) ($this->subscription->free_video_count ?? 0));
    }

    /**
     * How many items have been unlocked through paid payments?
     *
     * = video_count × paid_payment_count  (from SubscriptionAccess.access_content_count)
     *
     * Returns 0 if access has expired or no access record exists.
     */
    public function paidUnlockedCount(): int
    {
        if ($this->isAdminBypass()) {
            return $this->totalItemCount;
        }
        if (!$this->hasActiveAccess()) {
            return 0;
        }
        if (!$this->access) {
            return 0;
        }
        return max(0, (int) $this->access->access_content_count);
    }

    /**
     * How many payments has this user successfully completed?
     *
     * Source: SubscriptionAccess.paid_no_of_subscriptions
     */
    public function paidPaymentCount(): int
    {
        if (!$this->access) {
            return 0;
        }
        return max(0, (int) $this->access->paid_no_of_subscriptions);
    }

    /**
     * Total unlocked item count — the primary access boundary.
     *
     * FORMULA:  paidUnlockedCount + freeItemCount
     *
     * Items at position 1..unlockedItemCount in the ordered flat list are accessible.
     * Items beyond that index are locked.
     *
     * Special cases:
     *   - Admin (id=1)  → totalItemCount (all items)
     *   - Expired user  → freeItemCount only (paid items = 0)
     *   - Not enrolled  → 0
     */
    public function unlockedItemCount(): int
    {
        if ($this->isAdminBypass()) {
            return $this->totalItemCount;
        }

        // Expired or no access record: paid portion = 0
        $paid = $this->hasActiveAccess() ? $this->paidUnlockedCount() : 0;

        return $paid + $this->freeItemCount();
    }

    // ── Access timing ─────────────────────────────────────────────

    /**
     * Unix timestamp when the user's access expires.
     * Returns null if not enrolled or admin (admin has no expiry).
     */
    public function accessExpiresAt(): ?int
    {
        if ($this->isAdminBypass()) {
            return null;
        }
        if (!$this->access) {
            return null;
        }
        return (int) $this->access->access_till_date;
    }

    // ── Status helpers ────────────────────────────────────────────

    /** True if enrolled but zero paid payments (free-only tier). */
    public function isFreeOnly(): bool
    {
        return $this->isEnrolled() && $this->paidPaymentCount() === 0;
    }

    /** True if has active paid subscription (payment made and not expired). */
    public function isPaidActive(): bool
    {
        if ($this->isAdminBypass()) {
            return true;
        }
        return $this->hasActiveAccess() && $this->paidUnlockedCount() > 0;
    }

    /** True if user made payments but access has since expired. */
    public function isPaidExpired(): bool
    {
        if (!$this->access) {
            return false;
        }
        return $this->paidPaymentCount() > 0 && !$this->hasActiveAccess();
    }

    // ── View model ────────────────────────────────────────────────

    /**
     * Returns a named array suitable for merging into controller $data / Blade view.
     *
     * Use these explicit keys in templates instead of the legacy $limit/$limit1 magic.
     *
     * Key `limit` is retained as a backward-compatible alias for unlockedItemCount
     * so that any existing blade logic that still reads $limit continues to work.
     */
    public function toViewData(): array
    {
        return [
            // Primary unlock boundary
            'unlockedItemCount' => $this->unlockedItemCount(),

            // Breakdown
            'freeItemCount'     => $this->freeItemCount(),
            'paidUnlockedCount' => $this->paidUnlockedCount(),
            'itemsPerPayment'   => $this->itemsPerPayment(),
            'paidPaymentCount'  => $this->paidPaymentCount(),

            // Timing
            'accessExpiresAt'   => $this->accessExpiresAt(),

            // Status flags
            'hasActiveAccess'   => $this->hasActiveAccess(),
            'isFreeOnly'        => $this->isFreeOnly(),
            'isPaidActive'      => $this->isPaidActive(),
            'isPaidExpired'     => $this->isPaidExpired(),
            'isEnrolled'        => $this->isEnrolled(),

            // Backward-compatible alias (replaces old $data['limit'])
            'limit'             => $this->unlockedItemCount(),
        ];
    }
}
