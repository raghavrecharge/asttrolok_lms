<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
       
        if (Auth::guard($guard)->check()) {
           $user = Auth::guard('web')->user();
            if ($user->role->name === 'admin' && $user->role->is_admin === 1) {
                \Log::info('Redirecting admin to admin panel', ['user_id' => $user->id]);
            return redirect('/admin');
        }
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
