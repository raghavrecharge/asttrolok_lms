<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\OpsCockpitLogger;

class OpsCockpitMiddleware
{
    protected $logger;

    public function __construct(OpsCockpitLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        try {
            $user = $request->user();

            $this->logger->logRequest([
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $user ? $user->id : null,
                'user_email' => $user ? $user->email : null,
                'user_role' => $user ? ($user->role_name ?? null) : null,
                'referer' => $request->header('referer'),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            ]);
        } catch (\Throwable $e) {
            // Silently fail — never break request for logging
        }

        return $response;
    }
}
