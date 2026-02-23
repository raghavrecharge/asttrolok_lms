<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Webinar;
use App\Http\Controllers\MailController;

   header('Access-Control-Allow-Origin:  *');
   header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
   header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('send-mail', [MailController::class, 'index']);
Route::group(['prefix' => 'my_api', 'namespace' => 'Api\Panel', 'middleware' => 'signed', 'as' => 'my_api.web.'], function () {
    Route::get('checkout/{user}', 'CartController@webCheckoutRender')->name('checkout');
    Route::get('/charge/{user}', 'PaymentsController@webChargeRender')->name('charge');
    Route::get('/subscribe/{user}/{subscribe}', 'SubscribesController@webPayRender')->name('subscribe');
    Route::get('/registration_packages/{user}/{package}', 'RegistrationPackagesController@webPayRender')->name('registration_packages');
});

Route::group(['prefix' => 'api_sessions'], function () {
    Route::get('/big_blue_button', ['uses' => 'Api\Panel\SessionsController@BigBlueButton'])->name('big_blue_button');
    Route::get('/agora', ['uses' => 'Api\Panel\SessionsController@agora'])->name('agora');

});

Route::get('/mobile-app', 'Web\MobileAppController@index')->middleware(['share'])->name('mobileAppRoute');
Route::get('/maintenance', 'Web\MaintenanceController@index')->middleware(['share'])->name('maintenanceRoute');

/* Emergency Database Update */
Route::get('/emergencyDatabaseUpdate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate');
    $msg1 = \Illuminate\Support\Facades\Artisan::output();

    \Illuminate\Support\Facades\Artisan::call('db:seed --class=SectionsTableSeeder');
    $msg2 = \Illuminate\Support\Facades\Artisan::output();

    \Illuminate\Support\Facades\Artisan::call('clear:all');

    return response()->json([
        'migrations' => $msg1,
        'sections' => $msg2,
    ]);
});

Route::group(['namespace' => 'Auth', 'middleware' => ['check_mobile_app', 'share', 'check_maintenance']], function () {
    Route::get('/', [\App\Http\Controllers\Web\HomeController::class, 'index']);
    Route::get('/login', 'LoginController@showLoginForm');
    Route::post('/login', 'LoginController@login');
    Route::get('/logout', 'LoginController@logout');
    Route::get('/register', 'RegisterController@showRegistrationForm');
    Route::post('/register', 'RegisterController@register');
    Route::get('/verification', 'VerificationController@index');
    Route::post('/verification', 'VerificationController@confirmCode');
    Route::get('/verification/resend', 'VerificationController@resendCode');
    Route::get('/forget-password', 'ForgotPasswordController@showLinkRequestForm');
    Route::post('/forget-password', 'ForgotPasswordController@forgot');
    Route::get('reset-password/{token}', 'ResetPasswordController@showResetForm');
    Route::post('/reset-password', 'ResetPasswordController@updatePassword');
    Route::get('/google', 'SocialiteController@redirectToGoogle');
    Route::get('/googles', 'SocialiteController@redirectToGoogle1');
    Route::get('/google/callback', 'SocialiteController@handleGoogleCallback');
    Route::get('/facebook/redirect', 'SocialiteController@redirectToFacebook');
    Route::get('/facebook/callback', 'SocialiteController@handleFacebookCallback');
    Route::get('/reff/{code}', 'ReferralController@referral');
});
//#######################################301#######################################################
// Route::get('/home', function () {
//     return redirect('/');
// });

Route::redirect('/courses/free-astrology-course', '/classes', 301);
Route::redirect('/index.php/course/3-days-astrology-workshop-2', '/classes', 301);
Route::redirect('/public/index.php/course/learn-free-vedic-astrology-course-online', '/classes', 301);
Route::redirect('/welcome/view_article/36624/Saturn-in-1st-House-of--Vedic-Astrology', '/classes', 301);
Route::redirect('/welcome/view_article/34404', '/', 301);
Route::redirect('/products/Business-Software', '/products', 301);
Route::redirect('/products/Painting-tools', '/products', 301);
Route::redirect('/products/Where-the-Crawdads-Sing-e-book', '/products', 301);
Route::redirect('/categories/astrology/Astrology-Basic', '/categories/astrology', 301);

