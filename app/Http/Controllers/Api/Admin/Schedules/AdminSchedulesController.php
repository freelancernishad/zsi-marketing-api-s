<?php

namespace App\Http\Controllers\Api\Admin\Schedules;

use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminSchedulesController extends Controller
{

    /**
     * Get all schedules with optional global filters
     */
    public function index(Request $request)
    {
        $query = Schedule::query();

        // Apply global search filters
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('assigned_to', 'LIKE', "%{$search}%")
                  ->orWhere('invitee_first_name', 'LIKE', "%{$search}%")
                  ->orWhere('invitee_last_name', 'LIKE', "%{$search}%")
                  ->orWhere('invitee_email', 'LIKE', "%{$search}%");
            });
        }

        // Add any additional filters (e.g., by date range)
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('event_start_time', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        // Paginate the results
        $schedules = $query->paginate(10);

        return response()->json([
            'schedules' => $schedules,
        ], 200);
    }

    /**
     * Get a single schedule by ID
     */
    public function show($id)
    {
        // Retrieve the schedule by ID
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Schedule not found.',
            ], 404);
        }

        return response()->json([
            'schedule' => $schedule,
        ], 200);
    }
}
