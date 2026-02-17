<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Api\User;

class ForgotPasswordController extends Controller
{
    private function detectType($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        return 'mobile';
    }

    public function sendEmail(Request $request)
    {
        try {

            // frontend same rahe
            $request->validate([
                'email' => 'required|string',
            ]);

            $input = $request->input('email');
            $type  = $this->detectType($input);

            // user find
            if ($type === 'email') {
                $user = User::where('email', $input)->first();
            } else {
                $mobile = preg_replace('/\D/', '', $input);
                $user = User::whereRaw("RIGHT(mobile, 10) = ?", [$mobile])->first();
            }

            if (!$user) {
                return response()->json([
                    'status' => 0,
                    'success' => false,
                    'message' => 'User not found.',
                ], 200);
            }

            // EXACT LOGIN JAISE OTP FLOW
            $verificationController = new VerificationController();
            $verificationController->checkConfirmed($user, $type, $input);
            
            $message = ($type === 'email')
                ? 'OTP sent successfully to your email.'
                : 'OTP sent successfully to your mobile number.';

            // RESPONSE SAME AS OLD EMAIL
            return response()->json([
                'status' => 1,
                'success' => true,
                'message' => $message,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Forgot password OTP error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }
    }
}

//     public function sendEmail1(Request $request)
// {
//   try {
//         $validatedData = $request->validate([
//             'email' => 'required|email|exists:users,email',
//         ]);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json([
//             'status' => 0,
//             'message' => 'Validation Error',
//             'errors' => $e->errors()
//         ], 422);
//     }

//     $token = \Illuminate\Support\Str::random(60);
//     DB::table('password_resets')->insert([
//         'email' => $request->input('email'),
//         'token' => $token,
//         'created_at' => Carbon::now()
//     ]);

//     $generalSettings = getGeneralSettings();
//     $emailData = [
//         'token' => $token,
//         'generalSettings' => $generalSettings,
//         'email' => $request->input('email')
//     ];

//     \Log::info('Preparing to send password reset email to: ' . $request->input('email'));

//     try {

//          $verificationController = new VerificationController();
//             $checkConfirmed = $verificationController->checkConfirmed($user, 'email', $request->input('email'));
//  \Log::info('Password reset email sent successfully to: ' . $request->input('email'));
//             if ($checkConfirmed['status'] == 'send') {
//                  return response()->json([
//             'status' => 1,
//             'message' => trans('auth.forget_password'),
//         ], 200);

//             }

//     } catch (\Exception $e) {
//         \Log::error('Email send error: ' . $e->getMessage());
//         return apiResponse2(0, 'failure', trans('auth.forget_password_failure'),[$e->getMessage()]);
//     }
// }

// }
