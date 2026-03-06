<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;

class OpsCockpitExceptionReporter
{
    /**
     * Report an exception to OpsCockpit via Pub/Sub.
     */
    public static function report(\Throwable $e): void
    {
        try {
            $logger = app(\App\Services\OpsCockpitLogger::class);

            $logger->logError([
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 10),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_id' => auth()->id(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $reportException) {
            // Never let reporting break the app
            Log::debug('OpsCockpit exception reporter failed: ' . $reportException->getMessage());
        }
    }
}