Route::redirect('/consult-with-astrologers', '/consultation', 301);
Route::redirect('/course/Astroshiromani_2024', '/course/astroshiromani-2024', 301);
Route::redirect('/course/learning/Astroshiromani_2024', 'astroshiromani-2024', 301);
Route::redirect('/categories/Astrology/classes', '/categories/astrology', 301);
Route::redirect('/course/Astrology-Advance-Level/file/985/download', '/course/astrology-advance-level/file/985/download', 301);
Route::redirect('/course/learning/Astrology-Advance-Level/file/985/download', 'astrology-advance-level/file/985/download', 301);
Route::redirect('/pages/About', '/about', 301);
Route::redirect('/home/index.php', '/', 301);
Route::redirect('/public', '/', 301);
Route::redirect('/course/Basic_Astrology_Course', '/course/basic-astrology-course', 301);
Route::redirect('/course/learning/Basic_Astrology_Course', 'basic-astrology-course', 301);
Route::redirect('/categories/Astrology', '/categories/astrology', 301);
Route::redirect('/categories/Astrology/Astrology-Basic', '/categories/astrology/astrology-basic', 301);
Route::redirect('/categories/Ayurveda', '/categories/ayurveda', 301);
Route::redirect('/categories/Numerology', '/categories/numerology', 301);
Route::redirect('/categories/Palmistry', '/categories/palmistry', 301);
Route::redirect('/categories/Vastu', '/categories/vastu', 301);
// Route::redirect('/classes', '/classes', 301);
Route::redirect('/course/Astrology-Advance-Level', '/course/astrology-advance-level', 301);
Route::redirect('/course/Astrology-Basic-Level', '/course/astrology-basic-level', 301);
Route::redirect('/course/Astrology-Basic-Level-English', '/course/astrology-basic-level-english', 301);
Route::redirect('/course/Astrology-Intermediate-Level', '/course/astrology-intermediate-level', 301);
Route::redirect('/course/Astromani_2023', '/course/astromani-2023', 301);
Route::redirect('/course/Astromani_2024', '/course/astromani-2024', 301);
Route::redirect('/course/Ayurveda', '/course/learn-ayurveda', 301);
Route::redirect('/course/Jaimini-Astrology', '/course/learn-jaimini-astrology', 301);
Route::redirect('/course/KP-Astrology', '/course/learn-kp-astrology', 301);
Route::redirect('/course/Marriage-and-Children-Astrology', '/course/learn-marriage-and-children-astrology', 301);
Route::redirect('/course/Medical-Astrology', '/course/learn-medical-astrology', 301);
Route::redirect('/course/Numerology', '/course/learn-numerology', 301);
Route::redirect('/course/Numerology-English', '/course/learn-numerology-english', 301);
Route::redirect('/course/Palmistry', '/course/learn-palmistry', 301);
Route::redirect('/course/Panchang-and-Muhurat', '/course/learn-panchang-and-muhurat', 301);
Route::redirect('/course/Panchang-workshop-2023', '/course/learn-panchang-workshop-2023', 301);
Route::redirect('/course/Planets_in_different_houses', '/course/learn-planets-in-different-houses', 301);
Route::redirect('/course/Prashna-Shastra', '/course/learn-prashna-shastra', 301);
Route::redirect('/course/Prashna-Shastra-English', '/course/learn-prashna-shastra-english', 301);
Route::redirect('/course/Professional-Astrology-Course', '/course/learn-professional-astrology-course', 301);
Route::redirect('/course/Vastu-Shastra', '/course/learn-vastu-shastra', 301);
Route::redirect('/register-course/Astromani_2024', '/register-course/astromani-2024', 301);
Route::redirect('/landingpage/Astromani_2024', '/landingpage/astromani-2024', 301);
Route::redirect('/landingpage/Astroshiromani-2024', '/landingpage/astroshiromani-2024', 301);




Route::redirect('/course/learning/Astrology-Advance-Level', 'astrology-advance-level', 301);
Route::redirect('/course/learning/Astrology-Basic-Level', 'astrology-basic-level', 301);
Route::redirect('/course/learning/Astrology-Basic-Level-English', 'astrology-basic-level-english', 301);
Route::redirect('/course/learning/Astrology-Intermediate-Level', 'astrology-intermediate-level', 301);
Route::redirect('/course/learning/Astromani_2023', 'astromani-2023', 301);
Route::redirect('/course/learning/Astromani_2024', 'astromani-2024', 301);
Route::redirect('/course/learning/Ayurveda', 'learn-ayurveda', 301);
Route::redirect('/course/learning/Jaimini-Astrology', 'learn-jaimini-astrology', 301);
Route::redirect('/course/learning/KP-Astrology', 'learn-kp-astrology', 301);
Route::redirect('/course/learning/Marriage-and-Children-Astrology', 'learn-marriage-and-children-astrology', 301);
Route::redirect('/course/learning/Medical-Astrology', 'learn-medical-astrology', 301);
Route::redirect('/course/learning/Numerology', 'learn-numerology', 301);
Route::redirect('/course/learning/Numerology-English', 'learn-numerology-english', 301);
Route::redirect('/course/learning/Palmistry', 'learn-palmistry', 301);
Route::redirect('/course/learning/Panchang-and-Muhurat', 'learn-panchang-and-muhurat', 301);
Route::redirect('/course/learning/Panchang-workshop-2023', 'learn-panchang-workshop-2023', 301);
Route::redirect('/course/learning/Planets_in_different_houses', 'learn-planets-in-different-houses', 301);
Route::redirect('/course/learning/Prashna-Shastra', 'learn-prashna-shastra', 301);
Route::redirect('/course/learning/Prashna-Shastra-English', 'learn-prashna-shastra-english', 301);
Route::redirect('/course/learning/Professional-Astrology-Course', 'learn-professional-astrology-course', 301);
Route::redirect('/course/learning/Vastu-Shastra', 'learn-vastu-shastra', 301);

//######################################301#######################################################


Route::get('/free-download', function (Illuminate\Http\Request $request) {
    $fileUrl = $request->query('url');
    $title = $request->query('title', 'file.pdf');

    // Agar URL nahi mila to 404
    if (!$fileUrl) {
        abort(404);
    }

    // Remote file fetch kare
    $response = Http::get($fileUrl);

    if ($response->ok()) {
        return Response::make($response->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $title . '"',
        ]);
    }

    abort(404);
});




