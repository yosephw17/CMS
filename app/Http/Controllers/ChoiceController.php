<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Choice;
use Illuminate\Http\Request;

class ChoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function bulkStore(Request $request) {
        // Validate the incoming data to ensure we have an array of choices
        $validated = $request->validate([
            'choices' => 'required|array',
            'choices.*.instructor_id' => 'required|exists:instructors,id',
            'choices.*.course_id' => 'required|exists:courses,id',
            'choices.*.assignment_id' => 'required|exists:assignments,id',
            'choices.*.rank' => 'nullable|integer',
        ]);

        

        // Insert the bulk choices
        $choices = array_map(function ($choice) {

            return [
                'instructor_id' => $choice['instructor_id'],
                'course_id' => $choice['course_id'],
                'assignment_id' => $choice['assignment_id'],  
                'rank' => $choice['rank'] ?? 0,  
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $validated['choices']);

        // Perform the bulk insert
        Choice::insert($choices);

        return response()->json(['message' => 'Choices inserted successfully'], 201);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
