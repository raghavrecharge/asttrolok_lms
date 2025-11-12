<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RemediesExport;
use App\Http\Controllers\Admin\traits\WebinarChangeCreator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\BundleWebinar;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Refile;
use App\Models\Gift;
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
use App\Models\Translation\RemedyTranslation;
use App\Models\RemedyChapter;
use App\Models\RemedyChapterItem;
use App\Models\WebinarExtraDescription;
use App\Models\RemedyFilterOption;
use App\Models\WebinarPartnerTeacher;
use App\User;
use App\Models\Remedy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


class RemedyController extends Controller

{ 
    
    use WebinarChangeCreator;

    

    public function index(Request $request)
    {
        
        
        $this->authorize('admin_remedies_list');
        
        removeContentLocale();

        $type = $request->get('type', 'remedy');
        $query = Remedy::where('remedies.type', $type);
        $totalRemedies = $query->count();
        $totalPendingRemedies = deepClone($query)->where('remedies.status', 'pending')->count();
        
        // $totalDurations = deepClone($query)->sum('duration');
        $totalSales = deepClone($query)->join('sales', 'remedies.id', '=', 'sales.remedy_id')
            ->select(DB::raw('count(sales.remedy_id) as sales_count'))
            ->whereNotNull('sales.remedy_id')
            ->whereNull('sales.refund_at')
            ->first();
      
        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();
            
        $inProgressRemedies = 0;
        if ($type == 'remedy') {
            $inProgressRemedies = $this->getInProgressRemediesCount();
        }
        
        
        
        $query = $this->filterRemedy($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ]);
            
        $remedies = $query->paginate(10);   
       
        // if(isset($query))
            // print_r("mayank");die();
        if ($request->get('status', null) == 'active_finished') {
            foreach ($remedies as $key => $remedy) {
                if ($remedy->last_date > time()) { // is in progress
                    unset($remedies[$key]);
                }
            }
        }
        
        foreach ($remedies as $remedy) {
        //     $giftsIds = Gift::query()->where('remedy_id', $remedy->id)
        //         ->where('status', 'active')
        //         ->where(function ($query) {
        //             $query->whereNull('date');
        //             $query->orWhere('date', '<', time());
        //         })
        //         ->whereHas('sale')
        //         ->pluck('id')
        //         ->toArray();
              
            $sales = Sale::query()
                ->where(function ($query) use ($remedy) {
                    $query->where('remedy_id', $remedy->id);
                })
                ->whereNull('refund_at')
                ->get();

            $remedy->sales = $sales;
        }
        
