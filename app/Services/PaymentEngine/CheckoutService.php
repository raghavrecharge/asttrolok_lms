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
        $upeProduct = $this->resolveProduct($webinarId, $productType, $webinar->slug ?? "webinar-{$webinarId}", $amount, $webinar->access_days);

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
            'sale_type' => 'new',
            'pricing_mode' => 'one_time',
            'base_fee_snapshot' => $amount,
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
        $this->ledger->appendEntry($upeSale->id, [
            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
            'direction' => UpeLedgerEntry::DIR_CREDIT,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => $paymentMethod,
            'gateway_reference' => $razorpayPaymentId,
            'description' => "Payment for course: {$webinar->slug}",
            'idempotency_key' => $razorpayPaymentId ? "rp_{$razorpayPaymentId}_webinar_{$webinarId}" : "checkout_webinar_{$userId}_{$webinarId}_" . time(),
        ]);

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

        $upeProduct = $this->resolveProduct($bundleId, 'bundle', $bundle->slug ?? "bundle-{$bundleId}", $amount, $bundle->access_days);

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
            'sale_type' => 'new',
            'pricing_mode' => 'one_time',
            'base_fee_snapshot' => $amount,
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'razorpay_payment_id' => $razorpayPaymentId,
                'source' => 'checkout',
                'bundle_id' => $bundleId,
            ]),
        ]);

        $this->ledger->appendEntry($upeSale->id, [
            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
            'direction' => UpeLedgerEntry::DIR_CREDIT,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => $paymentMethod,
            'gateway_reference' => $razorpayPaymentId,
            'description' => "Payment for bundle: {$bundle->slug}",
            'idempotency_key' => $razorpayPaymentId ? "rp_{$razorpayPaymentId}_bundle_{$bundleId}" : "checkout_bundle_{$userId}_{$bundleId}_" . time(),
        ]);

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

        $upeProduct = $this->resolveProduct($subscriptionId, 'subscription', $subscription->slug ?? "subscription-{$subscriptionId}", $amount, $subscription->access_days);

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
                'sale_type' => 'renewal',
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
                'sale_type' => 'new',
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

        $this->ledger->appendEntry($upeSale->id, [
            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
            'direction' => UpeLedgerEntry::DIR_CREDIT,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => $paymentMethod,
            'gateway_reference' => $razorpayPaymentId,
            'description' => "Subscription payment: {$subscription->slug}",
            'idempotency_key' => $razorpayPaymentId ? "rp_{$razorpayPaymentId}_sub_{$subscriptionId}" : "checkout_sub_{$userId}_{$subscriptionId}_" . time(),
        ]);

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
     * Process an installment upfront payment.
     * Creates UPE sale in pending_payment + installment plan + schedules.
     */
    public function processInstallmentPayment(int $userId, int $webinarId, float $amount, int $installmentPaymentId, string $paymentMethod = 'razorpay', ?string $razorpayPaymentId = null): array
    {
        $webinar = \App\Models\Webinar::findOrFail($webinarId);

        $productType = match ($webinar->type) {
            'webinar' => 'webinar',
            default => 'course_video',
        };
        $upeProduct = $this->resolveProduct($webinarId, $productType, $webinar->slug ?? "webinar-{$webinarId}", $webinar->price ?? $amount, $webinar->access_days);

        // Check if UPE sale already exists for this installment
        $existingSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $upeProduct->id)
            ->where('pricing_mode', 'installment')
            ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
            ->first();

        if ($existingSale) {
            // This is a subsequent installment payment — just add ledger entry
            $this->ledger->appendEntry($existingSale->id, [
                'entry_type' => UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                'direction' => UpeLedgerEntry::DIR_CREDIT,
                'amount' => $amount,
                'currency' => 'INR',
                'payment_method' => $paymentMethod,
                'gateway_reference' => $razorpayPaymentId,
                'description' => "Installment payment for: {$webinar->slug}",
                'idempotency_key' => $razorpayPaymentId ? "rp_{$razorpayPaymentId}_inst_{$installmentPaymentId}" : "inst_{$userId}_{$installmentPaymentId}_" . time(),
            ]);

            // Check if fully paid → activate
            $plan = UpeInstallmentPlan::where('sale_id', $existingSale->id)->first();
            if ($plan) {
                $totalPaid = $plan->totalPaid();
                if ($totalPaid >= $plan->total_amount) {
                    $existingSale->update(['status' => 'active']);
                }
            }

            $legacySale = Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $webinar->creator_id ?? 1,
                'webinar_id' => $webinarId,
                'installment_payment_id' => $installmentPaymentId,
                'type' => 'installment_payment',
                'payment_method' => 'payment_channel',
                'amount' => $amount,
                'total_amount' => $amount,
                'created_at' => time(),
            ]);

            $this->clearAccessCache($userId, $upeProduct->id);

            return ['upe_sale' => $existingSale, 'legacy_sale' => $legacySale, 'already_exists' => true];
        }

        // New installment purchase — create sale + plan
        $validFrom = now();
        $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $upeProduct->id,
            'sale_type' => 'new',
            'pricing_mode' => 'installment',
            'base_fee_snapshot' => $webinar->price ?? $amount,
            'status' => 'pending_payment',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'razorpay_payment_id' => $razorpayPaymentId,
                'source' => 'checkout',
                'webinar_id' => $webinarId,
                'installment_payment_id' => $installmentPaymentId,
            ]),
        ]);

        // Resolve installment plan details from legacy InstallmentOrder
        $installmentPaymentRecord = \App\Models\InstallmentOrderPayment::find($installmentPaymentId);
        $installmentOrder = $installmentPaymentRecord ? $installmentPaymentRecord->installmentOrder : null;
        $totalInstallments = 1;
        $totalAmount = $webinar->price ?? $amount;

        if ($installmentOrder && $installmentOrder->installment) {
            $totalInstallments = ($installmentOrder->installment->steps_count ?? 1) + 1; // +1 for upfront
            $totalAmount = $installmentOrder->item_price ?? $webinar->price ?? $amount;
        }

        $plan = UpeInstallmentPlan::create([
            'sale_id' => $upeSale->id,
            'total_amount' => $totalAmount,
            'total_installments' => $totalInstallments,
            'status' => 'active',
        ]);

        // Upfront schedule
        UpeInstallmentSchedule::create([
            'plan_id' => $plan->id,
            'installment_number' => 1,
            'due_date' => now(),
            'amount_due' => $amount,
            'status' => 'paid',
        ]);

        // Ledger entry for upfront payment
        $this->ledger->appendEntry($upeSale->id, [
            'entry_type' => UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
            'direction' => UpeLedgerEntry::DIR_CREDIT,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => $paymentMethod,
            'gateway_reference' => $razorpayPaymentId,
            'description' => "Installment upfront for: {$webinar->slug}",
            'idempotency_key' => $razorpayPaymentId ? "rp_{$razorpayPaymentId}_inst_{$installmentPaymentId}" : "inst_upfront_{$userId}_{$webinarId}_" . time(),
        ]);

        $this->audit->logSaleCreated($userId, 'student', $upeSale->toArray());

        // Legacy dual-write
        $legacySale = Sale::create([
            'buyer_id' => $userId,
            'seller_id' => $webinar->creator_id ?? 1,
            'webinar_id' => $webinarId,
            'installment_payment_id' => $installmentPaymentId,
            'type' => 'installment_payment',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        Accounting::create([
            'user_id' => $webinar->creator_id ?? 1,
            'installment_payment_id' => $installmentPaymentId,
            'sale_id' => $legacySale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => "Installment upfront payment",
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ]);

        $this->clearAccessCache($userId, $upeProduct->id);

        Log::info('CheckoutService: Installment purchase completed', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'upe_sale_id' => $upeSale->id, 'plan_id' => $plan->id,
        ]);

        return ['upe_sale' => $upeSale, 'legacy_sale' => $legacySale, 'already_exists' => false];
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
    private function resolveProduct(int $externalId, string $productType, string $name, float $baseFee, ?int $validityDays = null): UpeProduct
    {
        return UpeProduct::firstOrCreate(
            ['external_id' => $externalId, 'product_type' => $productType],
            [
                'name' => $name,
                'base_fee' => $baseFee,
                'validity_days' => $validityDays,
                'status' => 'active',
            ]
        );
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
