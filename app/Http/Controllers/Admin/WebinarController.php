<?php

namespace App\Http\Controllers\Admin;
use App\Exports\WebinarsExport;
use App\Http\Controllers\Admin\traits\WebinarChangeCreator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\BundleWebinar;
use App\Models\Category;
use App\Models\Faq;
use App\Models\File;
use App\Models\Gift;
use Illuminate\Support\Facades\Log;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Notification;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\SpecialOffer;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\Translation\WebinarTranslation;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\Models\WebinarExtraDescription;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use App\User;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WebinarExtraDetails;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Models\CourseProgress;
use Illuminate\Validation\Rule;
use Cviebrock\EloquentSluggable\Services\SlugService;

class WebinarController extends Controller
{
    use WebinarChangeCreator;

    public function index(Request $request)
    {
        try {
            $this->authorize('admin_webinars_list');

            removeContentLocale();

            $type = $request->get('type', 'webinar');
            $query = Webinar::where('webinars.type', $type);

            $totalWebinars = $query->count();
            $totalPendingWebinars = deepClone($query)->where('webinars.status', 'pending')->count();
            $totalDurations = deepClone($query)->sum('duration');
            $totalSales = deepClone($query)->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                ->select(DB::raw('count(sales.webinar_id) as sales_count'))
                ->whereNotNull('sales.webinar_id')
                ->whereNull('sales.refund_at')
                ->first();

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $inProgressWebinars = 0;
            if ($type == 'webinar') {
                $inProgressWebinars = $this->getInProgressWebinarsCount();
            }

            $query = $this->filterWebinar($query, $request)
                ->with([
                    'category',
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    },
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    }
                ]);

            $webinars = $query->paginate(10);

            if ($request->get('status', null) == 'active_finished') {
                foreach ($webinars as $key => $webinar) {
                    if ($webinar->last_date > time()) {
                        unset($webinars[$key]);
                    }
                }
            }

