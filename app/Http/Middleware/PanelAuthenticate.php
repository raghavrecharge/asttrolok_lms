<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PanelAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (auth()->check() and !auth()->user()->isAdmin()) {

            $referralSettings = getReferralSettings();
            view()->share('referralSettings', $referralSettings);


            $this->redirectIfPublic();
            return $next($request);
        }

        return redirect('/login');
    }
     function redirectIfPublic() {
   
    $requestUri = $_SERVER['REQUEST_URI'];
    
    if (strpos($requestUri, '/public/') === 0) {
        $newUri = str_replace('/public/', '/', $requestUri, $count);
        if ($count > 0) {
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $scheme . '://' . $host . $newUri;
            header("Location: $currentUrl");
            exit();
        }
    }
}
}
