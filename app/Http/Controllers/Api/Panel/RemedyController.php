<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\AdvertisingBanner;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\File;
use App\Models\QuizzesResult;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\TextLesson;
use App\Models\RemedyLearning;
use App\Models\RemedyChapter;

use App\Models\WebinarChapter;
use App\Models\RemedyReport;
use App\Models\Webinar;
use App\Models\Remedy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Refile;
use App\Models\Category;
use Jenssegers\Agent\Agent;
class RemedyController extends Controller
{

    public $tableName = 'remedies';
    public $columnId = 'remedy_id';

    public function remedy($slug, $justReturnData = false)
    {
        try {
            $user = apiAuth();

            $course = Remedy::where('slug', $slug)
                ->with([

                    'chapters' => function ($query) use ($user) {
                        $query->where('status', RemedyChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');
                            }
                        ]);
                    },

                    'files' => function ($query) use ($user) {
                        $query->join('remedy_chapters', 'remedy_chapters.id', '=', 'refiles.chapter_id')
                            ->select('*');

                    }
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

            $hasBought = $course->checkUserHasBought($user, true, true);
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

            $isFavorite = false;

            if (!empty($user)) {
                $isFavorite = Favorite::where('webinar_id', $course->id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            $remedyContentCount = 0;

            if (!empty($course->files)) {
                $remedyContentCount+= $course->files->count();
            }

            $advertisingBanners = AdvertisingBanner::where('published', true)
                ->whereIn('position', ['course', 'course_sidebar'])
                ->get();

            $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

            $filesWithoutChapter = $course->files->whereNull('chapter_id');

            $pageRobot = getPageRobot('course_show');
            $canSale = ($course->canSale() and !$hasBought);

            $showInstallments = true;
            $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

            if ($canSale and !empty($course->price) and getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
                $cashbackRulesMixin = new CashbackRules($user);
                $cashbackRules = $cashbackRulesMixin->getRules('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
            }

            $data = [
                'pageTitle' => $course->title,
                'pageDescription' => $course->seo_description,
                'pageRobot' => $pageRobot,
                'course' => $course,
                'isFavorite' => $isFavorite,
                'hasBought' => $hasBought,
                'user' => $user,
                'remedyContentCount' => $remedyContentCount,
                'advertisingBanners' => $advertisingBanners->where('position', 'course'),
                'advertisingBannersSidebar' => $advertisingBanners->where('position', 'course_sidebar'),

                'sessionsWithoutChapter' => $sessionsWithoutChapter,
                'filesWithoutChapter' => $filesWithoutChapter,

                'cashbackRules' => $cashbackRules ?? null,

                'page' =>'remedies'

            ];

                    if ($justReturnData) {
                        return $data;
                    }
                    $agent = new Agent();
                if ($agent->isMobile()){
                        return view(getTemplate() . '.remedy.index', $data);
                }else{
                    return view('web.default2' . '.remedy.index', $data);
                }
        } catch (\Exception $e) {
            \Log::error('remedy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function remedies(Request $request,$slug='')
    {
        try {
            $slug;
            $category =null;
            if (!empty($slug)) {
                $categoryQuery = Category::where('slug', $slug)->get();
                $category = $categoryQuery[0]->id;

                $remediesQuery = Remedy::where('remedies.status', 'active')
                ->where('private', false)
                ->where('category_id',$category)
                ->with([

                    'chapters' => function ($query)  {
                        $query->where('status', RemedyChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');
                            }
                        ]);
                    },

                    'files' => function ($query) {
                        $query->join('remedy_chapters', 'remedy_chapters.id', '=', 'refiles.chapter_id')
                            ->select('*');

                    }
                ]);

            }else{

                $remediesQuery = Remedy::where('remedies.status', 'active')
                    ->where('private', false)
                    ->with([

                        'chapters' => function ($query)  {
                            $query->where('status', RemedyChapter::$chapterActive);
                            $query->orderBy('order', 'asc');

                            $query->with([
                                'chapterItems' => function ($query) {
                                    $query->orderBy('order', 'asc');
                                }
                            ]);
                        },

                        'files' => function ($query) {
                            $query->join('remedy_chapters', 'remedy_chapters.id', '=', 'refiles.chapter_id')
                                ->select('*');

                        }
                    ]);
            }

            $type = $request->get('type');
            if (!empty($type) and is_array($type) and in_array('bundle', $type)) {
                $remediesQuery = Bundle::where('bundles.status', 'active');
                $this->tableName = 'bundles';
                $this->columnId = 'bundle_id';
            }
            $remediesQuery = $this->handleFilters($request, $remediesQuery);

            $sort = $request->get('sort', null);

            if (empty($sort) or $sort == 'newest') {
                $remediesQuery = $remediesQuery->orderBy("{$this->tableName}.created_at", 'desc');
            }

            $remedies = $remediesQuery->with([
                'tickets'
            ])->get();

            $pageTitle ='Remedies';
            $pageDescription = $seoSettings['description'] ?? '';
            $pageRobot = getPageRobot('classes');

            $data = [
                'pageTitle' => $pageTitle,
                'pageDescription' => $pageDescription,
                'remedies' => $remedies,
            ];
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        } catch (\Exception $e) {
            \Log::error('remedies error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function handleFilters($request, $query)
    {
        try {
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

            if ($this->tableName == 'remedies') {

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

                    $Category1 =[];
                    foreach($Category as $val){
                        $Category1[]=$val['id'];
                    }
                    $query->whereIn('category_id', $Category1);
             }

                if (!empty($search)) {
                    $query->where(function ($qu) use ($search) {
                        $qu->where('slug', 'like', "%$search%");

                    });
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

            $query->where('lang','HI');

            }
             if (!empty($english)) {

            $query->where('lang','EN');

            }

            if (!empty($sort)) {
                if ($sort == 'expensive') {
                    $query->whereNotNull('price');
                    $query->where('price', '>', 0);
                    $query->orderBy('price', 'desc');
                }

                if ($sort == 'inexpensive') {
                    $query->whereNotNull('price');
                    $query->where('price', '>', 0);
                    $query->orderBy('price', 'asc');
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
                    $query->leftJoin('remedy_reviews', function ($join) {
                        $join->on("{$this->tableName}.id", '=', "remedy_reviews.{$this->columnId}");
                        $join->where('remedy_reviews.status', 'active');
                    })
                        ->whereNotNull('rates')
                        ->select("{$this->tableName}.*", DB::raw('avg(rates) as rates'))
                        ->groupBy("{$this->tableName}.id")
                        ->orderBy('rates', 'desc');
                }
            }

            if (!empty($filterOptions) and is_array($filterOptions)) {
                $remedyIdsFilterOptions = RemedyFilterOption::whereIn('filter_option_id', $filterOptions)
                    ->pluck($this->columnId)
                    ->toArray();

                $query->whereIn("{$this->tableName}.id", $remedyIdsFilterOptions);
            }

            return $query;
        } catch (\Exception $e) {
            \Log::error('handleFilters error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function checkQuizzesResults($user, $quizzes)
    {
        $canDownloadCertificate = false;

        foreach ($quizzes as $quiz) {
            $quiz = $this->checkQuizResults($user, $quiz);
        }

        return $quizzes;
    }

    private function checkQuizResults($user, $quiz)
    {
        $canDownloadCertificate = false;

        $canTryAgainQuiz = false;
        $userQuizDone = QuizzesResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($userQuizDone)) {
            $quiz->user_grade = $userQuizDone->first()->user_grade;
            $quiz->result_count = $userQuizDone->count();
            $quiz->result = $userQuizDone->first();

            $status_pass = false;
            foreach ($userQuizDone as $result) {
                if ($result->status == QuizzesResult::$passed) {
                    $status_pass = true;
                }
            }

            $quiz->result_status = $status_pass ? QuizzesResult::$passed : $userQuizDone->first()->status;

            if ($quiz->certificate and $quiz->result_status == QuizzesResult::$passed) {
                $canDownloadCertificate = true;
            }
        }

        if (!isset($quiz->attempt) or (count($userQuizDone) < $quiz->attempt and $quiz->result_status !== QuizzesResult::$passed)) {
            $canTryAgainQuiz = true;
        }

        $quiz->can_try = $canTryAgainQuiz;
        $quiz->can_download_certificate = $canDownloadCertificate;

        return $quiz;
    }

    private function checkCanAccessToPrivateCourse($course, $user = null): bool
    {
         $user = apiAuth();

        $canAccess = !$course->private;
        $hasBought = $course->checkUserHasBought($user);

        if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin() or $hasBought)) {
            $canAccess = true;
        }

        return $canAccess;
    }

    public function downloadFile($slug, $file_id)
    {
        try {
            $webinar = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
                $file = File::where('webinar_id', $webinar->id)
                    ->where('id', $file_id)
                    ->first();

                if (!empty($file) and $file->downloadable) {
                    $canAccess = true;

                    if ($file->accessibility == 'paid') {
                        $canAccess = $webinar->checkUserHasBought();
                    }

                    if ($canAccess) {
                        if (in_array($file->storage, ['s3', 'external_link'])) {
                            return redirect($file->file);
                        }

                        $filePath = public_path($file->file);

                        if (file_exists($filePath)) {
                            $extension = \Illuminate\Support\Facades\File::extension($filePath);

                            $fileName = str_replace(' ', '-', $file->title);
                            $fileName = str_replace('.', '-', $fileName);
                            $fileName .= '.' . $extension;

                            $headers = array(
                                'Content-Type: application/' . $file->file_type,
                            );

                            return response()->download($filePath, $fileName, $headers);
                        }
                    } else {
                        $toastData = [
                            'title' => trans('public.not_access_toast_lang'),
                            'msg' => trans('public.not_access_toast_msg_lang'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }
                }
            }

            return back();
        } catch (\Exception $e) {
            \Log::error('downloadFile error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function showHtmlFile($slug, $file_id)
    {
        try {
            $webinar = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
                $file = File::where('webinar_id', $webinar->id)
                    ->where('id', $file_id)
                    ->first();

                if (!empty($file)) {
                    $canAccess = true;

                    if ($file->accessibility == 'paid') {
                        $canAccess = $webinar->checkUserHasBought();
                    }

                    if ($canAccess) {
                        $filePath = $file->interactive_file_path;

                        if (\Illuminate\Support\Facades\File::exists(public_path($filePath))) {
                            $data = [
                                'pageTitle' => $file->title,
                                'path' => url($filePath)
                            ];
                            return view('web.default.course.learningPage.interactive_file', $data);
                        }

                        abort(404);
                    } else {
                        $toastData = [
                            'title' => trans('public.not_access_toast_lang'),
                            'msg' => trans('public.not_access_toast_msg_lang'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.'
            ], 403);
        } catch (\Exception $e) {
            \Log::error('showHtmlFile error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getFilePath(Request $request)
    {
        try {
            $this->validate($request, [
                'file_id' => 'required'
            ]);

            $file_id = $request->get('file_id');

            $file = File::where('id', $file_id)
                ->first();

            if (!empty($file)) {
                $webinar = Webinar::where('id', $file->webinar_id)
                    ->where('status', 'active')
                    ->with([
                        'files' => function ($query) {
                            $query->select('id', 'webinar_id', 'file_type')
                                ->where('status', 'active')
                                ->orderBy('order', 'asc');
                        }
                    ])
                    ->first();

                if (!empty($webinar)) {
                    $canAccess = true;

                    if ($file->accessibility == 'paid') {
                        $canAccess = $webinar->checkUserHasBought();
                    }

                    if ($canAccess) {
                        $path = $file->file;

                        if ($file->storage == 'upload') {
                            $path = url("/course/$webinar->slug/file/$file->id/play");
                        } elseif ($file->storage == 'upload_archive') {
                            $path = url("/course/$webinar->slug/file/$file->id/showHtml");
                        }

                        return response()->json([
                            'code' => 200,
                            'storage' => $file->storage,
                            'path' => $path,
                            'storageService' => $file->storage
                        ], 200);
                    }
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.'
            ], 403);
        } catch (\Exception $e) {
            \Log::error('getFilePath error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function playFile($slug, $file_id)
    {
        try {
            $webinar = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
                $file = File::where('webinar_id', $webinar->id)
                    ->where('id', $file_id)
                    ->first();

                if (!empty($file)) {
                    $canAccess = true;

                    if ($file->accessibility == 'paid') {
                        $canAccess = $webinar->checkUserHasBought();
                    }

                    if ($canAccess) {
                        $notVideoSource = ['iframe', 'google_drive', 'dropbox'];

                        if (in_array($file->storage, $notVideoSource)) {
                            $data = [
                                'pageTitle' => $file->title,
                                'iframe' => $file->file
                            ];

                            return view('web.default.course.learningPage.interactive_file', $data);
                        } else if ($file->isVideo()) {
                            return response()->file(public_path($file->file));
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.'
            ], 403);
        } catch (\Exception $e) {
            \Log::error('playFile error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getLesson(Request $request, $slug, $lesson_id)
    {
        try {
            $user = apiAuth();

            $course = Remedy::where('slug', $slug)
                ->where('status', 'active')
                ->with(['teacher', 'textLessons' => function ($query) {
                    $query->orderBy('order', 'asc');
                }])
                ->first();

            if (!empty($course) and $this->checkCanAccessToPrivateCourse($course)) {
                $textLesson = TextLesson::where('id', $lesson_id)
                    ->where('remedy_id', $course->id)
                    ->where('status', RemedyChapter::$chapterActive)
                    ->with([
                        'attachments' => function ($query) {
                            $query->with('file');
                        },
                        'learningStatus' => function ($query) use ($user) {
                            $query->where('user_id', !empty($user) ? $user->id : null);
                        }
                    ])
                    ->first();

                if (!empty($textLesson)) {
                    $canAccess = $course->checkUserHasBought();

                    if ($textLesson->accessibility == 'paid' and !$canAccess) {
                        $toastData = [
                            'title' => trans('public.request_failed'),
                            'msg' => trans('cart.you_not_purchased_this_course'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $checkSequenceContent = $textLesson->checkSequenceContent();
                    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));

                    if (!empty($checkSequenceContent) and $sequenceContentHasError) {
                        $toastData = [
                            'title' => trans('public.request_failed'),
                            'msg' => ($checkSequenceContent['all_passed_items_error'] ? $checkSequenceContent['all_passed_items_error'] . ' - ' : '') . ($checkSequenceContent['access_after_day_error'] ?? ''),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $nextLesson = null;
                    $previousLesson = null;

                    if (!empty($nextLesson)) {
                        $nextLesson->not_purchased = ($nextLesson->accessibility == 'paid' and !$canAccess);
                    }

                    $data = [
                        'pageTitle' => $textLesson->title,

                        'course' => $course,
                        'nextLesson' => $nextLesson,
                        'previousLesson' => $previousLesson,
                    ];

                    return view(getTemplate() . '.course.text_lesson', $data);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('getLesson error: ' . $e->getMessage(), [
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
            $user = apiAuth();

                $course = Remedy::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();

                if (!empty($course)) {
                    $checkCourseForSale = checkCourseForSale($course, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    if (!empty($course->price) and $course->price > 0) {
                        $toastData = [
                            'title' => trans('cart.fail_purchase'),
                            'msg' => trans('cart.course_not_free'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    Sale::create([
                        'buyer_id' => $user->id,
                        'seller_id' => $course->creator_id,
                        'remedy_id' => $course->id,
                        'type' => Sale::$remedy,
                        'payment_method' => Sale::$credit,
                        'amount' => 0,
                        'total_amount' => 0,
                        'created_at' => time(),
                    ]);

                    $notifyOptions = [
                        '[u.name]' => $user->full_name,
                        '[c.title]' => $course->title,
                        '[amount]' => trans('public.free'),
                        '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
                    ];
                    sendNotification("new_course_enrollment", $notifyOptions, 1);

                    $toastData = [
                        'title' => '',
                        'msg' => trans('cart.success_pay_msg_for_free_course'),
                        'status' => 'success'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                abort(404);
        } catch (\Exception $e) {
            \Log::error('free error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function reportWebinar(Request $request, $id)
    {
        try {
            $user = apiAuth();

                $data = $request->all();

                $validator = Validator::make($data, [
                    'reason' => 'required|string',
                    'message' => 'required|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'code' => 422,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $webinar = Webinar::select('id', 'status')
                    ->where('id', $id)
                    ->where('status', 'active')
                    ->first();

                if (!empty($webinar)) {
                    WebinarReport::create([
                        'user_id' => $user->id,
                        'webinar_id' => $webinar->id,
                        'reason' => $data['reason'],
                        'message' => $data['message'],
                        'created_at' => time()
                    ]);

                    $notifyOptions = [
                        '[u.name]' => $user->full_name,
                        '[content_type]' => trans('product.course')
                    ];
                    sendNotification("new_report_item_for_admin", $notifyOptions, 1);

                    return response()->json([
                        'code' => 200
                    ], 200);
                }
        } catch (\Exception $e) {
            \Log::error('reportWebinar error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function learningStatus(Request $request, $id)
    {
        try {
            $user = apiAuth();

                $course = Remedy::where('id', $id)->first();

                if (!empty($course) and $course->checkUserHasBought($user)) {
                    $data = $request->all();

                    $item = $data['item'];
                    $item_id = $data['item_id'];
                    $status = $data['status'];

                    RemedyLearning::where('user_id', $user->id)
                        ->where($item, $item_id)
                        ->delete();

                    if ($status and $status == "true") {
                        RemedyLearning::create([
                            'user_id' => $user->id,
                            $item => $item_id,
                            'created_at' => time()
                        ]);
                    }

                    return response()->json([], 200);
                }
        } catch (\Exception $e) {
            \Log::error('learningStatus error: ' . $e->getMessage(), [
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
            $user = apiAuth();

                $course = Webinar::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();

                if (!empty($course)) {
                    if (empty($course->points)) {
                        $toastData = [
                            'title' => '',
                            'msg' => trans('update.can_not_buy_this_course_with_point'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $availablePoints = $user->getRewardPoints();

                    if ($availablePoints < $course->points) {
                        $toastData = [
                            'title' => '',
                            'msg' => trans('update.you_have_no_enough_points_for_this_course'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $checkCourseForSale = checkCourseForSale($course, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    Sale::create([
                        'buyer_id' => $user->id,
                        'seller_id' => $course->creator_id,
                        'webinar_id' => $course->id,
                        'type' => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount' => 0,
                        'total_amount' => 0,
                        'created_at' => time(),
                    ]);

                    RewardAccounting::makeRewardAccounting($user->id, $course->points, 'withdraw', null, false, RewardAccounting::DEDUCTION);

                    $toastData = [
                        'title' => '',
                        'msg' => trans('update.success_pay_course_with_point_msg'),
                        'status' => 'success'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                abort(404);
        } catch (\Exception $e) {
            \Log::error('buyWithPoint error: ' . $e->getMessage(), [
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
            $user = apiAuth();

            if (!empty($user) and !empty(getFeaturesSettings('direct_classes_payment_button_status'))) {
                $this->validate($request, [
                    'item_id' => 'required',
                    'item_name' => 'nullable',
                ]);

                $data = $request->except('_token');

                $webinarId = $data['item_id'];
                $ticketId = $data['ticket_id'] ?? null;

                $webinar = Webinar::where('id', $webinarId)
                    ->where('private', false)
                    ->where('status', 'active')
                    ->first();

                if (!empty($webinar)) {
                    $checkCourseForSale = checkCourseForSale($webinar, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    $fakeCarts = collect();

                    $fakeCart = new Cart();
                    $fakeCart->creator_id = $user->id;
                    $fakeCart->webinar_id = $webinarId;
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
}
