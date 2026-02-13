<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\FileResource;
use App\Models\Api\File;
use App\Models\WebinarChapter;

class FileController extends Controller
{
    public function show($file_id)
    {
        try {
            $file = File::where('id', $file_id)
                ->where('files.status', WebinarChapter::$chapterActive)->first();
            abort_unless($file, 404);
            if ($error = $file->canViewError()) {

            }
            $resource = new FileResource($file);
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
