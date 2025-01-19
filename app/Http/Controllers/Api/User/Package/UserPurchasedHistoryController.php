<?php

namespace App\Http\Controllers\Api\User\Package;

use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPurchasedHistoryController extends Controller
{
    /**
     * Get the authenticated user's active packages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activePackages()
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Use the model function to get active packages
        $activePackages = UserPackage::getActivePackages($user->id);

        // Return the result as a JSON response
        return response()->json($activePackages);
    }

    /**
     * Get the authenticated user's package history.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function packageHistory(Request $request)
    {
        // Check if the authenticated user is an admin
        if (Auth::guard('admin')->check()) {
            // For admins, get the user_id from the request parameters
            $userId = $request->input('user_id');

            // Validate that user_id is provided
            if (!$userId) {
                return response()->json(['message' => 'User ID is required for admin access'], 400);
            }
        } else {
            // For regular users, get the authenticated user's ID
            $userId = Auth::id();
        }

        // Use the model function to get package history
        $packageHistory = UserPackage::getPackageHistory($userId);

        // Return the result as a JSON response
        return response()->json($packageHistory);
    }

    /**
     * Get the authenticated user's purchased package history with related data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchasedHistory(Request $request)
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Retrieve the search query
        $searchQuery = $request->input('search');

        // Query for the authenticated user's packages
        $query = UserPackage::with([
            'package:id,name,price', // Load only 'id', 'name', and 'price' of the package
        ])->where('user_id', $user->id);

        // Apply global search if search query is provided
        if ($searchQuery) {
            $query->whereHas('package', function ($packageQuery) use ($searchQuery) {
                $packageQuery->where('name', 'like', '%' . $searchQuery . '%');
            })->orWhere('id', 'like', '%' . $searchQuery . '%') // Search by UserPackage ID
              ->orWhere('started_at', 'like', '%' . $searchQuery . '%') // Search by started_at
              ->orWhere('ends_at', 'like', '%' . $searchQuery . '%');   // Search by ends_at
        }

        // Execute the query and get results
        $userPackages = $query->get();

        // Hide 'discounts' and 'discounted_price' from the package relationship
        $userPackages->each(function ($userPackage) {
            $userPackage->package->makeHidden(['discounts', 'discounted_price']);
        });

        // Return the result as a JSON response
        return response()->json($userPackages);
    }

    /**
     * Get details of a single purchased package for the authenticated user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSinglePurchasedHistory($id)
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Retrieve the UserPackage with related data
        $userPackage = UserPackage::with([
            'package:id,name,price,features',     // Load the package relationship with specific fields
            'addons' => function ($query) {  // Limit the fields loaded for the addons
                $query->select('id', 'user_id', 'package_id', 'addon_id', 'purchase_id');
            },
            'addons.addon' => function ($query) {  // Limit the fields loaded for the addon details
                $query->select('id', 'addon_name', 'price');
            }
        ])->where('user_id', $user->id)->find($id);

        // Check if the UserPackage exists
        if (!$userPackage) {
            return response()->json(['message' => 'Package history not found'], 404);
        }

        // Hide unnecessary fields from the package
        $userPackage->package->makeHidden(['discounts', 'discounted_price']);

        $userPackage['pdf'] = url("package/invoice/$id");

        // Return the result as a JSON response
        return response()->json($userPackage);
    }
}
