<?php

namespace App\Http\Controllers\Api\Admin\Package;

use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use Illuminate\Http\Request;

class AdminPurchasedHistoryController extends Controller
{
    /**
     * Get all purchased package history with related data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllHistory(Request $request)
    {
        // Retrieve all purchased packages with specific columns for user and package relationships
        $userPackages = UserPackage::with([
            'user:id,name',          // Load only 'id' and 'name' of the user
            'package:id,name,price', // Load only 'id', 'name', and 'price' of the package
        ])->get();

        // Hide 'discounts' and 'discounted_price' from the package relationship
        $userPackages->each(function ($userPackage) {
            $userPackage->package->makeHidden(['discounts', 'discounted_price']);
        });

        // Return the result as a JSON response
        return response()->json($userPackages);
    }


    /**
     * Get a single purchased package history by user_package_id with related data.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingleHistory($id)
    {
        // Retrieve a single purchased package with related data
        $userPackage = UserPackage::with([
            'user',                      // Load the user relationship
            'package:id,name,price',     // Load the package relationship with specific fields
            'addons' => function ($query) {  // Limit the fields loaded for the addons
                $query->select('id', 'user_id', 'package_id', 'addon_id', 'purchase_id'); // Select only 'id', 'addon_name', 'price' for the addon
            },
            'addons.addon' => function ($query) {  // Limit the fields loaded for the addon details
                $query->select('id', 'addon_name', 'price'); // Select only 'id', 'addon_name', 'price' for the addon details
            }
        ])->find($id);

        // Hide unnecessary fields from the package
        $userPackage->package->makeHidden(['discounts', 'discounted_price', 'features']);

        // Check if the UserPackage exists
        if (!$userPackage) {
            return response()->json(['message' => 'Package history not found'], 404);
        }

        // Return the result as a JSON response
        return response()->json($userPackage);
    }

}
