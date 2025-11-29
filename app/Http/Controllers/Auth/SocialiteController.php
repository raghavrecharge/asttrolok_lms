<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\User;

class SocialiteController extends Controller
{

    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            \Log::error('redirectToGoogle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function redirectToGoogle1()
    {
        try {
            $account = Socialite::driver('google')->user();

            $user = User::where('google_id', $account->id)
                ->orWhere('email', $account->email)
                ->first();

            if (empty($user)) {
                $user = User::create([
                    'full_name' => $account->name,
                    'email' => $account->email,
                    'google_id' => $account->id,
                    'role_id' => Role::getUserRoleId(),
                    'role_name' => Role::$user,
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => time(),
                    'password' => null
                ]);
            }

            $user->update([
                'google_id' => $account->id,
            ]);

            Auth::login($user);

            return redirect('/');
        } catch (Exception $e) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('auth.fail_login_by_google'),
                'status' => 'error'
            ];

            return redirect('/login');
        }
    }

    public function handleGoogleCallback()
    {
        try {
            $account = Socialite::driver('google')->user();

            $user = User::where('google_id', $account->id)
                ->orWhere('email', $account->email)
                ->first();

            if (empty($user)) {
                $user = User::create([
                    'full_name' => $account->name,
                    'email' => $account->email,
                    'google_id' => $account->id,
                    'role_id' => Role::getUserRoleId(),
                    'role_name' => Role::$user,
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => time(),
                    'password' => null
                ]);
            }

            $user->update([
                'google_id' => $account->id,
            ]);

            Auth::login($user);

            return redirect('/');
        } catch (Exception $e) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('auth.fail_login_by_google'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }

    public function redirectToFacebook()
    {
        try {
            return Socialite::driver('facebook')->redirect();
        } catch (\Exception $e) {
            \Log::error('redirectToFacebook error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleFacebookCallback()
    {
        try {
            $account = Socialite::driver('facebook')->user();

            $user = User::where('facebook_id', $account->id)->first();

            if (empty($user)) {
                $user = User::create([
                    'full_name' => $account->name,
                    'email' => $account->email,
                    'facebook_id' => $account->id,
                    'role_id' => Role::getUserRoleId(),
                    'role_name' => Role::$user,
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => time(),
                    'password' => null
                ]);
            }

            Auth::login($user);
            return redirect('/');
        } catch (Exception $e) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('auth.fail_login_by_facebook'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }
}
