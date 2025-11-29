<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Role;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\User;

class SocialiteController extends Controller
{

    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->stateless()->redirect();
        } catch (\Exception $e) {
            \Log::error('redirectToGoogle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            validateParam($request->all(), [
                'email' => 'required|email',
                'name' => 'required',
                'id' => 'required'
            ]);
            $data = $request->all();
            $user = User::where('google_id', $data['id'])
                ->orWhere('email', $data['email'])
                ->first();
            $registered = true;
            if (empty($user)) {
                $registered = false;
                $user = User::create([
                    'full_name' => $data['name'],
                    'email' => $data['email'],
                    'google_id' => $data['id'],
                    'role_id' => Role::getUserRoleId(),
                    'role_name' => Role::$user,
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => time(),
                    'password' => null
                ]);
            }
            $user->update([
                'google_id' => $data['id'],
            ]);

            $data = [];
            $data['user_id']=$user->id ;
            $data['already_registered'] = $registered;
            if ($registered) {

                $token = auth('api')->tokenById($user->id);
                $data['token']=$token ;
                return apiResponse2(1, 'login', trans('api.auth.login'), $data);

            }
            return apiResponse2(1, 'registered', trans('api.auth.registered'), $data);
        } catch (\Exception $e) {
            \Log::error('handleGoogleCallback error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function redirectToFacebook()
    {
        try {
            return Socialite::driver('facebook')->stateless()->redirect();
        } catch (\Exception $e) {
            \Log::error('redirectToFacebook error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleFacebookCallback(Request $request)
    {
        try {
            validateParam($request->all(), [
                'email' => 'required|email',
                'name' => 'required',
                'id' => 'required'
            ]);
            $data = $request->all();
            $user = User::where('facebook_id', $data['id'])->orWhere('email', $data['email'])->first();
            $registered = true;
            if (empty($user)) {
                $registered = false;
                $user = User::create([
                    'full_name' => $data['name'],
                    'email' => $data['email'],
                    'facebook_id' => $data['id'],
                    'role_id' => Role::getUserRoleId(),
                    'role_name' => Role::$user,
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => time(),
                    'password' => null
                ]);
            }
            $data = [];
            $data['user_id']=$user->id ;
            $data['already_registered'] = $registered;
            if ($registered) {

                $token = auth('api')->tokenById($user->id);
               $data['token']= $token ;
                return apiResponse2(1, 'login', trans('api.auth.login'), $data);

            }
            return apiResponse2(1, 'registered', trans('api.auth.registered'), $data);
        } catch (\Exception $e) {
            \Log::error('handleFacebookCallback error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
