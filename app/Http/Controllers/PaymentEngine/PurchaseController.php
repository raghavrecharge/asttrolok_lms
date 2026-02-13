<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\DiscountEngine;
use App\Services\PaymentEngine\PurchaseEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private PurchaseEngine $purchase;
    private DiscountEngine $discount;
    private PaymentLedgerService $ledger;
    private AccessEngine $access;

    public function __construct(
        PurchaseEngine $purchase,
        DiscountEngine $discount,
        PaymentLedgerService $ledger,
        AccessEngine $access
    ) {
        $this->purchase = $purchase;
        $this->discount = $discount;
        $this->ledger = $ledger;
        $this->access = $access;
    }

    /**
     * GET /upe/products
     * List active products with optional filters.
     */
    public function products(Request $request): JsonResponse
    {
        $query = UpeProduct::active();

        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        $products = $query->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $products]);
    }

    /**
     * GET /upe/products/{id}
     * Show a single product with pricing.
     */
    public function showProduct(int $id, Request $request): JsonResponse
    {
        $product = UpeProduct::findOrFail($id);
        $user = $request->user();
        $discountCode = $request->input('coupon');

        $pricing = $this->purchase->calculatePrice(
            $product,
            $discountCode,
            $user ? $user->id : null
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => $product,
                'pricing' => $pricing,
            ],
        ]);
    }

    /**
     * POST /upe/purchase/calculate
     * Server-side price calculation (no side effects).
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:upe_products,id',
            'coupon' => 'nullable|string|max:64',
        ]);

        $product = UpeProduct::findOrFail($request->input('product_id'));
        $user = $request->user();

        $pricing = $this->purchase->calculatePrice(
            $product,
            $request->input('coupon'),
            $user->id
        );

        return response()->json(['status' => 'success', 'data' => $pricing]);
    }

    /**
     * POST /upe/purchase/create
     * Create a sale (intent to purchase). Does NOT process payment.
     */
    public function createSale(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:upe_products,id',
            'pricing_mode' => 'required|in:full,installment,subscription,free',
            'coupon' => 'nullable|string|max:64',
            'referral_code' => 'nullable|string|max:32',
        ]);

        $product = UpeProduct::findOrFail($request->input('product_id'));
        $user = $request->user();

        $sale = $this->purchase->createSale(
            userId: $user->id,
            product: $product,
            pricingMode: $request->input('pricing_mode'),
            saleType: $request->input('pricing_mode') === 'free' ? 'free' : 'paid',
            discountCode: $request->input('coupon'),
            referralCode: $request->input('referral_code')
        );

        $effectivePrice = $this->discount->effectivePrice($sale);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sale' => $sale,
                'effective_price' => $effectivePrice,
                'requires_payment' => $sale->isPendingPayment(),
            ],
        ], 201);
    }

    /**
     * POST /upe/purchase/pay
     * Process payment for an existing pending sale.
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'sale_id' => 'required|integer|exists:upe_sales,id',
            'payment_method' => 'required|in:cash,bank_transfer,razorpay,paypal,stripe,payment_link,wallet',
            'gateway_transaction_id' => 'nullable|string|max:255',
            'gateway_response' => 'nullable|array',
        ]);

        $sale = UpeSale::findOrFail($request->input('sale_id'));
        $user = $request->user();

        if ($sale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $effectivePrice = $this->discount->effectivePrice($sale);

        $entry = $this->purchase->processPayment(
            sale: $sale,
            amount: $effectivePrice,
            paymentMethod: $request->input('payment_method'),
            gatewayTransactionId: $request->input('gateway_transaction_id'),
            gatewayResponse: $request->input('gateway_response'),
            processedBy: $user->id
        );

        $sale->refresh();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sale' => $sale,
                'ledger_entry' => $entry,
                'balance' => $this->ledger->balance($sale->id),
            ],
        ]);
    }

    /**
     * GET /upe/purchase/my-sales
     * List current user's sales.
     */
    public function mySales(Request $request): JsonResponse
    {
        $user = $request->user();

        $sales = UpeSale::forUser($user->id)
            ->with('product')
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $sales]);
    }

    /**
     * GET /upe/purchase/sale/{id}
     * Show a single sale with ledger summary.
     */
    public function showSale(int $id, Request $request): JsonResponse
    {
        $sale = UpeSale::with(['product', 'ledgerEntries'])->findOrFail($id);
        $user = $request->user();

        if ($sale->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'sale' => $sale,
                'ledger_summary' => $this->ledger->summary($sale->id),
                'access' => $this->access->computeAccess($sale->user_id, $sale->product_id)->toArray(),
            ],
        ]);
    }

    /**
     * GET /upe/access/check
     * Check if current user has access to a product.
     */
    public function checkAccess(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer']);

        $user = $request->user();
        $result = $this->access->hasAccess($user->id, $request->input('product_id'));

        return response()->json(['status' => 'success', 'data' => $result->toArray()]);
    }
}
