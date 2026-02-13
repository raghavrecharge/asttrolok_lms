<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsHelper
{
    public static function sendOtp($mobile, $otp)
    {
        try {
            $config = config('msg91');

            $payload = [
                'template_id' => $config['template_id'],
                'sender' => $config['sender_id'],
                'mobiles' => $config['country'] . $mobile,
                'otp' => $otp,
            ];

            $response = Http::withHeaders([
                'authkey' => $config['auth_key'],
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($config['api_url'], $payload);

            if ($response->successful()) {
                Log::info("OTP sent successfully to {$mobile}", $response->json());
                return [
                    'status' => true,
                    'message' => 'OTP sent successfully',
                    'data' => $response->json(),
                ];
            }

            Log::error("MSG91 OTP API Error for {$mobile}", [
                'response' => $response->json(),
                'payload' => $payload,
            ]);

            return [
                'status' => false,
                'message' => 'Failed to send OTP',
                'data' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error("Exception while sending OTP", [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => 'Something went wrong while sending OTP',
                'error' => $e->getMessage(),
            ];
        }
    }
}
