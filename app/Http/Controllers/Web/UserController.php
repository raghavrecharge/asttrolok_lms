<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Category;
use App\Models\ForumTopic;
use App\Models\Newsletter;
use App\Models\Product;
use App\Models\ReserveMeeting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\UserOccupation;
use App\Models\Webinar;
use App\User;
use App\Models\Role;
use App\Models\Follow;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class UserController extends Controller
{
    private $cacheDuration = 60;
    private $cacheKeyPrefix = 'instructors_page';
    public function profile($id)
    {
        try {
            $cacheKey = 'user_profile_' . $id;

            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.user.profile', $cachedData);
                }else{
                    return view('web.default2' . '.user.profile', $cachedData);
                }
            }
            $user = User::where('id', $id)

                ->with([
                    'blog' => function ($query) {
                        $query->where('status', 'publish');
                        $query->withCount([
                            'comments' => function ($query) {
                                $query->where('status', 'active');
                            }
                        ]);
                    },
                    'products' => function ($query) {
                        $query->where('status', Product::$active);
                    },
                    'userMetas',
                    'consultationSeos'
                ])
                ->first();

            if (!$user) {
                abort(404);
            }

            $userMetas = $user->userMetas;

            if (!empty($userMetas)) {
                foreach ($userMetas as $meta) {
                    $user->{$meta->name} = $meta->value;
                }
            }

            $userBadges = $user->getBadges();

            $meeting = Meeting::where('creator_id', $user->id)
                ->with([
                    'meetingTimes'
                ])
                ->first();

            $times = [];
            $installments = null;
            $cashbackRules = null;

            if (!empty($meeting) and !empty($meeting->meetingTimes)) {
                $times = convertDayToNumber($meeting->meetingTimes->where('disabled',0)->groupby('day_label')->toArray());

                $authUser = auth()->user();

                if (getFeaturesSettings('cashback_active') and (empty($authUser) or !$authUser->disable_cashback)) {
                    $cashbackRulesMixin = new CashbackRules($authUser);
                    $cashbackRules = $cashbackRulesMixin->getRules('meetings', null, null, null, $user->id);
                }
            }

            $followings = $user->following();
            $followers = $user->followers();

            $authUserIsFollower = false;
            if (auth()->check()) {
                $authUserIsFollower = $followers->where('follower', auth()->id())
                    ->where('status', Follow::$accepted)
                    ->first();
            }

            $userMetas = $user->userMetas;
            $occupations = $user->occupations()
                ->with([
                    'category'
                ])->get();

            $webinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                })
                ->orderBy('updated_at', 'desc')
                ->with(['teacher' => function ($qu) {
                    $qu->select('id', 'full_name', 'avatar');
                }, 'reviews', 'tickets', 'feature'])
                ->get();

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
            $appointments = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->whereNotNull('reserved_at')
                ->where('status', '!=', ReserveMeeting::$canceled)
                ->count();

            $studentsIds = Sale::whereNull('refund_at')
                ->where('seller_id', $user->id)
                ->whereNotNull('webinar_id')
                ->pluck('buyer_id')
                ->toArray();
            $user->students_count = count(array_unique($studentsIds));

            $instructors = null;
            if ($user->isOrganization()) {
                $instructors = User::where('organ_id', $user->id)
                    ->where('role_name', Role::$teacher)
                    ->where('status', 'active')
                    ->get();
            }

            $pageTitle = optional($user->consultationSeos->first())->title ?? $user->full_name . ' ' . trans('public.profile');
            $pagedescription = optional($user->consultationSeos->first())->description ?? '';
            $pageh1 = optional($user->consultationSeos->first())->h1 ?? $user->full_name;
            $pagekeyword = optional($user->consultationSeos->first())->keyword ?? '';

            $data = [
                'pageTitle' => $pageTitle,
                'pageDescription' => $pagedescription,
                'pageh1' => $pageh1,
                'pagekeyword' => $pagekeyword,
                'user' => $user,
                'userBadges' => $userBadges,
                'meeting' => $meeting,
                'times' => $times,
                'userRates' => $user->rates(),
                'userFollowers' => $followers,
                'userFollowing' => $followings,
                'authUserIsFollower' => $authUserIsFollower,
                'educations' => $userMetas->where('name', 'education'),
                'experiences' => $userMetas->where('name', 'experience'),
                'occupations' => $occupations,
                'webinars' => $webinars,
                'appointments' => $appointments,
                'meetingTimezone' => $meeting ? $meeting->getTimezone() : null,
                'instructors' => $instructors,
                'consult' => $instructors,
                'forumTopics' => $this->getUserForumTopics($user->id),
                'cashbackRules' => $cashbackRules,
            ];
             Cache::put($cacheKey, $data, now()->addMinutes($this->cacheDuration));
            $agent = new Agent();
            if ($agent->isMobile()){
                return view(getTemplate() . '.user.profile', $data);
            }else{
                return view('web.default2' . '.user.profile', $data);
            }
        } catch (\Exception $e) {
            \Log::error('profile error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getUserForumTopics($userId)
    {
        $forumTopics = null;

        if (!empty(getFeaturesSettings('forums_status')) and getFeaturesSettings('forums_status')) {
            $forumTopics = ForumTopic::where('creator_id', $userId)
                ->orderBy('pin', 'desc')
                ->orderBy('created_at', 'desc')
                ->withCount([
                    'posts'
                ])
                ->get();

            foreach ($forumTopics as $topic) {
                $topic->lastPost = $topic->posts()->orderBy('created_at', 'desc')->first();
            }
        }

        return $forumTopics;
    }

    public function followToggle($id)
    {
        try {
            $authUser = auth()->user();
            $user = User::where('id', $id)->first();

            $followStatus = false;
            $follow = Follow::where('follower', $authUser->id)
                ->where('user_id', $user->id)
                ->first();

            if (empty($follow)) {
                Follow::create([
                    'follower' => $authUser->id,
                    'user_id' => $user->id,
                    'status' => Follow::$accepted,
                ]);

                $followStatus = true;
            } else {
                $follow->delete();
            }

            return response()->json([
                'code' => 200,
                'follow' => $followStatus
            ], 200);
        } catch (\Exception $e) {
            \Log::error('followToggle error: ' . $e->getMessage(), [
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

            return response()->json([
                'times' => $resultMeetingTimes
            ], 200);
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

                $meetingTimes = $meeting->meetingTimes()->where('disabled',0)->where('day_label', $dayLabel)->get();

                if (!empty($meetingTimes) and count($meetingTimes)) {

                    foreach ($meetingTimes as $meetingTime) {
                        $can_reserve = true;

                        $reserveMeeting1 = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                            ->where('day', $date)
                            ->whereIn('status', ['pending', 'open'])
                            ->WhereNotNull('reserved_at')
                            ->get();
                        $count = count($reserveMeeting1);

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
                        for($i=1;$i<=2;$i++){

                        date_default_timezone_set("Asia/Kolkata");
                        if(strtotime(date("Y-m-d")) == strtotime($date)){
                        if(strtotime(date("h:i:sa"))>=strtotime($vvv[0])){

                        $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "slotid" => $i,
                            "time" => $i==1 ? $vvv[0] : date("h:iA",strtotime("+15 minutes", strtotime($vvv[0]))),
                            "description" => $meetingTime->description,
                            "can_reserve" => false,
                            'meeting_type' => $meetingTime->meeting_type
                        ];
                        }else{
                           $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "slotid" => $i,
                            "time" => $i==1 ? $vvv[0] : date("h:iA",strtotime("+15 minutes", strtotime($vvv[0]))),
                            "description" => $meetingTime->description,
                            "can_reserve" => $can_reserve ? $can_reserve : ($reserveMeeting->slotid == null || $count == 2 ? false :($reserveMeeting->slotid == $i  ? $can_reserve : true)),
                            'meeting_type' => $meetingTime->meeting_type
                        ];
                        }
                        }else{
                            $resultMeetingTimes[] = [
                            "id" => $meetingTime->id,
                            "slotid" => $i,
                            "time" => $i==1 ? $vvv[0] : date("h:iA",strtotime("+15 minutes", strtotime($vvv[0]))),
                            "description" => $meetingTime->description,
                            "can_reserve" => $can_reserve ? $can_reserve : ($reserveMeeting->slotid == null || $count == 2 ? false : ($reserveMeeting->slotid == $i  ? $can_reserve : true)),
                            'meeting_type' => $meetingTime->meeting_type
                        ];
                        }

                        }
                    }
                }
            }

            return response()->json([
                'times' => $resultMeetingTimes
            ], 200);
        } catch (\Exception $e) {
            \Log::error('availableTimes1 error: ' . $e->getMessage(), [
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
            $agent = new Agent();
            $cachedData = Cache::get($this->cacheKeyPrefix);

            if ($cachedData) {
               if ($agent->isMobile()){
                      return view(getTemplate() . '.pages.instructors', $cachedData);
                }else{
                      return view('web.default2' . '.pages.instructors', $cachedData);
                }
            }
            $seoSettings = getSeoMetas('instructors');
            $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('home.instructors');
            $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('home.instructors');
            $pageRobot = getPageRobot('instructors');

            $data = $this->handleInstructorsOrOrganizationsPage($request, Role::$teacher);
            $data['title'] = trans('home.instructors');
            $data['page'] = 'consult-with-astrologers';
            $data['pageTitle'] = $pageTitle;
            $data['pageDescription'] = $pageDescription;
            $data['pageRobot'] = $pageRobot;

            if ($agent->isMobile()){
                return view(getTemplate() . '.pages.instructors', $data);
            }else{
                return view('web.default2' . '.pages.instructors', $data);
            }
        } catch (\Exception $e) {
            \Log::error('instructors error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleInstructorsOrOrganizationsPage(Request $request, $role)
    {
        try {
            $query = User::where('role_name', $role)

                ->where('users.status', 'active')
                ->where(function ($query) {
                    $query->where('users.ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('users.ban_end_at')
                                ->orWhere('users.ban_end_at', '<', time());
                        });
                })
                ->with(['meeting' => function ($query) {
                    $query->with('meetingTimes');
                    $query->withCount('meetingTimes');
                }]);

            $instructors = $this->filterInstructors($request, deepClone($query), $role)
            ->get();

            if ($request->ajax()) {
                $html = null;

                foreach ($instructors as $instructor) {
                    $html .= '<div class="col-12 col-lg-3">';

                    $agent = new Agent();
                    if ($agent->isMobile()){
                        $html .= (string)view()->make('web.default.pages.instructor_card', ['instructor' => $instructor]);
                    }else{
                        $html .= (string)view()->make('web.default2.pages.instructor_card', ['instructor' => $instructor]);
                    }

                        $html .= '</div>';
                    }

                return response()->json([
                    'html' => $html,
                    'last_page' => $instructors->lastPage(),
                ], 200);
            }

            if (empty($request->get('sort')) or !in_array($request->get('sort'), ['top_rate', 'top_sale'])) {
                $bestRateInstructorsQuery = $this->getBestRateUsers(deepClone($query), $role);

                $bestSalesInstructorsQuery = $this->getTopSalesUsers(deepClone($query), $role);

                $bestRateInstructors = $bestRateInstructorsQuery
                    ->limit(8)
                    ->get();

                $bestSalesInstructors = $bestSalesInstructorsQuery
                    ->limit(8)
                    ->get();
            }

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $data = [
                'pageTitle' => trans('home.instructors'),
                'instructors' => $instructors,
                'consult' => $instructors,
                'instructorsCount' => deepClone($query)->count(),
                'bestRateInstructors' => $bestRateInstructors ?? null,
                'bestSalesInstructors' => $bestSalesInstructors ?? null,
                'categories' => $categories,
            ];

            return $data;
        } catch (\Exception $e) {
            \Log::error('handleInstructorsOrOrganizationsPage error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function filterInstructors($request, $query, $role)
    {
        $categories = $request->get('categories', null);
        $sort = $request->get('sort', null);
        $availableForMeetings = $request->get('available_for_meetings', null);
        $hasFreeMeetings = $request->get('free_meetings', null);
        $withDiscount = $request->get('discount', null);
        $search = $request->get('search', null);

        if (!empty($categories) and is_array($categories)) {
            $userIds = UserOccupation::whereIn('category_id', $categories)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }

        if (!empty($sort) and $sort == 'top_rate') {
            $query = $this->getBestRateUsers($query, $role);
        }

        if (!empty($sort) and $sort == 'top_sale') {
            $query = $this->getTopSalesUsers($query, $role);
        }

        if (!empty($sort) and $sort == 'max_price') {
           $query = $this->getTopPrice($query, $role);
        }

        if (!empty($sort) and $sort == 'min_price') {
           $query = $this->getLowPrice($query, $role);
        }

        if (!empty($availableForMeetings) and $availableForMeetings == 'on') {
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

        if (!empty($hasFreeMeetings) and $hasFreeMeetings == 'on') {
            $freeMeetingsIds = Meeting::where('disabled', 0)
                ->where(function ($query) {
                    $query->whereNull('amount')->orWhere('amount', '0');
                })->groupBy('creator_id')
                ->pluck('creator_id')
                ->toArray();

            $query->whereIn('users.id', $freeMeetingsIds);
        }

        if (!empty($withDiscount) and $withDiscount == 'on') {
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

    private function getTopPrice($query, $role)
    {
        $query->leftJoin('meetings', function ($join) {
            $join->on('users.id', '=', 'meetings.creator_id');
        })
            ->select('users.*', 'meetings.creator_id')
            ->orderBy('meetings.amount', 'desc');

        return $query;
    }

     private function getLowPrice($query, $role)
    {
        $query->leftJoin('meetings', function ($join) {
            $join->on('users.id', '=', 'meetings.creator_id');
        })
            ->select('users.*', 'meetings.creator_id')
            ->orderBy('meetings.amount', 'asc');

        return $query;
    }

    public function makeNewsletter(Request $request)
    {
        try {
            $this->validate($request, [
                'newsletter_email' => 'required|string|email|max:60|unique:newsletters,email'
            ]);

            $data = $request->all();
            $user_id = null;
            $email = $data['newsletter_email'];

            if (auth()->check()) {
                $user = auth()->user();

                if (empty($user->email)) {
                    $user->update([
                        'email' => $email,
                        'newsletter' => true,
                    ]);
                } else if ($user->email == $email) {
                    $user_id = $user->id;

                    $user->update([
                        'newsletter' => true,
                    ]);
                }
            }

            $check = Newsletter::where('email', $data['newsletter_email'])->first();

            if (!empty($check)) {
                if (!empty($check->user_id) and !empty($user_id) and $check->user_id != $user_id) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('update.this_email_used_by_another_user'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                } elseif (empty($check->user_id) and !empty($user_id)) {
                    $check->update([
                        'user_id' => $user_id
                    ]);
                }
            } else {
                Newsletter::create([
                    'user_id' => $user_id,
                    'email' => $data['newsletter_email'],
                    'created_at' => time()
                ]);
            }

            if (!empty($user_id)) {
                $newsletterReward = RewardAccounting::calculateScore(Reward::NEWSLETTERS);
                RewardAccounting::makeRewardAccounting($user_id, $newsletterReward, Reward::NEWSLETTERS, $user_id, true);
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('site.create_newsletter_success'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('makeNewsletter error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function sendMessage(Request $request, $id)
    {
        if (!empty($id)) {
            $user = User::select('id', 'email')
                ->where('id', $id)
                ->first();

            if (!empty($user) and !empty($user->email)) {
                $data = $request->all();

                $validator = Validator::make($data, [
                    'title' => 'required|string',
                    'email' => 'required|email',
                    'description' => 'required|string',
                    'captcha' => 'required|captcha',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'code' => 422,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $mail = [
                    'title' => $data['title'],
                    'message' => trans('site.you_have_message_from', ['email' => $data['email']]) . "\n" . $data['description'],
                ];

                try {

                    return response()->json([
                        'code' => 200
                    ]);
                } catch (Exception $e) {
                    return response()->json([
                        'code' => 500,
                        'message' => trans('site.server_error_try_again')
                    ]);
                }
            }

            return response()->json([
                'code' => 403,
                'message' => trans('site.user_disabled_public_message')
            ]);
        }
    }

    public function getUserId(Request $request)
    {
        try {
            $email = $request->email;
            $name = $request->name;
            $number = $request->phone;

            $user = User::where('email', $email)
                        ->orWhere('mobile', $number)
                        ->first();

            if (!$user) {
                $user = User::create([
                    'role_name' => 'user',
                    'role_id' => 1,
                    'mobile' => $number ?? null,
                    'email' => $email ?? null,
                    'full_name' => $name,
                    'status'=>'active',
                    'access_content' => 1,
                    'password' => Hash::make(123456),
                    'pwd_hint' => 123456,
                    'affiliate' => 0,
                    'timezone' => 'Asia/Kolkata' ?? null,
                    'created_at' => time()
                ]);
            }

            return response()->json(['user_id' => $user->id]);
        } catch (\Exception $e) {
            \Log::error('getUserId error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
