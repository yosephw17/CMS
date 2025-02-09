<?php

namespace App\Http\Controllers;

use App\Models\YearSemesterCourse;
use Illuminate\Http\Request;

class YearSemesterCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(YearSemesterCourse::with(['year', 'semester', 'course'])->get());
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
        $request->validate([
            'year_id' => 'required|exists:years,id',
            'semester_id' => 'required|exists:semesters,id',
            'course_id' => 'required|exists:courses,id'
        ]);

        $yearSemesterCourse = YearSemesterCourse::create($request->all());

        return response()->json($yearSemesterCourse->load(['year', 'semester', 'course']), 201);
    }
//     public function removeCourse(Request $request, $courseId)
// {
//     // Validate year_id and semester_id
//     $request->validate([
//         'year_id' => 'required|exists:years,id',
//         'semester_id' => 'required|exists:semesters,id',
//     ]);

//     // Find the course
//     $yearSemesterCourse = YearSemesterCourse::where('course_id', $courseId)
//         ->where('year_id', $request->year_id)
//         ->where('semester_id', $request->semester_id)
//         ->first();

//     if ($yearSemesterCourse) {
//         // Delete the course
//         $yearSemesterCourse->delete();
//         return response()->json(['message' => 'Course removed successfully.'], 200);
//     }

//     return response()->json(['message' => 'Course not found.'], 404);
// }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(YearSemesterCourse::with(['year', 'semester', 'course'])->findOrFail($id));
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
        $yearSemesterCourse = YearSemesterCourse::findOrFail($id);
        $yearSemesterCourse->update($request->only(['year_id', 'semester_id', 'course_id']));

        return response()->json($yearSemesterCourse->load(['year', 'semester', 'course']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $yearSemesterCourse = YearSemesterCourse::findOrFail($id);
        $yearSemesterCourse->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function findByYearAndSemester(Request $request)
{
    $request->validate([
        'year_id' => 'required|integer',
        'semester_id' => 'required|integer',
    ]);

    $courses = YearSemesterCourse::where('year_id', $request->year_id)
                    ->where('semester_id', $request->semester_id)
                    // ->with('course') // Assuming there's a relationship to fetch course details
                    ->get();

    return response()->json($courses);
}
}
