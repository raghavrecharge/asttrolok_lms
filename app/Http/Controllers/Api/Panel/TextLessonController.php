<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Http\Resources\TextLessonResource;
use App\Models\Api\TextLesson;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;

class TextLessonController extends Controller
{
    public function show($id)
    {
        try {
            $textLesson = TextLesson::where('id', $id)
                ->where('status', WebinarChapter::$chapterActive)->first();
            abort_unless($textLesson, 404);

            if ($error = $textLesson->canViewError()) {

            }
            $resource = new TextLessonResource($textLesson);
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
