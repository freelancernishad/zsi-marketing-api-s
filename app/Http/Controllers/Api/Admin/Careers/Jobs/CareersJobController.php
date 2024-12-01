<?php

namespace App\Http\Controllers\Api\Admin\Careers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\CareersJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareersJobController extends Controller
{

/**
 * Display a listing of the jobs.
 */
public function index(Request $request)
{
    // Check if 'per_page' parameter exists in the request
    $perPage = $request->get('per_page', 10); // Default to 10 if not provided

    $jobs = CareersJob::paginate($perPage);

    return response()->json($jobs);
}


    /**
     * Store a newly created job in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'job_details' => 'required|string',
            'responsibilities' => 'nullable|string',
            'vacancies' => 'required|integer',
            'job_type' => 'required|string',
            'expiry_date' => 'required|date',
            'category' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'experience_level' => 'nullable|string',
            'salary_type' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'office_time' => 'nullable|string',
            'show_on_career_page' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job = CareersJob::create($request->only([
            'job_title',
            'job_details',
            'responsibilities',
            'vacancies',
            'job_type',
            'expiry_date',
            'category',
            'employment_type',
            'experience_level',
            'salary_type',
            'salary',
            'office_time',
            'show_on_career_page',
        ]));

        return response()->json($job);
    }

    /**
     * Display the specified job.
     */
    public function show($id)
    {
        $job = CareersJob::find($id);

        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        return response()->json($job);
    }

    /**
     * Update the specified job in storage.
     */
    public function update(Request $request, $id)
    {
        $job = CareersJob::find($id);

        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'job_details' => 'required|string',
            'responsibilities' => 'nullable|string',
            'vacancies' => 'required|integer',
            'job_type' => 'required|string',
            'expiry_date' => 'required|date',
            'category' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'experience_level' => 'nullable|string',
            'salary_type' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'office_time' => 'nullable|string',
            'show_on_career_page' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job->update($request->only([
            'job_title',
            'job_details',
            'responsibilities',
            'vacancies',
            'job_type',
            'expiry_date',
            'category',
            'employment_type',
            'experience_level',
            'salary_type',
            'salary',
            'office_time',
            'show_on_career_page',
        ]));

        return response()->json($job);
    }

    /**
     * Remove the specified job from storage.
     */
    public function destroy($id)
    {
        $job = CareersJob::find($id);

        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $job->delete();
        return response()->json(['message' => 'Job deleted successfully']);
    }
}
