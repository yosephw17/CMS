<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearSemesterCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseIds = range(1, 20);

        for ($year = 1; $year <= 8; $year++) {
            for ($semester = 1; $semester <= 2; $semester++) {
                // Randomly select 5 to 7 course IDs from the 1–20 range
                $selectedCourses = collect($courseIds)->shuffle()->take(rand(5, 7));

                foreach ($selectedCourses as $courseId) {
                    DB::table('year_semester_courses')->insert([
                        'year_id' => $year,
                        'semester_id' => $semester,
                        'course_id' => $courseId,
                        'department_id' => rand(1, 2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
