<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Course;
use App\Models\Parameter;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $assignedCourses = []; // Track assigned courses
    
            // Calculate scores for each instructor-course pair
            foreach ($instructors as $instructor) {
                foreach ($courses as $course) {
                    $score = 0;
    
                    $choice = $instructor->choices->where('course_id', $course->id)->where('assignment_id', $assignment_id)->first();
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
    
                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'score' => $score,
                        'choice_rank' => $choiceRank ?? 999, // Higher number means no preference
                    ];
                }
            }
    
            // Sort the scores: Highest score first, if tied, prioritize by choice rank (lower rank is better)
            $sortedScores = collect($instructorScores)->sortBy([
                ['score', 'desc'],
                ['choice_rank', 'asc']
            ])->groupBy('course_id');
    
            $allResults = []; // Store all instructors' scores per course
    
            foreach ($courses as $course) {
                $isCourseAssigned = false;
                $courseResults = []; // Store all instructor scores for this course
    
                if (isset($sortedScores[$course->id])) {
                    foreach ($sortedScores[$course->id] as $instructorData) {
                        $instructor = Instructor::find($instructorData['instructor_id']);
                        $role = $instructor->role;
                        $loadCapacity = $role->load;
                        $currentLoad = $instructorLoad[$instructor->id] ?? 0;
    
                        // Store result for transparency
                        $courseResults[] = [
                            'Instructor' => $instructor->name,
                            'Score' => $instructorData['score'],
                            'Choice Rank' => $instructorData['choice_rank'],
                            'Course' => $course->name,
                        ];
    
                        $existingResult = Result::where('instructor_id', $instructor->id)
                            ->where('course_id', $course->id)
                            ->where('assignment_id', $assignment_id)
                            ->exists();
    
                        if (!$existingResult) {
                            $is_assigned = 0;
    
                            // Check if the instructor has a higher preference for another course
                            $hasHigherPreference = false;
                            foreach ($courses as $otherCourse) {
                                if ($otherCourse->id != $course->id && !in_array($otherCourse->id, $assignedCourses)) {
                                    $otherChoice = $instructor->choices->where('course_id', $otherCourse->id)->where('assignment_id', $assignment_id)->first();
                                    $otherChoiceRank = $otherChoice ? $otherChoice->rank : 999;
    
                                    if ($otherChoiceRank < $instructorData['choice_rank']) {
                                        $hasHigherPreference = true;
                                        break;
                                    }
                                }
                            }
    
                            // Assign only if within load capacity, not already assigned, and no higher preference
                            if ($currentLoad + $course->cp <= $loadCapacity && !$isCourseAssigned && !$hasHigherPreference) {
                                $is_assigned = 1;
                                $instructorLoad[$instructor->id] = $currentLoad + $course->cp;
                                $isCourseAssigned = true;
                                $assignedCourses[] = $course->id; // Mark this course as assigned
                            }
    
                            $result = Result::create([
                                'instructor_id' => $instructor->id,
                                'course_id' => $course->id,
                                'assignment_id' => $assignment_id,
                                'point' => $instructorData['score'],
                                'is_assigned' => $is_assigned,
                            ]);
                            Log::info("Assigned Instructor {$instructor->name} to Course {$course->name}", [
                                'Instructor ID' => $instructor->id,
                                'Course ID' => $course->id,
                                'Score' => $instructorData['score'],
                                'Choice Rank' => $instructorData['choice_rank'],
                                'Final Assigned' => $is_assigned
                            ]);
                            $assignedResults[] = $result;
                        }
                    }
                }
    
                $allResults[$course->id] = $courseResults; // Store all instructor scores per course
            }
    
            DB::commit();
    
            return response()->json([
                'assigned_results' => $assignedResults,
                'all_scores' => $allResults // For transparency
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}
