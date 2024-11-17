<?php


use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\Api\Coupon\CouponController;
use App\Http\Controllers\Api\Auth\User\AuthUserController;
use App\Http\Controllers\Api\User\Package\UserPackageController;
use App\Http\Controllers\Api\User\SocialMedia\UserSocialMediaLinkController;



Route::prefix('auth/user')->group(function () {
    Route::post('login', [AuthUserController::class, 'login'])->name('login');
    Route::post('register', [AuthUserController::class, 'register']);

    Route::middleware(AuthenticateUser::class)->group(function () { // Applying user middleware
        Route::post('logout', [AuthUserController::class, 'logout']);
        Route::get('me', [AuthUserController::class, 'me']);
        Route::post('change-password', [AuthUserController::class, 'changePassword']);
    });
});

Route::prefix('user')->group(function () {
    Route::middleware(AuthenticateUser::class)->group(function () {

////// auth routes


        Route::post('package/subscribe', [UserPackageController::class, 'subscribe']);

    });

});


Route::prefix('social-media')->group(function () {
    // Get all social media links
    Route::get('links', [UserSocialMediaLinkController::class, 'index'])->name('socialMediaLinks.index');

    // Get a specific social media link
    Route::get('links/{id}', [UserSocialMediaLinkController::class, 'show'])->name('socialMediaLinks.show');
});

Route::prefix('coupons')->group(function () {
    Route::post('/apply', [CouponController::class, 'apply']);
});
