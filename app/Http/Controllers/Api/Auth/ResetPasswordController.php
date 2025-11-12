<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    //   use ResetsPasswords;
    
    
   public function updatePassword(Request $request)
{
    $authUser = apiAuth();

    if (!$authUser) {
        return apiResponse2(0, 'UNAUTHORIZED', 'User not exists our record');
    }

    validateParam($request->all(), [
        'username' => 'required|string', // Email or mobile
        'password' => 'required|string|min:6|confirmed',
        'password_confirmation' => 'required',
    ]);

    $username = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
    
    $value = $request->get('username'); // mobile number ya email
    $user = User::where($username, $value)->first();

        if (!$user) {
             return apiResponse2(0, 'USER_NOT_FOUND', 'The user with the specified ID does not exist.');
        }
        
   
    $authUser->password = Hash::make($request->password);
    $authUser->save();

    return apiResponse2(1, 'PASSWORD_UPDATED', 'Your password has been updated successfully.');
}



    public function updatePasswordcopy(Request $request,$token)
    {

        validateParam($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);
$user = apiAuth();
        $data = $request->all();

        $updatePassword = DB::table('password_resets')
         //   ->where(['email' => $data['email'], 'token' => $data['token']])
            ->where(['email' => $data['email']])
            ->first();

        if (!empty($updatePassword)) {
            $user = User::where('email', $data['email'])
                ->update(['password' => Hash::make($data['password'])]);
            DB::table('password_resets')->where(['email' => $data['email']])->delete();
           return apiResponse2(1, 'reset', 'password reset');
        //   return apiResponse2(1, 'password reset.');
        }
        // return apiResponse2(1, 'reset', 'password reset');
        return apiResponse2(0, 'failed','there is not such request to reset password');

    }
}
