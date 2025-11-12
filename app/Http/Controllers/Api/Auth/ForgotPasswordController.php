<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\Verification;
class ForgotPasswordController extends Controller
{

    // public function sendEmail(Request $request)
    // {
    //     validateParam($request->all(), [
    //         'email' => 'required|email|exists:users',
    //     ]);

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
    //     try {
    //         Mail::send('web.default.auth.password_verify', $emailData, function ($message) use ($request) {
    //             $message->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'));
    //             $message->to($request->input('email'));
    //             $message->subject('Reset Password Notification');
    //         });


    //         return apiResponse2(1, 'done',trans('auth.forget_password'));

    //     } catch (\Exception  $e) {
    //         return $e;
    //          return apiResponse2(0, 'failure',trans('auth.forget_password_failure'));

    //     }
    // }
    
  public function sendEmail(Request $request)
{
    try {
        // Validate request
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->input('email');
        $time = time();
        $otp = rand(1000, 9999); // Generate 4-digit OTP

        // Check if an OTP already exists and is within the 5-minute limit
        $verification = Verification::where('email', $email)
            ->whereNull('verified_at') // Ensure OTP is not used
            ->where('expired_at', '>', $time) // Ensure OTP is still valid
            ->first();

        if ($verification) {
            return response()->json([
                'status' => 0,
                'success' => true,
                'message' => 'OTP already sent. Please check your email.',
            ], 400);
        }

        // Create new OTP record
        $data = [
            "email" => $email,
            'code' => $otp,
            'user_id' => null,
            'created_at' => $time,
            'expired_at' => $time + (2 * 60), // 5 minutes expiry time
            'verified_at' => null,
        ];

        $verification = Verification::updateOrCreate(['email' => $email], $data);

        // Send Email with OTP
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


// DB::table('password_reset_logs')->insert([
//     'email' => $data['email'],
//     'reset_at' => now(),
//     'ip_address' => request()->ip(),  
//     'user_agent' => request()->userAgent(),  // ब्राउज़र/डिवाइस info
//     'status' => 'success',  // या 'failed' अगर कोई validation fail हो
// ]);
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
        
       
        // return apiResponse2(1, 'done', trans('auth.forget_password'),$emailData1);
            }
    //     Mail::send('web.default.auth.password_verify', $emailData, function ($message) use ($request, $generalSettings) {
    //         $message->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'));
    //         $message->to($request->input('email'));
    //         $message->subject('Reset Password Notification');
    //     });
    //      $emailData1 = [
    //     'token' => $token,
    //     'email' => $request->input('email')
    // ];
        

    } catch (\Exception $e) {
        \Log::error('Email send error: ' . $e->getMessage());
        return apiResponse2(0, 'failure', trans('auth.forget_password_failure'),[$e->getMessage()]);
    }
}

}
