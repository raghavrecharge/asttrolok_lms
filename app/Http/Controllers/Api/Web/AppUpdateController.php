<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppUpdateSetting;

class AppUpdateController extends Controller
{
    public function check(Request $request)
    {
        $settings = AppUpdateSetting::first();
        try {
           return response()->json([
            'success' => true,
            'message' => 'App update settings fetched successfully',
            'data' => [
                'latest_version_android' => $settings->latest_version_android,
                'latest_version_ios' => $settings->latest_version_ios,
                'force_update_android' => $settings->force_update_android,
                'force_update_ios' => $settings->force_update_ios,
                'optional_update' => $settings->optional_update,
                'force_update_message' => $settings->force_update_message,
                'optional_update_message' => $settings->optional_update_message,
                'delay_seconds' => $settings->delay_seconds,
                'playstore_url' => $settings->playstore_url,
                'appstore_url' => $settings->appstore_url,
            ]
        ]);
        } catch (\Throwable $th) {
           return response()->json([
            'success' => false,
            'message' => 'Failed to fetch app update settings',
            'data' => []
        ]);
    }
}

}