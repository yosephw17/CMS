<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade

class CourseMaterialTableSeeder extends Seeder
{
    public function run()
    {
        $courseMaterials = [
            ['field_id' => 54, 'course_id' => 366],
            ['field_id' => 54, 'course_id' => 373],
            ['field_id' => 54, 'course_id' => 376],
            ['field_id' => 54, 'course_id' => 378],
            ['field_id' => 54, 'course_id' => 372],
            ['field_id' => 1, 'course_id' => 370],
            ['field_id' => 1, 'course_id' => 374],
            ['field_id' => 1, 'course_id' => 383],
            ['field_id' => 2, 'course_id' => 381],
            ['field_id' => 2, 'course_id' => 367],
            ['field_id' => 53, 'course_id' => 371],
            ['field_id' => 53, 'course_id' => 375],
            ['field_id' => 53, 'course_id' => 377],
            ['field_id' => 53, 'course_id' => 380],
            ['field_id' => 53, 'course_id' => 382],
            ['field_id' => 55, 'course_id' => 374],
            ['field_id' => 113, 'course_id' => 369],
            ['field_id' => 113, 'course_id' => 368],
        ];

        DB::table('course_field')->insert($courseMaterials);
    }
}
