<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeAuditLog;
use App\Models\PaymentEngine\UpePaymentRequest;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeSubscription;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\User;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\AdjustmentEngine;
use App\Services\PaymentEngine\InstallmentEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use App\Services\PaymentEngine\Policies\StandardAdjustmentPolicy;
use App\Services\PaymentEngine\Policies\StandardRefundPolicy;
use App\Services\PaymentEngine\PurchaseEngine;
use App\Services\PaymentEngine\RefundEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpeController extends Controller
{
    // ──────────────────────────────────────────────
    //  SALES
    // ──────────────────────────────────────────────

    public function sales(Request $request)
    {
        $query = UpeSale::with(['product', 'user']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('pricing_mode')) {
            $query->where('pricing_mode', $request->pricing_mode);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->orderByDesc('id')->paginate(20);

        $stats = [
            'total' => UpeSale::count(),
            'active' => UpeSale::where('status', 'active')->count(),
            'pending' => UpeSale::where('status', 'pending_payment')->count(),
            'refunded' => UpeSale::whereIn('status', ['refunded', 'partially_refunded'])->count(),
        ];

        $pageTitle = 'UPE Sales';

        return view('admin.upe.sales', compact('sales', 'stats', 'pageTitle'));
    }

    public function saleDetail(int $id, PaymentLedgerService $ledger, AccessEngine $access)
    {
        $sale = UpeSale::with(['product', 'user', 'ledgerEntries', 'installmentPlan.schedules', 'subscription'])->findOrFail($id);
        $ledgerSummary = $ledger->summary($sale->id);
        $accessResult = $access->computeAccess($sale->user_id, $sale->product_id);
        $pageTitle = "Sale #{$sale->id}";

        return view('admin.upe.sale_detail', compact('sale', 'ledgerSummary', 'accessResult', 'pageTitle'));
    }

    // ──────────────────────────────────────────────
    //  PAYMENT REQUESTS
    // ──────────────────────────────────────────────

    public function requests(Request $request)
    {
        $query = UpePaymentRequest::with(['user', 'sale'])
            ->where('request_type', '!=', 'upgrade');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        $requests = $query->orderByDesc('id')->paginate(20);
        $pendingCount = UpePaymentRequest::where('status', 'pending')->where('request_type', '!=', 'upgrade')->count();
        $pageTitle = 'UPE Payment Requests';

        return view('admin.upe.requests', compact('requests', 'pendingCount', 'pageTitle'));
    }

    public function requestDetail(int $id, PaymentLedgerService $ledger)
    {
        $paymentRequest = UpePaymentRequest::with(['user', 'sale.product'])->findOrFail($id);
        $ledgerSummary = $paymentRequest->sale ? $ledger->summary($paymentRequest->sale_id) : null;
        $pageTitle = "Request #{$paymentRequest->id}";

        return view('admin.upe.request_detail', compact('paymentRequest', 'ledgerSummary', 'pageTitle'));
    }

    public function verifyRequest(int $id, PaymentRequestService $service)
    {
        $req = UpePaymentRequest::findOrFail($id);
        $service->verify($req, auth()->id());

        return back()->with(['toast' => ['title' => 'Success', 'msg' => 'Request verified.', 'status' => 'success']]);
    }

    public function approveRequest(int $id, PaymentRequestService $service)
    {
        $req = UpePaymentRequest::findOrFail($id);
        $service->approve($req, auth()->id());

        return back()->with(['toast' => ['title' => 'Success', 'msg' => 'Request approved.', 'status' => 'success']]);
    }

    public function rejectRequest(int $id, Request $request, PaymentRequestService $service)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $req = UpePaymentRequest::findOrFail($id);
        $service->reject($req, auth()->id(), $request->reason);

        return back()->with(['toast' => ['title' => 'Rejected', 'msg' => 'Request rejected.', 'status' => 'warning']]);
    }

    public function executeRequest(int $id, PaymentRequestService $service, RefundEngine $refundEngine, AdjustmentEngine $adjustmentEngine, PurchaseEngine $purchaseEngine, PaymentLedgerService $ledger)
    {
        $req = UpePaymentRequest::findOrFail($id);
        $adminId = auth()->id();

        $result = $service->execute($req, $adminId, function ($request) use ($refundEngine, $adjustmentEngine, $purchaseEngine, $ledger, $adminId) {
            $payload = $request->payload ?? [];

            switch ($request->request_type) {
                case 'refund':
                    $sale = UpeSale::findOrFail($request->sale_id);
                    $amount = $payload['amount'] ?? $ledger->balance($sale->id);
                    $policy = new StandardRefundPolicy($payload['refund_percent'] ?? 100);
                    $entry = $refundEngine->processRefund($sale, $amount, $policy, $payload['reason'] ?? 'Admin approved refund', $adminId);
                    return ['refund_entry_id' => $entry->id, 'amount' => $amount];

                case 'offline_payment':
                    $sale = UpeSale::findOrFail($request->sale_id);
                    $amount = $payload['amount'] ?? 0;
                    $entry = $ledger->recordPayment($sale->id, $amount, $payload['payment_method'] ?? 'cash', null, null, $adminId, $payload['description'] ?? 'Offline payment');
                    if ($sale->isPendingPayment()) {
                        $sale->update(['status' => 'active', 'valid_from' => now(), 'valid_until' => $sale->product && $sale->product->validity_days ? now()->addDays($sale->product->validity_days) : null, 'executed_at' => now()]);
                    }
                    return ['payment_entry_id' => $entry->id, 'amount' => $amount];

                case 'upgrade':
                case 'adjustment':
                    $sourceSale = UpeSale::findOrFail($request->sale_id);
                    $targetProduct = UpeProduct::findOrFail($payload['target_product_id']);
                    $policy = new StandardAdjustmentPolicy($payload['adjustment_percent'] ?? 80);
                    $result = $adjustmentEngine->execute($sourceSale, $targetProduct, $policy, $payload['adjustment_type'] ?? 'upgrade', $adminId);
                    return ['adjustment_id' => $result['adjustment']->id, 'remaining' => $result['remaining_to_pay']];

                default:
                    return ['message' => 'Executed (no specific handler for type: ' . $request->request_type . ')'];
            }
        });

        if ($result['already_executed'] ?? false) {
            return back()->with(['toast' => ['title' => 'Info', 'msg' => 'Request was already executed.', 'status' => 'info']]);
        }

        return back()->with(['toast' => ['title' => 'Success', 'msg' => 'Request executed successfully.', 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  QUICK ACTIONS
    // ──────────────────────────────────────────────

    public function grantFreeAccess(Request $request, PurchaseEngine $engine)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:upe_products,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $product = UpeProduct::findOrFail($request->product_id);
        $sale = $engine->processFreeSale($request->user_id, $product, auth()->id(), $request->reason);

        return back()->with(['toast' => ['title' => 'Success', 'msg' => "Free access granted. Sale #{$sale->id} created.", 'status' => 'success']]);
    }

    public function recordOfflinePayment(Request $request, PaymentLedgerService $ledger)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,bank_transfer,payment_link',
            'description' => 'nullable|string|max:500',
        ]);

        $sale = UpeSale::findOrFail($request->sale_id);

        $entry = $ledger->recordPayment(
            $sale->id,
            $request->amount,
            $request->payment_method,
            null, null,
            auth()->id(),
            $request->description ?? 'Offline payment recorded by admin'
        );

        if ($sale->isPendingPayment()) {
            $sale->update([
                'status' => 'active',
                'valid_from' => now(),
                'valid_until' => $sale->product && $sale->product->validity_days ? now()->addDays($sale->product->validity_days) : null,
                'executed_at' => now(),
            ]);
        }

        return back()->with(['toast' => ['title' => 'Success', 'msg' => "Payment of ₹{$request->amount} recorded.", 'status' => 'success']]);
    }

    public function processRefund(Request $request, RefundEngine $engine)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:500',
            'refund_percent' => 'nullable|numeric|min:1|max:100',
        ]);

        $sale = UpeSale::findOrFail($request->sale_id);
        $policy = new StandardRefundPolicy($request->refund_percent ?? 100);

        $entry = $engine->processRefund($sale, $request->amount, $policy, $request->reason, auth()->id());

        return back()->with(['toast' => ['title' => 'Refund Processed', 'msg' => "₹{$request->amount} refunded for Sale #{$sale->id}.", 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  AUDIT LOG
    // ──────────────────────────────────────────────

    public function auditLog(Request $request)
    {
        $query = UpeAuditLog::query();

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }
        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $logs = $query->orderByDesc('id')->paginate(30);
        $pageTitle = 'UPE Audit Log';

        return view('admin.upe.audit_log', compact('logs', 'pageTitle'));
    }

    // ──────────────────────────────────────────────
    //  USER ACCESS LOOKUP
    // ──────────────────────────────────────────────

    public function userAccess(Request $request, AccessEngine $access)
    {
        $user = null;
        $sales = collect();
        $accessResults = [];

        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if ($user) {
                $sales = UpeSale::where('user_id', $user->id)->with('product')->orderByDesc('id')->get();
                foreach ($sales as $sale) {
                    $accessResults[$sale->id] = $access->computeAccess($user->id, $sale->product_id);
                }
            }
        }

        $pageTitle = 'User Access Lookup';
        $products = UpeProduct::where('status', 'active')->orderBy('product_type')->orderBy('external_id')->get();

        return view('admin.upe.user_access', compact('user', 'sales', 'accessResults', 'products', 'pageTitle'));
    }
}
