<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\CalendarLinks\Link;

class ReserveMeeting extends Model
{
    protected $table = "reserve_meetings";
    public static $open = "open";
    public static $finished = "finished";
    public static $pending = "pending";
    public static $canceled = "canceled";

    public $timestamps = false;

    protected $guarded = ['id'];

    public function meetingTime()
    {
        return $this->belongsTo('App\Models\MeetingTime', 'meeting_time_id', 'id');
    }

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting', 'meeting_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function session()
    {
        return $this->hasOne('App\Models\Session', 'reserve_meeting_id', 'id');
    }

    public function getDiscountPrice($user)
    {
        $price = $this->paid_amount;
        $totalDiscount = 0;

        if (!empty($this->discount)) {
            $totalDiscount += ($price * $this->discount) / 100;
        }

        if (!empty($user) and !empty($user->getUserGroup()) and isset($user->getUserGroup()->discount) and $user->getUserGroup()->discount > 0) {
            $totalDiscount += ($price * $user->getUserGroup()->discount) / 100;
        }

        return $totalDiscount;
    }

    public function addToCalendarLink()
    {
        try {
            $date = date('Y-m-d', $this->start_at);
            $startTime = date('H:i', $this->start_at);
            $endTime = date('H:i', $this->end_at);

            $link = Link::create(trans('public.reserve_meeting'), \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $startTime), \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $endTime))
                ->description(trans('public.reserve_meeting_on_asttrolok'))
                ->google();

            return $link;
        } catch (\Exception $e) {
            return '';
        }
    }
}
