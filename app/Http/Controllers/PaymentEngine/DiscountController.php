<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeDiscount;
use App\Services\PaymentEngine\DiscountEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    private DiscountEngine $discount;

    public function __construct(DiscountEngine $discount)
    {
        $this->discount = $discount;
    }

    /**
     * POST /upe/discount/validate
     * Validate a coupon code.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:64',
            'product_id' => 'required|integer|exists:upe_products,id',
        ]);

        $user = $request->user();
        $product = \App\Models\PaymentEngine\UpeProduct::findOrFail($request->input('product_id'));

        $result = $this->discount->validate(
            $request->input('code'),
            $user->id,
            $product->id,
            (float) $product->base_fee
        );

        return response()->json([
            'status' => $result['valid'] ? 'success' : 'error',
            'data' => [
                'valid' => $result['valid'],
                'discount_amount' => $result['amount'],
                'effective_price' => max(0, (float) $product->base_fee - $result['amount']),
                'reason' => $result['reason'],
            ],
        ]);
    }

    /**
     * GET /upe/discount/list (Admin only)
     * List all discounts.
     */
    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $query = UpeDiscount::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $discounts = $query->orderByDesc('id')->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $discounts]);
    }

    /**
     * POST /upe/discount/create (Admin only)
     * Create a new discount/coupon.
     */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $request->validate([
            'code' => 'nullable|string|max:64|unique:upe_discounts,code',
            'discount_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'max_discount_amount' => 'nullable|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'scope' => 'required|in:global,product,category,user',
            'scope_ids' => 'nullable|array',
            'allowed_roles' => 'nullable|array',
            'max_uses_total' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'stackable' => 'nullable|boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $discount = UpeDiscount::create(array_merge(
            $request->only([
                'code', 'discount_type', 'value', 'max_discount_amount',
                'min_order_amount', 'scope', 'scope_ids', 'allowed_roles',
                'max_uses_total', 'max_uses_per_user', 'stackable',
                'valid_from', 'valid_until',
            ]),
            ['created_by' => $user->id, 'status' => 'active']
        ));

        return response()->json(['status' => 'success', 'data' => $discount], 201);
    }

    /**
     * PUT /upe/discount/{id}/disable (Admin only)
     */
    public function disable(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $discount = UpeDiscount::findOrFail($id);
        $discount->update(['status' => 'disabled']);

        return response()->json(['status' => 'success', 'data' => $discount->fresh()]);
    }
}
