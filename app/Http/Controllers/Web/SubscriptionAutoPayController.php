<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Sale;
use App\Models\Accounting;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeSubscription;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

class SubscriptionAutoPayController extends Controller
{
    protected $razorpayApi;

    public function __construct()
    {
        $this->razorpayApi = new Api(
            env('RAZORPAY_API_KEY'),
            env('RAZORPAY_API_SECRET')
        );
    }

    /**
     * Create a Razorpay Subscription for AutoPay.
     * Called via AJAX from the payment page.
     *
     * POST /subscriptions/autopay/create
     */
    public function createSubscription(Request $request)
    {
        try {
            $validated = $request->validate([
                'subscription_id' => 'required|integer',
                'name' => 'required|string',
                'email' => 'required|email',
                'number' => 'required|string',
            ]);

            $subscription = Subscription::findOrFail($validated['subscription_id']);

            if (empty($subscription->razorpay_plan_id)) {
                return response()->json([
                    'error' => 'AutoPay is not configured for this subscription. No Razorpay Plan ID found.',
                ], 400);
            }

            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Authentication required.'], 401);
            }

            // Check if user already has an active Razorpay subscription for this product
            $upeProduct = UpeProduct::where('product_type', 'subscription')
                ->where('external_id', $subscription->id)
                ->first();

            if ($upeProduct) {
                $existingUpeSub = UpeSubscription::where('user_id', $user->id)
                    ->where('product_id', $upeProduct->id)
                    ->whereIn('status', ['active', 'trial'])
                    ->whereNotNull('gateway_subscription_id')
                    ->where('gateway_subscription_id', 'like', 'sub_%')
                    ->first();

                if ($existingUpeSub) {
                    return response()->json([
                        'error' => 'You already have an active AutoPay subscription.',
                    ], 400);
                }
            }

            // Create Razorpay Subscription
            $razorpaySubscription = $this->razorpayApi->subscription->create([
                'plan_id' => $subscription->razorpay_plan_id,
                'total_count' => $subscription->razorpay_plan_payments_total_count ?? 60,
                'customer_notify' => 1,
                'notes' => [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'user_name' => $validated['name'],
                    'user_email' => $validated['email'],
                ],
            ]);

            Log::info('Razorpay AutoPay subscription created', [
                'razorpay_subscription_id' => $razorpaySubscription->id,
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);

            return response()->json([
                'razorpay_subscription_id' => $razorpaySubscription->id,
                'key' => env('RAZORPAY_API_KEY'),
                'subscription_id' => $subscription->id,
                'amount' => $subscription->getPrice() * 100,
                'currency' => 'INR',
                'name' => $validated['name'],
                'email' => $validated['email'],
                'contact' => $validated['number'],
            ]);
        } catch (\Exception $e) {
            Log::error('AutoPay createSubscription error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => 'Failed to create AutoPay subscription: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify the first payment of AutoPay subscription after Razorpay checkout.
     * Called via form POST after successful Razorpay checkout.
     *
     * GET /subscriptions/autopay/verify
     */
    public function verifySubscription(Request $request)
    {
        try {
            $razorpayPaymentId = $request->input('razorpay_payment_id');
            $razorpaySubscriptionId = $request->input('razorpay_subscription_id');
            $razorpaySignature = $request->input('razorpay_signature');
            $subscriptionId = $request->input('subscription_id');

            if (empty($razorpayPaymentId) || empty($razorpaySubscriptionId) || empty($razorpaySignature)) {
                return redirect('/')->with(['toast' => [
                    'title' => 'Error',
                    'msg' => 'Payment verification failed. Missing parameters.',
                    'status' => 'error',
                ]]);
            }

            // Verify signature
            $expectedSignature = hash_hmac('sha256', $razorpayPaymentId . '|' . $razorpaySubscriptionId, env('RAZORPAY_API_SECRET'));

            if ($expectedSignature !== $razorpaySignature) {
                Log::error('AutoPay signature verification failed', [
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_subscription_id' => $razorpaySubscriptionId,
                ]);
                return redirect('/')->with(['toast' => [
                    'title' => 'Error',
                    'msg' => 'Payment verification failed. Invalid signature.',
                    'status' => 'error',
                ]]);
            }

            $user = auth()->user();
            if (!$user) {
                return redirect('/login');
            }

            $subscription = Subscription::findOrFail($subscriptionId);

            // Get the actual payment amount from Razorpay
            $razorpayPayment = $this->razorpayApi->payment->fetch($razorpayPaymentId);
            $amount = $razorpayPayment->amount / 100; // Convert paise to rupees

            DB::beginTransaction();

            // Process the first payment (same as one-time, but also store gateway_subscription_id)
            $this->processAutoPayFirstPayment(
                $user->id,
                $subscription,
                $amount,
                $razorpayPaymentId,
                $razorpaySubscriptionId
            );

            DB::commit();

            Log::info('AutoPay first payment verified', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'razorpay_subscription_id' => $razorpaySubscriptionId,
                'amount' => $amount,
            ]);

            return redirect($subscription->getLearningPageUrl())->with(['toast' => [
                'title' => 'Success',
                'msg' => 'AutoPay subscription activated! Your payment will be auto-deducted every month.',
                'status' => 'success',
            ]]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AutoPay verify error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/')->with(['toast' => [
                'title' => 'Error',
                'msg' => 'Payment processing failed. Please contact support.',
                'status' => 'error',
            ]]);
        }
    }

