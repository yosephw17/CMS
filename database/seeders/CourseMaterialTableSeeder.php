<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade

class CourseMaterialTableSeeder extends Seeder
{
    public function run()
    {
        $courseMaterials = [
            ['field_id' => 1, 'course_id' => 1],
            ['field_id' => 2, 'course_id' => 2],
            ['field_id' => 3, 'course_id' => 3],
            ['field_id' => 4, 'course_id' => 4],
            ['field_id' => 5, 'course_id' => 5],
            ['field_id' => 6, 'course_id' => 6],
            ['field_id' => 7, 'course_id' => 7],
            ['field_id' => 8, 'course_id' => 8],
            ['field_id' => 9, 'course_id' => 9],
            ['field_id' => 10, 'course_id' => 10],
            ['field_id' => 1, 'course_id' => 11],
            ['field_id' => 3, 'course_id' => 12],
            ['field_id' => 5, 'course_id' => 13],
            ['field_id' => 4, 'course_id' => 14],
            ['field_id' => 1, 'course_id' => 15],
            ['field_id' => 6, 'course_id' => 16],
            ['field_id' => 7, 'course_id' => 17],
            ['field_id' => 11, 'course_id' => 18],
        ];

        DB::table('course_field')->insert($courseMaterials);
    }
}
