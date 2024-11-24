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
            'name' => 'sometimes|string|max:255',
            'profile_picture' => 'sometimes|image|max:2048',


            'phone' => 'sometimes|string|max:15',
            'business_name' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'region' => 'sometimes|string|max:255',
            'zip_code' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update user's profile with validated data
        $user->update($request->only([
            'name',
            'phone',
            'business_name',
            'country',
            'state',
            'city',
            'region',
            'zip_code',
        ]));







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
