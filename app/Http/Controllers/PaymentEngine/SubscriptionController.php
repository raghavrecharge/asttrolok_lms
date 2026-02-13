<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeSubscription;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use App\Services\PaymentEngine\PurchaseEngine;
use App\Services\PaymentEngine\SubscriptionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private SubscriptionEngine $subscription;
    private PurchaseEngine $purchase;
    private PaymentLedgerService $ledger;
    private PaymentRequestService $requestService;

    public function __construct(
        SubscriptionEngine $subscription,
        PurchaseEngine $purchase,
        PaymentLedgerService $ledger,
        PaymentRequestService $requestService
    ) {
        $this->subscription = $subscription;
        $this->purchase = $purchase;
        $this->ledger = $ledger;
        $this->requestService = $requestService;
    }

    /**
     * POST /upe/subscription/create
     * Subscribe to a product.
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:upe_products,id',
            'billing_interval' => 'nullable|in:monthly,quarterly,yearly',
            'trial_days' => 'nullable|integer|min:0|max:90',
        ]);

        $user = $request->user();
        $product = UpeProduct::findOrFail($request->input('product_id'));

        $sale = $this->purchase->createSale(
            userId: $user->id,
            product: $product,
            pricingMode: 'subscription',
            saleType: 'paid'
        );

        $sub = $this->subscription->create(
            userId: $user->id,
            product: $product,
            sale: $sale,
            billingAmount: (float) $product->base_fee,
            billingInterval: $request->input('billing_interval', 'monthly'),
            trialDays: $request->input('trial_days', 0),
            gracePeriodDays: 3
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'subscription' => $sub,
                'sale' => $sale,
            ],
        ], 201);
    }

    /**
     * GET /upe/subscription/my
     * List current user's subscriptions.
     */
    public function mySubscriptions(Request $request): JsonResponse
    {
        $user = $request->user();

        $subs = UpeSubscription::where('user_id', $user->id)
            ->with(['product', 'sale'])
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $subs]);
    }

    /**
     * GET /upe/subscription/{id}
     * Show subscription details with billing history.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $sub = UpeSubscription::with(['product', 'sale', 'cycles'])->findOrFail($id);
        $user = $request->user();

        if ($sub->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'subscription' => $sub,
                'has_access' => $sub->hasAccess(),
                'ledger_balance' => $this->ledger->balance($sub->sale_id),
            ],
        ]);
    }

    /**
     * POST /upe/subscription/{id}/cancel
     * Cancel a subscription. Access continues until period end.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $sub = UpeSubscription::findOrFail($id);
        $user = $request->user();

        if ($sub->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $this->subscription->cancel($sub, $user->id);

        return response()->json([
            'status' => 'success',
            'data' => $sub->fresh(),
            'message' => 'Subscription cancelled. Access continues until ' . $sub->current_period_end,
        ]);
    }

    /**
     * POST /upe/subscription/{id}/revoke (Admin only)
     * Revoke subscription immediately.
     */
    public function revoke(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $sub = UpeSubscription::findOrFail($id);
        $this->subscription->revoke($sub, $user->id);

        return response()->json([
            'status' => 'success',
            'data' => $sub->fresh(),
            'message' => 'Subscription revoked immediately.',
        ]);
    }
}
