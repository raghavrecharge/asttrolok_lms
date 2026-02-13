<?php

namespace App\Http\Controllers\Api\Traits;

use Illuminate\Support\Facades\Log;
use Exception;

trait CheckContentLimitationTrait
{
    public function checkContentLimitation($user = null, $coursePage = false)
    {
        try {
            if (!empty($user) and !$user->access_content) {
                $data = [
                    'pageTitle' => trans('update.not_access_to_content'),
                    'pageRobot' => getPageRobotNoIndex(),
                    'userNotAccess' => true
                ];

                return view('web.default.course.private_content', $data);
            } elseif (empty($user) and getFeaturesSettings('webinar_private_content_status') and $coursePage) {
            $gg=getFeaturesSettings('webinar_private_content_status');
                $data = [
                    'pageTitle' => trans('update.private_content'),
                    'pageRobot' => getPageRobotNoIndex(),
                ];
                return view('web.default.course.private_content', $data);
            }

            return "ok";
        } catch (\Exception $e) {
            \Log::error('checkContentLimitation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}