<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Auth', 'middleware' => ['api.request.type']], function () {

    Route::post('/register/step/{step}', ['as' => 'register', 'uses' => 'RegisterController@stepRegister']);
    Route::post('/signup', ['as' => 'signup', 'uses' => 'RegisterController@Register']);
    Route::post('/login', ['as' => 'login', 'uses' => 'LoginController@requestOtp']);
    Route::post('/send-otp', ['as' => 'send-otp', 'uses' => 'OtpController@sendOtp']);
    Route::post('/verify-otp', ['as' => 'verify-otp', 'uses' => 'OtpController@verifyOtp']);
    Route::get('/sendotp', ['as' => 'sendotp', 'uses' => 'LoginController@sendOtp']);
    Route::post('/resend-otp', ['as' => 'resend-otp', 'uses' => 'LoginController@resendOtp']);

Route::post('/getData', ['as' => 'getData', 'uses' => 'LoginController@getData']);
Route::post('/setData', ['as' => 'setData', 'uses' => 'LoginController@setData']);

    Route::post('/forget-password', ['as' => 'forgot', 'uses' => 'ForgotPasswordController@sendEmail']);
    Route::post('/reset-password', ['as' => 'updatePassword', 'uses' => 'ResetPasswordController@updatePassword']);
    Route::post('/verification', ['as' => 'verification', 'uses' => 'VerificationController@confirmCode']);
    Route::get('/google', ['as' => 'google', 'uses' => 'SocialiteController@redirectToGoogle']);
    Route::get('/facebook', ['as' => 'google', 'uses' => 'SocialiteController@redirectToFacebook']);
    Route::post('/google/callback', ['as' => 'google_callback', 'uses' => 'SocialiteController@handleGoogleCallback']);
    Route::post('/facebook/callback', ['as' => 'facebook_callback', 'uses' => 'SocialiteController@handleFacebookCallback']);

   // Route::get('/reff/{code}', 'ReferralController@referral');

});

Route::post('/logout', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout', 'middleware' => ['api.auth']]);


