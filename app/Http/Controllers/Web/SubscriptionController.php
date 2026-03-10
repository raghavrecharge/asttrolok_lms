<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\SubscriptionWebinarChapterItems;
use App\Models\AdvertisingBanner;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\DiscountCourse;
use App\Models\Favorite;
use App\Models\File;
use App\Models\QuizzesResult;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\TextLesson;
use App\Models\CourseLearning;
use App\Models\WebinarChapter;
use App\Models\WebinarReport;
use App\Models\SubscriptionExtraDetails;
use App\Models\Webinar;
use App\Models\Subscription;
use App\Models\SubscriptionAccess;
use App\Services\PaymentEngine\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\User;

use Jenssegers\Agent\Agent;

class SubscriptionController extends Controller
{
    use CheckContentLimitationTrait;
    use InstallmentsTrait;

    public function subscription($slug, $justReturnData = false)
    {
        try {
            $user = null;

            if (auth()->check()) {
                $user = auth()->user();
            }
            if (empty($user)) {
                $user = apiAuth();
            }

            $subscription = Subscription::where('slug', $slug)
                ->with([
                'tags',
                'faqs' => function ($query) {
                $query->orderBy('order', 'asc');
            },

                'filterOptions',
                'category',
                'extraDetails',
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

            if ($subscription->private == 1) {
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

            // No auto-redirect — enrolled users can still view the subscription details page.
            // The "Start Learning" button on the page links to the learning page.

            $isFavorite = false;

            if (!empty($user)) {
                $isFavorite = Favorite::where('webinar_id', $subscription->id)
                    ->where('user_id', $user->id)
                    ->first();
            }

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

            $advertisingBanners = AdvertisingBanner::where('published', true)
                ->whereIn('position', ['subscription', 'subscription_sidebar'])
                ->get();

            $pageRobot = getPageRobot('subscription_show');
            $canSale = ($subscription->canSale() and !$hasBought);

            $showInstallments = true;
            $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

            if ($overdueInstallmentOrders->isNotEmpty() and getInstallmentsSettings('disable_instalments_when_the_user_have_an_overdue_installment')) {
                $showInstallments = false;
            }

            if ($canSale and !empty($subscription->price) and $subscription->price > 0 and $showInstallments and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('subscriptions', $subscription->id, $subscription->type, $subscription->category_id, $subscription->teacher_id);
            }

            if ($canSale and !empty($subscription->price) and getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
                $cashbackRulesMixin = new CashbackRules($user);
                $cashbackRules = $cashbackRulesMixin->getRules('subscriptions', $subscription->id, $subscription->type, $subscription->category_id, $subscription->teacher_id);
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
                'pageRobot' => $pageRobot,
                'subscription' => $subscription,
                'isFavorite' => $isFavorite,
                'hasBought' => $hasBought,
                'user' => $user,
                'webinarContentCount' => $webinarContentCount,
                'advertisingBanners' => $advertisingBanners->where('position', 'subscription'),
                'advertisingBannersSidebar' => $advertisingBanners->where('position', 'subscription_sidebar'),
                'activeSpecialOffer' => $subscription->activeSpecialOffer(),

                'installments' => $installments ?? null,
                'cashbackRules' => $cashbackRules ?? null,

                'chapterItems' => $chapterItems,

            ];

            if (isset($user->id)) {
                if ($subscription->id == 2069) {
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

                    curl_setopt($webhookcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));

                    $webhookresponse = curl_exec($webhookcurl);

                    curl_close($webhookcurl);

                }
            }
            if ($justReturnData) {
                return $data;
            }

            $agent = new Agent();
            if ($agent->isMobile()) {
                return view(getTemplate() . '.subscription.index', $data);
            }
            else {
                return view('web.default2' . '.subscription.index', $data);
            }
        }
        catch (\Exception $e) {
            \Log::error('subscription error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function subscription1($slug, $justReturnData = false)
    {

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

        // print_r($subscription);die();

        if (empty($subscription)) {
            return $justReturnData ? false : back();
        }


        if ($subscription->private == 1) {
            if (!$justReturnData) {
                $contentLimitation = $this->checkContentLimitation($user, true);

                if ($contentLimitation != "ok") {
                    return $contentLimitation;
                }
            }
        }        //   print_r($subscription->private);
        $hasBought = $subscription->checkUserHasBought($user, true, true);
        $isPrivate = $subscription->private;

        if (!empty($user) and ($user->id == $subscription->creator_id or $user->organ_id == $subscription->creator_id or $user->isAdmin())) {
            $isPrivate = false;
        }

        if ($isPrivate and $hasBought) { // check the user has bought the subscription or not
            $isPrivate = false;
        }

        if ($isPrivate) {
            // echo 'ok';
            return $justReturnData ? false : back();
        }

        $isFavorite = false;

        if (!empty($user)) {
            $isFavorite = Favorite::where('webinar_id', $subscription->id)
                ->where('user_id', $user->id)
                ->first();
        }

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

        $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['subscription', 'subscription_sidebar'])
            ->get();


        $pageRobot = getPageRobot('subscription_show'); // index
        $canSale = ($subscription->canSale() and !$hasBought);

        /* Installments */
        $showInstallments = true;
        $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

        if ($overdueInstallmentOrders->isNotEmpty() and getInstallmentsSettings('disable_instalments_when_the_user_have_an_overdue_installment')) {
            $showInstallments = false;
        }

        if ($canSale and !empty($subscription->price) and $subscription->price > 0 and $showInstallments and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans('subscriptions', $subscription->id, $subscription->type, $subscription->category_id, $subscription->teacher_id);
        }

        /* Cashback Rules */
        if ($canSale and !empty($subscription->price) and getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $cashbackRules = $cashbackRulesMixin->getRules('subscriptions', $subscription->id, $subscription->type, $subscription->category_id, $subscription->teacher_id);
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
            'pageRobot' => $pageRobot,
            'subscription' => $subscription,
            'isFavorite' => $isFavorite,
            'hasBought' => $hasBought,
            'user' => $user,
            'webinarContentCount' => $webinarContentCount,
            'advertisingBanners' => $advertisingBanners->where('position', 'subscription'),
            'advertisingBannersSidebar' => $advertisingBanners->where('position', 'subscription_sidebar'),
            'activeSpecialOffer' => $subscription->activeSpecialOffer(),
            // 'sessionsWithoutChapter' => $sessionsWithoutChapter,
            // 'filesWithoutChapter' => $filesWithoutChapter,
            // 'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
            // 'quizzes' => $quizzes,
            'installments' => $installments ?? null,
            'cashbackRules' => $cashbackRules ?? null,
            // 'astromani_23' =>$subscription_Astromani_2023,
            // 'subscription_Professional' =>$subscription_Professional,
            'chapterItems' => $chapterItems,

        ];

        if ($justReturnData) {
            return $data;
        }

        $agent = new Agent();
        if ($agent->isMobile()) {
            return view(getTemplate() . '.subscription.index', $data);
        }
        else {
            return view('web.default2' . '.subscription.index', $data);
        }
    // return view('web.default.subscription.index', $data);
    }

    public function landingpage($slug)
    {
        try {
            $data = [
                'slug' => $slug,
            ];
            return view('web.default2.subscription.landingPage.' . $slug, $data);
        }
        catch (\Exception $e) {
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

            $quiz->result_status = $status_pass ?QuizzesResult::$passed : $userQuizDone->first()->status;

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

    private function checkCanAccessToPrivateSubscription($subscription, $user = null): bool
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $canAccess = !$subscription->private;
        $hasBought = $subscription->checkUserHasBought($user);

        if (!empty($user) and ($user->id == $subscription->creator_id or $user->organ_id == $subscription->creator_id or $user->isAdmin() or $hasBought)) {
            $canAccess = true;
        }

        return $canAccess;
    }

    public function downloadFile($slug, $file_id)
    {
        try {
            $subscriptions = subscription::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($subscriptions) and $this->checkCanAccessToPrivateSubscription($subscriptions)) {
                $file = File::where('id', $file_id)->first();

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
                            if ($file->storage == 'upload') {
                                $filePath = Storage::disk('upload')->url($file->file);
                            }

                            return redirect()->away($filePath);

                        }
                        $file = Storage::disk('upload')->download($file->file);

                        return $file;
                    }
                    else {
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
        }
        catch (\Exception $e) {
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

            if (!empty($webinar) and $this->checkCanAccessToPrivateSubscription($webinar)) {
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
                            if ($agent->isMobile()) {
                                return view(getTemplate() . '.subscription.learningPage.interactive_file', $data);
                            }
                            else {
                                return view('web.default2' . '.subscription.learningPage.interactive_file', $data);
                            }

                        }

                        abort(404);
                    }
                    else {
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
        }
        catch (\Exception $e) {
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
                            $path = url("/subscription/$webinar->slug/file/$file->id/play");
                        }
                        elseif ($file->storage == 'upload_archive') {
                            $path = url("/subscription/$webinar->slug/file/$file->id/showHtml");
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
        }
        catch (\Exception $e) {
            \Log::error('getFilePath error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
    public function getFilePath1(Request $request)    {
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
                'type' => $extension,
                'file' => $filePath
            ]);
        }
        catch (\Exception $e) {
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
                            if ($agent->isMobile()) {
                                return view(getTemplate() . '.subscription.learningPage.interactive_file', $data);
                            }
                            else {
                                return view('web.default2' . '.subscription.learningPage.interactive_file', $data);
                            }

                        }
                        else if ($file->isVideo()) {
                            return response()->file(public_path($file->file));
                        }
                    }
                }
            }

            abort(403);
        }
        catch (\Exception $e) {
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

            $subscription = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->with(['teacher', 'textLessons' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
                ->first();

            if (!empty($subscription) and $this->checkCanAccessToPrivateCourse($subscription)) {
                $textLesson = TextLesson::where('id', $lesson_id)
                    ->where('webinar_id', $subscription->id)
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
                    $canAccess = $subscription->checkUserHasBought();

                    if ($textLesson->accessibility == 'paid' and !$canAccess) {
                        $toastData = [
                            'title' => trans('public.request_failed'),
                            'msg' => trans('cart.you_not_purchased_this_subscription'),
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
                    if (!empty($subscription->textLessons) and count($subscription->textLessons)) {
                        $nextLesson = $subscription->textLessons->where('order', '>', $textLesson->order)->first();
                        $previousLesson = $subscription->textLessons->where('order', '<', $textLesson->order)->first();
                    }

                    if (!empty($nextLesson)) {
                        $nextLesson->not_purchased = ($nextLesson->accessibility == 'paid' and !$canAccess);
                    }

                    $data = [
                        'pageTitle' => $textLesson->title,
                        'textLesson' => $textLesson,
                        'subscription' => $subscription,
                        'nextLesson' => $nextLesson,
                        'previousLesson' => $previousLesson,
                    ];
                    $agent = new Agent();
                    if ($agent->isMobile()) {
                        return view(getTemplate() . '.subscription.text_lesson', $data);
                    }
                    else {
                        return view('web.default2' . '.subscription.text_lesson', $data);
                    }

                }
            }

            abort(404);
        }
        catch (\Exception $e) {
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
            if (!auth()->check()) {
                return redirect('/login');
            }

            $user = auth()->user();

            $subscription = Subscription::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (empty($subscription)) {
                abort(404);
            }

            // Check if already enrolled — prevent duplicate free enrollment
            $existingAccess = SubscriptionAccess::where('subscription_id', $subscription->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingAccess) {
                return redirect($subscription->getLearningPageUrl())->with(['toast' => [
                        'title' => '',
                        'msg' => 'You are already enrolled in this subscription.',
                        'status' => 'info'
                    ]]);
            }

            DB::beginTransaction();

            // Grant free-enrollment access via shared service.
            // access_content_count = 0; resolver will add free_video_count on top.
            // See SubscriptionAccessResolver: unlockedItemCount = 0 + free_video_count.
            $accessTillDate = time() + ($subscription->access_days * 24 * 60 * 60);

            $accessService = app(\App\Services\SubscriptionAccessService::class);
            $accessService->grantFreeAccess($user->id, $subscription->id, $accessTillDate);

            // UPE: Create free subscription sale (also handles legacy Sale dual-write)
            $checkout = app(CheckoutService::class);
            $checkout->processSubscriptionPurchase($user->id, $subscription->id, 0, 'free', null);

            DB::commit();

            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[c.title]' => $subscription->title,
                '[amount]' => trans('public.free'),
                '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            ];
            sendNotification("new_subscription_enrollment", $notifyOptions, 1);

            Log::info('Free subscription enrollment', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'free_video_count' => $subscription->free_video_count,
                'access_till_date' => date('Y-m-d', $accessTillDate),
            ]);

            return redirect($subscription->getLearningPageUrl())->with(['toast' => [
                    'title' => 'Welcome!',
                    'msg' => 'You have been enrolled successfully! You have access to ' . $subscription->free_video_count . ' free videos for ' . $subscription->access_days . ' days.',
                    'status' => 'success'
                ]]);

        }
        catch (\Exception $e) {
            DB::rollBack();
            \Log::error('free enrollment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with(['toast' => [
                    'title' => 'Error',
                    'msg' => 'Something went wrong during enrollment. Please try again.',
                    'status' => 'error'
                ]]);
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
                        '[content_type]' => trans('product.subscription')
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
        }
        catch (\Exception $e) {
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

                $subscription = Webinar::where('id', $id)->first();

                if (!empty($subscription) and $subscription->checkUserHasBought($user)) {
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
        }
        catch (\Exception $e) {
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

                $subscription = Webinar::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();

                if (!empty($subscription)) {
                    if (empty($subscription->points)) {
                        $toastData = [
                            'title' => '',
                            'msg' => trans('update.can_not_buy_this_subscription_with_point'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $availablePoints = $user->getRewardPoints();

                    if ($availablePoints < $subscription->points) {
                        $toastData = [
                            'title' => '',
                            'msg' => trans('update.you_have_no_enough_points_for_this_subscription'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $checkCourseForSale = checkCourseForSale($subscription, $user);

                    if ($checkCourseForSale != 'ok') {
                        return $checkCourseForSale;
                    }

                    Sale::create([
                        'buyer_id' => $user->id,
                        'seller_id' => $subscription->creator_id,
                        'webinar_id' => $subscription->id,
                        'type' => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount' => 0,
                        'total_amount' => 0,
                        'created_at' => time(),
                    ]);

                    RewardAccounting::makeRewardAccounting($user->id, $subscription->points, 'withdraw', null, false, RewardAccounting::DEDUCTION);

                    $toastData = [
                        'title' => '',
                        'msg' => trans('update.success_pay_subscription_with_point_msg'),
                        'status' => 'success'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                abort(404);
            }
            else {
                return redirect('/login');
            }
        }
        catch (\Exception $e) {
            \Log::error('buyWithPoint error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function directPayment(Request $request, $slug)
    {
        try {
            $discountCouponId = 0;
            if (session('discountCouponId')) {
                $discountCouponId = session('discountCouponId');
            }

            $subscription = Subscription::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (empty($subscription)) {
                abort(404);
            }

            $user = auth()->user();

            // Check if user has already enrolled (has SubscriptionAccess record)
            $hasEnrolled = false;
            if ($user) {
                $subscriptionAccess = SubscriptionAccess::where('subscription_id', $subscription->id)
                    ->where('user_id', $user->id)
                    ->first();
                $hasEnrolled = !empty($subscriptionAccess);
            }

            // New user (never enrolled) → show FREE enrollment page
            if (!$hasEnrolled) {
                $data = [
                    'subscription' => $subscription,
                ];

                $agent = new Agent();
                if ($agent->isMobile()) {
                    return view(getTemplate() . '.cart.buyNowSubscriptionFree', $data);
                }
                else {
                    return view('web.default2' . '.cart.buyNowSubscriptionFree', $data);
                }
            }

            // Already enrolled → show PAID options (one-time + AutoPay)
            $itemPrice = $subscription->getPrice();

            // Get wallet balance for logged-in users
            $walletBalance = 0;
            if (auth()->check()) {
                $walletBalance = app(\App\Services\PaymentEngine\WalletService::class)->balance(auth()->id());
            }

            $data = [
                'subscription' => $subscription,
                'total' => $itemPrice,
                'walletBalance' => $walletBalance,
            ];

            $agent = new Agent();
            if ($agent->isMobile()) {
                return view(getTemplate() . '.cart.buyNowSubscription', $data);
            }
            else {
                return view('web.default2' . '.cart.buyNowSubscription', $data);
            }

        }
        catch (\Exception $e) {
            \Log::error('directPayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
    public function directPayment1(Request $request, $slug)
    {
        try {
            $discountCouponId = 0;
            if (session('discountCouponId')) {
                $discountCouponId = session('discountCouponId');
            }

            $subscription = Subscription::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            $item = $subscription;

            // Use the shared getPrice() method to handle discounts/special offers correctly
            $itemPrice = $subscription->getPrice();

            $data = [
                'subscription' => $subscription,
                'total' => $itemPrice,
            ];

            $agent = new Agent();
            if ($agent->isMobile()) {
                return view(getTemplate() . '.cart.buyNowSubscription1', $data);
            }
            else {
                return view('web.default2' . '.cart.buyNowSubscription1', $data);
            }

            abort(404);
        }
        catch (\Exception $e) {
            \Log::error('directPayment1 error: ' . $e->getMessage(), [
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
            if ($itemType == 'subscription') {
                $subscription = Webinar::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                return $subscription;

            }

            return null;
        }
        catch (\Exception $e) {
            \Log::error('getItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
