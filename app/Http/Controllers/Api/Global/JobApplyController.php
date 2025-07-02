<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use App\Models\JobApply;
use App\Models\CareersJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobApplyController extends Controller
{
    /**
     * Store a new job application.
     */
    public function store(Request $request)
    {
        // Validate data using Validator
        $validator = Validator::make($request->all(), [
            'careers_job_id' => 'required|exists:careers_jobs,id',  // Ensure the CareersJob ID is valid
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'cover_letter' => 'nullable|string',
            'experience_level' => 'nullable|string',
            'years_of_experience' => 'nullable|string',
            'resume' => 'required|file|mimes:pdf,doc,docx,jpeg,png,jpg',
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get validated data
        $validatedData = $validator->validated();

        // Get the CareersJob model using the provided careers_job_id
        $careersJob = CareersJob::find($validatedData['careers_job_id']);

        // Add CareersJob data to JobApply (only include relevant fields)
        $validatedData['job_title'] = $careersJob->job_title;
        $validatedData['job_details'] = $careersJob->job_details;
        $validatedData['responsibilities'] = $careersJob->responsibilities;
        $validatedData['vacancies'] = $careersJob->vacancies;
        $validatedData['job_type'] = $careersJob->job_type;
        $validatedData['expiry_date'] = $careersJob->expiry_date;
        $validatedData['category'] = $careersJob->category;
        $validatedData['employment_type'] = $careersJob->employment_type;
        // $validatedData['experience_level'] = $careersJob->experience_level;
        $validatedData['salary_type'] = $careersJob->salary_type;
        $validatedData['salary'] = $careersJob->salary;
        $validatedData['office_time'] = $careersJob->office_time;
        $validatedData['show_on_career_page'] = $careersJob->show_on_career_page;

        // Create JobApply and associate the CareersJob ID
        $jobApply = JobApply::create($validatedData);

        // Handle file upload for resume
        if ($request->hasFile('resume')) {
            $jobApply->saveResume($request->file('resume'));
        }

        return response()->json($jobApply, 201);
    }

    /**
     * Search job application by application_id.
     */
    public function searchByApplicationId($application_id)
    {
        $jobApply = JobApply::where('application_id', $application_id)->first();

        if (!$jobApply) {
            return response()->json([
                'error' => 'Job application not found.'
            ], 404);
        }

        return response()->json($jobApply);
    }
}