    /**
     * Razorpay Webhook handler for subscription events.
     *
     * POST /subscriptions/autopay/webhook
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();
            $webhookSignature = $request->header('X-Razorpay-Signature');
            $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET', env('RAZORPAY_API_SECRET'));

            // Verify webhook signature
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if ($expectedSignature !== $webhookSignature) {
                Log::warning('AutoPay webhook: Invalid signature');
                return response()->json(['status' => 'invalid_signature'], 400);
            }

            $event = $payload['event'] ?? '';
            $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
            $subscriptionEntity = $payload['payload']['subscription']['entity'] ?? [];

            Log::info('AutoPay webhook received', [
                'event' => $event,
                'razorpay_subscription_id' => $subscriptionEntity['id'] ?? 'unknown',
            ]);

            switch ($event) {
                case 'subscription.charged':
                    $this->handleSubscriptionCharged($paymentEntity, $subscriptionEntity);
                    break;

                case 'subscription.halted':
                    $this->handleSubscriptionHalted($subscriptionEntity);
                    break;

                case 'subscription.cancelled':
                    $this->handleSubscriptionCancelled($subscriptionEntity);
                    break;

                case 'subscription.completed':
                    $this->handleSubscriptionCompleted($subscriptionEntity);
                    break;

                default:
                    Log::info('AutoPay webhook: Unhandled event', ['event' => $event]);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('AutoPay webhook error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    // ── Private helpers ──

    /**
     * Process the first AutoPay payment — creates all records + stores gateway_subscription_id.
     */
    private function processAutoPayFirstPayment(int $userId, Subscription $subscription, float $amount, string $razorpayPaymentId, string $razorpaySubscriptionId)
    {
        // 1+2. Legacy: record payment + sync SubscriptionAccess via shared service.
        // Renewal rule (max of fresh vs extended end date) is enforced inside the service.
        $accessService = app(\App\Services\SubscriptionAccessService::class);
        $accessService->syncAccessAfterPayment($userId, $subscription->id, $amount);

        // 3. UPE: CheckoutService for UPE records (UpeSale, UpeSubscription, Ledger, legacy Sale+Accounting)
        $checkout = app(CheckoutService::class);
        $result = $checkout->processSubscriptionPurchase($userId, $subscription->id, $amount, 'razorpay', $razorpayPaymentId);

        // 4. Store gateway_subscription_id on UpeSubscription for recurring billing
        $upeProduct = UpeProduct::where('product_type', 'subscription')
            ->where('external_id', $subscription->id)
            ->first();

        if ($upeProduct) {
            $upeSubscription = UpeSubscription::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->whereIn('status', ['active', 'trial'])
                ->orderByDesc('id')
                ->first();

            if ($upeSubscription) {
                $upeSubscription->update([
                    'gateway_subscription_id' => $razorpaySubscriptionId,
                ]);

                Log::info('AutoPay: gateway_subscription_id stored', [
                    'upe_subscription_id' => $upeSubscription->id,
                    'gateway_subscription_id' => $razorpaySubscriptionId,
                ]);
            }
        }
    }

