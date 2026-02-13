<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use App\Services\PaymentEngine\RefundEngine;
use App\Services\PaymentEngine\Policies\StandardRefundPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    private RefundEngine $refund;
    private PaymentLedgerService $ledger;
    private PaymentRequestService $requestService;

    public function __construct(
        RefundEngine $refund,
        PaymentLedgerService $ledger,
        PaymentRequestService $requestService
    ) {
        $this->refund = $refund;
        $this->ledger = $ledger;
        $this->requestService = $requestService;
    }

    /**
     * POST /upe/refund/estimate
     * Calculate refund estimate without executing.
     */
    public function estimate(Request $request): JsonResponse
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'refund_percent' => 'nullable|numeric|min:1|max:100',
        ]);

        $sale = UpeSale::findOrFail($request->input('sale_id'));
        $policy = new StandardRefundPolicy($request->input('refund_percent', 100));

        $estimate = $this->refund->calculateRefund($sale, $policy);

        return response()->json(['status' => 'success', 'data' => $estimate]);
    }

    /**
     * POST /upe/refund/request
     * User/support creates a refund request (enters workflow).
     */
    public function createRequest(Request $request): JsonResponse
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'reason' => 'required|string|max:1000',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $user = $request->user();
        $sale = UpeSale::findOrFail($request->input('sale_id'));

        if ($sale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $paymentRequest = $this->requestService->create(
            'refund',
            $user->id,
            $sale->id,
            [
                'reason' => $request->input('reason'),
                'requested_amount' => $request->input('amount'),
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $paymentRequest,
            'message' => 'Refund request created. Awaiting verification.',
        ], 201);
    }

    /**
     * POST /upe/refund/execute (Admin only)
     * Execute an approved refund request.
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'request_id' => 'required|integer|exists:upe_payment_requests,id',
            'amount' => 'required|numeric|min:0.01',
            'refund_percent' => 'nullable|numeric|min:1|max:100',
            'payment_method' => 'nullable|in:cash,bank_transfer,razorpay,paypal,stripe,payment_link,wallet,system',
        ]);

        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $paymentRequest = \App\Models\PaymentEngine\UpePaymentRequest::findOrFail($request->input('request_id'));

        $result = $this->requestService->execute($paymentRequest, $user->id, function ($req) use ($request, $user) {
            $sale = UpeSale::findOrFail($req->sale_id);
            $policy = new StandardRefundPolicy($request->input('refund_percent', 100));

            $entry = $this->refund->processRefund(
                sale: $sale,
                amount: $request->input('amount'),
                policy: $policy,
                reason: $req->payload['reason'] ?? 'Admin refund',
                processedBy: $user->id,
                paymentMethod: $request->input('payment_method', 'system'),
                idempotencyKey: "refund_req_{$req->id}"
            );

            return [
                'ledger_entry_id' => $entry->id,
                'refunded_amount' => $request->input('amount'),
                'new_balance' => $this->ledger->balance($sale->id),
                'sale_status' => $sale->fresh()->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
            'already_executed' => $result['already_executed'] ?? false,
        ]);
    }
}
