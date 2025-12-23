<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Panel\ContactFormController;
Route::group(['middleware' => ['api.auth']], function () {

    Route::get('/', function () {
        return 'test panel';
    });
   
   Route::post('/contact', [ContactFormController::class, 'store']);
    Route::group(['prefix' => '/comments'], function () {
        Route::get('/', ['uses' => 'CommentsController@list']);
        Route::post('/', ['uses' => 'CommentsController@store', 'middleware' => 'api.request.type']);
        Route::delete('/{id}', ['uses' => 'CommentsController@destroy']);
        Route::put('/{id}', ['uses' => 'CommentsController@update', 'middleware' => 'api.request.type']);
        Route::post('/{id}/reply', ['uses' => 'CommentsController@reply', 'middleware' => 'api.request.type']);
        Route::post('/{id}/report', ['uses' => 'CommentsController@report', 'middleware' => 'api.request.type']);

    });
    Route::get('/quick-info', ['uses' => 'SummaryController@list']);
    Route::post('/webinars/{id}/free', ['uses' => 'WebinarsController@free']);
    Route::group(['prefix' => 'subscribe'], function () {
        Route::get('/', ['uses' => 'SubscribesController@index']);
        //     Route::post('/', ['uses' => 'SubscribesController@pay']);
        Route::post('/web_pay', ['uses' => 'SubscribesController@webPayGenerator']);
        Route::post('/apply', ['uses' => 'SubscribesController@apply']);
        Route::post('/general_apply', ['uses' => 'SubscribesController@generalApply']);

    });
    Route::get('/webinars/purchases', ['uses' => 'WebinarsController@indexPurchases']);
    Route::get('/webinars/summary', ['uses' => 'WebinarsController@PurchaseCourseSummary']);
    Route::get('/subscription', ['uses' => 'WebinarsController@SubscriptionData']);
    Route::get('/subscription/summary', ['uses' => 'WebinarsController@SubscriptionDataSummary']);
    Route::get('/webinars/organization', ['uses' => 'WebinarsController@indexOrganizations']);
    Route::group(['prefix' => '/reviews'], function () {
        Route::get('/', ['uses' => 'WebinarReviewController@list']);
        Route::post('/', ['uses' => 'WebinarReviewController@store', 'middleware' => 'api.request.type']);
        Route::post('/{id}/reply', ['uses' => 'WebinarReviewController@reply', 'middleware' => 'api.request.type']);
        Route::delete('/{id}', ['uses' => 'WebinarReviewController@destroy']);
        //  Route::put('/{id}', ['uses' => 'CommentsController@update', 'middleware' => 'format']);
    });
    Route::group(['prefix' => '/support'], function () {

        Route::get('/class_support', ['uses' => 'SupportsController@classSupport']);
        Route::get('/my_class_support', ['uses' => 'SupportsController@myClassSupport']);
        Route::get('/tickets', ['uses' => 'SupportsController@platformSupport']);

        Route::get('departments', ['uses' => 'SupportDepartmentsController@index']);


        Route::get('/{id}', ['uses' => 'SupportsController@show']);
        Route::get('/{id}/close', ['uses' => 'SupportsController@close']);

        Route::post('/', ['uses' => 'SupportsController@store']);

        Route::post('/{id}/conversations', ['uses' => 'SupportsController@storeConversations']);


        // SupportDepartmentsController

    });
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', ['uses' => 'NotificationsController@list']);
        Route::post('/{id}/seen', ['uses' => 'NotificationsController@seen']);
    });
    /***** Favorites ******/
    Route::group(['prefix' => 'favorites'], function () {
        Route::get('/', ['uses' => 'FavoritesController@list']);
        Route::post('/toggle/{id}', ['uses' => 'FavoritesController@toggle']);
        Route::post('/toggle2', ['uses' => 'FavoritesController@toggle2']);
        Route::delete('/{id}', ['uses' => 'FavoritesController@destroy']);
    });
    Route::group(['prefix' => '/classes'], function () {
        Route::get('/', ['uses' => 'WebinarsController@list', 'middleware' => ['api.level-access:teacher']]);
    });
    Route::group(['prefix' => '/meetings'], function () {
        Route::post('/{id}/finish', ['uses' => 'ReserveMeetingsController@finish']);
        Route::get('/overview', ['uses' => 'ReserveMeetingsController@reservation']);
        Route::get('requests', ['uses' => 'ReserveMeetingsController@requests']);
        Route::get('/', ['uses' => 'ReserveMeetingsController@index']);
        Route::get('/{id}', ['uses' => 'ReserveMeetingsController@show']);

    });
    
    Route::group(['prefix' => '/rewards'], function () {

        Route::get('/', ['uses' => 'RewardsController@index']);
        Route::post('/exchange', ['uses' => 'RewardsController@exchange']);
        Route::post('/webinar/{id}/apply', ['uses' => 'RewardsController@buyWithPoint']);
        //    Route::get('/{slug}/points/apply', 'WebinarController@buyWithPoint');
        Route::get('/reward-courses', ['uses' => 'RewardsController@courses']);

    });
    Route::group(['prefix' => '/registration-packages', 'middleware' => ['api.level-access:teacher']], function () {

        Route::get('/', ['uses' => 'RegistrationPackagesController@index']);
        Route::post('/pay', ['uses' => 'RegistrationPackagesController@webPayGenerator']);
    });
    Route::group(['prefix' => '/quizzes'], function () {

        Route::get('created', ['uses' => 'QuizzesController@created', 'middleware' => ['api.level-access:teacher']]);
        Route::get('not_participated', ['uses' => 'QuizzesController@notParticipated']);
        Route::get('{quizId}/result', ['uses' => 'QuizzesController@resultsByQuiz']);

        Route::get('results/my-results', ['uses' => 'QuizzesResultController@myResults']);
        Route::get('results/my-student-result', ['uses' => 'QuizzesResultController@myStudentResult', 'middleware' => ['api.level-access:teacher']]);
        Route::get('results/{quizResultId}/status', ['uses' => 'QuizzesResultController@status']);
        //    Route::get('results/{quizResultId}/download', 'CertificatesController@download');
        Route::get('results/{quizResultId}/show', 'CertificatesController@makeCertificate');
        Route::get('/learning/itemInfo/{id}', 'QuizzesController@show');
        Route::post('results/{quizResultId}/review',
            ['uses' => 'QuizzesResultController@updateResult', 'middleware' => ['api.level-access:teacher']]);


        Route::get('{id}/start', ['uses' => 'QuizzesResultController@start']);
        Route::post('{id}/store-result', ['uses' => 'QuizzesResultController@quizzesStoreResult']);


    });
    Route::get('certificates/achievements', ['uses' => 'CertificatesController@achievements']);
    Route::get('certificates/created', ['uses' => 'CertificatesController@created', 'middleware' => ['api.level-access:teacher']]);
    Route::get('certificates/students', ['uses' => 'CertificatesController@students', 'middleware' => ['api.level-access:teacher']]);
    Route::post('/become_instructor', ['uses' => 'UsersController@store']);
    Route::post('/users/{id}/follow', ['uses' => 'UsersController@followToggle']);
    Route::group(['prefix' => '/cart'], function () {
        Route::get('list', ['uses' => 'CartController@index']);
        Route::delete('{id}', ['uses' => 'CartController@destroy']);
        Route::post('coupon/validate3', ['uses' => 'CartController@validateCoupon3']);
        Route::post('coupon/validate2', ['uses' => 'CartController@validateCoupon2']);
        Route::post('coupon/validate1', ['uses' => 'CartController@validateCoupon1']);
        Route::post('coupon/validate', ['uses' => 'CartController@validateCoupon']);
        Route::post('checkout', ['uses' => 'CartController@checkout']);
        Route::post('payment/verify', ['uses' => 'PaymentsController@paymentVerify']);
        Route::post('store', ['uses' => 'CartController@store']);
        Route::post('/', ['uses' => 'AddCartController@store']);
        Route::post('web_checkout', ['uses' => 'CartController@webCheckoutGenerator']);


    });
    
   
        Route::get('PaymentChannel', ['uses' => 'CartController@PaymentChannel']);
    
    
     Route::group(['prefix' => '/buynow'], function () {
        // Route::post('/', ['uses' => 'PaymentsController@BuyNowProccess']);
        Route::post('/', ['uses' => 'PaymentsController@BuyNowProccessApi']);
        Route::post('/checkout', 'PaymentsController@directPayment');
        // Route::post('checkout', ['uses' => 'CartController@buyNowcheckout']);
        Route::post('web_checkout', ['uses' => 'CartController@webCheckoutGenerator']);


    });
    Route::group(['prefix' => '/installments'], function () {
           Route::get('/', 'InstallmentsController@istallmentlist');
            Route::get('/plan', 'WebinarsController@getInstallmentsByCourse');
            Route::get('/{id}', 'InstallmentsController@index');
            Route::post('/store', 'InstallmentsController@store');
            Route::get('/order/overdue', 'InstallmentsController@installmentsOverdue');
            Route::get('/{id}/details', 'InstallmentsController@show');
            Route::get('/{id}/cancel', 'InstallmentsController@cancelVerification');
            Route::get('/{id}/pay_upcoming_part', 'InstallmentsController@payUpcomingPart');
            Route::get('/{id}/steps/{step_id}/pay', 'InstallmentsController@payStep');
            Route::post('/verifypartpayment', ['uses' => 'PaymentsController@payPartPaymentProccess']);

    });
    
    
    Route::group(['prefix' => 'financial'], function () {

        Route::get('sales', ['uses' => 'SalesController@index']);
        Route::post('/charge', ['uses' => 'PaymentsController@charge']);
        Route::post('/wallet-payment-Verify', ['uses' => 'PaymentsController@verifyPayment']);

        Route::post('/web_charge', ['uses' => 'PaymentsController@webChargeGenerator']);
        Route::get('/summary', ['uses' => 'AccountingsController@summary']);
        Route::get('/month-spend', ['uses' => 'AccountingsController@monthSpend']);
        Route::get('/fund-overview', ['uses' => 'AccountingsController@fundOverview']);
          Route::get('/fund-overview-month', ['uses' => 'AccountingsController@fundOverviewMonth']);
        Route::get('/platform-bank-accounts', ['uses' => 'AccountingsController@platformBankAccounts']);
        Route::get('/accounts-type', ['uses' => 'AccountingsController@accountTypes']);
        Route::get('/{id}/sale/{sale_id}/invoice', 'PaymentsController@invoice');
        //
        //   $siteBankAccounts = getOfflineBankSettings();
        Route::group(['prefix' => 'payout'], function () {
            Route::get('/', ['uses' => 'PayoutsController@index']);
            Route::post('/', ['uses' => 'PayoutsController@requestPayout']);

        });
        Route::group(['prefix' => 'offline-payments'], function () {
            Route::get('/', ['uses' => 'OfflinePayments@index']);
            Route::put('{id}', ['uses' => 'OfflinePayments@update']);
            Route::delete('{id}/', ['uses' => 'OfflinePayments@destroy']);
            Route::post('/', ['uses' => 'OfflinePayments@store']);

        });
        
    });
    Route::group(['prefix' => 'payments'], function () {
        Route::post('/request', 'PaymentsController@paymentRequest');
        Route::post('/credit', 'PaymentsController@paymentByCredit');
        Route::get('/verify/{gateway}', ['as' => 'payment_verify', 'uses' => 'PaymentsController@paymentVerify']);
        Route::post('/verify/{gateway}', ['as' => 'payment_verify_post', 'uses' => 'PaymentsController@paymentVerify']);
    });
    Route::group(['prefix' => 'profile-setting'], function () {
        Route::get('/', ['uses' => 'UsersController@setting']);
        Route::post('/store-meta', ['uses' => 'UsersController@storeMetas']);
        Route::post('/delete-meta', ['uses' => 'UsersController@deleteMeta']);
        Route::put('/password', ['uses' => 'UsersController@updatePassword', 'middleware' => 'api.request.type']);
        Route::put('/', ['uses' => 'UsersController@update']);
        Route::post('/images', ['uses' => 'UsersController@updateImages']);
    });

    Route::group(['prefix' => 'store'], function () {
        Route::group(['middleware' => ['api.level-access:teacher']], function () {
            Route::get('/products', ['uses' => 'ProductController@index']);
            Route::get('/products/comments', ['uses' => 'ProductController@myComments']);
            Route::get('/products/{id}', ['uses' => 'ProductController@show']);
            Route::get('/sales', ['uses' => 'ProductOrderController@index']);
            Route::get('/sales/customers', ['uses' => 'ProductOrderController@getBuyers']);

        });
        Route::get('/purchases', 'ProductOrderController@getPurchases');
        Route::get('/purchases/comments', 'ProductController@purchasedComment');

    });

    Route::group(['prefix' => 'my_assignments'], function () {
        Route::get('/', ['uses' => 'AssignmentController@index']);
        Route::get('/{assignment}', ['uses' => 'AssignmentController@show'])->name('assignment.show');

        //  Route::get('/my-courses-assignments', ['uses' => 'AssignmentController@myCoursesAssignments']);

    });
    Route::get('assignments/{assignment}/messages', ['uses' => 'AssignmentHistoryMessageController@index']);
    Route::post('assignments/{assignment}/messages', ['uses' => 'AssignmentHistoryMessageController@store']);


    /***** blogs *****/
    Route::apiResource('blogs/comments', BlogCommentController::class)->middleware('api.level-access:teacher');
    Route::apiResource('blogs', BlogController::class)->middleware('api.level-access:teacher');


    /***** delete account request *****/
    Route::post('/delete-account', 'DeleteAccountRequestController@store');

    /***** webinar certificate  *****/
    Route::get('webinars/certificates', ['uses' => 'WebinarCertificateController@index']);
    Route::get('webinars/certificates/{id}', ['uses' => 'WebinarCertificateController@show'])->name('webinar.certificate');
    Route::get('webinars/{id}/statistic', ['uses' => 'WebinarStatisticController@index'])->middleware('api.level-access:teacher');

    /***** Bundles  *****/
    Route::group(['prefix' => 'bundles'], function () {

        Route::post('{id}/buyWithPoint', ['uses' => 'BundleController@buyWithPoint']);
        Route::post('{id}/free', ['uses' => 'BundleController@free']);

    });
    /***** Reviews  *****/
    Route::group(['prefix' => '/reviews3'], function () {
        Route::get('/', ['uses' => 'WebinarReviewController@list']);
        Route::post('/', ['uses' => 'ReviewController@store', 'middleware' => 'api.request.type']);
        Route::post('/{id}/reply', ['uses' => 'ReviewController@reply', 'middleware' => 'api.request.type']);
        Route::delete('/{id}', ['uses' => 'ReviewController@destroy']);
    });

    //  Route::apiResource('webinars/{id}/forums', WebinarForumController::class);

    Route::group(['prefix' => 'webinars'], function () {

        Route::get('/{webinar}/noticeboards', ['uses' => 'CourseNoticeboardController@index']);

        Route::get('/{webinar}/', ['uses' => 'WebinarsController@show']);
        Route::get('/{webinar}/chapters/', ['uses' => 'WebinarChapterController@index']);
        Route::get('/{webinar}/chapters/{chapter}', ['uses' => 'WebinarChapterController@show']);

        Route::get('/{webinar}/forums/', ['uses' => 'CourseForumController@index']);
        Route::post('/{webinar}/forums', ['uses' => 'CourseForumController@store']);
        Route::put('/forums/{forum}', ['uses' => 'CourseForumController@update']);
        Route::post('/forums/{forum}/pin', ['uses' => 'CourseForumController@pin']);

        Route::group(['prefix' => 'forums'], function () {
             Route::post('/{forum}/answers', ['uses' => 'CourseForumAnswerController@store']);
            Route::get('/{forum}/answers', ['uses' => 'CourseForumAnswerController@index']);
            Route::post('/{forum}/answers', ['uses' => 'CourseForumAnswerController@store']);
            Route::put('/answers/{answer}', ['uses' => 'CourseForumAnswerController@update']);
            Route::post('/answers/{answer}/pin', ['uses' => 'CourseForumAnswerController@pin']);
            Route::post('/answers/{answer}/resolve', ['uses' => 'CourseForumAnswerController@resolve']);
        });
    });
     Route::group(['prefix' => 'forums'], function () {
        Route::post('/new-topic', 'ForumsController@storeTopic');
        Route::get('/topics', 'ForumsController@topics');
        Route::get('/topics/{id}/removeBookmarks', 'ForumsController@removeBookmarks');
        Route::get('/posts', 'ForumsController@posts');
        Route::get('/bookmarks', 'ForumsController@bookmarks');
        Route::get('/topics/{slug}', 'ForumsController@topicsdetailslist');
        
        
        Route::get('/', 'ForumsController@index');
        Route::get('/create-topic', 'ForumController@createTopic');
        Route::post('/create-topic', 'ForumController@storeTopic');
        Route::get('/search', 'ForumController@search');
        Route::group(['prefix' => '/topics'], function () {
            Route::post('/details', 'ForumsController@posts');
            Route::post('/likeToggle', 'ForumsController@topicLikeToggle');
            Route::get('/edit', 'ForumsController@topicEdit');
            Route::post('/edit', 'ForumsController@topicUpdate');
            Route::post('/bookmark', 'ForumsController@topicBookmarkToggle');
            Route::get('/downloadAttachment/{attachment_id}', 'ForumsController@topicDownloadAttachment');

            // Route::group(['prefix' => '/{topic_slug}/posts'], function () {
            //     Route::get('/', 'ForumsController@posts');
                Route::post('/posts', 'ForumsController@storePost');
            //     Route::post('/report', 'ForumsController@storeTopicReport');
            //     Route::get('/{post_id}/edit', 'ForumsController@postEdit');
            //     Route::post('/{post_id}/edit', 'ForumsController@postUpdate');
            //     Route::post('/{post_id}/likeToggle', 'ForumsController@postLikeToggle');
            //     Route::post('/{post_id}/un_pin', 'ForumsController@postUnPin');
            //     Route::post('/{post_id}/pin', 'ForumsController@postPin');
            //     Route::get('/{post_id}/downloadAttachment', 'ForumsController@postDownloadAttachment');
            // });
        });
    });
    
   Route::group(['prefix' => 'remedy'], function () {
        Route::get('/', 'RemedyController@remedies');
        Route::get('/{slug}', 'RemedyController@remedy');
        
   });
   Route::get('/testimonials', 'TestimonialController@index');
    
    Route::get('/files/{file}', ['uses' => 'FileController@show'])->name('file.show');
    Route::get('/sessions/{session}', ['uses' => 'SessionController@show'])->name('session.show');;
    Route::get('/text-lessons/{lesson}', ['uses' => 'TextLessonController@show'])->name('text_lesson.show');
    Route::get('/text-lessons/{lesson}/navigation', ['uses' => 'WebinarTextLessonController@index']);
    Route::get('/assignments/{assignment}', ['uses' => 'WebinarAssignmentController@show'])->name('assignment.show');
    Route::get('/quizzes/{quiz}', ['uses' => 'QuizzesController@show'])->name('quiz.show');
Route::get('/get-user/{meetingid}', ['uses' => 'SessionController@getParticipants']);
});
