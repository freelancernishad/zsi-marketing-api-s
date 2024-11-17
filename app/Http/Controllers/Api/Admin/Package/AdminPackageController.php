<?php

namespace App\Http\Controllers\Api\Admin\Package;

use App\Models\Package;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPackageController extends Controller
{
    /**
     * Show a list of all packages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch all packages
        $packages = Package::all();

        return response()->json($packages);
    }

    /**
     * Show a single package's details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        return response()->json($package);
    }

    /**
     * Create a new package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules =  [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array',
        ];
        $validationResponse = validateRequest($request->all(), $rules);
        if ($validationResponse) {
            return $validationResponse; // Return if validation fails
        }

        $data = [
            "name"=>$request->name,
            "description"=>$request->description,
            "price"=>$request->price,
            "duration_days"=>$request->duration_days,
            "features"=>$request->features,
        ];
        // Create and store the package
        $package = Package::create($data);

        return response()->json($package, 201);
    }

    /**
     * Update an existing package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        // Define the validation rules
        $rules =  [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
        ];

        // Validate the incoming request
        $validationResponse = validateRequest($request->all(), $rules);
        if ($validationResponse) {
            return $validationResponse; // Return if validation fails
        }

        // Collect only the data that is passed in the request
        $data = $request->only(['name', 'description', 'price', 'duration_days', 'features']);

        // Remove null values to avoid overwriting them with null
        $data = array_filter($data, function ($value) {
            return !is_null($value);
        });

        // If no data is provided, return a validation error
        if (empty($data)) {
            return response()->json(['message' => 'No valid fields to update'], 400);
        }

        // Update the package details with only the fields provided in the request
        $package->update($data);

        return response()->json($package);
    }


    /**
     * Delete a package.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        // Delete the package
        $package->delete();

        return response()->json(['message' => 'Package deleted successfully']);
    }
}

