<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Parameter;
use App\Models\Field;
use App\Models\ProfessionalExperience;
use Faker\Factory as Faker;
use App\Services\CourseAssignmentService;

class CourseAssignmentSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            $faker = Faker::create();

            // Step 1: Insert parameters with predefined points
            $parameters = [
                ['name' => 'professional_experience', 'points' => 20],
                ['name' => 'research', 'points' => 15],
                ['name' => 'educational_background', 'points' => 10],
            ];
            Parameter::insert($parameters);

            // Step 2: Insert fields
            $fields = [];
            for ($i = 1; $i <= 5; $i++) {
                $fields[] = [
                    'name' => $faker->word,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Field::insert($fields);

            // Step 3: Insert professional experiences
            $fields = Field::all();
            $profExperiences = [];
            foreach ($fields as $field) {
                $profExperiences[] = [
                    'name' => $faker->sentence(2),
                    'description' => $faker->paragraph,
                    'field_id' => $field->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ProfessionalExperience::insert($profExperiences);

            // Step 4: Create 20 instructors
            $instructors = [];
            for ($i = 1; $i <= 20; $i++) {
                $instructors[] = [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'phone' => '222',
                    'role_id' => '1',
                    'is_available' => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Instructor::insert($instructors);
            $instructors = Instructor::all();

            // Step 5: Create 15 courses
            $courses = [];
            for ($i = 1; $i <= 15; $i++) {
                $courses[] = [
                    'name' => $faker->sentence(3),
                    'course_code' => $faker->unique()->word . '-' . strtoupper($faker->lexify('????')),
                    'cp' => rand(1, 10),
                    'lecture_cp' => rand(0, 5),
                    'lab_cp' => rand(0, 5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Course::insert($courses);
            $courses = Course::all();

            // Step 6: Insert assignments
            $assignments = [];
            for ($i = 1; $i <= 10; $i++) {
                $assignments[] = [
                    'year' => $faker->year,
                    'semester' => rand(1, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('assignments')->insert($assignments);
            $assignments = DB::table('assignments')->get();

            // Step 7: Assign professional experiences to instructors via pivot table
            // $profExps = [];
            // $professionalExperiences = ProfessionalExperience::all();
            // foreach ($instructors as $instructor) {
            //     for ($j = 0; $j < rand(1, 3); $j++) {
            //         // Insert into the pivot table
            //         $profExps[] = [
            //             'instructor_id' => $instructor->id,
            //             'pro_exp_id' => $professionalExperiences->random()->id,
            //             'created_at' => now(),
            //             'updated_at' => now(),
            //         ];
            //     }
            // }
            // DB::table('instructor_professional_experience')->insert($profExps);

            // Step 8: Assign choices to instructors for courses
            $choices = [];
            foreach ($instructors as $instructor) {
                foreach ($courses as $course) {
                    if (rand(0, 1)) { // 50% chance instructor is interested in this course
                        $choices[] = [
                            'instructor_id' => $instructor->id,
                            'course_id' => $course->id,
                            'assignment_id' => $assignments->random()->id, // Randomly select an existing assignment
                            'rank' => rand(1, 3),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            // DB::table('choice')->insert($choices);

            // Step 9: Run the course assignment service
            app(CourseAssignmentService::class)->assignCourses();

            DB::commit();
            $this->command->info('Course assignments seeded successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Seeding failed: ' . $e->getMessage());
        }
    }
}
