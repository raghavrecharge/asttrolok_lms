<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeReferral;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Support\Str;

class ReferralEngine
{
    private PaymentLedgerService $ledger;
    private AuditService $audit;

    public function __construct(PaymentLedgerService $ledger, AuditService $audit)
    {
        $this->ledger = $ledger;
        $this->audit = $audit;
    }

    /**
     * Generate a unique referral link/code for a user.
     */
    public function generateCode(int $userId, float $bonusAmount = 0, string $bonusType = 'wallet_credit'): UpeReferral
    {
        // Check if user already has an unused referral code
        $existing = UpeReferral::where('referrer_user_id', $userId)
            ->whereNull('referred_user_id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $code = strtoupper(Str::random(8));

        // Ensure uniqueness
        while (UpeReferral::where('referral_code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }

        return UpeReferral::create([
            'referrer_user_id' => $userId,
            'referral_code' => $code,
            'bonus_type' => $bonusType,
            'bonus_amount' => $bonusAmount,
            'bonus_status' => 'pending',
        ]);
    }

    /**
     * Track a signup via referral code.
     * Called when a new user registers using a referral link.
     *
     * @throws \RuntimeException on self-referral or invalid code
     */
    public function trackSignup(string $referralCode, int $newUserId): ?UpeReferral
    {
        $referral = UpeReferral::byCode($referralCode)->first();

        if (!$referral) {
            return null; // Invalid code, silently ignore
        }

        if ($referral->isSelfReferral($newUserId)) {
            throw new \RuntimeException('Self-referral is not allowed.');
        }

        if ($referral->referred_user_id !== null) {
            // Code already used; generate a new one for the referrer
            // but allow tracking via a new referral entry
            $referral = UpeReferral::create([
                'referrer_user_id' => $referral->referrer_user_id,
                'referral_code' => $referral->referral_code . '_' . Str::random(4),
                'bonus_type' => $referral->bonus_type,
                'bonus_amount' => $referral->bonus_amount,
                'bonus_status' => 'pending',
                'referred_user_id' => $newUserId,
            ]);

            return $referral;
        }

        $referral->update([
            'referred_user_id' => $newUserId,
        ]);

        return $referral;
    }

    /**
     * Link a referral to a specific sale (called during purchase).
     */
    public function linkToSale(UpeReferral $referral, UpeSale $sale): void
    {
        if ($referral->referred_sale_id !== null) {
            return; // Already linked
        }

        $referral->update(['referred_sale_id' => $sale->id]);
    }

    /**
     * Credit the referral bonus to the referrer.
     * Called ONLY after the referred user's payment is confirmed.
     *
     * @throws \RuntimeException if already credited or ineligible
     */
    public function creditBonus(UpeReferral $referral): ?UpeLedgerEntry
    {
        if (!$referral->isPending()) {
            return null; // Already processed
        }

        if ($referral->bonus_amount <= 0) {
            $referral->update(['bonus_status' => 'ineligible']);
            return null;
        }

        if (!$referral->referred_sale_id) {
            return null; // No sale linked yet
        }

        // Verify the referred sale has actual payment
        $sale = UpeSale::find($referral->referred_sale_id);
        if (!$sale || !$this->ledger->hasPayment($sale->id)) {
            return null; // No confirmed payment yet
        }

        // Find or create a sale for the referrer to credit the bonus against
        // For wallet credits, we create a system ledger entry
        $referrerActiveSale = UpeSale::where('user_id', $referral->referrer_user_id)
            ->accessible()
            ->orderByDesc('id')
            ->first();

        if (!$referrerActiveSale) {
            // No active sale to credit against — mark as pending for later
            return null;
        }

        $entry = $this->ledger->recordReferralBonus(
            saleId: $referrerActiveSale->id,
            amount: (float) $referral->bonus_amount,
            referralId: $referral->id
        );

        $referral->update([
            'bonus_status' => 'credited',
            'bonus_ledger_entry_id' => $entry->id,
            'credited_at' => now(),
        ]);

        $this->audit->log(
            0, 'system', 'referral.bonus_credited', 'referral', $referral->id, null, [
                'referrer_user_id' => $referral->referrer_user_id,
                'referred_user_id' => $referral->referred_user_id,
                'bonus_amount' => $referral->bonus_amount,
                'bonus_type' => $referral->bonus_type,
                'credited_to_sale' => $referrerActiveSale->id,
            ]
        );

        return $entry;
    }

    /**
     * Process all pending referral bonuses where payment has been confirmed.
     * Called by scheduled job.
     */
    public function processPendingBonuses(): int
    {
        $pending = UpeReferral::pending()
            ->whereNotNull('referred_sale_id')
            ->where('bonus_amount', '>', 0)
            ->get();

        $credited = 0;
        foreach ($pending as $referral) {
            $entry = $this->creditBonus($referral);
            if ($entry) {
                $credited++;
            }
        }

        return $credited;
    }
}
