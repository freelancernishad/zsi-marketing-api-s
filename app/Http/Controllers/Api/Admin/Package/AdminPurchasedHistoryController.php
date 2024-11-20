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
        // Retrieve all purchased packages with related data
        $userPackages = UserPackage::with([
            'user',           // Load the user relationship
            'package',        // Load the package relationship
            'addons',         // Load all UserPackageAddon relationships
            'addons.addon',   // Load the addon related to the UserPackageAddon
        ])->get();

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
            'user',           // Load the user relationship
            'package',        // Load the package relationship
            'addons',         // Load all UserPackageAddon relationships
            'addons.addon',   // Load the addon related to the UserPackageAddon
        ])->find($id);

        // Check if the UserPackage exists
        if (!$userPackage) {
            return response()->json(['message' => 'Package history not found'], 404);
        }

        // Return the result as a JSON response
        return response()->json($userPackage);
    }
}
