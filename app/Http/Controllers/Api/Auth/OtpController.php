<?php
use App\Http\Controllers\Controller;

use App\Helpers\SmsHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $otp = rand(100000, 999999);
        $mobile = $request->mobile;

        Cache::put('otp_'.$mobile, $otp, now()->addMinutes(5));

        $response = SmsHelper::sendOtp($mobile, $otp);
        return response()->json($response);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        $cachedOtp = Cache::get('otp_'.$request->mobile);

        if (!$cachedOtp) {
            return response()->json(['status' => false, 'message' => 'OTP expired or invalid']);
        }

        if ($cachedOtp == $request->otp) {
            Cache::forget('otp_'.$request->mobile);
            return response()->json(['status' => true, 'message' => 'OTP verified successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Incorrect OTP']);
    }
}
