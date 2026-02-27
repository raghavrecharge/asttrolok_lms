<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use App\Models\PaymentEngine\UpeSubscription;
use App\Models\Sale;
use App\Models\Accounting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    private PaymentLedgerService $ledger;
    private AuditService $audit;

    public function __construct(PaymentLedgerService $ledger, AuditService $audit)
    {
        $this->ledger = $ledger;
        $this->audit = $audit;
    }

    /**
     * Process a webinar (course) purchase.
     * Creates UPE sale + ledger entry + legacy Sale dual-write.
     */
    public function processWebinarPurchase(int $userId, int $webinarId, float $amount, string $paymentMethod = 'razorpay', ?string $razorpayPaymentId = null): array
    {
        $webinar = \App\Models\Webinar::findOrFail($webinarId);

        // Resolve UPE product
        $productType = match ($webinar->type) {
            'webinar' => 'webinar',
            default => 'course_video',
        };
        $upeProduct = $this->resolveProduct($webinarId, $productType, $webinar->price ?? $amount, $webinar->access_days);

        // Check idempotency — already has active UPE sale?
        $existingSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $upeProduct->id)
            ->whereIn('status', ['active', 'partially_refunded'])
            ->first();

        if ($existingSale) {
            Log::warning('CheckoutService: User already has active UPE sale', [
                'user_id' => $userId, 'product_id' => $upeProduct->id, 'existing_sale_id' => $existingSale->id,
            ]);
            return ['upe_sale' => $existingSale, 'legacy_sale' => null, 'already_exists' => true];
        }

        // Create UPE sale
        $validFrom = now();
        $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $upeProduct->id,
            'sale_type' => $amount > 0 ? 'paid' : 'free',
            'pricing_mode' => 'full',
            'base_fee_snapshot' => $upeProduct->base_fee,
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'razorpay_payment_id' => $razorpayPaymentId,
                'source' => 'checkout',
                'webinar_id' => $webinarId,
            ]),
        ]);

        // Create ledger entry
        $this->ledger->append(
            $upeSale->id,
            UpeLedgerEntry::TYPE_PAYMENT,
            UpeLedgerEntry::DIR_CREDIT,
            $amount,
            $paymentMethod,
            $razorpayPaymentId,
            null, // gatewayResponse
            null, // referenceType
            null, // referenceId
            "Payment for course: {$webinar->slug}",
            null, // processedBy
            $razorpayPaymentId ? "rp_{$razorpayPaymentId}_webinar_{$webinarId}" : "checkout_webinar_{$userId}_{$webinarId}_" . time()
        );

        // Audit
        $this->audit->logSaleCreated($userId, 'student', $upeSale->toArray());

        // Legacy dual-write
        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $webinar->creator_id,
            'webinar_id' => $webinarId,
            'type' => 'webinar',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        // Accounting entry
        Accounting::create([
            'user_id' => $webinar->creator_id,
            'webinar_id' => $webinarId,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Course purchase: ' . ($webinar->title ?? $webinar->slug),
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        // Clear access cache
        $this->clearAccessCache($userId, $upeProduct->id);

        Log::info('CheckoutService: Webinar purchase completed', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'upe_sale_id' => $upeSale->id, 'legacy_sale_id' => $legacySale->id,
        ]);

        return ['upe_sale' => $upeSale, 'legacy_sale' => $legacySale, 'already_exists' => false];
    }

    /**
     * Process a bundle purchase.
     */
    public function processBundlePurchase(int $userId, int $bundleId, float $amount, string $paymentMethod = 'razorpay', ?string $razorpayPaymentId = null): array
    {
        $bundle = \App\Models\Bundle::findOrFail($bundleId);

        $upeProduct = $this->resolveProduct($bundleId, 'bundle', $bundle->price ?? $amount, $bundle->access_days);

        $existingSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $upeProduct->id)
            ->whereIn('status', ['active', 'partially_refunded'])
            ->first();

        if ($existingSale) {
            Log::warning('CheckoutService: User already has active bundle sale', [
                'user_id' => $userId, 'bundle_id' => $bundleId,
            ]);
            return ['upe_sale' => $existingSale, 'legacy_sale' => null, 'already_exists' => true];
        }

        $validFrom = now();
        $validUntil = $bundle->access_days ? $validFrom->copy()->addDays($bundle->access_days) : null;

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $upeProduct->id,
            'sale_type' => $amount > 0 ? 'paid' : 'free',
            'pricing_mode' => 'full',
            'base_fee_snapshot' => $upeProduct->base_fee,
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'razorpay_payment_id' => $razorpayPaymentId,
                'source' => 'checkout',
                'bundle_id' => $bundleId,
            ]),
        ]);

        $this->ledger->append(
            $upeSale->id,
            UpeLedgerEntry::TYPE_PAYMENT,
            UpeLedgerEntry::DIR_CREDIT,
            $amount,
            $paymentMethod,
            $razorpayPaymentId,
            null, // gatewayResponse
            null, // referenceType
            null, // referenceId
            "Payment for bundle: {$bundle->slug}",
            null, // processedBy
            $razorpayPaymentId ? "rp_{$razorpayPaymentId}_bundle_{$bundleId}" : "checkout_bundle_{$userId}_{$bundleId}_" . time()
        );

        $this->audit->logSaleCreated($userId, 'student', $upeSale->toArray());

        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $bundle->creator_id,
            'bundle_id' => $bundleId,
            'type' => 'bundle',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        Accounting::create([
            'user_id' => $bundle->creator_id,
            'bundle_id' => $bundleId,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Bundle purchase: ' . ($bundle->title ?? $bundle->slug),
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        $this->clearAccessCache($userId, $upeProduct->id);

        Log::info('CheckoutService: Bundle purchase completed', [
            'user_id' => $userId, 'bundle_id' => $bundleId,
            'upe_sale_id' => $upeSale->id,
        ]);

        return ['upe_sale' => $upeSale, 'legacy_sale' => $legacySale, 'already_exists' => false];
    }

    /**
     * Process a subscription purchase.
     */
    public function processSubscriptionPurchase(int $userId, int $subscriptionId, float $amount, string $paymentMethod = 'razorpay', ?string $razorpayPaymentId = null): array
    {
        $subscription = \App\Models\Subscription::findOrFail($subscriptionId);

        $upeProduct = $this->resolveProduct($subscriptionId, 'subscription', $amount, $subscription->access_days);

        $validFrom = now();
        $validUntil = $subscription->access_days ? $validFrom->copy()->addDays($subscription->access_days) : $validFrom->copy()->addDays(30);

        // Check for existing active subscription
        $existingSub = UpeSubscription::where('user_id', $userId)
            ->where('product_id', $upeProduct->id)
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->first();

        if ($existingSub) {
            // Renew: extend period
            $newEnd = $existingSub->current_period_end->copy()->addDays($subscription->access_days ?? 30);
            $existingSub->update([
                'current_period_end' => $newEnd,
                'status' => 'active',
            ]);

            // Create sale for the renewal payment
            $upeSale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'subscription',
                'base_fee_snapshot' => $amount,
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $newEnd,
                'metadata' => json_encode([
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'source' => 'checkout',
                    'subscription_id' => $subscriptionId,
                    'upe_subscription_id' => $existingSub->id,
                ]),
            ]);
        } else {
            // New subscription
            $upeSale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_type' => $amount > 0 ? 'paid' : 'free',
                'pricing_mode' => 'subscription',
                'base_fee_snapshot' => $amount,
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'metadata' => json_encode([
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'source' => 'checkout',
                    'subscription_id' => $subscriptionId,
                ]),
            ]);

            UpeSubscription::create([
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_id' => $upeSale->id,
                'status' => 'active',
                'billing_interval' => 'monthly',
                'billing_amount' => $amount,
                'current_period_start' => $validFrom,
                'current_period_end' => $validUntil,
                'grace_period_days' => 3,
            ]);
        }

        if ($amount > 0) {
            $this->ledger->append(
                $upeSale->id,
                UpeLedgerEntry::TYPE_PAYMENT,
                UpeLedgerEntry::DIR_CREDIT,
                $amount,
                $paymentMethod,
                $razorpayPaymentId,
                null, // gatewayResponse
                null, // referenceType
                null, // referenceId
                "Subscription payment: {$subscription->slug}",
                null, // processedBy
                $razorpayPaymentId ? "rp_{$razorpayPaymentId}_sub_{$subscriptionId}" : "checkout_sub_{$userId}_{$subscriptionId}_" . time()
            );
        }

        $this->audit->logSaleCreated($userId, 'student', $upeSale->toArray());

        // Legacy dual-write (Sale + SubscriptionAccess + SubscriptionPayments handled by caller)
        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $subscription->creator_id ?? 1,
            'subscription_id' => $subscriptionId,
            'type' => 'subscription',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        Accounting::create([
            'user_id' => $subscription->creator_id ?? 1,
            'subscription_id' => $subscriptionId,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Subscription: ' . ($subscription->title ?? $subscription->slug),
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        $this->clearAccessCache($userId, $upeProduct->id);

        Log::info('CheckoutService: Subscription purchase completed', [
            'user_id' => $userId, 'subscription_id' => $subscriptionId,
            'upe_sale_id' => $upeSale->id,
        ]);

        return ['upe_sale' => $upeSale, 'legacy_sale' => $legacySale, 'already_exists' => false];
    }

    /**
     * Process an installment upfront payment (DEPRECATED — use processPartPayment instead).
     * Kept for backward compatibility. Delegates to processPartPayment internally.
     */
    public function processInstallmentPayment(int $userId, int $webinarId, float $amount, int $installmentPaymentId, string $paymentMethod = 'razorpay', ?string $razorpayPaymentId = null): array
    {
        Log::info('CheckoutService::processInstallmentPayment delegating to processPartPayment', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'amount' => $amount, 'installment_payment_id' => $installmentPaymentId,
        ]);

        // Resolve installment_id from legacy InstallmentOrderPayment if available
        $installmentId = null;
        $legacyPayment = \App\Models\InstallmentOrderPayment::find($installmentPaymentId);
        if ($legacyPayment && $legacyPayment->installmentOrder) {
            $installmentId = $legacyPayment->installmentOrder->installment_id;
        }
        if (!$installmentId) {
            $installmentId = (int) \App\Models\Installment::where('enable', true)->value('id');
        }

        return $this->processPartPayment($userId, $webinarId, $amount, $installmentId, $paymentMethod, $razorpayPaymentId);
    }

    /**
     * Process a meeting purchase (no UPE — meetings are not course access).
     * Legacy-only for now.
     */
    public function processMeetingPurchase(int $userId, int $meetingId, float $amount, array $extraData = []): Sale
    {
        $meeting = \App\Models\Meeting::findOrFail($meetingId);

        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $meeting->creator_id,
            'meeting_id' => $meetingId,
            'type' => 'meeting',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        Accounting::create([
            'user_id' => $meeting->creator_id,
            'meeting_id' => $meetingId,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Meeting booking: ' . ($meeting->title ?? 'Meeting'),
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        return $legacySale;
    }

    /**
     * Process a product (physical/digital) purchase. Legacy-only.
     */
    public function processProductPurchase(int $userId, int $productId, float $amount, ?int $productOrderId = null, ?string $razorpayPaymentId = null): Sale
    {
        $product = \App\Models\Product::findOrFail($productId);

        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $product->creator_id,
            'product_id' => $productId,
            'type' => 'product',
            'product_order_id' => $productOrderId,
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        Accounting::create([
            'user_id' => $product->creator_id,
            'product_id' => $productId,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Product purchase: ' . ($product->title ?? 'Product'),
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        return $legacySale;
    }

    /**
     * Find or create a UPE product by external_id + type.
     */
    private function resolveProduct(int $externalId, string $productType, float $baseFee, ?int $validityDays = null): UpeProduct
    {
        return UpeProduct::firstOrCreate(
            ['external_id' => $externalId, 'product_type' => $productType],
            [
                'base_fee' => $baseFee,
                'validity_days' => $validityDays,
                'status' => 'active',
            ]
        );
    }

    /**
     * Process a quick-pay purchase with arbitrary amount.
     * Creates UPE sale + installment plan + schedules, then applies payment via InstallmentEngine waterfall.
     * If sale already exists, just applies the payment to the existing plan.
     */
    public function processQuickPayment(
        int $userId,
        int $webinarId,
        float $amount,
        int $installmentId,
        string $paymentMethod = 'razorpay',
        ?string $razorpayPaymentId = null
    ): array {
        $webinar = \App\Models\Webinar::findOrFail($webinarId);
        $installment = \App\Models\Installment::findOrFail($installmentId);

        $productType = match ($webinar->type) {
            'webinar' => 'webinar',
            default => 'course_video',
        };
        $coursePrice = $webinar->getPrice() ?? $webinar->price;
        $upeProduct = $this->resolveProduct($webinarId, $productType, $coursePrice, $webinar->access_days);

        // Check if UPE sale already exists
        $existingSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $upeProduct->id)
            ->where('pricing_mode', 'installment')
            ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
            ->first();

        if ($existingSale) {
            // Top-up: apply payment to existing plan
            $plan = UpeInstallmentPlan::where('sale_id', $existingSale->id)->first();

            if ($plan) {
                $hasUnpaid = $plan->schedules()
                    ->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])
                    ->exists();

                if ($hasUnpaid) {
                    $engine = app(InstallmentEngine::class);
                    $engineResult = $engine->recordPayment(
                        $plan, $amount, $paymentMethod, $razorpayPaymentId
                    );
                }

                // Check if fully paid → activate
                if ($plan->totalPaid() >= $plan->total_amount) {
                    $existingSale->update(['status' => 'active']);
                }
            }

            // Legacy dual-write
            $legacySale = Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $webinar->creator_id ?? 1,
                'webinar_id' => $webinarId,
                'type' => 'installment_payment',
                'payment_method' => 'payment_channel',
                'amount' => $amount,
                'total_amount' => $amount,
                'created_at' => time(),
            ]);

            // Legacy part payment record
            \App\Models\WebinarPartPayment::create([
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'installment_id' => $installmentId,
                'amount' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Accounting::create([
                'user_id' => $webinar->creator_id ?? 1,
                'sale_id' => $legacySale->id,
                'amount' => $amount,
                'type' => 'addiction',
                'description' => "Quick pay top-up for: {$webinar->slug}",
                'is_affiliate' => false,
                'is_cashback' => false,
                'store_type' => 'automatic',
                'tax' => 0,
                'commission' => 0,
                'discount' => 0,
                'created_at' => time(),
            ]);

            $this->clearAccessCache($userId, $upeProduct->id);

            Log::info('CheckoutService: Quick pay top-up recorded', [
                'user_id' => $userId, 'webinar_id' => $webinarId,
                'upe_sale_id' => $existingSale->id, 'amount' => $amount,
            ]);

            return ['upe_sale' => $existingSale, 'legacy_sale' => $legacySale, 'already_exists' => true];
        }

        // New purchase — create sale + plan + schedules
        $validFrom = now();
        $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $upeProduct->id,
            'sale_type' => 'paid',
            'pricing_mode' => 'installment',
            'base_fee_snapshot' => $coursePrice,
            'status' => 'pending_payment',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'razorpay_payment_id' => $razorpayPaymentId,
                'source' => 'quick_pay',
                'webinar_id' => $webinarId,
                'installment_id' => $installmentId,
            ]),
        ]);

        // Build schedule amounts from legacy installment config
        $itemPrice = $coursePrice;
        $scheduleAmounts = [];

        // Upfront
        $upfrontAmount = round($itemPrice * ($installment->upfront / 100), 2);
        $scheduleAmounts[] = ['amount' => $upfrontAmount, 'deadline_days' => 0];

        // Steps
        $steps = $installment->steps()->orderBy('order')->get();
        foreach ($steps as $step) {
            $stepAmount = round($step->getPrice($itemPrice), 2);
            $scheduleAmounts[] = ['amount' => $stepAmount, 'deadline_days' => (int) $step->deadline];
        }

        $totalAmount = round(array_sum(array_column($scheduleAmounts, 'amount')), 2);
        $numInstallments = count($scheduleAmounts);

        $plan = UpeInstallmentPlan::create([
            'sale_id' => $upeSale->id,
            'total_amount' => $totalAmount,
            'num_installments' => $numInstallments,
            'plan_type' => 'standard',
            'status' => 'active',
        ]);

        // Create all schedules
        foreach ($scheduleAmounts as $i => $sched) {
            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => $i + 1,
                'due_date' => now()->addDays($sched['deadline_days']),
                'amount_due' => $sched['amount'],
                'amount_paid' => 0,
                'status' => ($i === 0) ? 'due' : 'upcoming',
            ]);
        }

        // Apply the payment via InstallmentEngine waterfall
        $engine = app(InstallmentEngine::class);
        $engineResult = $engine->recordPayment(
            $plan, $amount, $paymentMethod, $razorpayPaymentId
        );

        // Check if fully paid → activate
        $plan->refresh();
        if ($plan->totalPaid() >= $plan->total_amount) {
            $upeSale->update(['status' => 'active']);
        }

        // Legacy dual-write
        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $webinar->creator_id ?? 1,
            'webinar_id' => $webinarId,
            'type' => 'installment_payment',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        // Legacy part payment record
        \App\Models\WebinarPartPayment::create([
            'user_id' => $userId,
            'webinar_id' => $webinarId,
            'installment_id' => $installmentId,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Accounting::create([
            'user_id' => $webinar->creator_id ?? 1,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => "Quick pay for: {$webinar->slug}",
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        $this->clearAccessCache($userId, $upeProduct->id);

        Log::info('CheckoutService: Quick pay purchase completed', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'upe_sale_id' => $upeSale->id, 'plan_id' => $plan->id,
            'amount' => $amount, 'engine_result' => $engineResult,
        ]);

        return ['upe_sale' => $upeSale, 'legacy_sale' => $legacySale, 'already_exists' => false];
    }

    /**
     * Process a part/installment payment — UPE-first flow.
     * Applies payment to the next unpaid UPE schedule via InstallmentEngine,
     * then does legacy dual-writes (WebinarPartPayment, Sale, Accounting) for backward compat.
     */
    public function processPartPayment(
        int $userId,
        int $webinarId,
        float $amount,
        ?int $installmentId = null,
        string $paymentMethod = 'razorpay',
        ?string $razorpayPaymentId = null,
        float $discount = 0
    ): array {
        $webinar = \App\Models\Webinar::findOrFail($webinarId);

        $productType = match ($webinar->type) {
            'webinar' => 'webinar',
            default => 'course_video',
        };
        $coursePrice = $webinar->getPrice() ?? $webinar->price;
        $upeProduct = $this->resolveProduct($webinarId, $productType, $coursePrice, $webinar->access_days);

        // Find existing UPE sale
        $existingSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $upeProduct->id)
            ->where('pricing_mode', 'installment')
            ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
            ->first();

        $engineResult = null;

        if ($existingSale) {
            // Subsequent payment — apply to existing plan via InstallmentEngine
            $plan = UpeInstallmentPlan::where('sale_id', $existingSale->id)
                ->whereIn('status', ['active', 'completed'])
                ->first();

            if ($plan) {
                $hasUnpaid = $plan->schedules()
                    ->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])
                    ->exists();

                if ($hasUnpaid) {
                    $engine = app(InstallmentEngine::class);
                    $engineResult = $engine->recordPayment(
                        $plan, $amount, $paymentMethod, $razorpayPaymentId
                    );
                }

                // Check if fully paid → activate
                if ($plan->totalPaid() >= $plan->total_amount) {
                    $existingSale->update(['status' => 'active']);
                }
            }

            // Legacy dual-write: Sale
            $legacySale = Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $webinar->creator_id ?? 1,
                'webinar_id' => $webinarId,
                'type' => 'installment_payment',
                'payment_method' => 'payment_channel',
                'amount' => $amount,
                'total_amount' => $amount,
                'created_at' => time(),
            ]);

            // Legacy dual-write: WebinarPartPayment
            \App\Models\WebinarPartPayment::create([
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'installment_id' => $installmentId,
                'amount' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Legacy dual-write: Accounting
            Accounting::create([
                'user_id' => $webinar->creator_id ?? 1,
                'sale_id' => $legacySale->id,
                'amount' => $amount,
                'type' => 'addiction',
                'description' => "Part payment for: {$webinar->slug}",
                'is_affiliate' => false,
                'is_cashback' => false,
                'store_type' => 'automatic',
                'tax' => 0,
                'commission' => 0,
                'discount' => $discount,
                'created_at' => time(),
            ]);

            $this->clearAccessCache($userId, $upeProduct->id);

            Log::info('CheckoutService: Part payment recorded (existing sale)', [
                'user_id' => $userId, 'webinar_id' => $webinarId,
                'upe_sale_id' => $existingSale->id, 'amount' => $amount,
                'engine_result' => $engineResult,
            ]);

            return ['upe_sale' => $existingSale, 'legacy_sale' => $legacySale, 'already_exists' => true, 'engine_result' => $engineResult];
        }

        // New installment purchase — create UPE sale + plan + schedules
        $validFrom = now();
        $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $upeProduct->id,
            'sale_type' => 'paid',
            'pricing_mode' => 'installment',
            'base_fee_snapshot' => $coursePrice,
            'status' => 'pending_payment',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'razorpay_payment_id' => $razorpayPaymentId,
                'source' => 'part_payment',
                'webinar_id' => $webinarId,
                'installment_id' => $installmentId,
            ]),
        ]);

        // Build schedule amounts from legacy installment config (one-time setup)
        $installment = $installmentId ? \App\Models\Installment::find($installmentId) : null;
        $itemPrice = $coursePrice - $discount;
        $scheduleAmounts = [];

        if ($installment) {
            $upfrontAmount = round($itemPrice * ($installment->upfront / 100), 2);
            $scheduleAmounts[] = ['amount' => $upfrontAmount, 'deadline_days' => 0];

            $steps = $installment->steps()->orderBy('order')->get();
            foreach ($steps as $step) {
                $stepAmount = round($step->getPrice($itemPrice), 2);
                $scheduleAmounts[] = ['amount' => $stepAmount, 'deadline_days' => (int) $step->deadline];
            }
        } else {
            $scheduleAmounts[] = ['amount' => $itemPrice, 'deadline_days' => 0];
        }

        $totalAmount = round(array_sum(array_column($scheduleAmounts, 'amount')), 2);
        $numInstallments = count($scheduleAmounts);

        $plan = UpeInstallmentPlan::create([
            'sale_id' => $upeSale->id,
            'total_amount' => $totalAmount,
            'num_installments' => $numInstallments,
            'plan_type' => 'standard',
            'status' => 'active',
        ]);

        foreach ($scheduleAmounts as $i => $sched) {
            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => $i + 1,
                'due_date' => now()->addDays($sched['deadline_days']),
                'amount_due' => $sched['amount'],
                'amount_paid' => 0,
                'status' => ($i === 0) ? 'due' : 'upcoming',
            ]);
        }

        // Apply the payment via InstallmentEngine waterfall
        $engine = app(InstallmentEngine::class);
        $engineResult = $engine->recordPayment(
            $plan, $amount, $paymentMethod, $razorpayPaymentId
        );

        // Check if fully paid → activate
        $plan->refresh();
        if ($plan->totalPaid() >= $plan->total_amount) {
            $upeSale->update(['status' => 'active']);
        }

        // Legacy dual-write: Sale
        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $webinar->creator_id ?? 1,
            'webinar_id' => $webinarId,
            'type' => 'installment_payment',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        // Legacy dual-write: WebinarPartPayment
        \App\Models\WebinarPartPayment::create([
            'user_id' => $userId,
            'webinar_id' => $webinarId,
            'installment_id' => $installmentId,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Legacy dual-write: Accounting
        Accounting::create([
            'user_id' => $webinar->creator_id ?? 1,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => "Part payment for: {$webinar->slug}",
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => $discount,
            'created_at' => time(),
        ]);

        $this->clearAccessCache($userId, $upeProduct->id);

        Log::info('CheckoutService: New part payment purchase', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'upe_sale_id' => $upeSale->id, 'plan_id' => $plan->id,
            'amount' => $amount, 'engine_result' => $engineResult,
        ]);

        return ['upe_sale' => $upeSale, 'legacy_sale' => $legacySale, 'already_exists' => false, 'engine_result' => $engineResult];
    }

    /**
     * Clear the AccessEngine cache for this user+product.
     */
    private function clearAccessCache(int $userId, int $productId): void
    {
        $cacheKey = AccessEngine::CACHE_PREFIX . "{$userId}_{$productId}";
        \Illuminate\Support\Facades\Cache::forget($cacheKey);
    }
}
