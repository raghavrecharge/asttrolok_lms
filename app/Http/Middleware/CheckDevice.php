<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDevice
{
    public function handle(Request $request, Closure $next)
    {
        
        if (Auth::check()) {
            $user = Auth::user();
            //   print_r($user->device_id);
            $currentDeviceId = $request->header('User-Agent');
// print_r($currentDeviceId);
            if ($user->device_id && $user->device_id !== $currentDeviceId) {
                if($user->id != 1){
                Auth::logout();
                return redirect('/login')->with('error', 'You have been logged out because you logged in from another device.');
                }
            }
        }

        return $next($request);
    }
}
?>