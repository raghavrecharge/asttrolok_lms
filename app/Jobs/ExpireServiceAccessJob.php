<?php

namespace App\Jobs;

use App\Models\ServiceAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireServiceAccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $expired = ServiceAccess::where('status', 'active')
                ->where('end_date', '<', now())
                ->get();

            $count = 0;

            foreach ($expired as $access) {
                DB::transaction(function () use ($access) {
                    $access->update(['status' => 'expired']);
                });

                Log::info('Service access expired', [
                    'access_id' => $access->id,
                    'user_id' => $access->user_id,
                    'service_type' => $access->service_type,
                    'end_date' => $access->end_date,
                ]);

                $count++;
            }

            if ($count > 0) {
                Log::info("ExpireServiceAccessJob completed: {$count} service access records expired.");
            }

        } catch (\Exception $e) {
            Log::error('ExpireServiceAccessJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
