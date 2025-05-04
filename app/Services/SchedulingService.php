<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\YearSemesterCourse;
use App\Models\Result;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Models\InstructorTimeSlot;
use App\Models\ScheduleResult;
use App\Models\ScheduleTimeSlot;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchedulingService
{
    public function generateSchedule($scheduleId)
    {
        Log::info('Starting schedule generation', ['schedule_id' => $scheduleId]);

        DB::beginTransaction();
        try {
            // Step 1: Fetch the schedule
            $schedule = Schedule::findOrFail($scheduleId);
            $year = $schedule->year;
            $semesterId = $schedule->semester_id;
            Log::info('Schedule fetched', ['year' => $year, 'semester_id' => $semesterId]);

            // Step 2: Fetch and group courses by semester, department, then year
            $courses = YearSemesterCourse::where('semester_id', $semesterId)
                ->has('course')
                ->join('years', function ($join) {
                    $join->on('year_semester_courses.year_id', '=', 'years.id')
                         ->whereColumn('year_semester_courses.department_id', 'years.department_id');
                })
                ->with(['course' => function ($query) {
                    $query->select('id', 'name', 'lecture_cp', 'lab_cp');
                }, 'department' => function ($query) {
                    $query->select('id', 'name');
                }])
                ->select(
                    'year_semester_courses.course_id',
                    'year_semester_courses.year_id',
                    'year_semester_courses.semester_id',
                    'year_semester_courses.department_id',
                    'year_semester_courses.preferred_lecture_room_id',
                    'year_semester_courses.preferred_lab_room_id'
                )
                ->get()
                ->groupBy('semester_id')
                ->map(function ($semesterCourses) {
                    return $semesterCourses->groupBy('department_id')->map(function ($departmentCourses) {
                        return $departmentCourses->groupBy('year_id');
                    });
                });
            Log::info('Courses fetched', [
                'total_courses' => $courses->sum(function ($semester) {
                    return $semester->sum(function ($dept) {
                        return $dept->sum(function ($year) {
                            return $year->count();
                        });
                    });
                }),
                'semester_count' => $courses->count(),
                'course_details' => $courses->map(function ($semester) {
                    return $semester->map(function ($dept) {
                        return $dept->map(function ($year) {
                            return $year->map(function ($course) {
                                return [
                                    'course_id' => $course->course_id,
                                    'course_name' => $course->course->name,
                                    'year_id' => $course->year_id,
                                    'department_id' => $course->department_id,
                                    'semester_id' => $course->semester_id,
                                    'lecture_cp' => $course->course->lecture_cp,
                                    'lab_cp' => $course->course->lab_cp,
                                    'preferred_lecture_room_id' => $course->preferred_lecture_room_id,
                                    'preferred_lab_room_id' => $course->preferred_lab_room_id,
                                ];
                            })->toArray();
                        })->toArray();
                    })->toArray();
                })->toArray(),
            ]);

            // Step 3: Cache rooms
            $rooms = Room::all()->keyBy('id');
            Log::info('Rooms fetched', ['total_rooms' => $rooms->count()]);

            // Step 4: Cache time slots
            $timeSlots = TimeSlot::where('is_break', false)
                ->with('day')
                ->orderBy('day_id')
                ->orderBy('start_time')
                ->get();
            $timeSlotsByDay = $timeSlots->groupBy('day_id');
            $timeSlotsById = $timeSlots->keyBy('id');
            Log::info('Time slots fetched', [
                'total_time_slots' => $timeSlots->count(),
                'slots' => $timeSlots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'day_id' => $slot->day_id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                    ];
                })->toArray(),
            ]);

            // Step 5: Cache instructor unavailability
            $instructorUnavailability = InstructorTimeSlot::all()
                ->groupBy('instructor_id')
                ->mapWithKeys(function ($slots, $instructorId) {
                    return [$instructorId => $slots->pluck('time_slot_id')->toArray()];
                });
            Log::info('Instructor unavailability fetched', [
                'instructors' => $instructorUnavailability->keys()->count(),
            ]);

            // Step 6: Initialize scheduled assignments and course days used
            $scheduledAssignments = [];
            $courseDaysUsed = []; // Tracks days used by each course_id

            // Step 7: Process each semester-department-year group
            foreach ($courses as $semesterId => $departments) {
                Log::info('Processing semester', ['semester_id' => $semesterId]);
                foreach ($departments as $departmentId => $years) {
                    Log::info('Processing department', ['department_id' => $departmentId]);
                    foreach ($years as $yearId => $yearCourses) {
                        Log::info('Processing year', ['year_id' => $yearId]);
                        foreach ($yearCourses as $course) {
                            Log::info('Processing course', [
                                'course_id' => $course->course_id,
                                'course_name' => $course->course->name,
                                'lecture_cp' => $course->course->lecture_cp,
                                'lab_cp' => $course->course->lab_cp,
                                'preferred_lecture_room_id' => $course->preferred_lecture_room_id,
                                'preferred_lab_room_id' => $course->preferred_lab_room_id,
                            ]);

                            // Step 8: Fetch instructors
                            $results = Result::where('course_id', $course->course_id)
                                ->whereHas('assignment', function ($query) use ($year, $semesterId, $departmentId) {
                                    $query->where('year', $year)
                                          ->where('semester_id', $semesterId)
                                          ->where('department_id', $departmentId);
                                })
                                ->orderByDesc('point')
                                ->orderBy('is_assigned', 'desc')
                                ->get();

                            if ($results->isEmpty()) {
                                Log::warning('No instructors found', ['course_id' => $course->course_id]);
                                continue;
                            }

                            // Step 9: Get section ID for the course
                            $sectionId = $this->getSectionId($course, $yearId, $departmentId);

                            // Step 10: Handle lecture and lab
                            $types = [
                                'lecture' => [
                                    'cp' => $course->course->lecture_cp,
                                    'preferred_room_id' => $course->preferred_lecture_room_id,
                                ],
                                'lab' => [
                                    'cp' => $course->course->lab_cp,
                                    'preferred_room_id' => $course->preferred_lab_room_id,
                                ],
                            ];

                            foreach ($types as $type => $config) {
                                $requiredSlots = $config['cp'];
                                if ($requiredSlots <= 0) {
                                    Log::info("Skipping $type for course, cp is 0", [
                                        'course_id' => $course->course_id,
                                    ]);
                                    continue;
                                }

                                // Try to assign all slots in one group
                                $slotGroups = $this->planSlotGroups($requiredSlots, $type);
                                $allSlotsAssigned = [];
                                $instructorId = null;
                                $roomId = null;
                                $assigned = false;

                                foreach ($results as $result) {
                                    $instructorId = $result->instructor_id;

                                    // Prioritize preferred room, fall back to all rooms
                                    $availableRooms = $config['preferred_room_id'] && $rooms->has($config['preferred_room_id'])
                                        ? $rooms->only($config['preferred_room_id'])
                                        : $rooms;

                                    foreach ($availableRooms as $room) {
                                        $roomId = $room->id;
                                        $allSlotsAssigned = [];

                                        // Try to assign all slots in one group on one day
                                        $slotsAssigned = $this->assignConsecutiveSlots(
                                            $timeSlotsByDay,
                                            $requiredSlots,
                                            $instructorId,
                                            $roomId,
                                            $instructorUnavailability,
                                            $scheduledAssignments,
                                            $courseDaysUsed[$course->course_id] ?? [],
                                            $course->course_id,
                                            $sectionId
                                        );

                                        if ($slotsAssigned) {
                                            $allSlotsAssigned = $slotsAssigned;
                                            $dayId = $timeSlotsById[$slotsAssigned[0]]->day_id;
                                            Log::info('Assigned slot group', [
                                                'course_id' => $course->course_id,
                                                'type' => $type,
                                                'slots_needed' => $requiredSlots,
                                                'time_slot_ids' => $slotsAssigned,
                                                'day_id' => $dayId,
                                                'instructor_id' => $instructorId,
                                                'room_id' => $roomId,
                                                'section_id' => $sectionId,
                                            ]);
                                            $assigned = true;
                                            break;
                                        } else {
                                            Log::warning('Failed to assign slot group', [
                                                'course_id' => $course->course_id,
                                                'type' => $type,
                                                'slots_needed' => $requiredSlots,
                                                'instructor_id' => $instructorId,
                                                'room_id' => $roomId,
                                                'section_id' => $sectionId,
                                            ]);
                                        }
                                    }

                                    if ($assigned) {
                                        break;
                                    }
                                }

                                if ($assigned && !empty($allSlotsAssigned)) {
                                    $scheduleResult = ScheduleResult::create([
                                        'course_id' => $course->course_id,
                                        'instructor_id' => $instructorId,
                                        'schedule_id' => $scheduleId,
                                        'section_id' => $sectionId,
                                        'room_id' => $roomId,
                                        'type' => $type,
                                    ]);

                                    $dayId = $timeSlotsById[$allSlotsAssigned[0]]->day_id;
                                    $courseDaysUsed[$course->course_id][] = $dayId;

                                    foreach ($allSlotsAssigned as $timeSlotId) {
                                        ScheduleTimeSlot::create([
                                            'schedule_result_id' => $scheduleResult->id,
                                            'time_slot_id' => $timeSlotId,
                                            'day_id' => $timeSlotsById[$timeSlotId]->day_id,
                                        ]);

                                        $scheduledAssignments[] = [
                                            'course_id' => $course->course_id,
                                            'instructor_id' => $instructorId,
                                            'room_id' => $roomId,
                                            'time_slot_id' => $timeSlotId,
                                            'day_id' => $timeSlotsById[$timeSlotId]->day_id,
                                            'section_id' => $sectionId,
                                        ];
                                    }
                                    Log::info('Schedule result created', [
                                        'schedule_result_id' => $scheduleResult->id,
                                        'course_id' => $course->course_id,
                                        'type' => $type,
                                        'instructor_id' => $instructorId,
                                        'room_id' => $roomId,
                                        'section_id' => $sectionId,
                                        'time_slots' => $allSlotsAssigned,
                                        'day_id' => $dayId,
                                    ]);
                                } else {
                                    Log::warning("Course $type could not be scheduled", [
                                        'course_id' => $course->course_id,
                                        'required_slots' => $requiredSlots,
                                        'section_id' => $sectionId,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            Log::info('Schedule generation completed', [
                'schedule_id' => $scheduleId,
                'total_assignments' => count($scheduledAssignments),
            ]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Scheduling failed', [
                'schedule_id' => $scheduleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    private function planSlotGroups($requiredSlots, $type)
    {
        $groups = [];

        // Assign all slots in one group if possible
        if ($requiredSlots > 0) {
            $groups[] = $requiredSlots;
        }

        Log::info('Planned slot groups', [
            'type' => $type,
            'required_slots' => $requiredSlots,
            'groups' => $groups,
        ]);
        return $groups;
    }

    private function assignConsecutiveSlots(
        $timeSlots,
        $slotsNeeded,
        $instructorId,
        $roomId,
        $instructorUnavailability,
        $scheduledAssignments,
        $courseDaysUsed,
        $courseId,
        $sectionId
    ) {
        foreach ($timeSlots as $dayId => $daySlots) {
            // Skip if the course is already scheduled on this day
            if (in_array($dayId, $courseDaysUsed)) {
                continue;
            }

            if ($daySlots->count() < $slotsNeeded) {
                continue;
            }

            for ($i = 0; $i <= $daySlots->count() - $slotsNeeded; $i++) {
                $slotsToTry = $daySlots->slice($i, $slotsNeeded)->values();
                $isConsecutive = true;

                // Check if slots are consecutive
                for ($j = 1; $j < $slotsToTry->count(); $j++) {
                    $prevSlot = $slotsToTry[$j - 1];
                    $currentSlot = $slotsToTry[$j];

                    // Allow up to 5 minutes gap between slots
                    $prevEndTime = strtotime($prevSlot->end_time);
                    $currentStartTime = strtotime($currentSlot->start_time);
                    $gapInSeconds = $currentStartTime - $prevEndTime;
                    $maxGap = 5 * 60; // 5 minutes in seconds

                    if ($gapInSeconds > $maxGap) {
                        $isConsecutive = false;
                        break;
                    }
                }

                if (!$isConsecutive) {
                    continue;
                }

                // Validate the entire slot group
                $allValid = true;
                $conflictingCourseId = null;
                foreach ($slotsToTry as $slot) {
                    $isValid = $this->isValidAssignment(
                        $instructorId,
                        $roomId,
                        $slot->id,
                        $instructorUnavailability,
                        $scheduledAssignments,
                        $courseId,
                        $sectionId,
                        $conflictingCourseId
                    );
                    if (!$isValid) {
                        $allValid = false;
                        break;
                    }
                }

                if ($allValid) {
                    return $slotsToTry->pluck('id')->toArray();
                } else {
                    Log::debug('Slot group invalid: Conflict detected', [
                        'course_id' => $courseId,
                        'section_id' => $sectionId,
                        'time_slot_ids' => $slotsToTry->pluck('id')->toArray(),
                        'day_id' => $dayId,
                        'conflicting_course_id' => $conflictingCourseId,
                    ]);
                }
            }
        }

        return [];
    }

    private function isValidAssignment(
        $instructorId,
        $roomId,
        $timeSlotId,
        $instructorUnavailability,
        $scheduledAssignments,
        $courseId,
        $sectionId,
        &$conflictingCourseId = null
    ) {
        // Check instructor unavailability
        if (isset($instructorUnavailability[$instructorId]) &&
            in_array($timeSlotId, $instructorUnavailability[$instructorId])) {
            Log::debug('Assignment invalid: Instructor unavailable', [
                'instructor_id' => $instructorId,
                'time_slot_id' => $timeSlotId,
                'course_id' => $courseId,
                'section_id' => $sectionId,
            ]);
            return false;
        }

        // Check for conflicts with other assignments
        foreach ($scheduledAssignments as $assignment) {
            if ($assignment['time_slot_id'] == $timeSlotId) {
                // Conflict if same instructor or same room
                if ($assignment['instructor_id'] == $instructorId || $assignment['room_id'] == $roomId) {
                    Log::debug('Assignment invalid: Instructor or room conflict', [
                        'time_slot_id' => $timeSlotId,
                        'instructor_id' => $instructorId,
                        'room_id' => $roomId,
                        'course_id' => $courseId,
                        'section_id' => $sectionId,
                        'existing_course_id' => $assignment['course_id'],
                        'existing_section_id' => $assignment['section_id'],
                    ]);
                    return false;
                }
                // Conflict if same section (different course)
                if ($assignment['section_id'] == $sectionId && $assignment['course_id'] != $courseId) {
                    $conflictingCourseId = $assignment['course_id'];
                    Log::debug('Assignment invalid: Section conflict with another course', [
                        'time_slot_id' => $timeSlotId,
                        'section_id' => $sectionId,
                        'course_id' => $courseId,
                        'conflicting_course_id' => $assignment['course_id'],
                    ]);
                    return false;
                }
            }
        }

        return true;
    }

    private function getSectionId($course, $yearId, $departmentId)
    {
        $sections = Section::where('year_id', $yearId)
            ->where('department_id', $departmentId)
            ->orderBy('number_of_students', 'asc')
            ->get();

        if ($sections->isNotEmpty()) {
            return $sections->first()->id;
        }

        $section = Section::firstOrCreate(
            [
                'year_id' => $yearId,
                'department_id' => $departmentId,
            ],
            [
                'name' => "Default Class for Year {$yearId}, Dept {$departmentId}",
                'number_of_students' => 0,
            ]
        );

        return $section->id;
    }
}