<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{

    /**
     * Display a listing of the courses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all courses with related fields
        $courses = Course::with('fields')->get();
        return response()->json($courses);
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255|unique:courses',
            'cp' => 'required|integer',
            'lecture_cp' => 'nullable|integer',
            'lab_cp' => 'nullable|integer',
            'tut_cp' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'fields' => 'nullable|array', // Validate that fields is an array
            'fields.*' => 'exists:fields,id' // Each field must exist in the fields table
        ]);
Log::info("message", [$request->all()]);
        // Create the course
        $course = Course::create($request->except('fields'));

        // Attach related fields if provided
        if ($request->has('fields')) {
            $course->fields()->attach($request->fields);
        }

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully.',
            'data' => $course->load('fields'),
        ]);
    }

    /**
     * Display the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Course $course)
    {
        return response()->json([
            'success' => true,
            'data' => $course->load('fields'),
        ]);
    }

    /**
     * Update the specified course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255|unique:courses,course_code,' . $course->id,
            'cp' => 'required|integer',
            'lecture_cp' => 'nullable|integer',
            'lab_cp' => 'nullable|integer',
            'tut_cp' => 'nullable|integer',
            'department_id'=>'nullable|integer',
            'fields' => 'nullable|array',
            'fields.*' => 'exists:fields,id'
        ]);

        // Update the course
        $course->update($request->except('fields'));

        // Sync related fields (remove old ones and add new ones)
        if ($request->has('fields')) {
            $course->fields()->sync($request->fields);
        }

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
            'data' => $course->load('fields'),
        ]);
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Course $course)
    {
        // Detach fields before deleting
        $course->fields()->detach();
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.',
        ]);
    }
}
