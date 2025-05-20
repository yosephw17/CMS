<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\YearSemesterCourse;
use App\Models\Parameter;
use App\Models\Result;
use App\Models\Assignment;
use App\Models\Section;
use App\Models\Stream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseAssignmentService
{
    public function assignCourses($assignment_id)
    {
        DB::beginTransaction();
    
        try {
            Log::info("Starting course assignment process for assignment ID: {$assignment_id}");
            
            // Fetch the assignment to get semester_id, department_id, and year
            $assignment = Assignment::findOrFail($assignment_id);
            $semester_id = $assignment->semester_id;
            $department_id = $assignment->department_id;
            $year = $assignment->year;
            
            Log::info("Assignment details:", [
                'year' => $year,
                'semester_id' => $semester_id,
                'department_id' => $department_id,
            ]);

            // Fetch streams to validate course assignments
            $streams = Stream::where('department_id', $department_id)->get()->keyBy('id');
            Log::info("Fetched streams:", ['count' => $streams->count()]);

            // Fetch instructors with their related data (only from the same department)
            $instructors = Instructor::with('role', 'choices', 'professionalExperiences', 'researches', 'educationalBackgrounds', 'courses')
                ->where('is_available', 1)
                ->where('department_id', $department_id)
                ->get();
            Log::info("Fetched instructors:", ['count' => $instructors->count()]);
    
            // Fetch parameters and courses for this semester and department
            $parameters = Parameter::pluck('points', 'name');
            $yearSemesterCourses = YearSemesterCourse::with([
                    'course',
                    'year' => function($query) use ($department_id) {
                        $query->where('department_id', $department_id);
                    },
                    'stream'
                ])
                ->where('semester_id', $semester_id)
                ->where('department_id', $department_id)
                ->get()
                ->filter(function($yearSemesterCourse) use ($streams, $year, $semester_id) {
                    // Validate stream applicability
                    if ($yearSemesterCourse->stream_id) {
                        $stream = $streams->get($yearSemesterCourse->stream_id);
                        if (!$stream) {
                            Log::warning('Course with invalid stream_id', [
                                'course_id' => $yearSemesterCourse->course_id,
                                'stream_id' => $yearSemesterCourse->stream_id,
                            ]);
                            return false;
                        }
                        $yearOrder = DB::table('years')->where('id', $yearSemesterCourse->year_id)->value('id');
                        $streamYearOrder = $stream->year_id;
                        if ($yearOrder < $streamYearOrder ||
                            ($yearOrder == $streamYearOrder && $semester_id < $stream->semester_id)) {
                            Log::warning('Stream not active for this year/semester', [
                                'course_id' => $yearSemesterCourse->course_id,
                                'stream_id' => $yearSemesterCourse->stream_id,
                                'stream_name' => $stream->name,
                                'year_id' => $yearSemesterCourse->year_id,
                                'semester_id' => $semester_id,
                            ]);
                            return false;
                        }
                    }
                    // Only include courses that have matching department_ids
                    return !is_null($yearSemesterCourse->course) && 
                           !is_null($yearSemesterCourse->year) &&
                           $yearSemesterCourse->course->department_id == $yearSemesterCourse->year->department_id;
                });
            
            Log::info("Fetched parameters and filtered year_semester_courses:", [
                'parameters' => $parameters,
                'year_semester_courses_count' => $yearSemesterCourses->count(),
            ]);
            
            if ($yearSemesterCourses->isEmpty()) {
                DB::commit();
                return response()->json([
                    'message' => 'No courses with matching department assignments found for this semester and department',
                    'assigned_results' => [],
                    'all_scores' => [],
                    'assignment_counts' => [],
                    'section_counts' => []
                ]);
            }
            
            // Get section counts by year_id
            $yearIds = $yearSemesterCourses->pluck('year_id')->unique()->filter()->values();
            $sectionsCountByYear = Section::whereIn('year_id', $yearIds)
                ->where('department_id', $department_id)
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
                Log::info("Calculating scores for instructor:", [
                    'instructor_id' => $instructor->id,
                    'name' => $instructor->name,
                ]);
    
                foreach ($yearSemesterCourses as $yearSemesterCourse) {
                    $course = $yearSemesterCourse->course;
                    $year = $yearSemesterCourse->year;
                    
                    // Skip if course or year doesn't have matching department
                    if (is_null($course->department_id)) {
                        continue;
                    }
                    $score = 0;
    
                    $choice = $instructor->choices->where('course_id', $course->id)
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
    
                    // Get previous instructor for this course
                    $previousInstructorId = $instructor->courses()
                        ->where('course_id', $course->id)
                        ->wherePivot('is_recent', true)
                        ->first()
                        ?->pivot
                        ->instructor_id;
    
                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'score' => $score,
                        'choice_rank' => $choiceRank ?? 999,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'previous_instructor_id' => $previousInstructorId,
                    ];
                }
            }
    
            // Sort scores globally
            $sortedScores = collect($instructorScores)->sortBy([
                ['score', 'desc'],
                ['choice_rank', 'asc']
            ]);
                 
            Log::info("Globally sorted scores:", $sortedScores->values()->all());
    
            $allResults = [];
            // Process assignments
            foreach ($sortedScores as $instructorData) {
                $yearSemesterCourse = YearSemesterCourse::where('course_id', $instructorData['course_id'])
                    ->where('semester_id', $semester_id)
                    ->where('department_id', $department_id)
                    ->where('stream_id', $instructorData['stream_id'])
                    ->first();
                
                if (!$yearSemesterCourse) {
                    Log::warning("No matching year_semester_course found for course:", [
                        'course_id' => $instructorData['course_id'],
                        'stream_id' => $instructorData['stream_id'],
                    ]);
                    continue;
                }
                
                $course = $yearSemesterCourse->course;
                $year = $yearSemesterCourse->year;
                
                // Additional check to ensure we don't process courses without matching departments
                if (is_null($course->department_id) || is_null($year->department_id) || 
                    $course->department_id != $year->department_id) {
                    continue;
                }
                
                $instructor = Instructor::find($instructorData['instructor_id']);
                $role = $instructor->role;
                $loadCapacity = $role->load;
                $currentLoad = $instructorLoad[$instructor->id] ?? 0;
    
                // Initialize course results if not exists
                if (!isset($allResults[$instructorData['course_id']])) {
                    $allResults[$instructorData['course_id']] = [];
                }
    
                // Store result for transparency
                $allResults[$instructorData['course_id']][] = [
                    'Instructor' => $instructor->name,
                    'Score' => $instructorData['score'],
                    'Choice Rank' => $instructorData['choice_rank'],
                    'Course' => $course->name,
                    'Year' => $year->name,
                    'Stream' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                    'Previous Instructor ID' => $instructorData['previous_instructor_id'],
                ];
    
                // Check if already assigned
                $existingResult = Result::where('instructor_id', $instructor->id)
                    ->where('course_id', $instructorData['course_id'])
                    ->where('assignment_id', $assignment_id)
                    ->exists();
    
                if (!$existingResult) {
                    $is_assigned = 0;
                    $reason = '';
    
                    // Check for higher preferences
                    $hasHigherPreference = false;
                    foreach ($yearSemesterCourses as $otherYearSemesterCourse) {
                        if ($otherYearSemesterCourse->course_id != $instructorData['course_id']) {
                            $otherChoice = $instructor->choices->where('course_id', $otherYearSemesterCourse->course_id)
                                ->where('assignment_id', $assignment_id)
                                ->first();
                            $otherChoiceRank = $otherChoice ? $otherChoice->rank : 999;
    
                            if ($otherChoiceRank < $instructorData['choice_rank']) {
                                $hasHigherPreference = true;
                                $reason = "Not assigned: Instructor has higher preference for another course.";
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
                        $reason = "Assigned: High score ({$instructorData['score']}) and within load capacity.";
                        if ($instructorData['previous_instructor_id'] == $instructor->id) {
                            $reason .= " Previously taught by this instructor.";
                        }
                    } elseif (!$hasHigherPreference && $currentLoad + $course->cp > $loadCapacity) {
                        $reason = "Not assigned: Exceeds instructor load capacity ({$currentLoad} + {$course->cp} > {$loadCapacity}).";
                    } elseif (!$hasHigherPreference && $currentAssignments >= $maxAssignments) {
                        $reason = "Not assigned: Maximum assignments reached for course (current: {$currentAssignments}, max: {$maxAssignments}).";
                    }
    
                    // Create result record
                    $result = Result::create([
                        'instructor_id' => $instructor->id,
                        'course_id' => $instructorData['course_id'],
                        'assignment_id' => $assignment_id,
                        'point' => $instructorData['score'],
                        'is_assigned' => $is_assigned,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                    ]);
    
                    Log::info("Assignment decision:", [
                        'course' => $course->name,
                        'year' => $year->name,
                        'instructor' => $instructor->name,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'stream_name' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                        'base_assignments' => $baseAssignments,
                        'sections_count' => $sectionsCount,
                        'max_assignments' => $maxAssignments,
                        'current_assignments' => $currentAssignments,
                        'is_assigned' => $is_assigned,
                        'score' => $instructorData['score'],
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                    ]);
    
                    $assignedResults[] = $result;
                }
            }
    
            DB::commit();
    
            return response()->json([
                'assigned_results' => $assignedResults,
                'all_scores' => $allResults,
                'assignment_counts' => $assignedCourses,
                'section_counts' => $sectionsCountByYear,
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during course assignment:", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
?>