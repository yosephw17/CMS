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

            // Fetch the assignment
            $assignment = Assignment::findOrFail($assignment_id);
            $semester_id = $assignment->semester_id;
            $department_id = $assignment->department_id;
            $year = $assignment->year;

            Log::info("Assignment details:", [
                'year' => $year,
                'semester_id' => $semester_id,
                'department_id' => $department_id,
            ]);

            // Fetch streams
            $streams = Stream::where('department_id', $department_id)->get()->keyBy('id');
            Log::info("Fetched streams:", ['count' => $streams->count()]);

            // Fetch instructors
            $instructors = Instructor::with('role', 'choices', 'professionalExperiences', 'researches', 'educationalBackgrounds', 'courses')
                ->where('is_available', 1)
                ->where('department_id', $department_id)
                ->get();
            $labAssistants = $instructors->filter(function ($instructor) {
                return $instructor->role->name === 'lab_assistant';
            });
            $lectureInstructors = $instructors->filter(function ($instructor) {
                return $instructor->role->name !== 'lab_assistant';
            });
            Log::info("Fetched instructors:", [
                'total_count' => $instructors->count(),
                'lab_assistants_count' => $labAssistants->count(),
                'lecture_instructors_count' => $lectureInstructors->count(),
            ]);

            // Fetch parameters and courses
            $parameters = Parameter::pluck('points', 'name');
            $yearSemesterCourses = YearSemesterCourse::with([
                    'course',
                    'year' => function ($query) use ($department_id) {
                        $query->where('department_id', $department_id);
                    },
                    'stream'
                ])
                ->where('semester_id', $semester_id)
                ->where('department_id', $department_id)
                ->get()
                ->filter(function ($yearSemesterCourse) use ($streams, $year, $semester_id) {
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

            // Log course types
            foreach ($yearSemesterCourses as $yearSemesterCourse) {
                $course = $yearSemesterCourse->course;
                Log::info("Course details:", [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'cp' => $course->cp,
                    'lecture_cp' => $course->lecture_cp,
                    'lab_cp' => $course->lab_cp,
                    'tut_cp' => $course->tut_cp,
                    'assignment_types' => $course->lab_cp > 0 ? ['lecture', 'lab', 'lab_assistant'] : ['lecture'],
                    'lecture_credit_used' => $course->lab_cp > 0 ? 'lecture_cp' : 'cp',
                ]);
            }

            // Get sections by year_id
            $yearIds = $yearSemesterCourses->pluck('year_id')->unique()->filter()->values();
            $sectionsByYear = Section::whereIn('year_id', $yearIds)
                ->where('department_id', $department_id)
                ->get()
                ->groupBy('year_id');
            $sectionsCountByYear = $sectionsByYear->map->count();

            Log::info("Sections by year:", [
                'year_ids' => $yearIds->toArray(),
                'sections_by_year' => $sectionsByYear->map(function ($sections) {
                    return $sections->map(function ($section) {
                        return ['id' => $section->id, 'year_id' => $section->year_id];
                    })->toArray();
                })->toArray(),
                'sections_count_by_year' => $sectionsCountByYear->toArray(),
            ]);

            $courseOccurrences = $yearSemesterCourses->groupBy('course_id')->map->count();
            $instructorScores = [];
            $assignedResults = [];
            $instructorLoad = [];
            $assignedCourses = [];
            $assignedLabLectures = [];
            $assignedLabAssistants = [];
            $lectureAssignments = [];
            $allResults = [];

            // Helper function to calculate instructor score
            $calculateScore = function ($instructor, $course, $assignment_id, $parameters) {
                $score = 0;
                $priorityBoost = 0;

                $choice = $instructor->choices->where('course_id', $course->id)
                                            ->where('assignment_id', $assignment_id)
                                            ->first();
                $choiceRank = $choice ? $choice->rank : null;

                if ($choiceRank) {
                    if ($choiceRank == 1) $score += 15;
                    elseif ($choiceRank == 2) $score += 10;
                    elseif ($choiceRank == 3) $score += 5;
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

                $previousInstructorId = $instructor->courses()
                    ->where('course_id', $course->id)
                    ->wherePivot('is_recent', true)
                    ->first()
                    ?->pivot
                    ->instructor_id;

                return [
                    'score' => $score,
                    'priority_boost' => $priorityBoost,
                    'choice_rank' => $choiceRank ?? 999,
                    'previous_instructor_id' => $previousInstructorId,
                ];
            };

            // Helper function to get any available section
            $getAnySection = function ($year_id) use ($sectionsByYear) {
                $sections = $sectionsByYear->get($year_id, collect([]));
                $section_id = $sections->isNotEmpty() ? $sections->first()->id : null;
                Log::info("getAnySection called:", [
                    'year_id' => $year_id,
                    'sections_count' => $sections->count(),
                    'section_id' => $section_id,
                    'available_sections' => $sections->pluck('id')->toArray(),
                ]);
                return $section_id;
            };

            // Phase 1: Assign Lectures
            foreach ($lectureInstructors as $instructor) {
                foreach ($yearSemesterCourses as $yearSemesterCourse) {
                    $course = $yearSemesterCourse->course;
                    $year = $yearSemesterCourse->year;

                    if (is_null($course->department_id)) {
                        continue;
                    }

                    $cp = $course->lab_cp > 0 ? ($course->lecture_cp + $course->tut_cp) : $course->cp;
                    if ($cp <= 0) {
                        continue;
                    }

                    $scoreData = $calculateScore($instructor, $course, $assignment_id, $parameters);
                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'score' => $scoreData['score'],
                        'priority_boost' => $scoreData['priority_boost'],
                        'choice_rank' => $scoreData['choice_rank'],
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'previous_instructor_id' => $scoreData['previous_instructor_id'],
                        'assignment_type' => 'lecture',
                        'credit_type' => $course->lab_cp > 0 ? 'lecture_cp' : 'cp',
                    ];
                }
            }

            $sortedLectureScores = collect($instructorScores)->sortBy([
                ['score', 'desc'],
                ['priority_boost', 'desc'],
                ['choice_rank', 'asc']
            ]);

            foreach ($sortedLectureScores as $instructorData) {
                $yearSemesterCourse = YearSemesterCourse::where('course_id', $instructorData['course_id'])
                    ->where('semester_id', $semester_id)
                    ->where('department_id', $department_id)
                    ->where('stream_id', $instructorData['stream_id'])
                    ->first();

                if (!$yearSemesterCourse) {
                    Log::warning("No matching year_semester_course found for course:", [
                        'course_id' => $instructorData['course_id'],
                        'stream_id' => $instructorData['stream_id'],
                        'assignment_type' => $instructorData['assignment_type'],
                    ]);
                    continue;
                }

                $course = $yearSemesterCourse->course;
                $year = $yearSemesterCourse->year;

                if (is_null($course->department_id) || is_null($year->department_id) ||
                    $course->department_id != $year->department_id) {
                    continue;
                }

                $instructor = Instructor::find($instructorData['instructor_id']);
                $loadCapacity = $instructor->role->load;
                $currentLoad = $instructorLoad[$instructor->id] ?? 0;
                $cp = $course->lab_cp > 0 ? ($course->lecture_cp + $course->tut_cp) : $course->cp;

                if (!isset($allResults[$instructorData['course_id']]['lecture'])) {
                    $allResults[$instructorData['course_id']]['lecture'] = [];
                }

                $allResults[$instructorData['course_id']]['lecture'][] = [
                    'Instructor' => $instructor->name,
                    'Score' => $instructorData['score'],
                    'Priority Boost' => $instructorData['priority_boost'],
                    'Choice Rank' => $instructorData['choice_rank'],
                    'Course' => $course->name,
                    'Year' => $year->name,
                    'Stream' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                    'Previous Instructor ID' => $instructorData['previous_instructor_id'],
                    'Assignment Type' => 'lecture',
                    'Credit Type' => $instructorData['credit_type'],
                ];

                $existingResult = Result::where('instructor_id', $instructor->id)
                    ->where('course_id', $instructorData['course_id'])
                    ->where('assignment_id', $assignment_id)
                    ->where('type', 'lecture')
                    ->exists();

                if (!$existingResult) {
                    $is_assigned = 0;
                    $reason = '';
                    $section_id = null;

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

                    $baseAssignments = $courseOccurrences[$course->id] ?? 1;
                    $sectionsCount = $sectionsCountByYear->get($yearSemesterCourse->year_id, 0);
                    $maxAssignments = $baseAssignments + ($sectionsCount > 1 ? $sectionsCount : 0);
                    $currentAssignments = $assignedCourses[$course->id] ?? 0;

                    Log::info("Lecture assignment pre-check:", [
                        'course_id' => $course->id,
                        'year_id' => $yearSemesterCourse->year_id,
                        'instructor_id' => $instructor->id,
                        'current_load' => $currentLoad,
                        'cp' => $cp,
                        'load_capacity' => $loadCapacity,
                        'has_higher_preference' => $hasHigherPreference,
                        'current_assignments' => $currentAssignments,
                        'max_assignments' => $maxAssignments,
                        'sections_count' => $sectionsCount,
                    ]);

                    if ($currentLoad + $cp <= $loadCapacity && !$hasHigherPreference && $currentAssignments < $maxAssignments) {
                        $is_assigned = 1;
                        $section_id = $getAnySection($yearSemesterCourse->year_id);
                        
                        $instructorLoad[$instructor->id] = $currentLoad + $cp;
                        $assignedCourses[$course->id] = ($assignedCourses[$course->id] ?? 0) + 1;
                        $lectureAssignments[$course->id][] = $instructor->id;
                        $reason = "Assigned: High score ({$instructorData['score']}) and within load capacity (added {$cp} {$instructorData['credit_type']} credits).";
                      
                    } elseif (!$hasHigherPreference && $currentLoad + $cp > $loadCapacity) {
                        $reason = "Not assigned: Exceeds instructor load capacity ({$currentLoad} + {$cp} > {$loadCapacity}).";
                    } elseif (!$hasHigherPreference && $currentAssignments >= $maxAssignments) {
                        $reason = "Not assigned: Maximum assignments reached for course (current: {$currentAssignments}, max: {$maxAssignments}).";
                    }

                    Log::info("Lecture assignment post-check:", [
                        'course_id' => $course->id,
                        'year_id' => $yearSemesterCourse->year_id,
                        'instructor_id' => $instructor->id,
                        'is_assigned' => $is_assigned,
                        'section_id' => $section_id,
                        'reason' => $reason,
                    ]);

                    $result = Result::create([
                        'instructor_id' => $instructor->id,
                        'course_id' => $instructorData['course_id'],
                        'assignment_id' => $assignment_id,
                        'point' => $instructorData['score'],
                        'is_assigned' => $is_assigned,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'section_id' => $is_assigned ? $section_id : null,
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                        'type' => 'lecture',
                    ]);

                    Log::info("Lecture assignment decision:", [
                        'course' => $course->name,
                        'year' => $year->name,
                        'instructor' => $instructor->name,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'stream_name' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                        'section_id' => $section_id,
                        'base_assignments' => $baseAssignments,
                        'sections_count' => $sectionsCount,
                        'max_assignments' => $maxAssignments,
                        'current_assignments' => $currentAssignments,
                        'is_assigned' => $is_assigned,
                        'score' => $instructorData['score'],
                        'priority_boost' => $instructorData['priority_boost'],
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                        'credits_added' => $is_assigned ? $cp : 0,
                        'credit_type' => $instructorData['credit_type'],
                        'current_load' => $currentLoad,
                        'load_capacity' => $loadCapacity,
                    ]);

                    $assignedResults[] = $result;
                }
            }

            // Phase 2: Assign Lab Lectures to Lecture Instructors
            Log::info("Phase 2: Assigning lab lectures (type='lab')");
            $instructorScores = [];
            foreach ($lectureInstructors as $instructor) {
                foreach ($yearSemesterCourses as $yearSemesterCourse) {
                    $course = $yearSemesterCourse->course;
                    $year = $yearSemesterCourse->year;

                    if (is_null($course->department_id) || $course->lab_cp <= 0) {
                        continue;
                    }

                    $scoreData = $calculateScore($instructor, $course, $assignment_id, $parameters);
                    $priorityBoost = $scoreData['priority_boost'];
                    if (isset($lectureAssignments[$course->id]) && in_array($instructor->id, $lectureAssignments[$course->id])) {
                        $priorityBoost += 10;
                    }
                    if (!isset($instructorLoad[$instructor->id]) || $instructorLoad[$instructor->id] == 0) {
                        $priorityBoost += 5;
                    }

                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'score' => $scoreData['score'],
                        'priority_boost' => $priorityBoost,
                        'choice_rank' => $scoreData['choice_rank'],
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'previous_instructor_id' => $scoreData['previous_instructor_id'],
                        'assignment_type' => 'lab',
                    ];
                }
            }

            $sortedLabScores = collect($instructorScores)->sortBy([
                ['priority_boost', 'desc'],
                ['score', 'desc'],
                ['choice_rank', 'asc']
            ]);

            foreach ($sortedLabScores as $instructorData) {
                $yearSemesterCourse = YearSemesterCourse::where('course_id', $instructorData['course_id'])
                    ->where('semester_id', $semester_id)
                    ->where('department_id', $department_id)
                    ->where('stream_id', $instructorData['stream_id'])
                    ->first();

                if (!$yearSemesterCourse) {
                    Log::warning("No matching year_semester_course found for course:", [
                        'course_id' => $instructorData['course_id'],
                        'stream_id' => $instructorData['stream_id'],
                        'assignment_type' => $instructorData['assignment_type'],
                    ]);
                    continue;
                }

                $course = $yearSemesterCourse->course;
                $year = $yearSemesterCourse->year;

                if (is_null($course->department_id) || is_null($year->department_id) ||
                    $course->department_id != $year->department_id) {
                    continue;
                }

                $instructor = Instructor::find($instructorData['instructor_id']);
                $loadCapacity = $instructor->role->load;
                $currentLoad = $instructorLoad[$instructor->id] ?? 0;
                $cp = $course->lab_cp;

                if (!isset($allResults[$instructorData['course_id']]['lab'])) {
                    $allResults[$instructorData['course_id']]['lab'] = [];
                }

                $allResults[$instructorData['course_id']]['lab'][] = [
                    'Instructor' => $instructor->name,
                    'Score' => $instructorData['score'],
                    'Priority Boost' => $instructorData['priority_boost'],
                    'Choice Rank' => $instructorData['choice_rank'],
                    'Course' => $course->name,
                    'Year' => $year->name,
                    'Stream' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                    'Previous Instructor ID' => $instructorData['previous_instructor_id'],
                    'Assignment Type' => 'lab',
                ];

                $existingResult = Result::where('instructor_id', $instructor->id)
                    ->where('course_id', $instructorData['course_id'])
                    ->where('assignment_id', $assignment_id)
                    ->where('type', 'lab')
                    ->exists();

                if (!$existingResult) {
                    $is_assigned = 0;
                    $reason = '';
                    $section_id = null;

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

                    $baseAssignments = $courseOccurrences[$course->id] ?? 1;
                    $sectionsCount = $sectionsCountByYear->get($yearSemesterCourse->year_id, 0);
                    $maxAssignments = $baseAssignments + ($sectionsCount > 1 ? $sectionsCount : 0);
                    $currentAssignments = $assignedLabLectures[$course->id] ?? 0;

                    Log::info("Lab lecture assignment pre-check:", [
                        'course_id' => $course->id,
                        'year_id' => $yearSemesterCourse->year_id,
                        'instructor_id' => $instructor->id,
                        'current_load' => $currentLoad,
                        'cp' => $cp,
                        'load_capacity' => $loadCapacity,
                        'has_higher_preference' => $hasHigherPreference,
                        'current_assignments' => $currentAssignments,
                        'max_assignments' => $maxAssignments,
                        'sections_count' => $sectionsCount,
                    ]);

                    if ($currentLoad + $cp <= $loadCapacity && !$hasHigherPreference && $currentAssignments < $maxAssignments) {
                        $is_assigned = 1;
                        $section_id = $getAnySection($yearSemesterCourse->year_id);
                        $instructorLoad[$instructor->id] = $currentLoad + $cp;
                        $assignedLabLectures[$course->id] = ($assignedLabLectures[$course->id] ?? 0) + 1;
                        $reason = "Assigned: High score ({$instructorData['score']}) and within load capacity (added {$cp} lab credits).";
                        if (isset($lectureAssignments[$course->id]) && in_array($instructor->id, $lectureAssignments[$course->id])) {
                            $reason .= " Preferred as lecture instructor for this course.";
                        } elseif (!isset($instructorLoad[$instructor->id]) || $instructorLoad[$instructor->id] == 0) {
                            $reason .= " Preferred due to no prior assignments.";
                        }
                        if ($instructorData['previous_instructor_id'] == $instructor->id) {
                            $reason .= " Previously taught by this instructor.";
                        }
                        if ($section_id) {
                            $reason .= " Assigned to section ID: {$section_id}.";
                        } else {
                            $reason .= " No section assigned (no sections available).";
                        }
                    } elseif (!$hasHigherPreference && $currentLoad + $cp > $loadCapacity) {
                        $reason = "Not assigned: Exceeds instructor load capacity ({$currentLoad} + {$cp} > {$loadCapacity}).";
                    } elseif (!$hasHigherPreference && $currentAssignments >= $maxAssignments) {
                        $reason = "Not assigned: Maximum assignments reached for course (current: {$currentAssignments}, max: {$maxAssignments}).";
                    }

                    Log::info("Lab lecture assignment post-check:", [
                        'course_id' => $course->id,
                        'year_id' => $yearSemesterCourse->year_id,
                        'instructor_id' => $instructor->id,
                        'is_assigned' => $is_assigned,
                        'section_id' => $section_id,
                        'reason' => $reason,
                    ]);

                    $result = Result::create([
                        'instructor_id' => $instructor->id,
                        'course_id' => $instructorData['course_id'],
                        'assignment_id' => $assignment_id,
                        'point' => $instructorData['score'],
                        'is_assigned' => $is_assigned,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'section_id' => $is_assigned ? $section_id : null,
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                        'type' => 'lab',
                    ]);

                    Log::info("Lab lecture assignment decision:", [
                        'course' => $course->name,
                        'year' => $year->name,
                        'instructor' => $instructor->name,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'stream_name' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                        'section_id' => $section_id,
                        'base_assignments' => $baseAssignments,
                        'sections_count' => $sectionsCount,
                        'max_assignments' => $maxAssignments,
                        'current_assignments' => $currentAssignments,
                        'is_assigned' => $is_assigned,
                        'score' => $instructorData['score'],
                        'priority_boost' => $instructorData['priority_boost'],
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                        'credits_added' => $is_assigned ? $cp : 0,
                        'credit_type' => 'lab_cp',
                        'current_load' => $currentLoad,
                        'load_capacity' => $loadCapacity,
                    ]);

                    $assignedResults[] = $result;
                }
            }

            // Phase 3: Assign Lab Assistants
            Log::info("Phase 3: Assigning lab assistants (type='lab_assistant')");
            $instructorScores = [];
            foreach ($labAssistants as $instructor) {
                foreach ($yearSemesterCourses as $yearSemesterCourse) {
                    $course = $yearSemesterCourse->course;
                    $year = $yearSemesterCourse->year;

                    if (is_null($course->department_id) || $course->lab_cp <= 0) {
                        continue;
                    }

                    $scoreData = $calculateScore($instructor, $course, $assignment_id, $parameters);
                    $instructorScores[] = [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'score' => $scoreData['score'],
                        'priority_boost' => $scoreData['priority_boost'],
                        'choice_rank' => $scoreData['choice_rank'],
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'previous_instructor_id' => $scoreData['previous_instructor_id'],
                        'assignment_type' => 'lab_assistant',
                    ];
                }
            }

            $sortedAssistantScores = collect($instructorScores)->sortBy([
                ['score', 'desc'],
                ['priority_boost', 'desc'],
                ['choice_rank', 'asc']
            ]);

            foreach ($sortedAssistantScores as $instructorData) {
                $yearSemesterCourse = YearSemesterCourse::where('course_id', $instructorData['course_id'])
                    ->where('semester_id', $semester_id)
                    ->where('department_id', $department_id)
                    ->where('stream_id', $instructorData['stream_id'])
                    ->first();

                if (!$yearSemesterCourse) {
                    Log::warning("No matching year_semester_course found for course:", [
                        'course_id' => $instructorData['course_id'],
                        'stream_id' => $instructorData['stream_id'],
                        'assignment_type' => $instructorData['assignment_type'],
                    ]);
                    continue;
                }

                $course = $yearSemesterCourse->course;
                $year = $yearSemesterCourse->year;

                if (is_null($course->department_id) || is_null($year->department_id) ||
                    $course->department_id != $year->department_id) {
                    continue;
                }

                $instructor = Instructor::find($instructorData['instructor_id']);
                $loadCapacity = $instructor->role->load;
                $currentLoad = $instructorLoad[$instructor->id] ?? 0;
                $cp = $course->lab_cp;

                if (!isset($allResults[$instructorData['course_id']]['lab_assistant'])) {
                    $allResults[$instructorData['course_id']]['lab_assistant'] = [];
                }

                $allResults[$instructorData['course_id']]['lab_assistant'][] = [
                    'Instructor' => $instructor->name,
                    'Score' => $instructorData['score'],
                    'Priority Boost' => $instructorData['priority_boost'],
                    'Choice Rank' => $instructorData['choice_rank'],
                    'Course' => $course->name,
                    'Year' => $year->name,
                    'Stream' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                    'Previous Instructor ID' => $instructorData['previous_instructor_id'],
                    'Assignment Type' => 'lab_assistant',
                ];

                $existingResult = Result::where('instructor_id', $instructor->id)
                    ->where('course_id', $instructorData['course_id'])
                    ->where('assignment_id', $assignment_id)
                    ->where('type', 'lab_assistant')
                    ->exists();

                if (!$existingResult) {
                    $is_assigned = 0;
                    $reason = '';
                    $section_id = null;

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

                    $baseAssignments = $courseOccurrences[$course->id] ?? 1;
                    $sectionsCount = $sectionsCountByYear->get($yearSemesterCourse->year_id, 0);
                    $maxAssignments = $baseAssignments + ($sectionsCount > 1 ? $sectionsCount : 0);
                    $currentAssignments = $assignedLabAssistants[$course->id] ?? 0;

                    Log::info("Lab assistant assignment pre-check:", [
                        'course_id' => $course->id,
                        'year_id' => $yearSemesterCourse->year_id,
                        'instructor_id' => $instructor->id,
                        'current_load' => $currentLoad,
                        'cp' => $cp,
                        'load_capacity' => $loadCapacity,
                        'has_higher_preference' => $hasHigherPreference,
                        'current_assignments' => $currentAssignments,
                        'max_assignments' => $maxAssignments,
                        'sections_count' => $sectionsCount,
                    ]);

                    if ($currentLoad + $cp <= $loadCapacity && !$hasHigherPreference && $currentAssignments < $maxAssignments) {
                        $is_assigned = 1;
                        $section_id = $getAnySection($yearSemesterCourse->year_id);
                        $instructorLoad[$instructor->id] = $currentLoad + $cp;
                        $assignedLabAssistants[$course->id] = ($assignedLabAssistants[$course->id] ?? 0) + 1;
                        $reason = "Assigned: High score ({$instructorData['score']}) and within load capacity (added {$cp} lab assistant credits).";
                        if ($instructorData['previous_instructor_id'] == $instructor->id) {
                            $reason .= " Previously taught by this instructor.";
                        }
                        if ($section_id) {
                            $reason .= " Assigned to section ID: {$section_id}.";
                        } else {
                            $reason .= " No section assigned (no sections available).";
                        }
                    } elseif (!$hasHigherPreference && $currentLoad + $cp > $loadCapacity) {
                        $reason = "Not assigned: Exceeds instructor load capacity ({$currentLoad} + {$cp} > {$loadCapacity}).";
                    } elseif (!$hasHigherPreference && $currentAssignments >= $maxAssignments) {
                        $reason = "Not assigned: Maximum assignments reached for course (current: {$currentAssignments}, max: {$maxAssignments}).";
                    }

                    Log::info("Lab assistant assignment post-check:", [
                        'course_id' => $course->id,
                        'year_id' => $yearSemesterCourse->year_id,
                        'instructor_id' => $instructor->id,
                        'is_assigned' => $is_assigned,
                        'section_id' => $section_id,
                        'reason' => $reason,
                    ]);

                    $result = Result::create([
                        'instructor_id' => $instructor->id,
                        'course_id' => $instructorData['course_id'],
                        'assignment_id' => $assignment_id,
                        'point' => $instructorData['score'],
                        'is_assigned' => $is_assigned,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'section_id' => $is_assigned ? $section_id : null,
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                        'type' => 'lab_assistant',
                    ]);

                    Log::info("Lab assistant assignment decision:", [
                        'course' => $course->name,
                        'year' => $year->name,
                        'instructor' => $instructor->name,
                        'stream_id' => $yearSemesterCourse->stream_id,
                        'stream_name' => $yearSemesterCourse->stream ? $yearSemesterCourse->stream->name : 'None',
                        'section_id' => $section_id,
                        'base_assignments' => $baseAssignments,
                        'sections_count' => $sectionsCount,
                        'max_assignments' => $maxAssignments,
                        'current_assignments' => $currentAssignments,
                        'is_assigned' => $is_assigned,
                        'score' => $instructorData['score'],
                        'priority_boost' => $instructorData['priority_boost'],
                        'previous_instructor_id' => $instructorData['previous_instructor_id'],
                        'reason' => $reason,
                        'credits_added' => $is_assigned ? $cp : 0,
                        'credit_type' => 'lab_cp',
                        'current_load' => $currentLoad,
                        'load_capacity' => $loadCapacity,
                    ]);

                    $assignedResults[] = $result;
                }
            }

            DB::commit();

            return response()->json([
                'assigned_results' => $assignedResults,
                'all_scores' => $allResults,
                'assignment_counts' => $assignedCourses,
                'lab_lecture_counts' => $assignedLabLectures,
                'lab_assistant_counts' => $assignedLabAssistants,
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