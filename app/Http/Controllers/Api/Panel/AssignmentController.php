<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarAssignmentHistoryResource;
use App\Http\Resources\WebinarAssignmentResource;
use App\Models\Sale;
use App\Models\Webinar;
use App\Models\Api\WebinarAssignment;
use App\Models\Api\WebinarAssignmentHistory;
use App\Models\WebinarAssignmentHistoryMessage;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!getFeaturesSettings('webinar_assignment_status')) {
                abort(403);
            }

            $user = apiAuth();

            $purchasedCoursesIds = Sale::where('buyer_id', $user->id)
                ->whereNotNull('webinar_id')
                ->whereNull('refund_at')
                ->pluck('webinar_id')
                ->toArray();

            $query = WebinarAssignment::whereIn('webinar_id', $purchasedCoursesIds)
                ->where('status', 'active')
                ->with(['assignmentHistory' => function ($d) use ($user) {
                    $d->where('student_id', $user->id);
                }]);

            $assignments = $query->handleFilters()->orderBy('created_at', 'desc')
                ->get()->map(function ($assignment) use ($user) {

                    return $assignment->assignmentHistory;
                });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [

                    'assignments' => WebinarAssignmentHistoryResource::collection($assignments),

                ]);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function show($id)
    {
        try {
            $user = apiAuth();
            $assignmnet = WebinarAssignment::where('id', $id)

                ->with(['assignmentHistory' => function ($d) use ($user) {
                    $d->where('student_id', $user->id);
                }])
                ->where('status', WebinarChapter::$chapterActive)->first();
            abort_unless($assignmnet, 404);

            $purchasedCoursesIds = Sale::where('buyer_id', $user->id)
                ->whereNotNull('webinar_id')
                ->whereNull('refund_at')
                ->pluck('webinar_id')
                ->toArray();
            if (!in_array($assignmnet->webinar->id,$purchasedCoursesIds)){
                abort(404);
            }

            if ($error = $assignmnet->canViewError()) {

            }
            $resource = new WebinarAssignmentHistoryResource($assignmnet->assignmentHistory);
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
