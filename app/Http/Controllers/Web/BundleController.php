<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\AdvertisingBanner;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\User;
use App\Models\Product;
use App\Models\Meeting;
use App\Models\Webinar;
use App\Models\Follow;
use App\Models\ForumTopic;
use App\Models\ReserveMeeting;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class BundleController extends Controller
{
    use InstallmentsTrait;
    use CheckContentLimitationTrait;

    public function index($slug)
    {
        try {
            $user = null;

            if (auth()->check()) {
                $user = auth()->user();
            }

            $bundle = Bundle::where('slug', $slug)
                ->with([
                    'tickets' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'filterOptions',
                    'category',
                    'teacher',
                    'tags',
                    'bundleWebinars' => function ($query) {
                        $query->with([
                            'webinar' => function ($query) {
                                $query->where('status', Webinar::$active);
                            },
                            'product' => function ($query) {
                                $query->where('status', Webinar::$active);
                            }
                        ]);
                    },
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                        $query->with([
                            'comments' => function ($query) {
                                $query->where('status', 'active');
                            },
                            'creator' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar');
                            }
                        ]);
                    },
                    'comments' => function ($query) {
                        $query->where('status', 'active');
                        $query->whereNull('reply_id');
                        $query->with([
                            'user' => function ($query) {
                                $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                            },
                            'replies' => function ($query) {
                                $query->where('status', 'active');
                                $query->with([
                                    'user' => function ($query) {
                                        $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                    }
                                ]);
                            }
                        ]);
                        $query->orderBy('created_at', 'desc');
                    },
                ])
                ->withCount([
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    }
                ])
                ->where('status', 'active')
                ->first();

            if (empty($bundle)) {
                abort(404);
            }

            $installmentLimitation = $this->installmentContentLimitation($user, $bundle->id, 'bundle_id');
            if ($installmentLimitation != "ok") {
                return $installmentLimitation;
            }

            $isFavorite = false;

            if (!empty($user)) {
                $isFavorite = Favorite::where('bundle_id', $bundle->id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            $hasBought = $bundle->checkUserHasBought($user);
            $canSale = ($bundle->canSale() and !$hasBought);

            $advertisingBanners = AdvertisingBanner::where('published', true)
                ->whereIn('position', ['bundle', 'bundle_sidebar'])
                ->get();

            if ($canSale and !empty($bundle->price) and $bundle->price > 0 and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('bundles', $bundle->id, $bundle->type, $bundle->category_id, $bundle->teacher_id);
            }

            if ($canSale and !empty($bundle->price) and getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
                $cashbackRulesMixin = new CashbackRules($user);
                $cashbackRules = $cashbackRulesMixin->getRules('bundles', $bundle->id, $bundle->type, $bundle->category_id, $bundle->teacher_id);
            }

            $pageRobot = getPageRobot('bundle_show');

            $userConsultants = User::where('status', Webinar::$active)
                ->where('role_id', 4)
                ->where('consultant', 1)
                ->whereHas('meeting', function ($q) {
                    $q->where('disabled', 0)
                    ->whereHas('meetingTimes');
                })

                ->get();

            $data = [
                'pageTitle' => $bundle->title,
                'pageDescription' => $bundle->seo_description,
                'pageRobot' => $pageRobot,
                'bundle' => $bundle,
                'isFavorite' => $isFavorite,
                'hasBought' => $hasBought,
                'user' => $user,
                'advertisingBanners' => $advertisingBanners->where('position', 'bundle'),
                'advertisingBannersSidebar' => $advertisingBanners->where('position', 'bundle_sidebar'),
                'activeSpecialOffer' => $bundle->activeSpecialOffer(),
                'cashbackRules' => $cashbackRules ?? null,
                'installments' => $installments ?? null,
                'canSale' => $canSale ?? null,
                'userConsultants' => $userConsultants,
            ];

            $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.bundle.index', $data);
                    }else{
                        return view('web.default2' . '.bundle.index', $data);
                    }
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function favoriteToggle($slug)
    {
        try {
            $userId = auth()->id();
            $bundle = Bundle::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($bundle)) {

                $isFavorite = Favorite::where('bundle_id', $bundle->id)
                    ->where('user_id', $userId)
                    ->first();

                if (empty($isFavorite)) {
                    Favorite::create([
                        'user_id' => $userId,
                        'bundle_id' => $bundle->id,
                        'created_at' => time()
                    ]);
                } else {
                    $isFavorite->delete();
                }
            }

            return response()->json([], 200);
        } catch (\Exception $e) {
            \Log::error('favoriteToggle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function buyWithPoint($slug)
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();

                $bundle = Bundle::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();

                if (!empty($bundle)) {
                    if (empty($bundle->points)) {
                        $toastData = [
                            'title' => '',
                            'msg' => trans('update.can_not_buy_this_bundle_with_point'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $availablePoints = $user->getRewardPoints();

                    if ($availablePoints < $bundle->points) {
                        $toastData = [
                            'title' => '',
                            'msg' => trans('update.you_have_no_enough_points_for_this_bundle'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $checkCourseForSale = checkCourseForSale($bundle, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    Sale::create([
                        'buyer_id' => $user->id,
                        'seller_id' => $bundle->creator_id,
                        'bundle_id' => $bundle->id,
                        'type' => Sale::$bundle,
                        'payment_method' => Sale::$credit,
                        'amount' => 0,
                        'total_amount' => 0,
                        'created_at' => time(),
                    ]);

                    RewardAccounting::makeRewardAccounting($user->id, $bundle->points, 'withdraw', null, false, RewardAccounting::DEDUCTION);

                    $toastData = [
                        'title' => '',
                        'msg' => trans('update.success_pay_bundle_with_point_msg'),
                        'status' => 'success'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                abort(404);
            } else {
                return redirect('/login');
            }
        } catch (\Exception $e) {
            \Log::error('buyWithPoint error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function free(Request $request, $slug)
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();

                $bundle = Bundle::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();

                if (!empty($bundle)) {
                    $checkCourseForSale = checkCourseForSale($bundle, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    if (!empty($bundle->price) and $bundle->price > 0) {
                        $toastData = [
                            'title' => trans('cart.fail_purchase'),
                            'msg' => trans('update.bundle_not_free'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    Sale::create([
                        'buyer_id' => $user->id,
                        'seller_id' => $bundle->creator_id,
                        'bundle_id' => $bundle->id,
                        'type' => Sale::$bundle,
                        'payment_method' => Sale::$credit,
                        'amount' => 0,
                        'total_amount' => 0,
                        'created_at' => time(),
                    ]);

                    $toastData = [
                        'title' => '',
                        'msg' => trans('cart.success_pay_msg_for_free_course'),
                        'status' => 'success'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                abort(404);
            } else {
                return redirect('/login');
            }
        } catch (\Exception $e) {
            \Log::error('free error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function directPayment(Request $request)
    {
        try {
            $user = auth()->user();

            if (!empty($user) and !empty(getFeaturesSettings('direct_bundles_payment_button_status'))) {
                $this->validate($request, [
                    'item_id' => 'required',
                    'item_name' => 'nullable',
                ]);

                $data = $request->except('_token');

                $bundleId = $data['item_id'];
                $ticketId = $data['ticket_id'] ?? null;

                $bundle = Bundle::where('id', $bundleId)
                    ->where('status', 'active')
                    ->first();

                if (!empty($bundle)) {
                    $checkCourseForSale = checkCourseForSale($bundle, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    $fakeCarts = collect();

                    $fakeCart = new Cart();
                    $fakeCart->creator_id = $user->id;
                    $fakeCart->bundle_id = $bundle->id;
                    $fakeCart->ticket_id = $ticketId;
                    $fakeCart->special_offer_id = null;
                    $fakeCart->created_at = time();

                    $fakeCarts->add($fakeCart);

                    $cartController = new CartController();

                    return $cartController->checkout(new Request(), $fakeCarts);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('directPayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function showConsultants(Request $request,$slug, $type)
    {
        try {
            $bundle = Bundle::where('slug', $slug)
                        ->with('bundleWebinars')
                        ->firstOrFail();
            $bundleId = $request->query('bundle_id');
            $bundle_webinar_id = $request->query('bundle_webinar_id');
            $consultationWithConsultants = [];

            foreach ($bundle->bundleWebinars as $bw) {

            if ($bw->consultation_type !== $type) {
                continue;
            }

            $slotTime = $bw->slot_time ?? 15;
            $consultants = collect();

            if ($bw->consultation_type === 'specific' && $bw->consultant_id) {
                $user = User::where('id', $bw->consultant_id)
                            ->where('status', 'active')
                            ->first();

                if ($user) {
                    $consultants->push($user);
                }

            }  elseif ($bw->consultation_type === 'range' && $bw->starting_price && $bw->ending_price) {
                    $slotTime2 =  $slotTime=='both' ? 30 : $slotTime ;

                $maxAmount = $bw->ending_price * (30 / $slotTime2);

                $possibleUsers = User::where('status', 'active')
                    ->where('consultant', 1)
                    ->whereHas('meeting', function ($query) use ($bw, $maxAmount) {
                        $query->whereBetween('amount', [$bw->starting_price, $maxAmount]);
                    })
                    ->with('meeting')
                    ->get();

                $usersInRange = $possibleUsers->filter(function ($user) use ($bw, $slotTime2) {
                    $meeting = $user->meeting;
                    if (empty($meeting) || empty($meeting->amount)) {
                        return false;
                    }

                    $perMinuteRate = $meeting->amount / 30;
                    $priceForSlot = $perMinuteRate * $slotTime2;

                    return ($priceForSlot >= $bw->starting_price && $priceForSlot <= $bw->ending_price);
                });

                $consultants = $consultants->merge($usersInRange);

            } elseif ($bw->consultation_type === 'all') {
                $allUsers = User::where('status', 'active')
                    ->where('consultant', 1)
                    ->get();

                $consultants = $consultants->merge($allUsers);
            }

            $consultationWithConsultants[] = [
                'consultation' => $bw,
                'consultants' => $consultants->unique('id'),
                'slot_time' => $slotTime,
            ];
            }
            $time = $request->query('time');
            $agent = new Agent();
            if ($agent->isMobile()) {
            return view('web.default.bundle.consultants', [
            'bundle' => $bundle,
            'bundle_id' => $bundle->id,
            'bundle_webinar_id' => $bundle_webinar_id,
            'consultations' => $consultationWithConsultants,
            'time' => $time,
            'title' => 'Consultants',
            ]);
            } else {
            return view('web.default2.bundle.consultants', [
            'bundle' => $bundle,
            'bundle_id' => $bundle->id,
            'bundle_webinar_id' => $bundle_webinar_id,
            'consultations' => $consultationWithConsultants,
            'time' => $time,
            'title' => 'Consultants',
            ]);
            }
        } catch (\Exception $e) {
            \Log::error('showConsultants error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 public function profile($id, $name, Request $request)
    {
        try {
            $bundleId = $request->query('bundle_id');
            $bundleWebinarId = $request->query('bundle_webinar_id');
              $time = $request->query('time');

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
                'userMetas'
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

            $data = [
            'pageTitle' => $user->full_name . ' ' . trans('public.profile'),
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
            'selectedTime' => $time,
            'bundle_id' => $bundleId,
            'bundle_webinar_id' => $bundleWebinarId,
            ];

            $agent = new Agent();
            if ($agent->isMobile()) {
            return view(getTemplate() . '.bundle.booking_profile', $data);
            } else {
            return view('web.default2.bundle.booking_profile', $data);
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
        public function book(Request $request)
{
        try {
            $bundleId = $request->input('bundle_id');
            $bundleWebinarId = $request->input('bundle_webinar_id');

            $allInput = $request->all();

            dd($bundleId, $bundleWebinarId, $allInput);
        } catch (\Exception $e) {
            \Log::error('book error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
