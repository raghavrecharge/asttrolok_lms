<?php

namespace App\Jobs;

use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use App\Models\WebinarAccessControl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentOverdueCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $openOrders = InstallmentOrder::whereIn('status', ['open', 'paying'])
                ->with(['installment.steps'])
                ->get();

            $overdueCount = 0;

            foreach ($openOrders as $order) {
                if ($order->checkOrderHasOverdue()) {
                    $overdueDays = $order->overdueDaysPast();

                    if ($overdueDays > 0) {
                        Log::info('Installment overdue detected', [
                            'installment_order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'webinar_id' => $order->webinar_id,
                            'overdue_days' => $overdueDays,
                        ]);

                        $overdueCount++;
                    }
                }
            }

            if ($overdueCount > 0) {
                Log::info("InstallmentOverdueCheckJob completed: {$overdueCount} overdue installments found.");
            }

        } catch (\Exception $e) {
            Log::error('InstallmentOverdueCheckJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
