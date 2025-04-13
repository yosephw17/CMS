<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\YearSemesterCourse;
use App\Models\Parameter;
use App\Models\Result;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseAssignmentService
{
    public function assignCourses($assignment_id)
    {
        DB::beginTransaction();
    
        try {
            Log::info("Starting course assignment process for assignment ID: {$assignment_id}");
            
            // Fetch the assignment to get semester_id and department_id
            $assignment = Assignment::findOrFail($assignment_id);
            $semester_id = $assignment->semester_id;
            $department_id = $assignment->department_id;
            
            Log::info("Assignment details:", [
                'semester_id' => $semester_id,
                'department_id' => $department_id
            ]);

            // Fetch instructors with their related data (only from the same department)
            $instructors = Instructor::with('role', 'choices', 'professionalExperiences', 'researches', 'educationalBackgrounds')
                ->where('is_available', 1)
                ->where('department_id', $department_id)
                ->get();
            Log::info("Fetched instructors:", ['count' => $instructors->count()]);
    
            // Fetch parameters and courses for this semester and department
            $parameters = Parameter::pluck('points', 'name');
            $yearSemesterCourses = YearSemesterCourse::with('course')
                ->where('semester_id', $semester_id)
                ->where('department_id', $department_id)
                ->get();
            
            Log::info("Fetched parameters and year_semester_courses:", [
                'parameters' => $parameters,
                'year_semester_courses_count' => $yearSemesterCourses->count(),
            ]);
    
            // Group year_semester_courses by course_id to track how many instructors we need per course
            $courseOccurrences = $yearSemesterCourses->groupBy('course_id')->map->count();
            
            $instructorScores = [];
            $assignedResults = [];
            $instructorLoad = [];
            $assignedCourses = []; // Track how many instructors have been assigned per course
    
            // Calculate scores for each instructor-course pair
            foreach ($instructors as $instructor) {
                Log::info("Calculating scores for instructor:", ['instructor_id' => $instructor->id, 'name' => $instructor->name]);
    
                foreach ($yearSemesterCourses as $yearSemesterCourse) {
                    $course = $yearSemesterCourse->course;
                    $score = 0;
    
                    $choice = $instructor->choices->where('course_id', $yearSemesterCourse->id)->where('assignment_id', $assignment_id)->first();
                    $choiceRank = $choice ? $choice->rank : null;
    
                    if ($choiceRank) {
                        if ($choiceRank == 1) $score += 15;
                        if ($choiceRank == 2) $score += 10;
                        if ($choiceRank == 3) $score += 5;
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
                    // Add this after the educational backgrounds scoring section
foreach ($instructor->courses as $taughtCourse) {
    Log::info("Calculating courses:", ['course_id' => $taughtCourse->pivot->course_id]);

    if ( $taughtCourse->pivot->course_id == $course->id) {

        // Add points based on number of semesters taught (range-based)
        $semestersTaught = $taughtCourse->pivot->number_of_semesters;
        $semesterPoints = $parameters['teaching_experience'] ?? 0;
        
        if ($semestersTaught > 10) {
            $score += $semesterPoints; // 100% of parameter value
        } elseif ($semestersTaught >= 5 && $semestersTaught <= 10) {
            $score += $semesterPoints * 0.5; // 50% of parameter value
        } elseif ($semestersTaught > 0) {
            $score += $semesterPoints * 0.25; // 25% of parameter value
        }
        
        // Add or subtract points based on whether it's recent (unchanged)
        if ($taughtCourse->pivot->is_recent) {
            $score += $parameters['recently_taught'] ?? 0;
        } 
    }
}
    
                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'year_semester_course_id' => $yearSemesterCourse->id,
                        'course_id' => $course->id,
                        'score' => $score,
                        'choice_rank' => $choiceRank ?? 999, // Higher number means no preference
                    ];
                }
            }
    
            // Sort ALL scores globally: Highest score first, if tied, prioritize by choice rank (lower rank is better)
            $sortedScores = collect($instructorScores)->sortBy([
                ['score', 'desc'],
                ['choice_rank', 'asc']
            ]);
            
            Log::info("Globally sorted scores:", ['sorted_scores' => $sortedScores->values()->all()]);
    
            $allResults = []; // Store all instructors' scores per course
    
            // Process all scores in the global sorted order
            foreach ($sortedScores as $instructorData) {
                $yearSemesterCourse = YearSemesterCourse::find($instructorData['year_semester_course_id']);
                $course = $yearSemesterCourse->course;
                $instructor = Instructor::find($instructorData['instructor_id']);
                $role = $instructor->role;
                $loadCapacity = $role->load;
                $currentLoad = $instructorLoad[$instructor->id] ?? 0;
    
                // Initialize course results array if not exists
                if (!isset($allResults[$yearSemesterCourse->id])) {
                    $allResults[$yearSemesterCourse->id] = [];
                }
    
                // Store result for transparency
                $allResults[$yearSemesterCourse->id][] = [
                    'Instructor' => $instructor->name,
                    'Score' => $instructorData['score'],
                    'Choice Rank' => $instructorData['choice_rank'],
                    'Course' => $course->name,
                ];
    
                $existingResult = Result::where('instructor_id', $instructor->id)
                    ->where('course_id', $yearSemesterCourse->id)
                    ->where('assignment_id', $assignment_id)
                    ->exists();
    
                if (!$existingResult) {
                    $is_assigned = 0;
    
                    // Check if the instructor has a higher preference for another course
                    $hasHigherPreference = false;
                    foreach ($yearSemesterCourses as $otherYearSemesterCourse) {
                        if ($otherYearSemesterCourse->id != $yearSemesterCourse->id) {
                            $otherChoice = $instructor->choices->where('course_id', $otherYearSemesterCourse->id)->where('assignment_id', $assignment_id)->first();
                            $otherChoiceRank = $otherChoice ? $otherChoice->rank : 999;
    
                            if ($otherChoiceRank < $instructorData['choice_rank']) {
                                $hasHigherPreference = true;
                                break;
                            }
                        }
                    }
    
                    // Get how many instructors we can assign to this course (based on occurrences in year_semester_courses)
                    $maxAssignments = $courseOccurrences[$course->id] ?? 1;
                    $currentAssignments = $assignedCourses[$course->id] ?? 0;
    
                    // Determine if we should assign this instructor
                    $shouldAssign = $currentLoad + $course->cp <= $loadCapacity && 
                                   !$hasHigherPreference && 
                                   $currentAssignments < $maxAssignments;
    
                    if ($shouldAssign) {
                        $is_assigned = 1;
                        $instructorLoad[$instructor->id] = $currentLoad + $course->cp;
                        $assignedCourses[$course->id] = ($assignedCourses[$course->id] ?? 0) + 1;
                    }
    
                    $result = Result::create([
                        'instructor_id' => $instructor->id,
                        'year_semester_course_id' => $yearSemesterCourse->id,
                        'course_id' => $course->id,
                        'assignment_id' => $assignment_id,
                        'point' => $instructorData['score'],
                        'is_assigned' => $is_assigned,
                    ]);
    
                    Log::info("Instructor to Course Assignment:", [
                        'instructor_id' => $instructor->id,
                        'instructor_name' => $instructor->name,
                        'year_semester_course_id' => $yearSemesterCourse->id,
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'score' => $instructorData['score'],
                        'choice_rank' => $instructorData['choice_rank'],
                        'is_assigned' => $is_assigned,
                        'current_assignments_for_course' => $assignedCourses[$course->id] ?? 0,
                        'max_assignments_for_course' => $maxAssignments,
                        'instructor_load' => $instructorLoad[$instructor->id] ?? 0,
                        'instructor_capacity' => $loadCapacity
                    ]);
    
                    $assignedResults[] = $result;
                }
            }
    
            DB::commit();
    
            Log::info("Course assignment process completed successfully.");
    
            return response()->json([
                'assigned_results' => $assignedResults,
                'all_scores' => $allResults, // For transparency
                'assignment_counts' => $assignedCourses // Show how many instructors were assigned per course
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during course assignment:", ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}