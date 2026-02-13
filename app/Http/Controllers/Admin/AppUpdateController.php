<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\AppUpdateSetting;
use Illuminate\Http\Request;

class AppUpdateController extends Controller
{
    public function index()
    {
        try {
            $pageTitle = 'App Update Settings';
            $updateSettings = AppUpdateSetting::first();

            return view('admin.app_update.index', compact('pageTitle', 'updateSettings'));
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'latest_version_android' => 'required|string',
                'latest_version_ios' => 'required|string',
                'force_update_android' => 'nullable|boolean',
                'force_update_ios' => 'nullable|boolean',
                'optional_update' => 'nullable|boolean',
                'force_update_message' => 'nullable|string',
                'optional_update_message' => 'nullable|string',
                'delay_seconds' => 'required|integer|min:0|max:60',
                'playstore_url' => 'nullable|url',
                'appstore_url' => 'nullable|url',
            ]);

            $updateSettings = AppUpdateSetting::first();

            $updateSettings->update([
                'latest_version_android' => $request->latest_version_android,
                'latest_version_ios' => $request->latest_version_ios,
                'force_update_android' => $request->has('force_update_android') ? 1 : 0,
                'force_update_ios' => $request->has('force_update_ios') ? 1 : 0,
                'optional_update' => $request->has('optional_update') ? 1 : 0,
                'force_update_message' => $request->force_update_message,
                'optional_update_message' => $request->optional_update_message,
                'delay_seconds' => $request->delay_seconds,
                'playstore_url' => $request->playstore_url,
                'appstore_url' => $request->appstore_url,
            ]);

            return back()->with('success', 'App update settings updated successfully!');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}