<?php

namespace App\Http\Controllers\Api\Admin\Users;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // List users with optional search
    public function index(Request $request)
    {
        $query = User::query();

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Set dynamic pagination
        $perPage = $request->input('per_page', 10); // Default to 10 if not specified
        $users = $query->paginate($perPage);

        return response()->json($users);
    }




    public function store(Request $request)
    {

        // Define validation rules
        $rules =  [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
        $validationResponse = validateRequest($request->all(), $rules);
        if ($validationResponse) {
            return $validationResponse; // Return if validation fails
        }



        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }


    public function show(User $user)
    {
        // Load the 'userBuyPackage' relationship and its nested relations
        $user->load([
            'userBuyPackage.addons.addon' => function ($query) {
                $query->select('id', 'addon_name', 'price'); // Select specific fields for the addon details
            },
            'userBuyPackage.package:id,name,price', // Load package data with selected fields
        ]);

        // Dynamically hide fields in the 'package' relation
        foreach ($user->userBuyPackage as $userPackage) {
            if ($userPackage->package) {
                $userPackage->package->makeHidden(['discounts', 'discounted_price']);
            }
        }

        return response()->json($user);
    }



    // Update a user
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
        ];

        $validationResponse = validateRequest($request->all(), $rules);
        if ($validationResponse) {
            return $validationResponse; // Return if validation fails
        }



        // Prepare the data array for update
        $data = [
            'name' => $request->name ?? $user->name, // Keep current value if not updating
            'email' => $request->email ?? $user->email,
            'password' => isset($request->password) ? Hash::make($request->password) : $user->password,
        ];

        // Update the user with the new data
        $user->update($data);

        return response()->json($user);
    }

    // Delete a user
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
