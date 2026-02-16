<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentEngine\PurchaseController;
use App\Http\Controllers\PaymentEngine\RefundController;
use App\Http\Controllers\PaymentEngine\InstallmentController;
use App\Http\Controllers\PaymentEngine\SubscriptionController;
use App\Http\Controllers\PaymentEngine\AdjustmentController;
use App\Http\Controllers\PaymentEngine\DiscountController;
use App\Http\Controllers\PaymentEngine\ReferralController;
use App\Http\Controllers\PaymentEngine\AdminPaymentController;

/*
|--------------------------------------------------------------------------
| Unified Payment Engine (UPE) API Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/development/upe
| Middleware: api.auth (all routes require authentication)
|
*/

// ── Products (public browsing) ──
Route::get('/products', [PurchaseController::class, 'products']);
Route::get('/products/{id}', [PurchaseController::class, 'showProduct']);

// ── Authenticated routes ──
Route::group(['middleware' => ['api.auth']], function () {

    // ── Purchase ──
    Route::group(['prefix' => 'purchase'], function () {
        Route::post('/calculate', [PurchaseController::class, 'calculatePrice']);
        Route::post('/create', [PurchaseController::class, 'createSale']);
        Route::post('/pay', [PurchaseController::class, 'processPayment']);
        Route::get('/my-sales', [PurchaseController::class, 'mySales']);
        Route::get('/sale/{id}', [PurchaseController::class, 'showSale']);
    });

    // ── Access ──
    Route::get('/access/check', [PurchaseController::class, 'checkAccess']);

    // ── Discount ──
    Route::group(['prefix' => 'discount'], function () {
        Route::post('/validate', [DiscountController::class, 'validateCoupon']);
        Route::get('/list', [DiscountController::class, 'list']);
        Route::post('/create', [DiscountController::class, 'create']);
        Route::put('/{id}/disable', [DiscountController::class, 'disable']);
    });

    // ── Refund ──
    Route::group(['prefix' => 'refund'], function () {
        Route::post('/estimate', [RefundController::class, 'estimate']);
        Route::post('/request', [RefundController::class, 'createRequest']);
        Route::post('/execute', [RefundController::class, 'execute']);
    });

    // ── Installment ──
    Route::group(['prefix' => 'installment'], function () {
        Route::post('/create-plan', [InstallmentController::class, 'createPlan']);
        Route::get('/plan/{id}', [InstallmentController::class, 'showPlan']);
        Route::post('/pay', [InstallmentController::class, 'pay']);
        Route::post('/restructure-request', [InstallmentController::class, 'restructureRequest']);
        Route::post('/restructure-execute', [InstallmentController::class, 'restructureExecute']);
    });

    // ── Subscription ──
    Route::group(['prefix' => 'subscription'], function () {
        Route::post('/create', [SubscriptionController::class, 'create']);
        Route::get('/my', [SubscriptionController::class, 'mySubscriptions']);
        Route::get('/{id}', [SubscriptionController::class, 'show']);
        Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('/{id}/revoke', [SubscriptionController::class, 'revoke']);
    });

    // ── Adjustment (Upgrade / Cross-course / Wrong-course) ──
    Route::group(['prefix' => 'adjustment'], function () {
        Route::post('/estimate', [AdjustmentController::class, 'estimate']);
        Route::post('/request', [AdjustmentController::class, 'createRequest']);
        Route::post('/execute', [AdjustmentController::class, 'execute']);
    });

    // ── Referral ──
    Route::group(['prefix' => 'referral'], function () {
        Route::post('/generate', [ReferralController::class, 'generate']);
        Route::post('/track', [ReferralController::class, 'track']);
        Route::get('/my', [ReferralController::class, 'myReferrals']);
    });

    // ── Support Actions ──
    Route::group(['prefix' => 'support'], function () {
        // Read-only routes (any authenticated user)
        Route::get('/visibility', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'visibility']);
        Route::get('/user-matrix', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'userMatrix']);
        Route::post('/check-eligibility', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'checkEligibility']);

        // Admin-only routes (create, approve, reject, execute, mentor management)
        Route::group(['middleware' => ['admin']], function () {
            Route::post('/create', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'create']);
            Route::get('/actions', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'index']);
            Route::get('/actions/{id}', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'show']);
            Route::post('/actions/{id}/approve', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'approve']);
            Route::post('/actions/{id}/reject', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'reject']);
            Route::post('/actions/{id}/execute', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'execute']);

            // Mentor badge management
            Route::post('/mentor/grant', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'grantMentorBadge']);
            Route::post('/mentor/revoke', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'revokeMentorBadge']);
            Route::get('/mentor/list', [\App\Http\Controllers\PaymentEngine\SupportActionController::class, 'listMentors']);
        });
    });

    // ── Admin Panel ──
    Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
        Route::get('/requests', [AdminPaymentController::class, 'listRequests']);
        Route::get('/requests/{id}', [AdminPaymentController::class, 'showRequest']);
        Route::post('/requests/{id}/verify', [AdminPaymentController::class, 'verifyRequest']);
        Route::post('/requests/{id}/approve', [AdminPaymentController::class, 'approveRequest']);
        Route::post('/requests/{id}/reject', [AdminPaymentController::class, 'rejectRequest']);
        Route::post('/grant-free', [AdminPaymentController::class, 'grantFreeAccess']);
        Route::get('/sales', [AdminPaymentController::class, 'listSales']);
        Route::get('/sale/{id}/ledger', [AdminPaymentController::class, 'saleLedger']);
        Route::get('/user/{userId}/access', [AdminPaymentController::class, 'userAccess']);
        Route::get('/audit', [AdminPaymentController::class, 'audit']);
        Route::post('/offline-payment', [AdminPaymentController::class, 'recordOfflinePayment']);
    });
});
