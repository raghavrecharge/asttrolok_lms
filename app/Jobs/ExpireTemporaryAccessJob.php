<?php

namespace App\Jobs;

use App\Models\WebinarAccessControl;
use App\Models\SupportAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireTemporaryAccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $expired = WebinarAccessControl::where('status', 'active')
                ->where('expire', '<', now())
                ->get();

            $count = 0;

            foreach ($expired as $access) {
                DB::transaction(function () use ($access) {
                    $access->update(['status' => 'expired']);
                });

                Log::info('Temporary access expired', [
                    'access_id' => $access->id,
                    'user_id' => $access->user_id,
                    'webinar_id' => $access->webinar_id,
                    'type' => $access->type ?? 'temporary',
                    'expired_at' => $access->expire,
                ]);

                $count++;
            }

            if ($count > 0) {
                Log::info("ExpireTemporaryAccessJob completed: {$count} access records expired.");
            }

        } catch (\Exception $e) {
            Log::error('ExpireTemporaryAccessJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
