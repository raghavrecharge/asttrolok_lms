<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\AdjustmentEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use App\Services\PaymentEngine\Policies\StandardAdjustmentPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdjustmentController extends Controller
{
    private AdjustmentEngine $adjustment;
    private PaymentLedgerService $ledger;
    private PaymentRequestService $requestService;

    public function __construct(
        AdjustmentEngine $adjustment,
        PaymentLedgerService $ledger,
        PaymentRequestService $requestService
    ) {
        $this->adjustment = $adjustment;
        $this->ledger = $ledger;
        $this->requestService = $requestService;
    }

    /**
     * POST /upe/adjustment/estimate
     * Calculate adjustment estimate without executing.
     */
    public function estimate(Request $request): JsonResponse
    {
        $request->validate([
            'source_sale_id' => 'required|integer|exists:upe_sales,id',
            'target_product_id' => 'required|integer|exists:upe_products,id',
            'adjustment_percent' => 'nullable|numeric|min:1|max:100',
        ]);

        $sourceSale = UpeSale::findOrFail($request->input('source_sale_id'));
        $targetProduct = UpeProduct::findOrFail($request->input('target_product_id'));
        $policy = new StandardAdjustmentPolicy($request->input('adjustment_percent', 80));

        $estimate = $this->adjustment->calculate($sourceSale, $targetProduct, $policy);

        return response()->json(['status' => 'success', 'data' => $estimate]);
    }

    /**
     * POST /upe/adjustment/request
     * Create an adjustment request (enters workflow).
     */
    public function createRequest(Request $request): JsonResponse
    {
        $request->validate([
            'source_sale_id' => 'required|integer|exists:upe_sales,id',
            'target_product_id' => 'required|integer|exists:upe_products,id',
            'adjustment_type' => 'required|in:upgrade,cross_course,wrong_course',
            'reason' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $sourceSale = UpeSale::findOrFail($request->input('source_sale_id'));

        if ($sourceSale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $paymentRequest = $this->requestService->create(
            'adjustment',
            $user->id,
            $sourceSale->id,
            [
                'source_sale_id' => $request->input('source_sale_id'),
                'target_product_id' => $request->input('target_product_id'),
                'adjustment_type' => $request->input('adjustment_type'),
                'reason' => $request->input('reason'),
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $paymentRequest,
            'message' => 'Adjustment request created. Awaiting approval.',
        ], 201);
    }

    /**
     * POST /upe/adjustment/execute (Admin only)
     * Execute an approved adjustment request.
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'request_id' => 'required|integer|exists:upe_payment_requests,id',
            'adjustment_percent' => 'nullable|numeric|min:1|max:100',
        ]);

        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $paymentRequest = \App\Models\PaymentEngine\UpePaymentRequest::findOrFail($request->input('request_id'));

        $result = $this->requestService->execute($paymentRequest, $user->id, function ($req) use ($request, $user) {
            $payload = $req->payload;
            $sourceSale = UpeSale::findOrFail($payload['source_sale_id']);
            $targetProduct = UpeProduct::findOrFail($payload['target_product_id']);
            $policy = new StandardAdjustmentPolicy($request->input('adjustment_percent', 80));

            $adjResult = $this->adjustment->execute(
                sourceSale: $sourceSale,
                targetProduct: $targetProduct,
                policy: $policy,
                adjustmentType: $payload['adjustment_type'],
                approvedBy: $user->id
            );

            return [
                'adjustment_id' => $adjResult['adjustment']->id,
                'target_sale_id' => $adjResult['target_sale']->id,
                'remaining_to_pay' => $adjResult['remaining_to_pay'],
            ];
        });

        return response()->json(['status' => 'success', 'data' => $result]);
    }
}
