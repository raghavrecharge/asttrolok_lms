<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use App\Http\Controllers\Controller;

use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\Newsletter;
use App\Models\Api\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\UserOccupation;
use App\Models\Api\Webinar;
use App\Models\Api\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Api\Setting;

use Illuminate\Support\Facades\Mail;
use App\PaymentChannels\ChannelManager;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;

use App\Models\Discount;

use App\Models\UserZoomLink;

use App\Models\UserSession;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Mixins\RegistrationBonus\RegistrationBonusAccounting;
use App\Models\Accounting;
use App\Models\Affiliate;
use App\Models\AffiliateCode;
use App\Models\Reward;
use App\Models\RewardAccounting;

use App\Models\UserMeta;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{

    public function profile(Request $request, $id)
    {
        try {
            $user = User::where('id', $id)
                ->whereIn('role_name', [Role::$organization, Role::$teacher, Role::$user])
                ->first();

            if (!$user) {
                abort(404);
            }
            if ($user) {
                unset($user->location);
            }

            if ($user->consultant == 1) {

                $meeting = Meeting::where('creator_id', $user->id)
                ->with([
                    'meetingTimes'
                ])
                ->first();

                return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'user' => $user,
                'meeting' => $meeting
            ]);
            }
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('profile error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function instructors(Request $request)
    {
        try {
            $providers = $this->handleProviders($request, [Role::$teacher]);

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $providers);
        } catch (\Exception $e) {
            \Log::error('instructors error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function mayank(Request $request)
    {
        try {
            return apiResponse2(1, 'mayank', trans('api.public.retrieved'), ['name' => 'mayank']);
        } catch (\Exception $e) {
            \Log::error('mayank error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function consultations(Request $request)
    {
        try {
            $providers = $this->handleProviders($request, [Role::$teacher, Role::$organization],1, true);
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $providers);
        } catch (\Exception $e) {
            \Log::error('consultations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function organizations(Request $request)
    {
        try {
            $providers = $this->handleProviders($request, [Role::$organization]);

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $providers);
        } catch (\Exception $e) {
            \Log::error('organizations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function providers(Request $request)
    {
        try {
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'instructors' => $this->instructors($request),
                'organizations' => $this->organizations($request),
                'consultations' => $this->consultations($request),
            ]);
        } catch (\Exception $e) {
            \Log::error('providers error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleProviders(Request $request, $role,$consultant=0, $has_meeting = false)
    {
        try {
            $query = User::whereIn('role_name', $role)

                ->where('users.status', 'active')
                ->where('users.consultant', $consultant)
                ->where(function ($query) {
                    $query->where('users.ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('users.ban_end_at')
                                ->orWhere('users.ban_end_at', '<', time());
                        });
                });

            if ($has_meeting) {
                $query->whereHas('meeting');
            }

            $users = $this->filterProviders($request, deepClone($query), $role)

                ->get()
                ->map(function ($user) {

                    return $user->brief;
                });

            return [
                'count' => $users->count(),
                'users' => $users,
            ];
        } catch (\Exception $e) {
            \Log::error('handleProviders error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function filterProviders($request, $query, $role)
    {
        $categories = $request->get('categories', null);
        $sort = $request->get('sort', null);
        $availableForMeetings = $request->get('available_for_meetings', null);
        $hasFreeMeetings = $request->get('free_meetings', null);
        $withDiscount = $request->get('discount', null);
        $search = $request->get('search', null);
        $organization_id = $request->get('organization', null);
        $downloadable = $request->get('downloadable', null);

        if ($downloadable) {
            $query->whereHas('webinars', function ($qu) {
                return $qu->where('downloadable', 1);
            });
        }
        if (!empty($categories) and is_array($categories)) {
            $userIds = UserOccupation::whereIn('category_id', $categories)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }
        if ($organization_id) {
            $query->where('organ_id', $organization_id);
        }

        if (!empty($sort) and $sort == 'top_rate') {
            $query = $this->getBestRateUsers($query, $role);
        }

        if (!empty($sort) and $sort == 'top_sale') {
            $query = $this->getTopSalesUsers($query, $role);
        }

        if (!empty($availableForMeetings) and $availableForMeetings == 1) {
            $hasMeetings = DB::table('meetings')
                ->where('meetings.disabled', 0)
                ->join('meeting_times', 'meetings.id', '=', 'meeting_times.meeting_id')
                ->select('meetings.creator_id', DB::raw('count(meeting_id) as counts'))
                ->groupBy('creator_id')
                ->orderBy('counts', 'desc')
                ->get();

            $hasMeetingsInstructorsIds = [];
            if (!empty($hasMeetings)) {
                $hasMeetingsInstructorsIds = $hasMeetings->pluck('creator_id')->toArray();
            }

            $query->whereIn('users.id', $hasMeetingsInstructorsIds);
        }

        if (!empty($hasFreeMeetings) and $hasFreeMeetings == 1) {
            $freeMeetingsIds = Meeting::where('disabled', 0)
                ->where(function ($query) {
                    $query->whereNull('amount')->orWhere('amount', '0');
                })->groupBy('creator_id')
                ->pluck('creator_id')
                ->toArray();

            $query->whereIn('users.id', $freeMeetingsIds);
        }

        if (!empty($withDiscount) and $withDiscount == 1) {
            $withDiscountMeetingsIds = Meeting::where('disabled', 0)
                ->whereNotNull('discount')
                ->groupBy('creator_id')
                ->pluck('creator_id')
                ->toArray();

            $query->whereIn('users.id', $withDiscountMeetingsIds);
        }

        if (!empty($search)) {
            $query->where(function ($qu) use ($search) {
                $qu->where('users.full_name', 'like', "%$search%")
                    ->orWhere('users.email', 'like', "%$search%")
                    ->orWhere('users.mobile', 'like', "%$search%");
            });
        }

        return $query;
    }

    private function getBestRateUsers($query, $role)
    {
        $query->leftJoin('webinars', function ($join) use ($role) {
            if ($role == Role::$organization) {
                $join->on('users.id', '=', 'webinars.creator_id');
            } else {
                $join->on('users.id', '=', 'webinars.teacher_id');
            }

            $join->where('webinars.status', 'active');
        })->leftJoin('webinar_reviews', function ($join) {
            $join->on('webinars.id', '=', 'webinar_reviews.webinar_id');
            $join->where('webinar_reviews.status', 'active');
        })
            ->whereNotNull('rates')
            ->select('users.*', DB::raw('avg(rates) as rates'))
            ->orderBy('rates', 'desc');

        if ($role == Role::$organization) {
            $query->groupBy('webinars.creator_id');
        } else {
            $query->groupBy('webinars.teacher_id');
        }

        return $query;
    }

    private function getTopSalesUsers($query, $role)
    {
        $query->leftJoin('sales', function ($join) {
            $join->on('users.id', '=', 'sales.seller_id')
                ->whereNull('refund_at');
        })
            ->whereNotNull('sales.seller_id')
            ->select('users.*', 'sales.seller_id', DB::raw('count(sales.seller_id) as counts'))
            ->groupBy('sales.seller_id')
            ->orderBy('counts', 'desc');

        return $query;
    }

    public function sendMessage(Request $request, $id)
    {

        $user = User::find($id);
        abort_unless($user, 404);
        if (!$user->public_message) {
            return apiResponse2(0, 'disabled_public_message', trans('api.user.disabled_public_message'));
        }

        validateParam($request->all(), [
            'title' => 'required|string',
            'email' => 'required|email',
            'description' => 'required|string',

        ]);
        $data = $request->all();

        $mail = [
            'title' => $data['title'],
            'message' => trans('site.you_have_message_from', ['email' => $data['email']]) . "\n" . $data['description'],
        ];

        if (!isProductionDomain()) {
            return apiResponse2(1, 'email_sent', trans('api.user.email_sent'));
        }

        try {
            Mail::to($user->email)->send(new \App\Mail\SendNotifications($mail));

            return apiResponse2(1, 'email_sent', trans('api.user.email_sent'));

        } catch (Exception $e) {

            return apiResponse2(0, 'email_error', $e->getMessage());

        }

    }

    public function makeNewsletter(Request $request)
    {
        try {
            validateParam($request->all(), [
                'email' => 'required|string|email|max:255|unique:newsletters,email'
            ]);

            $data = $request->all();
            $user_id = null;
            $email = $data['email'];
            if (auth()->check()) {
                $user = auth()->user();

                if ($user->email == $email) {
                    $user_id = $user->id;

                    $user->update([
                        'newsletter' => true,
                    ]);
                }
            }

            Newsletter::create([
                'user_id' => $user_id,
                'email' => $email,
                'created_at' => time()
            ]);

            return apiResponse2('1', 'subscribed_newsletter', 'email subscribed in newsletter successfully.');
        } catch (\Exception $e) {
            \Log::error('makeNewsletter error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function availableTimes(Request $request, $id)
    {
        try {
            $timestamp = $request->get('timestamp');
            $dayLabel = $request->get('day_label');
            $date = $request->get('date');

            $user = User::where('id', $id)
                ->whereIn('role_name', [Role::$teacher, Role::$organization])
                ->where('status', 'active')
                ->first();

            if (!$user) {
                abort(404);
            }

            $meeting = Meeting::where('creator_id', $user->id)
                ->with(['meetingTimes'])
                ->first();

            if ($meeting->disabled ==1) {
            return apiResponse2(0, 'not_found', 'No available meeting times found for this day');
            }

            $resultMeetingTimes = [];

            if (!empty($meeting->meetingTimes)) {

                if (empty($dayLabel)) {
                    $dayLabel = dateTimeFormat($timestamp, 'l', false, false);
                }

                $dayLabel = mb_strtolower($dayLabel);

                $meetingTimes = $meeting->meetingTimes()->where('disabled',0)->where('day_label', $dayLabel)->get();

                if (!empty($meetingTimes) and count($meetingTimes)) {

                    foreach ($meetingTimes as $meetingTime) {
                        $can_reserve = true;

                        $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                            ->where('day', $date)
                            ->whereIn('status', ['pending', 'open'])
                            ->WhereNotNull('reserved_at')
                            ->first();
            if ($reserveMeeting) {

            }else{
            $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                            ->where('day', $date)
                            ->whereIn('status', ['pending', 'open'])
                            ->first();

            }

                        if ($reserveMeeting && ($reserveMeeting->locked_at || $reserveMeeting->reserved_at)) {
                            $can_reserve = false;
                        }

                        $vvv=explode('-', $meetingTime->time);
                        date_default_timezone_set("Asia/Kolkata");
                        if(strtotime(date("Y-m-d")) == strtotime($date)){
                        if(strtotime(date("Y-m-d h:i:sa"))>=strtotime($vvv[0])){

                        $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "time" => $vvv[0] ,
                            "description" => $meetingTime->description,
                            "can_reserve" => false,
                            'meeting_type' => $meetingTime->meeting_type
                        ];
                        }else{
                            $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "time" => $vvv[0] ,
                            "description" => $meetingTime->description,
                            "can_reserve" => $can_reserve,
                            'meeting_type' => $meetingTime->meeting_type
                        ];
                        }
                        }else{
                            $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "time" => $vvv[0] ,
                            "description" => $meetingTime->description,
                            "can_reserve" => $can_reserve,
                            'meeting_type' => $meetingTime->meeting_type
                        ];
                        }
                    }
                }
            }
            if($resultMeetingTimes){
            $time= [
                'times' => $resultMeetingTimes
            ];
              return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $time);
            }else{
               return apiResponse2(1, 'retrieved', "Data not found", []);

            }
        } catch (\Exception $e) {
            \Log::error('availableTimes error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function availableTimes1(Request $request, $id)
    {
        try {
            $timestamp = $request->get('timestamp');
            $dayLabel = $request->get('day_label');
            $date = $request->get('date');

            $user = User::where('id', $id)
            ->whereIn('role_name', [Role::$teacher, Role::$organization])
            ->where('status', 'active')
            ->first();

            if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => []
            ], 404);
            }

            $meeting = Meeting::where('creator_id', $user->id)
            ->with('meetingTimes')
            ->first();

             if ($meeting->disabled ==1) {
            return apiResponse2(0, 'not_found', 'No available meeting times found for this day');
            }

            $resultMeetingTimes = [];

            if (!empty($meeting->meetingTimes)) {
            if (empty($dayLabel)) {
                $dayLabel = dateTimeFormat($timestamp, 'l', false, false);
            }

            $dayLabel = mb_strtolower($dayLabel);
            $meetingTimes = $meeting->meetingTimes()->where('disabled', 0)->where('day_label', $dayLabel)->get();

            foreach ($meetingTimes as $meetingTime) {
                $can_reserve = true;

                $reserveMeetings = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                    ->where('day', $date)
                    ->whereIn('status', ['pending', 'open'])
                    ->whereNotNull('reserved_at')
                    ->get();

                $count = $reserveMeetings->count();
                $reserveMeeting = $reserveMeetings->first() ?? ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                    ->where('day', $date)
                    ->whereIn('status', ['pending', 'open'])
                    ->first();

                if ($reserveMeeting && ($reserveMeeting->locked_at || $reserveMeeting->reserved_at)) {
                    $can_reserve = false;
                }

                $timeRange = explode('-', $meetingTime->time);
                $slotStartTime = $timeRange[0];
                $secondSlotTime = date("h:iA", strtotime("+15 minutes", strtotime($slotStartTime)));

                date_default_timezone_set("Asia/Kolkata");
                $isToday = (strtotime(date("Y-m-d")) == strtotime($date));
                $currentTime = strtotime(date("h:i:sa"));

                for ($i = 1; $i <= 2; $i++) {
                    $slotTime = ($i == 1) ? $slotStartTime : $secondSlotTime;

                    $canBook = $can_reserve;
                    if (!$can_reserve) {
                        $canBook = ($reserveMeeting->slotid == null || $count == 2) ? false : ($reserveMeeting->slotid == $i ? true : true);
                    }

                    if ($isToday && $currentTime >= strtotime($slotStartTime)) {
                        $canBook = false;
                    }

                    $resultMeetingTimes[] = [
                        "id" => $meetingTime->id,
                        "slotid" => $i,
                        "time" => $slotTime,
                        "description" => $meetingTime->description,
                        "can_reserve" => $canBook,
                        'meeting_type' => $meetingTime->meeting_type,
                    ];
                }
            }
            }

            if (!empty($resultMeetingTimes)) {
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), ['times' => $resultMeetingTimes]);
            }

            return response()->json([
            'status' => false,
            'message' => 'Data not found',
            'data' => []
            ], 404);
        } catch (\Exception $e) {
            \Log::error('availableTimes1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function ReservedSlot(Request $request, $id)
    {
        try {
            $timestamp = $request->get('timestamp');
            $dayLabel = $request->get('day_label');
            $date = $request->get('date');

            $user = User::where('id', $id)
                ->whereIn('role_name', [Role::$teacher, Role::$organization])
                ->where('status', 'active')
                ->first();

            if (!$user) {
                abort(404);
            }

            $meeting = Meeting::where('creator_id', $user->id)
                ->with(['meetingTimes'])
                ->first();

            $resultMeetingTimes = [];

            if (!empty($meeting->meetingTimes)) {

                if (empty($dayLabel)) {
                    $dayLabel = dateTimeFormat($timestamp, 'l', false, false);
                }

                $dayLabel = mb_strtolower($dayLabel);

                $meetingTimes = $meeting->meetingTimes()->where('day_label', $dayLabel)->get();

                if (!empty($meetingTimes) and count($meetingTimes)) {

                    foreach ($meetingTimes as $meetingTime) {
                        $can_reserve = true;

                        $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                            ->where('day', $date)
                            ->whereIn('status', ['pending', 'open'])
                            ->WhereNotNull('reserved_at')
                            ->first();
            if ($reserveMeeting) {

                        if ($reserveMeeting && ($reserveMeeting->locked_at || $reserveMeeting->reserved_at)) {
                            $can_reserve = false;
                        }

                        $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "data" => $reserveMeeting
                        ];

                    }
                    }
                }
            }

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'count' => count($resultMeetingTimes),
                'times' => $resultMeetingTimes
            ]);
        } catch (\Exception $e) {
            \Log::error('ReservedSlot error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function reserve(Request $request)
    {
        try {
            $data = $request->only([
                'time', 'day', 'student_count', 'meeting_type', 'description'
            ]);

            $meeting_discount_id = $request->get('meeting_discount_id');

            $user = apiAuth();

            if (!$user) {
                $user = User::findOrCreateForPurchase(
                    $data['email'],
                    $data['mobile'],
                    $data['full_name'],
                    $data['password'] ?? null
                );
            }

            $studentCount = $data['student_count'] ?? 1;
            $meetingType = in_array($data['meeting_type'], ['in_person', 'online']) ? $data['meeting_type'] : 'online';

            $meetingTime = MeetingTime::with('meeting')->find($data['time']);
            if (!$meetingTime || !$meetingTime->meeting || $meetingTime->meeting->disabled) {
                return $this->error("Invalid or disabled meeting");
            }

            $meeting = $meetingTime->meeting;

            if ($meeting->creator_id == $user->id) {
                return $this->error("You cannot reserve your own meeting");
            }

            $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                ->where('day', $data['day'])
                ->first();

            if ($reserveMeeting && ($reserveMeeting->locked_at || $reserveMeeting->reserved_at)) {
                return $this->error("This time slot is already locked or reserved");
            }

            $amount = $this->calculateAmount($meeting, $meetingTime, $studentCount, $meetingType);
            if (!$amount['status']) return $amount['result'];

            $hours = $this->getDurationInHours($meetingTime->time);
            $startAt = $this->convertToUtc($data['day'], explode('-', $meetingTime->time)[0], $meeting->getTimezone());
            $endAt = $this->convertToUtc($data['day'], explode('-', $meetingTime->time)[1], $meeting->getTimezone());
            $discountAmount = $this->calculateDiscount($meetingTime, $amount['result'], $meeting_discount_id);

            $reserveMeeting = ReserveMeeting::updateOrCreate([
                'user_id' => $user->id,
                'meeting_time_id' => $meetingTime->id,
                'meeting_id' => $meetingTime->meeting_id,
                'status' => ReserveMeeting::$pending,
                'day' => $data['day'],
                'meeting_type' => $meetingType,
                'student_count' => $studentCount
            ], [
                'date' => strtotime($data['day']),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'paid_amount' => ($amount['result'] * $hours)-$discountAmount,
                'discount' => $meeting->discount,
                'description' => "",
                'created_at' => time(),
            ]);

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::$paying,
                'amount' => $amount['result'] * $hours,
                'tax' => 0,
                'total_discount' => $discountAmount ?? 0,
                'total_amount' => ($amount['result'] * $hours)-$discountAmount,
                'product_delivery_fee' => 0,
                'created_at' => time(),
            ]);

            OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'reserve_meeting_id' => $reserveMeeting->id,
                'amount' => $amount['result'] * $hours,
                'total_amount' => ($amount['result'] * $hours)-$discountAmount,
                'created_at' => time(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Meeting reserved and order created successfully',
                'order_id' => $order
            ]);
        } catch (\Exception $e) {
            \Log::error('reserve error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function calculateAmount($meeting, $meetingTime, $studentCount, $meetingType)
    {

        return ['status' => true, 'result' => $meeting->amount];
    }

    private function getDurationInHours($timeRange)
    {
        [$start, $end] = explode('-', $timeRange);
        return (strtotime($end) - strtotime($start)) / 1800;
    }

    private function convertToUtc($date, $time, $timezone)
    {
        return strtotime("$date $time");
    }

    private function finalizeOrder($order, $reserveMeeting)
    {
        $order->update(['payment_method' => 'payment_channel']);
        $orderItem = OrderItem::where('order_id', $order->id)->first();
        $sale = Sale::createSales($orderItem, $order->payment_method);

        $reserveMeeting->update([
            'sale_id' => $sale->id,
            'reserved_at' => time()
        ]);

        $order->update(['status' => Order::$paid]);
        Accounting::createAccounting($orderItem, null);
    }

    private function error($msg)
    {
        return response()->json([
            'status' => 'error',
            'message' => $msg,
            'title' => trans('public.request_failed')
        ]);
    }

   public function reserves(Request $request)
{
        try {
            $user = apiAuth();
            $name = $user->full_name;
            $email = $user->email;
            $contact = $user->mobile;

            $timeId = $request->input('time');
            $day = $request->input('day');
            $studentCount = $request->get('student_count', 1);
            $selectedMeetingType = in_array($request->get('meeting_type'), ['in_person', 'online']) ? $request->get('meeting_type') : 'online';
            $description = $request->get('description');
            $meeting_discount_id = $request->get('meeting_discount_id');

            if (!$timeId) {
            return $this->errorResponse(trans('meeting.select_time_to_reserve'));
            }

            $meetingTime = MeetingTime::with('meeting')->find($timeId);
            if (!$meetingTime || !$meetingTime->meeting || $meetingTime->meeting->disabled) {
            return $this->errorResponse(trans('meeting.meeting_disabled'));
            }

            $meeting = $meetingTime->meeting;

            if ($meeting->creator_id == $user->id) {
            return $this->errorResponse(trans('update.cant_reserve_your_appointment'));
            }

            if ($meeting->amount > 0) {
            $existingReservation = ReserveMeeting::where('meeting_time_id', $timeId)->where('day', $day)->first();

            if ($existingReservation && ($existingReservation->locked_at || $existingReservation->reserved_at)) {
                return $this->errorResponse(
                    $existingReservation->locked_at ? trans('meeting.locked_time') : trans('meeting.reserved_time')
                );
            }

            $hourlyAmountResult = $this->handleHourlyMeetingAmount($meeting, $meetingTime, $studentCount, $selectedMeetingType);
            if (!$hourlyAmountResult['status']) return $hourlyAmountResult['result'];

            $hourlyAmount = $hourlyAmountResult['result'];
            $discountAmount = $this->calculateDiscount($meetingTime, $hourlyAmount, $meeting_discount_id);

            $explodetime = explode('-', $meetingTime->time);
            $hours = (strtotime($explodetime[1]) - strtotime($explodetime[0])) / 1800;

            $startAt = $this->handleUtcDate($day, $explodetime[0], $meeting->getTimezone());
            $endAt = $this->handleUtcDate($day, $explodetime[1], $meeting->getTimezone());

            $reserveMeeting = ReserveMeeting::updateOrCreate([
                'user_id' => $user->id,
                'meeting_time_id' => $timeId,
                'meeting_id' => $meeting->id,
                'status' => ReserveMeeting::$pending,
                'day' => $day,
                'meeting_type' => $selectedMeetingType,
                'student_count' => $studentCount
            ], [
                'date' => strtotime($day),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'paid_amount' => ($hourlyAmount * $hours) - $discountAmount,
                'discount' => $meeting->discount,
                'description' => $description,
                'created_at' => time()
            ]);

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::$paying,
                'amount' => $hourlyAmount * $hours,
                'tax' => 0,
                'total_discount' => $discountAmount,
                'total_amount' => ($hourlyAmount * $hours) - $discountAmount,
                'product_delivery_fee' => 0,
                'created_at' => time()
            ]);

            $discount = isset($discount) ? $discount : null;

            OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'reserve_meeting_id' => $reserveMeeting->id,
                'discount_id' => $discount ? $discount->id : null,
                'amount' => $hourlyAmount * $hours,
                'total_amount' => ($hourlyAmount * $hours) - $discountAmount,
                'tax' => 0,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'product_delivery_fee' => 0,
                'discount' => $discountAmount,
                'created_at' => time()
            ]);

            return response()->json([
                'status' => 'success',
                'orderid' => $order,
                'amount' => ($hourlyAmount * $hours) - $discountAmount
            ]);
            }

            return $this->handleFreeMeetingReservation($user, $meeting, $meetingTime, $day, $selectedMeetingType, $studentCount);
        } catch (\Exception $e) {
            \Log::error('reserves error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
private function errorss($message)
{
    return response()->json([
        'title' => trans('public.request_failed'),
        'msg' => $message,
        'status' => 'error'
    ]);
}
private function calculateDiscount($meetingTime, $hourlyAmount, $discountId)
{
    $discountAmount = 0;

    if (!empty($meetingTime->meeting->discount)) {
        $discountAmount += ($hourlyAmount * $meetingTime->meeting->discount) / 100;
    }

    if ($discountId) {
        $discount = Discount::where('id', $discountId)
            ->where('source', 'meeting')
            ->where('status', 'active')
            ->first();

        if ($discount && $discount->expired_at > time()) {
            $discountAmount += ($hourlyAmount * $discount->percent) / 100;
        }
    }

    return $discountAmount;
}
private function sendWebhookData($user, $orderId)
{
    $orderItem = OrderItem::with('reserveMeeting.meeting')->where('order_id', $orderId)->first();

    if (!$orderItem || !$orderItem->reserveMeeting || !$orderItem->reserveMeeting->meeting) {
        return;
    }

    $creator = User::find($orderItem->reserveMeeting->meeting->creator_id);

    $webhookUrl = 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/8fcecb81-cd75-406e-990e-e1a348edfa17';

    $payload = [
        'student_name'      => $user->full_name,
        'student_mobile'    => $user->mobile,
        'student_email'     => $user->email,
        'consultant_id'     => $creator->id,
        'consultant_name'   => $creator->full_name,
        'consultant_mobile' => $creator->mobile,
        'consultant_email'  => $creator->email,
        'meeting_start_at'  => date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at),
        'meeting_end_at'    => date('m/d/Y H:i:s', $orderItem->reserveMeeting->end_at),
        'paying_amount'     => $orderItem->reserveMeeting->paid_amount,
        'status'            => 'paying',
        'create_at'         => date('Y/m/d H:i')
    ];

    try {
        Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post($webhookUrl, $payload);
    } catch (\Exception $e) {
        \Log::error('Webhook failed: ' . $e->getMessage());
    }
}

   public function reserve15copy(Request $request)
   {
        try {
            $user = apiAuth();
            $name = $user->full_name;
            $email = $user->email;
            $contact = $user->mobile;

            $timeId = $request->input('time');
            $day = $request->input('day');
            $studentCount = $request->get('student_count', 1);
            $selectedMeetingType = in_array($request->get('meeting_type'), ['in_person', 'online']) ? $request->get('meeting_type') : 'online';
            $description = $request->get('description');
            $meeting_discount_id = $request->get('meeting_discount_id');

            $fields = explode(',', $timeId);
            if (count($fields) == 2) {
            $timeId = intval($fields[0]);
            $slot_id = intval($fields[1]);
            }

            if (!empty($timeId)) {
            $meetingTime = MeetingTime::with('meeting')->find($timeId);

            if (!empty($meetingTime)) {
                $meeting = $meetingTime->meeting;

                if ($meeting->creator_id == $user->id) {
                    return response()->json([
                        'title' => trans('public.request_failed'),
                        'msg' => trans('update.cant_reserve_your_appointment'),
                        'status' => 'error'
                    ]);
                }

                if (!empty($meeting) && !$meeting->disabled) {
                    if (!empty($meeting->amount) && $meeting->amount > 0) {

                        $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                            ->where('day', $day)
                            ->first();

                        if (!empty($reserveMeeting) && $reserveMeeting->locked_at) {
                            return response()->json([
                                'title' => trans('public.request_failed'),
                                'msg' => trans('meeting.locked_time'),
                                'status' => 'error'
                            ]);
                        }

                        if (!empty($reserveMeeting) && $reserveMeeting->reserved_at && empty($reserveMeeting->slotid)) {
                            return response()->json([
                                'title' => trans('public.request_failed'),
                                'msg' => trans('meeting.reserved_time'),
                                'status' => 'error'
                            ]);
                        }

                        $hourlyAmountResult = $this->handleHourlyMeetingAmount($meeting, $meetingTime, $studentCount, $selectedMeetingType);

                        if (!$hourlyAmountResult['status']) {
                            return $hourlyAmountResult['result'];
                        }

                        $hourlyAmount = $hourlyAmountResult['result'];
                        $discountAmount = 0;
                        $discount_id = $meeting_discount_id ?? 0;
                        date_default_timezone_set('Asia/Kolkata');

                        if (isset($meetingTime->meeting->discount)) {
                            $discountAmount = ($hourlyAmount * $meetingTime->meeting->discount) / 100;
                        }

                        if ($discount_id != 0) {
                            $discount = Discount::where('id', $discount_id)->where('source', 'meeting')->where('status', 'active')->first();
                            if ($discount && $discount->expired_at > time()) {
                                $discountCouponAmount = ($hourlyAmount * $discount->percent) / 100;
                                $discountAmount += $discountCouponAmount;
                            }
                        }

                        $explodetime = explode('-', $meetingTime->time);
                        $hours = (strtotime($explodetime[1]) - strtotime($explodetime[0])) / 1800;
                        $instructorTimezone = $meeting->getTimezone();

                        $startAt = $this->handleUtcDate($day, $explodetime[0], $instructorTimezone);
                        $endAt = $this->handleUtcDate($day, $explodetime[1], $instructorTimezone);

                        $reserveMeeting = ReserveMeeting::updateOrCreate([
                            'user_id' => $user->id,
                            'meeting_time_id' => $meetingTime->id,
                            'meeting_id' => $meetingTime->meeting_id,
                            'status' => ReserveMeeting::$pending,
                            'day' => $day,
                            'meeting_type' => $selectedMeetingType,
                            'student_count' => $studentCount,
                            'slotid' => $slot_id
                        ], [
                            'date' => strtotime($day),
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'paid_amount' => ($hourlyAmount > 0) ? (($hourlyAmount * $hours) - $discountAmount) / 2 : 0,
                            'discount' => $meetingTime->meeting->discount,
                            'description' => $description,
                            'created_at' => time(),
                        ]);

                        $order = Order::create([
                            'user_id' => $user->id,
                            'status' => Order::$paying,
                            'amount' => ($hourlyAmount > 0) ? ($hourlyAmount * $hours) / 2 : 0,
                            'tax' => 0,
                            'total_discount' => $discountAmount / 2,
                            'total_amount' => ($hourlyAmount > 0) ? (($hourlyAmount * $hours) - $discountAmount) / 2 : 0,
                            'product_delivery_fee' => 0,
                            'created_at' => time(),
                        ]);

                        $orderitem = OrderItem::create([
                            'user_id' => $user->id,
                            'order_id' => $order->id,
                            'reserve_meeting_id' => $reserveMeeting->id ?? null,
                            'discount_id' => $discount_id != 0 ? $discount_id : null,
                            'amount' => ($hourlyAmount > 0) ? $hourlyAmount * $hours / 2 : 0,
                            'total_amount' => ($hourlyAmount > 0) ? (($hourlyAmount * $hours) - $discountAmount) / 2 : 0,
                            'tax' => 0,
                            'tax_price' => 0,
                            'commission' => 0,
                            'commission_price' => 0,
                            'product_delivery_fee' => 0,
                            'discount' => $discountAmount / 2,
                            'created_at' => time(),
                        ]);

                        $orderItem = OrderItem::where('order_id', $order->id)->first();
                        $creater = User::find($orderItem->reserveMeeting->meeting->creator_id);

                        $webhookdata = [
                            'student_name' => $name,
                            'student_mobile' => $contact,
                            'student_email' => $email,
                            'consultant_id' => $creater->id,
                            'consultant_name' => $creater->full_name,
                            'consultant_mobile' => $creater->mobile,
                            'consultant_email' => $creater->email,
                            'meeting_start_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at),
                            'meeting_end_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->end_at),
                            'paying_amount' => $orderItem->reserveMeeting->paid_amount,
                            'status' => 'paying',
                            'create_at' => date("Y/m/d H:i")
                        ];

                        return response()->json([
                            'status' => 'success',
                            'orderid' => $order->id,
                            'amount' => ($hourlyAmount > 0) ? (($hourlyAmount * $hours) - $discountAmount) / 2 : 0,
                        ]);

                    } else {
                        return $this->handleFreeMeetingReservation($user, $meeting, $meetingTime, $day, $selectedMeetingType, $studentCount);
                    }
                } else {
                    return response()->json([
                        'title' => trans('public.request_failed'),
                        'msg' => trans('meeting.meeting_disabled'),
                        'status' => 'error'
                    ]);
                }
            }
            }

            return response()->json([
            'title' => trans('public.request_failed'),
            'msg' => trans('meeting.select_time_to_reserve'),
            'status' => 'error'
            ]);
        } catch (\Exception $e) {
            \Log::error('reserve15copy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function reserve15(Request $request)
    {
        try {
            $user = apiAuth();

            $name = $user->full_name;
            $email = $user->email;
            $contact = $user->mobile;

            $timeIdInput = $request->input('time');
            $day = $request->input('day');
            $studentCount = $request->input('student_count', 1);
            $meetingType = in_array($request->input('meeting_type'), ['in_person', 'online'])
                ? $request->input('meeting_type')
                : 'online';

            $description = $request->input('description');
            $meetingDiscountId = $request->input('meeting_discount_id');

            $slotId = null;
            $timeFields = explode(',', $timeIdInput);
            if (count($timeFields) === 2) {
                $timeId = (int) $timeFields[0];
                $slotId = (int) $timeFields[1];
            } else {
                $timeId = (int) $timeIdInput;
            }

            if (empty($timeId)) {
                return $this->errorResponse(trans('meeting.select_time_to_reserve'));
            }

            $meetingTime = MeetingTime::with('meeting')->find($timeId);
            if (!$meetingTime || !$meetingTime->meeting || $meetingTime->meeting->disabled) {
                return $this->errorResponse(trans('meeting.meeting_disabled'));
            }

            $meeting = $meetingTime->meeting;

            if ($meeting->creator_id == $user->id) {
                return $this->errorResponse(trans('update.cant_reserve_your_appointment'));
            }

            $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                ->where('day', $day)
                ->first();

            if (!empty($reserveMeeting)) {
                if ($reserveMeeting->locked_at) {
                    return $this->errorResponse(trans('meeting.locked_time'));
                }

                if ($reserveMeeting->reserved_at && empty($reserveMeeting->slotid)) {
                    return $this->errorResponse(trans('meeting.reserved_time'));
                }
            }

            $amountResult = $this->handleHourlyMeetingAmount($meeting, $meetingTime, $studentCount, $meetingType);
            if (!$amountResult['status']) {
                return $amountResult['result'];
            }

            $hourlyRate = $amountResult['result'];
            $discountAmount = 0;

            if (!empty($meeting->discount)) {
                $discountAmount = ($hourlyRate * $meeting->discount) / 100;
            }

            $discountId = null;
            if (!empty($meetingDiscountId)) {
                $discount = Discount::where('id', $meetingDiscountId)
                    ->where('source', 'meeting')
                    ->where('status', 'active')
                    ->where('expired_at', '>', time())
                    ->first();

                if ($discount) {
                    $discountAmount += ($hourlyRate * $discount->percent) / 100;
                    $discountId = $discount->id;
                }
            }

            $timeRange = explode('-', $meetingTime->time);
            $hours = (strtotime($timeRange[1]) - strtotime($timeRange[0])) / 1800;

            $startAt = $this->handleUtcDate($day, $timeRange[0], $meeting->getTimezone());
            $endAt = $this->handleUtcDate($day, $timeRange[1], $meeting->getTimezone());

            $finalAmount = ($hourlyRate * $hours - $discountAmount) / 2;

            $reserveMeeting = ReserveMeeting::updateOrCreate([
                'user_id' => $user->id,
                'meeting_time_id' => $meetingTime->id,
                'meeting_id' => $meeting->id,
                'status' => ReserveMeeting::$pending,
                'day' => $day,
                'meeting_type' => $meetingType,
                'student_count' => $studentCount,
                'slotid' => $slotId
            ], [
                'date' => strtotime($day),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'paid_amount' => $finalAmount,
                'discount' => $meeting->discount,
                'description' => $description,
                'created_at' => time()
            ]);

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::$paying,
                'amount' => $hourlyRate * $hours / 2,
                'tax' => 0,
                'total_discount' => $discountAmount / 2,
                'total_amount' => $finalAmount,
                'product_delivery_fee' => 0,
                'created_at' => time()
            ]);

            $orderItem = OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'reserve_meeting_id' => $reserveMeeting->id,
                'discount_id' => $discountId,
                'amount' => $hourlyRate * $hours / 2,
                'total_amount' => $finalAmount,
                'tax' => 0,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'product_delivery_fee' => 0,
                'discount' => $discountAmount / 2,
                'created_at' => time()
            ]);

            $creator = User::find($meeting->creator_id);

            $webhookPayload = [
                'student_name' => $name,
                'student_mobile' => $contact,
                'student_email' => $email,
                'consultant_id' => $creator->id,
                'consultant_name' => $creator->full_name,
                'consultant_mobile' => $creator->mobile,
                'consultant_email' => $creator->email,
                'meeting_start_at' => date('m/d/Y H:i:s', $reserveMeeting->start_at),
                'meeting_end_at' => date('m/d/Y H:i:s', $reserveMeeting->end_at),
                'paying_amount' => $reserveMeeting->paid_amount,
                'status' => 'paying',
                'create_at' => date('Y/m/d H:i')
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Meeting reserved and order created successfully',
                'order_id' => $order,
                'amount' => $finalAmount,
            ]);
        } catch (\Exception $e) {
            \Log::error('reserve15 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function consultationpayment(Request $request)
    {
        try {
            $user = apiAuth();
            $data1=$request->all();

            $gateway  =$data1['gateway'];

             $order=Order::where('id', $data1['order_id'])
                ->first();

             if (!empty($order)) {

                 if ($order->total_amount > $data1['total']) {
                 return apiResponse2(0, 'failed', 'Order amount and paid amount is not same');

                }

                 $order->update(['payment_method' => 'payment_channel']);

                $orderItem=OrderItem::where('order_id', $data1['order_id'])->first();

                if ($gateway === 'credit') {

                    if ($user->getAccountingCharge() < $order->total_amount) {
                        $order->update(['status' => Order::$fail]);

                         return apiResponse2(0, 'failed', 'insufficient wallet amount');
                    }
                }else{

                    $paymentChannel = PaymentChannel::where('class_name', $gateway)
                ->where('status', 'active')
                ->first();

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verifyApi($request);

            if (!$order) {
            return response()->json([
                'error' => 'An internal error occurred. Please try again later.',
                'details' => 'This payment has already been captured'
            ], 400);
            }

                }
               if ($order && $order->status == Order::$paying) {

                    $sale = Sale::createSales($orderItem, $order->payment_method);

                    if (!empty($orderItem->reserve_meeting_id)) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                        $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();

                        $reserveMeeting->update([
                            'sale_id' => $sale->id,
                            'reserved_at' => time()
                        ]);

                    $order->update(['status' => Order::$paid]);

                    Accounting::createAccounting($orderItem, $gateway=='credit'?$gateway:null);
                    }

                }else{
                return apiResponse2(0, 'failed', 'status is not paying');
            }

            $createrzoom = UserZoomLink::where('user_id', $orderItem->reserveMeeting->meeting->creator_id)->first();

                               date_default_timezone_set('Asia/Kolkata');

            $mail_befor_1_hour=-1;
            $mail_befor_1_day=-1;
            if(!empty($createrzoom)){
            $start = strtotime(date("m/d/Y h:i"));
            $stop = strtotime(date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at));
            $diff = ($stop - $start);
            $mail_befor_1_day = ($diff/60)-1440;
            $mail_befor_1_hour = ($diff/60)-60;

            }

            $value = $user->mobile;
            $value1 = $value;

            $mobileregex = "/^[0-9]{10}$/";
            if(preg_match($mobileregex, $value)===0){
            $value1= preg_replace('/[^0-9]/', '', $value);
            $len = strlen($value1);

                if($len==13) {
            preg_match( '/^(\d{3})(\d{10})$/', $value1,  $matches );
            $result =$matches[2];
            $value1= $result;
            }

            if($len==12) {
            preg_match( '/^(\d{2})(\d{10})$/', $value1,  $matches );
            $result =$matches[2];
            $value1= $result;
            }
            if($len==11) {
            preg_match( '/^(\d{1})(\d{10})$/', $value1,  $matches );
            $result =$matches[2];
            $value1= $result;
            }

            }

            $value2 = $creater->mobile;
            $value23 = $creater->mobile;
            $mobileregex = "/^[0-9]{10}$/";
            if(preg_match($mobileregex, $value2)===0){
            $value21= preg_replace('/[^0-9]/', '', $value2);
            $len = strlen($value21);

            if($len==13) {
            preg_match( '/^(\d{3})(\d{10})$/', $value21,  $matches );
            $result =$matches[2];
            $value23= $result;
            }

            if($len==12) {
            preg_match( '/^(\d{2})(\d{10})$/', $value21,  $matches );
            $result =$matches[2];
            $value23= $result;
            }
            if($len==11) {
            preg_match( '/^(\d{1})(\d{10})$/', $value21,  $matches );
            $result =$matches[2];
            $value23= $result;
            }

            }

            $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/6bcc434d-8597-4cce-ae5d-8110cf5dbff7';

            $webhookdata = [
            'student_id' => $orderItem->user_id,
            'student_name' => $user->full_name,
            'student_mobile' => '91'.$value1,
            'student_email' => $user->email,
            'consultant_id' => $creater->id,
            'consultant_name' => $creater->full_name,
            'consultant_mobile' => $value23,
            'consultant_email' => $creater->email,
            'consultant_zoom_user' => !empty($createrzoom)?$createrzoom->gmail:'null',
            'consultant_zoom_pwd' => !empty($createrzoom)?$createrzoom->zoom_pwd:'null',
            'birth_date' => $data1['birthdate'],
            'birth_time' => $data1['birthtime'],
            'birth_place' => $data1['birthplace'],
            'meeting_start_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->start_at),
            'meeting_end_at' => date('m/d/Y H:i:s', $orderItem->reserveMeeting->end_at),
            'meeting_link' => !empty($createrzoom)?$createrzoom->zoom_link:'null',
            'mail_befor_1_hour' => $mail_befor_1_hour>0?$mail_befor_1_hour:'null',
            'mail_befor_1_day' => $mail_befor_1_day>0?$mail_befor_1_day:'null',
            'paid_amount' => $orderItem->reserveMeeting->paid_amount,
            'status' => 'paid',
            'create_at' => date("Y/m/d H:i")

            ];

            return apiResponse2(1, 'success', 'Payment Successfully Paid');

            }else{
                return apiResponse2(0, 'fail', 'No Order with this ID');
            }
        } catch (\Exception $e) {
            \Log::error('consultationpayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function errorResponse($message)
{
    return response()->json([
        'title' => trans('public.request_failed'),
        'message' => $message,
        'status' => 'error'
    ]);
}

    private function handleHourlyMeetingAmount(Meeting $meeting, MeetingTime $meetingTime, $studentCount, $selectedMeetingType)
    {
        if (empty($studentCount)) {
            $studentCount = 1;
        }

        $status = true;
        $hourlyAmount = $meeting->amount;

        if ($selectedMeetingType == 'in_person' and in_array($meetingTime->meeting_type, ['in_person', 'all'])) {
            if ($meeting->in_person) {
                $hourlyAmount = $meeting->in_person_amount;
            } else {
                $toastData = [
                    'status' => 'error',
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.in_person_meetings_unavailable'),
                ];
                $hourlyAmount = response()->json($toastData);
                $status = false;
            }
        }

        if ($meeting->group_meeting and $status) {
            $types = ['in_person', 'online'];

            foreach ($types as $type) {
                if ($selectedMeetingType == $type and in_array($meetingTime->meeting_type, ['all', $type])) {

                    $meetingMaxVar = $type . '_group_max_student';
                    $meetingMinVar = $type . '_group_min_student';
                    $meetingAmountVar = $type . '_group_amount';

                    if ($studentCount < $meeting->$meetingMinVar) {
                        $hourlyAmount = $hourlyAmount * $studentCount;
                    } else if ($studentCount > $meeting->$meetingMaxVar) {
                        $toastData = [
                            'status' => 'error',
                            'title' => trans('public.request_failed'),
                            'msg' => trans('update.group_meeting_max_student_count_hint', ['max' => $meeting->$meetingMaxVar]),
                        ];
                        $hourlyAmount = response()->json($toastData);
                        $status = false;
                    } else if ($studentCount >= $meeting->$meetingMinVar and $studentCount <= $meeting->$meetingMaxVar) {
                        $hourlyAmount = $meeting->$meetingAmountVar * $studentCount;
                    }
                }
            }
        }

        return [
            'status' => $status,
            'result' => $hourlyAmount
        ];
    }

    private function handleFreeMeetingReservation($user, $meeting, $meetingTime, $day, $selectedMeetingType, $studentCount)
    {
        $instructorTimezone = $meeting->getTimezone();
        $explodetime = explode('-', $meetingTime->time);

        $startAt = $this->handleUtcDate($day, $explodetime[0], $instructorTimezone);
        $endAt = $this->handleUtcDate($day, $explodetime[1], $instructorTimezone);

        $reserve = ReserveMeeting::updateOrCreate([
            'user_id' => $user->id,
            'meeting_time_id' => $meetingTime->id,
            'meeting_id' => $meetingTime->meeting_id,
            'status' => ReserveMeeting::$pending,
            'day' => $day,
            'meeting_type' => $selectedMeetingType,
            'student_count' => $studentCount
        ], [
            'date' => strtotime($day),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'paid_amount' => 0,
            'discount' => $meetingTime->meeting->discount,
            'created_at' => time(),
        ]);

        if (!empty($reserve)) {
            $sale = Sale::create([
                'buyer_id' => $user->id,
                'seller_id' => $meeting->creator_id,
                'meeting_id' => $meeting->id,
                'type' => Sale::$meeting,
                'payment_method' => Sale::$credit,
                'amount' => 0,
                'total_amount' => 0,
                'created_at' => time(),
            ]);

            if (!empty($sale)) {
                $reserve->update([
                    'sale_id' => $sale->id,
                    'reserved_at' => time()
                ]);
            }
        }

        $toastData = [
            'title' => '',
            'msg' => trans('cart.success_pay_msg_for_free_meeting'),
            'status' => 'success'
        ];
        return response()->json($toastData);
    }
    private function handleUtcDate($day, $clock, $instructorTimezone)
    {
        $date = $day . ' ' . $clock;

        $utcDate = convertTimeToUTCzone($date, $instructorTimezone);

        return $utcDate->getTimestamp();
    }
}
