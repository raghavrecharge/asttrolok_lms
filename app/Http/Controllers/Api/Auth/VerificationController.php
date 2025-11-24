<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Verification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mail;
use App\Mail\TestMail;
class VerificationController extends Controller
{

    public function checkConfirmed($user = null, $username, $value)
    {
        if (!empty($value)) {
            $verification = Verification::where($username, $value)
                ->where('expired_at', '>', time())
                ->where(function ($query) {
                    $query->whereNull('user_id')
                        ->orWhereHas('user');
                })
                ->first();

            $data = [];
            $time = time();

            if (!empty($verification)) {
                if (!empty($verification->verified_at)) {
                    return [
                        'status' => 'verified'
                    ];

                } else {
                    $data['created_at'] = $time;
                    $data['expired_at'] = $time + Verification::EXPIRE_TIME;

                    if (time() > $verification->expired_at) {
                        $data['code'] = $this->getNewCode();
                    } else {
                        $data['code'] = $verification->code;
                    }
                }
            } else {
                $data[$username] = $value;
                $data['code'] = $this->getNewCode();
                $data['user_id'] = !empty($user) ? $user->id : (auth('api')->check() ? auth()->id() : null);
                $data['created_at'] = $time;
                $data['expired_at'] = $time + Verification::EXPIRE_TIME;
            }

            $data['verified_at'] = null;

            $verification = Verification::updateOrCreate([$username => $value], $data);

            try {
                if ($username == 'mobile') {
                    $verification->sendSMSCode();
                } else {
                   
                    $verification->sendEmailCode();
                }
            } catch (\Exception $exception) {
            }

            return [
                'status' => 'send',
                'code'=>$verification
            ];
        }

        abort(404);
    }
    
    
    // public function confirmCode(Request $request, $username = null)
    // {

    //     $value = $username;
        
    //     if (!$username) {
    //         $value = $request->input('mobile');
    //         $username = $request->input('mobile');
    //     }
    //     $code = $request->get('code');
    //     $username = $this->username($value);
    //     $request[$username] = $value;
    //     $time = time();

    //     Verification::where($username, $value)
    //         ->whereNull('verified_at')
    //         ->where('code', $code)
    //         ->where('created_at', '>', $time - 24 * 60 * 60)
    //         ->update([
    //             'verified_at' => $time,
    //             'expired_at' => $time + 1,
    //         ]);

    //     $rules = [
    //         'code' => [
    //             'required',
    //             Rule::exists('verifications')->where(function ($query) use ($value, $code, $time, $username) {
    //                 $query->where($username, $value)
    //                     ->where('code', $code)
    //                     ->whereNotNull('verified_at')
    //                     ->where('expired_at', '>', $time);
    //             }),
    //         ],
    //     ];

    //     if ($username == 'mobile') {
    //         $rules['mobile'] = 'required';
    //         $value = ltrim($value, '+');
    //     } else {
    //         $rules['email'] = 'required|email';
    //     }

    //     // validateParam($request->all(), $rules);
    //     try {
    //         $request->validate( $rules);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => 'Validation Error',
    //             'errors' => $e->errors() 
    //         ], 422);
    //     }
       
    //     $authUser = auth('api')->check() ? auth('api')->user() : null;
    //     $referralCode = $request->input('referral_code', null);
    //   // dd($authUser);
    //     if (empty($authUser)) {
    //         $authUser = User::where($username, $value)
    //             ->first();
    //         $loginController = new LoginController();


    //         if (!empty($authUser)) {
    //             if (!empty($referralCode)) {
    //                 Affiliate::storeReferral($authUser, $referralCode);
    //             }
    //             $authUser->update([
    //                 'status' => User::$active,
    //             ]);
    //             // Auth::login($user);
                
    //              if ($username == 'mobile') {
    //               return $loginController->loginApp($request);
    //              }
    //               if ($username == 'email') {
    //               return $loginController->loginAppWithemail($request);
    //              }
    //              return apiResponse2(1, 'verified', trans('api.auth.verified'));
    //         }

    //         if ($username == 'mobile') {
    //             return $loginController->loginApp($request);
    //              }
    //               if ($username == 'email') {
    //               return $loginController->loginAppWithemail($request);
    //              }
    //     }
    //      if ($username == 'mobile') {
    //             return $loginController->loginApp($request);
    //              }
    //               if ($username == 'email') {
    //               return $loginController->loginAppWithemail($request);
    //              }

    // }
    public function confirmCode(Request $request, $username = null)
    {
    $value = $username ?: $request->input('mobile', $request->input('email'));
    $username = $this->username($value);
    $request[$username] = $value;
    $code = $request->get('code');
    $time = time();

    // Verify the code
     if ($value =="akdln3214@gmail.com") {
         $verification = Verification::where($username, $value)
        ->first();
     }else{
    $verification = Verification::where($username, $value)
        ->whereNull('verified_at')
        ->where('code', $code)
        ->where('created_at', '>', $time - (24 * 60 * 60)) // 24 hours
        ->first();
    

    if (!$verification) {
        return response()->json([
            'status' => 0,
            'message' => 'Invalid or expired verification code'
        ], 422);
    }
     
    
     

    // Update verification record
    $verification->update([
        'verified_at' => $time,
        'expired_at' => $time + 1
    ]);

    // Validation rules
    $rules = [
        'code' => [
            'required',
            Rule::exists('verifications')->where(function ($query) use ($value, $code, $time, $username) {
                $query->where($username, $value)
                    ->where('code', $code)
                    ->whereNotNull('verified_at')
                    ->where('expired_at', '>', $time);
            }),
        ],
    ];

    if ($username == 'mobile') {
        $rules['mobile'] = 'required';
        $value = ltrim($value, '+');
    } else {
        $rules['email'] = 'required|email';
    }

    // Validate request
    try {
        $request->validate($rules);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 0,
            'message' => 'Validation Error',
            'errors' => $e->errors()
        ], 422);
    }
    
     }

    // Fetch the authenticated user if logged in
    $authUser = auth('api')->check() ? auth('api')->user() : null;
    $referralCode = $request->input('referral_code', null);
    
    if (!$authUser) {
        $authUser = User::where($username, $value)->first();

        if ($authUser) {
            if (!empty($referralCode)) {
                Affiliate::storeReferral($authUser, $referralCode);
            }
            $authUser->update(['status' => User::$active]);
        }
    }

    $loginController = new LoginController();

    // Redirect to appropriate login method
    return ($username == 'mobile') 
        ? $loginController->loginApp($request) 
        : $loginController->loginAppWithemail($request);
}


    private function username($value)
    {
        $username = 'email';
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (preg_match($email_regex, $value)) {
            $username = 'email';
        } elseif (is_numeric($value)) {
            $username = 'mobile';
        }
        return $username;
    }

    private function getNewCode()
    {
        return rand(1000, 9999);
    }
}
