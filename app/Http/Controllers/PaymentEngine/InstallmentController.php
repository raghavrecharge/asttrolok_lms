<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\InstallmentEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    private InstallmentEngine $installment;
    private PaymentLedgerService $ledger;
    private PaymentRequestService $requestService;

    public function __construct(
        InstallmentEngine $installment,
        PaymentLedgerService $ledger,
        PaymentRequestService $requestService
    ) {
        $this->installment = $installment;
        $this->ledger = $ledger;
        $this->requestService = $requestService;
    }

    /**
     * POST /upe/installment/create-plan
     * Create an installment plan for a sale.
     */
    public function createPlan(Request $request): JsonResponse
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'num_installments' => 'required|integer|min:2|max:24',
            'plan_type' => 'nullable|in:standard,flexible',
            'custom_schedule' => 'nullable|array',
            'custom_schedule.*.amount' => 'required_with:custom_schedule|numeric|min:0.01',
            'custom_schedule.*.due_date' => 'required_with:custom_schedule|date|after:today',
        ]);

        $user = $request->user();
        $sale = UpeSale::findOrFail($request->input('sale_id'));

        if ($sale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $plan = $this->installment->createPlan(
            sale: $sale,
            numInstallments: $request->input('num_installments'),
            planType: $request->input('plan_type', 'standard'),
            customSchedule: $request->input('custom_schedule', []),
            approvedBy: $user->isAdmin() ? $user->id : null
        );

        return response()->json([
            'status' => 'success',
            'data' => $plan->load('schedules'),
        ], 201);
    }

    /**
     * GET /upe/installment/plan/{id}
     * Show installment plan with schedules.
     */
    public function showPlan(int $id, Request $request): JsonResponse
    {
        $plan = UpeInstallmentPlan::with('schedules')->findOrFail($id);
        $user = $request->user();
        $sale = $plan->sale;

        if ($sale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'plan' => $plan,
                'total_paid' => $plan->totalPaid(),
                'total_remaining' => $plan->totalRemaining(),
                'next_due' => $plan->nextDueSchedule(),
            ],
        ]);
    }

    /**
     * POST /upe/installment/pay
     * Record a payment against the next due installment.
     */
    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:upe_installment_plans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,razorpay,paypal,stripe,payment_link,wallet',
            'gateway_transaction_id' => 'nullable|string|max:255',
            'gateway_response' => 'nullable|array',
        ]);

        $user = $request->user();
        $plan = UpeInstallmentPlan::findOrFail($request->input('plan_id'));
        $sale = $plan->sale;

        if ($sale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $result = $this->installment->recordPayment(
            plan: $plan,
            amount: $request->input('amount'),
            paymentMethod: $request->input('payment_method'),
            gatewayTransactionId: $request->input('gateway_transaction_id'),
            gatewayResponse: $request->input('gateway_response'),
            processedBy: $user->id
        );

        return response()->json(['status' => 'success', 'data' => $result]);
    }

    /**
     * POST /upe/installment/restructure-request
     * Request restructuring of an installment plan (enters workflow).
     */
    public function restructureRequest(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:upe_installment_plans,id',
            'new_num_installments' => 'required|integer|min:2|max:24',
            'reason' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $plan = UpeInstallmentPlan::findOrFail($request->input('plan_id'));

        $paymentRequest = $this->requestService->create(
            'restructure',
            $user->id,
            $plan->sale_id,
            [
                'plan_id' => $plan->id,
                'new_num_installments' => $request->input('new_num_installments'),
                'reason' => $request->input('reason'),
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $paymentRequest,
            'message' => 'Restructure request created. Awaiting approval.',
        ], 201);
    }

    /**
     * POST /upe/installment/restructure-execute (Admin only)
     * Execute an approved restructure request.
     */
    public function restructureExecute(Request $request): JsonResponse
    {
        $request->validate([
            'request_id' => 'required|integer|exists:upe_payment_requests,id',
        ]);

        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $paymentRequest = \App\Models\PaymentEngine\UpePaymentRequest::findOrFail($request->input('request_id'));

        $result = $this->requestService->execute($paymentRequest, $user->id, function ($req) use ($user) {
            $planId = $req->payload['plan_id'] ?? null;
            $newNum = $req->payload['new_num_installments'] ?? 6;

            $oldPlan = UpeInstallmentPlan::findOrFail($planId);
            $newPlan = $this->installment->restructure($oldPlan, $newNum, [], $user->id);

            return [
                'old_plan_id' => $oldPlan->id,
                'new_plan_id' => $newPlan->id,
                'new_num_installments' => $newNum,
                'remaining_amount' => $newPlan->total_amount,
            ];
        });

        return response()->json(['status' => 'success', 'data' => $result]);
    }
}
