<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\TextLessonResource;
use App\Models\Api\TextLesson;
use App\Models\Api\Webinar;
use Illuminate\Http\Request;

class WebinarTextLessonController extends Controller
{
    public function index($id)
    {
        try {
            $text_lesson = TextLesson::find($id);
            abort_unless($text_lesson, 404);
            $webinar = $text_lesson->webinar;
            $all_text_lessons = $webinar->textLessons->map(function ($item, $key)  {
                $item->index = $key + 1;
                return $item;
            });

            $resource = TextLessonResource::collection($all_text_lessons);

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);
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
