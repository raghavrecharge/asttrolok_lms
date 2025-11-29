<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\UserCookieSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CookieSecurityController extends Controller
{
    public $cookieKey = 'cookie-security';

    public function setAll()
    {
        try {
            $this->handleStore(UserCookieSecurity::$ALL, null);

            return response()->json([
                'code' => 200,
                'msg' => trans('update.cookie_security_successfully_submitted')
            ]);
        } catch (\Exception $e) {
            \Log::error('setAll error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 public function setreject()
    {
        try {
            Cookie::queue(Cookie::forget('cookie_consent'));
            Cookie::queue(Cookie::forget('custom_cookies'));
            return response()->json(['code' => 200, 'msg' => __('You have rejected all cookies.')]);
        } catch (\Exception $e) {
            \Log::error('setreject error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function setCustomize(Request $request)
    {
        try {
            $settings = $request->get('settings');

            $this->handleStore(UserCookieSecurity::$CUSTOMIZE, json_encode($settings));

            return response()->json([
                'code' => 200,
                'msg' => trans('update.cookie_security_successfully_submitted')
            ]);
        } catch (\Exception $e) {
            \Log::error('setCustomize error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleStore($type, $settings)
    {
        $user = auth()->user();

        $data = [
            'type' => $type,
            'settings' => $settings,
            'created_at' => time()
        ];

        if (!empty($user)) {
            UserCookieSecurity::updateOrCreate([
                'user_id' => $user->id,
            ],
                $data
            );
        } else {
            Cookie::queue($this->cookieKey, json_encode($data), 30 * 24 * 60,'/',
                '.asttrolok.com',
                true,
                true,
                false,
                'None'
                );
        }

    }
}
