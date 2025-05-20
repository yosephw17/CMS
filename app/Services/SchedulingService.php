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
use App\Models\Stream;
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

            // Step 2: Fetch streams to determine active streams
            $streams = Stream::all()->keyBy('id')->mapWithKeys(function ($stream) {
                return [$stream->id => [
                    'department_id' => $stream->department_id,
                    'year_id' => $stream->year_id,
                    'semester_id' => $stream->semester_id,
                    'name' => $stream->name,
                ]];
            });
            Log::info('Streams fetched', ['total_streams' => $streams->count()]);

            // Step 3: Fetch and group courses by semester, department, year, and stream
            Log::debug('Course fetch query', [
                'sql' => YearSemesterCourse::where('year_semester_courses.semester_id', $semesterId)
                    ->has('course')
                    ->join('years', function ($join) {
                        $join->on('year_semester_courses.year_id', '=', 'years.id')
                             ->whereColumn('year_semester_courses.department_id', 'years.department_id');
                    })
                    ->leftJoin('streams', 'year_semester_courses.stream_id', '=', 'streams.id')
                    ->toSql(),
                'bindings' => [$semesterId]
            ]);

            $courses = YearSemesterCourse::where('year_semester_courses.semester_id', $semesterId)
                ->has('course')
                ->join('years', function ($join) {
                    $join->on('year_semester_courses.year_id', '=', 'years.id')
                         ->whereColumn('year_semester_courses.department_id', 'years.department_id');
                })
                ->leftJoin('streams', 'year_semester_courses.stream_id', '=', 'streams.id')
                ->with([
                    'course' => function ($query) {
                        $query->select('courses.id', 'courses.name', 'courses.lecture_cp', 'courses.lab_cp');
                    },
                    'department' => function ($query) {
                        $query->select('departments.id', 'departments.name');
                    },
                    'stream' => function ($query) {
                        $query->select('streams.id', 'streams.name');
                    }
                ])
                ->select(
                    'year_semester_courses.course_id',
                    'year_semester_courses.year_id',
                    'year_semester_courses.semester_id',
                    'year_semester_courses.department_id',
                    'year_semester_courses.stream_id',
                    'year_semester_courses.preferred_lecture_room_id',
                    'year_semester_courses.preferred_lab_room_id'
                )
                ->get()
                ->filter(function ($course) use ($year, $semesterId) {
                    if (!$course->stream_id) {
                        return true; // Non-stream courses
                    }
                    if (!$course->stream) {
                        Log::warning('Course with invalid stream_id', [
                            'course_id' => $course->course_id,
                            'stream_id' => $course->stream_id,
                        ]);
                        return false;
                    }
                    $yearOrder = DB::table('years')->where('years.id', $year)->value('years.id');
                    return $yearOrder > $course->stream->year_id ||
                           ($yearOrder == $course->stream->year_id && $semesterId >= $course->stream->semester_id);
                })
                ->groupBy('semester_id')
                ->map(function ($semesterCourses) {
                    return $semesterCourses->groupBy('department_id')->map(function ($departmentCourses) {
                        return $departmentCourses->groupBy('year_id')->map(function ($yearCourses) {
                            return $yearCourses->groupBy(function ($course) {
                                return $course->stream_id ?: 'null';
                            });
                        });
                    });
                });
            Log::info('Courses fetched', [
                'total_courses' => $courses->sum(function ($semester) {
                    return $semester->sum(function ($dept) {
                        return $dept->sum(function ($year) {
                            return $year->sum(function ($stream) {
                                return $stream->count();
                            });
                        });
                    });
                }),
                'semester_count' => $courses->count(),
                'course_details' => $courses->map(function ($semester) {
                    return $semester->map(function ($dept) {
                        return $dept->map(function ($year) {
                            return $year->map(function ($streamCourses, $streamId) {
                                return $streamCourses->map(function ($course) {
                                    return [
                                        'course_id' => $course->course_id,
                                        'course_name' => $course->course->name,
                                        'year_id' => $course->year_id,
                                        'department_id' => $course->department_id,
                                        'semester_id' => $course->semester_id,
                                        'stream_id' => $course->stream_id,
                                        'stream_name' => $course->stream ? $course->stream->name : 'None',
                                        'lecture_cp' => $course->course->lecture_cp,
                                        'lab_cp' => $course->course->lab_cp,
                                        'preferred_lecture_room_id' => $course->preferred_lecture_room_id,
                                        'preferred_lab_room_id' => $course->preferred_lab_room_id,
                                    ];
                                })->toArray();
                            })->toArray();
                        })->toArray();
                    })->toArray();
                })->toArray(),
            ]);

            // Step 4: Cache rooms
            $rooms = Room::all()->keyBy('id');
            Log::info('Rooms fetched', ['total_rooms' => $rooms->count()]);

            // Step 5: Cache time slots
            $timeSlots = TimeSlot::where('time_slots.is_break', false)
                ->with(['day' => function ($query) {
                    $query->select('days.id', 'days.name');
                }])
                ->orderBy('day_id')
                ->orderBy('start_time')
                ->select('time_slots.id', 'time_slots.day_id', 'time_slots.start_time', 'time_slots.end_time', 'time_slots.is_break')
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

            // Step 6: Cache instructor unavailability
            $instructorUnavailability = InstructorTimeSlot::select('instructor_id', 'time_slot_id')
                ->get()
                ->groupBy('instructor_id')
                ->mapWithKeys(function ($slots, $instructorId) {
                    return [$instructorId => $slots->pluck('time_slot_id')->toArray()];
                });
            Log::info('Instructor unavailability fetched', [
                'instructors' => $instructorUnavailability->keys()->count(),
            ]);

            // Step 7: Initialize scheduled assignments and course days used
            $scheduledAssignments = [];
            $courseDaysUsed = [];

            // Step 8: Process each semester-department-year-stream group
            foreach ($courses as $semesterId => $departments) {
                Log::info('Processing semester', ['semester_id' => $semesterId]);
                foreach ($departments as $departmentId => $years) {
                    Log::info('Processing department', ['department_id' => $departmentId]);
                    foreach ($years as $yearId => $streams) {
                        Log::info('Processing year', ['year_id' => $yearId]);
                        foreach ($streams as $streamId => $streamCourses) {
                            $streamIdLog = $streamId === 'null' ? null : $streamId;
                            Log::info('Processing stream', [
                                'stream_id' => $streamIdLog,
                                'stream_name' => $streamId === 'null' ? 'None' : ($streamCourses->first()->stream->name ?? 'Unknown'),
                            ]);
                            foreach ($streamCourses as $course) {
                                Log::info('Processing course', [
                                    'course_id' => $course->course_id,
                                    'course_name' => $course->course->name,
                                    'lecture_cp' => $course->course->lecture_cp,
                                    'lab_cp' => $course->course->lab_cp,
                                    'preferred_lecture_room_id' => $course->preferred_lecture_room_id,
                                    'preferred_lab_room_id' => $course->preferred_lab_room_id,
                                    'stream_id' => $course->stream_id,
                                    'stream_name' => $course->stream ? $course->stream->name : 'None',
                                ]);

                                // Step 9: Fetch instructors
                                $results = Result::where('results.course_id', $course->course_id)
                                    ->where(function ($query) use ($streamId) {
                                        if ($streamId !== 'null') {
                                            $query->where('results.stream_id', $streamId);
                                        } else {
                                            $query->whereNull('results.stream_id');
                                        }
                                    })
                                    ->with(['instructor' => function ($query) {
                                        $query->select('instructors.id', 'instructors.name');
                                    }])
                                    ->orderByDesc('point')
                                    ->orderBy('is_assigned', 'desc')
                                    ->select('results.id', 'results.course_id', 'results.instructor_id', 'results.point', 'results.is_assigned')
                                    ->get();

                                Log::info('Fetched results for course', [
                                    'course_id' => $course->course_id,
                                    'stream_id' => $course->stream_id,
                                    'results_count' => $results->count(),
                                    'results' => $results->toArray(),
                                ]);

                                if ($results->isEmpty()) {
                                    Log::warning('No instructors found', [
                                        'course_id' => $course->course_id,
                                        'stream_id' => $course->stream_id,
                                    ]);
                                    continue;
                                }

                                // Step 10: Get section ID for the course
                                $sectionId = $this->getSectionId($course, $yearId, $departmentId, $streamId === 'null' ? null : $streamId, $year);
                                Log::debug('Retrieved section ID', [
                                    'course_id' => $course->course_id,
                                    'year_id' => $yearId,
                                    'department_id' => $departmentId,
                                    'stream_id' => $streamId,
                                    'section_id' => $sectionId,
                                ]);

                                if (!$sectionId) {
                                    Log::warning('No valid section found for course', [
                                        'course_id' => $course->course_id,
                                        'stream_id' => $course->stream_id,
                                        'year_id' => $yearId,
                                        'department_id' => $departmentId,
                                    ]);
                                    continue;
                                }

                                // Step 11: Handle lecture and lab
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
                                                $sectionId,
                                                $course->stream_id
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
                                                    'stream_id' => $course->stream_id,
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
                                                    'stream_id' => $course->stream_id,
                                                ]);
                                            }
                                        }

                                        if ($assigned) {
                                            break;
                                        }
                                    }

                                    if ($assigned && !empty($allSlotsAssigned)) {
                                        Log::debug('Creating schedule result', [
                                            'course_id' => $course->course_id,
                                            'stream_id' => $course->stream_id,
                                            'instructor_id' => $instructorId,
                                            'section_id' => $sectionId,
                                            'room_id' => $roomId,
                                            'type' => $type,
                                        ]);
                                        $scheduleResult = ScheduleResult::create([
                                            'course_id' => $course->course_id,
                                            'instructor_id' => $instructorId,
                                            'schedule_id' => $scheduleId,
                                            'section_id' => $sectionId,
                                            'room_id' => $roomId,
                                            'type' => $type,
                                            'stream_id' => $course->stream_id,
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
                                                'stream_id' => $course->stream_id,
                                            ];
                                        }
                                        Log::info('Schedule result created', [
                                            'schedule_result_id' => $scheduleResult->id,
                                            'course_id' => $course->course_id,
                                            'type' => $type,
                                            'instructor_id' => $instructorId,
                                            'room_id' => $roomId,
                                            'section_id' => $sectionId,
                                            'stream_id' => $course->stream_id,
                                            'time_slots' => $allSlotsAssigned,
                                            'day_id' => $dayId,
                                        ]);
                                    } else {
                                        Log::warning("Course $type could not be scheduled", [
                                            'course_id' => $course->course_id,
                                            'required_slots' => $requiredSlots,
                                            'section_id' => $sectionId,
                                            'stream_id' => $course->stream_id,
                                        ]);
                                    }
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
        $sectionId,
        $streamId = null
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
                        $streamId,
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
                        'stream_id' => $streamId,
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
        $streamId = null,
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
                'stream_id' => $streamId,
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
                        'stream_id' => $streamId,
                        'existing_course_id' => $assignment['course_id'],
                        'existing_section_id' => $assignment['section_id'],
                        'existing_stream_id' => $assignment['stream_id'],
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
                        'stream_id' => $streamId,
                        'conflicting_course_id' => $assignment['course_id'],
                        'conflicting_stream_id' => $assignment['stream_id'],
                    ]);
                    return false;
                }
                // Conflict if same instructor and different stream
                if ($assignment['instructor_id'] == $instructorId &&
                    $assignment['stream_id'] != $streamId &&
                    $assignment['course_id'] != $courseId) {
                    $conflictingCourseId = $assignment['course_id'];
                    Log::debug('Assignment invalid: Instructor conflict across streams', [
                        'time_slot_id' => $timeSlotId,
                        'instructor_id' => $instructorId,
                        'course_id' => $courseId,
                        'stream_id' => $streamId,
                        'conflicting_course_id' => $assignment['course_id'],
                        'conflicting_stream_id' => $assignment['stream_id'],
                    ]);
                    return false;
                }
            }
        }

        return true;
    }

    private function getSectionId($course, $yearId, $departmentId, $streamId = null, $scheduleYear)
    {
        $query = Section::where('sections.year_id', $yearId)
            ->where('sections.department_id', $departmentId);

        if ($streamId) {
            $query->where('sections.stream_id', $streamId);
            $stream = Stream::find($streamId);
            if (!$stream) {
                Log::warning('Invalid stream_id for section', [
                    'stream_id' => $streamId,
                    'year_id' => $yearId,
                ]);
                return null;
            }
            $yearOrder = DB::table('years')->where('years.id', $scheduleYear)->value('years.id');
            if ($yearOrder < $stream->year_id) {
                Log::warning('Stream not active for this year', [
                    'stream_id' => $streamId,
                    'stream_name' => $stream->name,
                    'year_id' => $yearId,
                    'stream_start_year' => $stream->year_id,
                ]);
                return null;
            }
        } else {
            $query->whereNull('sections.stream_id');
        }

        Log::debug('Section fetch query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $sections = $query->orderBy('sections.number_of_students', 'asc')->get();

        if ($sections->isNotEmpty()) {
            return $sections->first()->id;
        }

        $streamName = $streamId ? ($course->stream->name ?? 'Stream ' . $streamId) : 'General';
        $section = Section::firstOrCreate(
            [
                'year_id' => $yearId,
                'department_id' => $departmentId,
                'stream_id' => $streamId,
            ],
            [
                'name' => "Section for Year {$yearId}, Dept {$departmentId}, {$streamName}",
                'number_of_students' => 0,
            ]
        );

        Log::info('Created or retrieved section', [
            'section_id' => $section->id,
            'section_name' => $section->name,
            'stream_id' => $streamId,
            'year_id' => $yearId,
            'department_id' => $departmentId,
        ]);

        return $section->id;
    }
}

?>