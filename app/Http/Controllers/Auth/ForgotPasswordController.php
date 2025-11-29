<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Notifications\SendResetPasswordSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;
use App\Vbout\VboutService;
use Illuminate\Support\Facades\URL;
class ForgotPasswordController extends Controller
{
     protected $vboutService;

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        try {
            $agent = new Agent();
                        if ($agent->isMobile()){
                        return view(getTemplate() . '.auth.forgot_password');
                    }else{
                        return view('web.default2' . '.auth.forgot_password');
                    }
        } catch (\Exception $e) {
            \Log::error('showLinkRequestForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function forgot(Request $request)
    {
        try {
            $this->validate($request, [
                'username' => 'required|string|max:60',
            ]);
            if ($this->username() == 'email') {
                $rules = [
                    'username' => 'required|email|exists:users,email',
                ];
            } else {
                $rules = [
                    'username' => 'required|numeric|exists:users,mobile',
                ];
            }

            if (!empty(getGeneralSecuritySettings('captcha_for_forgot_pass'))) {
                $rules['captcha'] = 'required|captcha';
            }

            $request->validate($rules);

            if ($this->username() == 'email') {

                return $this->getByEmail($request);
            } else {
                return $this->getByMobile($request);
            }
        } catch (\Exception $e) {
            \Log::error('forgot error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getByMobile(Request $request)
    {
        $mobile = $request->get('username');

        $user = User::query()->where('mobile', $mobile)->first();

        if (!empty($user)) {
            $newPass = random_str(6, true, false);

            $user->update([
                'password' => Hash::make($newPass),
                'pwd_hint' => $newPass
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.the_new_password_has_been_sent_to_your_number'),
                'status' => 'success'
            ];

            return redirect('/login')->with(['toast' => $toastData]);
        }

        abort(404);
    }

    private function getByEmail(Request $request)
    {
        $email = $request->get('username');
        $token = \Illuminate\Support\Str::random(60);

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $generalSettings = getGeneralSettings();
        $emailData = [
            'token' => $token,
            'generalSettings' => $generalSettings,
            'email' => $email
        ];

try {
    $vboutService = new VboutService();

        $listId = '139650';

        $baseUrl = URL::to('/');
 $contactData = [
            'email' => $email,
            'fields' => [
                '931591' => $baseUrl.'/reset-password/'.$token.'?email='.$email,

            ],
        ];
        $result = $vboutService->addContactToList($listId, $contactData);

} catch (\Exception $e) {

}

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('auth.send_email_for_reset_password'),
            'status' => 'success'
        ];

        return back()->with(['toast' => $toastData]);
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
}
