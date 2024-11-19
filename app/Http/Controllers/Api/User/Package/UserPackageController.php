<?php

namespace App\Http\Controllers\Api\User\Package;

use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserPackageController extends Controller
{
    /**
     * Get a list of packages with features and applicable discount based on duration (months).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get the list of all packages with features, discount rate, and discounted price
        $packages = Package::all()->makeHidden(['discounts']);

        // Return the list of packages with calculated discount details
        return response()->json($packages);
    }

    /**
     * Get a single package's details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the package by ID
        $package = Package::find($id)->makeHidden(['discounts']);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        // Return the package details with calculated discount rate and discounted price
        return response()->json($package);
    }
}
