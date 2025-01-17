<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\Api\Coupon\CouponController;
use App\Http\Controllers\Api\Auth\User\AuthUserController;
use App\Http\Controllers\Api\Auth\User\VerificationController;
use App\Http\Controllers\Api\User\Package\UserPackageController;
use App\Http\Controllers\Api\Auth\User\UserPasswordResetController;
use App\Http\Controllers\Api\User\Schedules\UserSchedulesController;
use App\Http\Controllers\Api\Admin\Transitions\AdminPaymentController;
use App\Http\Controllers\Api\User\UserManagement\UserProfileController;
use App\Http\Controllers\Api\User\Package\UserPurchasedHistoryController;
use App\Http\Controllers\Api\Admin\Package\CustomPackageRequestController;
use App\Http\Controllers\Api\User\SupportTicket\SupportTicketApiController;
use App\Http\Controllers\Api\User\SocialMedia\UserSocialMediaLinkController;
use App\Http\Controllers\Api\Admin\SupportTicket\AdminSupportTicketApiController;

// Authentication routes for users
Route::prefix('auth/user')->group(function () {
    Route::post('login', [AuthUserController::class, 'login'])->name('login');
    Route::post('register', [AuthUserController::class, 'register']);

    Route::middleware(AuthenticateUser::class)->group(function () {
        Route::post('logout', [AuthUserController::class, 'logout']);
        Route::get('me', [AuthUserController::class, 'me']);
        Route::post('change-password', [AuthUserController::class, 'changePassword']);
        Route::get('check-token', [AuthUserController::class, 'checkToken']);
    });
});

// User-specific routes
Route::prefix('user')->middleware(AuthenticateUser::class)->group(function () {
    // Profile routes
    Route::get('/profile', [UserProfileController::class, 'getProfile']);
    Route::post('/profile', [UserProfileController::class, 'updateProfile']);

    // Package routes
    Route::post('package/subscribe', [UserPackageController::class, 'packagePurchase']);

    Route::post('/custom/package/request', [CustomPackageRequestController::class, 'store']);


    // Get active packages
    Route::get('/active/Service', [UserPurchasedHistoryController::class, 'activePackages']);
    // Get package history
    Route::get('/service/history', [UserPurchasedHistoryController::class, 'packageHistory']);



    Route::get('/packages/history', [UserPurchasedHistoryController::class, 'getPurchasedHistory']);
    Route::get('/packages/history/{id}', [UserPurchasedHistoryController::class, 'getSinglePurchasedHistory']);

    // Support ticket routes
    Route::get('/support', [SupportTicketApiController::class, 'index']);
    Route::post('/support', [SupportTicketApiController::class, 'store']);
    Route::get('/support/{ticket}', [SupportTicketApiController::class, 'show']);
    Route::post('/support/{ticket}/reply', [AdminSupportTicketApiController::class, 'reply']);

    // Schedule routes
    Route::post('/schedule', [UserSchedulesController::class, 'create']);
    Route::get('/schedules', [UserSchedulesController::class, 'index']);
    Route::get('/schedule/{id}', [UserSchedulesController::class, 'show']);


    Route::prefix('billings')->group(function () {
        Route::get('/billing-history', [AdminPaymentController::class, 'getAllTransactionHistory'])
            ->name('user.transitions.transaction-history');

        Route::get('/billing-single/{id}', [AdminPaymentController::class, 'getTransactionById']);
    });







});

// Social media routes
Route::prefix('social-media')->group(function () {
    Route::get('links', [UserSocialMediaLinkController::class, 'index'])->name('socialMediaLinks.index');
    Route::get('links/{id}', [UserSocialMediaLinkController::class, 'show'])->name('socialMediaLinks.show');
});

// Coupon routes
Route::prefix('coupons')->group(function () {
    Route::post('/apply', [CouponController::class, 'apply']);
    Route::post('/check', [CouponController::class, 'checkCoupon']);
});

// Password reset routes
Route::post('user/password/email', [UserPasswordResetController::class, 'sendResetLinkEmail']);
Route::post('user/password/reset', [UserPasswordResetController::class, 'reset']);

// Verification routes
Route::post('/verify-otp', [VerificationController::class, 'verifyOtp']);
Route::post('/resend/otp', [VerificationController::class, 'resendOtp']);
Route::get('/email/verify/{hash}', [VerificationController::class, 'verifyEmail']);
Route::post('/resend/verification-link', [VerificationController::class, 'resendVerificationLink']);
