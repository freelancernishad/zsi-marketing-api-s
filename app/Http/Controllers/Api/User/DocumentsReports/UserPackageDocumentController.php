<?php

namespace App\Http\Controllers\Api\User\DocumentsReports;

use App\Http\Controllers\Controller;
use App\Models\UserPackageDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPackageDocumentController extends Controller
{
    /**
     * Get a list of documents or reports for a specific UserPackage.
     *
     * @param Request $request
     * @param int $userPackageId
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $userPackageId, $type)
    {
        // Validate the type
        if (!in_array($type, ['document', 'report'])) {
            return response()->json(['message' => 'Invalid type specified'], 400);
        }

        // Get the list of documents/reports for the UserPackage with the package relationship
        $documents = UserPackageDocument::with('userPackage.package') // Eager load the package relationship
            ->where('userpackage_id', $userPackageId)
            ->where('type', $type)
            ->get();

        // Format the response
        $formattedDocuments = $documents->map(function ($document) {
            return [
                'package_name' => $document->userPackage->package->name ?? 'N/A', // Package name
                'uploaded_date' => $document->uploaded_date, // Uploaded date
                'file_name' => basename($document->file), // Extract file name from the file path
                'file' => $document->file, // Full file path or URL
            ];
        });

        return response()->json($formattedDocuments);
    }

    /**
     * Upload a document or report for a specific UserPackage.
     *
     * @param Request $request
     * @param int $userPackageId
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $userPackageId, $type)
    {
        // Validate the type
        if (!in_array($type, ['document', 'report'])) {
            return response()->json(['message' => 'Invalid type specified'], 400);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // Adjust file types and size as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new UserPackageDocument record
        $document = new UserPackageDocument();
        $document->userpackage_id = $userPackageId;

        // Upload the file and save the file path
        $filePath = $document->saveDocumentFile($request->file('file'), $type);

        return response()->json($document, 201);
    }

    /**
     * Update a document or report for a specific UserPackage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the document/report
        $document = UserPackageDocument::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document/Report not found'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'file' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // Adjust file types and size as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the file if a new file is provided
        if ($request->hasFile('file')) {
            // Delete the old file
            $document->deleteFileFromStorage();

            // Upload the new file and save the file path
            $document->saveDocumentFile($request->file('file'), $document->type);
        }

        // Save the changes
        $document->save();

        return response()->json($document);
    }

    /**
     * Delete a document or report.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the document/report
        $document = UserPackageDocument::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document/Report not found'], 404);
        }

        // Delete the file from storage
        $document->deleteFileFromStorage();

        // Delete the record
        $document->delete();

        return response()->json(['message' => 'Document/Report deleted successfully']);
    }
}
