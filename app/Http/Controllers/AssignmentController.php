<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Result;
use App\Services\CourseAssignmentService;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    protected $courseAssignmentService;

    public function __construct(CourseAssignmentService $courseAssignmentService)
    {
        $this->courseAssignmentService = $courseAssignmentService;
    }

    public function index()
    {
        $assignments = Assignment::with(['results.instructor', 'results.course','results.instructor.role'])->get();
        return response()->json($assignments);

    }

    public function show($id){
        $assignment = Assignment::with(['results.instructor', 'results.course'])->findOrFail($id);
        return response()->json($assignment);

    }
    public function latest()
    
    {
        // Fetch the latest assignment using 'created_at' timestamp
        $latestAssignment = Assignment::latest()->first(); // or you can use orderBy('created_at', 'desc')

        if (!$latestAssignment) {
            return response()->json(['error' => 'No assignments found'], 404);
        }

        return response()->json($latestAssignment);
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string',
        ]);
    
        $exists = Assignment::where('year', $request->year)
                            ->where('semester_id', $request->semester_id)
                            ->where('department_id', $request->department_id)
                            ->exists();
    
        if ($exists) {
            return response()->json(['error' => 'The selected year and semester already exist.'], 400);
        }
    
        $assignment = Assignment::create([
            'year' => $request->year,
            'semester_id' => $request->semester_id,
            'department_id'=>$request->department_id,
        ]);
    
        return response()->json(['success' => 'Assignment created successfully.', 'assignment' => $assignment], 201);
    }
    
    public function assignCourses($id)
    {
        // Ensure the assignment exists
        $assignment = Assignment::findOrFail($id);
        $assignedResults = $this->courseAssignmentService->assignCourses($assignment->id);

        // Return the assignment details along with assigned results
        return response()->json([
            'assignment' => $assignment,
            'assigned_results' => $assignedResults
        ], 201);
    }
// public function assignmentUpdate(Request $request, $id){
//     $request->validate([
//         'instructor_id' => 'required|exists:instructors,id',
//         'course_id'=>'required|exists:courses,id',
//         'change_reason' => 'required|string',
//         'is_assigned' => 'boolean',
//     ]);
//     $result = Result::where('assignment_id',$id)->where('instructor_id', $request->instructor_id)->where('course_id',$request->course_id)->get();
// Log::info("result", $result->all());
//     // Optional: log previous instructor if needed
//     // $assignment->previous_instructor_id = $assignment->instructor_id;

//     $result->instructor_id = $request->instructor_id;
//     $result->change_reason = $request->change_reason;
//     $result->is_assigned = $request->is_assigned ?? 1;
//     $result->save();

//     return response()->json([
//         'message' => 'Assignment updated successfully',
//         'assignment' => $assignment,
//     ]);
// }
public function assignmentUpdate(Request $request, $id) {
    $request->validate([
        'instructor_id' => 'required|exists:instructors,id',
        'previous_instructor_id' => 'required|exists:instructors,id',
        'course_id' => 'required|exists:courses,id',
        'change_reason' => 'required|string',
        'is_assigned' => 'boolean',
    ]);

    $result = Result::where('assignment_id', $id)
                    ->where('instructor_id', $request->previous_instructor_id)
                    ->where('course_id', $request->course_id)
                    ->first(); // Get the first result
                    Log::info("Assignment Update - assignment_id: " . $id . ", instructor_id: " . $request->instructor_id . ", course_id: " . $request->previous_instructor_id);

    if (!$result) {
        return response()->json(['message' => 'Assignment not found'], 404);
    }

    // Update the assignment
    $result->instructor_id = $request->instructor_id;
    $result->reason = $request->change_reason;
    $result->is_assigned = $request->is_assigned ?? 1;
    $result->save();

    return response()->json([
        'message' => 'Assignment updated successfully',
        'assignment' => $result,
    ]);
}

    public function update(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

        $request->validate([
            'year' => 'required|string',
         ]);

        $assignment->update([
            'year' => $request->year,
            'semester_id' => $request->semester_id,
            'department_id'=>$request->department_id,
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
