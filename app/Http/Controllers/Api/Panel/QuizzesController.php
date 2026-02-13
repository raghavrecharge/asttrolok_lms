<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
 use App\Http\Resources\QuizResource;
 use App\Models\Api\Quiz;
use App\Models\Api\QuizzesResult;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;

class QuizzesController extends Controller
{
    public function show($id){
        try {
            $quiz = Quiz::where('id', $id)
                ->where('status', WebinarChapter::$chapterActive)->first();
            abort_unless($quiz, 404);

            if ($error = $quiz->canViewError()) {

            }
            $resource = new QuizResource($quiz);
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

    public function created(Request $request)
    {
        try {
            $user = apiAuth();
            $quizzes = $user->userCreatedQuizzes()->
            orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')->get()->map(function ($quiz) {
                    return $quiz->details;
                });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'quizzes' => $quizzes
            ]);
        } catch (\Exception $e) {
            \Log::error('created error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function notParticipated(Request $request)
    {
        try {
            $user = apiAuth();
            $webinarIds = $user->getPurchasedCoursesIds();

            $quizzes = Quiz::whereIn('webinar_id', $webinarIds)
                ->where('status', 'active')
                ->whereDoesntHave('quizResults', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->handleFilters()
                ->orderBy('created_at', 'desc')
                ->get()->map(function ($quiz) {
                    return $quiz->details;
                });

                $query = QuizzesResult::where('user_id', $user->id);

            $quizResultsCount = deepClone($query)->count();
            $passedCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$passed)->count();
            $failedCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$failed)->count();
            $waitingCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$waiting)->count();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
            'quizzesResults' => $quizzes,
            'quizzesResultsCount' => $quizResultsCount,
            'passedCount' => $passedCount,
            'failedCount' => $failedCount,
            'waitingCount' => $waitingCount
            ]);
        } catch (\Exception $e) {
            \Log::error('notParticipated error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function resultsByQuiz($quizId)
    {
        try {
            $user = apiAuth();
            $query = QuizzesResult::where('user_id', $user->id)
                ->where('quiz_id', $quizId);

            abort_unless(deepClone($query)->count(), 404);

            $result = (deepClone($query)->where('status', QuizzesResult::$passed)->first()) ?: null;
            if (!$result) {
                $result = deepClone($query)->latest()->first();
            }

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved')
                , $result->details
            );
        } catch (\Exception $e) {
            \Log::error('resultsByQuiz error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
