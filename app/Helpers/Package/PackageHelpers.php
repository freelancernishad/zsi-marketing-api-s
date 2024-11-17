<?php

use App\Models\Package;
use App\Models\UserPackage;
use Illuminate\Support\Facades\Auth;

function PackageSubscribe($package_id)
{
    $package = Package::find($package_id);

    if (!$package) {
        return response()->json(['message' => 'Package not found'], 404);
    }

    // Example logic to assign the package to the user
    $userPackage = new UserPackage();
    $userPackage->user_id = Auth::id();
    $userPackage->package_id = $package->id;
    $userPackage->started_at = now();
    $userPackage->ends_at = now()->addDays($package->duration_days);
    $userPackage->save();

    return response()->json(['message' => 'Successfully subscribed to the package']);
}
