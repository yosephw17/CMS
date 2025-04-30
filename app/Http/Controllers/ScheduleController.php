<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // Get all schedules
    public function index()
    {
        return response()->json(Schedule::all());
    }

    // Store a new schedule
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string',
        ]);
        $exists = Schedule::where('year', $request->year)
        ->where('semester_id', $request->semester_id)
        ->exists();

if ($exists) {
return response()->json(['error' => 'The selected year and semester already exist.'], 400);
}
        $schedule = Schedule::create([
            'year' => $request->year,
            'semester_id' => $request->semester_id,
        ]);

        return response()->json([
            'message' => 'Schedule created successfully.',
            'data' => $schedule
        ], 201);
    }

    // Show a specific schedule
    public function show(Schedule $schedule)
    {
        return response()->json($schedule);
    }

    // Update a schedule
    public function update(Request $request, Schedule $schedule)
    {
         $request->validate([
            'year' => 'required|string',
        ]);

        $schedule->update([
            'year' => $request->year,
            'semester_id' => $request->semester_id,
        ]);

        return response()->json([
            'message' => 'Schedule updated successfully.',
            'data' => $schedule
        ]);
    }

    // Delete a schedule
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully.'
        ]);
    }
}
