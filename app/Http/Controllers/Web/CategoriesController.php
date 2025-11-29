<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\FeatureWebinar;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\Translation\CategoryTranslation;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class CategoriesController extends Controller
{
    private $cacheDuration = 60;

    public function index(Request $request, $categorySlug, $subCategorySlug = null)
    {
        try {
            if (!empty($categorySlug)) {

                $categoryQuery = Category::query()->where('slug', $categorySlug);

                if (!empty($subCategorySlug)) {
                    $categoryQuery = Category::query()->where('slug', $subCategorySlug);
                }

                $category = $categoryQuery->withCount('webinars')
                    ->with(['filters' => function ($query) {
                        $query->with('options');
                    }])->first();

                if (!empty($category)) {
                    $categoryIds = [$category->id];

                    if (!empty($category->subCategories) and count($category->subCategories)) {
                        $categoryIds = array_merge($categoryIds, $category->subCategories->pluck('id')->toArray());
                    }

                    $featureWebinars = FeatureWebinar::whereIn('page', ['categories', 'home_categories'])
                        ->where('status', 'publish')
                        ->whereHas('webinar', function ($q) use ($categoryIds) {
                            $q->where('status', Webinar::$active);
                            $q->whereHas('category', function ($q) use ($categoryIds) {
                                $q->whereIn('id', $categoryIds);
                            });
                        })
                        ->with(['webinar' => function ($query) {
                            $query->with(['teacher' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar');
                            }, 'reviews', 'tickets', 'feature']);
                        }])
                        ->orderBy('updated_at', 'desc')
                        ->get();

                    $webinarsQuery = Webinar::where('webinars.status', 'active')
                        ->where('private', false)
                        ->whereIn('category_id', $categoryIds);

                    $classesController = new ClassesController();
                    $moreOptions = $request->get('moreOptions');
                    $tableName = 'webinars';

                    if (!empty($moreOptions) and is_array($moreOptions) and in_array('bundles', $moreOptions)) {
                        $webinarsQuery = Bundle::where('bundles.status', 'active')
                            ->whereIn('category_id', $categoryIds);

                        $tableName = 'bundles';
                        $classesController->tableName = 'bundles';
                        $classesController->columnId = 'bundle_id';
                    }

                    $webinarsQuery = $classesController->handleFilters($request, $webinarsQuery);

                    $sort = $request->get('sort', null);

                    if (empty($sort)) {
                        $webinarsQuery = $webinarsQuery->orderBy("{$tableName}.order", 'asc');
                        $webinarsQuery = $webinarsQuery->orderBy("{$tableName}.created_at", 'desc');
                    }

                    $webinars = $webinarsQuery->with(['tickets'])

                    ->get();
                    $seoSettings = getSeoMetas('categories');
                    $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.categories_page_title') ;
                    $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.categories_page_title');
                    $pageRobot = getPageRobot('categories');

                    $seodata['Astrology'] = 'With the large number of astrology courses discover your cosmic path at Asttrolok! Learn astrology online simply and explore the stars.';
                    $seodata['Ayurveda'] = 'With ayurveda course learn how to balance your body, mind, spirit and get in-depth knowledge of ancient Indian medical practices.';
                    $seodata['Palmistry'] = "Explore Asttrolok's palmistry course to learn hand reading lines & understand the hidden meanings of the minor lines on your palm. Begin your journey now!";
                    $seodata['Vastu'] = "Learn Asttrolok's vastu shastra course online & master the art of vastu shastra. Become an expert of vastu principles. Enroll for your vastu course now!";
                    $seodata['Numerology'] = 'Learn Vedic numerology by Asttrolok best numerologist of India & master the Power of Numbers. Enroll now for a numerology course.';

                    $seotitle['Astrology'] = 'Astrology Courses | Learn Astrology Online';
                    $seotitle['Ayurveda'] = 'Ayurveda Courses: Learn Ayurveda Online ';
                    $seotitle['Palmistry'] = 'Palm Astrology | Palmistry Course Online';
                    $seotitle['Vastu'] = 'Vastu Course Online | Learn Vastu Shastra';
                    $seotitle['Numerology'] = 'Numerology Course | Vedic Numerology ';

                    $seoh1['Astrology'] = 'Learn Vedic Astrology Online';
                    $seoh1['Ayurveda'] = 'Learn Ayurveda Online';
                    $seoh1['Palmistry'] = 'Learn Palm Astrology';
                    $seoh1['Vastu'] = 'Vaastu Shastra Course Online';
                    $seoh1['Numerology'] = 'Vedic Numerology By Asttrolok';

                   $dynamic_rate_course=[
            '2025' =>4.1,
            '2026' =>4.5,
            '2027' =>4.75,
            '2028' =>4.8,
            '2029' =>4.6,
            '2030' =>4.5,
            '2031' =>4.9,
            '2033' =>4.5,
            '2034' =>4.75,
            '2035' =>4.8,
            '2036' =>4.1,
            '2038' =>4.5,
            '2045' =>4.4,
            '2046' =>4.5,
            '2047' =>4.75,
            '2048' =>4.8,
            '2049' =>4.4,
            '2050' =>4.5,
            '2052' =>4.1,
            '2053' =>4.5,
            '2055' =>4.75,
            '2056' =>4.8,
            '2057' =>4.3,
            '2058' =>4.5,
            '2062' =>4.2,
            '2063' =>4.5,
            '2064' =>4.75,
            '2065' =>4.8,
            '2066' =>4.9,
            '2067' =>4.5,
            '2068' =>4.1,
            '2069' =>4.7,
            '2070' =>4.9
            ];

                    $user = auth()->user();
                $hasBoughtCourse[]=false;
                if(isset($user)){
                $webinars1 = Webinar::where('webinars.status', 'active')
            ->where('private', false)
            ->get();

            foreach($webinars1 as $webinar1){
            if($webinar1->checkUserHasBought($user, true, true))
            {
                $hasBoughtCourse[] = $webinar1->id;
            }
            }}

                    $data = [

                        'pageTitle' => $seotitle[$category->title],
                        'pageTitle1' => $seoh1[$category->title],
                        'pageDescription' => !empty($seodata[$category->title]) ? $seodata[$category->title] : $pageDescription,
                        'pageRobot' => $pageRobot,
                        'category' => $category,
                        'webinars' => $webinars,
                        'featureWebinars' => $featureWebinars,
                        'webinarsCount' => $webinars->count(),
                        'sortFormAction' => $category->getUrl(),
                        'dynamic_rate_course'=>$dynamic_rate_course,
                        'hasBoughtCourse' => $hasBoughtCourse,
                        'page' => 'classes'
                    ];

                    $agent = new Agent();
                    if ($agent->isMobile()){
                            return view(getTemplate() . '.pages.categories', $data);
                    }else{
                        return view('web.default2' . '.pages.categories', $data);
                    }

                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
