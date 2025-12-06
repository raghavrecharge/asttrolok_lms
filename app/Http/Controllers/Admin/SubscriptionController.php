<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\Subscription;
use App\Models\SubscriptionFilterOption;
use App\Models\SubscriptionWebinarChapterItems;
use App\Models\Category;
use App\Models\Gift;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Sale;
use App\Models\File;
use App\Models\FileTranslation;
use App\Models\SpecialOffer;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\Translation\SubscriptionTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\Product;
use App\Models\SubscriptionExtraDetails;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->authorize('admin_subscriptions_list');

            removeContentLocale();

            $query = Subscription::query();

            $totalSubscriptions = $query->count();
            $totalPendingSubscriptions = deepClone($query)->where('subscriptions.status', Subscription::$pending)->count();
            $totalSales = deepClone($query)->join('sales', 'subscriptions.id', '=', 'sales.subscription_id')
                ->select(DB::raw('count(sales.subscription_id) as sales_count, sum(total_amount) as total_amount'))
                ->whereNotNull('sales.subscription_id')
                ->whereNull('sales.refund_at')
                ->first();

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $query = $this->handleFilters($query, $request)
                ->with([
                    'category',
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    },
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    }
                ])
                ->withCount([
                    'subscriptionWebinars'
                ]);

            $subscriptions = $query->paginate(10);

            foreach ($subscriptions as $subscription) {
                $giftsIds = Gift::query()->where('subscription_id', $subscription->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $sales = Sale::query()
                    ->where(function ($query) use ($subscription, $giftsIds) {
                        $query->where('subscription_id', $subscription->id);
                        $query->orWhereIn('gift_id', $giftsIds);
                    })
                    ->whereNull('refund_at')
                    ->get();

                $subscription->sales = $sales;
            }

            $data = [
                'pageTitle' => trans('update.subscriptions'),
                'subscriptions' => $subscriptions,
                'totalSubscriptions' => $totalSubscriptions,
                'totalPendingSubscriptions' => $totalPendingSubscriptions,
                'totalSales' => $totalSales,
                'categories' => $categories,
            ];

            $teacher_ids = $request->get('teacher_ids', null);
            if (!empty($teacher_ids)) {
                $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
            }

            return view('admin.subscriptions.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $teacher_ids = $request->get('teacher_ids', null);
        $category_id = $request->get('category_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($teacher_ids) and count($teacher_ids)) {
            $query->whereIn('teacher_id', $teacher_ids);
        }

        if (!empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (!empty($status)) {
            $query->where('subscriptions.status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $subscriptionIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $subscriptionIdsHasDiscount[] = $ticket->subscription_id;
                        }
                    }

                    $specialOffersSubscriptionIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('subscription_id')
                        ->toArray();

                    $subscriptionIdsHasDiscount = array_merge($specialOffersSubscriptionIds, $subscriptionIdsHasDiscount);

                    $query->whereIn('id', $subscriptionIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'subscriptions.id', '=', 'sales.subscription_id')
                        ->select('subscriptions.*', 'sales.subscription_id', 'sales.refund_at', DB::raw('count(sales.subscription_id) as sales_count'))
                        ->whereNotNull('sales.subscription_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.subscription_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'subscriptions.id', '=', 'sales.subscription_id')
                        ->select('subscriptions.*', 'sales.subscription_id', 'sales.refund_at', DB::raw('count(sales.subscription_id) as sales_count'))
                        ->whereNotNull('sales.subscription_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.subscription_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'subscriptions.id', '=', 'sales.subscription_id')
                        ->select('subscriptions.*', 'sales.subscription_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.subscription_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.subscription_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'subscriptions.id', '=', 'sales.subscription_id')
                        ->select('subscriptions.*', 'sales.subscription_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.subscription_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.subscription_id')
                        ->orderBy('amounts', 'desc');
                    break;

                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'updated_at_asc':
                    $query->orderBy('updated_at', 'asc');
                    break;

                case 'updated_at_desc':
                    $query->orderBy('updated_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function create()
    {
        try {
            $this->authorize('admin_subscriptions_create');

            removeContentLocale();

            $categories = Category::where('parent_id', null)->get();

            $data = [
                'pageTitle' => trans('update.new_subscription'),
                'categories' => $categories
            ];

            return view('admin.subscriptions.create', $data);
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
            $this->authorize('admin_subscriptions_create');

            $this->validate($request, [
                'title' => 'required|max:255',
                'slug' => 'max:255|unique:subscriptions,slug',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
                'teacher_id' => 'required|exists:users,id',
                'category_id' => 'required',
            ]);

            $data = $request->all();

            if (empty($data['slug'])) {
                $data['slug'] = Subscription::makeSlug($data['title']);
            }

            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }

            $subscription = Subscription::create([
                'slug' => $data['slug'],
                'teacher_id' => $data['teacher_id'],
                'creator_id' => $data['teacher_id'],
                'thumbnail' => $data['thumbnail'],
                'image_cover' => $data['image_cover'],
                'video_demo' => $data['video_demo'],
                'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
                'subscribe' => !empty($data['subscribe']) ? true : false,
                'points' => $data['points'] ?? null,
                'price' => $data['price'],
                'access_days' => $data['access_days'] ?? null,
                'video_count' => $data['video_count'] ?? 0,
                'category_id' => $data['category_id'],
                'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
                'status' => Subscription::$pending,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            if ($subscription) {
                SubscriptionTranslation::updateOrCreate([
                    'subscription_id' => $subscription->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'seo_description' => $data['seo_description'],
                ]);
            }

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                SubscriptionFilterOption::where('subscription_id', $subscription->id)->delete();

                foreach ($filters as $filter) {
                    SubscriptionFilterOption::create([
                        'subscription_id' => $subscription->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                Tag::where('subscription_id', $subscription->id)->delete();

                foreach ($tags as $tag) {
                    Tag::create([
                        'subscription_id' => $subscription->id,
                        'title' => $tag,
                    ]);
                }
            }

            return redirect(getAdminPanelUrl() . '/subscriptions/' . $subscription->id . '/edit?locale=' . $data['locale']);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');

            $subscription = Subscription::where('id', $id)
                ->with([
                    'tickets',
                    'faqs',
                    'extraDetails',
                    'category' => function ($query) {
                        $query->with(['filters' => function ($query) {
                            $query->with('options');
                        }]);
                    },
                    'tags',
                    'subscriptionWebinars'
                ])
                ->first();

            if (empty($subscription)) {
                abort(404);
            }
            $SubscriptionWebinarChapterItems_count = SubscriptionWebinarChapterItems:: where('subscription_id',$subscription->id)->max('order') ?? 0;
            $count=0 + $SubscriptionWebinarChapterItems_count;

            $webinar_id_in_plan=[];
            foreach ($subscription->subscriptionWebinars as $subscriptionWebinars){
               $webinar = $subscriptionWebinars->webinar;
                $quizzes = $webinar->quizzes;
                $webinar_id_in_plan[]=$webinar->id;

                 foreach ($webinar->chapters as $chapter){

                 foreach ($chapter->chapterItems as $chapterItems){

            if($chapterItems->type == 'file'){
                if(File::where('id',$chapterItems->item_id)->first()){
                    if(empty(SubscriptionWebinarChapterItems::where('subscription_id',$subscription->id)
                    ->where('item_id', $chapterItems->item_id)->first())){
                        $count++;

                        SubscriptionWebinarChapterItems::Create([
                            'subscription_id' => $subscription->id,
                            'user_id' => $chapterItems->user_id,
                            'chapter_id' => $chapter->id,
                            'webinar_id' => $chapter->webinar_id,
                            'chapter_item_id' => $chapterItems->id,
                            'item_id' => $chapterItems->item_id,
                            'type' => $chapterItems->type,
                            'order' => $count,
                            'created_at' => time(),
                        ]);

                    }
                }
            }else{

            if(empty(SubscriptionWebinarChapterItems::where('subscription_id',$subscription->id)
                    ->where('item_id', $chapterItems->item_id)->first())){
                        $count++;
            SubscriptionWebinarChapterItems::Create([
                        'subscription_id' => $subscription->id,
                        'user_id' => $chapterItems->user_id,
                        'chapter_id' => $chapter->id,
                        'webinar_id' => $chapter->webinar_id,
                        'chapter_item_id' => $chapterItems->id,
                        'item_id' => $chapterItems->item_id,
                        'type' => $chapterItems->type,
                        'order' => $count,
                        'created_at' => time(),
                    ]);

                    }
                 }
                 }

            }
            }

            SubscriptionWebinarChapterItems::where('subscription_id',$subscription->id)->whereNotIn('webinar_id', $webinar_id_in_plan)->delete();

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $subscription->getTable(), $subscription->id);

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $tags = $subscription->tags->pluck('title')->toArray();

            $userIds = [$subscription->creator_id, $subscription->teacher_id];

            $userWebinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->get();

            $userProducts = Product::where('status', Webinar::$active)
                ->get();

            $userConsultants = User::where('status', Webinar::$active)
            ->where('role_id', 4)
            ->where('consultant', 1)
            ->whereHas('meeting', function ($q) {
            $q->where('disabled', 0)
            ->whereHas('meetingTimes');
            })
            ->get();

            $chapterItems = SubscriptionWebinarChapterItems::with(['file', 'quiz'])
            ->where('subscription_id', $subscription->id)
            ->orderBy('order', 'asc')
            ->get();

            $subscriptionId = $id;
            $subscription = Subscription::with(['subscriptionWebinars.webinar', 'subscriptionWebinars.webinar.chapters'])
            ->findOrFail($id);
            $subscriptionItems = SubscriptionWebinarChapterItems::with('file')
            ->where('subscription_id', $subscriptionId)
            ->orderBy('order', 'asc')
            ->get();
            $data = [
                'pageTitle' => trans('admin/main.edit') . ' | ' . $subscription->title,
                'userWebinars' => $userWebinars,
                'userProducts' => $userProducts,
                'userConsultants' => $userConsultants,
                'categories' => $categories,
                'subscription' => $subscription,
                'subscriptionCategoryFilters' => !empty($subscription->category) ? $subscription->category->filters : null,
                'subscriptionFilterOptions' => $subscription->filterOptions->pluck('filter_option_id')->toArray(),
                'tickets' => $subscription->tickets,
                'faqs' => $subscription->faqs,
                'subscriptionTags' => $tags,
                 'subscriptionItems' => $subscriptionItems,
                'subscriptionWebinars' => $subscription->subscriptionWebinars,
                'defaultLocale' => getDefaultLocale(),
                 'chapterItems' => $chapterItems,
            ];

            return view('admin.subscriptions.create', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_edit');
            $data = $request->all();

            $subscription = Subscription::find($id);
            $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
            $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
            $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

            $rules = [
                'title' => 'required|max:255',
                'slug' => 'max:255|unique:subscriptions,slug,' . $subscription->id,
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
                'teacher_id' => 'required|exists:users,id',
                'category_id' => 'required',
            ];

            $this->validate($request, $rules);

            if (!empty($data['teacher_id'])) {
                $teacher = User::findOrFail($data['teacher_id']);
                $creator = $subscription->creator;

                if (empty($teacher) or ($creator->isOrganization() and ($teacher->organ_id != $creator->id and $teacher->id != $creator->id))) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('admin/main.is_not_the_teacher_of_this_organization'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }
            }

            if (empty($data['slug'])) {
                $data['slug'] = Subscription::makeSlug($data['title']);
            }

            $data['status'] = $publish ? Subscription::$active : ($reject ? Subscription::$inactive : ($isDraft ? Subscription::$isDraft : Subscription::$pending));
            $data['updated_at'] = time();
            $data['subscribe'] = !empty($data['subscribe']) ? true : false;

            if ($data['category_id'] != $subscription->category_id) {
                SubscriptionFilterOption::where('subscription_id', $subscription->id)->delete();
            }

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                SubscriptionFilterOption::where('subscription_id', $subscription->id)->delete();

                foreach ($filters as $filter) {
                    SubscriptionFilterOption::create([
                        'subscription_id' => $subscription->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                Tag::where('subscription_id', $subscription->id)->delete();

                foreach ($tags as $tag) {
                    Tag::create([
                        'subscription_id' => $subscription->id,
                        'title' => $tag,
                    ]);
                }
            }

            unset($data['_token'],
                $data['current_step'],
                $data['draft'],
                $data['get_next'],
                $data['partners'],
                $data['tags'],
                $data['filters'],
                $data['ajax']
            );

            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }

            $subscription->update([
                'slug' => $data['slug'],
                'teacher_id' => $data['teacher_id'],
                'thumbnail' => $data['thumbnail'],
                'image_cover' => $data['image_cover'],
                'video_demo' => $data['video_demo'],
                'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
                'subscribe' => $data['subscribe'],
                'points' => $data['points'] ?? null,
                'price' => $data['price'],
                'access_days' => $data['access_days'] ?? null,
                'video_count' => $data['video_count'] ?? 0,
                'category_id' => $data['category_id'],
                'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
                'status' => $data['status'],
                'updated_at' => time(),
            ]);
       $extraDetailsData = $request->only([
        'plan_type', 'plan_badge', 'plan_price', 'price_suffix', 'plan_duration_option', 
        'plan_cancel_text', 'comparison_text', 'plan_icon', 'is_featured', 'heading_main', 'heading_sub',
        'heading_extra', 'additional_description', 'extra_description', 'subtitle', 'subdescription',
        'material_text', 'material_icon', 'learn_text', 'price_icon', 'plan_movie', 'learn_title', 'learn_description',
        'learn_icon', 'bonus_heading', 'bonus_icon', 'ad_title', 'ad_subtitle', 'ad_description', 'ad_img',
        'certification_time', 'certification_focus', 'certification_outcome', 'rate_title', 'rate_options', 'rate_icon'
    ]);
  $subscription->extraDetails()->updateOrCreate(
    ['subscription_id' => $subscription->id],
    $extraDetailsData
);
            if ($subscription) {
                SubscriptionTranslation::updateOrCreate([
                    'subscription_id' => $subscription->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'seo_description' => $data['seo_description'],
                ]);
            }

            $notifyOptions = [
                '[item_title]' => $subscription->title,
            ];

            if ($publish) {
                sendNotification('subscription_approved', $notifyOptions, $subscription->teacher_id);

            } elseif ($reject) {
                sendNotification('subscription_rejected', $notifyOptions, $subscription->teacher_id);
            }

            removeContentLocale();

            return back();
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorize('admin_subscriptions_delete');

            $subscription = Subscription::find($id);

            if (!empty($subscription)) {
                $subscription->delete();
            }

            return redirect(getAdminPanelUrl() . '/subscriptions');
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function studentsLists(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinar_students_lists');

            $subscription = Subscription::where('id', $id)
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    }
                ])
                ->first();

            if (!empty($subscription)) {
                $giftsIds = Gift::query()->where('subscription_id', $subscription->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $query = User::join('sales', 'sales.buyer_id', 'users.id')

                    ->where(function ($query) use ($subscription) {
                        $query->where('sales.subscription_id', $subscription->id);
                    })
                    ->whereNull('sales.refund_at');

                $students = $this->studentsListsFilters($subscription, $query, $request)
                    ->orderBy('sales.created_at', 'desc')
                    ->paginate(10);

                $userGroups = Group::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $totalExpireStudents = 0;
                if (!empty($subscription->access_days)) {
                    $accessTimestamp = $subscription->access_days * 24 * 60 * 60;

                    $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                        ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                        ->where(function ($query) use ($subscription) {
                            $query->where('sales.subscription_id', $subscription->id);
                        })
                        ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                        ->whereNull('sales.refund_at')
                        ->count();
                }

                $subscriptionWebinars = $subscription->subscriptionWebinars;

                $webinarStatisticController = new WebinarStatisticController();

                foreach ($students as $key => $student) {
                    $learnings = 0;
                    $webinarCount = 0;

                    foreach ($subscriptionWebinars as $subscriptionWebinar) {
                        if (!empty($subscriptionWebinar->webinar)) {
                            $webinarCount += 1;
                            $learnings += $webinarStatisticController->getCourseProgressForStudent($subscriptionWebinar->webinar, $student->id);
                        }
                    }

                    $learnings = ($learnings > 0 and $webinarCount > 0) ? round($learnings / $webinarCount, 2) : 0;

                    if (!empty($student->gift_id)) {
                        $gift = Gift::query()->where('id', $student->gift_id)->first();

                        if (!empty($gift)) {
                            $receipt = $gift->receipt;

                            if (!empty($receipt)) {
                                $receipt->rates = $student->rates;
                                $receipt->access_to_purchased_item = $student->access_to_purchased_item;
                                $receipt->sale_id = $student->sale_id;
                                $receipt->purchase_date = $student->purchase_date;
                                $receipt->learning = $learnings;

                                $students[$key] = $receipt;
                            } else {
                                $newUser = new User();
                                $newUser->full_name = $gift->name;
                                $newUser->email = $gift->email;
                                $newUser->rates = 0;
                                $newUser->access_to_purchased_item = $student->access_to_purchased_item;
                                $newUser->sale_id = $student->sale_id;
                                $newUser->purchase_date = $student->purchase_date;
                                $newUser->learning = 0;

                                $students[$key] = $newUser;
                            }
                        }
                    } else {
                        $student->learning = $learnings;
                    }
                }

                $roles = Role::all();

                $data = [
                    'pageTitle' => trans('admin/main.students'),
                    'webinar' => $subscription,
                    'students' => $students,
                    'userGroups' => $userGroups,
                    'roles' => $roles,
                    'totalStudents' => $students->total(),
                    'totalActiveStudents' => $students->total() - $totalExpireStudents,
                    'totalExpireStudents' => $totalExpireStudents,
                ];

                return view('admin.subscriptions.students', $data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('studentsLists error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function studentsListsFilters($subscription, $query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $full_name = $request->get('full_name');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $role_id = $request->get('role_id');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'sales.created_at');

        if (!empty($full_name)) {
            $query->where('users.full_name', 'like', "%$full_name%");
        }

        if (!empty($sort)) {
            if ($sort == 'rate_asc') {
                $query->orderBy('webinar_reviews.rates', 'asc');
            }

            if ($sort == 'rate_desc') {
                $query->orderBy('webinar_reviews.rates', 'desc');
            }
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }

        if (!empty($role_id)) {
            $query->where('users.role_id', $role_id);
        }

        if (!empty($status)) {
            if ($status == 'expire' and !empty($subscription->access_days)) {
                $accessTimestamp = $subscription->access_days * 24 * 60 * 60;

                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }

    public function notificationToStudents($id)
    {
        try {
            $this->authorize('admin_webinar_notification_to_students');

            $subscription = Subscription::findOrFail($id);

            $data = [
                'pageTitle' => trans('notification.send_notification'),
                'subscription' => $subscription
            ];

            return view('admin.subscriptions.send-notification-to-course-students', $data);
        } catch (\Exception $e) {
            \Log::error('notificationToStudents error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function sendNotificationToStudents(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinar_notification_to_students');

            $this->validate($request, [
                'title' => 'required|string',
                'message' => 'required|string',
            ]);

            $data = $request->all();

            $subscription = Subscription::where('id', $id)
                ->with([
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                        $query->with([
                            'buyer'
                        ]);
                    }
                ])
                ->first();

            if (!empty($subscription)) {
                foreach ($subscription->sales as $sale) {
                    if (!empty($sale->buyer)) {
                        $user = $sale->buyer;

                        Notification::create([
                            'user_id' => $user->id,
                            'group_id' => null,
                            'sender_id' => auth()->id(),
                            'title' => $data['title'],
                            'message' => $data['message'],
                            'sender' => Notification::$AdminSender,
                            'type' => 'single',
                            'created_at' => time()
                        ]);

                        if (!empty($user->email) and env('APP_ENV') == 'production') {
                            \Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message']]));
                        }
                    }
                }

                $toastData = [
                    'title' => trans('public.request_success'),
                    'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => count($subscription->sales)]),
                    'status' => 'success'
                ];

                return redirect(getAdminPanelUrl("/subscriptions/{$subscription->id}/students"))->with(['toast' => $toastData]);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('sendNotificationToStudents error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function search(Request $request)
    {
        try {
            $term = $request->get('term');

            $option = $request->get('option', null);

            $query = Subscription::select('id')
                ->whereTranslationLike('title', "%$term%");

            $subscriptions = $query->get();

            return response()->json($subscriptions, 200);
        } catch (\Exception $e) {
            \Log::error('search error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
public function updateStatus(Request $request, $id)
{
        try {
            $request->validate([
            'status' => 'required|in:active,inactive',
            ]);

            $item = SubscriptionWebinarChapterItems::findOrFail($id);

            $item->status = $request->status;
            $item->save();

            return response()->json([
                'title' => trans('public.request_success'),
                'msg' => trans('update.items_sorted_successful')
            ]);
        } catch (\Exception $e) {
            \Log::error('updateStatus error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function orderItems(Request $request)
    {
        try {
            $this->authorize('admin_webinars_edit');
            $data = $request->all();

            $validator = Validator::make($data, [
                'items' => 'required',
                'table' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tableName = $data['table'];
            $itemIds = explode(',', $data['items']);

            if (!is_array($itemIds) and !empty($itemIds)) {
                $itemIds = [$itemIds];
            }

            if (!empty($itemIds) and is_array($itemIds) and count($itemIds)) {
                switch ($tableName) {
                    case 'tickets':
                        foreach ($itemIds as $order => $id) {
                            Ticket::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'sessions':
                        foreach ($itemIds as $order => $id) {
                            Session::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'files':
                        foreach ($itemIds as $order => $id) {
                            File::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'text_lessons':
                        foreach ($itemIds as $order => $id) {
                            TextLesson::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'webinar_chapters':
                        foreach ($itemIds as $order => $id) {
                            WebinarChapter::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'webinar_chapter_items':
                        foreach ($itemIds as $order => $id) {
                            WebinarChapterItem::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                    case 'bundle_webinars':
                        foreach ($itemIds as $order => $id) {
                            BundleWebinar::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                     case 'subscription_webinar_chapter_items':
                        foreach ($itemIds as $order => $id) {
                            SubscriptionWebinarChapterItems::where('id', $id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                }
            }

            return response()->json([
                'title' => trans('public.request_success'),
                'msg' => trans('update.status_updated_successful')
            ]);
        } catch (\Exception $e) {
            \Log::error('orderItems error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 public function course($slug, $justReturnData = false)
    {
        try {
            $user = null;

            if (auth()->check()) {
                $user = auth()->user();
            }

            $subscription = Subscription::where('slug', $slug)
                ->with([
                    'tags',
                    'faqs' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },

                    'filterOptions',
                    'category',
                    'teacher',

                ])
                ->withCount([
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    },
                ])
                ->where('status', 'active')
                ->first();

            if (empty($subscription)) {
                return $justReturnData ? false : back();
            }

            if($subscription->private==1){
             if (!$justReturnData) {
                $contentLimitation = $this->checkContentLimitation($user, true);

                if ($contentLimitation != "ok") {
                    return $contentLimitation;
                }
            }
            }

            $hasBought = $subscription->checkUserHasBought($user, true, true);
            $isPrivate = $subscription->private;

            if (!empty($user) and ($user->id == $subscription->creator_id or $user->organ_id == $subscription->creator_id or $user->isAdmin())) {
                $isPrivate = false;
            }

            if ($isPrivate and $hasBought) {
                $isPrivate = false;
            }

            if ($isPrivate) {

                return $justReturnData ? false : back();
            }

            $isFavorite = false;

            $webinarContentCount = 0;
            if (!empty($subscription->sessions)) {
                $webinarContentCount += $subscription->sessions->count();
            }
            if (!empty($subscription->files)) {
                $webinarContentCount += $subscription->files->count();
            }
            if (!empty($subscription->textLessons)) {
                $webinarContentCount += $subscription->textLessons->count();
            }
            if (!empty($subscription->quizzes)) {
                $webinarContentCount += $subscription->quizzes->count();
            }
            if (!empty($subscription->assignments)) {
                $webinarContentCount += $subscription->assignments->count();
            }

            $chapterItems = SubscriptionWebinarChapterItems::with(['file', 'quiz'])
            ->where('subscription_id', $subscription->id)
                ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

            $data = [
                'pageH1' => $subscription->h1,
                'pageTitle' => $subscription->title,
                'pageDescription' => $subscription->seo_description,
                'pageRobot' => null,
                'course' => $subscription,
                'isFavorite' => $isFavorite,
                'hasBought' => $hasBought,
                'user' => $user,
                'webinarContentCount' => $webinarContentCount,
                'advertisingBanners' => null,
                'advertisingBannersSidebar' => null,
                'activeSpecialOffer' => $subscription->activeSpecialOffer(),

                'installments' => $installments ?? null,
                'cashbackRules' => $cashbackRules ?? null,

                 'chapterItems' => $chapterItems,

            ];

            if ($justReturnData) {
                return $data;
            }
            return $data;
        } catch (\Exception $e) {
            \Log::error('course error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    // Add this method at the end of WebinarController class
private function saveExtraDetails(Request $request, $subscriptionId)
{
    try {
        SubscriptionExtraDetails::updateOrCreate(
            ['subscription_id' => $subscriptionId],
            [
                'plan_type' => $request->plan_type,
                'plan_badge' => $request->plan_badge,
                'plan_price' => $request->plan_price,
                'price_suffix' => $request->price_suffix,
                'plan_duration' => $request->plan_duration,
                'plan_option' => $request->plan_option,
                'plan_cancel_text' => $request->plan_cancel_text,
                'comparison_text' => $request->comparison_text,
                'plan_icon' => $request->plan_icon,
                'is_featured' => $request->is_featured,
                'heading_main' => $request->heading_main,
                'heading_sub' => $request->heading_sub,
                'heading_extra' => $request->heading_extra,
                'additional_description' => $request->additional_description,
                'extra_description' => $request->extra_description,
                'subtitle' => $request->subtitle,
                'subdescription' => $request->subdescription,
                'material_text' => $request->material_text ? json_encode(array_filter($request->material_text)) : null,
                'material_icon' => $request->material_icon,
                'learn_text' => $request->learn_text ? json_encode(array_filter($request->learn_text)) : null,
                'price_icon' => $request->price_icon,
                'plan_movie' => $request->plan_movie,
                'learn_title' => $request->learn_title,
                'learn_description' => $request->learn_description,
                'learn_icon' => $request->learn_icon,
                'bonus_heading' => $request->bonus_heading,
                'bonus_icon' => $request->bonus_icon,
                'ad_title' => $request->ad_title,
                'ad_subtitle' => $request->ad_subtitle,
                'ad_description' => $request->ad_description,
                'ad_img' => $request->ad_img,
                'certification_time' => $request->certification_time ? json_encode(array_filter($request->certification_time)) : null,
                'certification_fomus' => $request->certification_fomus ? json_encode(array_filter($request->certification_fomus)) : null,
                'certification_outcome' => $request->certification_outcome ? json_encode(array_filter($request->certification_outcome)) : null,
                'rate_title' => $request->rate_title,
                'rate_options' => $request->rate_options ? json_encode(array_filter($request->rate_options)) : null,
                'rate_icon' => $request->rate_icon,
            ]
        );
    } catch (\Exception $e) {
        \Log::error('saveExtraDetails error: ' . $e->getMessage());
    }
}

}
