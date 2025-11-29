<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\CartManagerController;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\UserSession;
use App\Models\LoginLog;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;
class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/panel';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        try {
            $seoSettings = getSeoMetas('login');
            $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.login_page_title');
            $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.login_page_title');
            $pageRobot = getPageRobot('login');

            $data = [
                'pageTitle' => $pageTitle,
                'pageDescription' => $pageDescription,
                'pageRobot' => $pageRobot,
            ];

            $agent = new Agent();
                        if ($agent->isMobile()){
                            return view(getTemplate() . '.auth.login', $data);
                    }else{
                        return view('web.default2' . '.auth.login', $data);
                    }
        } catch (\Exception $e) {
            \Log::error('showLoginForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'username' => 'required|numeric|max:60',
                'password' => 'required|min:6|max:40',
            ];

            if ($this->username() == 'email') {
                $rules['username'] = 'required|email';
            }

            if (!empty(getGeneralSecuritySettings('captcha_for_login'))) {
                $rules['captcha'] = 'required|captcha';
            }

            $this->validate($request, $rules);

            if ($this->attemptLogin($request)) {

                return $this->afterLogged($request);

            }

            return $this->sendFailedLoginResponse($request);
        } catch (\Exception $e) {
            \Log::error('login error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();

            $this->guard()->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            if (!empty($user) and $user->logged_count > 0) {
                $user->update([
                    'logged_count' => $user->logged_count - 1
                ]);
            }

            return redirect('/');
        } catch (\Exception $e) {
            \Log::error('logout error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function username()
    {
        try {
            $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

            if (empty($this->username)) {
                $this->username = 'mobile';
                if (preg_match($email_regex, request('username', null))) {
                    $this->username = 'email';
                }
            }
            return $this->username;
        } catch (\Exception $e) {
            \Log::error('username error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = [
            $this->username() => $request->get('username'),
            'password' => $request->get('password')
        ];
        $remember = true;

        return $this->guard()->attempt($credentials, $remember);
    }

    public function sendFailedLoginResponse(Request $request)
    {
        try {
            throw ValidationException::withMessages([
                'username' => [trans('validation.password_or_username')],
            ]);
        } catch (\Exception $e) {
            \Log::error('sendFailedLoginResponse error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    protected function sendBanResponse($user)
    {
        throw ValidationException::withMessages([
            'username' => [trans('auth.ban_msg', ['date' => dateTimeFormat($user->ban_end_at, 'j M Y')])],
        ]);
    }

    protected function sendNotActiveResponse($user)
    {
        $toastData = [
            'title' => trans('public.request_failed'),
            'msg' => trans('auth.login_failed_your_account_is_not_verified'),
            'status' => 'error'
        ];

        return redirect('/login')->with(['toast' => $toastData]);
    }

    protected function sendMaximumActiveSessionResponse()
    {
        $toastData = [
            'title' => trans('update.login_failed'),
            'msg' => trans('update.device_limit_reached_please_try_again'),
            'status' => 'error'
        ];

        return redirect('/login')->with(['login_failed_active_session' => $toastData]);
    }

    public function afterLogged(Request $request, $verify = false)
    {
        try {
            $user = auth()->user();
            $deviceId = $request->header('User-Agent');

            $user->update(['device_id' => $deviceId]);
            $token = bin2hex(random_bytes(64));
            $user->update(['token_login' => $token]);
            session()->put('token_login', $token);

            if ($user->ban) {
                $time = time();
                $endBan = $user->ban_end_at;
                if (!empty($endBan) and $endBan > $time) {
                    $this->guard()->logout();
                    $request->session()->flush();
                    $request->session()->regenerate();

                    return $this->sendBanResponse($user);
                } elseif (!empty($endBan) and $endBan < $time) {
                    $user->update([
                        'ban' => false,
                        'ban_start_at' => null,
                        'ban_end_at' => null,
                    ]);
                }
            }

            if ($user->status != User::$active and !$verify) {
                $this->guard()->logout();
                $request->session()->flush();
                $request->session()->regenerate();

                $verificationController = new VerificationController();
                $checkConfirmed = $verificationController->checkConfirmed($user, $this->username(), $request->get('username'));

                if ($checkConfirmed['status'] == 'send') {
                    return redirect('/verification');
                }
            } elseif ($verify) {
                session()->forget('verificationId');

                $user->update([
                    'status' => User::$active,
                ]);

                $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
                RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);
            }

            if ($user->status != User::$active) {
                $this->guard()->logout();
                $request->session()->flush();
                $request->session()->regenerate();

                return $this->sendNotActiveResponse($user);
            }

            $checkLoginDeviceLimit = $this->checkLoginDeviceLimit($user);
            if ($checkLoginDeviceLimit != "ok") {
                $this->guard()->logout();
                $request->session()->flush();
                $request->session()->regenerate();

                return $this->sendMaximumActiveSessionResponse();
            }

            $user->update([
                'logged_count' => (int)$user->logged_count + 1
            ]);

                $userAgent = $request->header('User-Agent');
            $parsed = $this->parseUserAgent($userAgent);
            date_default_timezone_set('Asia/Kolkata');
            LoginLog::create([
            'user_id'    => $user->id,
            'ip_address' => '0',
            'browser'    => $parsed['browser'],
            'device'     => $parsed['device'],
            'platform'   => $parsed['platform'],
            'login_at'   => now(),
            ]);

            $cartManagerController = new CartManagerController();
            $cartManagerController->storeCookieCartsToDB();

            if ($user->isAdmin()) {
                return redirect(getAdminPanelUrl() . '');
            } else {
                $rdn='';
                if ($request->has('rd')) {
            $rdn=$request->get('rd');
            }

                if($rdn!=''){

                     return redirect($rdn);
                }else{
                return redirect('/panel');
                }

            }
        } catch (\Exception $e) {
            \Log::error('afterLogged error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function parseUserAgent($userAgent)
{
        try {
            $browser = 'Unknown';
            $platform = 'Unknown';

            if (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac';
            } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
            } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
            } elseif (preg_match('/iphone|ipad/i', $userAgent)) {
            $platform = 'iOS';
            }

            if (preg_match('/MSIE/i', $userAgent) || preg_match('/Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
            } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
            } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
            } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
            } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
            } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
            }

            return [
            'browser'  => $browser,
            'platform' => $platform,
            'device'   => 'Unknown',
            ];
        } catch (\Exception $e) {
            \Log::error('parseUserAgent error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function checkLoginDeviceLimit($user)
    {
        $securitySettings = getGeneralSecuritySettings();

        if (!empty($securitySettings) and !empty($securitySettings['login_device_limit'])) {
            $limitCount = !empty($securitySettings['number_of_allowed_devices']) ? $securitySettings['number_of_allowed_devices'] : 1;

            $count = $user->logged_count;

            if ($count >= $limitCount) {
                return "no";
            }
        }

        return 'ok';
    }
}
