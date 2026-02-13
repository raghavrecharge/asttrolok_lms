<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeAuditLog;
use App\Models\PaymentEngine\UpePaymentRequest;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use App\Services\PaymentEngine\PurchaseEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    private PaymentLedgerService $ledger;
    private PaymentRequestService $requestService;
    private PurchaseEngine $purchase;
    private AccessEngine $access;

    public function __construct(
        PaymentLedgerService $ledger,
        PaymentRequestService $requestService,
        PurchaseEngine $purchase,
        AccessEngine $access
    ) {
        $this->ledger = $ledger;
        $this->requestService = $requestService;
        $this->purchase = $purchase;
        $this->access = $access;
    }

    /**
     * GET /upe/admin/requests
     * List all payment requests with optional filters.
     */
    public function listRequests(Request $request): JsonResponse
    {
        $query = UpePaymentRequest::with(['user', 'sale']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        $requests = $query->orderByDesc('id')->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $requests]);
    }

    /**
     * GET /upe/admin/requests/{id}
     * Show a single payment request with full details.
     */
    public function showRequest(int $id): JsonResponse
    {
        $req = UpePaymentRequest::with(['user', 'sale', 'verifiedByUser', 'approvedByUser'])->findOrFail($id);

        $data = ['request' => $req];

        if ($req->sale_id) {
            $data['ledger_summary'] = $this->ledger->summary($req->sale_id);
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * POST /upe/admin/requests/{id}/verify
     * Support verifies a request.
     */
    public function verifyRequest(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $paymentRequest = UpePaymentRequest::findOrFail($id);

        $updated = $this->requestService->verify($paymentRequest, $user->id);

        return response()->json(['status' => 'success', 'data' => $updated]);
    }

    /**
     * POST /upe/admin/requests/{id}/approve
     * Admin approves a request.
     */
    public function approveRequest(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $paymentRequest = UpePaymentRequest::findOrFail($id);
        $updated = $this->requestService->approve($paymentRequest, $user->id);

        return response()->json(['status' => 'success', 'data' => $updated]);
    }

    /**
     * POST /upe/admin/requests/{id}/reject
     * Reject a request at any stage.
     */
    public function rejectRequest(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $user = $request->user();
        $paymentRequest = UpePaymentRequest::findOrFail($id);
        $updated = $this->requestService->reject($paymentRequest, $user->id, $request->input('reason'));

        return response()->json(['status' => 'success', 'data' => $updated]);
    }

    /**
     * POST /upe/admin/grant-free
     * Admin grants free access to a product for a user.
     */
    public function grantFreeAccess(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'product_id' => 'required|integer|exists:upe_products,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $product = UpeProduct::findOrFail($request->input('product_id'));

        $sale = $this->purchase->processFreeSale(
            $request->input('user_id'),
            $product,
            $user->id,
            $request->input('reason', 'Admin grant')
        );

        return response()->json([
            'status' => 'success',
            'data' => $sale,
            'message' => 'Free access granted.',
        ], 201);
    }

    /**
     * GET /upe/admin/sales
     * List all sales with filters.
     */
    public function listSales(Request $request): JsonResponse
    {
        $query = UpeSale::with('product');

        if ($request->has('user_id')) {
            $query->forUser($request->input('user_id'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('product_id')) {
            $query->forProduct($request->input('product_id'));
        }

        $sales = $query->orderByDesc('id')->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $sales]);
    }

    /**
     * GET /upe/admin/sale/{id}/ledger
     * Full ledger for a sale.
     */
    public function saleLedger(int $id): JsonResponse
    {
        $sale = UpeSale::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sale' => $sale,
                'summary' => $this->ledger->summary($sale->id),
                'entries' => $this->ledger->entries($sale->id),
            ],
        ]);
    }

    /**
     * GET /upe/admin/user/{userId}/access
     * Check a user's access across all their products.
     */
    public function userAccess(int $userId): JsonResponse
    {
        $productIds = UpeSale::where('user_id', $userId)->distinct()->pluck('product_id')->toArray();
        $results = $this->access->bulkCheck($userId, $productIds);

        $formatted = [];
        foreach ($results as $pid => $result) {
            $formatted[] = array_merge(['product_id' => $pid], $result->toArray());
        }

        return response()->json(['status' => 'success', 'data' => $formatted]);
    }

    /**
     * GET /upe/admin/audit
     * Query audit log.
     */
    public function audit(Request $request): JsonResponse
    {
        $query = UpeAuditLog::query();

        if ($request->has('entity_type') && $request->has('entity_id')) {
            $query->forEntity($request->input('entity_type'), $request->input('entity_id'));
        }
        if ($request->has('actor_id')) {
            $query->byActor($request->input('actor_id'));
        }
        if ($request->has('action')) {
            $query->ofAction($request->input('action'));
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->input('per_page', 50));

        return response()->json(['status' => 'success', 'data' => $logs]);
    }

    /**
     * POST /upe/admin/offline-payment
     * Record an offline payment for a pending sale.
     */
    public function recordOfflinePayment(Request $request): JsonResponse
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer',
            'reference_note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Admin only.'], 403);
        }

        $sale = UpeSale::findOrFail($request->input('sale_id'));

        $entry = $this->purchase->processPayment(
            sale: $sale,
            amount: $request->input('amount'),
            paymentMethod: $request->input('payment_method'),
            processedBy: $user->id
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'sale' => $sale->fresh(),
                'ledger_entry' => $entry,
                'balance' => $this->ledger->balance($sale->id),
            ],
        ]);
    }
}
