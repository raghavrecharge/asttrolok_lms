<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TransactionsHistoryRazorpay;
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

        BuyNowProcessJob::dispatch($jobData)->delay(now()->addSeconds(5));
        Log::info('Webhook dispatched BuyNowProcessJob for: ' . $razorpayPaymentId);
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