<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mixins\BunnyCDN\BunnyVideoStream;
use App\Models\Refile;
use App\Models\Translation\RefileTranslation;
use App\Models\Remedy;
use App\Models\RemedyChapterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Validator;

class RefileController extends Controller
{
    public function store(Request $request)
    {
        
        // print_r("<script>alert('mayank')</script>");
        $this->authorize('admin_remedies_edit');

        $s3FileInput = $request->file('s3_file');
        $data = $request->get('ajax')['new'];
        $data['s3_file'] = $s3FileInput;

        
        if (empty($data['storage'])) {
            $data['storage'] = 'upload';
        }
        
        if (!empty($data['file_path']) and is_array($data['file_path'])) {
            $data['file_path'] = $data['file_path'][0];
        }

        $sourceRequiredFileType = ['external_link', 's3', 'google_drive', 'upload'];
        $sourceRequiredFileVolume = ['external_link', 'google_drive'];
        $sourceDefaultFileTypeAndVolume = ['youtube', 'vimeo', 'iframe', 'secure_host'];

        if (in_array($data['storage'], $sourceDefaultFileTypeAndVolume)) {
            $data['file_type'] = 'video';
            $data['volume'] = 0;
        }
        
        $rules = [
            'remedy_id' => 'required',
            'chapter_id' => 'required',
            'title' => 'required|max:255',
            'accessibility' => 'required|' . Rule::in(Refile::$accessibility),
            'file_path' => 'required',
            'file_type' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileType)),
            'volume' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileVolume)),
            'description' => 'nullable',
        ];
        if ($data['storage'] == 'upload_archive') {
            $rules['interactive_type'] = 'required';
            $rules['interactive_file_name'] = Rule::requiredIf($data['interactive_type'] == 'custom');
        }

        if (in_array($data['storage'], ['s3', 'secure_host'])) {
            $rules ['file_path'] = 'nullable';
            $rules ['s3_file'] = 'required';
        }

        
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }


        $data['downloadable'] = !empty($data['downloadable']);
        if (in_array($data['storage'], ['youtube', 'vimeo', 'iframe', 'google_drive', 'upload_archive'])) {
            $data['downloadable'] = false;
        } elseif (in_array($data['storage'], ['external_link', 's3']) and $data['file_type'] != 'video') {
            $data['downloadable'] = true;
        }

        if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
            $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null;
        } else {
            $data['check_previous_parts'] = false;
            $data['access_after_day'] = null;
        }

        $remedy = Remedy::find($data['remedy_id']);

        if (!empty($remedy)) {
            $user = $remedy->creator;

            $volumeMatches = [''];
            $fileInfos = null;

            if ($data['storage'] == 'upload_archive') {
                $fileInfos = $this->fileInfo($data['file_path']);
                        

                if (empty($fileInfos) or $fileInfos['extension'] != 'zip') {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'file_path' => [trans('validation.mimes', ['attribute' => 'file', 'values' => 'zip'])]
                        ],
                    ], 422);
                }

                $fileInfos['extension'] = 'archive';
                $data['interactive_file_path'] = $this->handleUnZipFile($data, $user->id);

            } elseif ($data['storage'] == 'upload') {
                $uploadFile = $this->fileInfo($data['file_path']);
                $data['volume'] = $uploadFile['size'];
            } elseif (in_array($data['storage'], ['s3', 'secure_host'])) {
                $data['volume'] = $request->file('s3_file')->getSize();;

                if ($data['storage'] == 's3') {
                    $result = $this->uploadFileToS3($data['s3_file'], $user->id);
                } else {
                    $result = $this->uploadFileToBunny($remedy, $data['s3_file']);
                }

                if (!$result['status']) {
                    return $result['path'];
                }

                $data['file_path'] = $result['path'];
                $fileInfos['extension'] = $data['file_type'];
                $fileInfos['size'] = $data['volume'];
            } else {
                preg_match('!\d+!', $data['volume'], $volumeMatches);
            }
            

