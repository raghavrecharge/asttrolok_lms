<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Agora\RtcTokenBuilder;
use App\Agora\RtmTokenBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgoraController extends Controller
{
    public $appId;
    private $appCertificate;

    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID');
        $this->appCertificate = env('AGORA_APP_CERTIFICATE');
    }

    public function getRTCToken(string $channelName, bool $isHost): string
    {
        try {
            $role = $isHost ? RtcTokenBuilder::RolePublisher : RtcTokenBuilder::RoleAttendee;

            $expireTimeInSeconds = 3600;
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            return RtcTokenBuilder::buildTokenWithUserAccount($this->appId, $this->appCertificate, $channelName, null, $role, $privilegeExpiredTs);
        } catch (\Exception $e) {
            \Log::error('getRTCToken error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getRTMToken($channelName): string
    {
        try {
            $expireTimeInSeconds = 3600;
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            return RtmTokenBuilder::buildToken($this->appId, $this->appCertificate, $channelName, null, $privilegeExpiredTs);
        } catch (\Exception $e) {
            \Log::error('getRTMToken error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
