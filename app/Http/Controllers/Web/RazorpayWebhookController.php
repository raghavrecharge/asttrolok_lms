<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\TransactionsHistoryRazorpay;
use App\Models\PaymentEngine\WalletTransaction;
use App\Services\PaymentEngine\WalletService;
use App\Jobs\BuyNowProcessJob;

class RazorpayWebhookController extends Controller
{
    protected $webhookSecret;

    public function __construct()
    {
        $this->webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');
    }

    public function handle(Request $request)
    {
        try {
            Log::info('Razorpay Webhook Received', $request->all());

            if (!$this->verifySignature($request)) {
                Log::error('Webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload);
                    break;
                case 'payment.failed':
                    $this->handlePaymentFailed($payload);
                    break;
                default:
                    Log::info('Unhandled webhook event: ' . $event);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    protected function verifySignature(Request $request)
    {
        $webhookSignature = $request->header('X-Razorpay-Signature');
        $webhookBody = $request->getContent();

        if (!$webhookSignature || !$this->webhookSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $webhookBody, $this->webhookSecret);
        return hash_equals($expectedSignature, $webhookSignature);
    }

    protected function handlePaymentCaptured($payload)
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;
        if (!$payment) return;

        $razorpayPaymentId = $payment['id'];

        $existing = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)
            ->where('status', 'completed')
            ->whereNotNull('processed_at')
            ->first();

        if ($existing) {
            Log::info('Payment already processed: ' . $razorpayPaymentId);
            return;
        }

        $notes = $payment['notes'] ?? [];

        TransactionsHistoryRazorpay::updateOrCreate(
            ['razorpay_payment_id' => $razorpayPaymentId],
            [
                'razorpay_order_id' => $payment['order_id'],
                'order_id' => $notes['order_id'] ?? null,
                'payment_type' => $notes['payment_type'] ?? 'webinar',
                'user_id' => $notes['user_id'] ?? null,
                'name' => $notes['name'] ?? null,
                'email' => $payment['email'] ?? $notes['email'] ?? null,
                'number' => $payment['contact'] ?? $notes['mobile'] ?? null,
                'amount' => $payment['amount'] / 100,
                'status' => 'completed',
                'payment_method' => $payment['method'] ?? null,
                'source' => 'webhook',
                'metadata' => json_encode($notes),
                'razorpay_description' => $notes['description'] ?? 'Payment',
            ]
        );

        $jobData = [
            'razorpay_payment_id' => $razorpayPaymentId,
            'order_id' => $notes['order_id'] ?? null,
            'payment_type' => $notes['payment_type'] ?? 'webinar',
            'user_id' => $notes['user_id'] ?? null,
            'name' => $notes['name'] ?? null,
            'email' => $payment['email'] ?? $notes['email'] ?? null,
            'number' => $payment['contact'] ?? $notes['mobile'] ?? null,
            'subscription_id' => $notes['subscription_id'] ?? null,
            'webinar_id' => $notes['webinar_id'] ?? null,
            'discount_id' => $notes['discount_id'] ?? null,
            'installment_payment_id' => $notes['installment_payment_id'] ?? null,
            'gateway' => 'Razorpay',
            'installment_id' => $notes['installment_id'] ?? null,
            'amount' => $notes['amount'] ?? null,
            'reserve_meeting_id' => $notes['reserve_meeting_id'] ?? null
        ];

        // Wallet-mediated: credit gateway to wallet, then debit full purchase from wallet
        $this->processWalletMediatedPayment($notes['order_id'] ?? null, $razorpayPaymentId);

        BuyNowProcessJob::dispatch($jobData)->delay(now()->addSeconds(5));
        Log::info('Webhook dispatched BuyNowProcessJob for: ' . $razorpayPaymentId);
    }

    /**
     * Wallet-mediated payment processing for webhook path.
     * Same logic as PaymentController::processWalletMediatedPayment().
     */
    protected function processWalletMediatedPayment($orderId, $razorpayPaymentId = null)
    {
        if (empty($orderId)) return;

        try {
            $order = Order::find($orderId);
            if (!$order || !$order->payment_data) return;

            $paymentData = json_decode($order->payment_data, true);
            if (!empty($paymentData['wallet_mediated_processed'])) return;

            $walletDeduction = (float) ($paymentData['wallet_deduction'] ?? 0);
            $originalAmount = (float) ($paymentData['original_amount'] ?? 0);
            if ($originalAmount <= 0) return;

            $userId = $order->user_id;
            if (!$userId) return;

            // Idempotency check
            $existing = WalletTransaction::where('reference_type', 'order')
                ->where('reference_id', $orderId)
                ->where('transaction_type', WalletTransaction::TXN_WALLET_PURCHASE)
                ->first();
            if ($existing) {
                Log::info('Webhook: Wallet-mediated already processed', ['order_id' => $orderId]);
                return;
            }

            $walletSvc = app(WalletService::class);
            $gatewayAmount = $originalAmount - $walletDeduction;

            // Step 1: Credit gateway amount to wallet
            if ($gatewayAmount > 0) {
                $walletSvc->creditFromGateway($userId, $gatewayAmount, (int) $orderId, $razorpayPaymentId);
            }

            // Step 2: Debit full purchase amount from wallet
            $walletSvc->purchaseFromWallet($userId, $originalAmount, (int) $orderId, 'Purchase for order #' . $orderId);

            // Update transaction amount to full purchase amount
            $txn = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)->first();
            if ($txn) {
                $txn->update(['amount' => $originalAmount]);
            }

            $paymentData['wallet_mediated_processed'] = true;
            $order->update(['payment_data' => json_encode($paymentData)]);

            Log::info('Webhook: Wallet-mediated payment completed', [
                'user_id' => $userId, 'gateway' => $gatewayAmount,
                'wallet_used' => $walletDeduction, 'total' => $originalAmount, 'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook: Wallet-mediated failed (non-fatal): ' . $e->getMessage(), ['order_id' => $orderId]);
        }
    }

    protected function handlePaymentFailed($payload)
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;
        if (!$payment) return;

        TransactionsHistoryRazorpay::updateOrCreate(
            ['razorpay_payment_id' => $payment['id']],
            [
                'razorpay_order_id' => $payment['order_id'],
                'status' => 'failed',
                'amount' => $payment['amount'] / 100,
                'source' => 'webhook',
            ]
        );

        Log::warning('Payment failed: ' . $payment['id']);
    }
}