<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Controllers\Api\AllowedOriginController;
use App\Http\Controllers\Api\Coupon\CouponController;
use App\Http\Controllers\Api\Admin\Users\UserController;
use App\Http\Controllers\Api\Auth\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\Package\AdminPackageController;
use App\Http\Controllers\Api\SystemSettings\SystemSettingController;
use App\Http\Controllers\Api\Auth\Admin\AdminResetPasswordController;
use App\Http\Controllers\Api\Admin\Transitions\AdminPaymentController;
use App\Http\Controllers\Api\Admin\Package\AdminPurchasedHistoryController;
use App\Http\Controllers\Api\Admin\PackageAddon\AdminPackageAddonController;
use App\Http\Controllers\Api\Admin\SocialMedia\AdminSocialMediaLinkController;
use App\Http\Controllers\Api\Admin\SupportTicket\AdminSupportTicketApiController;

Route::prefix('auth/admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('register', [AdminAuthController::class, 'register']);

    Route::middleware(AuthenticateAdmin::class)->group(function () { // Applying admin middleware
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
        Route::get('check-token', [AdminAuthController::class, 'checkToken']);

    });
});

Route::prefix('admin')->group(function () {
    Route::middleware(AuthenticateAdmin::class)->group(function () { // Applying admin middleware
        Route::post('/system-setting', [SystemSettingController::class, 'storeOrUpdate']);
        Route::get('/allowed-origins', [AllowedOriginController::class, 'index']);
        Route::post('/allowed-origins', [AllowedOriginController::class, 'store']);
        Route::put('/allowed-origins/{id}', [AllowedOriginController::class, 'update']);
        Route::delete('/allowed-origins/{id}', [AllowedOriginController::class, 'destroy']);



        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);          // List users
            Route::post('/', [UserController::class, 'store']);         // Create user
            Route::get('/{user}', [UserController::class, 'show']);     // Show user details
            Route::put('/{user}', [UserController::class, 'update']);   // Update user
            Route::delete('/{user}', [UserController::class, 'destroy']); // Delete user
        });

        Route::prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);
            Route::post('/', [CouponController::class, 'store']);
            Route::post('/{id}', [CouponController::class, 'update']);
            Route::delete('/{id}', [CouponController::class, 'destroy']);
        });

        Route::prefix('transitions')->group(function () {
            Route::get('/transaction-history', [AdminPaymentController::class, 'getAllTransactionHistory'])
                ->name('admin.transitions.transaction-history');
        });


        Route::prefix('social-media')->group(function () {
            Route::get('links', [AdminSocialMediaLinkController::class, 'index'])->name('admin.socialMediaLinks.index');
            Route::get('links/{id}', [AdminSocialMediaLinkController::class, 'show'])->name('admin.socialMediaLinks.show');
            Route::post('links', [AdminSocialMediaLinkController::class, 'store'])->name('admin.socialMediaLinks.store');
            Route::post('links/{id}', [AdminSocialMediaLinkController::class, 'update'])->name('admin.socialMediaLinks.update');
            Route::delete('links/{id}', [AdminSocialMediaLinkController::class, 'destroy'])->name('admin.socialMediaLinks.destroy');
            Route::patch('links/{id}/toggle-status', [AdminSocialMediaLinkController::class, 'toggleStatus']);
            Route::patch('links/{id}/update-index-no', [AdminSocialMediaLinkController::class, 'updateIndexNo']);
        });

        Route::prefix('/')->group(function () {
            Route::get('packages', [AdminPackageController::class, 'index']);
            Route::get('packages/{id}', [AdminPackageController::class, 'show']);
            Route::post('packages', [AdminPackageController::class, 'store']);
            Route::put('packages/{id}', [AdminPackageController::class, 'update']);
            Route::delete('packages/{id}', [AdminPackageController::class, 'destroy']);
        });


        Route::prefix('/')->group(function () {
            Route::get('package-addons/', [AdminPackageAddonController::class, 'index']); // List all addons
            Route::post('package-addons/', [AdminPackageAddonController::class, 'store']); // Create a new addon
            Route::get('package-addons/{id}', [AdminPackageAddonController::class, 'show']); // Get a specific addon
            Route::put('package-addons/{id}', [AdminPackageAddonController::class, 'update']); // Update an addon
            Route::delete('package-addons/{id}', [AdminPackageAddonController::class, 'destroy']); // Delete an addon
        });


        // Support ticket routes
        Route::get('/support', [AdminSupportTicketApiController::class, 'index']);
        Route::get('/support/{ticket}', [AdminSupportTicketApiController::class, 'show']);
        Route::post('/support/{ticket}/reply', [AdminSupportTicketApiController::class, 'reply']);
        Route::patch('/support/{ticket}/status', [AdminSupportTicketApiController::class, 'updateStatus']);




        Route::get('/package/purchased-history', [AdminPurchasedHistoryController::class, 'getAllHistory']);
        Route::get('/package/purchased-history/{id}', [AdminPurchasedHistoryController::class, 'getSingleHistory']);


    });
});



