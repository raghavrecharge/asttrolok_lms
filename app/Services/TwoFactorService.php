<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TwoFactorService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('TWOFACTOR_API_KEY');
        $this->baseUrl = 'https://2factor.in/API/V1';
    }

    /**
     * Send OTP using approved template "sendotp"
     */
    public function sendOTP($phone)
    {
        try {
            $phone = $this->formatPhone($phone);

            $templateName = 'sendotp'; 
            
            $url = "{$this->baseUrl}/{$this->apiKey}/SMS/{$phone}/AUTOGEN/{$templateName}";

            Log::info('Sending OTP with approved template', [
                'phone' => $phone,
                'template' => $templateName,
                'url' => $url
            ]);

            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                throw new Exception('API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            Log::info('2Factor Response', $data);

            if (isset($data['Status']) && $data['Status'] == 'Success') {
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'session_id' => $data['Details'], 
                    'template' => $templateName
                ];
            }

            return [
                'success' => false,
                'message' => $data['Details'] ?? 'Failed to send OTP',
                'response' => $data
            ];

        } catch (Exception $e) {
            Log::error('OTP Send Error', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP using 2Factor API
     */
    public function verifyOTP($sessionId, $otp)
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiKey}/SMS/VERIFY/{$sessionId}/{$otp}";

            Log::info('Verifying OTP', [
                'session_id' => $sessionId,
                'url' => $url
            ]);

            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new Exception('Verification request failed');
            }

            $data = $response->json();

            Log::info('Verification Response', $data);

            if (isset($data['Status']) && $data['Status'] == 'Success' && $data['Details'] == 'OTP Matched') {
                return [
                    'success' => true,
                    'message' => 'OTP verified successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $data['Details'] ?? 'Invalid OTP'
            ];

        } catch (Exception $e) {
            Log::error('OTP Verification Error', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to 10 digits
     */
    private function formatPhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove country code if present
        if (strlen($phone) == 12 && substr($phone, 0, 2) == '91') {
            $phone = substr($phone, 2);
        } elseif (strlen($phone) == 13 && substr($phone, 0, 3) == '+91') {
            $phone = substr($phone, 3);
        }

        if (strlen($phone) != 10) {
            throw new Exception('Invalid phone number. Must be 10 digits.');
        }

        return $phone;
    }
}