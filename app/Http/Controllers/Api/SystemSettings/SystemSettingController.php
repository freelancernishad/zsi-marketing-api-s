<?php

namespace App\Http\Controllers\Api\SystemSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    public function storeOrUpdate(Request $request)
{
    // Validate the input to ensure it's an array of key-value pairs
    $rules = [
        '*' => 'required|array', // Each item must be an array with key-value pairs
        '*.key' => 'required|string', // Each key must be a string
        '*.value' => 'required|string', // Each value must be a string
    ];

    // Create the validator instance
    $validator = Validator::make($request->all(), $rules);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422); // Unprocessable Entity
    }

    // Loop through the settings array and update or create each setting
    $settingsData = $request->all();

    foreach ($settingsData as $setting) {
        // Update or create the system setting in the database
        SystemSetting::updateOrCreate(
            ['key' => $setting['key']], // Search by 'key'
            ['value' => $setting['value']] // If found, update 'value'; if not, create a new setting
        );

        // If the setting key is related to AWS, update the .env file
        if (in_array($setting['key'], [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_URL',
            'AWS_ENDPOINT',
            'AWS_USE_PATH_STYLE_ENDPOINT',
        ])) {
            // Get the current .env contents
            $envPath = base_path('.env');
            $envContents = file_get_contents($envPath);

            // Create the pattern to match the existing setting key in .env
            $pattern = "/^" . preg_quote($setting['key'], '/') . "=.*/m";

            // If the key exists in the .env file, replace it, otherwise add it
            if (preg_match($pattern, $envContents)) {
                // Replace the line with the new value
                $envContents = preg_replace($pattern, $setting['key'] . '=' . $setting['value'], $envContents);
            } else {
                // If the key doesn't exist, append it at the end of the .env file
                $envContents .= "\n" . $setting['key'] . '=' . $setting['value'];
            }

            // Write the updated contents back to the .env file
            file_put_contents($envPath, $envContents);
        }
    }

    // Return success response
    return response()->json([
        'message' => 'System settings saved and .env updated successfully!'
    ], 200); // OK
}

}
