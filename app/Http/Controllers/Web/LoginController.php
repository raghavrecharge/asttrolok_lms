<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\CartManagerController;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\UserSession;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;
use App\Models\Webinar;
use App\Models\Sale;
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
                            return view(getTemplate() . '.course.landingPage.login', $data);
                    }else{
                        return view('web.default2' . '.course.landingPage.login', $data);
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
    public function showLoginFormEnglish()
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
                            return view(getTemplate() . '.course.landingPage.login-english', $data);
                    }else{
                        return view('web.default2' . '.course.landingPage.login-english', $data);
                    }
        } catch (\Exception $e) {
            \Log::error('showLoginFormEnglish error: ' . $e->getMessage(), [
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
            $slug = $request->get('slug');
            $user = auth()->user();

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

            $cartManagerController = new CartManagerController();
            $cartManagerController->storeCookieCartsToDB();

            if ($user->isAdmin()) {
                return redirect(getAdminPanelUrl() . '');
            } else {
                if(isset($slug)){
                $rdn='';
                $course = Webinar::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();
                    if(!($course->checkUserHasBought($user, true, true)))
            {

                if (!empty($course)) {
                    $checkCourseForSale = checkCourseForSale($course, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    if (!empty($course->price) and $course->price > 0) {
                        $toastData = [
                            'title' => trans('cart.fail_purchase'),
                            'msg' => trans('cart.course_not_free'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    Sale::create([
                        'buyer_id' => $user->id,
                        'seller_id' => $course->creator_id,
                        'webinar_id' => $course->id,
                        'type' => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount' => 0,
                        'total_amount' => 0,
                        'created_at' => time(),
                    ]);

                    date_default_timezone_set('Asia/Kolkata');

                $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY0MDYzNjA0Mzc1MjZhNTUzMDUxMzYi_pc';

            $webhookdata = [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'country_code' => $user->country_code ?? null,
            'user_mobile' => $user->mobile,
            'user_email' => $user->email,
            'user_role' => $user->role_name,
            'user_password' => $request->password,
            'slug' => $slug,
            'create_at' => date("Y/m/d H:i"),
            'by' =>'login'
            ];

            $webhookcurl = curl_init($webhookurl);

            curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($webhookcurl, CURLOPT_POST, true);

            curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

            $webhookresponse = curl_exec($webhookcurl);

            curl_close($webhookcurl);

            $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/caece889-e99d-4975-a107-341ef58c5f7f';
            if($slug=='learn-free-astrology-course-english'){
                $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/ff19d522-10e4-40e8-99b9-4c61796ac9a4';
            }

            $gohighlevelcurl = curl_init($gohighlevel);

            curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($gohighlevelcurl, CURLOPT_POST, true);

            curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));

            curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
            ]);

            $gohighlevelresponse = curl_exec($gohighlevelcurl);
                }
            }
            $rdn='/course/learning/'.$slug;

                if($rdn!=''){

                     return redirect($rdn);
                }else{
                return redirect('/panel');
                }

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
