<?php

use Illuminate\Support\Facades\Route;


    Route::get('/app-update-check', [App\Http\Controllers\Api\Web\AppUpdateController::class, 'check']);

// Route::group([], function () {
Route::group(['middleware' => ['api.auth']], function () {
    Route::group(['prefix' => 'courses'], function () {

        Route::get('/', ['uses' => 'WebinarController@index']);
        Route::get('/{id}', ['uses' => 'WebinarController@show']);
        Route::get('/{id}/content', ['uses' => 'WebinarController@content']);
        Route::get('/{id}/quizzes', ['uses' => 'WebinarContentController@quizzes']);
        Route::get('/{id}/certificates', ['uses' => 'WebinarContentController@certificates']);
        

        Route::get('reports/reasons', ['uses' => 'ReportsController@index']);


        Route::post('/{id}/report', ['uses' => 'WebinarController@report', 'middleware' => 'api.auth']);

        Route::post('/{webinar_id}/toggle', ['uses' => 'WebinarController@learningStatus', 'middleware' => 'api.auth']);


    });

    Route::get('certificate_validation', ['uses' => 'CertificatesController@checkValidate', 'middleware' => 'api.request.type']);

    Route::get('featured-courses', ['uses' => 'FeatureWebinarController@index']);
    Route::get('categories', ['uses' => 'CategoriesController@index']);
    Route::get('categories/{id}/webinars', ['uses' => 'CategoriesController@categoryWebinar']);
    Route::get('trend-categories', ['uses' => 'CategoriesController@trendCategory']);
    Route::get('search', ['uses' => 'SearchController@list']);
    Route::get('searches', ['uses' => 'SearchController@list1']);
    
    
    Route::get('yt-videos', ['uses' => 'YtVideoController@index']);
    Route::get('latest-videos', ['uses' => 'YtVideoController@latestVideos']);

    /******  Users ******/
    Route::group(['prefix' => 'providers'], function () {
        Route::get('instructors', ['uses' => 'UserController@instructors']);
        Route::get('organizations', ['uses' => 'UserController@organizations']);
        Route::get('astrologers', ['uses' => 'UserController@consultations']);

    });

    /******  Meetings ******/
    // Route::post('meetings/reserve', ['uses' => 'MeetingsController@reserve', 'middleware' => ['api.auth', 'api.request.type']]);
    Route::get('meetings/reserve', ['uses' => 'UserController@reserve']);
    Route::get('meetings/reserve15', ['uses' => 'UserController@reserve15']);
    Route::get('users/{id}/meetings', ['uses' => 'UserController@availableTimes']);
    Route::post('meetings/consultationpayment', ['uses' => 'UserController@consultationpayment']);
    Route::get('users/{id}/availableTimes', 'UserController@availableTimes1');
    // Route::get('users/{id}/ReservedSlot', ['uses' => 'UserController@ReservedSlot']);
    // Route::get('users/{id}/ReservedSlot', ['uses' => 'UserController@reserve']);
    // Route::get('users/{id}/ReservedSlot15', ['uses' => 'UserController@reserve15']);


    Route::get('users/{id}/profile', ['uses' => 'UserController@profile']);
    Route::post('users/{id}/send-message', 'UserController@sendMessage');


    Route::get('/files/{file_id}/download', ['uses' => 'FilesController@download']);

    Route::group(['prefix' => 'blogs'], function () {
        Route::get('/', ['uses' => 'BlogController@index']);
        Route::get('/categories', ['uses' => 'BlogCategoryController@index']);
        Route::get('/{id}', ['uses' => 'BlogController@show']);

    });

    Route::get('advertising-banner', ['uses' => 'AdvertisingBannerController@list']);

    Route::get('/subscribe', ['uses' => 'SubscribesController@list']);

    Route::get('instructors', ['uses' => 'UserController@instructors']);
    Route::get('profile/{id}', ['uses' => 'UserController@profile']);
    Route::get('mayank', ['uses' => 'UserController@mayank']);
    Route::get('organizations', ['uses' => 'UserController@organizations']);

    Route::post('newsletter', ['uses' => 'UserController@makeNewsletter', 'middleware' => 'format']);
    Route::post('contact', ['uses' => 'ContactController@store', 'middleware' => 'format']);

    Route::group(['prefix' => 'regions'], function () {
        Route::get('/countries/', ['uses' => 'RegionsController@countries']);
        Route::get('/provinces/{id?}', ['uses' => 'RegionsController@provinces']);
        Route::get('/cities/{id?}', ['uses' => 'RegionsController@cities']);
        Route::get('/districts/{id?}', ['uses' => 'RegionsController@districts']);


    });
    Route::get('timezones', ['uses' => 'TimeZonesController@index']);

    /******  Bundles ******/
    Route::group(['prefix' => 'bundles'], function () {
        Route::get('/', ['uses' => 'BundleController@index']);
        Route::get('/{id}/webinars', ['uses' => 'BundleWebinarController@index']);
        Route::post('/{id}/free', ['uses' => 'BundleWebinarController@free']);
        Route::get('/{id}', ['uses' => 'BundleController@show']);
    });

    /******  Products ******/
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', ['uses' => 'ProductController@index']);
        Route::get('/{id}', ['uses' => 'ProductController@show']);

    });
    
     Route::group(['prefix' => 'waitlists'], function () {
        Route::post('/join', 'WaitlistController@store');
    });
    Route::get('/product_categories', ['uses' => 'ProductCategoryController@index']);
     

});
   Route::post('payment/auctus/access', 'OfflineOctusPaymentController@fullAccessByoctus');








