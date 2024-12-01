<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Controllers\Api\AllowedOriginController;
use App\Http\Controllers\Api\Coupon\CouponController;
use App\Http\Controllers\Api\Admin\Users\UserController;
use App\Http\Controllers\Api\Auth\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\Careers\JobApplyController;
use App\Http\Controllers\Api\Admin\Package\AdminPackageController;
use App\Http\Controllers\Api\SystemSettings\SystemSettingController;
use App\Http\Controllers\Api\Admin\Careers\Jobs\CareersJobController;
use App\Http\Controllers\Api\Auth\Admin\AdminResetPasswordController;
use App\Http\Controllers\Api\Admin\Schedules\AdminSchedulesController;
use App\Http\Controllers\Api\Admin\Transitions\AdminPaymentController;
use App\Http\Controllers\Api\Admin\Package\AdminPurchasedHistoryController;
use App\Http\Controllers\Api\Admin\PackageAddon\AdminPackageAddonController;
use App\Http\Controllers\Api\Admin\DashboardMetrics\AdminDashboardController;
use App\Http\Controllers\Api\Admin\SocialMedia\AdminSocialMediaLinkController;
use App\Http\Controllers\Api\Admin\SupportTicket\AdminSupportTicketApiController;

// Admin Authentication Routes
Route::prefix('auth/admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('register', [AdminAuthController::class, 'register']);

    Route::middleware(AuthenticateAdmin::class)->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
        Route::get('check-token', [AdminAuthController::class, 'checkToken']);
    });
});

// Admin Routes
Route::prefix('admin')->middleware(AuthenticateAdmin::class)->group(function () {
    // System Settings
    Route::post('/system-setting', [SystemSettingController::class, 'storeOrUpdate']);

    // Allowed Origins
    Route::prefix('allowed-origins')->group(function () {
        Route::get('/', [AllowedOriginController::class, 'index']);
        Route::post('/', [AllowedOriginController::class, 'store']);
        Route::put('/{id}', [AllowedOriginController::class, 'update']);
        Route::delete('/{id}', [AllowedOriginController::class, 'destroy']);
    });

    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index']);

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    // Coupons
    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index']);
        Route::post('/', [CouponController::class, 'store']);
        Route::post('/{id}', [CouponController::class, 'update']);
        Route::delete('/{id}', [CouponController::class, 'destroy']);
    });

    // Transitions
    Route::prefix('transitions')->group(function () {
        Route::get('/transaction-history', [AdminPaymentController::class, 'getAllTransactionHistory'])
            ->name('admin.transitions.transaction-history');
    });

    // Social Media Links
    Route::prefix('social-media/links')->group(function () {
        Route::get('/', [AdminSocialMediaLinkController::class, 'index'])->name('admin.socialMediaLinks.index');
        Route::get('/{id}', [AdminSocialMediaLinkController::class, 'show'])->name('admin.socialMediaLinks.show');
        Route::post('/', [AdminSocialMediaLinkController::class, 'store'])->name('admin.socialMediaLinks.store');
        Route::post('/{id}', [AdminSocialMediaLinkController::class, 'update'])->name('admin.socialMediaLinks.update');
        Route::delete('/{id}', [AdminSocialMediaLinkController::class, 'destroy'])->name('admin.socialMediaLinks.destroy');
        Route::patch('/{id}/toggle-status', [AdminSocialMediaLinkController::class, 'toggleStatus']);
        Route::patch('/{id}/update-index-no', [AdminSocialMediaLinkController::class, 'updateIndexNo']);
    });

    // Packages
    Route::prefix('packages')->group(function () {
        Route::get('/', [AdminPackageController::class, 'index']);
        Route::get('/{id}', [AdminPackageController::class, 'show']);
        Route::post('/', [AdminPackageController::class, 'store']);
        Route::put('/{id}', [AdminPackageController::class, 'update']);
        Route::delete('/{id}', [AdminPackageController::class, 'destroy']);
    });

    // Package Addons
    Route::prefix('package-addons')->group(function () {
        Route::get('/', [AdminPackageAddonController::class, 'index']);
        Route::post('/', [AdminPackageAddonController::class, 'store']);
        Route::get('/{id}', [AdminPackageAddonController::class, 'show']);
        Route::put('/{id}', [AdminPackageAddonController::class, 'update']);
        Route::delete('/{id}', [AdminPackageAddonController::class, 'destroy']);
    });

    // Support Tickets
    Route::prefix('support')->group(function () {
        Route::get('/', [AdminSupportTicketApiController::class, 'index']);
        Route::get('/{ticket}', [AdminSupportTicketApiController::class, 'show']);
        Route::post('/{ticket}/reply', [AdminSupportTicketApiController::class, 'reply']);
        Route::patch('/{ticket}/status', [AdminSupportTicketApiController::class, 'updateStatus']);
    });

    // Purchased History
    Route::prefix('package/purchased-history')->group(function () {
        Route::get('/', [AdminPurchasedHistoryController::class, 'getAllHistory']);
        Route::get('/{id}', [AdminPurchasedHistoryController::class, 'getSingleHistory']);
    });

    // Schedules
    Route::prefix('schedules')->group(function () {
        Route::get('/', [AdminSchedulesController::class, 'index']);
        Route::get('/{id}', [AdminSchedulesController::class, 'show']);
    });


    Route::prefix('careers/jobs')->group(function () {
        Route::get('/', [CareersJobController::class, 'index']); // List all jobs
        Route::post('/', [CareersJobController::class, 'store']); // Create a new job
        Route::get('/{id}', [CareersJobController::class, 'show']); // Show a specific job
        Route::put('/{id}', [CareersJobController::class, 'update']); // Update a job
        Route::delete('/{id}', [CareersJobController::class, 'destroy']); // Delete a job
    });

    Route::prefix('careers')->group(function () {
        // JobApply routes
        Route::get('job-applies', [JobApplyController::class, 'index']); // List Job Applications with Pagination
        Route::post('job-applies/{id}/status', [JobApplyController::class, 'changeStatus']); // Change Job Application Status
    });



});

Route::prefix('careers')->group(function () {
    Route::get('job-applies/export', [JobApplyController::class, 'exportToExcel']);
});

