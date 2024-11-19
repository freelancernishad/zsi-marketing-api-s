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


if (!function_exists('applyDiscount')) {
    /**
     * Apply discount based on price, duration, and available discounts.
     *
     * @param float $price
     * @param int $durationMonths
     * @param array $discounts
     * @return array
     */
    function applyDiscount(float $price, int $durationMonths, array $discounts): array
    {
        // Default to no discount
        $discountRate = 0;
        $discountedPrice = $price * $durationMonths;

        // Loop through the available discounts and apply the one matching the duration
        foreach ($discounts as $discount) {
            if ($discount['duration_months'] == $durationMonths) {
                $discountRate = $discount['discount_rate'];
                break;
            }
        }

        // Calculate the discounted price
        $discountedPrice = $discountedPrice - ($discountedPrice * ($discountRate / 100));

        // Return the discount rate and discounted price
        return [
            'discount_rate' => $discountRate,
            'discounted_price' => round($discountedPrice, 2)
        ];
    }
}
