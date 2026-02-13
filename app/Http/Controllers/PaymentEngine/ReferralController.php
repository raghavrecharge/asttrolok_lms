<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeReferral;
use App\Services\PaymentEngine\ReferralEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    private ReferralEngine $referral;

    public function __construct(ReferralEngine $referral)
    {
        $this->referral = $referral;
    }

    /**
     * POST /upe/referral/generate
     * Generate a referral code for the current user.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'bonus_amount' => 'nullable|numeric|min:0',
            'bonus_type' => 'nullable|in:wallet_credit,discount_credit',
        ]);

        $user = $request->user();

        $referral = $this->referral->generateCode(
            $user->id,
            $request->input('bonus_amount', 0),
            $request->input('bonus_type', 'wallet_credit')
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'referral_code' => $referral->referral_code,
                'referral_link' => url('/ref/' . $referral->referral_code),
                'bonus_amount' => $referral->bonus_amount,
                'bonus_type' => $referral->bonus_type,
            ],
        ]);
    }

    /**
     * POST /upe/referral/track
     * Track a signup via referral code (called during registration).
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'referral_code' => 'required|string|max:32',
        ]);

        $user = $request->user();

        try {
            $referral = $this->referral->trackSignup($request->input('referral_code'), $user->id);

            return response()->json([
                'status' => 'success',
                'data' => $referral,
                'message' => $referral ? 'Referral tracked.' : 'Invalid referral code.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /upe/referral/my
     * List current user's referrals (as referrer).
     */
    public function myReferrals(Request $request): JsonResponse
    {
        $user = $request->user();

        $referrals = UpeReferral::where('referrer_user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $referrals]);
    }
}
