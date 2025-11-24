<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
//use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Affiliate;
use App\Models\Role;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Models\Api\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Msg91Service;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    public function requestOtp(Request $request)
    {
        $rules = [
            'username' => 'required|string|numeric',
        ];

        if ($this->username() == 'email') {
            $rules['username'] = 'required|string|email';
            $rules['password'] = 'required|string|min:6';
        }
        validateParam($request->all(), $rules);

        return $this->attemptLogin($request);

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
  
   public function getData(Request $request)
    {
         Log::info('attandence get data ');
         return response()->json(["success"=> true,'message' => 'data fetch successfully .', 'data' => $request->all()]);
    }
    
    public function setData(Request $request)
    {
         Log::info('attandence set data');
         return response()->json(["success"=> true,'message' => 'data store successfully .', 'data' => $request->all()]);
    }
  

   public function attemptLogin(Request $request)
    {
        $username = $this->username(); // 'email' ya 'mobile'
        $inputUsername = $request->get('username');
        $inputPassword = $request->get('password');
    
        $userCase = User::where($username, $inputUsername)->first();
    
        if ($userCase) {
            if (!Hash::check($inputPassword, $userCase->password)) {
                return apiResponse2(0, 'INVALID_PASSWORD', 'The password you entered is incorrect.');
            }
    
            $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($userCase, $username, $inputUsername);
    
            if ($checkConfirmed['status'] == 'verified') {
                if ($userCase->full_name) {
                    return apiResponse2(0, 'already_registered', trans('api.auth.already_registered'));
                }
            }
    
            if ($username === 'mobile') {
                return $this->mobileWithOtp($request);
            } else {
                
                
                return $this->emailWithOtp($request);
            }
        } else {
            return apiResponse2(0, 'USER_NOT_FOUND', 'The user with the specified ID does not exist.');
        }
    
        $credentials = [
            $username => $inputUsername,
            'password' => $inputPassword,
        ];
    
        if (!$token = auth('api')->attempt($credentials)) {
            return apiResponse2(0, 'INVALID_CREDENTIALS', 'The username or password you entered is incorrect.');
        }
    
        return $this->afterLogged($request, $token);
    }



    public function afterLogged(Request $request, $token, $verify = false)
    {
       
        $user = auth('api')->user();

        if ($user->ban) {
            $time = time();
            $endBan = $user->ban_end_at;
            if (!empty($endBan) and $endBan > $time) {
                auth('api')->logout();
                return apiResponse2(0, 'banned_account', trans('auth.banned_account'));
            } elseif (!empty($endBan) and $endBan < $time) {
                $user->update([
                    'ban' => false,
                    'ban_start_at' => null,
                    'ban_end_at' => null,
                ]);
            }

        }

        if ($user->status != User::$active and !$verify) {
            // auth('api')->logout();
            auth('api')->logout();
            //  dd(apiAuth());
            $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($user, $this->username(), $request->input('username'));

            if ($checkConfirmed['status'] == 'send') {

                return apiResponse2(0, 'not_verified', trans('api.auth.not_verified'));

            } elseif ($checkConfirmed['status'] == 'verified') {
                $user->update([
                    'status' => User::$active,
                ]);
            }
        } elseif ($verify) {
            $user->update([
                'status' => User::$active,
            ]);

        }

        if ($user->status != User::$active) {
            \auth('api')->logout();
            return apiResponse2(0, 'inactive_account', trans('auth.inactive_account'));
        }

        $profile_completion = [];
        // $data= $user;
        // $data['token'] = $token;
        if (!$user->full_name) {
            $profile_completion[] = 'full_name';
            $data['profile_completion'] = $profile_completion;
        }
         return response()->json(["success"=> true,'message' => 'Login successfully.', 'user' => $user,'token'=>$token]);
        // return apiResponse2(1, 'login', trans('auth.login'), $data);


    }

    // public function logout()
    // {
    //     JWTAuth::parseToken()->invalidate();
    //     auth('api')->logout();
    //     if (!apiAuth()) {
    //         return apiResponse2(1, 'logout', trans('auth.logout'));
    //     }
    //     return apiResponse2(0, 'failed', trans('auth.logout.failed'));
    // }
     public function logout(Request $request)
    {
        try {
            // Attempt to log out the user using the provided token
            JWTAuth::parseToken()->invalidate();

            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token could not be parsed from the request'], 401);
        }
    }
    
    function sanitizeMobileNumber($mobile)
    {
      
        $mobile = preg_replace('/\D/', '', $mobile);
    
        if (strlen($mobile) > 10 ) {
            $mobile = substr($mobile, 2);
        }
    
        return $mobile;
    }
    
    function checkMobileExists($mobile)
    {
       
        // $sanitizedMobile = $this->sanitizeMobileNumber($mobile);
    
        $exists = User::where('mobile', $mobile)->exists();
        if($exists){
            $exists = DB::table('users')
        ->where('mobile', $mobile)
        ->orWhere('mobile', $mobile)
        ->exists();
        }
        return $exists;
    }
    
    
    
    
   public function mobileWithOtp(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'mobile' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 400);
        // }
        
        $mobile = $request->input('username');
        //  print_r($mobile);die;
        $user = User::where('mobile', $mobile)->first();
    //   $sanitizedMobile = $this->sanitizeMobileNumber($mobile);
        
        if (!$this->checkMobileExists($mobile)) {
            $user = User::whereRaw("RIGHT(mobile, 10) = ?", [$mobile])->get();
            // if(!$exists){
            // $user = User::create([
            //             'role_name' => Role::$user,
            //             'role_id' => Role::getUserRoleId(),
            //             'mobile' => $mobile,
            //             'status' => User::$pending,
            //             'password' => Hash::make(123456),
            //             // 'affiliate' => $usersAffiliateStatus,
            //             'created_at' => time()
            //         ]);
            // }
                  
        }
           
            $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($user, 'mobile', $mobile);
            
        // SMS::sendOtp($mobile, $otp);
     
        return response()->json(["success"=> true,'message' => 'OTP sent successfully.','status'=>$checkConfirmed['status'],'code'=>$checkConfirmed['code']->code]);
    }
    
    public function resendOtp(Request $request)
    {
        $username = $this->username(); // 'email' ya 'mobile'
        $value = $request->get('username'); // mobile number ya email
        $user = User::where($username, $value)->first();

        
        if (!$value) {
            return apiResponse2(0, 'USERNAME_REQUIRED', 'Username is required.');
        }
        if (!$user) {
             return apiResponse2(0, 'USER_NOT_FOUND', 'The user with the specified ID does not exist.');
        }
    
        $verificationController = new VerificationController();
        $response = $verificationController->checkConfirmed($user, $username, $value);
    
        if ($response['status'] == 'send') {
            return apiResponse2(1, 'OTP_RESENT', 'OTP has been resent successfully.');
        }
    
        return apiResponse2(0, 'ALREADY_VERIFIED', 'User is already verified.');
    }
     public function emailWithOtp(Request $request)
    {
       
        $email = $request->input('username');
        $user = User::where('email', $email)->first();
        if ($user) {
          
              $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($user, 'email', $email);
                
        }
          
            
        // SMS::sendOtp($mobile, $otp);
     
        return response()->json(["success"=> true,'message' => 'OTP sent successfully.','status'=>$checkConfirmed['status'],'code'=>$checkConfirmed['code']->code]);
    }

    public function loginApp(Request $request)
    {
       
        $mobile = $request->input('mobile');
        // $sanitizedMobile = $this->sanitizeMobileNumber($mobile);
      
        $user = User::whereRaw("RIGHT(mobile, 10) = ?", [$mobile])->first();
        if(!$user){
                  $user = User::where('mobile', $mobile)->first();
            }
       $token = JWTAuth::fromUser($user);
       
        return response()->json(["success"=> true,'message' => 'Login successfully.', 'user' => $user,'token'=>$token]);
    }
     public function loginAppWithemail(Request $request)
    {
       
        $email = $request->input('email');
        // $sanitizedMobile = $this->sanitizeMobileNumber($mobile);
      
       $user = User::where('email', $email)->first();
        if($user){
                 $token = JWTAuth::fromUser($user);
       
        return response()->json(["success"=> true,'message' => 'Login successfully.', 'user' => $user,'token'=>$token]);
            }else{
                 return response()->json(["error"=> false,'message' => 'Login failed.', 'user' => []]);
            }
      
    }
    public function sendOtp(Request $request)
{
    
     $validatedData =   $request->validate([
        'mobile' => ['required', 'digits:10', 'regex:/^[6-9]\d{9}$/'],
    ]);
  
    if(!empty($validatedData)){
    return response()->json(['message' => 'Validation Passed', 'data' => $validatedData], 401);
    }
      try {
    $mobile = $request->mobile; // User ka mobile number
    $otp = rand(100000, 999999);
    $message = "Your OTP is $otp. Valid for 10 minutes.";

    $msg91 = new Msg91Service();
    $response = $msg91->sendSms($mobile, $message);

    return response()->json($response);
      } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong!',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
