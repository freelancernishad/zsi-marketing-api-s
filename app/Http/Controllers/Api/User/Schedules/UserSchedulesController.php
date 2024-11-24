<?php

namespace App\Http\Controllers\Api\User\Schedules;

use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserSchedulesController extends Controller
{

    // Create a new schedule
    public function create(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|string|max:255',
            'event_start_time' => 'required|date',
            'event_end_time' => 'required|date|after:event_start_time',
            'invitee_first_name' => 'required|string|max:255',
            'invitee_last_name' => 'required|string|max:255',
            'invitee_full_name' => 'nullable|string|max:255',
            'invitee_email' => 'required|email',
            'answers' => 'required|array',
            'answers.*' => 'string',  // Answers should be an array of strings
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Format the event times to MySQL-compatible format (YYYY-MM-DD HH:MM:SS)
        $eventStartTime = Carbon::parse($request->event_start_time)->format('Y-m-d H:i:s');
        $eventEndTime = Carbon::parse($request->event_end_time)->format('Y-m-d H:i:s');

        // Create the schedule, associate with the authenticated user
        $schedule = Schedule::create([
            'assigned_to' => $request->assigned_to,
            'event_start_time' => $eventStartTime,
            'event_end_time' => $eventEndTime,
            'invitee_first_name' => $request->invitee_first_name,
            'invitee_last_name' => $request->invitee_last_name,
            'invitee_full_name' => $request->invitee_full_name ?? $request->invitee_first_name . ' ' . $request->invitee_last_name,
            'invitee_email' => $request->invitee_email,
            'answers' => $request->answers,
            'user_id' => Auth::id(), // Associate the schedule with the authenticated user
        ]);

        return response()->json($schedule, 201);
    }

    // Get the authenticated user's schedules
    public function index()
    {
        $user = Auth::user();

        // Get the schedules associated with the authenticated user
        $schedules = Schedule::where('user_id', $user->id)->get();

        return response()->json($schedules, 200);
    }

    // Get a single schedule by ID
    public function show($id)
    {
        $user = Auth::user();

        // Find the schedule, but only if it's associated with the authenticated user
        $schedule = Schedule::where('user_id', $user->id)->find($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Schedule not found or unauthorized.',
            ], 404);
        }

        return response()->json($schedule, 200);
    }
}
