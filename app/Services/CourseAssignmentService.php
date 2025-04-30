<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\YearSemesterCourse;
use App\Models\Parameter;
use App\Models\Result;
use App\Models\Assignment;
use App\Models\Section;
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
            $instructors = Instructor::with('role', 'choices', 'professionalExperiences', 'researches', 'educationalBackgrounds', 'courses')
                ->where('is_available', 1)
                ->where('department_id', $department_id)
                ->get();
            Log::info("Fetched instructors:", ['count' => $instructors->count()]);
    
            // Fetch parameters and courses for this semester and department
            $parameters = Parameter::pluck('points', 'name');
            $yearSemesterCourses = YearSemesterCourse::with('course')
                ->where('semester_id', $semester_id)
                ->where('department_id', $department_id)
                ->get()
                ->filter(function($yearSemesterCourse) {
                    // Only include courses that have a department_id
                    return !is_null($yearSemesterCourse->course->department_id);
                });
            
            Log::info("Fetched parameters and filtered year_semester_courses:", [
                'parameters' => $parameters,
                'year_semester_courses_count' => $yearSemesterCourses->count(),
            ]);
            
            if ($yearSemesterCourses->isEmpty()) {
                DB::commit();
                return response()->json([
                    'message' => 'No courses with department assignments found for this semester and department',
                    'assigned_results' => [],
                    'all_scores' => [],
                    'assignment_counts' => [],
                    'section_counts' => []
                ]);
            }
            
            // Get section counts by year_id
            $yearIds = $yearSemesterCourses->pluck('year_id')->unique()->filter()->values();
            $sectionsCountByYear = Section::whereIn('year_id', $yearIds)
                ->groupBy('year_id')
                ->select('year_id', DB::raw('COUNT(*) as count'))
                ->get()
                ->keyBy('year_id')
                ->map(function($item) {
                    return $item->count;
                });
            
            Log::info("Sections count by year:", $sectionsCountByYear->toArray());
            
            $courseOccurrences = $yearSemesterCourses->groupBy('course_id')->map->count();

            $instructorScores = [];
            $assignedResults = [];
            $instructorLoad = [];
            $assignedCourses = [];
    
            // Calculate scores for each instructor-course pair
            foreach ($instructors as $instructor) {
                Log::info("Calculating scores for instructor:", ['instructor_id' => $instructor->id, 'name' => $instructor->name]);
    
                foreach ($yearSemesterCourses as $yearSemesterCourse) {
                    $course = $yearSemesterCourse->course;
                    
                    // Skip if course doesn't have a department (though we already filtered these out)
                    if (is_null($course->department_id)) {
                        continue;
                    }
                    
                    $score = 0;
    
                    $choice = $instructor->choices->where('year_semester_course_id', $yearSemesterCourse->id)
                                                  ->where('assignment_id', $assignment_id)
                                                  ->first();
                    $choiceRank = $choice ? $choice->rank : null;
    
                    // Choice rank scoring
                    if ($choiceRank) {
                        if ($choiceRank == 1) $score += 15;
                        elseif ($choiceRank == 2) $score += 10;
                        elseif ($choiceRank == 3) $score += 5;
                    }
    
                    // Professional experience scoring
                    foreach ($instructor->professionalExperiences as $experience) {
                        foreach ($course->fields as $field) {
                            if ($experience->field_id == $field->id) {
                                $score += $parameters['professional_experience'] ?? 0;
                            }
                        }
                    }
    
                    // Research scoring
                    foreach ($instructor->researches as $research) {
                        foreach ($course->fields as $field) {
                            if ($research->field_id == $field->id) {
                                $score += $parameters['research'] ?? 0;
                            }
                        }
                    }
    
                    // Educational background scoring
                    foreach ($instructor->educationalBackgrounds as $education) {
                        foreach ($course->fields as $field) {
                            if ($education->field_id == $field->id) {
                                $score += $parameters['educational_background'] ?? 0;
                            }
                        }
                    }
                    
                    // Teaching experience scoring
                    foreach ($instructor->courses as $taughtCourse) {
                        if ($taughtCourse->pivot->course_id == $course->id) {
                            $semestersTaught = $taughtCourse->pivot->number_of_semesters;
                            $semesterPoints = $parameters['teaching_experience'] ?? 0;
                            
                            if ($semestersTaught > 10) {
                                $score += $semesterPoints;
                            } elseif ($semestersTaught >= 5) {
                                $score += $semesterPoints * 0.5;
                            } elseif ($semestersTaught > 0) {
                                $score += $semesterPoints * 0.25;
                            }
                            
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
                        'choice_rank' => $choiceRank ?? 999,
                    ];
                }
            }
    
            // Sort scores globally
            $sortedScores = collect($instructorScores)->sortBy([
                ['score', 'desc'],
                ['choice_rank', 'asc']
            ]);
                 
            Log::info("Globally sorted scores:", ['sorted_scores' => $sortedScores->values()->all()]);
    
            $allResults = [];
            // Process assignments
            foreach ($sortedScores as $instructorData) {
                $yearSemesterCourse = YearSemesterCourse::find($instructorData['year_semester_course_id']);
                $course = $yearSemesterCourse->course;
                
                // Additional check to ensure we don't process courses without department
                if (is_null($course->department_id)) {
                    continue;
                }
                
                $instructor = Instructor::find($instructorData['instructor_id']);
                $role = $instructor->role;
                $loadCapacity = $role->load;
                $currentLoad = $instructorLoad[$instructor->id] ?? 0;
    
                // Initialize course results if not exists
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
    
                // Check if already assigned
                $existingResult = Result::where('instructor_id', $instructor->id)
                    ->where('course_id', $yearSemesterCourse->id)
                    ->where('assignment_id', $assignment_id)
                    ->exists();
    
                if (!$existingResult) {
                    $is_assigned = 0;
    
                    // Check for higher preferences
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
    
                    // Calculate max assignments (course occurrences + sections count)
                    $baseAssignments = $courseOccurrences[$course->id] ?? 1;
                    $sectionsCount = $sectionsCountByYear->get($yearSemesterCourse->year_id, 0);
                    $maxAssignments = $baseAssignments + $sectionsCount;
                    
                    $currentAssignments = $assignedCourses[$course->id] ?? 0;
    
                    // Determine if we should assign
                    $shouldAssign = $currentLoad + $course->cp <= $loadCapacity && 
                                   !$hasHigherPreference && 
                                   $currentAssignments < $maxAssignments;
    
                    if ($shouldAssign) {
                        $is_assigned = 1;
                        $instructorLoad[$instructor->id] = $currentLoad + $course->cp;
                        $assignedCourses[$course->id] = ($assignedCourses[$course->id] ?? 0) + 1;
                    }
    
                    // Create result record
                    $result = Result::create([
                        'instructor_id' => $instructor->id,
                        'year_semester_course_id' => $yearSemesterCourse->id,
                        'course_id' => $course->id,
                        'assignment_id' => $assignment_id,
                        'point' => $instructorData['score'],
                        'is_assigned' => $is_assigned,
                    ]);
    
                    Log::info("Assignment decision:", [
                        'course' => $course->name,
                        'instructor' => $instructor->name,
                        'base_assignments' => $baseAssignments,
                        'sections_count' => $sectionsCount,
                        'max_assignments' => $maxAssignments,
                        'current_assignments' => $currentAssignments,
                        'is_assigned' => $is_assigned
                    ]);
    
                    $assignedResults[] = $result;
                }
            }
    
            DB::commit();
    
            return response()->json([
                'assigned_results' => $assignedResults,
                'all_scores' => $allResults,
                'assignment_counts' => $assignedCourses,
                'section_counts' => $sectionsCountByYear
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during course assignment:", ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}