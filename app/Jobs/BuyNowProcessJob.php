<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Web\PaymentController;
use App\Models\TransactionsHistoryRazorpay;

class BuyNowProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;

    public function __construct($requestData)
    {
        Log::info('__construct');
        $this->requestData = $requestData;
    }

    public function handle()
    {
        $data = $this->requestData;
        $paymentId = $data['razorpay_payment_id'];

        try {
            Log::info('BuyNowProcessJob started', ['payment_id' => $paymentId]);

            // ✅ Check if already processed using YOUR table
            $transaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $paymentId)
                ->where('status', 'completed')
                ->whereNotNull('processed_at')
                ->first();

            if ($transaction) {
                Log::info('Payment already processed, skipping: ' . $paymentId);
                return;
            }

            // Your existing payment processing logic
            $PaymentController = new PaymentController();
            $PaymentController->paymentVerifyBackgroundProccess($data);

            // ✅ Mark as processed in YOUR table
            TransactionsHistoryRazorpay::where('razorpay_payment_id', $paymentId)
                ->update(['processed_at' => now()]);

            Log::info('BuyNowProcessJob completed successfully', ['payment_id' => $paymentId]);

        } catch (\Exception $e) {
            Log::error('Error in BuyNowProcessJob: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}