        // print_r(!empty($totalSales));die();
        $data = [
            'pageTitle' => trans('admin/pages/remedies.remedies_list_page_title'),
            'remedies' => $remedies,
            'totalRemedies' => $totalRemedies,
            'totalPendingRemedies' => $totalPendingRemedies,
            // 'totalDurations' => $totalDurations,
            'totalSales' => !empty($totalSales) ? $totalSales->sales_count : 0,

            'categories' => $categories,
            'inProgressRemedies' => $inProgressRemedies,
            'classesType' => $type,
        ];
        // 
        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }
        return view('admin.remedies.lists', $data);
    }

    private function filterRemedy($query, $request)
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
                    $query->where('remedies.status', 'active')
                        ->where('start_date', '>', $time);
                    break;
                case 'active_in_progress':
                    $query->where('remedies.status', 'active')
                        // ->where('start_date', '<=', $time)
                        ->join('sessions', 'remedies.id', '=', 'sessions.remedy_id')
                        ->select('remedies.*', 'sessions.date', DB::raw('max(`date`) as last_date'))
                        ->groupBy('sessions.remedy_id')
                        ->where('sessions.date', '>', $time);
                    break;
                case 'active_finished':
                    $query->where('remedies.status', 'active')
                        // ->where('start_date', '<=', $time)
                        ->join('sessions', 'remedies.id', '=', 'sessions.remedy_id')
                        ->select('remedies.*', 'sessions.date', DB::raw('max(`date`) as last_date'))
                        ->groupBy('sessions.remedy_id');
                    break;
                default:
                    $query->where('remedies.status', $status);
                    break;
            }
        }
        
        if (!empty($sort)) {
            
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $remedyIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        // ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $remedyIdsHasDiscount[] = $ticket->remedy_id;
                        }
                    }

                    $specialOffersRemedyIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('remedy_id')
                        ->toArray();

                    $remedyIdsHasDiscount = array_merge($specialOffersRemedyIds, $remedyIdsHasDiscount);

                    $query->whereIn('id', $remedyIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'remedies.id', '=', 'sales.remedy_id')
                        ->select('remedies.*', 'sales.remedy_id', 'sales.refund_at', DB::raw('count(sales.remedy_id) as sales_count'))
                        ->whereNotNull('sales.remedy_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.remedy_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'remedies.id', '=', 'sales.remedy_id')
                        ->select('remedies.*', 'sales.remedy_id', 'sales.refund_at', DB::raw('count(sales.remedy_id) as sales_count'))
                        ->whereNotNull('sales.remedy_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.remedy_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'remedies.id', '=', 'sales.remedy_id')
                        ->select('remedies.*', 'sales.remedy_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.remedy_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.remedy_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'remedies.id', '=', 'sales.remedy_id')
                        ->select('remedies.*', 'sales.remedy_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.remedy_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.remedy_id')
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

    private function getInProgressRemediesCount()
    {
        $count = 0;
        $remedies = Remedy::where('type', 'remedy')
            ->where('status', 'active')
            ->whereHas('sessions')
            ->get();

        foreach ($remedies as $remedy) {
            if ($remedy->isProgressing()) {
                $count += 1;
            }
        }

        return $count;
    }

    public function create()
    {
        // print_r("mayank");die();
        
        $this->authorize('admin_remedies_create');

        removeContentLocale();

        $teachers = User::where('role_name', Role::$teacher)->get();
        
        
        $categories = Category::where('parent_id', null)->get();
        
        $data = [
            'pageTitle' => trans('admin/main.remedy_new_page_title'),
            'teachers' => $teachers,
            'categories' => $categories
        ];
        
        // return view('admin.remedies.create', $data);
        // print_r($data["categories"]);die();
        return view('admin.remedies.create', $data);
        // echo "mayank";
        
    }

    public function store(Request $request)
    {
        $this->authorize('admin_remedies_create');
        
        $this->validate($request, [
            // 'type' => 'required|in:remedy,course,text_lesson',
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:remedies,slug',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
            // 'duration' => 'required|numeric',
            // 'start_date' => 'required_if:type,webinar',
            // 'capacity' => 'required_if:type,webinar',
        ]);

        $data = $request->all();
        
        // if ($data['type'] != Remedy::$remedy) {
        //     // $data['start_date'] = null;
        // }

        // if (!empty($data['start_date']) and $data['type'] == Webinar::$remedy) {
        //     if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
        //         $data['timezone'] = getTimezone();
        //     }

        //     $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

        //     $data['start_date'] = $startDate->getTimestamp();
        // }

        if (empty($data['slug'])) {
            $data['slug'] = Remedy::makeSlug($data['title']);
        }

        // if (empty($data['video_demo'])) {
        //     $data['video_demo_source'] = null;
        // }

        // if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
        //     $data['video_demo_source'] = 'upload';
        // }
        
        $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        // $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;
//         $dtf=[
//             'type' => Remedy::$remedy,
//             'slug' => $data['slug'],
//             'teacher_id' => $data['teacher_id'],
//             'creator_id' => $data['teacher_id'],
//             'thumbnail' => $data['thumbnail'],
//             'image_cover' => $data['image_cover'],
//             // 'video_demo' => $data['video_demo'],
//             // 'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
//             // 'capacity' => $data['capacity'] ?? null,
//             // 'start_date' => (!empty($data['start_date'])) ? $data['start_date'] : null,
//             // 'timezone' => $data['timezone'] ?? null,
//             // 'duration' => $data['duration'] ?? null,
//             // 'support' => !empty($data['support']) ? true : false,
//             // 'certificate' => !empty($data['certificate']) ? true : false,
//             'downloadable' => !empty($data['downloadable']) ? true : false,
//             // 'partner_instructor' => !empty($data['partner_instructor']) ? true : false,
//             // 'subscribe' => !empty($data['subscribe']) ? true : false,
//             'private' => !empty($data['private']) ? true : false,
//             // 'forum' => !empty($data['forum']) ? true : false,
//             // 'enable_waitlist' => (!empty($data['enable_waitlist'])),
//             // 'access_days' => $data['access_days'] ?? null,
//             'price' => $data['price'],
//             // 'organization_price' => $data['organization_price'] ?? null,
//             // 'points' => $data['points'] ?? null,
//             'category_id' => $data['category_id'],
//             'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
//             'status' => Remedy::$pending,
//             'created_at' => time(),
//             'updated_at' => time(),
//         ];
//   print_r($dtf);

        $remedy = Remedy::create([
            'type' => 'remedy',
            'slug' => $data['slug'],
            'teacher_id' => $data['teacher_id'],
            'creator_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'downloadable' => !empty($data['downloadable']) ? true : false,
            'private' => !empty($data['private']) ? true : false,
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'message_for_reviewer' =>!empty($data['message_for_reviewer']) ? $data['message_for_reviewer'] : '', 
            'status' => 'pending',
            'created_at' => time(),
        ]);
        
    
        if ($remedy) {
            RemedyTranslation::updateOrCreate([
                'remedy_id' => $remedy->id,
                'locale' => mb_strtolower($data['locale']),
        
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
                'seo_title' => $data['seo_title'],
            ]);
        }
         

        
        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            RemedyFilterOption::where('remedy_id', $remedy->id)->delete();
            foreach ($filters as $filter) {
                RemedyFilterOption::create([
                    'remedy_id' => $remedy->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        // if (!empty($request->get('tags'))) {
        //     $tags = explode(',', $request->get('tags'));
        //     Tag::where('remedy_id', $remedy->id)->delete();

        //     foreach ($tags as $tag) {
        //         Tag::create([
        //             'remedy_id' => $remedy->id,
        //             'title' => $tag,
        //         ]);
        //     }
        // }

        // if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
        //     WebinarPartnerTeacher::where('remedy_id', $remedy->id)->delete();

        //     foreach ($request->get('partners') as $partnerId) {
        //         WebinarPartnerTeacher::create([
        //             'remedy_id' => $remedy->id,
        //             'teacher_id' => $partnerId,
        //         ]);
        //     }
        // }


        return redirect(getAdminPanelUrl() . '/remedies/' . $remedy->id . '/edit');
    }

    public function edit(Request $request, $id)
    { 
        
        $this->authorize('admin_remedies_edit');

        $remedy = Remedy::where('id', $id)
            ->with([
                'tickets',
                'sessions',
                'files',
                'faqs',
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                    }]);
                },
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
    // print_r($remedy["chapterItems"]);die();

        if (empty($remedy)) {
            abort(404);
        }
  
        // $locale = $request->get('locale', app()->getLocale());
        // storeContentLocale($locale, $remedy->getTable(), $remedy->id);

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        // $teacherQuizzes = Quiz::where('remedy_id', null)
        //     ->where('creator_id', $remedy->teacher_id)
        //     ->get();

        // $tags = $remedy->tags->pluck('title')->toArray();

        $data = [
            'pageTitle' => trans('admin/main.edit') . ' | ' . $remedy->title,
            'categories' => $categories,
            'remedy' => $remedy,
            'remedyCategoryFilters' => !empty($remedy->category) ? $remedy->category->filters : null,
            'remedyFilterOptions' => $remedy->filterOptions->pluck('filter_option_id')->toArray(),
            'tickets' => $remedy->tickets,
            'chapters' => $remedy->chapters,
            'sessions' => $remedy->sessions,
            'files' => $remedy->files,
            // 'textLessons' => $remedy->textLessons,
            'faqs' => $remedy->faqs,
            // 'assignments' => $remedy->assignments,
            // 'teacherQuizzes' => $teacherQuizzes,
            // 'prerequisites' => $remedy->prerequisites,
            // 'remedyQuizzes' => $remedy->quizzes,
            // 'remedyPartnerTeacher' => $remedy->remedyPartnerTeacher,
            // 'remedyTags' => $tags,
            'defaultLocale' => getDefaultLocale(),
        ];
//   print_r($data["chapters"]);
//   die();
        return view('admin.remedies.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');
        $data = $request->all();
        
        $remedy = Remedy::find($id);
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
        $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

        $rules = [
            'type' => 'required|in:remedy,course,text_lesson',
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:remedies,slug,' . $remedy->id,
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
        ];

        // if ($remedy->isRemedy()) {
        //     // $rules['start_date'] = 'required|date';
        //     // $rules['duration'] = 'required';
        //     $rules['capacity'] = 'required|integer';
        // }

        $this->validate($request, $rules);

        if (!empty($data['teacher_id'])) {
            $teacher = User::find($data['teacher_id']);
            $creator = $remedy->creator;

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
            $data['slug'] = Remedy::makeSlug($data['title']);
        }

        $data['status'] = $publish ? Remedy::$active : ($reject ? Remedy::$inactive : ($isDraft ? Remedy::$isDraft : Remedy::$pending));
        $data['updated_at'] = time();

        // if (!empty($data['start_date']) and $remedy->type == 'remedy') {
        //     if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
        //         $data['timezone'] = getTimezone();
        //     }

        //     // $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

        //     // $data['start_date'] = $startDate->getTimestamp();
        // } else {
        //     // $data['start_date'] = null;
        // }


        // $data['support'] = !empty($data['support']) ? true : false;
        // $data['certificate'] = !empty($data['certificate']) ? true : false;
        $data['downloadable'] = !empty($data['downloadable']) ? true : false;
        // $data['partner_instructor'] = !empty($data['partner_instructor']) ? true : false;
        // $data['subscribe'] = !empty($data['subscribe']) ? true : false;
        // $data['forum'] = !empty($data['forum']) ? true : false;
        $data['private'] = !empty($data['private']) ? true : false;
        // $data['enable_waitlist'] = (!empty($data['enable_waitlist']));

        // if (empty($data['partner_instructor'])) {
        //     WebinarPartnerTeacher::where('remedy_id', $remedy->id)->delete();
        //     unset($data['partners']);
        // }

        if ($data['category_id'] !== $remedy->category_id) {
            RemedyFilterOption::where('remedy_id', $remedy->id)->delete();
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            RemedyFilterOption::where('remedy_id', $remedy->id)->delete();
            foreach ($filters as $filter) {
                RemedyFilterOption::create([
                    'remedy_id' => $remedy->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        // if (!empty($request->get('tags'))) {
        //     $tags = explode(',', $request->get('tags'));
        //     Tag::where('remedy_id', $remedy->id)->delete();

        //     foreach ($tags as $tag) {
        //         Tag::create([
        //             'remedy_id' => $remedy->id,
        //             'title' => $tag,
        //         ]);
        //     }
        // }

        if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
            WebinarPartnerTeacher::where('remedy_id', $remedy->id)->delete();

            foreach ($request->get('partners') as $partnerId) {
                WebinarPartnerTeacher::create([
                    'remedy_id' => $remedy->id,
                    'teacher_id' => $partnerId,
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

        $newCreatorId = !empty($data['organ_id']) ? $data['organ_id'] : $data['teacher_id'];
        $changedCreator = ($remedy->creator_id != $newCreatorId);

        $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;

        $remedy->update([
            'slug' => $data['slug'],
            'creator_id' => $newCreatorId,
            'teacher_id' => $data['teacher_id'],
            'type' => $data['type'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            // 'video_demo' => $data['video_demo'],
            // 'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            // 'capacity' => $data['capacity'] ?? null,
            // 'start_date' => $data['start_date'],
            // 'timezone' => $data['timezone'] ?? null,
            // 'duration' => $data['duration'] ?? null,
            // 'support' => $data['support'],
            // 'certificate' => $data['certificate'],
            'private' => $data['private'],
            // 'enable_waitlist' => $data['enable_waitlist'],
            'downloadable' => $data['downloadable'],
            // 'partner_instructor' => $data['partner_instructor'],
            // 'subscribe' => $data['subscribe'],
            // 'forum' => $data['forum'],
            // 'access_days' => $data['access_days'] ?? null,
            'price' => $data['price'],
            // 'organization_price' => $data['organization_price'] ?? null,
            'category_id' => $data['category_id'],
            // 'points' => $data['points'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => $data['status'],
            'updated_at' => time(),
        ]);

        if ($remedy) {
            RemedyTranslation::updateOrCreate([
                'remedy_id' => $remedy->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_title' => $data['seo_title'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        if ($publish) {
            sendNotification('course_approve', ['[c.title]' => $remedy->title], $remedy->teacher_id);

            $createClassesReward = RewardAccounting::calculateScore(Reward::CREATE_CLASSES);
            RewardAccounting::makeRewardAccounting(
                $remedy->creator_id,
                $createClassesReward,
                Reward::CREATE_CLASSES,
                $remedy->id,
                true
            );

        } elseif ($reject) {
            sendNotification('course_reject', ['[c.title]' => $remedy->title], $remedy->teacher_id);
        }

        if ($changedCreator) {
            $this->webinarChangedCreator($remedy);
        }

        removeContentLocale();

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_remedies_delete');
// print_r("mayank");die();
        $remedy = Remedy::query()->findOrFail($id);

        $remedy->delete();

        return redirect(getAdminPanelUrl() . '/remedies');
    }

    public function approve(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $remedy = Remedy::query()->findOrFail($id);

        $remedy->update([
            'status' => Remedy::$active
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_status_changes_to_approved'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/remedies')->with(['toast' => $toastData]);
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $remedy = Remedy::query()->findOrFail($id);

        $remedy->update([
            'status' => Remedy::$inactive
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_status_changes_to_rejected'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/remedies')->with(['toast' => $toastData]);
    }

    public function unpublish(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

        $remedy = Remedy::query()->findOrFail($id);

        $remedy->update([
            'status' => Remedy::$pending
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_status_changes_to_unpublished'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/remedies')->with(['toast' => $toastData]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Remedy::select('id')
            ->whereTranslationLike('title', "%$term%");

        if (!empty($option) and $option == 'just_remedy') {
            $query->where('type', Remedy::$remedy);
            $query->where('status', Remedy::$active);
        }

        $remedy = $query->get();

        return response()->json($remedy, 200);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_remedies_export_excel');

        $query = Remedy::query();

        $query = $this->filterRemedy($query, $request)
            ->with(['teacher' => function ($qu) {
                $qu->select('id', 'full_name');
            }, 'sales']);

        $remedies = $query->get();

        $remedyExport = new RemediesExport($remedies);

        return Excel::download($remedyExport, 'remedies.xlsx');
    }

    public function studentsLists(Request $request, $id)
    {
        $this->authorize('admin_webinar_students_lists');

        $remedy = Remedy::where('id', $id)
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


        if (!empty($remedy)) {
            $giftsIds = Gift::query()->where('remedy_id', $remedy->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $query = User::join('sales', 'sales.buyer_id', 'users.id')
                ->leftJoin('webinar_reviews', function ($query) use ($remedy) {
                    $query->on('webinar_reviews.creator_id', 'users.id')
                        ->where('webinar_reviews.remedy_id', $remedy->id);
                })
                ->select('users.*', 'webinar_reviews.rates', 'sales.access_to_purchased_item', 'sales.id as sale_id', 'sales.gift_id', DB::raw('sales.created_at as purchase_date'))
                ->where(function ($query) use ($remedy, $giftsIds) {
                    $query->where('sales.remedy_id', $remedy->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereNull('sales.refund_at');

            $students = $this->studentsListsFilters($remedy, $query, $request)
                ->orderBy('sales.created_at', 'desc')
                ->paginate(10);

            $userGroups = Group::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalExpireStudents = 0;
            if (!empty($remedy->access_days)) {
                $accessTimestamp = $remedy->access_days * 24 * 60 * 60;

                $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                    ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                    ->where(function ($query) use ($remedy, $giftsIds) {
                        $query->where('sales.remedy_id', $remedy->id);
                        $query->orWhereIn('sales.gift_id', $giftsIds);
                    })
                    ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                    ->whereNull('sales.refund_at')
                    ->count();
            }

            $remediestatisticController = new RemediestatisticController();

            $allStudentsIds = User::join('sales', 'sales.buyer_id', 'users.id')
                ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                ->where(function ($query) use ($remedy, $giftsIds) {
                    $query->where('sales.remedy_id', $remedy->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereNull('sales.refund_at')
                ->pluck('id')
                ->toArray();

            $learningPercents = [];
            foreach ($allStudentsIds as $studentsId) {
                $learningPercents[$studentsId] = $remediestatisticController->getCourseProgressForStudent($remedy, $studentsId);
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
                            $receipt->learning = $remediestatisticController->getCourseProgressForStudent($remedy, $receipt->id);

                            $learningPercents[$student->id] = $receipt->learning;

                            $students[$key] = $receipt;
                        } else { /* Gift recipient who has not registered yet */
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
                'remedy' => $remedy,
                'students' => $students,
                'userGroups' => $userGroups,
                'roles' => $roles,
                'totalStudents' => $students->total(),
                'totalActiveStudents' => $students->total() - $totalExpireStudents,
                'totalExpireStudents' => $totalExpireStudents,
                'averageLearning' => count($learningPercents) ? round(array_sum($learningPercents) / count($learningPercents), 2) : 0,
            ];

            return view('admin.remedies.students', $data);
        }

        abort(404);
    }

    private function studentsListsFilters($remedy, $query, $request)
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
            if ($status == 'expire' and !empty($remedy->access_days)) {
                $accessTimestamp = $remedy->access_days * 24 * 60 * 60;

                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }

    public function notificationToStudents($id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $remedy = Remedy::findOrFail($id);

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'remedy' => $remedy
        ];

        return view('admin.remedies.send-notification-to-course-students', $data);
    }


    public function sendNotificationToStudents(Request $request, $id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $remedy = Remedy::where('id', $id)
            ->with([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                    $query->with([
                        'buyer'
                    ]);
                }
            ])
            ->first();

        if (!empty($remedy)) {
            foreach ($remedy->sales as $sale) {
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
                        \Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message']]));
                    } catch (\Exception $e) {
    // Log the error message if needed
    // Log::error('Mail sending failed: ' . $e->getMessage());
}
                    }
                }
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => count($remedy->sales)]),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl("/remedies/{$remedy->id}/students"))->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function orderItems(Request $request)
    {
        $this->authorize('admin_remedies_edit');
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
                        Refile::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'text_lessons':
                    foreach ($itemIds as $order => $id) {
                        TextLesson::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'remedy_chapters':
                    foreach ($itemIds as $order => $id) {
                        RemedyChapter::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'remedy_chapter_items':
                    foreach ($itemIds as $order => $id) {
                        RemedyChapterItem::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                case 'bundle_remedies':
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
    }


    public function getContentItemByLocale(Request $request, $id)
    {
        $this->authorize('admin_remedies_edit');

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

        $remedy = Remedy::where('id', $id)->first();

        if (!empty($remedy)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($remedy->$relation)) {
                $item = $remedy->$relation->where('id', $itemId)->first();

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
}
