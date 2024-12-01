<?php

namespace App\Http\Controllers\Api\Admin\Careers;

use App\Http\Controllers\Controller;
use App\Models\JobApply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobApplyController extends Controller
{
    /**
     * Display a listing of the job applications with pagination and filters.
     */
    public function index(Request $request)
    {
        // Default per_page value
        $perPage = $request->input('per_page', 10);

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
}
