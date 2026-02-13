<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChapterVedio;
use Illuminate\Support\Facades\Validator;

class ChapterVedioController extends Controller
{
    public function store(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'webinar_chapter_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'video_url' => 'required|url',
        'duration' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors()
        ], 422);
    }

    // Database mein save
    $video = ChapterVedio::create($request->only([
        'webinar_chapter_id',
        'title',
        'video_url',
        'duration'
    ]));

    return response()->json([
        'success' => true,
        'message' => 'Video created successfully',
        'data' => $video
    ], 201);
}
public function update(Request $request, $id)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'webinar_chapter_id' => 'sometimes|integer',
        'title' => 'sometimes|string|max:255',
        'video_url' => 'sometimes|url',
        'duration' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors()
        ], 422);
    }

    // Video find karein
    $video = ChapterVedio::find($id);

    if (!$video) {
        return response()->json([
            'success' => false,
            'message' => 'Video not found'
        ], 404);
    }

    // Update karein
    $video->update($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Video updated successfully',
        'data' => $video
    ], 200);
}
}