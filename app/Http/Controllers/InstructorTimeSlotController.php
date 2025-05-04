<?php

namespace App\Http\Controllers;

use App\Models\InstructorTimeSlot;
use Illuminate\Http\Request;

class InstructorTimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return InstructorTimeSlot::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'time_slot_id' => 'required|exists:time_slots,id',
        ]);

        return InstructorTimeSlot::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(InstructorTimeSlot $instructorTimeSlot)
    {
        return $instructorTimeSlot;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InstructorTimeSlot $instructorTimeSlot)
    {
        $validated = $request->validate([
            'instructor_id' => 'sometimes|required|exists:instructors,id',
            'time_slot_id' => 'sometimes|required|exists:time_slots,id',
        ]);

        $instructorTimeSlot->update($validated);
        return $instructorTimeSlot;
    }

    /**
     * Remove the specified resource from storage.
     */
   /**
 * Remove a specific instructor-time slot assignment
 */
public function destroy(Request $request)
{
    $validated = $request->validate([
        'instructor_id' => 'required|exists:instructors,id',
        'time_slot_id' => 'required|exists:time_slots,id'
    ]);

    $deleted = InstructorTimeSlot::where('instructor_id', $validated['instructor_id'])
        ->where('time_slot_id', $validated['time_slot_id'])
        ->firstOrFail()
        ->delete();

    return response()->noContent();
}
}