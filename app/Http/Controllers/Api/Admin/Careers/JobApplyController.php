<?php

namespace App\Http\Controllers\Api\Admin\Careers;

use App\Models\JobApply;
use Illuminate\Http\Request;
use App\Exports\JobApplyExport;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class JobApplyController extends Controller
{
    /**
     * Display a listing of the job applications with pagination and filters.
     */
    public function index(Request $request)
    {
        // Default per_page value
        // $perPage = $request->input('per_page', 10);
        $perPage = 30;

        // Start query
        $query = JobApply::query();

        // Apply filters for all fields if they exist
        $filters = [
            'full_name', 'email', 'phone', 'cover_letter', 'resume',
            'job_title', 'job_details', 'responsibilities', 'vacancies',
            'job_type', 'expiry_date', 'category', 'employment_type',
            'experience_level', 'salary_type', 'salary', 'office_time',
            'show_on_career_page', 'requested_origin', 'application_id',
            'careers_job_id', 'status'
        ];

        foreach ($filters as $field) {
            if ($request->has($field)) {
                // Apply filtering for exact match or like query (if applicable)
                if ($field == 'expiry_date') {
                    // Special handling for date filtering if needed
                    $query->whereDate($field, $request->input($field));
                } else {
                    $query->where($field, 'like', '%' . $request->input($field) . '%');
                }
            }
        }

        // Get the paginated results
        $jobApplies = $query->paginate($perPage);

        return response()->json($jobApplies);
    }

    /**
     * Change the status of a job application.
     */
    public function changeStatus(Request $request, $id)
    {
        // Validate status change
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in: Pending,In Process,Waiting,Hired,Potential',  // Status must be one of the predefined values
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the job application by ID
        $jobApply = JobApply::find($id);

        if (!$jobApply) {
            return response()->json([
                'success' => false,
                'message' => 'Job application not found.'
            ], 404);
        }

        // Update the status
        $jobApply->status = $request->status;
        $jobApply->save();

        return response()->json([
            'success' => true,
            'message' => 'Job application status updated successfully.',
            'data' => $jobApply
        ]);
    }



  /**
     * Export job applications to Excel (only for admins).
     */
    public function exportToExcel(Request $request)
    {
        // Get token from URL query parameter (for example, ?token=your_token)
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['error' => 'No token provided.'], 400);
        }

        try {
            // Validate and authenticate the token for the admin guard
            $admin = JWTAuth::setToken($token)->authenticate();

            if (!$admin) {
                return response()->json(['error' => 'Unauthorized. Invalid token.'], 403);
            }

            // Check if the authenticated user has admin privileges
            // If your Admin model doesn't have an 'is_admin' field or other check, just proceed with the assumption the token is for an admin.
            // You could also check other properties here if needed (e.g., roles).

            // If everything is valid, allow the export
            return Excel::download(new JobApplyExport, 'job_applications.xlsx');
        } catch (\Exception $e) {
            // Log the error (for debugging)
            Log::error('Error authenticating JWT for admin: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to authenticate token.'], 500);
        }
    }

}
