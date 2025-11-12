<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Models\Meeting;
use App\Models\Api\ReserveMeeting;
use \Illuminate\Http\Request;


class ReserveMeetingsController extends Controller
{
    public function index(Request $request)
    {

        $data = [
            'reservations' => [
                'count'=>count($this->getReservation()) ,
                'meetings' => $this->getReservation(),
            ],
            'requests' =>[
                'count'=>count( $this->getRequests()) ,
                'meetings'=> $this->getRequests()
            ],
        ];
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);

    }

    public function show(Request $request, $id)
    {
        $user = apiAuth();
        $reserveMeetingsQuery = ReserveMeeting::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere(function ($q) use ($user) {

                    $q->whereHas('meeting', function ($qq) use ($user) {
                        $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
                        $qq->whereIn('meeting_id', $meetingIds);
                    });
                });
            })
            ->first();
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
            'meeting' => $reserveMeetingsQuery
        ]);


    }

    public function reservation(Request $request)
    {
              $user = apiAuth();
    $reserveMeetingsQuery = ReserveMeeting::where('user_id', $user->id)
            ->whereNotNull('reserved_at')
            ->whereHas('sale', function ($query) {
                $query->whereNull('refund_at');
            });

        $openReserveCount = $reserveMeetingsQuery->where('status', \App\models\ReserveMeeting::$open)->count();
       

        $meetingIds = $reserveMeetingsQuery->select('meeting_id')->get();
       
        $sumReservePaid = $reserveMeetingsQuery->sum('paid_amount');
      $activeMeetingTimes =0;
          $meetingTimesCount =0;
      
        if(!empty($meetingIds) && count($meetingIds) >0 ){
              
        $activeMeetingTimeIds = ReserveMeeting::whereIn('meeting_id', $meetingIds)
            ->where('status', ReserveMeeting::$pending)
            ->whereHas('sale', function ($query) {
                $query->whereNull('refund_at');
            })
            ->pluck('meeting_time_id')
            ->toArray();
            
             $meetingTimesCount = array_count_values($activeMeetingTimeIds);
        $activeMeetingTimes = MeetingTime::whereIn('id', $activeMeetingTimeIds)->get();
        }

       

        $activeHoursCount = 0;
         if(!empty($activeMeetingTimes)){
        foreach ($activeMeetingTimes as $time) {
            $explodetime = explode('-', $time->time);
            $hour = strtotime($explodetime[1]) - strtotime($explodetime[0]);

            if (!empty($meetingTimesCount) and is_array($meetingTimesCount) and !empty($meetingTimesCount[$time->id])) {
                $hour = $hour * $meetingTimesCount[$time->id];
            }

            $activeHoursCount += $hour;
        }
         }
         $data = [
            'pageTitle' => trans('meeting.meeting_requests_page_title'),
            // 'pendingReserveCount' => $pendingReserveCount,
            'totalReserveCount' => count($this->getReservation()),
            'sumReservePaid' => $sumReservePaid,
            'activeHoursCount' => $activeHoursCount,
            // 'usersReservedTimes' => $usersReservedTimes,
            'reserveMeetings' => $this->getReservation(),
        ];
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            $data
        );

    }

    public function requests(Request $request)
    {
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            $this->getRequests()
        );
    }

    public function getReservation()
    {

        $user = apiAuth();
        // print_r($user);die;
        $reservedMeetings = ReserveMeeting::where('user_id', $user->id)
            ->whereHas('sale')
            ->whereNotNull('reserved_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reserveMeeting) {
                return $reserveMeeting->details;
            });

        return $reservedMeetings;
    }

    public function getRequests()
    {
        $user = apiAuth();
        $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
        $reservedMeetings = ReserveMeeting::whereIn('meeting_id', $meetingIds)->whereHas('sale')
            ->orderBy('created_at', 'desc')

            ->get()->map(function ($reserveMeeting) {
                return $reserveMeeting->details;
            });

        return $reservedMeetings;
    }

    public function finish($id)
    {
        $user = apiAuth();

        $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');

        $ReserveMeeting = ReserveMeeting::where('id', $id)
            ->where(function ($query) use ($user, $meetingIds) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('meeting_id', $meetingIds);
            })
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

            return apiResponse2(1, 'finished',
                trans('api.meeting.finished'));

        }
        abort(404);

    }

}