    /**
     * Handle recurring payment charged by Razorpay (webhook: subscription.charged).
     * This processes every monthly auto-deduction AFTER the first payment.
     */
    private function handleSubscriptionCharged(array $paymentEntity, array $subscriptionEntity)
    {
        $razorpaySubscriptionId = $subscriptionEntity['id'] ?? null;
        $razorpayPaymentId = $paymentEntity['id'] ?? null;
        $amount = ($paymentEntity['amount'] ?? 0) / 100;

        if (empty($razorpaySubscriptionId) || empty($razorpayPaymentId)) {
            Log::warning('AutoPay charged: Missing subscription_id or payment_id');
            return;
        }

        // Find UpeSubscription by gateway_subscription_id
        $upeSubscription = UpeSubscription::where('gateway_subscription_id', $razorpaySubscriptionId)->first();

        if (!$upeSubscription) {
            Log::warning('AutoPay charged: No UpeSubscription found', [
                'razorpay_subscription_id' => $razorpaySubscriptionId,
            ]);
            return;
        }

        $userId = $upeSubscription->user_id;
        $upeProduct = $upeSubscription->product;

        if (!$upeProduct || $upeProduct->product_type !== 'subscription') {
            Log::warning('AutoPay charged: Invalid product', ['upe_subscription_id' => $upeSubscription->id]);
            return;
        }

        $subscription = Subscription::find($upeProduct->external_id);
        if (!$subscription) {
            Log::warning('AutoPay charged: Subscription not found', ['external_id' => $upeProduct->external_id]);
            return;
        }

        // Idempotency: check if this payment was already processed
        $idempotencyKey = "autopay_charged_{$razorpayPaymentId}";
        $existingLedger = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->exists();
        if ($existingLedger) {
            Log::info('AutoPay charged: Already processed (idempotent)', [
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            // 1+2. Legacy: record payment + sync SubscriptionAccess via shared service.
            // Renewal rule (max of fresh vs extended end date) is enforced inside the service.
            $accessService = app(\App\Services\SubscriptionAccessService::class);
            $accessService->syncAccessAfterPayment($userId, $subscription->id, $amount);

            // 3. UPE: Create renewal sale + ledger entry
            $upeSale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'subscription',
                'base_fee_snapshot' => $amount,
                'status' => 'active',
                'valid_from' => now(),
                'valid_until' => now()->addDays($subscription->access_days),
                'metadata' => json_encode([
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_subscription_id' => $razorpaySubscriptionId,
                    'source' => 'autopay_webhook',
                    'subscription_id' => $subscription->id,
                    'upe_subscription_id' => $upeSubscription->id,
                ]),
            ]);

            // 4. UPE: Ledger entry
            $ledger = app(PaymentLedgerService::class);
            $ledger->append(
                $upeSale->id,
                UpeLedgerEntry::TYPE_PAYMENT,
                UpeLedgerEntry::DIR_CREDIT,
                $amount,
                'razorpay',
                $razorpayPaymentId,
                null,
                null,
                null,
                "AutoPay subscription renewal: {$subscription->slug}",
                null,
                $idempotencyKey
            );

            // 5. Update UpeSubscription period
            $newPeriodEnd = $upeSubscription->current_period_end
                ? $upeSubscription->current_period_end->copy()->addDays($subscription->access_days)
                : now()->addDays($subscription->access_days);

            $upeSubscription->update([
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => $newPeriodEnd,
            ]);

            // 6. Legacy dual-write: Sale + Accounting
            Sale::create([
                'buyer_id' => $userId,
                'seller_id' => $subscription->creator_id ?? 1,
                'subscription_id' => $subscription->id,
                'type' => 'subscription',
                'payment_method' => 'payment_channel',
                'amount' => $amount,
                'total_amount' => $amount,
                'created_at' => time(),
            ]);

            Accounting::create([
                'user_id' => $subscription->creator_id ?? 1,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'type' => 'addiction',
                'description' => 'AutoPay Subscription: ' . ($subscription->title ?? $subscription->slug),
                'is_affiliate' => false,
                'is_cashback' => false,
                'store_type' => 'automatic',
                'tax' => 0,
                'commission' => 0,
                'discount' => 0,
                'created_at' => time(),
            ]);

            DB::commit();

            Log::info('AutoPay charged processed successfully', [
                'user_id' => $userId,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'paid_count' => $paidCount,
                'access_content_count' => $accessContentCount,
                'access_till_date' => date('Y-m-d', $accessTillDate),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AutoPay charged processing failed: ' . $e->getMessage(), [
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_subscription_id' => $razorpaySubscriptionId,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle subscription halted (payment failed after retries).
     */
    private function handleSubscriptionHalted(array $subscriptionEntity)
    {
        $razorpaySubscriptionId = $subscriptionEntity['id'] ?? null;

        $upeSubscription = UpeSubscription::where('gateway_subscription_id', $razorpaySubscriptionId)->first();

        if ($upeSubscription) {
            $upeSubscription->update(['status' => 'past_due']);

            Log::warning('AutoPay halted: Subscription marked past_due', [
                'upe_subscription_id' => $upeSubscription->id,
                'user_id' => $upeSubscription->user_id,
                'razorpay_subscription_id' => $razorpaySubscriptionId,
            ]);
        }
    }

    /**
     * Handle subscription cancelled (by user or admin).
     */
    private function handleSubscriptionCancelled(array $subscriptionEntity)
    {
        $razorpaySubscriptionId = $subscriptionEntity['id'] ?? null;

        $upeSubscription = UpeSubscription::where('gateway_subscription_id', $razorpaySubscriptionId)->first();

        if ($upeSubscription) {
            $upeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            Log::info('AutoPay cancelled', [
                'upe_subscription_id' => $upeSubscription->id,
                'user_id' => $upeSubscription->user_id,
                'razorpay_subscription_id' => $razorpaySubscriptionId,
            ]);
        }
    }

    /**
     * Handle subscription completed (all payments done).
     */
    private function handleSubscriptionCompleted(array $subscriptionEntity)
    {
        $razorpaySubscriptionId = $subscriptionEntity['id'] ?? null;

        $upeSubscription = UpeSubscription::where('gateway_subscription_id', $razorpaySubscriptionId)->first();

        if ($upeSubscription) {
            $upeSubscription->update(['status' => 'expired']);

            Log::info('AutoPay completed (all payments done)', [
                'upe_subscription_id' => $upeSubscription->id,
                'user_id' => $upeSubscription->user_id,
                'razorpay_subscription_id' => $razorpaySubscriptionId,
            ]);
        }
    }

    /**
     * Cancel AutoPay subscription (user-initiated).
     *
     * POST /subscriptions/autopay/cancel
     */
    public function cancelSubscription(Request $request)
    {
        try {
            $validated = $request->validate([
                'subscription_id' => 'required|integer',
            ]);

            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Authentication required.'], 401);
            }

            $subscription = Subscription::findOrFail($validated['subscription_id']);

            $upeProduct = UpeProduct::where('product_type', 'subscription')
                ->where('external_id', $subscription->id)
                ->first();

            if (!$upeProduct) {
                return response()->json(['error' => 'Subscription product not found.'], 404);
            }

            $upeSubscription = UpeSubscription::where('user_id', $user->id)
                ->where('product_id', $upeProduct->id)
                ->whereIn('status', ['active', 'trial'])
                ->whereNotNull('gateway_subscription_id')
                ->where('gateway_subscription_id', 'like', 'sub_%')
                ->first();

            if (!$upeSubscription) {
                return response()->json(['error' => 'No active AutoPay subscription found.'], 404);
            }

            // Cancel on Razorpay
            $this->razorpayApi->subscription->fetch($upeSubscription->gateway_subscription_id)->cancel([
                'cancel_at_cycle_end' => 1, // Cancel at end of current billing cycle
            ]);

            $upeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            Log::info('AutoPay cancelled by user', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'razorpay_subscription_id' => $upeSubscription->gateway_subscription_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'AutoPay has been cancelled. Your access continues until the end of the current billing period.',
            ]);
        } catch (\Exception $e) {
            Log::error('AutoPay cancel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => 'Failed to cancel AutoPay: ' . $e->getMessage()], 500);
        }
    }
}
