<?php

namespace App\Http\Controllers\Api\Traits;

trait CheckContentLimitationTrait
{
    public function checkContentLimitation($user = null, $coursePage = false)
    {
        if (!empty($user) and !$user->access_content) {
            $data = [
                'pageTitle' => trans('update.not_access_to_content'),
                'pageRobot' => getPageRobotNoIndex(),
                'userNotAccess' => true
            ];

            return view('web.default.course.private_content', $data);
        } elseif (empty($user) and getFeaturesSettings('webinar_private_content_status') and $coursePage) { // user not login
        $gg=getFeaturesSettings('webinar_private_content_status');
            $data = [
                'pageTitle' => trans('update.private_content'),
                'pageRobot' => getPageRobotNoIndex(),
            ];
            return view('web.default.course.private_content', $data);
        }


        return "ok";
    }
}