<?php

namespace App\Http\Controllers\Api\Instructor;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\Quiz;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\User;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Api\Panel\ReserveMeetingsController;

class ReserveMeetingController extends Controller
{
    public function createLink(Request $request)
    {
        try {
            validateParam($request->all(), [
                'link' => 'required|url',
                'reserved_meeting_id' => 'required|exists:reserve_meetings,id',

            ]);

            $user = apiAuth();
            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');

            $link = $request->input('link');
            $ReserveMeeting = ReserveMeeting::where('id', $request->input('reserved_meeting_id'))
                ->whereIn('meeting_id', $meetingIds)
                ->first();

            if (!empty($ReserveMeeting) and !empty($ReserveMeeting->meeting)) {
                $ReserveMeeting->update([
                    'link' => $link,
                    'password' => $request->input('password') ?? null,
                    'status' => ReserveMeeting::$open,
                ]);

                $notifyOptions = [
                    '[link]' => $link,
                    '[instructor.name]' => $ReserveMeeting->meeting->creator->full_name,
                    '[time.date]' => $ReserveMeeting->day,
                ];
                sendNotification('new_appointment_link', $notifyOptions, $ReserveMeeting->user_id);

                return apiResponse2(1, 'stored', trans('api.public.stored'));
            }
        } catch (\Exception $e) {
            \Log::error('createLink error: ' . $e->getMessage(), [
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
            $controller = new ReserveMeetingsController();
            return $controller->requests( $request);
        } catch (\Exception $e) {
            \Log::error('requests error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
