<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\CourseNoticeboard;
use App\Models\CourseNoticeboardStatus;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseNoticeboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->isOrganization() and !$user->isTeacher()) {
                abort(404);
            }

            $query = CourseNoticeboard::where('creator_id', $user->id);

            $noticeboards = $this->handleFilters($request, $query)
                ->with([
                    'webinar'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $webinars = Webinar::select('id')
                ->where('status', Webinar::$active)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id);
                    $query->orWhere('teacher_id', $user->id);
                })
                ->get();

            $data = [
                'pageTitle' => trans('panel.noticeboards'),
                'noticeboards' => $noticeboards,
                'isCourseNotice' => true,
                'webinars' => $webinars,
            ];

            return view(getTemplate() . '.panel.noticeboard.index', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleFilters(Request $request, $query)
    {
        try {
            $from = $request->get('from');
            $to = $request->get('to');
            $webinarId = $request->get('webinar_id');
            $title = $request->get('title');
            $color = $request->get('color');

            $query = fromAndToDateFilter($from, $to, $query, 'created_at');

            if (!empty($webinarId)) {
                $query->where('webinar_id', $webinarId);
            }

            if (!empty($color)) {
                $query->where('color', $color);
            }

            if (!empty($title)) {
                $query->where('title', 'like', "%$title%");
            }

            return $query;
        } catch (\Exception $e) {
            \Log::error('handleFilters error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function create()
    {
        try {
            $user = auth()->user();

            if (!$user->isOrganization() and !$user->isTeacher()) {
                abort(404);
            }

            $webinars = Webinar::select('id')
                ->where('status', Webinar::$active)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id);
                    $query->orWhere('teacher_id', $user->id);
                })
                ->get();

            $data = [
                'pageTitle' => trans('panel.new_noticeboard'),
                'isCourseNotice' => true,
                'webinars' => $webinars,
            ];

            return view(getTemplate() . '.panel.noticeboard.create', $data);
        } catch (\Exception $e) {
            \Log::error('create error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->isOrganization() and !$user->isTeacher()) {
                abort(404);
            }

            $data = $request->all();

            $validator = Validator::make($data, [
                'title' => 'required|string|max:255',
                'webinar_id' => 'required',
                'color' => 'required',
                'message' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            $webinar = Webinar::where('id', $data['webinar_id'])->first();

            if (empty($webinar) or ($webinar->teacher_id != $user->id and $webinar->creator_id != $user->id)) {
                return response()->json([
                    'code' => 422,
                    'errors' => [
                        'webinar_id' => [trans('cart.course_not_found')]
                    ]
                ], 422);
            }

            CourseNoticeboard::create([
                'creator_id' => $user->id,
                'webinar_id' => $webinar->id,
                'color' => $data['color'],
                'title' => $data['title'],
                'message' => $data['message'],
                'created_at' => time()
            ]);

            $studentsIds = $webinar->getStudentsIds();
            if (count($studentsIds)) {
                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                    '[item_title]' => $data['title'],
                    '[time.date]' => dateTimeFormat(time(), 'j M Y H:i')
                ];

                foreach ($studentsIds as $studentId) {
                    sendNotification("new_course_notice", $notifyOptions, $studentId);
                }
            }

            return response()->json([
                'code' => 200,
                'redirectTo' => '/panel/course-noticeboard'
            ]);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($noticeboard_id)
    {
        try {
            $user = auth()->user();

            if (!$user->isOrganization() and !$user->isTeacher()) {
                abort(404);
            }

            $noticeboard = CourseNoticeboard::where('creator_id', $user->id)
                ->where('id', $noticeboard_id)
                ->first();

            $webinars = Webinar::select('id')
                ->where('status', Webinar::$active)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id);
                    $query->orWhere('teacher_id', $user->id);
                })
                ->get();

            if (!empty($noticeboard)) {
                $data = [
                    'pageTitle' => trans('panel.noticeboards'),
                    'noticeboard' => $noticeboard,
                    'webinars' => $webinars,
                    'isCourseNotice' => true,
                ];

                return view(getTemplate() . '.panel.noticeboard.create', $data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $noticeboard_id)
    {
        try {
            $user = auth()->user();

            if (!$user->isOrganization() and !$user->isTeacher()) {
                abort(404);
            }

            $noticeboard = CourseNoticeboard::where('creator_id', $user->id)
                ->where('id', $noticeboard_id)
                ->first();

            if (!empty($noticeboard)) {
                $data = $request->all();

                $validator = Validator::make($data, [
                    'title' => 'required|string|max:255',
                    'webinar_id' => 'required',
                    'color' => 'required',
                    'message' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'code' => 422,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $webinar = Webinar::where('id', $data['webinar_id'])->first();

                if (empty($webinar) or ($webinar->teacher_id != $user->id and $webinar->creator_id != $user->id)) {
                    return response()->json([
                        'code' => 422,
                        'errors' => [
                            'webinar_id' => [trans('cart.course_not_found')]
                        ]
                    ], 422);
                }

                $noticeboard->update([
                    'webinar_id' => $webinar->id,
                    'color' => $data['color'],
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'created_at' => time()
                ]);

                CourseNoticeboardStatus::where('noticeboard_id', $noticeboard->id)->delete();

                return response()->json([
                    'code' => 200,
                    'redirectTo' => '/panel/course-noticeboard'
                ]);
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

    public function delete($noticeboard_id)
    {
        try {
            $user = auth()->user();

            if (!$user->isOrganization() and !$user->isTeacher()) {
                abort(404);
            }

            $noticeboard = CourseNoticeboard::where('creator_id', $user->id)
                ->where('id', $noticeboard_id)
                ->first();

            if (!empty($noticeboard)) {
                $noticeboard->delete();

                return response()->json([
                    'code' => 200,
                ]);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
