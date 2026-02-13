<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class Fast2smsService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.fast2sms.api_key');
        $this->apiUrl = config('services.fast2sms.url', 'https://www.fast2sms.com/dev/bulkV2');
    }

    /**
     * Send OTP to mobile number
     * 
     * @param string $mobile
     * @param string|null $code (optional - if you want to use verification code from your system)
     * @return array
     */
    public function sendOTP(string $mobile, ?string $code = null): array
    {
        try {
            // If code is not provided, generate one
            $otp = $code ?? rand(1000, 9999);
            $minuts =5;
            
            $message = "Your OTP for login at Asttrolok is $otp. Do not share it with anyone. Valid for $minuts  minutes.";

            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->apiUrl, [
                'route' => 'q',
                'message' => $message,
                'numbers' => $mobile
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'session_id' => $responseData['request_id'] ?? uniqid('otp_'),
                    'data' => $responseData
                ];
            }

            Log::error('Fast2SMS API Error', [
                'mobile' => $mobile,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP',
                'session_id' => null,
                'error' => $response->body()
            ];

        } catch (Exception $e) {
            Log::error('Fast2SMS Exception', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error sending OTP: ' . $e->getMessage(),
                'session_id' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send custom message
     * 
     * @param string $mobile
     * @param string $message
     * @return array
     */
    public function sendMessage(string $mobile, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->apiUrl, [
                'route' => 'q',
                'message' => $message,
                'numbers' => $mobile
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $response->body()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}