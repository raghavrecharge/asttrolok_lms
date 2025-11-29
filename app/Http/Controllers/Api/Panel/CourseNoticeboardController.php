<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseNoticeboardResource;
use App\Models\Api\Webinar;
use App\Models\CourseNoticeboard;
use Illuminate\Http\Request;

class CourseNoticeboardController extends Controller
{
    public function index($webinar_id)
    {
        try {
            $webinar = Webinar::find($webinar_id);
            abort_unless($webinar, 404);
            $user = apiAuth();

            if ($webinar->creator_id != $user->id and $webinar->teacher_id != $user->id and !$user->isAdmin()) {
                $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $webinar->id)
                    ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();

                if ($unReadCourseNoticeboards) {
                    $url = $webinar->getNoticeboardsPageUrl();

                }
            }
            $noticeboards = $webinar
                ->noticeboards;

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), CourseNoticeboardResource::collection($noticeboards));
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