Route::group(['namespace' => 'Web', 'middleware' => ['check_mobile_app', 'impersonate', 'share', 'check_maintenance']], function () {
    Route::get('/stripe', function () {
        return view('web.default.cart.channels.stripe');
    });
    Route::redirect('/public', '/', 301);
    Route::fallback(function () {
        return view("errors.404", ['pageTitle' => trans('public.error_404_page_title')]);
    });

    // set Locale
    Route::post('/locale', 'LocaleController@setLocale')->name('appLocaleRoute');

    // set Locale
    Route::post('/set-currency', 'SetCurrencyController@setCurrency');
    
    Route::get('/cronjob', 'InstallmentsController@cronJob');
    
     Route::get('/chatbot', 'HomeController@chatbot');
    Route::get('/checkvideo', 'HomeController@checkvideo');
    Route::get('/events', 'HomeController@redirect');
    Route::get('/consultation', 'HomeController@redirect');
    Route::get('/contact/store', 'HomeController@redirect');
    Route::get('/contact-us', 'HomeController@redirect');
    Route::get('/index.php/welcome/', 'HomeController@redirect');
    Route::get('/g/collect', 'HomeController@redirect');
    Route::post('/events', 'HomeController@redirect');
    Route::post('/consultation', 'HomeController@redirect');
    Route::post('/contact/store', 'HomeController@redirect');
    Route::post('/contact-us', 'HomeController@redirect');
    Route::post('/index.php/welcome/', 'HomeController@redirect');
    Route::post('/g/collect', 'HomeController@redirect');
    Route::get('/thank-you', 'HomeController@thankyou');
    
    Route::get('/know-more', 'HomeController@knowmore');
    
    Route::get('/about', 'HomeController@about');
    Route::post('/webhook-url', 'HomeController@webhookdata');
    Route::get('/landingpage/{slug}', 'WebinarController@landingpage');
    Route::get('/register-course/{slug}', 'InstallmentsController@partPayment');
    Route::post('/register-course/{slug}', 'RegisterController@registerForCourse');
    Route::get('/quick-pay/{slug}', 'QuickPayController@show');
    Route::get('/sync-installment-payments/{orderId?}', [Web\InstallmentPaymentSyncController::class, 'syncPaymentsFromPartPayments'])->name('sync.installment.payments');
    Route::post('/sync-installment-order/{orderId}', [Web\InstallmentPaymentSyncController::class, 'syncOrder'])->name('sync.installment.order');
    Route::get('/process-sub-step-payment', 'InstallmentsController@processSubStepPayment');
    Route::get('/login-free', 'LoginController@showLoginForm');
    Route::post('/login-free', 'LoginController@login');
    Route::get('/login-free-english', 'LoginController@showLoginFormEnglish');
    Route::post('/login-free-english', 'LoginController@login');
    Route::get('/register-free', 'RegisterController@showRegistrationFormForFree');
    Route::get('/register-free-english', 'RegisterController@showRegistrationFormForFreeEnglish');
    Route::post('/register-free/{slug}', 'RegisterController@registerForFree');
    Route::get('/fullAccess111', 'InstallmentsController@fullAccess');
    Route::get('/fullAccess2', 'InstallmentsController@fullAccess2');
    Route::get('/directAccess111', 'InstallmentsController@directAccess111');
    Route::get('/shortpaymentsection', 'InstallmentsController@shortPaymentSection');
    Route::get('/checkCourseAccess1', 'InstallmentsController@checkCourseAccess1');
    Route::get('/shortpaymentsection1', 'InstallmentsController@shortPaymentSection1');
    Route::get('/updatePaymentSection', 'InstallmentsController@updatePaymentSection');
    Route::get('/astrpshiromani111', 'InstallmentsController@astrpshiromani');
    
    Route::post('/get-user-id', 'UserController@getUserId');
    
    Route::post('/set-session', 'CartController@setSession')->name('set.session');
    Route::post('/unset-session', 'CartController@unsetSession')->name('unset.session');
    

    // Unified payment routes
    Route::post('/payments/initiate', 'PaymentController@initiatePayment')
        ->name('payments.initiate');
    
    Route::get('/payments/callback', 'PaymentController@handleCallback')
        ->name('payments.callback');
    
    // Webhook (must exclude from CSRF)
    Route::post('/webhooks/razorpay', 'RazorpayWebhookController@handle')
        ->name('webhooks.razorpay');
        
     
     Route::get("robots.txt" , function () {
return \Illuminate\Support\Facades\Redirect::to('robots.txt');
 });
 Route::get("llm.txt" , function () {
return \Illuminate\Support\Facades\Redirect::to('llm.txt');
 });
//      Route::get("sitemap.xml" , function () {
// return \Illuminate\Support\Facades\Redirect::to('sitemap.xml');
//  });
      Route::get("sitemap.xml" , function () {
    $blogs = Blog::latest()->get();
    $Webinars= Webinar::where('status','active')->latest()->get();
    return response()->view('web.sitemap', [
            'blogs' => $blogs,
            'webinars' => $Webinars,
        ])->header('Content-Type', 'text/xml');
 });
      Route::group(['prefix' => 'assets'], function () {
        Route::get('/frontend/new_lms/images/{slug}', 'HomeController@redirect');
    });
     
  Route::get('/favorites/{slug}/{slug2}', 'HomeController@redirect');
  
    Route::get('/getDefaultAvatar', 'DefaultAvatarController@make');
    
    Route::get('/payment/success', 'InstallmentsController@requestSubmitted');
    
    Route::group(['prefix' => 'course'], function () {
        Route::get('/{slug}', 'WebinarController@course');
        Route::get('/{slug}/file/{file_id}/download', 'WebinarController@downloadFile');
        Route::get('/{slug}/file/{file_id}/showHtml', 'WebinarController@showHtmlFile');
        Route::get('/{slug}/lessons/{lesson_id}/read', 'WebinarController@getLesson');
        Route::post('/getFilePath', 'WebinarController@getFilePath');
        Route::post('/getFilePath1', 'WebinarController@getFilePath1');
        Route::get('/{slug}/file/{file_id}/play', 'WebinarController@playFile');
        Route::get('/{slug}/free', 'WebinarController@free');
        Route::get('/{slug}/points/apply', 'WebinarController@buyWithPoint');
        Route::post('/{id}/report', 'WebinarController@reportWebinar');
        Route::post('/{id}/learningStatus', 'WebinarController@learningStatus');
        Route::post('/buy-now', 'WebinarController@directPayment');
        Route::post('/direct-payment', 'WebinarController@directPayment');
        Route::get('/{slug}/installments', 'WebinarController@getInstallmentsByCourse');
        
        Route::group(['middleware' => 'web.auth'], function () {
            

            Route::post('/learning/itemInfo', 'LearningPageController@getItemInfo');
            Route::get('/learning/{slug}', 'LearningPageController@index');
            Route::get('/learning1/{slug}', 'LearningPageController@inst');
            Route::get('/learning2/{slug}', 'LearningPageController@inststep');
            Route::get('/learning/{slug}/noticeboards', 'LearningPageController@noticeboards');
            Route::get('/assignment/{assignmentId}/download/{id}/attach', 'LearningPageController@downloadAssignment');
            Route::post('/assignment/{assignmentId}/history/{historyId}/message', 'AssignmentHistoryController@storeMessage');
            Route::post('/assignment/{assignmentId}/history/{historyId}/setGrade', 'AssignmentHistoryController@setGrade');
            Route::get('/assignment/{assignmentId}/history/{historyId}/message/{messageId}/downloadAttach', 'AssignmentHistoryController@downloadAttach');

            Route::group(['prefix' => '/learning/{slug}/forum'], function () { // LearningPageForumTrait
                Route::get('/', 'LearningPageController@forum');
                Route::post('/store', 'LearningPageController@forumStoreNewQuestion');
                Route::get('/{forumId}/edit', 'LearningPageController@getForumForEdit');
                Route::post('/{forumId}/update', 'LearningPageController@updateForum');
                Route::post('/{forumId}/pinToggle', 'LearningPageController@forumPinToggle');
                Route::get('/{forumId}/downloadAttach', 'LearningPageController@forumDownloadAttach');

                Route::group(['prefix' => '/{forumId}/answers'], function () {
                    Route::get('/', 'LearningPageController@getForumAnswers');
                    Route::post('/', 'LearningPageController@storeForumAnswers');
                    Route::get('/{answerId}/edit', 'LearningPageController@answerEdit');
                    Route::post('/{answerId}/update', 'LearningPageController@answerUpdate');
                    Route::post('/{answerId}/{togglePinOrResolved}', 'LearningPageController@answerTogglePinOrResolved');
                });
            });

            // Route::post('/direct-payment', 'WebinarController@directPayment');
        });
    });
    Route::group(['prefix' => 'subscriptions'], function () {
        Route::get('/{slug}', 'SubscriptionController@subscription');
        Route::get('/{slug}/file/{file_id}/download', 'SubscriptionController@downloadFile');
        Route::get('/{slug}/file/{file_id}/showHtml', 'SubscriptionController@showHtmlFile');
        Route::get('/{slug}/lessons/{lesson_id}/read', 'SubscriptionController@getLesson');
        Route::post('/getFilePath', 'SubscriptionController@getFilePath');
        Route::get('/{slug}/file/{file_id}/play', 'SubscriptionController@playFile');
        Route::get('/{slug}/free', 'SubscriptionController@free');
        Route::get('/{slug}/points/apply', 'SubscriptionController@buyWithPoint');
        Route::post('/{id}/report', 'SubscriptionController@reportWebinar');
        Route::post('/{id}/learningStatus', 'SubscriptionController@learningStatus');
        Route::post('/buy-now', 'SubscriptionController@directPayment');
        Route::get('/direct-payment/{slug}', 'SubscriptionController@directPayment');
        Route::get('/direct-payment1/{slug}', 'SubscriptionController@directPayment1');
        Route::get('/{slug}/installments', 'SubscriptionController@getInstallmentsByCourse');
         Route::get('/update-status', 'SubscriptionController@updateStatus');
       

        Route::group(['middleware' => 'web.auth'], function () {
            

            Route::post('/learning/itemInfo', 'SubscriptionLearningPageController@getItemInfo');
            Route::get('/learning/{slug}', 'SubscriptionLearningPageController@index');
            Route::get('/learning1/{slug}', 'SubscriptionLearningPageController@inst');
            Route::get('/learning2/{slug}', 'SubscriptionLearningPageController@inststep');
            Route::get('/learning/{slug}/noticeboards', 'SubscriptionLearningPageController@noticeboards');
            Route::get('/assignment/{assignmentId}/download/{id}/attach', 'SubscriptionLearningPageController@downloadAssignment');
            Route::post('/assignment/{assignmentId}/history/{historyId}/message', 'AssignmentHistoryController@storeMessage');
            Route::post('/assignment/{assignmentId}/history/{historyId}/setGrade', 'AssignmentHistoryController@setGrade');
            Route::get('/assignment/{assignmentId}/history/{historyId}/message/{messageId}/downloadAttach', 'AssignmentHistoryController@downloadAttach');

            Route::group(['prefix' => '/learning/{slug}/forum'], function () { // LearningPageForumTrait
                Route::get('/', 'LearningPageController@forum');
                Route::post('/store', 'LearningPageController@forumStoreNewQuestion');
                Route::get('/{forumId}/edit', 'LearningPageController@getForumForEdit');
                Route::post('/{forumId}/update', 'LearningPageController@updateForum');
                Route::post('/{forumId}/pinToggle', 'LearningPageController@forumPinToggle');
                Route::get('/{forumId}/downloadAttach', 'LearningPageController@forumDownloadAttach');

                Route::group(['prefix' => '/{forumId}/answers'], function () {
                    Route::get('/', 'LearningPageController@getForumAnswers');
                    Route::post('/', 'LearningPageController@storeForumAnswers');
                    Route::get('/{answerId}/edit', 'LearningPageController@answerEdit');
                    Route::post('/{answerId}/update', 'LearningPageController@answerUpdate');
                    Route::post('/{answerId}/{togglePinOrResolved}', 'LearningPageController@answerTogglePinOrResolved');
                });
            });

            // Route::post('/direct-payment', 'WebinarController@directPayment');
        });
    });
    Route::post('/direct-payment', 'WebinarController@directPayment');
    Route::group(['prefix' => 'remedy'], function () {
        Route::get('/{slug}', 'RemedyController@remedy');
        Route::get('/{slug}/file/{file_id}/download', 'WebinarController@downloadFile');
        Route::get('/{slug}/file/{file_id}/showHtml', 'WebinarController@showHtmlFile');
        Route::get('/{slug}/lessons/{lesson_id}/read', 'WebinarController@getLesson');
        Route::post('/getFilePath', 'WebinarController@getFilePath');
        Route::get('/{slug}/file/{file_id}/play', 'WebinarController@playFile');
        Route::get('/{slug}/free', 'WebinarController@free');
        Route::get('/{slug}/points/apply', 'WebinarController@buyWithPoint');
        Route::post('/{id}/report', 'WebinarController@reportWebinar');
        Route::post('/{id}/learningStatus', 'WebinarController@learningStatus');

        Route::group(['middleware' => 'web.auth'], function () {
            
            Route::get('/{slug}/installments', 'WebinarController@getInstallmentsByCourse');

            Route::post('/learning/itemInfo', 'LearningPageController@getItemInfo');
            Route::get('/learning/{slug}', 'LearningPageController@index');
            Route::get('/learning/{slug}/noticeboards', 'LearningPageController@noticeboards');
            Route::get('/assignment/{assignmentId}/download/{id}/attach', 'LearningPageController@downloadAssignment');
            Route::post('/assignment/{assignmentId}/history/{historyId}/message', 'AssignmentHistoryController@storeMessage');
            Route::post('/assignment/{assignmentId}/history/{historyId}/setGrade', 'AssignmentHistoryController@setGrade');
            Route::get('/assignment/{assignmentId}/history/{historyId}/message/{messageId}/downloadAttach', 'AssignmentHistoryController@downloadAttach');

            Route::group(['prefix' => '/learning/{slug}/forum'], function () { // LearningPageForumTrait
                Route::get('/', 'LearningPageController@forum');
                Route::post('/store', 'LearningPageController@forumStoreNewQuestion');
                Route::get('/{forumId}/edit', 'LearningPageController@getForumForEdit');
                Route::post('/{forumId}/update', 'LearningPageController@updateForum');
                Route::post('/{forumId}/pinToggle', 'LearningPageController@forumPinToggle');
                Route::get('/{forumId}/downloadAttach', 'LearningPageController@forumDownloadAttach');

                Route::group(['prefix' => '/{forumId}/answers'], function () {
                    Route::get('/', 'LearningPageController@getForumAnswers');
                    Route::post('/', 'LearningPageController@storeForumAnswers');
                    Route::get('/{answerId}/edit', 'LearningPageController@answerEdit');
                    Route::post('/{answerId}/update', 'LearningPageController@answerUpdate');
                    Route::post('/{answerId}/{togglePinOrResolved}', 'LearningPageController@answerTogglePinOrResolved');
                });
            });

            Route::post('/direct-payment', 'WebinarController@directPayment');
        });
    });

    Route::group(['prefix' => 'certificate_validation'], function () {
        Route::get('/', 'CertificateValidationController@index');
        Route::post('/validate', 'CertificateValidationController@checkValidate');
    });


    Route::group(['prefix' => 'cart'], function () {
        Route::post('/store', 'CartManagerController@store');
        Route::get('/{id}/delete', 'CartManagerController@destroy');
    });
    
     Route::get('fullnewaccess', 'InstallmentsController@fullAccessForm');
     Route::post('fullnewaccess/', 'InstallmentsController@fullAccess');

     Route::get('directnewaccess', 'InstallmentsController@directAccessForm');
     Route::post('directnewaccess/', 'InstallmentsController@directAccess');
     
    Route::group(['prefix' => 'cart'], function () {
        Route::get('/', 'CartController@cart1');
        Route::post('/', 'CartController@cart1');
        Route::get('cart1/', 'CartController@cart1');
        Route::post('cart1/', 'CartController@cart1');
        
       

        Route::post('/coupon/validate', 'CartController@couponValidate');
        Route::get('/coupon/validate1', 'CartController@couponValidate1');
        Route::post('/coupon/validate1', 'CartController@couponValidate1');
        Route::get('/coupon/validate2', 'CartController@couponValidate2');
        Route::post('/coupon/validate2', 'CartController@couponValidate2');
        Route::get('/coupon/validate3', 'CartController@couponValidate3');
        Route::post('/coupon/validate3', 'CartController@couponValidate3');
        Route::post('/checkout', 'CartController@checkout')->name('checkout');
    });

    Route::group(['middleware' => 'web.auth'], function () {
        
            Route::get('fullaccess', 'InstallmentsController@fullAccessForm');
            Route::post('fullaccess/', 'InstallmentsController@fullAccess');

        Route::group(['prefix' => 'laravel-filemanager'], function () {
            \UniSharp\LaravelFilemanager\Lfm::routes();
        });

        Route::group(['prefix' => 'reviews'], function () {
            Route::post('/store', 'WebinarReviewController@store');
            Route::post('/store-reply-comment', 'WebinarReviewController@storeReplyComment');
            Route::get('/{id}/delete', 'WebinarReviewController@destroy');
            Route::get('/{id}/delete-comment/{commentId}', 'WebinarReviewController@destroy');
        });

        Route::group(['prefix' => 'favorites'], function () {
            Route::get('{slug}/toggle', 'FavoriteController@toggle');
            Route::post('/{id}/update', 'FavoriteController@update');
            Route::get('/{id}/delete', 'FavoriteController@destroy');
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::post('/store', 'CommentController@store');
            Route::post('/{id}/reply', 'CommentController@storeReply');
            Route::post('/{id}/update', 'CommentController@update');
            Route::post('/{id}/report', 'CommentController@report');
            Route::get('/{id}/delete', 'CommentController@destroy');
        });

        // Route::group(['prefix' => 'cart'], function () {
        //     Route::get('/', 'CartController@cart1');
        //     Route::post('/', 'CartController@cart1');
        //     Route::get('cart1/', 'CartController@cart1');
        //     Route::post('cart1/', 'CartController@cart1');

        //     Route::post('/coupon/validate', 'CartController@couponValidate');
        //     Route::get('/coupon/validate1', 'CartController@couponValidate1');
        //     Route::post('/coupon/validate1', 'CartController@couponValidate1');
        //     Route::post('/checkout', 'CartController@checkout')->name('checkout');
        // });

        Route::group(['prefix' => 'users'], function () {
            Route::get('/{id}/follow', 'UserController@followToggle');
        });

        Route::group(['prefix' => 'become-instructor'], function () {
            Route::get('/', 'BecomeInstructorController@index')->name('becomeInstructor');
            Route::get('/packages', 'BecomeInstructorController@packages')->name('becomeInstructorPackages');
            Route::get('/packages/{id}/checkHasInstallment', 'BecomeInstructorController@checkPackageHasInstallment');
            Route::get('/packages/{id}/installments', 'BecomeInstructorController@getInstallmentsByRegistrationPackage');
            Route::post('/', 'BecomeInstructorController@store');
        });

    });

    Route::group(['prefix' => 'meetings'], function () {
        Route::post('/reserve', 'MeetingController@reserve');
        Route::post('/reserve15', 'MeetingController@reserve15');
        Route::post('/reserve_panel', 'MeetingController@reserve_panel');
        Route::post('/reserve15_panel', 'MeetingController@reserve15_panel');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/{id}/{name}', 'UserController@profile');
        Route::post('/{id}/availableTimes', 'UserController@availableTimes');
        Route::post('/{id}/availableTimes1', 'UserController@availableTimes1');
        Route::post('/{id}/send-message', 'UserController@sendMessage');
    });

    Route::group(['prefix' => 'payments'], function () {
        Route::post('/payment-request', 'PaymentController@paymentRequest');
        // Route::get('/verify/{gateway}', ['as' => 'payment_verify', 'uses' => 'PaymentController@paymentVerify']);
        Route::get('/verify/{gateway}', ['as' => 'payment_verify', 'uses' => 'PaymentController@BuyNowProccess']);
        Route::post('/verify/{gateway}', ['as' => 'payment_verify_post', 'uses' => 'PaymentController@paymentVerify']);
        Route::get('/status', 'PaymentController@payStatus');
        Route::get('/payku/callback/{id}', 'PaymentController@paykuPaymentVerify')->name('payku.result');
    });

    Route::group(['prefix' => 'subscribes'], function () {
        Route::get('/apply/{webinarSlug}', 'SubscribeController@apply');
        Route::get('/apply/bundle/{bundleSlug}', 'SubscribeController@bundleApply');
    });

    Route::group(['prefix' => 'search'], function () {
        Route::get('/', 'SearchController@index');
    });
    Route::get('/categories/{slug}', 'CategoriesController@index');
    Route::group(['prefix' => 'categories'], function () {
         Route::get('/{categoryTitle}/{subCategoryTitle?}', 'CategoriesController@index');
        
    });

    Route::group(['prefix' => 'remedies'], function () {
        Route::get('/', 'RemedyController@remedies');
        Route::get('/{slug}', 'RemedyController@remedies');
    });

    Route::get('/classes', 'ClassesController@index');
    
    Route::get('/verifysubscriptionaccess', 'PaymentController@directaccess123456');

    Route::get('/reward-courses', 'RewardCoursesController@index');

    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'BlogController@index');
        Route::get('/categories/{category}', 'BlogController@index')->name('blog.category');
        Route::get('/{slug}', 'BlogController@show');
    });

    Route::group(['prefix' => 'contact'], function () {
        Route::get('/', 'ContactController@index');
        Route::post('/store', 'ContactController@store');
        Route::post('/course', 'ContactController@course');
    });

    Route::group(['prefix' => 'consult-with-astrologers'], function () {
        Route::get('/', 'UserController@instructors');
    });
      Route::group(['prefix' => 'consultation'], function () {
        Route::get('/', 'UserController@instructors');
    });
    Route::get('razorpay', 'RazorpayController@index');
    Route::group(['prefix' => 'razorpay'], function () {
        Route::get('/paynow', 'RazorpayController@paynow');
        // Route::get('/', 'RazorpayController@index');
        Route::post('/pay', 'RazorpayController@pay');
        Route::get('/consultationdetailsshow', 'RazorpayController@consultationdetailsshow');
        Route::get('/bookmeeting', 'RazorpayController@bookmeeting');
        Route::get('/consultationdetails', 'RazorpayController@consultationdetails');
        Route::post('/consultationdetails', 'RazorpayController@consultationdetails');
        Route::get('/thankyou', 'RazorpayController@thankyou');
    });

    // Route::group(['prefix' => 'organizations'], function () {
    //     Route::get('/', 'UserController@organizations');
    // });
    
    // seo chnages y
    Route::get('/organizations', function () {
        return redirect('/');
    });
    // Route::get('/index.php/course/3-days-astrology-workshop-2', function () {
    // return redirect('/classes', 301);
    // });
    
    // Route::get('/public/index.php/course/learn-free-vedic-astrology-course-online', function () {
    //     return redirect('/classes', 301);
    // });
    
    // Route::get('/courses/free-astrology-course', function () {
    //     return redirect('/classes', 301);
    // });
    
    // Route::get('/welcome/view_article/34404', function () {
    //     return redirect('/classes', 301);
    // });
    
    // Route::get('/welcome/view_article/36624/Saturn-in-1st-House-of--Vedic-Astrology', function () {
    //     return redirect('/classes', 301);
    // });
    
    Route::get('/index.php/course/{any}', function ($any) {
    return redirect('/classes', 301);
    })->where('any', '.*');
    
    // Redirect all /public/index.php/course/* URLs to /classes
    Route::get('/public/index.php/course/{any}', function ($any) {
        return redirect('/classes', 301);
    })->where('any', '.*');
    
    // Redirect all /courses/* URLs to /classes
    Route::get('/courses/{any}', function ($any) {
        return redirect('/classes', 301);
    })->where('any', '.*');
    
    // Redirect all /welcome/view_article/* URLs to /classes
    Route::get('/welcome/view_article/{any}', function ($any) {
        return redirect('/classes', 301);
    })->where('any', '.*');
        
    Route::get('/{id}.html', function ($id) {
        if (is_numeric($id)) {
            return redirect('/', 301);
        }
    });

    
    
    


    Route::group(['prefix' => 'load_more'], function () {
        Route::get('/{role}', 'UserController@handleInstructorsOrOrganizationsPage');
    });

    Route::group(['prefix' => 'pages'], function () {
        Route::get('/{link}', 'PagesController@index');
    });

    // Captcha
    Route::group(['prefix' => 'captcha'], function () {
        Route::post('create', function () {
            $response = ['status' => 'success', 'captcha_src' => captcha_src('flat')];

            return response()->json($response);
        });
        Route::get('{config?}', '\Mews\Captcha\CaptchaController@getCaptcha');
    });

    Route::post('/newsletters', 'UserController@makeNewsletter');

    Route::group(['prefix' => 'jobs'], function () {
        Route::get('/{methodName}', 'JobsController@index');
        Route::post('/{methodName}', 'JobsController@index');
         Route::get('/sendInstallmentReminders', 'JobsController@sendInstallmentReminders');
    });

    Route::group(['prefix' => 'regions'], function () {
        Route::get('/provincesByCountry/{countryId}', 'RegionController@provincesByCountry');
        Route::get('/citiesByProvince/{provinceId}', 'RegionController@citiesByProvince');
        Route::get('/districtsByCity/{cityId}', 'RegionController@districtsByCity');
    });

    Route::group(['prefix' => 'instructor-finder'], function () {
        Route::get('/', 'InstructorFinderController@index');
        Route::get('/wizard', 'InstructorFinderController@wizard');
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', 'ProductController@searchLists');
        Route::get('/{slug}', 'ProductController@show');
        Route::post('/{slug}/points/apply', 'ProductController@buyWithPoint');

        Route::group(['prefix' => 'reviews'], function () {
            Route::post('/store', 'ProductReviewController@store');
            Route::post('/store-reply-comment', 'ProductReviewController@storeReplyComment');
            Route::get('/{id}/delete', 'ProductReviewController@destroy');
            Route::get('/{id}/delete-comment/{commentId}', 'ProductReviewController@destroy');
        });

        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{slug}/installments', 'ProductController@getInstallmentsByProduct');
            Route::post('/direct-payment', 'ProductController@directPayment');
        });
    });

    Route::get('/reward-products', 'RewardProductsController@index');

    Route::group(['prefix' => 'bundles'], function () {
        Route::get('/{slug}', 'BundleController@index');
        Route::get('/{slug}/free', 'BundleController@free');
      Route::get('/{slug}/{type}', 'BundleController@showConsultants')->name('bundle.consultation.consultants');
       Route::get('/profile/{id}/{name}', 'BundleController@profile')->name('bundle.consultation.profile');
       Route::post('/meetings/book', 'BundleController@book')->name('bundle.consultation.book');
        
        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{slug}/favorite', 'BundleController@favoriteToggle');
            Route::get('/{slug}/points/apply', 'BundleController@buyWithPoint');

            Route::group(['prefix' => 'reviews'], function () {
                Route::post('/store', 'BundleReviewController@store');
                Route::post('/store-reply-comment', 'BundleReviewController@storeReplyComment');
                Route::get('/{id}/delete', 'BundleReviewController@destroy');
                Route::get('/{id}/delete-comment/{commentId}', 'BundleReviewController@destroy');
            });

            Route::post('/direct-payment', 'BundleController@directPayment');
        });
    });
    Route::get('/tutorial-guide', 'ForumController@tutorialGuide');
    Route::group(['prefix' => 'forums'], function () {
        Route::get('/', 'ForumController@index');
        Route::get('/create-topic', 'ForumController@createTopic');
        Route::post('/create-topic', 'ForumController@storeTopic');
        Route::get('/search', 'ForumController@search');

        Route::group(['prefix' => '/{slug}/topics'], function () {
            Route::get('/', 'ForumController@topics');
            Route::post('/{topic_slug}/likeToggle', 'ForumController@topicLikeToggle');
            Route::get('/{topic_slug}/edit', 'ForumController@topicEdit');
            Route::post('/{topic_slug}/edit', 'ForumController@topicUpdate');
            Route::post('/{topic_slug}/bookmark', 'ForumController@topicBookmarkToggle');
            Route::get('/{topic_slug}/downloadAttachment/{attachment_id}', 'ForumController@topicDownloadAttachment');

            Route::group(['prefix' => '/{topic_slug}/posts'], function () {
                Route::get('/', 'ForumController@posts');
                Route::post('/', 'ForumController@storePost');
                Route::post('/report', 'ForumController@storeTopicReport');
                Route::get('/{post_id}/edit', 'ForumController@postEdit');
                Route::post('/{post_id}/edit', 'ForumController@postUpdate');
                Route::post('/{post_id}/likeToggle', 'ForumController@postLikeToggle');
                Route::post('/{post_id}/un_pin', 'ForumController@postUnPin');
                Route::post('/{post_id}/pin', 'ForumController@postPin');
                Route::get('/{post_id}/downloadAttachment', 'ForumController@postDownloadAttachment');
            });
        });
    });

    Route::group(['prefix' => 'cookie-security'], function () {
        Route::post('/all', 'CookieSecurityController@setAll');
        Route::post('/customize', 'CookieSecurityController@setCustomize');
    });


    Route::group(['prefix' => 'upcoming_courses'], function () {
        Route::get('/', 'UpcomingCoursesController@index');
        Route::get('{slug}', 'UpcomingCoursesController@show');
        Route::get('{slug}/toggleFollow', 'UpcomingCoursesController@toggleFollow');
        Route::get('{slug}/favorite', 'UpcomingCoursesController@favorite');
        Route::post('{id}/report', 'UpcomingCoursesController@report');
    });

    Route::group(['prefix' => 'installments'], function () {
        // Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/request_submitted', 'InstallmentsController@requestSubmitted');
            Route::get('/request_rejected', 'InstallmentsController@requestRejected');
            Route::get('/{id}', 'InstallmentsController@index');
            //Route::get('/{id}/store', 'InstallmentsController@store');
            Route::get('/{id}/store', 'InstallmentsController@cronJob');
        // });
    });

    Route::group(['prefix' => 'waitlists'], function () {
        Route::post('/join', 'WaitlistController@store');
    });

    Route::group(['prefix' => 'gift'], function () {
        Route::group(['middleware' => 'web.auth'], function () {
            Route::get('/{item_type}/{item_slug}', 'GiftController@index');
            Route::post('/{item_type}/{item_slug}', 'GiftController@store');
        });
    });

    // Offline Payment Routes
    Route::group(['prefix' => 'offline-payment', 'middleware' => 'web.auth'], function () {
        Route::get('/create/{webinarId}', 'OfflinePaymentController@create')->name('offline_payment.create');
        Route::get('/show/{paymentId}', 'OfflinePaymentController@show')->name('offline_payment.show');
        Route::get('/', 'OfflinePaymentController@index')->name('offline_payment.index');
    });

    // Event Payment Routes
    Route::group(['prefix' => 'events'], function () {
        Route::get('/pay/{eventId}/{token}', 'EventPaymentController@showPaymentPage')->name('events.payment');
        Route::post('/pay/{eventId}/{token}/process', 'EventPaymentController@processPayment')->name('events.payment.process');
        Route::post('/pay/{eventId}/{token}/verify', 'EventPaymentController@verifyPayment')->name('events.payment.verify');
        Route::get('/pay/{eventId}/{token}/success', 'EventPaymentController@paymentSuccess')->name('events.payment.success');
        Route::get('/pay/{eventId}/{token}/failed', 'EventPaymentController@paymentFailed')->name('events.payment.failed');
    });


});