            foreach ($webinars as $webinar) {
                $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $sales = Sale::query()
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('webinar_id', $webinar->id);
                        $query->orWhereIn('gift_id', $giftsIds);
                    })
                    ->whereNull('refund_at')
                    ->get();

                $webinar->sales = $sales;
            }

            $data = [
                'pageTitle' => trans('admin/pages/webinars.webinars_list_page_title'),
                'webinars' => $webinars,
                'totalWebinars' => $totalWebinars,
                'totalPendingWebinars' => $totalPendingWebinars,
                'totalDurations' => $totalDurations,
                'totalSales' => !empty($totalSales) ? $totalSales->sales_count : 0,
                'categories' => $categories,
                'inProgressWebinars' => $inProgressWebinars,
                'classesType' => $type,
            ];

            $teacher_ids = $request->get('teacher_ids', null);
            if (!empty($teacher_ids)) {
                $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
            }

            return view('admin.webinars.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function filterWebinar($query, $request)
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
            $time = time();

            switch ($status) {
                case 'active_not_conducted':
                    $query->where('webinars.status', 'active')
                        ->where('start_date', '>', $time);
                    break;
                case 'active_in_progress':
                    $query->where('webinars.status', 'active')
                        ->where('start_date', '<=', $time)
                        ->join('sessions', 'webinars.id', '=', 'sessions.webinar_id')
                        ->select('webinars.*', 'sessions.date', DB::raw('max(`date`) as last_date'))
                        ->groupBy('sessions.webinar_id')
                        ->where('sessions.date', '>', $time);
                    break;
                case 'active_finished':
                    $query->where('webinars.status', 'active')
                        ->where('start_date', '<=', $time)
                        ->join('sessions', 'webinars.id', '=', 'sessions.webinar_id')
                        ->select('webinars.*', 'sessions.date', DB::raw('max(`date`) as last_date'))
                        ->groupBy('sessions.webinar_id');
                    break;
                default:
                    $query->where('webinars.status', $status);
                    break;
            }
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $webinarIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $webinarIdsHasDiscount[] = $ticket->webinar_id;
                        }
                    }

                    $specialOffersWebinarIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('webinar_id')
                        ->toArray();

                    $webinarIdsHasDiscount = array_merge($specialOffersWebinarIds, $webinarIdsHasDiscount);

                    $query->whereIn('id', $webinarIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.webinar_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.webinar_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
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

                case 'public_courses':
                    $query->where('private', false);
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'courses_private':
                    $query->where('private', true);
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getInProgressWebinarsCount()
    {
        $count = 0;
        $webinars = Webinar::where('type', 'webinar')
            ->where('status', 'active')
            ->where('start_date', '<=', time())
            ->whereHas('sessions')
            ->get();

        foreach ($webinars as $webinar) {
            if ($webinar->isProgressing()) {
                $count += 1;
            }
        }

        return $count;
    }

    public function create()
    {
        try {
            $this->authorize('admin_webinars_create');

            removeContentLocale();

            $teachers = User::where('role_name', Role::$teacher)->get();
            $categories = Category::where('parent_id', null)->get();

            $data = [
                'pageTitle' => trans('admin/main.webinar_new_page_title'),
                'teachers' => $teachers,
                'categories' => $categories
            ];

            return view('admin.webinars.create', $data);
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
            $this->authorize('admin_webinars_create');

            // Validation rules
            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'slug' => 'nullable|max:255|unique:webinars,slug',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
                'teacher_id' => 'required|exists:users,id',
                'category_id' => 'required|exists:categories,id',
                'duration' => 'required|numeric',
                'lang' => 'required|in:EN,HI',
                'h1' => 'nullable|string|max:255',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:500',
                'points' => 'nullable|numeric',
                'capacity' => 'nullable|integer',
                'price' => 'nullable|numeric',
                'organization_price' => 'nullable|numeric',
                'access_days' => 'nullable|integer',
                'order' => 'nullable|integer',
                
                // Extra Details - Main Content
                'subtitle' => 'nullable|string|max:255',
                'heading_main' => 'nullable|string|max:255',
                'heading_sub' => 'nullable|string|max:255',
                'subdescription' => 'nullable|string',
                'additional_description' => 'nullable|string',
                'is_featured' => 'nullable|string',
                'free_content_thumbnail' => 'nullable|string',
                
                // Material Items
                'material_icon' => 'nullable|array|max:4',
                'material_icon.*' => 'nullable|string',
                'material_text' => 'nullable|array|max:4',
                'material_text.*' => 'nullable|string|max:255',
                
                // Comparison Plan
                'plan_duration' => 'nullable|string|max:100',
                'plan_type' => 'nullable|string|max:100',
                'plan_price' => 'nullable|string|max:100',
                'plan_cancel_text' => 'nullable|string|max:255',
                'plan_movie' => 'nullable|string',
                'plan_duration_option' => 'nullable|string|max:100',
                'price_suffix' => 'nullable|string|max:50',
                'plan_badge' => 'nullable|string|max:100',
                'plan_icon' => 'nullable|string',
                'price_icon' => 'nullable|string',
                'comparison_text' => 'nullable|string',
                
                // Learn Section
                'learn_title' => 'nullable|string|max:255',
                'learn_description' => 'nullable|string',
                'learn_icon' => 'nullable|array|max:4',
                'learn_icon.*' => 'nullable|string',
                'learn_text' => 'nullable|array|max:4',
                'learn_text.*' => 'nullable|string|max:255',
                
                // Bonus
                'bonus_heading' => 'nullable|string|max:255',
                'bonus_icon' => 'nullable|string',
                
                // Certification Roadmap
                'certification_time' => 'nullable|array|max:3',
                'certification_time.*' => 'nullable|string|max:100',
                'certification_focus' => 'nullable|array|max:3',
                'certification_focus.*' => 'nullable|string|max:255',
                'certification_outcome' => 'nullable|array|max:3',
                'certification_outcome.*' => 'nullable|string|max:255',
                
                // Rating
                'rate_title' => 'nullable|string|max:255',
                'rate_options' => 'nullable|array|max:5',
                'rate_options.*' => 'nullable|string|max:255',
                'rate_icon' => 'nullable|array|max:5',
                'rate_icon.*' => 'nullable|string',
                
                // Advertisement
                'ad_subtitle' => 'nullable|string|max:255',
                'ad_title' => 'nullable|string|max:255',
                'ad_description' => 'nullable|string',
            ];

            // Additional rules based on type
            if ($request->input('type') == 'webinar') {
                $rules['start_date'] = 'required|date';
                $rules['capacity'] = 'required|integer';
            }

            $this->validate($request, $rules);

            $data = $request->all();

            // Handle start_date
            if ($data['type'] != Webinar::$webinar && $data['type'] != 'course') {
                $data['start_date'] = null;
            }

            if (!empty($data['start_date']) && ($data['type'] == Webinar::$webinar || $data['type'] == 'course')) {
                if (empty($data['timezone']) || !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }

                $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);
                $data['start_date'] = $startDate->getTimestamp();
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Webinar::makeSlug($data['title']);
            }

            // Handle video demo
            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) && !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }

            // Handle order
            if (!empty($data['order'])) {
                $order = $data['order'];
            } else {
                $lastOrder = Webinar::orderBy('order', 'desc')->first();
                $order = $lastOrder ? $lastOrder->order + 1 : 1;
            }

            // Convert prices
            $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
            $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;

            // Set default values for arrays if empty
            $defaultMaterialIcons = [
                '/public/public/vector1660-2i9i.svg',
                '/public/public/Vector (2).png',
                '/public/public/Vector (3).png',
                '/public/public/vector1661-4d1k.svg',
            ];

            $defaultLearnIcons = [
                '/public/public/public/public/backgroundborder2863-oxt.svg',
                '/public/public/svg2863-o6p.svg',
                '/public/public/backgroundborder2863-j91r.svg',
                '/public/public/backgroundborder2863-1wk.svg',
            ];

            $data['material_icon'] = !empty($data['material_icon']) && count(array_filter($data['material_icon'])) > 0 
                ? $data['material_icon'] 
                : $defaultMaterialIcons;

            $data['learn_icon'] = !empty($data['learn_icon']) && count(array_filter($data['learn_icon'])) > 0 
                ? $data['learn_icon'] 
                : $defaultLearnIcons;

            // Create webinar
            $webinar = Webinar::create([
                'type' => $data['type'],
                'slug' => $data['slug'],
                'teacher_id' => $data['teacher_id'],
                'lang' => $data['lang'],
                'creator_id' => $data['teacher_id'],
                'thumbnail' => $data['thumbnail'],
                'image_cover' => $data['image_cover'],
                'video_demo' => $data['video_demo'] ?? null,
                'video_demo_source' => $data['video_demo'] ? ($data['video_demo_source'] ?? 'upload') : null,
                'capacity' => $data['capacity'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'timezone' => $data['timezone'] ?? null,
                'duration' => $data['duration'] ?? null,
                'support' => !empty($data['support']),
                'certificate' => !empty($data['certificate']),
                'downloadable' => !empty($data['downloadable']),
                'partner_instructor' => !empty($data['partner_instructor']),
                'subscribe' => !empty($data['subscribe']),
                'private' => !empty($data['private']),
                'forum' => !empty($data['forum']),
                'enable_waitlist' => !empty($data['enable_waitlist']),
                'access_days' => $data['access_days'] ?? null,
                'price' => $data['price'],
                'organization_price' => $data['organization_price'] ?? null,
                'points' => $data['points'] ?? null,
                'category_id' => $data['category_id'],
                'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
                'status' => Webinar::$pending,
                'created_at' => time(),
                'updated_at' => time(),
                'order' => $order,
                'course_rate' => $data['course_rate'] ?? 4.5,
            ]);

            if (!$webinar) {
                throw new \Exception('Failed to create webinar');
            }

            // Create translation
            WebinarTranslation::create([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale'] ?? app()->getLocale()),
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'] ?? '',
                'seo_title' => $data['seo_title'] ?? '',
                'h1' => $data['h1'] ?? ''
            ]);

            // Create extra details
            $webinar->extraDetails()->create([
                // Main Content
                'subtitle' => $data['subtitle'] ?? null,
                'heading_main' => $data['heading_main'] ?? null,
                'heading_sub' => $data['heading_sub'] ?? null,
                'subdescription' => $data['subdescription'] ?? null,
                'additional_description' => $data['additional_description'] ?? null,
                'is_featured' => $data['is_featured'] ?? null,
                'free_content_thumbnail' => $data['free_content_thumbnail'] ?? null,
                
                // Material Items
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
                'plan_duration' => $data['plan_duration'] ?? null,
                'price_suffix' => $data['price_suffix'] ?? null,
                'plan_duration_option' => $data['plan_duration_option'] ?? null,
                'plan_cancel_text' => $data['plan_cancel_text'] ?? null,
                'comparison_text' => $data['comparison_text'] ?? null,
                'plan_icon' => $data['plan_icon'] ?? '/public/public/vector1615-24vi.svg',
                'plan_movie' => $data['plan_movie'] ?? null,
                'price_icon' => $data['price_icon'] ?? null,
                
                // Certification
                'certification_time' => $data['certification_time'] ?? [],
                'certification_focus' => $data['certification_focus'] ?? [],
                'certification_outcome' => $data['certification_outcome'] ?? [],
                
                // Rating
                'rate_title' => $data['rate_title'] ?? null,
                'rate_options' => $data['rate_options'] ?? [],
                'rate_icon' => $data['rate_icon'] ?? [],
                
                // Advertisement
                'ad_title' => $data['ad_title'] ?? null,
                'ad_subtitle' => $data['ad_subtitle'] ?? null,
                'ad_description' => $data['ad_description'] ?? null,
            ]);

            // Handle filters
            $filters = $request->get('filters', null);
            if (!empty($filters) && is_array($filters)) {
                foreach ($filters as $filter) {
                    WebinarFilterOption::create([
                        'webinar_id' => $webinar->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            // Handle tags
            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                foreach ($tags as $tag) {
                    Tag::create([
                        'webinar_id' => $webinar->id,
                        'title' => trim($tag),
                    ]);
                }
            }

            // Handle partner instructors
            if (!empty($request->get('partner_instructor')) && !empty($request->get('partners'))) {
                foreach ($request->get('partners') as $partnerId) {
                    WebinarPartnerTeacher::create([
                        'webinar_id' => $webinar->id,
                        'teacher_id' => $partnerId,
                    ]);
                }
            }

            Log::channel('activity')->info('Course created', [
                'course_id' => $webinar->id,
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('webinars.success_store'),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl() . '/webinars/' . $webinar->id . '/edit?locale=' . ($data['locale'] ?? app()->getLocale()))
                ->with(['toast' => $toastData]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Webinar store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('public.system_error') . ': ' . $e->getMessage(),
                'status' => 'error'
            ];

            return back()
                ->with(['toast' => $toastData])
                ->withInput();
        }
    }

    /**
     * Update existing webinar
     */
  public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $webinar = Webinar::findOrFail($id);
            $data = $request->all();

            $isDraft = (!empty($data['draft']) && $data['draft'] == 1);
            $reject = (!empty($data['draft']) && $data['draft'] == 'reject');
            $publish = (!empty($data['draft']) && $data['draft'] == 'publish');

            // Validation rules
            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'slug' => [
                    'nullable',
                    'max:255',
                    Rule::unique('webinars')->ignore($webinar->id)
                ],
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
                'teacher_id' => 'required|exists:users,id',
                'category_id' => 'required|exists:categories,id',
                'lang' => 'required|in:EN,HI',
                'h1' => 'nullable|string|max:255',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:500',
                'points' => 'nullable|numeric',
                'capacity' => 'nullable|integer',
                'price' => 'nullable|numeric',
                'organization_price' => 'nullable|numeric',
                'access_days' => 'nullable|integer',
                'order' => 'nullable|integer',
                
                // Extra Details - Main Content
                'subtitle' => 'nullable|string|max:255',
                'heading_main' => 'nullable|string|max:255',
                'heading_sub' => 'nullable|string|max:255',
                'subdescription' => 'nullable|string',
                'additional_description' => 'nullable|string',
                'is_featured' => 'nullable|string',
                'free_content_thumbnail' => 'nullable|string',
                
                // Material Items
                'material_icon' => 'nullable|array|max:4',
                'material_icon.*' => 'nullable|string',
                'material_text' => 'nullable|array|max:4',
                'material_text.*' => 'nullable|string|max:255',
                
                // Comparison Plan
                'plan_duration' => 'nullable|string|max:100',
                'plan_type' => 'nullable|string|max:100',
                'plan_price' => 'nullable|string|max:100',
                'plan_cancel_text' => 'nullable|string|max:255',
                'plan_movie' => 'nullable|string',
                'plan_duration_option' => 'nullable|string|max:100',
                'price_suffix' => 'nullable|string|max:50',
                'plan_badge' => 'nullable|string|max:100',
                'plan_icon' => 'nullable|string',
                'price_icon' => 'nullable|string',
                'comparison_text' => 'nullable|string',
                
                // Learn Section
                'learn_title' => 'nullable|string|max:255',
                'learn_description' => 'nullable|string',
                'learn_icon' => 'nullable|array|max:4',
                'learn_icon.*' => 'nullable|string',
                'learn_text' => 'nullable|array|max:4',
                'learn_text.*' => 'nullable|string|max:255',
                
                // Bonus
                'bonus_heading' => 'nullable|string|max:255',
                'bonus_icon' => 'nullable|string',
                
                // Certification Roadmap
                'certification_time' => 'nullable|array|max:3',
                'certification_time.*' => 'nullable|string|max:100',
                'certification_focus' => 'nullable|array|max:3',
                'certification_focus.*' => 'nullable|string|max:255',
                'certification_outcome' => 'nullable|array|max:3',
                'certification_outcome.*' => 'nullable|string|max:255',
                
                // Rating
                'rate_title' => 'nullable|string|max:255',
                'rate_options' => 'nullable|array|max:5',
                'rate_options.*' => 'nullable|string|max:255',
                'rate_icon' => 'nullable|array|max:5',
                'rate_icon.*' => 'nullable|string',
                
                // Advertisement
                'ad_subtitle' => 'nullable|string|max:255',
                'ad_title' => 'nullable|string|max:255',
                'ad_description' => 'nullable|string',
            ];

            if ($webinar->isWebinar()) {
                $rules['start_date'] = 'required|date';
                $rules['duration'] = 'required|numeric';
                $rules['capacity'] = 'required|integer';
            }

            $this->validate($request, $rules);

            // Validate teacher
            if (!empty($data['teacher_id'])) {
                $teacher = User::find($data['teacher_id']);
                $creator = $webinar->creator;

                if (empty($teacher) || ($creator->isOrganization() && ($teacher->organ_id != $creator->id && $teacher->id != $creator->id))) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('admin/main.is_not_the_teacher_of_this_organization'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData])->withInput();
                }
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Webinar::makeSlug($data['title']);
            }

            // Determine status
            $data['status'] = $publish ? Webinar::$active : ($reject ? Webinar::$inactive : ($isDraft ? Webinar::$isDraft : Webinar::$pending));
            $data['updated_at'] = time();

            // Handle start_date
            if (!empty($data['start_date']) && ($data['type'] == Webinar::$webinar || $data['type'] == 'course')) {
                if (empty($data['timezone']) || !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }

                $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);
                $data['start_date'] = $startDate->getTimestamp();
            } else {
                $data['start_date'] = null;
            }

            // Handle boolean fields
            $data['support'] = !empty($data['support']);
            $data['certificate'] = !empty($data['certificate']);
            $data['downloadable'] = !empty($data['downloadable']);
            $data['partner_instructor'] = !empty($data['partner_instructor']);
            $data['subscribe'] = !empty($data['subscribe']);
            $data['forum'] = !empty($data['forum']);
            $data['private'] = !empty($data['private']);
            $data['enable_waitlist'] = !empty($data['enable_waitlist']);

            // Handle partner instructor
            if (empty($data['partner_instructor'])) {
                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
            }

            // Handle category change
            if ($data['category_id'] !== $webinar->category_id) {
                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            }

            // Handle filters
            $filters = $request->get('filters', null);
            if (!empty($filters) && is_array($filters)) {
                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
                foreach ($filters as $filter) {
                    WebinarFilterOption::create([
                        'webinar_id' => $webinar->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            // Handle tags
            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                Tag::where('webinar_id', $webinar->id)->delete();

                foreach ($tags as $tag) {
                    Tag::create([
                        'webinar_id' => $webinar->id,
                        'title' => trim($tag),
                    ]);
                }
            }

            // Handle partner instructors
            if (!empty($request->get('partner_instructor')) && !empty($request->get('partners'))) {
                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();

                foreach ($request->get('partners') as $partnerId) {
                    WebinarPartnerTeacher::create([
                        'webinar_id' => $webinar->id,
                        'teacher_id' => $partnerId,
                    ]);
                }
            }

            // Handle video demo
            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) && !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }

            // Set default values for arrays if empty
            $defaultMaterialIcons = [
                '/public/public/vector1660-2i9i.svg',
                '/public/public/Vector (2).png',
                '/public/public/Vector (3).png',
                '/public/public/vector1661-4d1k.svg',
            ];

            $defaultLearnIcons = [
                '/public/public/public/public/backgroundborder2863-oxt.svg',
                '/public/public/svg2863-o6p.svg',
                '/public/public/backgroundborder2863-j91r.svg',
                '/public/public/backgroundborder2863-1wk.svg',
            ];

            $emptyArray = [];

            $data['material_icon'] = !empty($data['material_icon']) && count(array_filter($data['material_icon'])) > 0 
                ? $data['material_icon'] 
                : $defaultMaterialIcons;

            $data['material_text'] = !empty($data['material_text']) && count(array_filter($data['material_text'])) > 0 
                ? $data['material_text'] 
                : $emptyArray;

            $data['learn_icon'] = !empty($data['learn_icon']) && count(array_filter($data['learn_icon'])) > 0 
                ? $data['learn_icon'] 
                : $defaultLearnIcons;

            $data['learn_text'] = !empty($data['learn_text']) && count(array_filter($data['learn_text'])) > 0 
                ? $data['learn_text'] 
                : $emptyArray;

            $data['certification_time'] = !empty($data['certification_time']) && count(array_filter($data['certification_time'])) > 0 
                ? $data['certification_time'] 
                : $emptyArray;

            $data['certification_focus'] = !empty($data['certification_focus']) && count(array_filter($data['certification_focus'])) > 0 
                ? $data['certification_focus'] 
                : $emptyArray;

            $data['certification_outcome'] = !empty($data['certification_outcome']) && count(array_filter($data['certification_outcome'])) > 0 
                ? $data['certification_outcome'] 
                : $emptyArray;

            $data['rate_options'] = !empty($data['rate_options']) && count(array_filter($data['rate_options'])) > 0 
                ? $data['rate_options'] 
                : $emptyArray;

            $data['rate_icon'] = !empty($data['rate_icon']) && count(array_filter($data['rate_icon'])) > 0 
                ? $data['rate_icon'] 
                : $emptyArray;

            // Determine creator
            $newCreatorId = !empty($data['organ_id']) ? $data['organ_id'] : $data['teacher_id'];
            $changedCreator = ($webinar->creator_id != $newCreatorId);

            // Convert prices
            $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
            $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;
            // Update webinar
            $webinar->update([
                'slug' => $data['slug'],
                'creator_id' => $newCreatorId,
                'lang' => $data['lang'],
                'teacher_id' => $data['teacher_id'],
                'type' => $data['type'],
                'thumbnail' => $data['thumbnail'],
                'free_content_thumbnail' => $data['free_content_thumbnail'],
                'image_cover' => $data['image_cover'],
                'video_demo' => $data['video_demo'] ?? null,
                'video_demo_source' => $data['video_demo'] ? ($data['video_demo_source'] ?? 'upload') : null,
                'capacity' => $data['capacity'] ?? null,
                'start_date' => $data['start_date'],
                'timezone' => $data['timezone'] ?? null,
                'duration' => $data['duration'] ?? null,
                'support' => $data['support'],
                'certificate' => $data['certificate'],
                'private' => $data['private'],
                'enable_waitlist' => $data['enable_waitlist'],
                'downloadable' => $data['downloadable'],
                'partner_instructor' => $data['partner_instructor'],
                'subscribe' => $data['subscribe'],
                'forum' => $data['forum'],
                'access_days' => $data['access_days'] ?? null,
                'price' => $data['price'],
                'organization_price' => $data['organization_price'] ?? null,
                'category_id' => $data['category_id'],
                'points' => $data['points'] ?? null,
                'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
                'status' => $data['status'],
                'updated_at' => time(),
                'order' => $data['order'] ?? $webinar->order,
                'course_rate' => $data['course_rate'] ?? $webinar->course_rate,
            ]);

            // Update or create extra details
            $webinar->extraDetails()->updateOrCreate(
                ['webinar_id' => $webinar->id],
                [
                    // Main Content
                    'subtitle' => $data['subtitle'] ?? null,
                    'heading_main' => $data['heading_main'] ?? null,
                    'heading_sub' => $data['heading_sub'] ?? null,
                    'subdescription' => $data['subdescription'] ?? null,
                    'additional_description' => $data['additional_description'] ?? null,
                    'is_featured' => $data['is_featured'] ?? null,
                    
                    // Material Items
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
                    'plan_duration' => $data['plan_duration'] ?? null,
                    'price_suffix' => $data['price_suffix'] ?? null,
                    'plan_duration_option' => $data['plan_duration_option'] ?? null,
                    'plan_cancel_text' => $data['plan_cancel_text'] ?? null,
                    'comparison_text' => $data['comparison_text'] ?? null,
                    'plan_icon' => $data['plan_icon'] ?? '/public/public/vector1615-24vi.svg',
                    'plan_movie' => $data['plan_movie'] ?? null,
                    'price_icon' => $data['price_icon'] ?? null,
                    
                    // Certification
                    'certification_time' => $data['certification_time'] ?? [],
                    'certification_focus' => $data['certification_focus'] ?? [],
                    'certification_outcome' => $data['certification_outcome'] ?? [],
                    
                    // Rating
                    'rate_title' => $data['rate_title'] ?? null,
                    'rate_options' => $data['rate_options'] ?? [],
                    'rate_icon' => $data['rate_icon'] ?? [],
                    
                    // Advertisement
                    'ad_title' => $data['ad_title'] ?? null,
                    'ad_subtitle' => $data['ad_subtitle'] ?? null,
                    'ad_description' => $data['ad_description'] ?? null,
                ]
            );

            // Update translation
            WebinarTranslation::updateOrCreate(
                [
                    'webinar_id' => $webinar->id,
                    'locale' => mb_strtolower($data['locale'] ?? app()->getLocale()),
                ],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'seo_description' => $data['seo_description'] ?? '',
                    'seo_title' => $data['seo_title'] ?? '',
                    'h1' => $data['h1'] ?? ''
                ]
            );

            // Handle notifications
            if ($publish) {
                sendNotification('course_approve', ['[c.title]' => $webinar->title], $webinar->teacher_id);

                $createClassesReward = RewardAccounting::calculateScore(Reward::CREATE_CLASSES);
                RewardAccounting::makeRewardAccounting(
                    $webinar->creator_id,
                    $createClassesReward,
                    Reward::CREATE_CLASSES,
                    $webinar->id,
                    true
                );
            } elseif ($reject) {
                sendNotification('course_reject', ['[c.title]' => $webinar->title], $webinar->teacher_id);
            }

            // Handle creator change
            if ($changedCreator) {
                $this->webinarChangedCreator($webinar);
            }

            Log::channel('activity')->info('Course updated', [
                'course_id' => $webinar->id,
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            removeContentLocale();

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('public.updated_successfully'),
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Webinar update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'webinar_id' => $id
            ]);

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('public.system_error') . ': ' . $e->getMessage(),
                'status' => 'error'
            ];

            return back()
                ->with(['toast' => $toastData])
                ->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $webinar = Webinar::where('id', $id)
                ->with([
                    'tickets',
                    'sessions',
                    'files',
                     'extraDetails',
                    'faqs',
                    'category' => function ($query) {
                        $query->with(['filters' => function ($query) {
                            $query->with('options');
                        }]);
                    },
                    'filterOptions',
                    'prerequisites',
                    'quizzes' => function ($query) {
                        $query->with([
                            'quizQuestions' => function ($query) {
                                $query->orderBy('order', 'asc');
                            }
                        ]);
                    },
                    'webinarPartnerTeacher' => function ($query) {
                        $query->with(['teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        }]);
                    },
                    'tags',
                    'textLessons',
                    'assignments',
                    'chapters' => function ($query) {
                        $query->orderBy('order', 'asc');
                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');

                                $query->with([
                                    'quiz' => function ($query) {
                                        $query->with([
                                            'quizQuestions' => function ($query) {
                                                $query->orderBy('order', 'asc');
                                            }
                                        ]);
                                    }
                                ]);
                            }
                        ]);
                    },
                ])
                ->first();

            if (empty($webinar)) {
                abort(404);
            }

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $webinar->getTable(), $webinar->id);

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $teacherQuizzes = Quiz::where('webinar_id', null)
                ->where('creator_id', $webinar->teacher_id)
                ->get();

            $tags = $webinar->tags->pluck('title')->toArray();

            $data = [
                'pageTitle' => trans('admin/main.edit') . ' | ' . $webinar->title,
                'categories' => $categories,
                'webinar' => $webinar,
                'webinarCategoryFilters' => !empty($webinar->category) ? $webinar->category->filters : null,
                'webinarFilterOptions' => $webinar->filterOptions->pluck('filter_option_id')->toArray(),
                'tickets' => $webinar->tickets,
                'chapters' => $webinar->chapters,
                'sessions' => $webinar->sessions,
                'files' => $webinar->files,
                'textLessons' => $webinar->textLessons,
                'faqs' => $webinar->faqs,
                'assignments' => $webinar->assignments,
                'teacherQuizzes' => $teacherQuizzes,
                'prerequisites' => $webinar->prerequisites,
                'webinarQuizzes' => $webinar->quizzes,
                'webinarPartnerTeacher' => $webinar->webinarPartnerTeacher,
                'webinarTags' => $tags,
                'defaultLocale' => getDefaultLocale(),
            ];

            return view('admin.webinars.create', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
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
            $this->authorize('admin_webinars_delete');

            $webinar = Webinar::query()->findOrFail($id);

            $webinar->delete();
             Log::channel('activity')->info('Course deleted', [
            'course_id' => $webinar->id,
            'ip' => $request->ip(),

             ]);

            return redirect(getAdminPanelUrl() . '/webinars');
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $webinar = Webinar::query()->findOrFail($id);

            $webinar->update([
                'status' => Webinar::$active
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.course_status_changes_to_approved'),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl() . '/webinars')->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('approve error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $webinar = Webinar::query()->findOrFail($id);

            $webinar->update([
                'status' => Webinar::$inactive
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.course_status_changes_to_rejected'),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl() . '/webinars')->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('reject error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function unpublish(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinars_edit');

            $webinar = Webinar::query()->findOrFail($id);

            $webinar->update([
                'status' => Webinar::$pending
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.course_status_changes_to_unpublished'),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl() . '/webinars')->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('unpublish error: ' . $e->getMessage(), [
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

            $query = Webinar::select('id')
                ->whereTranslationLike('title', "%$term%");

            if (!empty($option) and $option == 'just_webinar') {
                $query->where('type', Webinar::$webinar);
                $query->where('status', Webinar::$active);
            }

            $webinar = $query->get();

            return response()->json($webinar, 200);
        } catch (\Exception $e) {
            \Log::error('search error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $this->authorize('admin_webinars_export_excel');

            $query = Webinar::query();

            $query = $this->filterWebinar($query, $request)
                ->with(['teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                }, 'sales']);

            $webinars = $query->get();

            $webinarExport = new WebinarsExport($webinars);

            return Excel::download($webinarExport, 'webinars.xlsx');
        } catch (\Exception $e) {
            \Log::error('exportExcel error: ' . $e->getMessage(), [
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

            $webinar = Webinar::where('id', $id)
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    },
                    'chapters' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'sessions' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'assignments' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'files' => function ($query) {
                        $query->where('status', 'active');
                    },
                ])
                ->first();

            if (!empty($webinar)) {
                $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $query = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->leftJoin('webinar_reviews', function ($query) use ($webinar) {
                        $query->on('webinar_reviews.creator_id', 'users.id')
                            ->where('webinar_reviews.webinar_id', $webinar->id);
                    })
                    ->select('users.*', 'webinar_reviews.rates', 'sales.access_to_purchased_item', 'sales.id as sale_id', 'sales.gift_id', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('sales.webinar_id', $webinar->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereNull('sales.refund_at');

                $students = $this->studentsListsFilters($webinar, $query, $request)->groupBy('sales.buyer_id')
                    ->orderBy('sales.created_at', 'desc')
                    ->paginate(10);

                $userGroups = Group::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $totalExpireStudents = 0;
                if (!empty($webinar->access_days)) {
                    $accessTimestamp = $webinar->access_days * 24 * 60 * 60;

                    $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                        ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                        ->where(function ($query) use ($webinar, $giftsIds) {
                            $query->where('sales.webinar_id', $webinar->id);
                            $query->orWhereIn('sales.gift_id', $giftsIds);
                        })
                        ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                        ->whereNull('sales.refund_at')
                        ->count();
                }

                $webinarStatisticController = new WebinarStatisticController();

                $allStudentsIds = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('sales.webinar_id', $webinar->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereNull('sales.refund_at')
                    ->pluck('id')
                    ->toArray();

                $learningPercents = [];

                foreach ($students as $key => $student) {
                    if (!empty($student->gift_id)) {
                        $gift = Gift::query()->where('id', $student->gift_id)->first();

                        if (!empty($gift)) {
                            $receipt = $gift->receipt;

                            if (!empty($receipt)) {
                                $receipt->rates = $student->rates;
                                $receipt->access_to_purchased_item = $student->access_to_purchased_item;
                                $receipt->sale_id = $student->sale_id;
                                $receipt->purchase_date = $student->purchase_date;
                                $receipt->learning = $webinarStatisticController->getCourseProgressForStudent($webinar, $receipt->id);

                                $learningPercents[$student->id] = $receipt->learning;

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
                        $student->learning = !empty($learningPercents[$student->id]) ? $learningPercents[$student->id] : 0;
                    }
                }

                $roles = Role::all();

                $data = [
                    'pageTitle' => trans('admin/main.students'),
                    'webinar' => $webinar,
                    'students' => $students,
                    'userGroups' => $userGroups,
                    'roles' => $roles,
                    'totalStudents' => $students->total(),
                    'totalActiveStudents' => $students->total() - $totalExpireStudents,
                    'totalExpireStudents' => $totalExpireStudents,
                    'averageLearning' => count($learningPercents) ? round(array_sum($learningPercents) / count($learningPercents), 2) : 0,
                ];

                return view('admin.webinars.students', $data);
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

    public function studentsLists1(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinar_students_lists');

            $webinar = Webinar::where('id', $id)
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    },
                    'chapters' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'sessions' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'assignments' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'files' => function ($query) {
                        $query->where('status', 'active');
                    },
                ])
                ->first();

            if (!empty($webinar)) {

                $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $query = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->leftJoin('webinar_reviews', function ($query) use ($webinar) {
                        $query->on('webinar_reviews.creator_id', 'users.id')
                            ->where('webinar_reviews.webinar_id', $webinar->id);
                    })
                    ->select('users.*', 'webinar_reviews.rates', 'sales.access_to_purchased_item', 'sales.id as sale_id', 'sales.gift_id', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('sales.webinar_id', $webinar->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereNull('sales.refund_at');

                $students = $this->studentsListsFilters($webinar, $query, $request)->groupBy('sales.buyer_id')
                    ->orderBy('sales.created_at', 'desc')
                    ->get();

                $userGroups = Group::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $totalExpireStudents = 0;
                if (!empty($webinar->access_days)) {
                    $accessTimestamp = $webinar->access_days * 24 * 60 * 60;

                    $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                        ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                        ->where(function ($query) use ($webinar, $giftsIds) {
                            $query->where('sales.webinar_id', $webinar->id);
                            $query->orWhereIn('sales.gift_id', $giftsIds);
                        })
                        ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                        ->whereNull('sales.refund_at')
                        ->count();
                }

                $webinarStatisticController = new WebinarStatisticController();

                $allStudentsIds = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('sales.webinar_id', $webinar->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereNull('sales.refund_at')
                    ->pluck('id')
                    ->toArray();

                $learningPercents = [];

                foreach ($students as $key => $student) {
                    if (!empty($student->gift_id)) {
                        $gift = Gift::query()->where('id', $student->gift_id)->first();

                        if (!empty($gift)) {
                            $receipt = $gift->receipt;

                            if (!empty($receipt)) {
                                $receipt->rates = $student->rates;
                                $receipt->access_to_purchased_item = $student->access_to_purchased_item;
                                $receipt->sale_id = $student->sale_id;
                                $receipt->purchase_date = $student->purchase_date;
                                $receipt->learning = $webinarStatisticController->getCourseProgressForStudent($webinar, $receipt->id);

                                $learningPercents[$student->id] = $receipt->learning;

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
                        $student->learning = !empty($learningPercents[$student->id]) ? $learningPercents[$student->id] : 0;
                    }
                }

                $roles = Role::all();

                $data = [
                    'pageTitle' => trans('admin/main.students'),
                    'webinar' => $webinar,
                    'students' => $students,
                    'userGroups' => $userGroups,
                    'roles' => $roles,
                    'totalStudents' => 10,
                    'totalActiveStudents' => 10 - $totalExpireStudents,
                    'totalExpireStudents' => $totalExpireStudents,
                    'averageLearning' => count($learningPercents) ? round(array_sum($learningPercents) / count($learningPercents), 2) : 0,
                ];

                echo count($students);
                echo 'start';
                $count=0;
                foreach($students as $student){

                    if($student->full_name){
                        $gohighleveldata['full_name']=$student->full_name;
                    }
                    if($student->email){
                        $gohighleveldata['email']=$student->email;
                    }
                    if($student->mobile){
                        $gohighleveldata['mobile']=$student->mobile;
                    }

                    $Progress = 0;
                    $totalVideos =0;
                    $webinar_id = $id;
                    $totalChapter = \App\Models\WebinarChapter::where('webinar_chapters.webinar_id', (int) $webinar_id)->where('status', 'active')->get();
                    if($totalChapter){
                      foreach($totalChapter as $value){
                      $totalItem = \App\Models\WebinarChapterItem::where('chapter_id', (int) $value->id)
                       ->where('type', 'file')
                        ->count();
                        $totalVideos +=$totalItem;
                      }
                    }

                    $watchedVideos = \App\Models\CourseProgress::where('webinar_id', (int) $webinar_id)
                        ->where('user_id',(int) $student->id)
                        ->sum('watch_percentage');
                    $slugs = \App\Models\Webinar::where('id', (int) $webinar_id)->where('status', 'active')->first();

                    if($totalVideos != 0){
                            $Progress = (int) ($watchedVideos/ $totalVideos);
                    }

                        $gohighleveldata['progress']=$Progress;
                    if($student->purchase_date){
                        $gohighleveldata['purchase_date']=dateTimeFormat($student->purchase_date, 'j M Y | H:i');
                    }

                    if($webinar_id == 2036){

                        $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/e3299a32-382c-41f1-98f1-833b19d30f6d';

                    }elseif($webinar_id == 2083){

                        $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/IKcI8dmQi9uGjaOJVEA8';

                    }

                    $gohighlevelcurl = curl_init($gohighlevel);

                    curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);

                    curl_setopt($gohighlevelcurl, CURLOPT_POST, true);

                    curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($gohighleveldata));

                    curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ]);

                    $gohighlevelresponse = curl_exec($gohighlevelcurl);

                    $count+=1;

                  echo 'done';die();

                }
                echo 'done';die();
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('studentsLists1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function studentsListsFilters($webinar, $query, $request)
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
            if ($status == 'expire' and !empty($webinar->access_days)) {
                $accessTimestamp = $webinar->access_days * 24 * 60 * 60;

                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }

    public function notificationToStudents($id)
    {
        try {
            $this->authorize('admin_webinar_notification_to_students');

            $webinar = Webinar::findOrFail($id);

            $data = [
                'pageTitle' => trans('notification.send_notification'),
                'webinar' => $webinar
            ];

            return view('admin.webinars.send-notification-to-course-students', $data);
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
        $this->authorize('admin_webinar_notification_to_students');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $webinar = Webinar::where('id', $id)
            ->with([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                    $query->with([
                        'buyer'
                    ]);
                }
            ])
            ->first();

        if (!empty($webinar)) {
            foreach ($webinar->sales as $sale) {
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
                        try{

                    } catch (\Exception $e) {

}
                    }
                }
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => count($webinar->sales)]),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl("/webinars/{$webinar->id}/students"))->with(['toast' => $toastData]);
        }

        abort(404);
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
                }
            }

            return response()->json([
                'title' => trans('public.request_success'),
                'msg' => trans('update.items_sorted_successful')
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

    public function getContentItemByLocale(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webinar = Webinar::where('id', $id)->first();

        if (!empty($webinar)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($webinar->$relation)) {
                $item = $webinar->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }
    public function studentsListsexel(Request $request, $id)
    {
        try {
            $this->authorize('admin_webinar_students_lists');

            $webinar = Webinar::where('id', $id)
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name');
                    },
                    'chapters' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'sessions' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'assignments' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'files' => function ($query) {
                        $query->where('status', 'active');
                    },
                ])
                ->first();

            if (!empty($webinar)) {
                $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $query = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->leftJoin('webinar_reviews', function ($query) use ($webinar) {
                        $query->on('webinar_reviews.creator_id', 'users.id')
                            ->where('webinar_reviews.webinar_id', $webinar->id);
                    })
                    ->select('users.*', 'webinar_reviews.rates', 'sales.access_to_purchased_item', 'sales.id as sale_id', 'sales.gift_id', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('sales.webinar_id', $webinar->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereNull('sales.refund_at');

                $students = $this->studentsListsFilters($webinar, $query, $request)
                    ->groupBy('sales.buyer_id')
                    ->orderBy('sales.created_at', 'desc')
                    ->get();

                $userGroups = Group::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $totalExpireStudents = 0;
                if (!empty($webinar->access_days)) {
                    $accessTimestamp = $webinar->access_days * 24 * 60 * 60;

                    $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                        ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                        ->where(function ($query) use ($webinar, $giftsIds) {
                            $query->where('sales.webinar_id', $webinar->id);
                            $query->orWhereIn('sales.gift_id', $giftsIds);
                        })
                        ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                        ->whereNull('sales.refund_at')
                        ->count();
                }

                $webinarStatisticController = new WebinarStatisticController();

                $allStudentsIds = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('sales.webinar_id', $webinar->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereNull('sales.refund_at')
                    ->pluck('id')
                    ->toArray();

                $learningPercents = [];
                foreach ($allStudentsIds as $studentsId) {
                    $learningPercents[$studentsId] = $webinarStatisticController->getCourseProgressForStudent($webinar, $studentsId);
                }

                foreach ($students as $key => $student) {
                    if (!empty($student->gift_id)) {
                        $gift = Gift::query()->where('id', $student->gift_id)->first();

                        if (!empty($gift)) {
                            $receipt = $gift->receipt;

                            if (!empty($receipt)) {
                                $receipt->rates = $student->rates;
                                $receipt->access_to_purchased_item = $student->access_to_purchased_item;
                                $receipt->sale_id = $student->sale_id;
                                $receipt->purchase_date = $student->purchase_date;
                                $receipt->learning = $webinarStatisticController->getCourseProgressForStudent($webinar, $receipt->id);

                                $learningPercents[$student->id] = $receipt->learning;

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
                        $student->learning = !empty($learningPercents[$student->id]) ? $learningPercents[$student->id] : 0;
                    }
                }

                $roles = Role::all();

                 return  $students;

            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('studentsListsexel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
     public function exportExcelStudents(Request $request ,$id)
    {
        try {
            $request->exel ="exel_data";
             $this->authorize('admin_users_export_excel');
            $student=  $this->exportExcelStudents($request, $id);
            $usersExport = new StudentsExport( $student);

            return Excel::download($usersExport, 'students.xlsx');
        } catch (\Exception $e) {
            \Log::error('exportExcelStudents error: ' . $e->getMessage(), [
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

            $course = Webinar::where('slug', $slug)
                ->with([
                    'quizzes' => function ($query) {
                        $query->where('status', 'active')
                            ->with(['quizResults', 'quizQuestions']);
                    },
                    'tags',
                    'prerequisites' => function ($query) {
                        $query->with(['prerequisiteWebinar' => function ($query) {
                            $query->with(['teacher' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar');
                            }]);
                        }]);
                        $query->orderBy('order', 'asc');
                    },
                    'faqs' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'webinarExtraDescription' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'chapters' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');

                            }
                        ]);
                    },
                    'files' => function ($query) use ($user) {
                        $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                            ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                            ->where('files.status', WebinarChapter::$chapterActive)
                            ->orderBy('chapterOrder', 'asc')
                            ->orderBy('files.order', 'asc')
                            ->with([
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', !empty($user) ? $user->id : null);
                                }
                            ]);
                    },
                    'textLessons' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive)
                            ->withCount(['attachments'])
                            ->orderBy('order', 'asc')
                            ->with([
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', !empty($user) ? $user->id : null);
                                }
                            ]);
                    },
                    'sessions' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive)
                            ->orderBy('order', 'asc')
                            ->with([
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', !empty($user) ? $user->id : null);
                                }
                            ]);
                    },
                    'assignments' => function ($query) {
                        $query->where('status', WebinarChapter::$chapterActive);
                    },
                    'tickets' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'filterOptions',
                    'category',
                    'teacher',
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
                    },
                    'noticeboards'
                ])
                ->where('status', 'active')
                ->first();

            if (empty($course)) {
                return $justReturnData ? false : back();
            }

            if($course->private==1){
             if (!$justReturnData) {
                $contentLimitation = $this->checkContentLimitation($user, true);

                if ($contentLimitation != "ok") {
                    return $contentLimitation;
                }
            }
            }

            $isPrivate = $course->private;

            if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin())) {
                $isPrivate = false;
            }

            if ($isPrivate and $hasBought) {
                $isPrivate = false;
            }

            if ($isPrivate) {

                return $justReturnData ? false : back();
            }

            $webinarContentCount = 0;
            if (!empty($course->sessions)) {
                $webinarContentCount += $course->sessions->count();
            }
            if (!empty($course->files)) {
                $webinarContentCount += $course->files->count();
            }
            if (!empty($course->textLessons)) {
                $webinarContentCount += $course->textLessons->count();
            }
            if (!empty($course->assignments)) {
                $webinarContentCount += $course->assignments->count();
            }

            $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

            $filesWithoutChapter = $course->files->whereNull('chapter_id');

            $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

            $data = [
                'pageTitle' => $course->title,
                'pageDescription' => $course->seo_description,
                'course' => $course,

                'user' => $user,
                'webinarContentCount' => $webinarContentCount,
                'activeSpecialOffer' => $course->activeSpecialOffer(),
                'sessionsWithoutChapter' => $sessionsWithoutChapter,
                'filesWithoutChapter' => $filesWithoutChapter,
                'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
                'cashbackRules' => $cashbackRules ?? null,

            ];

            if ($justReturnData) {
                return $data;
            }

            $agent = new Agent();
            if ($agent->isMobile()){
                return view(getTemplate() . '.course.index', $data);
            }else{
                return view('web.default2' . '.course.index', $data);
            }
        } catch (\Exception $e) {
            \Log::error('course error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
