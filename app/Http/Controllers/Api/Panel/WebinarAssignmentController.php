<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Http\Resources\WebinarAssignmentResource;
use App\Models\Api\File;
use App\Models\Api\WebinarAssignment;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;

class WebinarAssignmentController extends Controller
{
    public function show($id)
    {
        try {
            $assignmnet = WebinarAssignment::where('id', $id)
                ->where('status', WebinarChapter::$chapterActive)->first();
            abort_unless($assignmnet,404);
            if ($error = $assignmnet->canViewError()) {

            }
            $resource = new WebinarAssignmentResource($assignmnet);
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
