<?php

namespace App\Http\Middleware;

use App\Models\SubAdminActivityLog;
use Closure;

class SubAdminActivityLogger
{
    /**
     * Log all write actions (POST, PUT, PATCH, DELETE) by sub-admins.
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated sub-admin users on write operations
        if (
            auth()->check() &&
            auth()->user()->role_name === 'sub_admin' &&
            in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
        ) {
            try {
                $section = $this->guessSectionFromUrl($request->path());

                SubAdminActivityLog::log(
                    auth()->id(),
                    'admin_action',
                    $request->method() . ' ' . $request->path(),
                    [
                        'section' => $section,
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('SubAdminActivityLogger error: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Guess the admin section from the URL path.
     */
    private function guessSectionFromUrl($path)
    {
        // Remove admin prefix
        $path = preg_replace('#^[^/]+/#', '', $path);

        // Extract the first segment as the section
        $segments = explode('/', $path);
        return $segments[0] ?? 'unknown';
    }
}
