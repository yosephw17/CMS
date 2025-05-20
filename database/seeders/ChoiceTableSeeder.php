<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch existing IDs from the database
        $instructorIds = DB::table('instructors')->pluck('id')->toArray();
        $courseIds = DB::table('courses')->pluck('id')->toArray();

        // Set assignment ID to 1
        $assignmentId = 1;

        // Ensure that we have data in all tables before proceeding
        if (empty($instructorIds) || empty($courseIds)) {
            $this->command->error('One or more required tables (instructors, courses) are empty. Please seed those tables first.');
            return;
        }

        $data = [];

        foreach ($instructorIds as $instructorId) {
            shuffle($courseIds); // Shuffle course order for randomness

            // Assign 3 choices (1, 2, and 3) to each instructor
            foreach (array_slice($courseIds, 0, 3) as $index => $courseId) {
                $data[] = [
                    'instructor_id' => $instructorId,
                    'course_id' => $courseId,
                    'assignment_id' => $assignmentId, // Fixed assignment ID = 1
                    'rank' => $index + 1, // Rank 1, 2, 3
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
