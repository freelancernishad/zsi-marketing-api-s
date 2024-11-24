<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * Upload a file to the specified disk ('protected' or 's3').
 */
Route::post('/upload-file', function (Request $request) {
    // Validation rules
    $rules = [
        'file' => 'required|file|max:2048', // Max 2MB file
    ];

    // Validate the request
    $validationResponse = validateRequest($request->all(), $rules);
    if ($validationResponse) {
        return $validationResponse; // Return if validation fails
    }

    try {
        // Determine the upload location
        if ($request->type === 's3') {
            $filePath = uploadFileToS3($request->file('file')); // Upload to S3
        } else {
            $filePath = uploadFileToProtected($request->file('file')); // Upload to 'protected' disk
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'file_path' => $filePath,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 400);
    }
});

/**
 * Read a file from the 'protected' disk.
 */
Route::get('/read-file/{filename}', function ($filename) {
    try {
        // Retrieve the file using the global function
        return readFileFromProtected($filename);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 404);
    }
});
