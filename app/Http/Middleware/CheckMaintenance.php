<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckMaintenance
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
         if (Auth::check()) {
            if (Auth::user()->token_login != session()->get('token_login') && Auth::user()->id != 1 && !session()->has('impersonated')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/');
            }
        }
        $route = $request->getPathInfo();
        // if ($request->path() !== '/' && substr($request->path(), -1) === '/') {
        //     // Redirect to the URL without the last trailing slash
        //     $cleanedUri = rtrim($request->getRequestUri(), '/');

        //     return redirect($cleanedUri, 301);
        // }
        $ignoreRoutes = [
            '/maintenance',
            '/locale',
        ];
               
        if (!in_array($route, $ignoreRoutes) and !request()->is('laravel-filemanager*')) {
            if (!empty(getFeaturesSettings('maintenance_status')) and getFeaturesSettings('maintenance_status')) {
                $maintenanceAccessKey = getFeaturesSettings('maintenance_access_key');

                if (!empty($maintenanceAccessKey) and !empty($request->get($maintenanceAccessKey))) {
                    return $next($request);
                }

                return redirect(route('maintenanceRoute'));
            }
        }
        
          $this->redirectIfPublic();
        return $next($request);
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
