<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Day;
use Illuminate\Support\Facades\Validator;

class TimetableController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    /**
     * Generate and store time slots based on user input.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:1',
            'lunch_start' => 'required|date_format:H:i|after_or_equal:start_time',
            'lunch_end' => 'required|date_format:H:i|after:lunch_start|before_or_equal:end_time',
            'gap_duration' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Generate time slots
            $slots = $this->timetableService->generateSlots(
                $request->start_time,
                $request->end_time,
                $request->slot_duration,
                $request->lunch_start,
                $request->lunch_end,
                $request->gap_duration
            );

            // Fetch all days
            $days = Day::all();

            if ($days->isEmpty()) {
                return response()->json([
                    'message' => 'No days found in the days table. Please add days first.',
                ], 400);
            }

            // Begin transaction to ensure data consistency
            DB::beginTransaction();

            // Clear existing time slots
            DB::table('time_slots')->delete();

            // Insert time slots for each day and prepare response
            $timetable = [];
            foreach ($days as $day) {
                $daySlots = [];
                foreach ($slots as $slot) {
                    // Insert each slot and get its ID
                    $slotId = DB::table('time_slots')->insertGetId([
                        'day_id' => $day->id,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'is_break' => $slot['is_break'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $daySlots[] = [
                        'id' => $slotId,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'status' => $slot['is_break'] ? 'lunch_break' : 'available',
                    ];
                }

                $timetable[] = [
                    'day' => $day->name,
                    'day_id' => $day->id,
                    'slots' => $daySlots,
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Time slots generated and inserted successfully for all days.',
                'data' => $timetable,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error generating time slots: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch the current timetable.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetch()
    {
        try {
            $days = Day::with(['timeSlots' => function ($query) {
                $query->select('id', 'day_id', 'start_time', 'end_time', 'is_break')
                      ->orderBy('start_time');
            }])->get();

            if ($days->isEmpty()) {
                return response()->json([
                    'message' => 'No days found in the days table.',
                    'data' => [],
                ], 200);
            }

            // Check if there are any time slots across all days
            $hasTimeSlots = $days->contains(function ($day) {
                return $day->timeSlots->isNotEmpty();
            });

            if (!$hasTimeSlots) {
                return response()->json([
                    'message' => 'There are no time slots available.',
                    'data' => [],
                ], 200);
            }

            $timetable = $days->map(function ($day) {
                $slots = $day->timeSlots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'status' => $slot->is_break ? 'lunch_break' : 'available',
                    ];
                })->toArray();

                return [
                    'day' => $day->name,
                    'day_id' => $day->id,
                    'slots' => $slots,
                ];
            })->toArray();

            return response()->json([
                'message' => 'Timetable fetched successfully',
                'data' => $timetable,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching timetable: ' . $e->getMessage(),
            ], 500);
        }
    }
}