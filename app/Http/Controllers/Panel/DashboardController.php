<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Controllers\Controller;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Mixins\Installment\InstallmentAccounting;
use App\Models\Cart;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use App\Models\Comment;
use App\Models\Gift;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\MeetingTime;
use App\Models\Translation\SessionTranslation;
use App\Models\WebinarChapterItem;
use App\Models\Sale;
use App\Models\Support;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Setting;
use App\Models\WebinarPartPayment;
use App\Models\Subscription;
use App\Models\WebinarAccessControl;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Session;
use App\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Http\Controllers\Web\PaymentController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;

use App\Models\Bundle;

class DashboardController extends Controller
{
    public function dashboard(Request $request, $id = null)
    {
        try {
            $user = auth()->user();

            $subscriptionAccess = DB::table('subscription_access')
            ->where('user_id', $user->id)
            ->select('subscription_id', 'access_till_date')
            ->get();

            $nextBadge = $user->getBadges(true, true);

            $data = [
                'pageTitle' => trans('panel.dashboard'),
                'nextBadge' => $nextBadge
            ];

            if (!$user->isUser()) {

                $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id')->toArray();
                $pendingAppointments = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                    ->whereHas('sale')
                    ->where('status', ReserveMeeting::$pending)
                    ->count();

                $userWebinarsIds = $user->getPurchasedCoursesIds();
                $supports = \App\Models\NewSupportForAsttrolok::whereIn('webinar_id', $userWebinarsIds)
                    ->whereIn('status', ['pending', 'approved', 'verified', 'executed'])
                    ->get();

                $comments = Comment::whereIn('webinar_id', $userWebinarsIds)
                    ->where('status', 'active')
                    ->whereNull('viewed_at')
                    ->get();

                $time = time();
                $firstDayMonth = strtotime(date('Y-m-01', $time));
                $lastDayMonth = strtotime(date('Y-m-t', $time));

                $monthlySales = Sale::where('seller_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$firstDayMonth, $lastDayMonth])
                    ->get();

                $data['pendingAppointments'] = $pendingAppointments;
                $data['supportsCount'] = count($supports);
                $data['commentsCount'] = count($comments);
                $data['monthlySalesCount'] = count($monthlySales) ? $monthlySales->sum('total_amount') : 0;
                $data['monthlyChart'] = $this->getMonthlySalesOrPurchase($user);

            } else {
                $webinarsIds = $user->getPurchasedCoursesIds();

                $webinars = Webinar::whereIn('id', $webinarsIds)
                    ->where('status', 'active')
                    ->get();

                $reserveMeetings = ReserveMeeting::where('user_id', $user->id)
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    })
                    ->where('status', ReserveMeeting::$open)
                    ->get();

                $supports = \App\Models\NewSupportForAsttrolok::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'approved', 'verified', 'executed'])
                    ->get();

                $comments = Comment::where('user_id', $user->id)
                    ->whereNotNull('webinar_id')
                    ->where('status', 'active')
                    ->get();

            $giftsIds = Gift::query()->where('email', $user->email)
                ->where('status', 'active')
                ->whereNull('product_id')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $bestSaleIds = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                ->selectRaw('MAX(CASE 
                    WHEN status = "active" THEN 4
                    WHEN status = "partially_refunded" THEN 3
                    WHEN status = "pending_payment" THEN 2
                    ELSE 1
                END) as priority')
                ->selectRaw('product_id')
                ->groupBy('product_id')
                ->pluck('product_id');

            $deduped = collect();
            $seenDashExternalIds = [];
            foreach ($bestSaleIds as $productId) {
                $sale = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->whereNotIn('status', ['refunded', 'cancelled', 'expired']) // EXCLUDE refunded
                    ->whereHas('product', function ($q) {
                        $q->whereIn('product_type', ['webinar', 'course_video', 'course_live', 'bundle']);
                    })
                    ->orderByRaw("FIELD(status, 'active', 'partially_refunded', 'pending_payment', 'completed') ASC")
                    ->orderByDesc('id')
                    ->first();
                if ($sale && $sale->product) {
                    $isBundle = $sale->product->product_type === 'bundle';
                    $dashKey = ($isBundle ? 'bundle_' : 'course_') . $sale->product->external_id;
                    if (in_array($dashKey, $seenDashExternalIds)) continue;
                    $seenDashExternalIds[] = $dashKey;
                    $deduped->push($sale->id);
                }
            }

            $upeSales = \App\Models\PaymentEngine\UpeSale::whereIn('id', $deduped)->with(['product'])->get();
            
            // Transform UPE sales to legacy Sale format for the view
            $consolidatedSales = collect();
            $seenCourseIds = [];
            $seenBundleIds = [];

            foreach ($upeSales as $upeSale) {
                if (!$upeSale->product) continue;
                
                $externalId = $upeSale->product->external_id;
                $productType = $upeSale->product->product_type;
                
                // Check if we've already seen this course/bundle
                if (in_array($productType, ['webinar', 'course_video', 'course_live'])) {
                    if (in_array($externalId, $seenCourseIds)) continue;
                    $seenCourseIds[] = $externalId;
                } elseif ($productType === 'bundle') {
                    if (in_array($externalId, $seenBundleIds)) continue;
                    $seenBundleIds[] = $externalId;
                }
                
                // Create mock Sale object for view compatibility
                $mockSale = new \App\Models\Sale([
                    'id' => $upeSale->id,
                    'buyer_id' => $upeSale->user_id,
                    'webinar_id' => in_array($productType, ['webinar', 'course_video', 'course_live']) ? $externalId : null,
                    'bundle_id' => $productType === 'bundle' ? $externalId : null,
                    'total_amount' => $upeSale->total_amount,
                    'created_at' => $upeSale->created_at->timestamp,
                    'payment_method' => 'upe',
                    'type' => $productType === 'bundle' ? 'bundle' : 'webinar',
                    'status' => null,
                    'refund_at' => null,
                ]);
                
                // Load the actual webinar/bundle relationship
                if ($mockSale->webinar_id) {
                    $mockSale->setRelation('webinar', \App\Models\Webinar::find($mockSale->webinar_id));
                } elseif ($mockSale->bundle_id) {
                    $mockSale->setRelation('bundle', \App\Models\Bundle::find($mockSale->bundle_id));
                }
                
                $consolidatedSales->push($mockSale);
            }

