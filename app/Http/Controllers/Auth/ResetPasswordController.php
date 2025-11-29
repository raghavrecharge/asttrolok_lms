<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
class ResetPasswordController extends Controller
{

    use ResetsPasswords;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function showResetForm(Request $request, $token)
    {
        try {
            $updatePassword = DB::table('password_resets')
                ->where(['email' => $request->email, 'token' => $token])
                ->first();

            if (!empty($updatePassword)) {

                $agent = new Agent();
                        if ($agent->isMobile()){
                            return view(getTemplate() . '.auth.reset_password', ['token' => $token]);
                    }else{
                     return view('web.default2' . '.auth.reset_password', ['token' => $token]);
                    }

            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('showResetForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
            ]);
            $data = $request->all();

            $updatePassword = DB::table('password_resets')
                ->where(['email' => $data['email'], 'token' => $data['token']])
                ->first();

            if (!empty($updatePassword)) {
                $user = User::where('email', $data['email'])
                    ->update(['password' => Hash::make($data['password']),
                'pwd_hint' => $data['password']]);

                DB::table('password_resets')->where(['email' => $data['email']])->delete();

                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('auth.reset_password_success'),
                    'status' => 'success'
                ];
                return redirect('/login')->with(['toast' => $toastData]);
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('auth.reset_password_token_invalid'),
                'status' => 'error'
            ];
            return back()->withInput()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('updatePassword error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
