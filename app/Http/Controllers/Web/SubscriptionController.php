<?php

namespace App\Http\Controllers\Web;

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
use App\Models\Webinar;
use App\Models\Subscription;
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
                // 'chapters' => function ($query) use ($user) {
                //     $query->where('status', WebinarChapter::$chapterActive);
                //     $query->orderBy('order', 'asc');

                //     $query->with([
                //         'chapterItems' => function ($query) {
                //             $query->orderBy('order', 'asc');
                //         }
                //     ]);
                // },
                // 'files' => function ($query) use ($user) {
                //     $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                //         ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                //         ->where('files.status', WebinarChapter::$chapterActive)
                //         ->orderBy('chapterOrder', 'asc')
                //         ->orderBy('files.order', 'asc')
                //         ->with([
                //             'learningStatus' => function ($query) use ($user) {
                //                 $query->where('user_id', !empty($user) ? $user->id : null);
                //             }
                //         ]);
                // },
                'filterOptions',
                'category',
                'teacher',
                // 'reviews' => function ($query) {
                //     $query->where('status', 'active');
                //     $query->with([
                //         'comments' => function ($query) {
                //             $query->where('status', 'active');
                //         },
                //         'creator' => function ($qu) {
                //             $qu->select('id', 'full_name', 'avatar');
                //         }
                //     ]);
                // },
                // 'comments' => function ($query) {
                //     $query->where('status', 'active');
                //     $query->whereNull('reply_id');
                //     $query->with([
                //         'user' => function ($query) {
                //             $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                //         },
                //         'replies' => function ($query) {
                //             $query->where('status', 'active');
                //             $query->with([
                //                 'user' => function ($query) {
                //                     $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                //                 }
                //             ]);
                //         }
                //     ]);
                //     $query->orderBy('created_at', 'desc');
                // },
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

        // if (!$justReturnData) {
        //     $installmentLimitation = $this->installmentContentLimitation($user, $subscription->id, 'webinar_id');

        //     if ($installmentLimitation != "ok") {
        //         return $installmentLimitation;
        //     }
        // }
        if($subscription->private==1){
         if (!$justReturnData) {
            $contentLimitation = $this->checkContentLimitation($user, true);
          
            if ($contentLimitation != "ok") {
                return $contentLimitation;
            }
        }
        }
