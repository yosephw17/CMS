<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Services\CourseAssignmentService;

class AssignmentController extends Controller
{
    protected $courseAssignmentService;

    public function __construct(CourseAssignmentService $courseAssignmentService)
    {
        $this->courseAssignmentService = $courseAssignmentService;
    }

    public function index()
    {
        $assignments = Assignment::orderBy('year')->orderBy('semester')->get();
        return Assignment::orderBy('year')->orderBy('semester')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string',
            'semester' => 'required|integer',
        ]);

        $assignment = Assignment::create([
            'year' => $request->year,
            'semester' => $request->semester,
        ]);

        // Call the service and pass the assignment ID
        // Call the service and get the assigned results
    $assignedResults = $this->courseAssignmentService->assignCourses($assignment->id);

    // Return the assignment details along with assigned results
    return response()->json([
        'assignment' => $assignment,
        'assigned_results' => $assignedResults
    ], 201);
    }

    public function update(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

        $request->validate([
            'year' => 'required|string',
            'semester' => 'required|integer',
        ]);

        $assignment->update([
            'year' => $request->year,
            'semester' => $request->semester,
        ]);

        return response()->json($assignment);
    }

    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted successfully']);
    }
}
