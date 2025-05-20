<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class YearSemesterCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseIds = range(1, 20);

        // Get lecture and lab room IDs
        $lectureRoomIds = DB::table('rooms')->where('type', 'lecture')->pluck('id')->toArray();
        $labRoomIds = DB::table('rooms')->where('type', 'lab')->pluck('id')->toArray();

        // Get stream IDs (assuming streams exist)
        $streamIds = DB::table('streams')->pluck('id')->toArray();

        for ($year = 1; $year <= 8; $year++) {
            for ($semester = 1; $semester <= 2; $semester++) {
                // Randomly select 5 to 7 course IDs
                $selectedCourses = collect($courseIds)->shuffle()->take(rand(5, 7));

                foreach ($selectedCourses as $courseId) {
                    // Assign stream_id for years >= 5, semester 2 (e.g., Power Engineering)
                    $streamId = null;
                    if ($year >= 5 && $semester == 2 && !empty($streamIds)) {
                        $streamId = Arr::random($streamIds); // Randomly assign a stream
                    }

                    DB::table('year_semester_courses')->insert([
                        'year_id' => $year,
                        'semester_id' => $semester,
                        'course_id' => $courseId,
                        'department_id' => rand(1, 2),
                        'stream_id' => $streamId,
                        'preferred_lecture_room_id' => Arr::random($lectureRoomIds),
                        'preferred_lab_room_id' => rand(0, 1) ? Arr::random($labRoomIds) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
?>