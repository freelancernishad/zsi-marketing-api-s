<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Global\JobApplyController;
use App\Http\Controllers\Api\Server\ServerStatusController;
use App\Http\Controllers\Api\User\Package\UserPackageController;
use App\Http\Controllers\Api\User\PackageAddon\UserPackageAddonController;

// Load additional route files
if (file_exists($userRoutes = __DIR__ . '/example.php')) {
    require $userRoutes;
}

if (file_exists($userRoutes = __DIR__ . '/users.php')) {
    require $userRoutes;
}

if (file_exists($adminRoutes = __DIR__ . '/admins.php')) {
    require $adminRoutes;
}

if (file_exists($stripeRoutes = __DIR__ . '/Gateways/stripe.php')) {
    require $stripeRoutes;
}

// Server status route
Route::get('/server-status', [ServerStatusController::class, 'checkStatus']);

// Global package routes
Route::get('global/packages', [UserPackageController::class, 'index']); // Get all packages with discounts
Route::get('global/package/{id}', [UserPackageController::class, 'show']); // Get a single package by ID with discounts

// Global package addon routes
Route::prefix('global')->group(function () {
    Route::get('package-addons', [UserPackageAddonController::class, 'index']); // List all addons
    Route::get('package-addons/{id}', [UserPackageAddonController::class, 'show']); // Get a specific addon
});



Route::post('/global/job-apply', [JobApplyController::class, 'store']);
Route::get('/global/job-apply/{application_id}', [JobApplyController::class, 'searchByApplicationId']);
