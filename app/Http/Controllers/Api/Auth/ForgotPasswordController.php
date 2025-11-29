<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\Verification;
class ForgotPasswordController extends Controller
{

  public function sendEmail(Request $request)
{
    try {

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->input('email');
        $time = time();
        $otp = rand(1000, 9999);

        $verification = Verification::where('email', $email)
            ->whereNull('verified_at')
            ->where('expired_at', '>', $time)
            ->first();

        if ($verification) {
            return response()->json([
                'status' => 0,
                'success' => true,
                'message' => 'OTP already sent. Please check your email.',
            ], 400);
        }

        $data = [
            "email" => $email,
            'code' => $otp,
            'user_id' => null,
            'created_at' => $time,
            'expired_at' => $time + (2 * 60),
            'verified_at' => null,
        ];

        $verification = Verification::updateOrCreate(['email' => $email], $data);

        try {
            $verification->sendEmailCode();
        } catch (\Exception $e) {
            \Log::error('Email send error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 1,
            'success' => true,
            'message' => 'OTP sent successfully to your email.',
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 0,
            'success' => false,
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    }
}

    public function sendEmail1(Request $request)
{
   try {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 0,
            'message' => 'Validation Error',
            'errors' => $e->errors()
        ], 422);
    }

    $token = \Illuminate\Support\Str::random(60);
    DB::table('password_resets')->insert([
        'email' => $request->input('email'),
        'token' => $token,
        'created_at' => Carbon::now()
    ]);

    $generalSettings = getGeneralSettings();
    $emailData = [
        'token' => $token,
        'generalSettings' => $generalSettings,
        'email' => $request->input('email')
    ];

    \Log::info('Preparing to send password reset email to: ' . $request->input('email'));

    try {

         $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($user, 'email', $request->input('email'));
 \Log::info('Password reset email sent successfully to: ' . $request->input('email'));
            if ($checkConfirmed['status'] == 'send') {
                 return response()->json([
            'status' => 1,
            'message' => trans('auth.forget_password'),
        ], 200);

            }

    } catch (\Exception $e) {
        \Log::error('Email send error: ' . $e->getMessage());
        return apiResponse2(0, 'failure', trans('auth.forget_password_failure'),[$e->getMessage()]);
    }
}

}