            try {

    if (!empty($user) && !empty($user->id)) {

        $rows = WebinarAccessControl::where('user_id', $user->id)
            ->whereNotNull('expire')
            ->select(
                'webinar_id',
                DB::raw('MAX(expire) as latest_expire')
            )
            ->groupBy('webinar_id')
            ->get();

        if ($rows && $rows->count() > 0) {
            foreach ($rows as $row) {
                if (!empty($row->webinar_id) && !empty($row->latest_expire)) {
                    $extendedAccesses[$row->webinar_id] = $row->latest_expire;
                }
            }
        }
    }

    // LMS-043 FIX: Also include UPE temporary access and extension info
    try {
        $upeSupportActions = \App\Models\PaymentEngine\UpeSupportAction::where('user_id', $user->id)
            ->whereIn('action_type', ['temporary_access', 'course_extension'])
            ->where('status', 'executed')
            ->get();

        foreach ($upeSupportActions as $action) {
            $webinarId = $action->webinar_id;
            if (!$webinarId) continue;

            if ($action->action_type === 'temporary_access' && !empty($action->expires_at)) {
                // Use the later of existing access or UPE temp access
                $upeExpire = $action->expires_at instanceof \Carbon\Carbon
                    ? $action->expires_at->timestamp
                    : strtotime($action->expires_at);
                if (!isset($extendedAccesses[$webinarId]) || $upeExpire > $extendedAccesses[$webinarId]) {
                    $extendedAccesses[$webinarId] = $upeExpire;
                }
            } elseif ($action->action_type === 'course_extension' && !empty($action->metadata)) {
                $meta = is_array($action->metadata) ? $action->metadata : json_decode($action->metadata, true);
                if (!empty($meta['new_valid_until'])) {
                    $newExpire = strtotime($meta['new_valid_until']);
                    if (!isset($extendedAccesses[$webinarId]) || $newExpire > $extendedAccesses[$webinarId]) {
                        $extendedAccesses[$webinarId] = $newExpire;
                    }
                }
            }
        }
    } catch (\Throwable $e2) {
        \Log::warning('LMS-043: Could not fetch UPE support actions for dashboard', [
            'error' => $e2->getMessage(),
        ]);
    }

} catch (\Throwable $e) {
    \Log::error('Dashboard extension fetch failed', [
        'user_id' => $user->id ?? null,
        'error'   => $e->getMessage(),
    ]);

    // fallback safe empty array
    $extendedAccesses = [];
}

// Also check UPE child sales (course extensions created via SupportUpeBridge::grantCourseExtension)
try {
    $upeExtensionSales = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
        ->where('sale_type', 'free')
        ->where('pricing_mode', 'free')
        ->whereNotNull('parent_sale_id')
        ->whereNotNull('valid_until')
        ->whereIn('status', ['active', 'partially_refunded'])
        ->with('product')
        ->get();

    foreach ($upeExtensionSales as $extSale) {
        if (!$extSale->product || !$extSale->product->external_id) continue;
        $webinarId = $extSale->product->external_id;
        $extExpire = $extSale->valid_until instanceof \Carbon\Carbon
            ? $extSale->valid_until->timestamp
            : strtotime($extSale->valid_until);
        if (!isset($extendedAccesses[$webinarId]) || $extExpire > $extendedAccesses[$webinarId]) {
            $extendedAccesses[$webinarId] = $extExpire;
        }
    }
} catch (\Throwable $e) {
    \Log::warning('Dashboard UPE extension sales fetch failed', ['error' => $e->getMessage()]);
}

    $time = time();

    $giftDurations = 0;
    $giftUpcoming = 0;
    $giftPurchasedCount = 0;

    foreach ($consolidatedSales as $sale) {
                if (!empty($sale->gift_id)) {
                    $gift = $sale->gift;

                    $sale->webinar_id = $gift->webinar_id;
                    $sale->bundle_id = $gift->bundle_id;

                    $sale->webinar = !empty($gift->webinar_id) ? $gift->webinar : null;
                    $sale->bundle = !empty($gift->bundle_id) ? $gift->bundle : null;

                    $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
                    $sale->gift_sender = $sale->buyer->full_name;
                    $sale->gift_date = $gift->date;;

                    $giftPurchasedCount += 1;

                    if (!empty($sale->webinar)) {
                        $giftDurations += $sale->webinar->duration;

                        $startDate = $sale->webinar->start_date;
                        $startTime = is_object($startDate) ? $startDate->timestamp : $startDate;
                        if ($startTime && $startTime > $time) {
                            $giftUpcoming += 1;
                        }
                    }

                    if (!empty($sale->bundle)) {
                        $bundleWebinars = $sale->bundle->bundleWebinars;

                        foreach ($bundleWebinars as $bundleWebinar) {
                            $giftDurations += $bundleWebinar->webinar->duration;
                        }
                    }
                }
            }

            // Calculate stats based on consolidatedSales (UPE-based)
            $purchasedCount = $consolidatedSales->count();
            
            $webinarsHours = 0;
            $bundlesHours = 0;
            