// $hhjc=[
//                 'creator_id' => $user->id,
//                 'remedy_id' => $data['remedy_id'],
//                 'chapter_id' => $data['chapter_id'],
//                 'file' => $data['file_path'],
//                 'volume' => formatSizeUnits(!empty($fileInfos) ? $fileInfos['size'] : $data['volume']),
//                 'file_type' => !empty($fileInfos) ? $fileInfos['extension'] : $data['file_type'],
//                 'accessibility' => $data['accessibility'],
//                 'storage' => $data['storage'],
//                 'interactive_type' => $data['interactive_type'] ?? null,
//                 'interactive_file_name' => $data['interactive_file_name'] ?? null,
//                 'interactive_file_path' => $data['interactive_file_path'] ?? null,
//                 'downloadable' => $data['downloadable'],
//                 'online_viewer' => (!empty($data['online_viewer']) and $data['online_viewer'] == 'on'),
//                 'check_previous_parts' => $data['check_previous_parts'],
//                 'access_after_day' => $data['access_after_day'],
//                 'status' => (!empty($data['status']) and $data['status'] == 'on') ? Refile::$Active : Refile::$Inactive,
//                 'created_at' => time()
//             ];
            
// return response()->json([
//                 'code' => $hhjc,
//             ], 200);
            $file = Refile::create([
                'creator_id' => $user->id,
                'remedy_id' => $data['remedy_id'],
                'chapter_id' => $data['chapter_id'],
                'file' => $data['file_path'],
                'volume' => formatSizeUnits(!empty($fileInfos) ? $fileInfos['size'] : $data['volume']),
                'file_type' => !empty($fileInfos) ? $fileInfos['extension'] : $data['file_type'],
                'accessibility' => $data['accessibility'],
                'storage' => $data['storage'],
                'interactive_type' => $data['interactive_type'] ?? null,
                'interactive_file_name' => $data['interactive_file_name'] ?? null,
                'interactive_file_path' => $data['interactive_file_path'] ?? null,
                'downloadable' => $data['downloadable'],
                'online_viewer' => (!empty($data['online_viewer']) and $data['online_viewer'] == 'on'),
                'check_previous_parts' => $data['check_previous_parts'],
                'access_after_day' => $data['access_after_day'],
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? Refile::$Active : Refile::$Inactive,
                'created_at' => time()
            ]);

            
            if (!empty($file)) {
                RefileTranslation::updateOrCreate([
                    'refile_id' => $file->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                if (!empty($file->chapter_id)) {
                    RemedyChapterItem::makeItem($file->creator_id, $file->chapter_id, $file->id, RemedyChapterItem::$chapterFile);
                }
            }

            return response()->json([
                'code' => 200,
            ], 200);
        }

        return response()->json([], 422);
    }


    public function edit(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $file = Refile::where('id', $id)->first();

        if (!empty($file)) {
            $locale = $request->get('locale', app()->getLocale());
            if (empty($locale)) {
                $locale = app()->getLocale();
            }
            storeContentLocale($locale, $file->getTable(), $file->id);

            $file->title = $file->getTitleAttribute();
            $file->description = $file->getDescriptionAttribute();
            $file->file_path = $file->file;
            $file->locale = mb_strtoupper($locale);

            return response()->json([
                'file' => $file
            ], 200);
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $s3FileInput = $request->file('s3_file');
        $data = $request->get('ajax')[$id];
        $data['s3_file'] = $s3FileInput;

        $sourceRequiredFileType = ['external_link', 's3', 'google_drive', 'upload'];
        $sourceRequiredFileVolume = ['external_link', 'google_drive'];

        if (empty($data['storage'])) {
            $data['storage'] = 'upload';
        }

        if (!empty($data['file_path']) and is_array($data['file_path'])) {
            $data['file_path'] = $data['file_path'][0];
        }

        $rules = [
            'remedy_id' => 'required',
            'chapter_id' => 'required',
            'title' => 'required|max:255',
            'accessibility' => 'required|' . Rule::in(Refile::$accessibility),
            'file_path' => 'required',
            'file_type' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileType)),
            'volume' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileVolume)),
            'description' => 'nullable',
        ];

        if ($data['storage'] == 'upload_archive') {
            $rules['interactive_type'] = 'required';
            $rules['interactive_file_name'] = Rule::requiredIf($data['interactive_type'] == 'custom');
        }

        if ($data['storage'] == 's3') {
            $rules ['file_path'] = 'nullable';
            $rules ['s3_file'] = 'nullable';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data['downloadable'] = !empty($data['downloadable']);
        if (in_array($data['storage'], ['youtube', 'vimeo', 'iframe', 'google_drive', 'upload_archive'])) {
            $data['downloadable'] = false;
        } elseif (in_array($data['storage'], ['external_link', 's3']) and $data['file_type'] != 'video') {
            $data['downloadable'] = true;
        }

        if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
            $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null;
        } else {
            $data['check_previous_parts'] = false;
            $data['access_after_day'] = null;
        }

        $volumeMatches = ['0'];
        $fileInfos = null;

        $remedy = Remedy::find($data['remedy_id']);
        $file = Refile::where('id', $id)->first();

        if (!empty($remedy) and !empty($file)) {

            if ($data['storage'] == 'upload_archive') {
                $fileInfos = $this->fileInfo($data['file_path']);

                if (empty($fileInfos) or $fileInfos['extension'] != 'zip') {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'file_path' => [trans('validation.mimes', ['attribute' => 'file', 'values' => 'zip'])]
                        ],
                    ], 422);
                }

                $fileInfos['extension'] = 'archive';
                $data['interactive_file_path'] = $this->handleUnZipFile($data, $file->creator_id);

            } elseif ($data['storage'] == 'upload') {
                $uploadFile = $this->fileInfo($data['file_path']);
                $data['volume'] = $uploadFile['size'];
            } elseif ($data['storage'] == 's3') {
                if (!empty($data['s3_file'])) {
                    $data['volume'] = $request->file('s3_file')->getSize();;
                    $result = $this->uploadFileToS3($data['s3_file'], $file->creator_id);

                    if (!$result['status']) {
                        return $result['path'];
                    }

                    $data['file_path'] = $result['path'];
                }

                $fileInfos['extension'] = $data['file_type'];
                $fileInfos['size'] = $data['volume'];
            } elseif (in_array($data['storage'], ['s3', 'secure_host'])) {
                $data['volume'] = $request->file('s3_file')->getSize();;

                if ($data['storage'] == 's3') {
                    $result = $this->uploadFileToS3($data['s3_file'], $file->creator_id);
                } else {
                    $result = $this->uploadFileToBunny($remedy, $data['s3_file']);
                }

                if (!$result['status']) {
                    return $result['path'];
                }

                $data['file_path'] = $result['path'];
                $fileInfos['extension'] = $data['file_type'];
                $fileInfos['size'] = $data['volume'];
            } else {
                preg_match('!\d+!', $data['volume'], $volumeMatches);
            }


            $changeChapter = ($data['chapter_id'] != $file->chapter_id);
            $oldChapterId = $file->chapter_id;

            $file->update([
                'chapter_id' => $data['chapter_id'],
                'file' => $data['file_path'],
                'volume' => formatSizeUnits(!empty($fileInfos) ? $fileInfos['size'] : $data['volume']),
                'file_type' => !empty($fileInfos) ? $fileInfos['extension'] : $data['file_type'],
                'accessibility' => $data['accessibility'],
                'storage' => $data['storage'],
                'interactive_type' => $data['interactive_type'] ?? null,
                'interactive_file_name' => $data['interactive_file_name'] ?? null,
                'interactive_file_path' => $data['interactive_file_path'] ?? null,
                'downloadable' => $data['downloadable'],
                'online_viewer' => (!empty($data['online_viewer']) and $data['online_viewer'] == 'on'),
                'check_previous_parts' => $data['check_previous_parts'],
                'access_after_day' => $data['access_after_day'],
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? Refile::$Active : Refile::$Inactive,
                'updated_at' => time()
            ]);

            if ($changeChapter) {
                RemedyChapterItem::changeChapter($file->creator_id, $oldChapterId, $file->chapter_id, $file->id, RemedyChapterItem::$chapterFile);
            }

            RefileTranslation::updateOrCreate([
                'refile_id' => $file->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
            ]);

            RemedyChapterItem::where('user_id', $file->creator_id)
                ->where('item_id', $file->id)
                ->where('type', RemedyChapterItem::$chapterFile)
                ->delete();

            if (!empty($file->chapter_id)) {
                RemedyChapterItem::makeItem($file->creator_id, $file->chapter_id, $file->id, RemedyChapterItem::$chapterFile);
            }

            removeContentLocale();

            return response()->json([
                'code' => 200,
            ], 200);
        }

        removeContentLocale();

        return response()->json([], 422);
    }

    private function handleUnZipFile($data, $user_id)
    {
        $path = $data['file_path'];
        $interactiveType = $data['interactive_type'] ?? null;
        $interactiveFileName = $data['interactive_file_name'] ?? null;

        $storage = Storage::disk('public');

        $fileInfo = $this->fileInfo($path);

        $extractPath = $user_id . '/' . $fileInfo['name'];
        $storageExtractPath = $storage->url($extractPath);

        if (!$storage->exists($extractPath)) {
            $storage->makeDirectory($extractPath);

            $filePath = public_path($path);

            $zip = new \ZipArchive();
            $res = $zip->open($filePath);

            if ($res) {
                $zip->extractTo(public_path($storageExtractPath));

                $zip->close();
            }
        }

        $fileName = 'index.html';

        if ($interactiveType == 'i_spring') {
            $fileName = 'story.html';
        } elseif ($interactiveType == 'custom') {
            $fileName = $interactiveFileName;
        }

        return $storageExtractPath . '/' . $fileName;
    }

    private function uploadFileToS3($file, $user_id)
    {
        $path = 'store/' . $user_id;

        $result = [
            'path' => null,
            'status' => true
        ];

        try {
            $fileName = time() . $file->getClientOriginalName();

            $storage = Storage::disk('minio');

            if (!$storage->exists($path)) {
                $storage->makeDirectory($path);
            }

            $path = $storage->put($path, $file, $fileName);
            $result['path'] = $storage->url($path);
        } catch (\Exception $ex) {

            $result = [
                'path' => response([
                    'code' => 500,
                    'message' => $ex->getMessage(),
                    'traces' => $ex->getTrace(),
                ], 500),
                'status' => false
            ];
        }

        return $result;
    }

    private function uploadFileToBunny($remedy, $file)
    {
        $result = [
            'path' => null,
            'status' => true
        ];

        try {
            $bunnyVideoStream = new BunnyVideoStream();

            $collectionId = $bunnyVideoStream->createCollection("course {$remedy->id}", true);

            if ($collectionId) {

                $videoUrl = $bunnyVideoStream->uploadVideo($file->getClientOriginalName(), $collectionId, $file);

                $result['path'] = $videoUrl;
            }
        } catch (\Exception $ex) {

            $result = [
                'path' => response([
                    'code' => 500,
                    'message' => $ex->getMessage(),
                    'traces' => $ex->getTrace(),
                ], 500),
                'status' => false
            ];
        }

        return $result;
    }

    // public function fileInfo($path)
    // {
    //     $file = array();

    //     $file_path = public_path($path);

    //     $filePath = pathinfo($file_path);

    //     $file['name'] = $filePath['filename'];
    //     $file['extension'] = $filePath['extension'];
    //     $file['size'] = filesize($file_path);

    //     return $file;
    // }
    
    public function fileInfo($path)
    {
        $file = array();

        $file_path = public_path($path);
        
        $filePath = pathinfo($file_path);
         $file_size = Storage::disk('upload')->size($path);
        $file['name'] = $filePath['filename'];
        $file['extension'] = $filePath['extension'];
        $file['size'] = $file_size;

        return $file;
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $file = Refile::where('id', $id)
            ->first();

        if (!empty($file)) {
            RemedyChapterItem::where('user_id', $file->creator_id)
                ->where('item_id', $file->id)
                ->where('type', RemedyChapterItem::$chapterFile)
                ->delete();

            $file->delete();
        }

        return response()->json([
            'code' => 200
        ], 200);
    }
}
