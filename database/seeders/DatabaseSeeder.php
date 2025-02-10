<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            EducationalBackgroundSeeder::class,
            ChoiceSeeder::class,
            InstructorEducationalBackgroundSeeder::class,
            InstructorRoleSeeder::class,
            CourseFieldPivotSeeder::class,
        ]);
    }
}

class EducationalBackgroundSeeder extends Seeder
{
    public function run()
    {
        DB::table('educational_backgrounds')->insert([
            [
                'id' => 1,
                'name' => 'Ph.D. in Computer Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Master\'s in Computer Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Bachelor\'s in Computer Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

class ChoiceSeeder extends Seeder
{
    public function run()
    {
        DB::table('choices')->insert([
            [
                'id' => 1,
                'instructor_id' => 1,
                'course_id' => 1,
                'assignment_id' => 1,
                'rank' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'instructor_id' => 1,
                'course_id' => 2,
                'assignment_id' => 1,
                'rank' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'instructor_id' => 2,
                'course_id' => 3,
                'assignment_id' => 1,
                'rank' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

class InstructorEducationalBackgroundSeeder extends Seeder
{
    public function run()
    {
        DB::table('instructor_educational_background')->insert([
            [
                'id' => 1,
                'instructor_id' => 1,
                'educational_background_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'instructor_id' => 1,
                'educational_background_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'instructor_id' => 2,
                'educational_background_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

class InstructorRoleSeeder extends Seeder
{
    public function run()
    {
        DB::table('instructor_roles')->insert([
            [
                'id' => 1,
                'name' => 'Professor',
                'load' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Associate Professor',
                'load' => 8,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Assistant Professor',
                'load' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

class CourseFieldPivotSeeder extends Seeder
{
    public function run()
    {
        DB::table('course_field')->insert([
            [
                'course_id' => 1,
                'field_id' => 1,
            ],
            [
                'course_id' => 1,
                'field_id' => 2,
            ],
            [
                'course_id' => 2,
                'field_id' => 2,
            ],
            [
                'course_id' => 2,
                'field_id' => 3,
            ],
            [
                'course_id' => 3,
                'field_id' => 1,
            ],
            [
                'course_id' => 3,
                'field_id' => 3,
            ],
        ]);
    }
}
