<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\Quiz;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Session;
use App\Models\Translation\SessionTranslation;
use App\Models\WebinarChapterItem;
use App\User;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReserveMeetingController extends Controller
{
    public function reservation(Request $request)
    {
        try {
            $user = auth()->user();
            $reserveMeetingsQuery = ReserveMeeting::where('user_id', $user->id)
                ->whereNotNull('reserved_at')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                });

            $totalReserveCount = (clone $reserveMeetingsQuery)->count();
            $finishedReserveCount = (clone $reserveMeetingsQuery)->where('status', \App\Models\ReserveMeeting::$finished)->count();
            $upcomingReserveCount = (clone $reserveMeetingsQuery)->whereIn('status', [
                \App\Models\ReserveMeeting::$open,
                \App\Models\ReserveMeeting::$pending
            ])->count();

            $meetingIds = (clone $reserveMeetingsQuery)->pluck('meeting_id')->toArray();
            $teacherIds = Meeting::whereIn('id', array_unique($meetingIds))
                ->pluck('creator_id')
                ->toArray();
            $instructors = User::select('id', 'full_name')
                ->whereIn('id', array_unique($teacherIds))
                ->get();

            $reserveMeetingsQuery = $this->filters($reserveMeetingsQuery, $request);
            $reserveMeetingsQuery = $reserveMeetingsQuery->with([
                'meetingTime',
                'meeting' => function ($query) {
                    $query->with([
                        'creator' => function ($query) {
                            $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'email');
                        }
                    ]);
                },
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'email');
                },
                'sale'
            ]);

            $reserveMeetings = $reserveMeetingsQuery
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('meeting.meeting_list_page_title'),
                'instructors' => $instructors,
                'reserveMeetings' => $reserveMeetings,
                'totalReserveCount' => $totalReserveCount,
                'finishedReserveCount' => $finishedReserveCount,
                'upcomingReserveCount' => $upcomingReserveCount,
            ];

            return view(getTemplate() . '.panel.meeting.reservation', $data);
        } catch (\Exception $e) {
            \Log::error('reservation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function requests(Request $request)
    {
        try {
            $meetingIds = Meeting::where('creator_id', auth()->user()->id)->pluck('id');

            $reserveMeetingsQuery = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where(function ($query) {
                    $query->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    });

                    $query->orWhere(function ($query) {
                        $query->whereIn('status', ['canceled']);
                        $query->whereHas('sale');
                    });
                });

            $pendingReserveCount = deepClone($reserveMeetingsQuery)->where('status', \App\models\ReserveMeeting::$pending)->count();
            $totalReserveCount = deepClone($reserveMeetingsQuery)->count();
            $sumReservePaid = deepClone($reserveMeetingsQuery)->sum('paid_amount');

            $userIdsReservedTime = deepClone($reserveMeetingsQuery)->pluck('user_id')->toArray();
            $usersReservedTimes = User::select('id', 'full_name')
                ->whereIn('id', array_unique($userIdsReservedTime))
                ->get();

            $reserveMeetingsQuery = $this->filters(deepClone($reserveMeetingsQuery), $request);
            $reserveMeetingsQuery = $reserveMeetingsQuery->with([
                'meetingTime',
                'meeting',
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'email');
                }
            ]);

            $sort = $request->get('sort', 'newest');
            switch ($sort) {
                case 'oldest':
                    $reserveMeetingsQuery->orderBy('created_at', 'asc');
                    break;
                case 'amount_high':
                    $reserveMeetingsQuery->orderBy('paid_amount', 'desc');
                    break;
                case 'amount_low':
                    $reserveMeetingsQuery->orderBy('paid_amount', 'asc');
                    break;
                default:
                    $reserveMeetingsQuery->orderBy('created_at', 'desc');
                    break;
            }

            $reserveMeetings = $reserveMeetingsQuery
                ->paginate(10);

            $activeMeetingTimeIds = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where('status', ReserveMeeting::$pending)
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->pluck('meeting_time_id')
                ->toArray();

            $meetingTimesCount = array_count_values($activeMeetingTimeIds);
            $activeMeetingTimes = MeetingTime::whereIn('id', $activeMeetingTimeIds)->get();

            $activeHoursCount = 0;
            foreach ($activeMeetingTimes as $time) {
                $explodetime = explode('-', $time->time);
                $hour = strtotime($explodetime[1]) - strtotime($explodetime[0]);

                if (!empty($meetingTimesCount) and is_array($meetingTimesCount) and !empty($meetingTimesCount[$time->id])) {
                    $hour = $hour * $meetingTimesCount[$time->id];
                }

                $activeHoursCount += $hour;
            }

            $data = [
                'pageTitle' => trans('meeting.meeting_requests_page_title'),
                'reserveMeetings' => $reserveMeetings,
                'pendingReserveCount' => $pendingReserveCount,
                'totalReserveCount' => $totalReserveCount,
                'sumReservePaid' => $sumReservePaid,
                'activeHoursCount' => $activeHoursCount,
                'usersReservedTimes' => $usersReservedTimes,
            ];

            return view(getTemplate() . '.panel.meeting.requests', $data);
        } catch (\Exception $e) {
            \Log::error('requests error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function filters($query, $request)
    {
        try {
            $from = $request->get('from');
            $to = $request->get('to');
            $day = $request->get('day');
            $instructor_id = $request->get('instructor_id');
            $student_id = $request->get('student_id');
            $status = $request->get('status');
            $openMeetings = $request->get('open_meetings');

            $query = fromAndToDateFilter($from, $to, $query, 'created_at');

            if (!empty($day) and $day != 'all') {
                $meetingTimeIds = $query->pluck('meeting_time_id');
                $meetingTimeIds = MeetingTime::whereIn('id', $meetingTimeIds)
                    ->where('day_label', $day)
                    ->pluck('id');

                $query->whereIn('meeting_time_id', $meetingTimeIds);
            }

            if (!empty($instructor_id) and $instructor_id != 'all') {

                $meetingsIds = Meeting::where('creator_id', $instructor_id)
                    ->where('disabled', false)
                    ->pluck('id')
                    ->toArray();

                $query->whereIn('meeting_id', $meetingsIds);
            }

            if (!empty($student_id) and $student_id != 'all') {
                $query->where('user_id', $student_id);
            }

            if (!empty($status) and $status != 'All') {
                $query->where('status', strtolower($status));
            }

            if (!empty($openMeetings) and $openMeetings == 'on') {
                $query->where('status', 'open');
            }

            $meetingType = $request->get('meeting_type');
            if (!empty($meetingType)) {
                $query->where('meeting_type', $meetingType);
            }

            return $query;
        } catch (\Exception $e) {
            \Log::error('filters error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function finish($id)
    {
        try {
            $user = auth()->user();

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');

            $ReserveMeeting = ReserveMeeting::where('id', $id)
                ->where(function ($query) use ($user, $meetingIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('meeting_id', $meetingIds);
                })
                ->with(['meeting', 'user'])
                ->first();

            if (!empty($ReserveMeeting)) {
                $ReserveMeeting->update([
                    'status' => ReserveMeeting::$finished
                ]);

                $notifyOptions = [
                    '[student.name]' => $ReserveMeeting->user->full_name,
                    '[instructor.name]' => $ReserveMeeting->meeting->creator->full_name,
                    '[time.date]' => $ReserveMeeting->day,
                ];
                sendNotification('meeting_finished', $notifyOptions, $ReserveMeeting->user_id);
                sendNotification('meeting_finished', $notifyOptions, $ReserveMeeting->meeting->creator_id);

                return response()->json([
                    'code' => 200
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('finish error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function createLink(Request $request)
    {
        try {
            $this->validate($request, [
                'link' => 'required|url'
            ]);

            $user = auth()->user();

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');

            $link = $request->input('link');
            $ReserveMeeting = ReserveMeeting::where('id', $request->input('item_id'))
                ->whereIn('meeting_id', $meetingIds)
                ->first();

            if (!empty($ReserveMeeting) and !empty($ReserveMeeting->meeting)) {
                $ReserveMeeting->update([
                    'link' => $link,
                    'password' => $request->input('password'),
                    'status' => ReserveMeeting::$open,
                ]);

                $notifyOptions = [
                    '[link]' => $link,
                    '[instructor.name]' => $ReserveMeeting->meeting->creator->full_name,
                    '[time.date]' => $ReserveMeeting->day,
                ];
                sendNotification('new_appointment_link', $notifyOptions, $ReserveMeeting->user_id);
            }

            return response()->json([
                'code' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('createLink error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function join(Request $request, $id)
    {
        try {
            $user = auth()->user();

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');

            $ReserveMeeting = ReserveMeeting::where('id', $id)
                ->where(function ($query) use ($user, $meetingIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('meeting_id', $meetingIds);
                })
                ->first();

            if (!empty($ReserveMeeting) and !empty($ReserveMeeting->link)) {
                return Redirect::away($ReserveMeeting->link);
            }

            abort(403);
        } catch (\Exception $e) {
            \Log::error('join error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function addLiveSession(Request $request, $id)
    {
        try {
            $user = auth()->user();

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');

            $ReserveMeeting = ReserveMeeting::where('id', $id)
                ->whereIn('meeting_id', $meetingIds)
                ->first();

            if (!empty($ReserveMeeting)) {
                $agoraSettings = [
                    'chat' => true,
                    'record' => true,
                    'users_join' => true
                ];

                $session = Session::query()->updateOrCreate([
                    'creator_id' => $user->id,
                    'reserve_meeting_id' => $ReserveMeeting->id,
                ], [
                    'date' => time(),
                    'duration' => (($ReserveMeeting->end_at - $ReserveMeeting->start_at) / 60),
                    'link' => null,
                    'session_api' => 'agora',
                    'agora_settings' => json_encode($agoraSettings),
                    'check_previous_parts' => false,
                    'status' => Session::$Active,
                    'created_at' => time()
                ]);

                if (!empty($session)) {
                    SessionTranslation::updateOrCreate([
                        'session_id' => $session->id,
                        'locale' => mb_strtolower(app()->getLocale()),
                    ], [
                        'title' => trans('update.new_in-app_call_session'),
                        'description' => trans('update.new_in-app_call_session'),
                    ]);

                    $ReserveMeeting->update([
                        'status' => ReserveMeeting::$open,
                    ]);

                    $notifyOptions = [
                        '[link]' => $session->getJoinLink(),
                        '[instructor.name]' => $user->full_name,
                        '[time.date]' => dateTimeFormat($session->date, 'j M Y H:i'),
                    ];
                    sendNotification('new_appointment_session', $notifyOptions, $ReserveMeeting->user_id);

                    return response()->json([
                        'code' => 200
                    ]);
                }
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('addLiveSession error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
