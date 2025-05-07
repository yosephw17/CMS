<?php

namespace App\Http\Controllers;

use App\Models\YearSemesterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YearSemesterCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   // CourseController.php
public function index(Request $request)
{
    // $validated = $request->validate([
    //     'year_name' => 'required|string',
    //     'semester_name' => 'required|string',
    //     'department_name' => 'required|string'
    // ]);

    $courses = YearSemesterCourse::with('course','semester','year','department')
        // ->whereHas('year', function($q) use ($validated) {
        //     $q->where('name', $validated['year_name']);
        // })
        // ->whereHas('semester', function($q) use ($validated) {
        //     $q->where('name', $validated['semester_name']);
        // })
        // ->whereHas('department', function($q) use ($validated) {
        //     $q->where('name', $validated['department_name']);
        // })
        ->get();

    return response()->json($courses);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function updatePreferredRooms(Request $request, $id)
    {
        Log::info("Update Preferred Rooms Request", $request->all());
    
        $validated = $request->validate([
            'preferred_lecture_room_id' => 'nullable|exists:rooms,id',
            'preferred_lab_room_id' => 'nullable|exists:rooms,id'
        ]);
    
        $course = YearSemesterCourse::findOrFail($id);
    
        if ($request->has('preferred_lecture_room_id')) {
            $course->preferred_lecture_room_id = $validated['preferred_lecture_room_id'];
        }
    
        if ($request->has('preferred_lab_room_id')) {
            $course->preferred_lab_room_id = $validated['preferred_lab_room_id'];
        }
    
        $course->save();
    
        return response()->json($request);
    }
    
 
    public function store(Request $request)
    { 
        

        $request->validate([
            'year_id' => 'required|exists:years,id',
            'semester_id' => 'required|exists:semesters,id',
            'course_id' => 'required|exists:courses,id',
            'department_id' => 'required|exists:departments,id',
            'stream_id' => [
                'nullable',
                'exists:streams,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $stream = \App\Models\Stream::where('id', $value)
                            ->where('department_id', $request->department_id)
                            ->first();
                        if (!$stream) {
                            $fail('The selected stream is not valid for this department.');
                        }
                        $yearOrder = \App\Models\Year::where('id', $request->year_id)->value('id');
                        $streamYearOrder = $stream->year_id;
                        if ($yearOrder < $streamYearOrder ||
                            ($yearOrder == $streamYearOrder && $request->semester_id < $stream->semester_id)) {
                            $fail('The selected stream is not active for this year and semester.');
                        }
                    }
                },
            ],
        ]);

        $yearSemesterCourse = YearSemesterCourse::create($request->all());
        Log::info("Update Preferred Rooms Request", ["year",$yearSemesterCourse]);
        return response()->json($yearSemesterCourse->load(['year', 'semester', 'course','department', 'stream']), 201);
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
        $yearSemesterCourse->update($request->only(['year_id', 'semester_id', 'course_id','stream_id']));

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