//   print_r($subscription->private);
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




        // if ($user) {
        //     $quizzes = $this->checkQuizzesResults($user, $quizzes);

        //     if (!empty($subscription->chapters) and count($subscription->chapters)) {
        //         foreach ($subscription->chapters as $chapter) {
        //             if (!empty($chapter->chapterItems) and count($chapter->chapterItems)) {
        //                 foreach ($chapter->chapterItems as $chapterItem) {
        //                     if (!empty($chapterItem->quiz)) {
        //                         $chapterItem->quiz = $this->checkQuizResults($user, $chapterItem->quiz);
        //                     }
        //                 }
        //             }
        //         }
        //     }

        //     if (!empty($subscription->quizzes) and count($subscription->quizzes)) {
        //         $subscription->quizzes = $this->checkQuizzesResults($user, $subscription->quizzes);
        //     }
        // }

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
//         echo "<pre>";
//  print_r($chapterItems);
//  die();
if(isset($user->id)){
if($subscription->id==2069){ 
$webhookurl = 'https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZiMDYzMzA0M2Q1MjY5NTUzNzUxMzUi_pc';
// Collection object
$webhookdata = [
  'id' => $user->id,
  'name' => $user->full_name,
  'mobile' => $user->mobile,
  'email' => $user->email,
];
// Initializes a new cURL session
$webhookcurl = curl_init($webhookurl);
// Set the CURLOPT_RETURNTRANSFER option to true
curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);
// Set the CURLOPT_POST option to true for POST request
curl_setopt($webhookcurl, CURLOPT_POST, true);
// Set the request data as JSON using json_encode function
curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
// Set custom headers for RapidAPI Auth and Content-Type header

// Execute cURL request with all previous settings
$webhookresponse = curl_exec($webhookcurl);
// Close cURL session
curl_close($webhookcurl);
  
}
}
        if ($justReturnData) {
            return $data;
        }

        $agent = new Agent();
        if ($agent->isMobile()){
            return view(getTemplate() . '.subscription.index', $data);
        }else{
            return view('web.default2' . '.subscription.index', $data);
        }
        // return view('web.default.subscription.index', $data);
    }

    public function landingpage($slug)
    {
       

        $data = [
            'slug' => $slug,
        ];
        return view('web.default2.subscription.landingPage.'.$slug,$data);
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

    // public function downloadFile($slug, $file_id)
    // {
    //     $webinar = Webinar::where('slug', $slug)
    //         ->where('status', 'active')
    //         ->first();

    //     if (!empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
    //         $file = File::where('webinar_id', $webinar->id)
    //             ->where('id', $file_id)
    //             ->first();

    //         if (!empty($file) and $file->downloadable) {
    //             $canAccess = true;

    //             if ($file->accessibility == 'paid') {
    //                 $canAccess = $webinar->checkUserHasBought();
    //             }

    //             if ($canAccess) {
    //                 if (in_array($file->storage, ['s3', 'external_link'])) {
    //                     return redirect($file->file);
    //                 }

    //                 $filePath = public_path($file->file);

    //                 if (file_exists($filePath)) {
    //                     $extension = \Illuminate\Support\Facades\File::extension($filePath);

    //                     $fileName = str_replace(' ', '-', $file->title);
    //                     $fileName = str_replace('.', '-', $fileName);
    //                     $fileName .= '.' . $extension;

    //                     $headers = array(
    //                         'Content-Type: application/' . $file->file_type,
    //                     );

    //                     return response()->download($filePath, $fileName, $headers);
    //                 }
    //             } else {
    //                 $toastData = [
    //                     'title' => trans('public.not_access_toast_lang'),
    //                     'msg' => trans('public.not_access_toast_msg_lang'),
    //                     'status' => 'error'
    //                 ];
    //                 return back()->with(['toast' => $toastData]);
    //             }
    //         }
    //     }

    //     return back();
    // }
    
     public function downloadFile($slug, $file_id)
    {
        
        $subscriptions = subscription::where('slug', $slug)
            ->where('status', 'active')
            ->first();
        // print_r($subscriptions);
        // die('sheetal');
        
        if (!empty($subscriptions) and $this->checkCanAccessToPrivateSubscription($subscriptions)) {
          $file = File::where('id', $file_id)->first();

            if (!empty($file) and $file->downloadable) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    // $canAccess = $webinar->checkUserHasBought();
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
                    //   return redirect($filePath);
                        // return response()->download($filePath, $fileName, $headers);
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
    }

    public function showHtmlFile($slug, $file_id)
    {
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
                    // $canAccess = $webinar->checkUserHasBought();
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
                        return view(getTemplate() . '.subscription.learningPage.interactive_file', $data);
                        }else{
                            return view('web.default2' . '.subscription.learningPage.interactive_file', $data);
                        }
                        // return view('web.default.subscription.learningPage.interactive_file', $data);
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
    }

    public function getFilePath(Request $request)
    {
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
                    // $canAccess = $webinar->checkUserHasBought();
                }

                if ($canAccess) {
                    $path = $file->file;

                    if ($file->storage == 'upload') {
                        $path = url("/subscription/$webinar->slug/file/$file->id/play");
                    } elseif ($file->storage == 'upload_archive') {
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
    public function getFilePath1(Request $request)
{
    
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
}
    public function playFile($slug, $file_id)
    {
        // this methode linked from video modal for play local video
        // and linked from file.blade for show google_drive,dropbox,iframe

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
                    // $canAccess = $webinar->checkUserHasBought();
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
                        return view(getTemplate() . '.subscription.learningPage.interactive_file', $data);
                        }else{
                            return view('web.default2' . '.subscription.learningPage.interactive_file', $data);
                        }
                        // return view('web.default.subscription.learningPage.interactive_file', $data);
                    } else if ($file->isVideo()) {
                        return response()->file(public_path($file->file));
                    }
                }
            }
        }

        abort(403);
    }

    public function getLesson(Request $request, $slug, $lesson_id)
    {
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
                if ($agent->isMobile()){
                return view(getTemplate() . '.subscription.text_lesson', $data);
                }else{
                    return view('web.default2' . '.subscription.text_lesson', $data);
                }
                // return view(getTemplate() . '.subscription.text_lesson', $data);
            }
        }

        abort(404);
    }

    public function free(Request $request, $slug)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $subscription = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($subscription)) {
                $checkCourseForSale = checkCourseForSale($subscription, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                if (!empty($subscription->price) and $subscription->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.subscription_not_free'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
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

                $notifyOptions = [
                    '[u.name]' => $user->full_name,
                    '[c.title]' => $subscription->title,
                    '[amount]' => trans('public.free'),
                    '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
                ];
                sendNotification("new_subscription_enrollment", $notifyOptions, 1);

                $toastData = [
                    'title' => '',
                    'msg' => trans('cart.success_pay_msg_for_free_subscription'),
                    'status' => 'success'
                ];
                return back()->with(['toast' => $toastData]);
            }

            abort(404);
        } else {
            return redirect('/login');
        }
    }

    public function reportWebinar(Request $request, $id)
    {
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

    public function learningStatus(Request $request, $id)
    {
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

    public function buyWithPoint($slug)
    {
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
        } else {
            return redirect('/login');
        }
    }

    public function directPayment(Request $request, $slug)
    { 
        
        
        $discountCouponId=0;
        if(session('discountCouponId')){
            $discountCouponId = session('discountCouponId');
        }
        // print_r($discountCouponId);
        // die('hi');
        // if(empty($user = auth()->user())){
        //   $user = User::where('id','2550')->first();
        //     // if (session('cart_id')) {
            
        //     //  $carts = Cart::where('id',session('cart_id'))->orwhere('cart_id',session('cart_id'))->get();
             
        //     //  }
        // }

        // if (!empty($user) and !empty(getFeaturesSettings('direct_classes_payment_button_status'))) {
            // $this->validate($request, [
            //     'item_id' => 'required',
            //     'item_name' => 'nullable',
            // ]);

            // $data = $request->except('_token');
 
            // $webinarId = $data['item_id'];
            // $ticketId = $data['ticket_id'] ?? null;

            $subscription = Subscription::where('slug', $slug)
                ->where('status', 'active')
                ->first();
                
            // $webinarId = $data['item_id'];
                
            // $DiscountCourse = DiscountCourse::where('subscription_id', $webinarId)
            //     ->first();
                 
            //     if($DiscountCourse){
                
            // $Discount = Discount::where('id', $DiscountCourse->discount_id )->where('status', 'active' )
            //     ->first();
            //     }
                
        //   print_r($webinarId);     
                
            $item = $subscription;
            
            $itemPrice = $item->getPrice();
            $price = $item->price;
            // if(!empty(session('discountCouponId'))){
            // $discountId=session('discountCouponId');
            // $discountCoupon = Discount::where('id', $discountId)->first();
            //  $percent = $discountCoupon->percent ?? 1;
            // $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
            // $itemPrice1=$itemPrice-$totalDiscount;
            // }else{
                // $totalDiscount = 0;
                // $itemPrice1=$itemPrice-$totalDiscount;
            // }

            // if (!empty($webinar)) {
            //     $checkCourseForSale = checkCourseForSale($webinar, $user);

            //     if ($checkCourseForSale != 'ok') {
            //         return $checkCourseForSale;
            //     }
            
                $data = [
                    // 'pageTitle' => trans('public.checkout_page_title'),
                    // 'paymentChannels' => $paymentChannels,
                    // 'carts' => $carts,
                    // 'subTotal' => $calculate["sub_total"],
                    // 'totalDiscount' => $totalDiscount,
                    // 'tax' => $calculate["tax"],
                    // 'taxPrice' => $calculate["tax_price"],
                    // 'total' => $calculate["total"],
                    // 'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                    // 'order' => $order,
                    // 'count' => $carts->count(),
                    // 'userCharge' => $user->getAccountingCharge(),
                    // 'discount' => $Discount ?? null,
                    'subscription' => $subscription,
                    'total' => $itemPrice1 ?? $itemPrice,
                ];
                // print_r($data['total']);
                
                $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.cart.buyNowSubscription', $data);
                }else{
                    return view('web.default2' . '.cart.buyNowSubscription', $data);
                }

                // $fakeCarts = collect();

                // $fakeCart = new Cart();
                // $fakeCart->creator_id = $user->id;
                // $fakeCart->webinar_id = $webinarId;
                // $fakeCart->ticket_id = $ticketId;
                // $fakeCart->special_offer_id = null;
                // $fakeCart->created_at = time();

                // $fakeCarts->add($fakeCart);

                // $cartController = new CartController();
// Print_r($request->all());die('mayank');
                // return $cartController->checkout($request, $fakeCarts);
            // }
        // }

        abort(404);
    }
    public function directPayment1(Request $request, $slug)
    { 
        
        
        $discountCouponId=0;
        if(session('discountCouponId')){
            $discountCouponId = session('discountCouponId');
        }
        // print_r($discountCouponId);
        // die('hi');
        // if(empty($user = auth()->user())){
        //   $user = User::where('id','2550')->first();
        //     // if (session('cart_id')) {
            
        //     //  $carts = Cart::where('id',session('cart_id'))->orwhere('cart_id',session('cart_id'))->get();
             
        //     //  }
        // }

        // if (!empty($user) and !empty(getFeaturesSettings('direct_classes_payment_button_status'))) {
            // $this->validate($request, [
            //     'item_id' => 'required',
            //     'item_name' => 'nullable',
            // ]);

            // $data = $request->except('_token');
 
            // $webinarId = $data['item_id'];
            // $ticketId = $data['ticket_id'] ?? null;

            $subscription = Subscription::where('slug', $slug)
                ->where('status', 'active')
                ->first();
                
            // $webinarId = $data['item_id'];
                
            // $DiscountCourse = DiscountCourse::where('subscription_id', $webinarId)
            //     ->first();
                 
            //     if($DiscountCourse){
                
            // $Discount = Discount::where('id', $DiscountCourse->discount_id )->where('status', 'active' )
            //     ->first();
            //     }
                
        //   print_r($webinarId);     
                
            $item = $subscription;
            
            $itemPrice = 0;
            $price = 0;
            // if(!empty(session('discountCouponId'))){
            // $discountId=session('discountCouponId');
            // $discountCoupon = Discount::where('id', $discountId)->first();
            //  $percent = $discountCoupon->percent ?? 1;
            // $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
            // $itemPrice1=$itemPrice-$totalDiscount;
            // }else{
                // $totalDiscount = 0;
                // $itemPrice1=$itemPrice-$totalDiscount;
            // }

            // if (!empty($webinar)) {
            //     $checkCourseForSale = checkCourseForSale($webinar, $user);

            //     if ($checkCourseForSale != 'ok') {
            //         return $checkCourseForSale;
            //     }
            
                $data = [
                    // 'pageTitle' => trans('public.checkout_page_title'),
                    // 'paymentChannels' => $paymentChannels,
                    // 'carts' => $carts,
                    // 'subTotal' => $calculate["sub_total"],
                    // 'totalDiscount' => $totalDiscount,
                    // 'tax' => $calculate["tax"],
                    // 'taxPrice' => $calculate["tax_price"],
                    // 'total' => $calculate["total"],
                    // 'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                    // 'order' => $order,
                    // 'count' => $carts->count(),
                    // 'userCharge' => $user->getAccountingCharge(),
                    // 'discount' => $Discount ?? null,
                    'subscription' => $subscription,
                    'total' => $itemPrice1 ?? $itemPrice,
                ];
                // print_r($data['total']);
                
                $agent = new Agent();
                if ($agent->isMobile()){
                    return view(getTemplate() . '.cart.buyNowSubscription1', $data);
                }else{
                    return view('web.default2' . '.cart.buyNowSubscription1', $data);
                }

                // $fakeCarts = collect();

                // $fakeCart = new Cart();
                // $fakeCart->creator_id = $user->id;
                // $fakeCart->webinar_id = $webinarId;
                // $fakeCart->ticket_id = $ticketId;
                // $fakeCart->special_offer_id = null;
                // $fakeCart->created_at = time();

                // $fakeCarts->add($fakeCart);

                // $cartController = new CartController();
// Print_r($request->all());die('mayank');
                // return $cartController->checkout($request, $fakeCarts);
            // }
        // }

        abort(404);
    }
    
    public function getItem($itemId, $itemType)
    {
       
        if ($itemType == 'subscription') {
            $subscription = Webinar::where('id', $itemId)
                ->where('status', 'active')
                ->first();

            // $hasBought = $subscription->checkUserHasBought($user);
            // $canSale = ($subscription->canSale() and !$hasBought);

            // if ($canSale and !empty($subscription->price)) {
                return $subscription;
            // }
        }

        return null;
    }
}
