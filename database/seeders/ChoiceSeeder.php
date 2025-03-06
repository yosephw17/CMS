<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch existing IDs from the database
        $instructorIds = DB::table('instructors')->pluck('id')->toArray();
        $courseIds = DB::table('courses')->pluck('id')->toArray();

        // Set assignment IDs to 158 and 159
        $assignmentIds = [158, 159];

        // Ensure that we have data in all tables before proceeding
        if (empty($instructorIds) || empty($courseIds) || empty($assignmentIds)) {
            $this->command->error('One or more required tables (instructors, courses) are empty. Please seed those tables first.');
            return;
        }

        $data = [];

        foreach ($instructorIds as $instructorId) {
            shuffle($courseIds); // Shuffle course order for randomness

            // Assign 3 choices (1, 2, and 3) to each instructor
            foreach (array_slice($courseIds, 0, 3) as $courseId) {
                $data[] = [
                    'instructor_id' => $instructorId,
                    'course_id' => $courseId,
                    'assignment_id' => $assignmentIds[array_rand($assignmentIds)], // Random assignment ID
                    'rank' => count($data) % 3 + 1, // Assign rank 1, 2, 3 in sequence
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert the data into the choice table
        DB::table('choice')->insert($data);
        $this->command->info('Choice table seeded successfully!');
    }
}
