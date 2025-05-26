<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    public function index()
    {
        return Instructor::with([
            'role',
            'educationalBackgrounds',
            'professionalExperiences',
            'courses' // Include courses relationship
        ])->get();
    }

    public function store(Request $request)
    {
        // Set default role_id and department_id if not provided
        $roleId = $request->role_id ?: 1;
        $departmentId = $request->department_id ?: 1;

        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'is_available' => 'required|boolean',
            'email' => 'required|email|unique:instructors,email',
            'phone' => 'nullable|string',
            'studying' => 'nullable|string',
            'is_studying' => 'nullable|boolean',
            'is_approved' => 'nullable|boolean',
            'is_mentor' => 'nullable|boolean',
            'role_id' => 'nullable|integer|exists:instructor_roles,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'pro_exp_ids' => 'nullable|array',
            'pro_exp_ids.*' => 'nullable|integer|exists:professional_experiences,id',
            'edu_backgrounds' => 'nullable|array',
            'edu_backgrounds.*.edu_background_id' => 'nullable|integer|exists:educational_backgrounds,id',
            'edu_backgrounds.*.field_id' => 'nullable|integer|exists:fields,id',
            'courses' => 'nullable|array', // Validate courses array
            'courses.*.course_id' => 'required|integer|exists:courses,id',
            'courses.*.number_of_semesters' => 'required|integer|min:0',
            'courses.*.is_recent' => 'required|boolean',
        ]);

        // Start a transaction to ensure data consistency
        return DB::transaction(function () use ($request, $roleId, $departmentId) {
            // Prepare data for instructor creation
            $data = $request->all();
            $data['role_id'] = $roleId;
            $data['department_id'] = $departmentId;
   if (!empty($data['studying']) && preg_match('/\b(PhD|Masters)\b/i', $data['studying'])) {
            $data['is_studying'] = 1;
        }
            // Remove relationship data from the main data
            unset($data['pro_exp_ids']);
            unset($data['edu_backgrounds']);
            unset($data['courses']);

            // Create the instructor
            $instructor = Instructor::create($data);

            // Attach professional experiences
            if ($request->has('pro_exp_ids') && !empty($request->pro_exp_ids)) {
                foreach ($request->pro_exp_ids as $proExpId) {
                    if ($proExpId) {
                        DB::table('instructor_professional_experience')->insert([
                            'instructor_id' => $instructor->id,
                            'pro_exp_id' => $proExpId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Attach educational backgrounds
            if ($request->has('edu_backgrounds') && !empty($request->edu_backgrounds)) {
                foreach ($request->edu_backgrounds as $eduBackground) {
                    if ($eduBackground['edu_background_id'] && $eduBackground['field_id']) {
                        DB::table('instructor_educational_background')->insert([
                            'instructor_id' => $instructor->id,
                            'edu_background_id' => $eduBackground['edu_background_id'],
                            'field_id' => $eduBackground['field_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Attach courses
            if ($request->has('courses') && !empty($request->courses)) {
                // Ensure only one course has is_recent = true
                $hasRecent = false;
                foreach ($request->courses as $course) {
                    if ($course['is_recent']) {
                        if ($hasRecent) {
                            $course['is_recent'] = false; // Enforce one is_recent
                        } else {
                            $hasRecent = true;
                        }
                    }
                    if ($course['course_id']) {
                        DB::table('instructor_course')->insert([
                            'instructor_id' => $instructor->id,
                            'course_id' => $course['course_id'],
                            'number_of_semesters' => $course['number_of_semesters'],
                            'is_recent' => $course['is_recent'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Load relationships for response
            $instructor->load(['role', 'educationalBackgrounds', 'professionalExperiences', 'courses']);

            return response()->json($instructor, 201);
        });
    }

    public function show($id)
    {
        $instructor = Instructor::with([
            'role',
            'educationalBackgrounds',
            'professionalExperiences',
            'researches',
            'courses' // Include courses relationship
        ])->findOrFail($id);
        return response()->json($instructor);
    }

    public function update(Request $request, Instructor $instructor)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:instructors,email,{$instructor->id}",
            'phone' => 'nullable|string',
            'studying' => 'nullable|string',
            'is_available' => 'required|boolean',
            'is_studying' => 'nullable|boolean',
            'is_approved' => 'nullable|boolean',
            'is_mentor' => 'nullable|boolean',
            'role_id' => 'required|integer|exists:instructor_roles,id',
            'department_id' => 'required|integer|exists:departments,id',
            'pro_exp_ids' => 'nullable|array',
            'pro_exp_ids.*' => 'nullable|integer|exists:professional_experiences,id',
            'edu_backgrounds' => 'nullable|array',
            'edu_backgrounds.*.edu_background_id' => 'nullable|integer|exists:educational_backgrounds,id',
            'edu_backgrounds.*.field_id' => 'nullable|integer|exists:fields,id',
            'courses' => 'nullable|array',
            'courses.*.course_id' => 'required|integer|exists:courses,id',
            'courses.*.number_of_semesters' => 'required|integer|min:0',
            'courses.*.is_recent' => 'required|boolean',
        ]);
        if (!empty($request->studying) && preg_match('/\b(PhD|Masters)\b/i', $request->studying)) {
    $request->merge(['is_studying' => 1]);
}


        // Start a transaction to ensure data consistency
        return DB::transaction(function () use ($request, $instructor) {
            // Update the instructor data
            $instructor->update($request->except('pro_exp_ids', 'edu_backgrounds', 'courses'));

            // Update professional experiences
            if ($request->has('pro_exp_ids')) {
                // Delete old professional experiences
                DB::table('instructor_professional_experience')
                    ->where('instructor_id', $instructor->id)
                    ->delete();

                // Attach new professional experiences
                foreach ($request->pro_exp_ids as $proExpId) {
                    if ($proExpId) {
                        DB::table('instructor_professional_experience')->insert([
                            'instructor_id' => $instructor->id,
                            'pro_exp_id' => $proExpId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Update educational backgrounds
            if ($request->has('edu_backgrounds')) {
                // Delete old educational backgrounds
                DB::table('instructor_educational_background')
                    ->where('instructor_id', $instructor->id)
                    ->delete();

                // Attach new educational backgrounds
                foreach ($request->edu_backgrounds as $eduBackground) {
                    if ($eduBackground['edu_background_id'] && $eduBackground['field_id']) {
                        DB::table('instructor_educational_background')->insert([
                            'instructor_id' => $instructor->id,
                            'edu_background_id' => $eduBackground['edu_background_id'],
                            'field_id' => $eduBackground['field_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Update courses
            if ($request->has('courses')) {
                // Delete old courses
                DB::table('instructor_course')
                    ->where('instructor_id', $instructor->id)
                    ->delete();

                // Attach new courses
                $hasRecent = false;
                foreach ($request->courses as $course) {
                    if ($course['is_recent']) {
                        if ($hasRecent) {
                            $course['is_recent'] = false; // Enforce one is_recent
                        } else {
                            $hasRecent = true;
                        }
                    }
                    if ($course['course_id']) {
                        DB::table('instructor_course')->insert([
                            'instructor_id' => $instructor->id,
                            'course_id' => $course['course_id'],
                            'number_of_semesters' => $course['number_of_semesters'],
                            'is_recent' => $course['is_recent'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Load relationships for response
            $instructor->load(['role', 'educationalBackgrounds', 'professionalExperiences', 'courses']);

            return response()->json($instructor);
        });
    }

    public function destroy(Instructor $instructor)
    {
        // Detach relationships (cascade delete handles instructor_course)
        $instructor->educationalBackgrounds()->detach();
        $instructor->professionalExperiences()->detach();

        $instructor->delete();

        return response()->json(['message' => 'Instructor deleted successfully'], 204);
    }
}