            foreach ($consolidatedSales as $sale) {
                if ($sale->webinar_id && $sale->webinar) {
                    $webinarsHours += $sale->webinar->duration;
                } elseif ($sale->bundle_id && $sale->bundle) {
                    $bundleWebinars = $sale->bundle->bundleWebinars;
                    foreach ($bundleWebinars as $bundleWebinar) {
                        if ($bundleWebinar->webinar) {
                            $bundlesHours += $bundleWebinar->webinar->duration;
                        }
                    }
                }
            }

            $hours = $webinarsHours + $bundlesHours + $giftDurations;

            $upComing = 0;
            foreach ($consolidatedSales as $sale) {
                if ($sale->webinar_id && $sale->webinar) {
                    $startDate = $sale->webinar->start_date;
                    $startTime = is_object($startDate) ? $startDate->timestamp : $startDate;
                    if ($startTime && $startTime > $time) {
                        $upComing++;
                    }
                }
            }

            $user = auth()->user();
            $reserveMeetingsQuery1 = ReserveMeeting::where('user_id', $user->id)
                ->whereNotNull('reserved_at')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                });

            $openReserveCount = deepClone($reserveMeetingsQuery1)->where('status', \App\models\ReserveMeeting::$open)->count();
            $totalReserveCount = deepClone($reserveMeetingsQuery1)->count();

            $meetingIds1 = deepClone($reserveMeetingsQuery1)->pluck('meeting_id')->toArray();
            $teacherIds = Meeting::whereIn('id', array_unique($meetingIds1))
                ->pluck('creator_id')
                ->toArray();
            $instructors = User::select('id', 'full_name')
                ->whereIn('id', array_unique($teacherIds))
                ->get();

            $reserveMeetingsQuery1 = $this->filters($reserveMeetingsQuery1, $request);
            $reserveMeetingsQuery1 = $reserveMeetingsQuery1->with([
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

            $reserveMeetings1 = $reserveMeetingsQuery1
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $activeMeetingTimeIds = ReserveMeeting::where('user_id', $user->id)
                ->where('status', ReserveMeeting::$open)
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->pluck('meeting_time_id');

            $activeMeetingTimes = MeetingTime::whereIn('id', $activeMeetingTimeIds)->get();

            $activeHoursCount = 0;
            foreach ($activeMeetingTimes as $time) {
                $explodetime = explode('-', $time->time);
                $activeHoursCount += strtotime($explodetime[1]) - strtotime($explodetime[0]);
            }

            $userAuth = auth()->user();
            $accountings = Accounting::where('user_id', $userAuth->id)
                ->where('system', false)
                ->where('tax', false)
                ->with([
                    'webinar',
                    'promotion',
                    'subscribe',
                    'meetingTime' => function ($query) {
                        $query->with(['meeting' => function ($query) {
                            $query->with(['creator' => function ($query) {
                                $query->select('id', 'full_name');
                            }]);
                        }]);
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10);

                $data['accountings'] = $accountings;

                $data['commission'] = getFinancialSettings('commission') ?? 0;

                $consolidatedSales1 = Sale::where(['buyer_id'=> $userAuth->id, 'status'=> null])->get();

                $amount_paid=[];
                foreach($consolidatedSales1 as $consolidatedSales2){
                    if($consolidatedSales2->webinar_id){
                        $webinars1 = Webinar:: where('id', $consolidatedSales2->webinar_id)
                ->first();
                $amount_paid[] = [
                    $consolidatedSales2->total_amount,
                    $consolidatedSales2->created_at,
                    $webinars1->title ?? 'No Title',
                    $consolidatedSales2->id,
                    $consolidatedSales2->webinar_id,
                    'course',
                    $consolidatedSales2->type
                ];

                    }elseif($consolidatedSales2->installment_payment_id){
                        $InstallmentOrderPayment = InstallmentOrderPayment::where('id', $consolidatedSales2->installment_payment_id)
                    ->first();
                    if($InstallmentOrderPayment){

                        $InstallmentOrder = InstallmentOrder::where('id', $InstallmentOrderPayment->installment_order_id )
                ->first();
                if($InstallmentOrder){
                        $webinars1 = Webinar:: where('id', $InstallmentOrder->webinar_id)
                ->first();

                $amount_paid[] = [
            $consolidatedSales2->total_amount,
            $consolidatedSales2->created_at,
            $webinars1->title ?? null,
            $consolidatedSales2->id,
            $InstallmentOrder->webinar_id,
            'course',
            $consolidatedSales2->type
            ];

                }
                    }
                    }elseif($consolidatedSales2->bundle){

                        $amount_paid[]=[ $consolidatedSales2->total_amount , $consolidatedSales2->created_at , 'Bundle Course', $consolidatedSales2->id, $consolidatedSales2->bundle_id, 'bundle', $consolidatedSales2->type ];
                    }elseif($consolidatedSales2->subscription_id){
                        $Subscription = Subscription::where('id', $consolidatedSales2->subscription_id)->first();

                        $amount_paid[]=[ $consolidatedSales2->total_amount , $consolidatedSales2->created_at , $Subscription?->title, $consolidatedSales2->id, $consolidatedSales2->subscription_id, 'subscription', $consolidatedSales2->type ];
                    }elseif($consolidatedSales2->product_order_id){

                        $amount_paid[]=[ $consolidatedSales2->total_amount , $consolidatedSales2->created_at , 'Product', $consolidatedSales2->id, $consolidatedSales2->product_order_id, 'product', $consolidatedSales2->type ];
                    }elseif($consolidatedSales2->meeting_id){

                        $amount_paid[]=[ $consolidatedSales2->total_amount , $consolidatedSales2->created_at , 'Meeting', $consolidatedSales2->id, $consolidatedSales2->meeting_id, 'meeting', $consolidatedSales2->type ];
                    }
                }

                // Collect webinar_ids already covered by Sale records to avoid duplicate rows
                $dashSaleWebinarIds = [];
                foreach ($amount_paid as $ap) {
                    if ($ap[5] === 'course' && !empty($ap[4])) {
                        $dashSaleWebinarIds[] = $ap[4];
                    }
                }

                $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$userAuth->id)->get();

                foreach ($WebinarPartPayment as $WebinarPartPayment1){
                    // Skip if this webinar already has a Sale entry (prevents installment duplicates)
                    if (in_array($WebinarPartPayment1->webinar_id, $dashSaleWebinarIds)) continue;

                    $webinars1 = Webinar:: where('id', $WebinarPartPayment1->webinar_id)
                ->first();
                $amount_paid[] = [
            $WebinarPartPayment1->amount,
            strtotime($WebinarPartPayment1->created_at),
            $webinars1?->title ?? null,
            $WebinarPartPayment1->id,
            $WebinarPartPayment1->webinar_id,
            'part',
            ''
            ];

                }
                usort($amount_paid, function($a, $b) {
                    return $b[1] <=> $a[1];
                });
                $data['amount_paid'] = $amount_paid;

            $user = auth()->user();

            // Collect webinar_ids and bundle_ids already shown via $consolidatedSales to avoid duplicates
            // LMS FIX: Use the complete purchased list (including UPE) for filtering to avoid double counting installments
            $purchasedWebinarsIdsForFiltering = $user->getPurchasedCoursesIds();
            
            // Get purchased bundle IDs from UPE
            $purchasedBundleIds = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                ->whereNotIn('status', ['refunded', 'cancelled', 'expired'])
                ->whereHas('product', function($q) {
                    $q->where('product_type', 'bundle');
                })
                ->with('product')
                ->get()
                ->pluck('product.external_id')
                ->filter()
                ->toArray();

            $query = InstallmentOrder::query()
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paying')
                ->whereNotIn('status', ['refunded', 'cancelled', 'expired'])
                ->where(function($query) use ($purchasedWebinarsIdsForFiltering, $purchasedBundleIds) {
                    // Only include orders for active (non-refunded) courses and bundles
                    $query->whereIn('webinar_id', $purchasedWebinarsIdsForFiltering)
                          ->orWhereIn('bundle_id', $purchasedBundleIds);
                });
                // Removed whereNotIn to allow merging all accessible courses

            $openInstallmentsCount = (clone $query)->where('status', 'open')->count();
            $pendingVerificationCount = deepClone($query)->where('status', 'pending_verification')->count();
            $finishedInstallmentsCount = $this->getFinishedInstallments($user);

            $orders = $query->with([
                'webinar' => function ($query) {
                    $query->withCount(['chapters', 'files', 'textLessons']);
                },
                'bundle',
                'installment' => function ($query) {
                    $query->with([
                        'steps' => function ($query) {
                            $query->orderBy('deadline', 'asc');
                        }
                    ]);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])->orderBy('created_at', 'desc')
                ->paginate(10);

            // Deduplicate orders by webinar_id (keep the latest per course)
            $seenWebinarIds = [];
            $uniqueOrders = $orders->filter(function ($order) use (&$seenWebinarIds) {
                if (in_array($order->webinar_id, $seenWebinarIds)) {
                    return false;
                }
                $seenWebinarIds[] = $order->webinar_id;
                return true;
            });
            $orders->setCollection($uniqueOrders);

            foreach ($orders as $order) {
                $getRemainedInstallments = $this->getRemainedInstallments($order);

                $order->remained_installments_count = $getRemainedInstallments['total'];
                $order->remained_installments_amount = $getRemainedInstallments['amount'];

                $order->upcoming_installment = $this->getUpcomingInstallment($order);

                $hasOverdue = $order->checkOrderHasOverdue();
                $order->has_overdue = $hasOverdue;
                $order->overdue_count = 0;
                $order->overdue_amount = 0;

                if ($hasOverdue) {
                    $getOrderOverdueCountAndAmount = $order->getOrderOverdueCountAndAmount();
                    $order->overdue_count = $getOrderOverdueCountAndAmount['count'];
                    $order->overdue_amount = $getOrderOverdueCountAndAmount['amount'];
                }
            $data['payments'] =  $order->payments;
            $data['installment'] = $order->installment;
            }

            $overdueInstallmentsCount = $this->getOverdueInstallments($user);

            $data['openInstallmentsCount'] = $openInstallmentsCount;
             $data['pendingVerificationCount'] = $pendingVerificationCount;
              $data['finishedInstallmentsCount'] = $finishedInstallmentsCount;
               $data['overdueInstallmentsCount'] = $overdueInstallmentsCount;
               $data['orders'] = $orders;

            $user = auth()->user();

            // Use UPE-based logic for webinars (exclude refunded)
            $userWebinarsIds = $user->getPurchasedCoursesIds();

            // Students see only their own tickets; instructors/orgs see own + tickets on their courses
            // Use $user->webinars (courses they teach) — matches support page scope exactly
            if ($user->isUser()) {
                $supportWebinarIds = [];
                $query = \App\Models\NewSupportForAsttrolok::query()
                    ->where('user_id', $user->id);
            } else {
                $supportWebinarIds = $user->webinars->pluck('id')->toArray();
                $query = \App\Models\NewSupportForAsttrolok::query()
                    ->where(function ($query) use ($user, $supportWebinarIds) {
                        $query->where('user_id', $user->id)
                            ->orWhereIn('webinar_id', $supportWebinarIds);
                    });
            }

            $supportsCount      = (clone $query)->count();
            // Open: any status that still requires action
            $openSupportsCount  = (clone $query)->whereIn('status', ['pending', 'in_review', 'approved', 'verified'])->count();
            // Closed: resolved in any final state (matches support page grouping)
            $closeSupportsCount = (clone $query)->whereIn('status', ['completed', 'executed', 'closed', 'rejected'])->count();

            $query = $this->filters1($query, $request, $userWebinarsIds);

            $supports = $query->orderBy('created_at', 'desc')
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_name');
                    },
                    'webinar' => function ($query) {
                        $query->with(['teacher' => function ($query) {
                            $query->select('id', 'full_name', 'avatar');
                        }]);
                    }
                ])->get();

            // Get webinars using UPE data (excludes refunded)
            $webinars = Webinar::select('id', 'creator_id', 'teacher_id', 'status')
                ->whereIn('id', array_unique($userWebinarsIds))
                ->where('status', 'active')
                ->get();

            $teacherIds = $webinars->pluck('teacher_id')->toArray();

            $teachers = User::select('id', 'full_name')
                ->where('id', '!=', $user->id)
                ->whereIn('id', array_unique($teacherIds))
                ->where('status', 'active')
                ->get();

            $studentsIds = Sale::whereIn('webinar_id', $userWebinarsIds)
                ->whereNull('refund_at')
                ->pluck('buyer_id')
                ->toArray();

            $students = [];
            if (!$user->isUser()) {
                $students = User::select('id', 'full_name')
                    ->whereIn('id', array_unique($studentsIds))
                    ->where('status', 'active')
                    ->get();
            }

                $data['supports'] = $supports;

                $data['openSupportsCount'] = $openSupportsCount;
                $data['closeSupportsCount'] = $closeSupportsCount;
                $data['students'] = $students;
                $data['teachers'] = $teachers;
                $data['webinars'] = $webinars;

            if (!empty($id) and is_numeric($id)) {
                $selectSupport = Support::where('id', $id)
                    ->where(function ($query) use ($user, $userWebinarsIds) {
                        $query->where('user_id', $user->id)
                            ->orWhereIn('webinar_id', $userWebinarsIds);
                    })
                    ->with([
                        'department',
                        'conversations' => function ($query) {
                            $query->with([
                                'sender' => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar', 'role_name');
                                },
                                'supporter' => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar', 'role_name');
                                }
                            ]);
                            $query->orderBy('created_at', 'asc');
                        },
                        'webinar' => function ($query) {
                            $query->with(['teacher' => function ($query) {
                                $query->select('id', 'full_name', 'avatar', 'role_name');
                            }
                            ]);
                        }])->first();

                if (empty($selectSupport)) {
                    return back();
                }

                $data['selectSupport'] = $selectSupport;
            }

            $query1 = Support::whereNotNull('department_id')
                ->where('user_id', $user->id);

            $supportsCount1 = deepClone($query1)->count();
            $openSupportsCount1 = deepClone($query1)->where('status', 'open')->count();
            $closeSupportsCount1 = deepClone($query1)->where('status', 'close')->count();

            // $featureWebinars = Webinar::where('status', 'active')
            //         ->where('private', false)
            //         ->get();
                    
            $query = Webinar::where('status', 'active')
             ->where('private', false);

            if (!empty($webinarsIds)) {
                $query->whereNotIn('id', $webinarsIds);
            }

            $featureWebinars = $query->get();

                $data['openInstallmentsCount'] = $openInstallmentsCount;
                $data['closeSupportsCount1'] = $closeSupportsCount1;
                
                // LMS-045 Sync: Calculate webinarsCount matching the Purchases page (unique products, not expanded)
                // We deduplicate by external IDs to avoid double counting between UPE and traditional sales
                $purchasedUpeProductIds = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                    ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
                    ->groupBy('product_id')
                    ->pluck('product_id')
                    ->toArray();
                
                $upeProducts = \App\Models\PaymentEngine\UpeProduct::whereIn('id', $purchasedUpeProductIds)
                    ->get(['external_id', 'product_type']);
                
                $uniqueTracker = [];
                foreach ($upeProducts as $p) {
                    $type = $p->product_type;
                    if (in_array($type, ['course_video', 'webinar', 'course_live'])) $type = 'webinar';
                    $uniqueTracker[] = $type . '_' . $p->external_id;
                }

                $traditionalSales = Sale::where('buyer_id', $user->id)
                    ->whereNull('refund_at')
                    ->where(function($q) {
                        $q->whereNotNull('webinar_id')->orWhereNotNull('bundle_id')->orWhereNotNull('subscription_id');
                    })
                    ->with([
                        'webinar' => function ($query) {
                            $query->withCount(['chapters', 'files', 'textLessons']);
                        },
                        'bundle'
                    ])
                    ->get(['webinar_id', 'bundle_id', 'subscription_id']);

                foreach ($traditionalSales as $sale) {
                    $key = null;
                    if ($sale->webinar_id) $key = 'webinar_' . $sale->webinar_id;
                    elseif ($sale->bundle_id) $key = 'bundle_' . $sale->bundle_id;
                    elseif ($sale->subscription_id) $key = 'subscription_' . $sale->subscription_id;

                    if ($key && !in_array($key, $uniqueTracker)) {
                        $uniqueTracker[] = $key;
                    }
                }

                $data['webinarsCount'] = count($uniqueTracker);

                $reserveMeetingsQuery = \App\Models\ReserveMeeting::where('user_id', $user->id)
                    ->whereNotNull('reserved_at')
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    });

                $data['openReserveCount'] = (clone $reserveMeetingsQuery)->where('status', \App\Models\ReserveMeeting::$open)->count();
                $data['totalReserveCount'] = (clone $reserveMeetingsQuery)->count();

                $data['supportsCount'] = count($supports);
                $data['commentsCount'] = count($comments);
                $data['reserveMeetingsCount'] = $data['totalReserveCount'];
                $data['monthlyChart'] = $this->getMonthlySalesOrPurchase($user);

                // Fetch UPE installment plans for mapping to sales/orders
                $upePlanMap = []; // product_type_external_id -> plan_id
                $upeLegacySaleMap = []; // legacy_sale_id -> plan_id
                $upeLegacyOrderMap = []; // legacy_order_id -> plan_id
                $upeLegacyInstallmentOrderMap = []; // legacy_installment_order_id -> plan_id

                try {
                    $upeInstallmentPlansList = \App\Models\PaymentEngine\UpeInstallmentPlan::whereHas('sale', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->with(['sale.product'])
                        ->get();

                    foreach ($upeInstallmentPlansList as $upePlan) {
                        if ($upePlan->sale) {
                            // Product-based mapping (Broadest fallback)
                            if ($upePlan->sale->product) {
                                $key = $upePlan->sale->product->product_type . '_' . $upePlan->sale->product->external_id;
                                // Normalize course typos
                                if (in_array($upePlan->sale->product->product_type, ['course_video', 'webinar', 'course_live'])) {
                                    $key = 'webinar_' . $upePlan->sale->product->external_id;
                                }
                                $upePlanMap[$key] = $upePlan->id;
                            }

                            // Metadata-based legacy mapping (Precise)
                            $meta = is_array($upePlan->sale->metadata) ? $upePlan->sale->metadata : json_decode($upePlan->sale->metadata, true);
                            if (!empty($meta['legacy_sale_id'])) {
                                $upeLegacySaleMap[$meta['legacy_sale_id']] = $upePlan->id;
                            }
                            if (!empty($meta['legacy_order_id'])) {
                                $upeLegacyOrderMap[$meta['legacy_order_id']] = $upePlan->id;
                            }
                            if (!empty($meta['legacy_installment_order_id'])) {
                                $upeLegacyInstallmentOrderMap[$meta['legacy_installment_order_id']] = $upePlan->id;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Dashboard UPE plan mapping failed', ['error' => $e->getMessage()]);
                }

                foreach ($orders as $order) {
                    if ($order->webinar_id) {
                        if (in_array($order->webinar_id, $seenCourseIds)) continue;
                        $seenCourseIds[] = $order->webinar_id;
                    } elseif ($order->bundle_id) {
                        if (in_array($order->bundle_id, $seenBundleIds)) continue;
                        $seenBundleIds[] = $order->bundle_id;
                    }
                    
                    // Transform InstallmentOrder to look like a Sale for the view
                    $mockSale = new \App\Models\Sale([
                        'buyer_id' => $order->user_id,
                        'webinar_id' => $order->webinar_id,
                        'bundle_id' => $order->bundle_id,
                        'created_at' => $order->created_at,
                        'type' => $order->webinar_id ? 'webinar' : ($order->bundle_id ? 'bundle' : 'other'),
                    ]);
                    $mockSale->setRelation('webinar', $order->webinar);
                    $mockSale->setRelation('bundle', $order->bundle);
                    $mockSale->is_installment = true;
                    $mockSale->installment_order = $order;
                    
                    // Attach UPE plan ID to mock sale for Pay Now link
                    $mockSale->upe_plan_id = $upeLegacyInstallmentOrderMap[$order->id] ?? null;
                    if (empty($mockSale->upe_plan_id)) {
                        if ($order->webinar_id) {
                            $mockSale->upe_plan_id = $upePlanMap['webinar_' . $order->webinar_id] ?? null;
                        } elseif ($order->bundle_id) {
                            $mockSale->upe_plan_id = $upePlanMap['bundle_' . $order->bundle_id] ?? null;
                        }
                    }
                    
                    // Also attach to the order object itself for other sections
                    $order->upe_plan_id = $mockSale->upe_plan_id;

                    $consolidatedSales->push($mockSale);
                }

                $data['sales'] = $consolidatedSales->sortByDesc('created_at');
                $data['hours'] = $hours;
                  $data['instructors'] = $instructors;
                $data['reserveMeetings'] = $reserveMeetings1;
                $data['openReserveCount'] = $openReserveCount;
                $data['totalReserveCount'] = $totalReserveCount;
              $data['extendedAccesses'] = $extendedAccesses ?? [];
                $data['activeHoursCount'] = round($activeHoursCount / 3600, 2);
                $data['featureWebinars']=$featureWebinars;
                $data['subscriptionAccess'] = $subscriptionAccess;

                // Certificates & Quizzes Passed counts
                try {
                    $data['certificatesCount'] = \App\Models\Certificate::where('student_id', $user->id)->count();
                } catch (\Throwable $e) {
                    $data['certificatesCount'] = 0;
                }
                try {
                    $data['quizzesPassedCount'] = \App\Models\QuizzesResult::where('user_id', $user->id)->where('status', 'passed')->count();
                } catch (\Throwable $e) {
                    $data['quizzesPassedCount'] = 0;
                }

                // UPE Payment Engine data
                try {
                    $upeUserId = $user->id;
                    $upeSaleIds = \App\Models\PaymentEngine\UpeSale::where('user_id', $upeUserId)->pluck('id');

                    // Active installment plans with schedules
                    $upeInstallmentPlans = \App\Models\PaymentEngine\UpeInstallmentPlan::whereIn('sale_id', $upeSaleIds)
                        ->where('status', 'active')
                        ->with(['sale.product', 'schedules'])
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();

                    $upePlanIds = $upeInstallmentPlans->pluck('id');

                    // Overdue schedules count
                    $upeOverdueCount = \App\Models\PaymentEngine\UpeInstallmentSchedule::whereIn('plan_id', $upePlanIds)
                        ->where('status', 'overdue')->count();

                    // Upcoming/overdue schedules (next payments due)
                    // Upcoming/overdue schedules (grouped by plan to avoid showing same course multiple times)
                    $upeAllPlanIds = \App\Models\PaymentEngine\UpeInstallmentPlan::whereIn('sale_id', $upeSaleIds)->pluck('id');
                    $upeUpcomingSchedules = \App\Models\PaymentEngine\UpeInstallmentSchedule::whereIn('id', function($q) use ($upeAllPlanIds) {
                            $q->select(DB::raw('MIN(id)'))
                              ->from('upe_installment_schedules')
                              ->whereIn('plan_id', $upeAllPlanIds)
                              ->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])
                              ->groupBy('plan_id');
                        })
                        ->orderBy('due_date')
                        ->with(['plan.sale.product'])
                        ->limit(5)
                        ->get();

                    // Recent ledger entries
                    $upeLedgerEntries = \App\Models\PaymentEngine\UpeLedgerEntry::whereIn('sale_id', $upeSaleIds)
                        ->orderBy('created_at', 'desc')
                        ->with('sale.product')
                        ->limit(10)
                        ->get();

                    // Active subscription
                    $upeSubscription = \App\Models\PaymentEngine\UpeSubscription::where('user_id', $upeUserId)
                        ->active()
                        ->with('product')
                        ->first();

                    $data['upeInstallmentPlans'] = $upeInstallmentPlans;
                    $data['upeOverdueCount'] = $upeOverdueCount;
                    $data['upeUpcomingSchedules'] = $upeUpcomingSchedules;
                    $data['upeLedgerEntries'] = $upeLedgerEntries;
                    $data['upeSubscription'] = $upeSubscription;
                } catch (\Throwable $e) {
                    \Log::warning('Dashboard UPE data fetch failed', ['error' => $e->getMessage()]);
                    $data['upeInstallmentPlans'] = collect();
                    $data['upeOverdueCount'] = 0;
                    $data['upeUpcomingSchedules'] = collect();
                    $data['upeLedgerEntries'] = collect();
                    $data['upeSubscription'] = null;
                }
            }

            

            $sidebanner = Setting::getsidebanner();

            $data['sidebanner'] = $sidebanner;
            $data['giftModal'] = $this->showGiftModal($user);

            // Get wallet balance + recent transactions using WalletService
            try {
                $walletBalance = 0;
                $recentWalletTransactions = collect();
                if (auth()->check()) {
                    $walletSvc = app(\App\Services\PaymentEngine\WalletService::class);
                    $walletBalance = $walletSvc->balance(auth()->id());
                    $recentWalletTransactions = \App\Models\PaymentEngine\WalletTransaction::where('user_id', auth()->id())
                        ->orderByDesc('id')
                        ->limit(5)
                        ->get();
                }
                $data['walletBalance'] = $walletBalance;
                $data['recentWalletTransactions'] = $recentWalletTransactions;
            } catch (\Throwable $e) {
                \Log::warning('Dashboard wallet balance fetch failed', ['error' => $e->getMessage()]);
                $data['walletBalance'] = 0;
                $data['recentWalletTransactions'] = collect();
            }

                if($user->role_name == 'user' || ($user->role_name == 'teacher' && !empty($user->consultant) && $user->consultant == 1)){

                     return view(getTemplate() . '.panel.dashboard.index', $data);
                }else{

                     return view(getTemplate() . '.panel.dashboard.index2', $data);
                }
        } catch (\Exception $e) {
            \Log::error('dashboard error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function bundledata($id = null) {
        try {
            $bundle = Bundle::where('id', $id)
            ->with([
                'teacher',
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
            ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->where('status', 'active')
            ->first();

            $userConsultants = User::where('status', Webinar::$active)
            ->where('role_id', 4)
            ->where('consultant', 1)
            ->whereHas('meeting', function ($q) {
                $q->where('disabled', 0)
                ->whereHas('meetingTimes');
            })
            ->get();

            $instructor = $bundle->teacher;
            $canReserve = true;

            $bundleWebinarId = null;
            if (!empty($bundle->bundleWebinars) && count($bundle->bundleWebinars) > 0) {
            $bundleWebinarId = $bundle->bundleWebinars->first()->id;
            }

            $data = [
            'bundle' => $bundle,
            'userConsultants' => $userConsultants,
            'instructor' => $instructor,
            'canReserve' => $canReserve,
            'bundle_id' => $bundle->id,
            'bundle_webinar_id' => $bundleWebinarId,
            ];

            return view(getTemplate() . '.panel.dashboard.bundle', $data);
        } catch (\Exception $e) {
            \Log::error('bundledata error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function filters1($query, $request, $userWebinarsIds = [])
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $student_id = $request->get('student');
        $teacher_id = $request->get('teacher');
        $webinar_id = $request->get('webinar');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($student_id) and $student_id != 'all') {
            $query->where('user_id', $student_id);
        }

        if (!empty($teacher_id) and $teacher_id != 'all') {
            $teacher = User::where('id', $teacher_id)
                ->where('status', 'active')
                ->first();

            $teacherWebinarIds = $teacher->webinars->pluck('id')->toArray();

            $query->whereIn('webinar_id', $teacherWebinarIds);
        }

        if (!empty($webinar_id) and $webinar_id != 'all') {
            $query->where('webinar_id', $webinar_id);
        }

        if (!empty($status) and $status != 'all') {
            $query->where('status', $status);
        }

        return $query;
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

    private function getRemainedInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->first();

            if (empty($payment)) {
                $total += 1;
                $amount += $step->getPrice($itemPrice);
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $dueAt = ($step->deadline * 86400) + $order->created_at;

            if ($dueAt < $time) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();

                if (empty($payment)) {
                    $total += 1;
                    $amount += $step->getPrice($itemPrice);
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getUpcomingInstallment($order)
    {
        $result = null;
        $deadline = 0;

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            if (empty($payment) and ($deadline == 0 or $deadline > $step->deadline)) {
                $deadline = $step->deadline;
                $result = $step;
            }
        }

        return $result;
    }

    private function getOverdueInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            if ($order->checkOrderHasOverdue()) {
                $count += 1;
            }
        }

        return $count;
    }

    private function getFinishedInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $steps = $order->installment->steps;
            $paidAllSteps = true;

            foreach ($steps as $step) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    })
                    ->first();

                if (empty($payment)) {
                    $paidAllSteps = false;
                }
            }

            if ($paidAllSteps) {
                $count += 1;
            }
        }

        return $count;
    }

    private function showGiftModal($user)
    {
        $gift = Gift::query()->where('email', $user->email)
            ->where('status', 'active')
            ->where('viewed', false)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->first();

        if (!empty($gift)) {
            $gift->update([
                'viewed' => true
            ]);

            $data = [
                'gift' => $gift
            ];

            $result = (string)view()->make('web.default.panel.dashboard.gift_modal', $data);
            $result = str_replace(array("\r\n", "\n", "  "), '', $result);

            return $result;
        }

        return null;
    }

    public function create()
    {
        try {
            $departments = SupportDepartment::all();
            $user = auth()->user();

            $webinarIds = $user->getPurchasedCoursesIds();

            $webinars = Webinar::select('id', 'creator_id')
                ->whereIn('id', $webinarIds)
                ->where('support', true)
                ->with(['creator' => function ($query) {
                    $query->select('id', 'full_name');
                }])->get();

            $data = [
                'pageTitle' => trans('panel.send_new_support'),
                'departments' => $departments,
                'webinars' => $webinars
            ];

            return view(getTemplate() . '.panel.support.new', $data);
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

            $this->validate($request, [
                'title' => 'required|min:2',
                'type' => 'required',
                'department_id' => 'required_if:type,platform_support|exists:support_departments,id',
                'webinar_id' => 'required_if:type,course_support|exists:webinars,id',
                'message' => 'required|min:2',
                'attach' => 'nullable|string',
            ]);

            $data = $request->all();
            unset($data['type']);

            $support = Support::create([
                'user_id' => $user->id,
                'department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
                'webinar_id' => !empty($data['webinar_id']) ? $data['webinar_id'] : null,
                'title' => $data['title'],
                'status' => 'open',
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            SupportConversation::create([
                'support_id' => $support->id,
                'sender_id' => $user->id,
                'message' => $data['message'],
                'attach' => $data['attach'],
                'created_at' => time(),
            ]);

            if (!empty($data['webinar_id'])) {
                $webinar = Webinar::findOrFail($data['webinar_id']);

                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                    '[u.name]' => $user->full_name
                ];
                sendNotification('support_message', $notifyOptions, $webinar->teacher_id);
            }

            if (!empty($data['department_id'])) {
                $notifyOptions = [
                    '[s.t.title]' => $support->title,
                ];
                sendNotification('support_message_admin', $notifyOptions, 1);
            }

            $url = '/panel/support';

            if (!empty($data['department_id'])) {
                $url = '/panel/support/tickets';
            }

            return redirect($url);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeConversations(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'message' => 'required|string|min:2',
            ]);

            $data = $request->all();
            $user = auth()->user();

            $userWebinarsIds = $user->getPurchasedCoursesIds();

            $support = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })->first();

            if (empty($support)) {
                abort(404);
            }

            $support->update([
                'status' => ($support->user_id == $user->id) ? 'open' : 'replied',
                'updated_at' => time()
            ]);

            SupportConversation::create([
                'support_id' => $support->id,
                'sender_id' => $user->id,
                'message' => $data['message'],
                'attach' => $data['attach'],
                'created_at' => time(),
            ]);

            if (!empty($support->webinar_id)) {
                $webinar = Webinar::findOrFail($support->webinar_id);

                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                ];
                sendNotification('support_message_replied', $notifyOptions, ($support->user_id == $user->id) ? $webinar->teacher_id : $user->id);
            }

            if (!empty($support->department_id)) {
                $notifyOptions = [
                    '[s.t.title]' => $support->title,
                ];
                sendNotification('support_message_replied_admin', $notifyOptions, 1);
            }

            return back();
        } catch (\Exception $e) {
            \Log::error('storeConversations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function close($id)
    {
        try {
            $user = auth()->user();
            $userWebinarsIds = $user->getPurchasedCoursesIds();

            $support = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })->first();

            if (empty($support)) {
                abort(404);
            }

            $support->update([
                'status' => 'close',
                'updated_at' => time()
            ]);

            return back();
        } catch (\Exception $e) {
            \Log::error('close error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    private function getMonthlySalesOrPurchase($user)
    {
        $months = [];
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create(date('Y'), $month);

            $start_date = $date->timestamp;
            $end_date = $date->copy()->endOfMonth()->timestamp;

            $months[] = trans('panel.month_' . $month);

            if (!$user->isUser()) {
                $monthlySales = Sale::where('seller_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('total_amount');

                $data[] = round($monthlySales, 2);
            } else {
                $monthlyPurchase = Sale::where('buyer_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->count();

                $data[] = $monthlyPurchase;
            }
        }

        return [
            'months' => $months,
            'data' => $data
        ];
    }
}
