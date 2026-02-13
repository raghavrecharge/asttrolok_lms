<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class WebinarQuizController extends Controller
{
    public function store(Request $request)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $this->validate($request, [
                'webinar_id' => 'required|exists:webinars,id',
                'quiz_id' => 'required',
            ]);

            $data = $request->all();

            $quiz = Quiz::where('id', $data['quiz_id'])
                ->where('webinar_id', null)
                ->first();

            if (!empty($quiz)) {
                $quiz->webinar_id = $data['webinar_id'];
                $quiz->chapter_id = $data['chapter_id'] ?? null;
                $quiz->save();

                return response()->json([
                    'code' => 200,
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $webinarQuiz = Quiz::where('id', $id)
                ->first();

            if (!empty($webinarQuiz)) {

                return response()->json([
                    'webinarQuiz' => $webinarQuiz
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $this->validate($request, [
                'webinar_id' => 'required',
                'quiz_id' => 'required',
            ]);

            $data = $request->all();

            $quiz = Quiz::findOrFail($data['quiz_id']);

            if (!empty($quiz)) {
                $quiz->webinar_id = $data['webinar_id'];
                $quiz->chapter_id = $data['chapter_id'] ?? null;
                $quiz->save();

                return response()->json([
                    'code' => 200,
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $quiz = Quiz::where('id', $id)->first();

            if (!empty($quiz)) {
                $quiz->delete();
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
