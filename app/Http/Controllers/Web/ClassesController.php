<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\FeatureWebinar;
use App\Models\SpecialOffer;
use App\Models\Ticket;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use App\Models\WebinarReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class ClassesController extends Controller
{
    public $tableName = 'webinars';
    public $columnId = 'webinar_id';
    
    private $cacheDuration = 60; // 1 hour
    private $cacheKeyPrefix = 'classes_page_';

    public function index(Request $request)
    {
        $cacheKey = $this->generateCacheKey($request);
        
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData) {
            return $this->renderView($cachedData, $request);
        }

        $webinarsQuery = Webinar::where('webinars.status', 'active')
            ->where('private', false);

        $type = $request->get('type');
        if (!empty($type) and is_array($type) and in_array('bundle', $type)) {
            $webinarsQuery = Bundle::where('bundles.status', 'active');
            $this->tableName = 'bundles';
            $this->columnId = 'bundle_id';
        }

        $webinarsQuery = $this->handleFilters($request, $webinarsQuery);

        $sort = $request->get('sort', null);

        if (empty($sort) or $sort == 'newest') {
            $webinarsQuery = $webinarsQuery->orderBy("{$this->tableName}.order", 'asc');
            $webinarsQuery = $webinarsQuery->orderBy("{$this->tableName}.created_at", 'desc');
        }

        $webinars = $webinarsQuery->with(['tickets'])->get();
        
        $hindi_classes = [];
        $englishclasses = [];
        foreach($webinars as $key => $val) {
            if($val->lang == 'HI') {
                $hindi_classes[$key] = $val; 
            }
            if($val->lang == 'EN') {
                $englishclasses[$key] = $val; 
            }
        }

        $seoSettings = getSeoMetas('classes');
        $pageTitle = $seoSettings['title'] ?? '';
        $pageDescription = $seoSettings['description'] ?? '';
        $pageRobot = getPageRobot('classes');

        $dynamic_rate_course = [
            '2025' => 4.1, '2026' => 4.5, '2027' => 4.75, '2028' => 4.8,
            '2029' => 4.6, '2030' => 4.5, '2031' => 4.9, '2033' => 4.5,
            '2034' => 4.75, '2035' => 4.8, '2036' => 4.1, '2038' => 4.5,
            '2045' => 4.4, '2046' => 4.5, '2047' => 4.75, '2048' => 4.8,
            '2049' => 4.4, '2050' => 4.5, '2052' => 4.1, '2053' => 4.5,
            '2055' => 4.75, '2056' => 4.8, '2057' => 4.3, '2058' => 4.5,
            '2062' => 4.2, '2063' => 4.5, '2064' => 4.75, '2065' => 4.8,
            '2066' => 4.9, '2067' => 4.5, '2068' => 4.1, '2069' => 4.7,
            '2070' => 4.9
        ];

        $user = auth()->user();
        $hasBoughtCourse = [false];
        
        if(isset($user)) {
            $hasBoughtCourse = $this->getUserBoughtCourses($user);
        }

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'webinars' => $webinars,
            'coursesCount' => $webinars->count(),
            'dynamic_rate_course' => $dynamic_rate_course,
            'page' => 'classes',
            'hindi_classes' => $hindi_classes,
            'englishclasses' => $englishclasses,
            'hasBoughtCourse' => $hasBoughtCourse,
        ];

        Cache::put($cacheKey, $data, now()->addMinutes($this->cacheDuration));

        return $this->renderView($data, $request);
    }

    private function renderView($data, $request)
    {
        $agent = new Agent();
        if ($agent->isMobile()) {
            return view(getTemplate() . '.pages.classes', $data);
        } else {
            return view('web.default2' . '.pages.classes', $data);
        }
    }

    private function generateCacheKey($request)
    {
        $keyParams = [
            'type' => json_encode($request->get('type', [])),
            'sort' => $request->get('sort', 'newest'),
            'free' => $request->get('free', ''),
            'hindi' => $request->get('hindi', ''),
            'english' => $request->get('english', ''),
            'discount' => $request->get('discount', ''),
            'downloadable' => $request->get('downloadable', ''),
            'categories' => json_encode($request->get('categories', [])),
            'search' => $request->get('search', ''),
            'filter_option' => json_encode($request->get('filter_option', [])),
        ];
        
        return $this->cacheKeyPrefix . md5(json_encode($keyParams));
    }

    private function getUserBoughtCourses($user)
    {
        $cacheKey = 'user_bought_courses_' . $user->id;
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user) {
            $hasBoughtCourse = [false];
            $webinars = Webinar::where('webinars.status', 'active')
                ->where('private', false)
                ->get();
            
            foreach($webinars as $webinar) {
                if($webinar->checkUserHasBought($user, true, true)) {
                    $hasBoughtCourse[] = $webinar->id;
                }
            }
            
            return $hasBoughtCourse;
        });
    }

    private function getAllCacheKeys()
    {
        return Cache::get('classes_cache_keys', []);
    }

    private function addCacheKey($key)
    {
        $keys = $this->getAllCacheKeys();
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::put('classes_cache_keys', $keys, now()->addDays(7));
        }
    }

    public function forgetAllCache()
    {
        $keys = $this->getAllCacheKeys();
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Cache::forget('user_bought_courses_*');
        
        Cache::forget('home_sections');
        Cache::forget('featured_webinars');
        Cache::forget('hindi_webinars');
        Cache::forget('english_webinars');
        Cache::forget('home_instructors');
        Cache::forget('home_consultant');
        Cache::forget('home_testimonials');
        Cache::forget('home_advertising_banners');
        Cache::forget('trend_categories');
        Cache::forget('remedies');
        Cache::forget('free_webinars');
        Cache::forget('best_rate_webinars');
        Cache::forget('has_discount_webinars');
        Cache::forget('best_sale_webinars');
        Cache::forget('upcoming_courses');
        Cache::forget('latest_webinars');
        
        Cache::forget('classes_cache_keys');
        
        return response()->json(['message' => 'All cache cleared successfully']);
    }

    public function forgetClassesCache()
    {
        $keys = $this->getAllCacheKeys();
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('classes_cache_keys');
        
        return response()->json(['message' => 'Classes cache cleared']);
    }

    public function forgetUserCache($userId = null)
    {
        if ($userId) {
            Cache::forget('user_bought_courses_' . $userId);
        } else {
            $userCachePattern = 'user_bought_courses_';
        }
        
        return response()->json(['message' => 'User cache cleared']);
    }

    public function forgetDiscountCache()
    {
        Cache::forget('has_discount_webinars');
        Cache::forget('best_sale_webinars');
        
        return response()->json(['message' => 'Discount cache cleared']);
    }

    public function handleFilters($request, $query)
    {
        $upcoming = $request->get('upcoming', null);
        $isFree = $request->get('free', null);
        $hindi = $request->get('hindi', null);
        $english = $request->get('english', null);
        $withDiscount = $request->get('discount', null);
        $isDownloadable = $request->get('downloadable', null);
        $sort = $request->get('sort', null);
        $filterOptions = $request->get('filter_option', []);
        $typeOptions = $request->get('type', []);
        $moreOptions = $request->get('moreOptions', []);
        $categories = $request->get('categories', null);
        $search = $request->get('search', null);

        $query->whereHas('teacher', function ($query) {
            $query->where('status', 'active')
                ->where(function ($query) {
                    $query->where('ban', false)
                        ->orWhere(function ($query) {
                            $query->whereNotNull('ban_end_at')
                                ->where('ban_end_at', '<', time());
                        });
                });
        });

        if ($this->tableName == 'webinars') {
            if (!empty($upcoming) and $upcoming == 'on') {
                $query->whereNotNull('start_date')
                    ->where('start_date', '>=', time());
            }

            if (!empty($isDownloadable) and $isDownloadable == 'on') {
                $query->where('downloadable', 1);
            }

            if (!empty($typeOptions) and is_array($typeOptions)) {
                $query->whereIn("{$this->tableName}.type", $typeOptions);
            }
            
            if (!empty($categories) and is_array($categories)) {
                $Category = Category::whereIn('id', $categories)->get()->toArray();
                $Category1 = [];
                foreach($Category as $val) {
                    $Category1[] = $val['id'];
                }
                $query->whereIn('category_id', $Category1);
            }

            if (!empty($moreOptions) and is_array($moreOptions)) {
                if (in_array('subscribe', $moreOptions)) {
                    $query->where('subscribe', 1);
                }
                if (in_array('certificate_included', $moreOptions)) {
                    $query->whereHas('quizzes', function ($query) {
                        $query->where('certificate', 1)
                            ->where('status', 'active');
                    });
                }
                if (in_array('with_quiz', $moreOptions)) {
                    $query->whereHas('quizzes', function ($query) {
                        $query->where('status', 'active');
                    });
                }
                if (in_array('featured', $moreOptions)) {
                    $query->whereHas('feature', function ($query) {
                        $query->whereIn('page', ['home_categories', 'categories'])
                            ->where('status', 'publish');
                    });
                }
            }
        }

        if (!empty($isFree) and $isFree == 'on') {
            $query->where(function ($qu) {
                $qu->whereNull('price')
                    ->orWhere('price', '0');
            });
        }
        
        if (!empty($hindi)) {
            $query->where('lang', 'HI');
        }
        
        if (!empty($english)) {
            $query->where('lang', 'EN');
        }
        
        if (!empty($search)) {
            $query->where(function ($qu) use ($search) {
                $qu->where('slug', 'like', "%$search%");
            });
        }
            
        if (!empty($withDiscount) and $withDiscount == 'on') {
            $now = time();
            $webinarIdsHasDiscount = [];

            $tickets = Ticket::where('start_date', '<', $now)
                ->where('end_date', '>', $now)
                ->get();

            foreach ($tickets as $ticket) {
                if ($ticket->isValid()) {
                    $webinarIdsHasDiscount[] = $ticket->{$this->columnId};
                }
            }

            $specialOffersWebinarIds = SpecialOffer::where('status', 'active')
                ->where('from_date', '<', $now)
                ->where('to_date', '>', $now)
                ->pluck('webinar_id')
                ->toArray();

            $webinarIdsHasDiscount = array_merge($specialOffersWebinarIds, $webinarIdsHasDiscount);
            $webinarIdsHasDiscount = array_unique($webinarIdsHasDiscount);

            $query->whereIn("{$this->tableName}.id", $webinarIdsHasDiscount);
        }

        if (!empty($sort)) {
            if ($sort == 'expensive') {
                $query->whereNotNull('price')
                    ->where('price', '>', 0)
                    ->orderBy('price', 'desc');
            }

            if ($sort == 'inexpensive') {
                $query->whereNotNull('price')
                    ->where('price', '>', 0)
                    ->orderBy('price', 'asc');
            }

            if ($sort == 'bestsellers') {
                $query->leftJoin('sales', function ($join) {
                    $join->on("{$this->tableName}.id", '=', "sales.{$this->columnId}")
                        ->whereNull('refund_at');
                })
                    ->whereNotNull("sales.{$this->columnId}")
                    ->select("{$this->tableName}.*", "sales.{$this->columnId}", DB::raw("count(sales.{$this->columnId}) as salesCounts"))
                    ->groupBy("sales.{$this->columnId}")
                    ->orderBy('salesCounts', 'desc');
            }

            if ($sort == 'best_rates') {
                $query->leftJoin('webinar_reviews', function ($join) {
                    $join->on("{$this->tableName}.id", '=', "webinar_reviews.{$this->columnId}");
                    $join->where('webinar_reviews.status', 'active');
                })
                    ->whereNotNull('rates')
                    ->select("{$this->tableName}.*", DB::raw('avg(rates) as rates'))
                    ->groupBy("{$this->tableName}.id")
                    ->orderBy('rates', 'desc');
            }
        }

        if (!empty($filterOptions) and is_array($filterOptions)) {
            $webinarIdsFilterOptions = WebinarFilterOption::whereIn('filter_option_id', $filterOptions)
                ->pluck($this->columnId)
                ->toArray();

            $query->whereIn("{$this->tableName}.id", $webinarIdsFilterOptions);
        }

        return $query;
    }
}