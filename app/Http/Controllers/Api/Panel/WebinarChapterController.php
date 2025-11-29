<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarChapterResource;
use App\Models\Api\Webinar;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;

class WebinarChapterController extends Controller
{
    public function index($webinar_id)
    {
        try {
            $chapters = WebinarChapter::where('webinar_id', $webinar_id)
                ->where('status', WebinarChapter::$chapterActive)
                ->orderBy('order', 'asc')
                ->with([
                    'chapterItems' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }
                ])
                ->get();
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), WebinarChapterResource::collection($chapters));
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
