<?php

namespace App\Http\Controllers\Auth;

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
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        
        $agent = new Agent();
                    if ($agent->isMobile()){
                    return view(getTemplate() . '.auth.forgot_password');
                }else{
                    return view('web.default2' . '.auth.forgot_password');
                }
        // return view(getTemplate() . '.auth.forgot_password');
    }

    public function forgot(Request $request)
    {
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
    }

    private function getByMobile(Request $request)
    {
        $mobile = $request->get('username');

        $user = User::query()->where('mobile', $mobile)->first();

        if (!empty($user)) {
            $newPass = random_str(6, true, false);

            // $user->notify(new SendResetPasswordSMS($user, $newPass));

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
        // $contactData = [
        //     'email' => $request->input('email'),
        //     'resetlink' => 'https://lms.asttrolok.in/',
        //     // Add other contact fields as needed
        // ];
        $baseUrl = URL::to('/');
 $contactData = [
            'email' => $email,
            'fields' => [
                '931591' => $baseUrl.'/reset-password/'.$token.'?email='.$email,
               
                // Add other contact fields as needed
            ],
        ];
        $result = $vboutService->addContactToList($listId, $contactData);
        // Mail::send('web.default.auth.password_verify', $emailData, function ($message) use ($email) {
        //     $message->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        //     $message->to($email);
        //     $message->subject('Reset Password Notification');
        // });
} catch (\Exception $e) {
    // Log the error message if needed
    // Log::error('Mail sending failed: ' . $e->getMessage());
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
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (empty($this->username)) {
            $this->username = 'mobile';

            if (preg_match($email_regex, request('username', null))) {
                $this->username = 'email';
            }
        }
        return $this->username;
    }
}
