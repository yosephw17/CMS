<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Course;
use App\Models\Parameter;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class CourseAssignmentService
{
    public function assignCourses($assignment_id)
    {
        DB::beginTransaction();

        try {
            $instructors = Instructor::with('role', 'choices', 'professionalExperiences', 'researches', 'educationalBackgrounds')
            ->where('is_available', 1)
            ->get();
        
            $parameters = Parameter::pluck('points', 'name');

            $courses = Course::all();

            $instructorScores = [];
            $assignedResults = [];
            $instructorLoad = [];

            foreach ($instructors as $instructor) {
                foreach ($courses as $course) {
                    $score = 0;

                    $choice = $instructor->choices->where('course_id', $course->id)->where('assignment_id', $assignment_id)->first();
                    if ($choice) {
                        if ($choice->rank == 1) $score += 15;
                        if ($choice->rank == 2) $score += 10;
                        if ($choice->rank == 3) $score += 5;
                    }

                    foreach ($instructor->professionalExperiences as $experience) {
                        foreach ($course->fields as $field) {
                            if ($experience->field_id == $field->id) {
                                $score += $parameters['professional_experience'] ?? 0;
                            }
                        }
                    }

                    foreach ($instructor->researches as $research) {
                        foreach ($course->fields as $field) {
                            if ($research->field_id == $field->id) {
                                $score += $parameters['research'] ?? 0;
                            }
                        }
                    }

                    foreach ($instructor->educationalBackgrounds as $education) {
                        foreach ($course->fields as $field) {
                            if ($education->field_id == $field->id) {
                                $score += $parameters['educational_background'] ?? 0;
                            }
                        }
                    }

                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'score' => $score,
                    ];
                }
            }

            $sortedScores = collect($instructorScores)->sortByDesc('score')->groupBy('course_id');

            foreach ($courses as $course) {
                $isCourseAssigned = false;
            
                if (isset($sortedScores[$course->id])) {
                    foreach ($sortedScores[$course->id] as $instructorData) {
                        $instructor = Instructor::find($instructorData['instructor_id']);
                        $role = $instructor->role;
                        $loadCapacity = $role->load;
            
                        $currentLoad = $instructorLoad[$instructor->id] ?? 0;
            
                        $existingResult = Result::where('instructor_id', $instructor->id)
                            ->where('course_id', $course->id)
                            ->where('assignment_id', $assignment_id)
                            ->exists();
            
                        if (!$existingResult) {
                            $is_assigned = 0;
                            if ($currentLoad + $course->cp <= $loadCapacity && !$isCourseAssigned) {
                                $is_assigned = 1;
                                $instructorLoad[$instructor->id] = $currentLoad + $course->cp;
                                $isCourseAssigned = true;
                            }
            
                            $result = Result::create([
                                'instructor_id' => $instructor->id,
                                'course_id' => $course->id,
                                'assignment_id' => $assignment_id,
                                'point' => $instructorData['score'],
                                'is_assigned' => $is_assigned,
                            ]);
            
                            $assignedResults[] = $result;
                        }
                    }
                }
            }
            

            DB::commit();
            return $assignedResults;

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
