<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpePaymentRequest;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\DiscountEngine;
use App\Services\PaymentEngine\InstallmentEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\PaymentRequestService;
use Illuminate\Http\Request;

class UpeController extends Controller
{
    // ──────────────────────────────────────────────
    //  MY PURCHASES
    // ──────────────────────────────────────────────

    public function myPurchases(Request $request, PaymentLedgerService $ledger, AccessEngine $access)
    {
        $user = auth()->user();

        // Subquery: pick the best (most relevant) sale per product
        // Priority: active > partially_refunded > pending_payment > others; then newest id
        $bestSaleIds = UpeSale::where('user_id', $user->id)
            ->selectRaw('MAX(CASE
                WHEN status = "active" THEN 4
                WHEN status = "partially_refunded" THEN 3
                WHEN status = "pending_payment" THEN 2
                ELSE 1
            END) as priority')
            ->selectRaw('product_id')
            ->groupBy('product_id')
            ->pluck('product_id');

        // For each product, get the single best sale
        $deduped = collect();
        foreach ($bestSaleIds as $productId) {
            $sale = UpeSale::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->whereHas('product', function ($q) use ($request) {
                    $type = $request->get('type', 'course');
                    if ($type === 'course') {
                        $q->whereIn('product_type', ['webinar', 'course_video', 'course_live', 'bundle']);
                    } elseif ($type === 'meeting') {
                        $q->where('product_type', 'meeting');
                    }
                })
                ->orderByRaw("FIELD(status, 'active', 'partially_refunded', 'pending_payment', 'completed', 'refunded', 'expired', 'cancelled') ASC")
                ->orderByDesc('id')
                ->first();
            if ($sale) {
                $deduped->push($sale->id);
            }
        }

        $query = UpeSale::whereIn('id', $deduped)->with(['product', 'installmentPlan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->orderByDesc('id')->paginate(15);

        $accessResults = [];
        $balances = [];
        foreach ($sales as $sale) {
            $accessResults[$sale->id] = $access->computeAccess($user->id, $sale->product_id);
            $balances[$sale->id] = $ledger->balance($sale->id);
        }

        $pageTitle = 'My Purchases';

        return view(getTemplate() . '.panel.upe.my_purchases', compact('sales', 'accessResults', 'balances', 'pageTitle'));
    }

    public function purchaseDetail(int $id, PaymentLedgerService $ledger, AccessEngine $access)
    {
        $user = auth()->user();
        $sale = UpeSale::where('user_id', $user->id)
            ->with(['product', 'ledgerEntries', 'installmentPlan.schedules', 'subscription'])
            ->findOrFail($id);

        $ledgerSummary = $ledger->summary($sale->id);
        $accessResult = $access->computeAccess($user->id, $sale->product_id);
        $pageTitle = 'Purchase Details';

        $products = UpeProduct::where('status', 'active')
            ->where('id', '!=', $sale->product_id)
            ->orderBy('product_type')
            ->orderBy('external_id')
            ->get();

        return view(getTemplate() . '.panel.upe.purchase_detail', compact('sale', 'ledgerSummary', 'accessResult', 'products', 'pageTitle'));
    }

    // ──────────────────────────────────────────────
    //  REQUEST REFUND
    // ──────────────────────────────────────────────

    public function requestRefund(Request $request, PaymentRequestService $service, PaymentLedgerService $ledger)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'reason' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        $sale = UpeSale::where('user_id', $user->id)->findOrFail($request->sale_id);

        if (!in_array($sale->status, ['active', 'partially_refunded'])) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'This sale is not eligible for refund.', 'status' => 'error']]);
        }

        $balance = $ledger->balance($sale->id);
        $amount = min($request->amount, $balance);

        $paymentRequest = $service->create('refund', $user->id, $sale->id, [
            'amount' => $amount,
            'reason' => $request->reason,
            'refund_percent' => 100,
        ]);

        return back()->with(['toast' => ['title' => 'Request Submitted', 'msg' => "Refund request #{$paymentRequest->id} submitted for review.", 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  REQUEST UPGRADE / ADJUSTMENT
    // ──────────────────────────────────────────────

    public function requestUpgrade(Request $request, PaymentRequestService $service)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'target_product_id' => 'required|integer|exists:upe_products,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $sale = UpeSale::where('user_id', $user->id)->findOrFail($request->sale_id);

        if (!in_array($sale->status, ['active', 'partially_refunded'])) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'This sale is not eligible for upgrade.', 'status' => 'error']]);
        }

        $paymentRequest = $service->create('upgrade', $user->id, $sale->id, [
            'target_product_id' => $request->target_product_id,
            'reason' => $request->reason ?? 'User requested upgrade',
            'adjustment_type' => 'upgrade',
            'adjustment_percent' => 80,
        ]);

        return back()->with(['toast' => ['title' => 'Request Submitted', 'msg' => "Upgrade request #{$paymentRequest->id} submitted for review.", 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  REQUEST COURSE EXTENSION (Scenario 1)
    // ──────────────────────────────────────────────

    public function requestExtension(Request $request, PaymentRequestService $service)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'extension_days' => 'required|integer|in:7,15,30',
            'reason' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $sale = UpeSale::where('user_id', $user->id)->findOrFail($request->sale_id);

        if (!in_array($sale->status, ['active', 'completed'])) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'This sale is not eligible for extension.', 'status' => 'error']]);
        }

        $existingCount = UpePaymentRequest::where('user_id', $user->id)
            ->where('sale_id', $sale->id)
            ->where('request_type', 'course_extension')
            ->whereNotIn('status', ['rejected'])
            ->count();

        if ($existingCount >= 3) {
            return back()->with(['toast' => ['title' => 'Limit Reached', 'msg' => 'Maximum 3 extension requests per purchase.', 'status' => 'error']]);
        }

        $paymentRequest = $service->create('course_extension', $user->id, $sale->id, [
            'extension_days' => $request->extension_days,
            'reason' => $request->reason,
            'current_valid_until' => $sale->valid_until,
        ]);

        return back()->with(['toast' => ['title' => 'Request Submitted', 'msg' => "Extension request #{$paymentRequest->id} submitted for review.", 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  REQUEST POST-PURCHASE COUPON (Scenario 10)
    // ──────────────────────────────────────────────

    public function requestCoupon(Request $request, PaymentRequestService $service)
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'coupon_code' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        $sale = UpeSale::where('user_id', $user->id)->findOrFail($request->sale_id);

        if (!in_array($sale->status, ['active', 'pending_payment'])) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'This sale is not eligible for coupon application.', 'status' => 'error']]);
        }

        $alreadyApplied = UpePaymentRequest::where('user_id', $user->id)
            ->where('sale_id', $sale->id)
            ->where('request_type', 'post_purchase_coupon')
            ->whereNotIn('status', ['rejected'])
            ->exists();

        if ($alreadyApplied) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'A coupon request is already pending or applied for this purchase.', 'status' => 'error']]);
        }

        $paymentRequest = $service->create('post_purchase_coupon', $user->id, $sale->id, [
            'coupon_code' => $request->coupon_code,
        ]);

        return back()->with(['toast' => ['title' => 'Request Submitted', 'msg' => "Coupon request #{$paymentRequest->id} submitted for verification.", 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  REQUEST INSTALLMENT RESTRUCTURE (Scenario 7)
    // ──────────────────────────────────────────────

    public function requestRestructure(Request $request, PaymentRequestService $service)
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:upe_installment_plans,id',
            'reason' => 'required|string|max:1000',
        ]);

        $user = auth()->user();

        $plan = UpeInstallmentPlan::with(['sale.product', 'schedules'])
            ->whereHas('sale', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->findOrFail($request->plan_id);

        if ($plan->status !== 'active') {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'This plan is not eligible for restructure.', 'status' => 'error']]);
        }

        // Must have at least one unpaid schedule
        $unpaidSchedules = $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue']);
        if ($unpaidSchedules->isEmpty()) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'All installments are already paid. Nothing to restructure.', 'status' => 'error']]);
        }

        // Check for existing pending request
        $alreadyRequested = UpePaymentRequest::where('user_id', $user->id)
            ->where('sale_id', $plan->sale_id)
            ->where('request_type', 'installment_restructure')
            ->whereNotIn('status', ['rejected', 'executed'])
            ->exists();

        if ($alreadyRequested) {
            return back()->with(['toast' => ['title' => 'Error', 'msg' => 'A restructure request is already pending for this plan.', 'status' => 'error']]);
        }

        // Determine target schedule: first unpaid by due date
        $targetSchedule = $unpaidSchedules->sortBy('due_date')->first();
        $isUpfront = ($targetSchedule->sequence <= 1);

        // Resolve webinar_id from UPE product metadata
        $webinarId = null;
        if ($plan->sale && $plan->sale->product) {
            $webinarId = $plan->sale->product->external_id;
        }

        // 1. Create UpePaymentRequest
        $paymentRequest = $service->create('installment_restructure', $user->id, $plan->sale_id, [
            'plan_id' => $plan->id,
            'schedule_id' => $targetSchedule->id,
            'schedule_sequence' => $targetSchedule->sequence,
            'schedule_amount' => $targetSchedule->amount_due,
            'schedule_remaining' => $targetSchedule->remainingAmount(),
            'schedule_due_date' => $targetSchedule->due_date ? $targetSchedule->due_date->format('Y-m-d') : null,
            'is_upfront' => $isUpfront,
            'reason' => $request->reason,
            'overdue_count' => $plan->schedules->where('status', 'overdue')->count(),
            'remaining_count' => $unpaidSchedules->count(),
            'remaining_amount' => $unpaidSchedules->sum('amount_due') - $unpaidSchedules->sum('amount_paid'),
            'webinar_id' => $webinarId,
        ]);

        // 2. Create support ticket so admin/support can see it
        $webinar = $webinarId ? \App\Models\Webinar::find($webinarId) : null;
        $supportTicket = \App\Models\NewSupportForAsttrolok::create([
            'user_id' => $user->id,
            'support_scenario' => 'installment_restructure',
            'webinar_id' => $webinarId,
            'title' => 'EMI Restructure: ' . ($webinar ? $webinar->title : "Plan #{$plan->id}"),
            'description' => $request->reason,
            'flow_type' => 'existing_student',
            'purchase_status' => 'purchased',
            'status' => 'pending',
            'restructure_reason' => $request->reason,
            'installment_amount' => $targetSchedule->remainingAmount(),
            'execution_result' => [
                'upe_payment_request_id' => $paymentRequest->id,
                'plan_id' => $plan->id,
                'schedule_id' => $targetSchedule->id,
                'schedule_sequence' => $targetSchedule->sequence,
                'schedule_amount' => (float) $targetSchedule->amount_due,
                'schedule_remaining' => $targetSchedule->remainingAmount(),
                'is_upfront' => $isUpfront,
            ],
        ]);

        // Link support ticket back to UPE request
        $paymentRequest->update([
            'payload' => array_merge($paymentRequest->payload ?? [], [
                'support_ticket_id' => $supportTicket->id,
            ]),
        ]);

        return back()->with(['toast' => ['title' => 'Request Submitted', 'msg' => "Restructure request #{$paymentRequest->id} submitted. Support ticket #{$supportTicket->ticket_number} created.", 'status' => 'success']]);
    }

    // ──────────────────────────────────────────────
    //  MY REQUESTS
    // ──────────────────────────────────────────────

    public function myRequests()
    {
        $user = auth()->user();
        $requests = UpePaymentRequest::where('user_id', $user->id)
            ->where('request_type', '!=', 'upgrade')
            ->with('sale.product')
            ->orderByDesc('id')
            ->paginate(15);

        $pageTitle = 'My Requests';

        return view(getTemplate() . '.panel.upe.my_requests', compact('requests', 'pageTitle'));
    }

    // ──────────────────────────────────────────────
    //  INSTALLMENT DASHBOARD
    // ──────────────────────────────────────────────

    public function installments(PaymentLedgerService $ledger)
    {
        $user = auth()->user();

        $sales = UpeSale::where('user_id', $user->id)
            ->where('pricing_mode', 'installment')
            ->with(['product', 'installmentPlan.schedules'])
            ->orderByDesc('id')
            ->get();

        $pageTitle = 'My Installments';

        return view(getTemplate() . '.panel.upe.installments', compact('sales', 'pageTitle'));
    }

    public function installmentDetail(int $planId, PaymentLedgerService $ledger)
    {
        $user = auth()->user();

        $plan = UpeInstallmentPlan::with(['schedules', 'sale.product'])
            ->whereHas('sale', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->findOrFail($planId);

        // Find the next unpaid schedule for the Pay button
        $nextUnpaid = $plan->schedules
            ->whereNotIn('status', ['paid', 'waived'])
            ->sortBy('due_date')
            ->first();

        $payUrl = null;
        if ($nextUnpaid && $plan->sale && $plan->sale->product) {
            $webinar = \App\Models\Webinar::find($plan->sale->product->external_id);
            if ($webinar) {
                $payUrl = url('/register-course/' . $webinar->slug . '?amount=' . $nextUnpaid->amount_due);
            }
        }

        // Check for existing restructure request
        $existingRestructureRequest = UpePaymentRequest::where('user_id', $user->id)
            ->where('sale_id', $plan->sale_id)
            ->where('request_type', 'installment_restructure')
            ->whereNotIn('status', ['rejected', 'executed'])
            ->latest()
            ->first();

        // If no active request, check if the last one was rejected (for UX feedback)
        $lastRejectedRestructure = null;
        if (!$existingRestructureRequest) {
            $lastRejectedRestructure = UpePaymentRequest::where('user_id', $user->id)
                ->where('sale_id', $plan->sale_id)
                ->where('request_type', 'installment_restructure')
                ->where('status', 'rejected')
                ->latest()
                ->first();
        }

        // Determine target schedule for restructure form
        $unpaidSchedules = $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue']);
        $restructureTarget = null;
        if ($unpaidSchedules->isNotEmpty()) {
            $restructureTarget = $unpaidSchedules->sortBy('due_date')->first();
        }

        $pageTitle = 'Installment Plan';

        return view(getTemplate() . '.panel.upe.installment_detail', compact(
            'plan', 'nextUnpaid', 'payUrl', 'pageTitle',
            'existingRestructureRequest', 'restructureTarget', 'lastRejectedRestructure'
        ));
    }
}
