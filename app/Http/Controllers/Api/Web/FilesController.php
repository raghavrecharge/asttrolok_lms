<?php

namespace App\Http\Controllers\Api\Web ;

use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Controllers\Api\Controller ;
use App\Models\Api\Webinar ;
use App\Models\Api\File ;
use App\Models\Api\WebinarChapter ;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\WebinarChapterItem;

class FilesController extends Controller{

    public function download($file_id)
    {
        try {
            $file=File::find($file_id) ;

            if(!$file){
                abort(404) ;
            }
            $webinar=$file->webinar()->where('private', false)
            ->where('status', 'active')->first() ;

            if(!$webinar){
                abort(404) ;
             }

            if(!$file->downloadable){
                return apiResponse2(1, 'not_downloadable', trans('api.file.not_downloadable'));
            }

            $canAccess = true;

            if ($file->accessibility == 'paid') {
                $canAccess = $webinar->checkUserHasBought(apiAuth());
            }

            if(!$canAccess){
                return apiResponse2(1, 'not_accessible', trans('api.file.not_accessible'));

            }

            $filePath = public_path($file->file);

            $fileName = str_replace(' ', '-', $file->title);
            $fileName = str_replace('.', '-', $fileName);
            $fileName .= '.' . $file->file_type;

            $headers = array(
                'Content-Type: application/' . $file->file_type,
            );

            return response()->download($filePath, $fileName, $headers);
        } catch (\Exception $e) {
            \Log::error('download error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 private function inferFileTypeFromInput(?string $filePath, ?string $storage = null): string
    {
        if (empty($filePath) && empty($storage)) {
            return 'unknown';
        }

        // prefer explicit storage flags
        if ($storage === 'iframe') return 'iframe';
        if ($storage === 'youtube') return 'youtube';
        if ($storage === 'vimeo') return 'vimeo';

        // check if iframe html string
        if ($filePath && stripos($filePath, '<iframe') !== false) {
            return 'iframe';
        }

        // youtube/vimeo urls
        if ($filePath && (stripos($filePath, 'youtube.com') !== false || stripos($filePath, 'youtu.be') !== false)) {
            return 'youtube';
        }
        if ($filePath && stripos($filePath, 'vimeo.com') !== false) {
            return 'vimeo';
        }

        // try extension from url/path
        if ($filePath) {
            $path = parse_url($filePath, PHP_URL_PATH) ?: $filePath;
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext) {
                $ext = strtolower($ext);
                $videoExt = ['mp4','mov','webm','mkv','avi'];
                $audioExt = ['mp3','wav','m4a','aac'];
                $docExt = ['pdf','doc','docx','ppt','pptx','xls','xlsx'];

                if (in_array($ext, $videoExt)) return 'video';
                if (in_array($ext, $audioExt)) return 'audio';
                if (in_array($ext, $docExt)) return $ext;
                return $ext;
            }
        }

        return 'unknown';
    }

 // inside FilesController

 public function createChapter(Request $request)
    {
        $data = $request->all();

        $rules = [
            'user_id' => 'nullable|integer',
            'webinar_id' => 'required|integer|exists:webinars,id',
            'order' => 'nullable|integer|min:0',
            'check_all_contents_pass' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,on,off',
            'locale' => 'nullable|string|max:10',
            'title' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // prefer authenticated user
        $userId = optional($request->user())->id ?? ($data['user_id'] ?? null);

        if (empty($userId)) {
            return response()->json(['status' => false, 'message' => 'user_id is required or provide auth token.'], 422);
        }

        $status = (!empty($data['status']) && ($data['status'] === 'on' || $data['status'] === 'active')) ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;
        $checkAll = filter_var($data['check_all_contents_pass'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
$now = time();

$chapter = WebinarChapter::create([
    'user_id' => $userId,
    'webinar_id' => $data['webinar_id'],
    'order' => isset($data['order']) ? (int)$data['order'] : null,
    'check_all_contents_pass' => $checkAll,
    'status' => $status,
    'created_at' => $now,
    'updated_at' => $now,
]);

        if (!empty($data['title'])) {
            $locale = $data['locale'] ?? 'en';
            // if using dimsav/laravel-translatable
            $chapter->translateOrNew($locale)->title = $data['title'];
            $chapter->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Chapter created successfully.',
            'chapter_id' => $chapter->id,
            'data' => $chapter
        ], 201);
    }

public function storeFile(Request $request, $chapterId = null)
{
    $data = $request->all();
    if ($chapterId) $data['chapter_id'] = $chapterId;

    $rules = [
        'chapter_id' => 'required|integer|exists:webinar_chapters,id',
        'webinar_id' => 'nullable|integer|exists:webinars,id',
        'creator_id' => 'nullable|integer',
        'remedy_id' => 'nullable|integer',
        'locale' => 'nullable|string|max:10',
        'title' => 'nullable|string|max:255',
        'storage' => 'nullable|in:upload,youtube,vimeo,external_link,iframe',
        'accessibility' => 'required|in:free,paid',
        'file' => 'nullable|string',
        'file_path' => 'nullable|string',
        'file_type' => 'nullable|string|max:64',
        'volume' => 'nullable|string|max:64',
        'description' => 'nullable|string',
        'status' => 'nullable|in:active,inactive,on,off',
        'check_previous_parts' => 'nullable',
        'access_after_day' => 'nullable|integer|min:0',
        'downloadable' => 'nullable|boolean',
        'online_viewer' => 'nullable|boolean',
        'interactive_type' => 'nullable|in:adobe_captivate,i_spring,custom',
        'interactive_file_name' => 'nullable|string|max:255',
        'interactive_file_path' => 'nullable|string|max:255',
        'order' => 'nullable|integer',
    ];

    $validator = Validator::make($data, $rules);
    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }

    $chapter = WebinarChapter::find($data['chapter_id']);
    if (!$chapter) {
        return response()->json(['status' => false, 'message' => 'Chapter not found'], 404);
    }

    // source values and safe defaults
    $filePath = $data['file'] ?? $data['file_path'] ?? '';
    $storage = $data['storage'] ?? 'external_link'; // fallback because DB NOT NULL
    $accessibility = $data['accessibility'] ?? 'paid'; // required non-null
    $volume = $data['volume'] ?? '0'; // DB NOT NULL
    $status = (!empty($data['status']) && ($data['status'] === 'on' || $data['status'] === 'active')) ? 'active' : 'inactive';
    $checkPrev = filter_var($data['check_previous_parts'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

    // infer file_type (handles iframe/html embeds). fallback 'unknown'
    $fileType = $data['file_type'] ?? $this->inferFileTypeFromInput($filePath, $storage);
    if (empty($fileType)) $fileType = 'unknown';

    // determine creator id: prefer auth, else request, else chapter owner, else admin fallback (1)
    $creatorId = optional($request->user())->id ?? ($data['creator_id'] ?? null);
    if (empty($creatorId) && !empty($chapter->user_id)) {
        $creatorId = $chapter->user_id;
    }
    if (empty($creatorId)) {
        $creatorId = 1; // change to appropriate system/admin id if needed
    }

    // integer unix timestamp for DB columns
    $now = time();

    $file = File::create([
        'creator_id' => $creatorId,
        'webinar_id' => $data['webinar_id'] ?? $chapter->webinar_id,
        'remedy_id' => $data['remedy_id'] ?? null,
        'chapter_id' => $chapter->id,
        'locale' => $data['locale'] ?? null,
        'title' => null, // saved via translations below if provided
        'storage' => $storage,
        'accessibility' => $accessibility,
        'file' => $filePath,
        'file_type' => $fileType,
        'volume' => $volume,
        'description' => $data['description'] ?? null,
        'status' => $status,
        'check_previous_parts' => $checkPrev,
        'access_after_day' => isset($data['access_after_day']) ? (int)$data['access_after_day'] : null,
        'downloadable' => !empty($data['downloadable']) ? 1 : 0,
        'online_viewer' => !empty($data['online_viewer']) ? 1 : 0,
        'interactive_type' => $data['interactive_type'] ?? null,
        'interactive_file_name' => $data['interactive_file_name'] ?? null,
        'interactive_file_path' => $data['interactive_file_path'] ?? null,
        'order' => isset($data['order']) ? (int)$data['order'] : 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Save translated title if provided (supports dimsav/astrotomic pattern)
    $respTitle = null;
    if (!empty($data['title'])) {
        $locale = $data['locale'] ?? 'en';
        // assign translation and save
        $file->translateOrNew($locale)->title = $data['title'];
        $file->save();

        // try to read translated title back in a safe way
        if (method_exists($file, 'getTranslation')) {
            $respTitle = $file->getTranslation('title', $locale);
        } else {
            $respTitle = $file->title ?? $data['title'];
        }

        // ensure the model instance has the title so response->data.title is set
        $file->title = $respTitle;
    }

    // --- Add chapter item entry so admin lists show it ---
    try {
        if (!empty($file->chapter_id) && !empty($file->id)) {
            \App\Models\WebinarChapterItem::makeItem($creatorId, $file->chapter_id, $file->id, \App\Models\WebinarChapterItem::$chapterFile);
        }
    } catch (\Exception $e) {
        // log but don't fail the request for this non-critical operation
        Log::error('Failed to create webinar_chapter_item: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    }

    return response()->json([
        'status' => true,
        'message' => 'File created successfully.',
        'file_id' => $file->id,
        'title' => $respTitle,
        'file_type' => $file->file_type,
        'data' => $file
    ], 201);
}

    public function storeChapterAndFile(Request $request)
    {
        $data = $request->all();

        $rules = [
            'chapter.webinar_id' => 'required|integer|exists:webinars,id',
            'chapter.user_id' => 'nullable|integer',
            'chapter.order' => 'nullable|integer|min:0',
            'chapter.check_all_contents_pass' => 'nullable|boolean',
            'chapter.status' => 'nullable|in:active,inactive,on,off',
            'chapter.locale' => 'nullable|string|max:10',
            'chapter.title' => 'nullable|string|max:255',

            'file.file_path' => 'nullable|string',
            'file.file' => 'nullable|string',
            'file.file_type' => 'nullable|string|max:64',
            'file.volume' => 'nullable|string|max:64',
            'file.accessibility' => 'nullable|in:free,paid',
            'file.status' => 'nullable|in:active,inactive,on,off',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $chapterInput = $data['chapter'] ?? [];
        $fileInput = $data['file'] ?? [];

        DB::beginTransaction();
        try {
            $chapterStatus = (!empty($chapterInput['status']) && ($chapterInput['status'] === 'on' || $chapterInput['status'] === 'active')) ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;
            $chapterCheck = filter_var($chapterInput['check_all_contents_pass'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            $userId = optional($request->user())->id ?? ($chapterInput['user_id'] ?? null);
            if (empty($userId)) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'user_id is required for chapter creation. Provide auth token or user_id.'], 422);
            }

            $chapter = WebinarChapter::create([
                'user_id' => $userId,
                'webinar_id' => $chapterInput['webinar_id'],
                'order' => isset($chapterInput['order']) ? (int)$chapterInput['order'] : null,
                'check_all_contents_pass' => $chapterCheck,
                'status' => $chapterStatus,
            ]);

            if (!empty($chapterInput['title'])) {
                $locale = $chapterInput['locale'] ?? 'en';
                $chapter->translateOrNew($locale)->title = $chapterInput['title'];
                $chapter->save();
            }

            $fp = $fileInput['file_path'] ?? $fileInput['file'] ?? null;
            $fileStatus = (!empty($fileInput['status']) && ($fileInput['status'] === 'on' || $fileInput['status'] === 'active')) ? 'active' : 'inactive';

            // infer file_type for the new file
            $fileType = $fileInput['file_type'] ?? $this->inferFileTypeFromInput($fp, $fileInput['storage'] ?? null);

            $file = File::create([
                'creator_id' => optional($request->user())->id ?? ($fileInput['creator_id'] ?? $userId),
                'webinar_id' => $chapter->webinar_id,
                'remedy_id' => $fileInput['remedy_id'] ?? null,
                'chapter_id' => $chapter->id,
                'locale' => $fileInput['locale'] ?? null,
                'title' => $fileInput['title'] ?? null,
                'storage' => $fileInput['storage'] ?? null,
                'accessibility' => $fileInput['accessibility'] ?? 'paid',
                'file' => $fp,
                'file_type' => $fileType,
                'volume' => $fileInput['volume'] ?? null,
                'description' => $fileInput['description'] ?? null,
                'status' => $fileStatus,
                'check_previous_parts' => !empty($fileInput['check_previous_parts']) ? 1 : 0,
                'access_after_day' => isset($fileInput['access_after_day']) ? (int)$fileInput['access_after_day'] : null,
                'downloadable' => !empty($fileInput['downloadable']) ? 1 : 0,
                'online_viewer' => !empty($fileInput['online_viewer']) ? 1 : 0,
                'interactive_type' => $fileInput['interactive_type'] ?? null,
                'interactive_file_name' => $fileInput['interactive_file_name'] ?? null,
                'interactive_file_path' => $fileInput['interactive_file_path'] ?? null,
                'order' => isset($fileInput['order']) ? (int)$fileInput['order'] : 0,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Chapter and file created successfully.',
                'chapter_id' => $chapter->id,
                'file_id' => $file->id,
                'file_title' => $file->title,
                'file_type' => $file->file_type,
                'chapter' => $chapter,
                'file' => $file
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('chapter+file create error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}