<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarResource;
use App\Models\Api\Webinar;
use Illuminate\Http\Request;

class WebinarStatisticController extends Controller
{
    public function index(Request $request, $webinarId)
    {
        try {
            $user = apiAuth();

            $webinar = Webinar::where('id', $webinarId)
                ->where(function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    });

                    $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    });
                })
                ->with([
                    'chapters' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'sessions' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'assignments' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'files' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                ])
                ->first();
            if (!$webinar) {
                abort(404);
            }
            $resource = new WebinarResource($webinar);
            $resource->statistic = true;

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'webinar' => $resource,

                ]);
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
