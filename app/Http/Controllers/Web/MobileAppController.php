<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobileAppController extends Controller
{
    public function index()
    {
        try {
            $data = [
                'pageTitle' => trans('update.download_mobile_app_and_enjoy'),
                'pageRobot' => getPageRobotNoIndex()
            ];

            return view('web.default.mobile_app.index', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
