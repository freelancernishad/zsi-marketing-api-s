<?php

namespace App\Http\Controllers\Api\User\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        $user = Auth::user(); // Retrieve the authenticated user

        // Load relationships with specific fields
        $user->load([
            'userPackagePackagesHistory.addons.addon' => function ($query) {
                $query->select('id', 'addon_name', 'price'); // Fetch only necessary fields
            },
            'userPackagePackagesHistory.package:id,name,price',
            'userPackagePackagesHistory.payments',
        ]);

        // Flatten addons to only include 'addon' object
        foreach ($user->userPackagePackagesHistory as $userPackage) {
            if ($userPackage->addons) {
                $userPackage->addons = $userPackage->addons->map(function ($addon) {
                    return $addon->addon; // Return the addon object directly
                });
            }

            // Hide unwanted fields in the 'package' relation if it exists
            if ($userPackage->package) {
                $userPackage->package->makeHidden(['discounts', 'discounted_price']);
            }
        }

        return response()->json($user);
    }




    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user(); // Retrieve the authenticated user

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'phone' => 'nullable|string|max:15',
            'business_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Filter out null or blank values
        $filteredData = collect($request->only([
            'name',
            'phone',
            'business_name',
            'country',
            'state',
            'city',
            'region',
            'zip_code',
        ]))->filter(function ($value) {
            return $value !== null && $value !== '';
        })->toArray();

        // Update user's profile with filtered data
        $user->update($filteredData);

        // Handle profile picture upload if provided
        if ($request->hasFile('profile_picture')) {
            try {
                $filePath = $user->saveProfilePicture($request->file('profile_picture'));
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to upload profile picture: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json($user);
    }


}
