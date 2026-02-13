<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\AdvertisingBanner;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\DiscountCourse;
use App\Models\Favorite;
use App\Models\File;
use App\Models\QuizzesResult;
use App\Models\RewardAccounting;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;


use App\Models\TextLesson;
use App\Models\CourseLearning;
use App\Models\WebinarChapter;
use App\Models\WebinarReport;
use App\Models\WebinarExtraDetails;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\User;

use Jenssegers\Agent\Agent;

class WebinarController extends Controller
{
    use CheckContentLimitationTrait;
    use InstallmentsTrait;

    public function course($slug, $justReturnData = false)
    {
        try {
            $user = null;

            if (auth()->check()) {
                $user = auth()->user();
            }

            $course_Astromani_2023 = Webinar::where('slug', 'Astromani_2023')
            ->with([
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
            ])
            ->where('status', 'active')
                ->first();

            $course_Professional = Webinar::where('slug', 'Professional-Astrology-Course')
            ->with([
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
            ])
            ->where('status', 'active')
                ->first();

            $course = Webinar::where('slug', $slug)
                ->with([
                    'quizzes' => function ($query) {
                        $query->where('status', 'active')
                            ->with(['quizResults', 'quizQuestions']);
                    },
                    'tags',
                     'extraDetails',
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

            $hasBought = $course->checkUserHasBought($user, true, true);
            $isPrivate = $course->private;

            if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin())) {
                $isPrivate = false;
            }

            if ($isPrivate and $hasBought) {
                $isPrivate = false;
            }

            if ($isPrivate) {

            }

            $isFavorite = false;

            if (!empty($user)) {
                $isFavorite = Favorite::where('webinar_id', $course->id)
                    ->where('user_id', $user->id)
                    ->first();
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
            if (!empty($course->quizzes)) {
                $webinarContentCount += $course->quizzes->count();
            }
            if (!empty($course->assignments)) {
                $webinarContentCount += $course->assignments->count();
            }

            $advertisingBanners = AdvertisingBanner::where('published', true)
                ->whereIn('position', ['course', 'course_sidebar'])
                ->get();

            $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

            $filesWithoutChapter = $course->files->whereNull('chapter_id');

            $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

            $quizzes = $course->quizzes->whereNull('chapter_id');

            if ($user) {
                $quizzes = $this->checkQuizzesResults($user, $quizzes);

                if (!empty($course->chapters) and count($course->chapters)) {
                    foreach ($course->chapters as $chapter) {
                        if (!empty($chapter->chapterItems) and count($chapter->chapterItems)) {
                            foreach ($chapter->chapterItems as $chapterItem) {
                                if (!empty($chapterItem->quiz)) {
                                    $chapterItem->quiz = $this->checkQuizResults($user, $chapterItem->quiz);
                                }
                            }
                        }
                    }
                }

                if (!empty($course->quizzes) and count($course->quizzes)) {
                    $course->quizzes = $this->checkQuizzesResults($user, $course->quizzes);
                }
            }

            $pageRobot = getPageRobot('course_show');
            $canSale = ($course->canSale() and !$hasBought);

            $showInstallments = true;
            $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

            if ($overdueInstallmentOrders->isNotEmpty() and getInstallmentsSettings('disable_instalments_when_the_user_have_an_overdue_installment')) {
                $showInstallments = false;
            }

            if ($canSale and !empty($course->price) and $course->price > 0 and $showInstallments and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
            }

            if ($canSale and !empty($course->price) and getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
                $cashbackRulesMixin = new CashbackRules($user);
                $cashbackRules = $cashbackRulesMixin->getRules('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
            }

            $data = [
                'pageH1' => $course->h1,
                'pageTitle' => $course->seo_title,
                'pageDescription' => $course->seo_description,
                'pageRobot' => $pageRobot,
                'course' => $course,
                'isFavorite' => $isFavorite,
                'hasBought' => $hasBought,
                'user' => $user,
                'webinarContentCount' => $webinarContentCount,
                'advertisingBanners' => $advertisingBanners->where('position', 'course'),
                'advertisingBannersSidebar' => $advertisingBanners->where('position', 'course_sidebar'),
                'activeSpecialOffer' => $course->activeSpecialOffer(),
                'sessionsWithoutChapter' => $sessionsWithoutChapter,
                'filesWithoutChapter' => $filesWithoutChapter,
                'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
                'quizzes' => $quizzes,
                'installments' => $installments ?? null,
                'cashbackRules' => $cashbackRules ?? null,
                'astromani_23' =>$course_Astromani_2023,
                'course_Professional' =>$course_Professional,

            ];

            if(isset($user->id)){
            if($course->id==2069){
            $webhookurl = 'https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZiMDYzMzA0M2Q1MjY5NTUzNzUxMzUi_pc';

            $webhookdata = [
            'id' => $user->id,
            'name' => $user->full_name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            ];

            $webhookcurl = curl_init($webhookurl);

            curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($webhookcurl, CURLOPT_POST, true);

            curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

            $webhookresponse = curl_exec($webhookcurl);

            curl_close($webhookcurl);

            }
            }
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

    public function landingpage($slug)
    {
        try {
            $data = [
                'slug' => $slug,
            ];
            return view('web.default2.course.landingPage.'.$slug,$data);
        } catch (\Exception $e) {
            \Log::error('landingpage error: ' . $e->getMessage(), [
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
        if (empty($user)) {
            $user = auth()->user();
        }

        $canAccess = !$course->private;

        $hasBought = true;

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
                            if($file->storage =='upload'){
                                 $filePath = Storage::disk('upload')->url($file->file);
                            }

                          return redirect()->away($filePath);

                        }

                        $file = Storage::disk('upload')->download($file->file);

            return $file;
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

                    }

                    if ($canAccess) {
                        $filePath = $file->interactive_file_path;

                        if (\Illuminate\Support\Facades\File::exists(public_path($filePath))) {
                            $data = [
                                'pageTitle' => $file->title,
                                'path' => url($filePath)
                            ];
                            $agent = new Agent();
                            if ($agent->isMobile()){
                            return view(getTemplate() . '.course.learningPage.interactive_file', $data);
                            }else{
                                return view('web.default2' . '.course.learningPage.interactive_file', $data);
                            }

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

            abort(403);
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

            abort(403);
        } catch (\Exception $e) {
            \Log::error('getFilePath error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
        public function getFilePath1(Request $request)
{
        try {
            $this->validate($request, [
            'file_id' => 'required|integer'
            ]);

            $file_id = $request->get('file_id');

            $filePath = File::where('id', $file_id)->value('file');
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
            }

            return response()->json([
            'success' => true,
            'type'=>$extension,
            'file' => $filePath
            ]);
        } catch (\Exception $e) {
            \Log::error('getFilePath1 error: ' . $e->getMessage(), [
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

                    }

                    if ($canAccess) {
                        $notVideoSource = ['iframe', 'google_drive', 'dropbox'];

                        if (in_array($file->storage, $notVideoSource)) {
                            $data = [
                                'pageTitle' => $file->title,
                                'iframe' => $file->file
                            ];
                            $agent = new Agent();
                            if ($agent->isMobile()){
                            return view(getTemplate() . '.course.learningPage.interactive_file', $data);
                            }else{
                                return view('web.default2' . '.course.learningPage.interactive_file', $data);
                            }

                        } else if ($file->isVideo()) {
                            return response()->file(public_path($file->file));
                        }
                    }
                }
            }

            abort(403);
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
            $user = null;

            if (auth()->check()) {
                $user = auth()->user();
            }

            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->with(['teacher', 'textLessons' => function ($query) {
                    $query->orderBy('order', 'asc');
                }])
                ->first();

            if (!empty($course) and $this->checkCanAccessToPrivateCourse($course)) {
                $textLesson = TextLesson::where('id', $lesson_id)
                    ->where('webinar_id', $course->id)
                    ->where('status', WebinarChapter::$chapterActive)
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
                    if (!empty($course->textLessons) and count($course->textLessons)) {
                        $nextLesson = $course->textLessons->where('order', '>', $textLesson->order)->first();
                        $previousLesson = $course->textLessons->where('order', '<', $textLesson->order)->first();
                    }

                    if (!empty($nextLesson)) {
                        $nextLesson->not_purchased = ($nextLesson->accessibility == 'paid' and !$canAccess);
                    }

                    $data = [
                        'pageTitle' => $textLesson->title,
                        'textLesson' => $textLesson,
                        'course' => $course,
                        'nextLesson' => $nextLesson,
                        'previousLesson' => $previousLesson,
                    ];
                    $agent = new Agent();
                    if ($agent->isMobile()){
                    return view(getTemplate() . '.course.text_lesson', $data);
                    }else{
                        return view('web.default2' . '.course.text_lesson', $data);
                    }

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
            if (auth()->check()) {
                $user = auth()->user();

                $course = Webinar::where('slug', $slug)
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
                        'webinar_id' => $course->id,
                        'type' => Sale::$webinar,
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

                    date_default_timezone_set('Asia/Kolkata');

                $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY0MDYzNjA0Mzc1MjZhNTUzMDUxMzYi_pc';

            $webhookdata = [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'country_code' => $user->country_code ?? null,
            'user_mobile' => $user->mobile,
            'user_email' => $user->email,
            'user_role' => $user->role_name,
            'user_password' => $request->password,
            'slug' => $slug,
            'create_at' => date("Y/m/d H:i"),
            'by'=>'login'
            ];

            $webhookcurl = curl_init($webhookurl);

            curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($webhookcurl, CURLOPT_POST, true);

            curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

            $webhookresponse = curl_exec($webhookcurl);

            curl_close($webhookcurl);
            $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/caece889-e99d-4975-a107-341ef58c5f7f';
            if($slug=='learn-free-astrology-course-english'){
                $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/ff19d522-10e4-40e8-99b9-4c61796ac9a4';
            }

            $gohighlevelcurl = curl_init($gohighlevel);

            curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($gohighlevelcurl, CURLOPT_POST, true);

            curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));

            curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
            ]);

            $gohighlevelresponse = curl_exec($gohighlevelcurl);
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

    public function reportWebinar(Request $request, $id)
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();

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
            }

            return response()->json([
                'code' => 401
            ], 200);
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
            if (auth()->check()) {
                $user = auth()->user();

                $course = Webinar::where('id', $id)->first();

                if (!empty($course) and $course->checkUserHasBought($user)) {
                    $data = $request->all();

                    $item = $data['item'];
                    $item_id = $data['item_id'];
                    $status = $data['status'];

                    CourseLearning::where('user_id', $user->id)
                        ->where($item, $item_id)
                        ->delete();

                    if ($status and $status == "true") {
                        CourseLearning::create([
                            'user_id' => $user->id,
                            $item => $item_id,
                            'created_at' => time()
                        ]);
                    }

                    return response()->json([], 200);
                }
            }

            abort(403);
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
            if (auth()->check()) {
                $user = auth()->user();

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

    public function directPayment(Request $request)
    {
        try {
            $discountCouponId=0;
            if(session('discountCouponId')){
                $discountCouponId = session('discountCouponId');
            }

                $this->validate($request, [
                    'item_id' => 'required',
                    'item_name' => 'nullable',
                ]);

                $data = $request->except('_token');

                $webinarId = $data['item_id'];

                $webinar = Webinar::where('id', $webinarId)
                    ->where('private', false)
                    ->where('status', 'active')
                    ->first();

                $DiscountCourse = DiscountCourse::where('course_id', $webinarId)
                    ->first();

                    if($DiscountCourse){

                $Discount = Discount::where('id', $DiscountCourse->discount_id )->where('status', 'active' )
                    ->first();
                    }

                $item = $this->getItem($webinarId, 'course');


                    $data = [

                        'totalDiscount' => $totalDiscount,

                        'discount' => $Discount ?? null,
                        'webinar' => $webinar,
                        'total' => $itemPrice1 ?? $itemPrice,
                    ];

                    $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.cart.buyNow', $data);
                    }else{
                        return view('web.default2' . '.cart.buyNow', $data);
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

    public function getItem($itemId, $itemType)
    {
        try {
            if ($itemType == 'course') {
                $course = Webinar::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                    return $course;

            }

            return null;
        } catch (\Exception $e) {
            \Log::error('getItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
