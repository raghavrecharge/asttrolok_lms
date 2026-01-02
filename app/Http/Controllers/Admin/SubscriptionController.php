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
    /**
     * Get validation rules
     */
    private function getValidationRules($id = null)
    {
        return [
            // Basic Information
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:subscriptions,slug,' . $id,
            'thumbnail' => 'required|string|max:500',
            'image_cover' => 'required|string|max:500',
            'description' => 'required|string',
            'seo_description' => 'nullable|string|max:500',
            'teacher_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id',
            'locale' => 'required|string|max:10',
            
            // Additional Information
            'access_days' => 'required|integer|min:1|max:36500', // Max 100 years
            'video_count' => 'required|integer|min:0|max:10000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'points' => 'nullable|integer|min:0',
            
            // Video Demo
            'video_demo' => 'nullable|string|max:500',
            'video_demo_source' => 'nullable|string|in:upload,youtube,vimeo,external_link',
            
            // Tags
            'tags' => 'nullable|string|max:500',
            
            // Filters
            'filters' => 'nullable|array',
            'filters.*' => 'integer|exists:filter_options,id',
            
            // Message
            'message_for_reviewer' => 'nullable|string|max:2000',
            
            // Main Content
            'subtitle' => 'nullable|string|max:255',
            'heading_main' => 'nullable|string|max:255',
            'heading_sub' => 'nullable|string|max:255',
            'subdescription' => 'nullable|string|max:1000',
            'additional_description' => 'nullable|string|max:2000',
            'is_featured' => 'nullable|string|max:500',
            
            // Material Items (4 items)
            'material_text' => 'nullable|array|max:4',
            'material_text.*' => 'nullable|string|max:500',
            'material_icon' => 'nullable|array|max:4',
            'material_icon.*' => 'nullable|string|max:500',
            
            // Comparison Plan
            'plan_type' => 'nullable|string|max:50',
            'plan_badge' => 'nullable|string|max:255',
            'plan_price' => 'nullable|numeric|min:0|max:999999.99',
            'price_suffix' => 'nullable|string|max:50',
            'plan_duration' => 'nullable|string|max:100',
            'plan_duration_option' => 'nullable|string|max:255',
            'plan_cancel_text' => 'nullable|string|max:255',
            'comparison_text' => 'nullable|string|max:2000',
            'plan_icon' => 'nullable|string|max:500',
            'plan_movie' => 'nullable|string|max:500',
            'price_icon' => 'nullable|string|max:500',
            
            // Learn Section (4 items)
            'learn_title' => 'nullable|string|max:255',
            'learn_description' => 'nullable|string|max:1000',
            'learn_text' => 'nullable|array|max:4',
            'learn_text.*' => 'nullable|string|max:500',
            'learn_icon' => 'nullable|array|max:4',
            'learn_icon.*' => 'nullable|string|max:500',
            
            // Bonus
            'bonus_heading' => 'nullable|string|max:255',
            'bonus_icon' => 'nullable|string|max:500',
            
            // Certification (3 items each)
            'certification_time' => 'nullable|array|max:3',
            'certification_time.*' => 'nullable|string|max:255',
            'certification_focus' => 'nullable|array|max:3',
            'certification_focus.*' => 'nullable|string|max:255',
            'certification_outcome' => 'nullable|array|max:3',
            'certification_outcome.*' => 'nullable|string|max:255',
            
            // Rating (5 items each)
            'rate_title' => 'nullable|string|max:255',
            'rate_options' => 'nullable|array|max:5',
            'rate_options.*' => 'nullable|string|max:255',
            'rate_icon' => 'nullable|array|max:5',
            'rate_icon.*' => 'nullable|string|max:500',
            
            // Advertisement
            'ad_title' => 'nullable|string|max:255',
            'ad_subtitle' => 'nullable|string|max:255',
            'ad_description' => 'nullable|string|max:2000',
        ];
    }
    
    /**
     * Get custom validation messages
     */
    private function getValidationMessages()
    {
        return [
            // Basic Information
            'title.required' => 'Title is required',
            'title.max' => 'Title cannot exceed 255 characters',
            'slug.unique' => 'This URL slug is already taken',
            'slug.max' => 'URL slug cannot exceed 255 characters',
            'thumbnail.required' => 'Thumbnail image is required',
            'image_cover.required' => 'Cover image is required',
            'description.required' => 'Description is required',
            'teacher_id.required' => 'Please select an instructor',
            'teacher_id.exists' => 'Selected instructor does not exist',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            
            // Additional Information
            'access_days.required' => 'Access days is required',
            'access_days.integer' => 'Access days must be a number',
            'access_days.min' => 'Access days must be at least 1',
            'access_days.max' => 'Access days cannot exceed 36500',
            'video_count.required' => 'Video count is required',
            'video_count.integer' => 'Video count must be a number',
            'video_count.min' => 'Video count cannot be negative',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price cannot be negative',
            'price.max' => 'Price is too large',
            
            // Array Items
            'material_text.array' => 'Material text must be an array',
            'material_text.max' => 'Maximum 4 material items allowed',
            'material_text.*.max' => 'Material text cannot exceed 500 characters',
            'material_icon.array' => 'Material icon must be an array',
            'material_icon.max' => 'Maximum 4 material icons allowed',
            'material_icon.*.max' => 'Material icon path cannot exceed 500 characters',
            
            'learn_text.array' => 'Learn text must be an array',
            'learn_text.max' => 'Maximum 4 learn items allowed',
            'learn_text.*.max' => 'Learn text cannot exceed 500 characters',
            'learn_icon.array' => 'Learn icon must be an array',
            'learn_icon.max' => 'Maximum 4 learn icons allowed',
            'learn_icon.*.max' => 'Learn icon path cannot exceed 500 characters',
            
            'certification_time.array' => 'Certification time must be an array',
            'certification_time.max' => 'Maximum 3 certification time periods allowed',
            'certification_time.*.max' => 'Certification time cannot exceed 255 characters',
            'certification_focus.array' => 'Certification focus must be an array',
            'certification_focus.max' => 'Maximum 3 certification focus areas allowed',
            'certification_focus.*.max' => 'Certification focus cannot exceed 255 characters',
            'certification_outcome.array' => 'Certification outcome must be an array',
            'certification_outcome.max' => 'Maximum 3 certification outcomes allowed',
            'certification_outcome.*.max' => 'Certification outcome cannot exceed 255 characters',
            
            'rate_options.array' => 'Rate options must be an array',
            'rate_options.max' => 'Maximum 5 rating options allowed',
            'rate_options.*.max' => 'Rating option cannot exceed 255 characters',
            'rate_icon.array' => 'Rate icon must be an array',
            'rate_icon.max' => 'Maximum 5 rating icons allowed',
            'rate_icon.*.max' => 'Rating icon path cannot exceed 500 characters',
            
            // Plan
            'plan_price.numeric' => 'Plan price must be a number',
            'plan_price.min' => 'Plan price cannot be negative',
        ];
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('admin_subscriptions_create');

            $this->validate($request, [
            // Basic Information
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscriptions,slug,',
            'thumbnail' => 'required|string|max:500',
            'image_cover' => 'required|string|max:500',
            'description' => 'required|string',
            'seo_description' => 'nullable|string|max:500',
            'teacher_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id',
            'locale' => 'required|string|max:10',
            
            // Additional Information
            'access_days' => 'required|integer|min:1|max:36500', // Max 100 years
            'video_count' => 'nullable|integer|min:0|max:10000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'points' => 'nullable|integer|min:0',
            
            // Video Demo
            'video_demo' => 'nullable|string|max:500',
            'video_demo_source' => 'nullable|string|in:upload,youtube,vimeo,external_link',
            
            // Tags
            'tags' => 'nullable|string|max:500',
            
            // Filters
            'filters' => 'nullable|array',
            'filters.*' => 'integer|exists:filter_options,id',
            
            // Message
            'message_for_reviewer' => 'nullable|string|max:2000',
            
            // Main Content
            'subtitle' => 'required|string|max:255',
            'heading_main' => 'nullable|string|max:255',
            'heading_sub' => 'nullable|string|max:255',
            'subdescription' => 'nullable|string|max:1000',
            'additional_description' => 'nullable|string|max:2000',
            'is_featured' => 'nullable|string|max:500',
            
            // Material Items (4 items)
            'material_text' => 'nullable|array|max:4',
            'material_text.*' => 'nullable|string|max:500',
            'material_icon' => 'nullable|array|max:4',
            'material_icon.*' => 'nullable|string|max:500',
            
            // Comparison Plan
            'plan_type' => 'nullable|string|max:50',
            'plan_badge' => 'nullable|string|max:255',
            'plan_price' => 'nullable|numeric|min:0|max:999999.99',
            'price_suffix' => 'nullable|string|max:50',
            'plan_duration' => 'nullable|string|max:100',
            'plan_duration_option' => 'nullable|string|max:255',
            'plan_cancel_text' => 'nullable|string|max:255',
            'comparison_text' => 'nullable|string|max:2000',
            'plan_icon' => 'nullable|string|max:500',
            'plan_movie' => 'nullable|string|max:500',
            'price_icon' => 'nullable|string|max:500',
            
            // Learn Section (4 items)
            'learn_title' => 'nullable|string|max:255',
            'learn_description' => 'nullable|string|max:1000',
            'learn_text' => 'nullable|array|max:4',
            'learn_text.*' => 'nullable|string|max:500',
            'learn_icon' => 'nullable|array|max:4',
            'learn_icon.*' => 'nullable|string|max:500',
            
            // Bonus
            'bonus_heading' => 'nullable|string|max:255',
            'bonus_icon' => 'nullable|string|max:500',
            
            // Certification (3 items each)
            'certification_time' => 'nullable|array|max:3',
            'certification_time.*' => 'nullable|string|max:255',
            'certification_focus' => 'nullable|array|max:3',
            'certification_focus.*' => 'nullable|string|max:255',
            'certification_outcome' => 'nullable|array|max:3',
            'certification_outcome.*' => 'nullable|string|max:255',
            
            // Rating (5 items each)
            'rate_title' => 'nullable|string|max:255',
            'rate_options' => 'nullable|array|max:5',
            'rate_options.*' => 'nullable|string|max:255',
            'rate_icon' => 'nullable|array|max:5',
            'rate_icon.*' => 'nullable|string|max:500',
            
            // Advertisement
            'ad_title' => 'nullable|string|max:255',
            'ad_subtitle' => 'nullable|string|max:255',
            'ad_description' => 'nullable|string|max:2000',
            'home_banner' => 'nullable|string|max:500',
            'home_view' => 'nullable|boolean',
            'risk_title' => 'nullable|string|max:255',
            'risk_description' => 'nullable|string|max:5000',
            'cta_text' => 'nullable|string|max:150',
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
                'home_banner' => $data['home_banner'],
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
                'home_view' => !empty($data['home_view']) ? 1 : 0,
                'status' => Subscription::$pending,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $subscription->extraDetails()->updateOrCreate(
            ['subscription_id' => $subscription->id],
            [
                
                // Main Content
                'subtitle' => $data['subtitle'] ?? null,
                'heading_main' => $data['heading_main'] ?? null,
                'heading_sub' => $data['heading_sub'] ?? null,
                'subdescription' => $data['subdescription'] ?? null,
                'additional_description' => $data['additional_description'] ?? null,
                'is_featured' => $data['is_featured'] ?? null,
                
                // Material Items - ✅ Pass arrays directly
                'material_text' => $data['material_text'] ?? [],
                'material_icon' => $data['material_icon'] ?? [],
                
                // Learn Section
                'learn_title' => $data['learn_title'] ?? null,
                'learn_description' => $data['learn_description'] ?? null,
                'learn_text' => $data['learn_text'] ?? [],
                'learn_icon' => $data['learn_icon'] ?? [],
                
                // Bonus
                'bonus_heading' => $data['bonus_heading'] ?? null,
                'bonus_icon' => $data['bonus_icon'] ?? null,
                
                // Comparison Plan
                'plan_type' => $data['plan_type'] ?? null,
                'plan_badge' => $data['plan_badge'] ?? null,
                'plan_price' => $data['plan_price'] ?? null,
                'price_suffix' => $data['price_suffix'] ?? null,
                'plan_duration' => $data['plan_duration'] ?? null,
                'plan_duration_option' => $data['plan_duration_option'] ?? null,
                'plan_cancel_text' => $data['plan_cancel_text'] ?? null,
                'comparison_text' => $data['comparison_text'] ?? null,
                'plan_icon' => $data['plan_icon'] ?? null,
                'plan_movie' => $data['plan_movie'] ?? null,
                'price_icon' => $data['price_icon'] ?? null,
                
                // Certification - ✅ Pass arrays directly
                'certification_time' => $data['certification_time'] ?? [],
                'certification_focus' => $data['certification_focus'] ?? [],
                'certification_outcome' => $data['certification_outcome'] ?? [],
                
                // Rating - ✅ Pass arrays directly
                'rate_title' => $data['rate_title'] ?? null,
                'rate_options' => $data['rate_options'] ?? [],
                'rate_icon' => $data['rate_icon'] ?? [],
                
                // Advertisement
                'ad_title' => $data['ad_title'] ?? null,
                'ad_subtitle' => $data['ad_subtitle'] ?? null,
                'ad_description' => $data['ad_description'] ?? null,
                'risk_title' => $data['risk_title'] ?? null,
                'risk_description' => $data['risk_description'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
            ]
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

            return redirect(getAdminPanelUrl() . '/subscriptions/' . $subscription->id . '/edit?locale=' . $data['locale'])->with('success', 'Subscription saved successfully');
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
            if (!empty($data['status']) and $data['status'] == 'active') {
              
            $publish = 'publish';

            }else {
                $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
                $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
                $publish = (!empty($data['draft']) and $data['draft'] == 'publish');
            }

            $rules = [
            // Basic Information
            'title' => 'required|string|max:255',
            'slug' => 'max:255|unique:subscriptions,slug,' . $subscription->id,
            'thumbnail' => 'required|string|max:500',
            'image_cover' => 'required|string|max:500',
            'home_banner' => 'nullable|string|max:500',
            'description' => 'required|string',
            'seo_description' => 'nullable|string|max:500',
            'teacher_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id',
            'locale' => 'required|string|max:10',
            
            // Additional Information
            'access_days' => 'required|integer|min:1|max:36500', // Max 100 years
            'video_count' => 'nullable|integer|min:0|max:10000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'points' => 'nullable|integer|min:0',
            
            // Video Demo
            'video_demo' => 'nullable|string|max:500',
            'video_demo_source' => 'nullable|string|in:upload,youtube,vimeo,external_link',
            
            // Tags
            'tags' => 'nullable|string|max:500',
            
            // Filters
            'filters' => 'nullable|array',
            'filters.*' => 'integer|exists:filter_options,id',
            
            // Message
            'message_for_reviewer' => 'nullable|string|max:2000',
            
            // Main Content
            'subtitle' => 'required|string|max:255',
            'heading_main' => 'nullable|string|max:255',
            'heading_sub' => 'nullable|string|max:255',
            'subdescription' => 'nullable|string|max:1000',
            'additional_description' => 'nullable|string|max:2000',
            'is_featured' => 'nullable|string|max:500',
            
            // Material Items (4 items)
            'material_text' => 'nullable|array|max:4',
            'material_text.*' => 'nullable|string|max:500',
            'material_icon' => 'nullable|array|max:4',
            'material_icon.*' => 'nullable|string|max:500',
            
            // Comparison Plan
            'plan_type' => 'nullable|string|max:50',
            'plan_badge' => 'nullable|string|max:255',
            'plan_price' => 'nullable|numeric|min:0|max:999999.99',
            'price_suffix' => 'nullable|string|max:50',
            'plan_duration' => 'nullable|string|max:100',
            'plan_duration_option' => 'nullable|string|max:255',
            'plan_cancel_text' => 'nullable|string|max:255',
            'comparison_text' => 'nullable|string|max:2000',
            'plan_icon' => 'nullable|string|max:500',
            'plan_movie' => 'nullable|string|max:500',
            'price_icon' => 'nullable|string|max:500',
            
            // Learn Section (4 items)
            'learn_title' => 'nullable|string|max:255',
            'learn_description' => 'nullable|string|max:1000',
            'learn_text' => 'nullable|array|max:4',
            'learn_text.*' => 'nullable|string|max:500',
            'learn_icon' => 'nullable|array|max:4',
            'learn_icon.*' => 'nullable|string|max:500',
            
            // Bonus
            'bonus_heading' => 'nullable|string|max:255',
            'bonus_icon' => 'nullable|string|max:500',
            
            // Certification (3 items each)
            'certification_time' => 'nullable|array|max:3',
            'certification_time.*' => 'nullable|string|max:255',
            'certification_focus' => 'nullable|array|max:3',
            'certification_focus.*' => 'nullable|string|max:255',
            'certification_outcome' => 'nullable|array|max:3',
            'certification_outcome.*' => 'nullable|string|max:255',
            
            // Rating (5 items each)
            'rate_title' => 'nullable|string|max:255',
            'rate_options' => 'nullable|array|max:5',
            'rate_options.*' => 'nullable|string|max:255',
            'rate_icon' => 'nullable|array|max:5',
            'rate_icon.*' => 'nullable|string|max:500',
            
            // Advertisement
            'ad_title' => 'nullable|string|max:255',
            'ad_subtitle' => 'nullable|string|max:255',
            'ad_description' => 'nullable|string|max:2000',
            'home_view' => 'nullable|boolean',
            'risk_title' => 'nullable|string|max:255',
            'risk_description' => 'nullable|string|max:5000',
            'cta_text' => 'nullable|string|max:150',
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
                'title' => $request->title,
                'slug' => $request->slug,
                'teacher_id' => $request->teacher_id,
                'thumbnail' => $request->thumbnail,
                'image_cover' => $request->image_cover,
                'home_banner' => $request->home_banner,
                'video_demo' => $request->video_demo,
                'video_demo_source' => $request->video_demo_source,
                'description' => $request->description,
                'seo_description' => $request->seo_description,
                'access_days' => $request->access_days,
                'video_count' => $request->video_count,
                'category_id' => $request->category_id,
                'message_for_reviewer' => $request->message_for_reviewer,
                'status' => $data['status'],
                'home_view' => !empty($data['home_view']) ? 1 : 0,
                'price' => $data['price'] ?? null,

            ]);

         $subscription->extraDetails()->updateOrCreate(
            ['subscription_id' => $subscription->id],
            [
                
                // Main Content
                'subtitle' => $data['subtitle'] ?? null,
                'heading_main' => $data['heading_main'] ?? null,
                'heading_sub' => $data['heading_sub'] ?? null,
                'subdescription' => $data['subdescription'] ?? null,
                'additional_description' => $data['additional_description'] ?? null,
                'is_featured' => $data['is_featured'] ?? null,
                
                // Material Items - ✅ Pass arrays directly
                'material_text' => $data['material_text'] ?? [],
                'material_icon' => $data['material_icon'] ?? [],
                
                // Learn Section
                'learn_title' => $data['learn_title'] ?? null,
                'learn_description' => $data['learn_description'] ?? null,
                'learn_text' => $data['learn_text'] ?? [],
                'learn_icon' => $data['learn_icon'] ?? [],
                
                // Bonus
                'bonus_heading' => $data['bonus_heading'] ?? null,
                'bonus_icon' => $data['bonus_icon'] ?? null,
                
                // Comparison Plan
                'plan_type' => $data['plan_type'] ?? null,
                'plan_badge' => $data['plan_badge'] ?? null,
                'plan_price' => $data['plan_price'] ?? null,
                'price_suffix' => $data['price_suffix'] ?? null,
                'plan_duration' => $data['plan_duration'] ?? null,
                'plan_duration_option' => $data['plan_duration_option'] ?? null,
                'plan_cancel_text' => $data['plan_cancel_text'] ?? null,
                'comparison_text' => $data['comparison_text'] ?? null,
                'plan_icon' => $data['plan_icon'] ?? null,
                'plan_movie' => $data['plan_movie'] ?? null,
                'price_icon' => $data['price_icon'] ?? null,
                
                // Certification - ✅ Pass arrays directly
                'certification_time' => $data['certification_time'] ?? [],
                'certification_focus' => $data['certification_focus'] ?? [],
                'certification_outcome' => $data['certification_outcome'] ?? [],
                
                // Rating - ✅ Pass arrays directly
                'rate_title' => $data['rate_title'] ?? null,
                'rate_options' => $data['rate_options'] ?? [],
                'rate_icon' => $data['rate_icon'] ?? [],
                
                // Advertisement
                'ad_title' => $data['ad_title'] ?? null,
                'ad_subtitle' => $data['ad_subtitle'] ?? null,
                'ad_description' => $data['ad_description'] ?? null,
                'risk_title' => $data['risk_title'] ?? null,
                'risk_description' => $data['risk_description'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
            ]
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

            return redirect()->back()->with('success', 'Subscription updated successfully');
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
