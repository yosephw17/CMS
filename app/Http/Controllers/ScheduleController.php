<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Day;
use App\Models\ScheduleResult;
use App\Models\Activity;
use App\Models\TimeSlot;
use App\Services\SchedulingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $schedulingService;

    public function __construct(SchedulingService $schedulingService)
    {
        $this->schedulingService = $schedulingService;
    }

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
  Activity::create([
            'name' => "Created schedule for year: {$request->year}, semester: {$request->semester_id}",
            'user_id' => Auth::id(),
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

    public function generateSchedule(Request $request, $id)
    {
        // $request->validate([
        //     'schedule_id' => 'required|integer|exists:schedules,id',
        // ]);

        // Increase execution time to 300 seconds (5 minutes)
        ini_set('max_execution_time', 300);

        $success = $this->schedulingService->generateSchedule($id);

        if ($success) {
            return response()->json(['message' => 'Schedule generated successfully']);
        }

        return response()->json(['message' => 'Schedule generation failed'], 500);
    }
    public function getScheduleResults(Request $request, $scheduleId)
    {
        try {
            $results = ScheduleResult::where('schedule_id', $scheduleId)
               
                ->with([
                    'course' => function ($query) {
                        $query->select('id', 'name', 'lecture_cp', 'lab_cp');
                    },
                    'instructor' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'room' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'section' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'scheduleTimeSlots.timeSlot.day' => function ($query) {
                        $query->select('days.id', 'days.name');
                    },
                    'scheduleTimeSlots.timeSlot' => function ($query) {
                        $query->select('time_slots.id', 'time_slots.day_id', 'time_slots.start_time', 'time_slots.end_time')
                            ->where('is_break', false);
                    },
                ])
                ->get()
                ->groupBy('section_id')
                ->map(function ($sectionResults, $sectionId) {
                    $section = $sectionResults->first()->section;
                    return [
                        'section_id' => $sectionId,
                        'section_name' => $section ? $section->name : "Section {$sectionId}",
                        'assignments' => $sectionResults->map(function ($result) {
                            $timeSlots = $result->scheduleTimeSlots
                                ->map(function ($sts) {
                                    $slot = $sts->timeSlot;
                                    return [
                                        'time_slot_id' => $slot->id,
                                        'day_id' => $slot->day_id,
                                        'day_name' => $slot->day->name,
                                        'start_time' => $slot->start_time,
                                        'end_time' => $slot->end_time,
                                    ];
                                })
                                ->sortBy('start_time')
                                ->values();

                            return [
                                'course_id' => $result->course_id,
                                'course_name' => $result->course ? $result->course->name : "Course {$result->course_id}",
                                'instructor_id' => $result->instructor_id,
                                'instructor_name' => $result->instructor ? $result->instructor->name : "Instructor {$result->instructor_id}",
                                'room_id' => $result->room_id,
                                'room_name' => $result->room ? $result->room->name : "Room {$result->room_id}",
                                'type' => $result->type,
                                'time_slots' => $timeSlots,
                            ];
                        })->values(),
                    ];
                })->values();

            if ($results->isEmpty()) {
                return response()->json([
                    'message' => 'No scheduling results found for the given schedule ID',
                    'data' => [],
                ], 200);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch schedule results', [
                'schedule_id' => $scheduleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to fetch schedule results',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getTimeSlots(Request $request)
    {
        try {
            Log::info('Fetching time slots');

            $timeSlots = TimeSlot::where('is_break', false)
                ->with(['day' => function ($query) {
                    $query->select('id', 'name');
                }])
                ->select('id', 'day_id', 'start_time', 'end_time')
                ->orderBy('start_time')
                ->get()
                ->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'day_id' => $slot->day_id,
                        'day_name' => $slot->day ? $slot->day->name : "Day {$slot->day_id}",
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                    ];
                });

            Log::info('Time slots fetched', ['count' => $timeSlots->count()]);

            return response()->json($timeSlots);
        } catch (\Exception $e) {
            Log::error('Failed to fetch time slots', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to fetch time slots',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDays(Request $request)
    {
        try {
            Log::info('Fetching days');

            $days = Day::select('id', 'name')
                ->orderBy('id')
                ->get();

            Log::info('Days fetched', ['count' => $days->count()]);

            return response()->json($days);
        } catch (\Exception $e) {
            Log::error('Failed to fetch days', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to fetch days',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // public function generate(Request $request)
    // {
    //     try {
    //         // Get schedule_id from request body
    //         $scheduleId = $request->input('schedule_id');

    //         if (!$scheduleId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Schedule ID is required',
    //             ], 400);
    //         }

    //         $schedule = Schedule::find($scheduleId);

    //         if (!$schedule) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Schedule not found',
    //             ], 404);
    //         }

    //         // Check if schedule already has results
    //         if ($schedule->results()->exists()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Schedule already has generated results. Clear existing schedule first.',
    //             ], 400);
    //         }

    //         // Generate the schedule
    //         $success = $this->scheduleGenerator->generateSchedule($schedule);

    //         if ($success) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Schedule generated successfully',
    //                 'data' => [
    //                     'schedule_id' => $schedule->id,
    //                     'generated_at' => now()->toDateTimeString(),
    //                     'results_count' => $schedule->results()->count()
    //                 ]
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Schedule generation failed'
    //         ], 500);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error generating schedule',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // Delete a schedule
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully.'
        ]);
    }
}