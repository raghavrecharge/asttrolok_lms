<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Web\PaymentController;
use App\Models\TransactionsHistoryRazorpay;

class BuyNowProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    public function handle()
    {
        $data = $this->requestData;
        $paymentId = $data['razorpay_payment_id'];

        try {
            Log::info('BuyNowProcessJob started', ['payment_id' => $paymentId]);

            $shouldProcess = false;

            DB::transaction(function () use ($paymentId, &$shouldProcess) {
                $transaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $paymentId)
                    ->where('status', 'completed')
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    Log::warning('Transaction record not found for: ' . $paymentId);
                    return;
                }

                if ($transaction->processed_at !== null) {
                    Log::info('Payment already processed (atomic check), skipping: ' . $paymentId);
                    return;
                }

                $transaction->update(['processed_at' => now()]);
                $shouldProcess = true;
            });

            if (!$shouldProcess) {
                return;
            }

            $PaymentController = new PaymentController();
            $PaymentController->paymentVerifyBackgroundProccess($data);

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