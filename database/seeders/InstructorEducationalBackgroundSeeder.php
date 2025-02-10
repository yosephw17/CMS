<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstructorEducationalBackgroundSeeder extends Seeder
{
    public function run()
    {
        $instructorIds = range(657, 676);
        $fieldIds = [1, 2, 53, 54, 55, 113];
        $eduBackgroundIds = [
            1, 2, 3, // Computer Science
            4, 5, // Computer Engineering
            6, 13, 14, // Software Engineering
            7, 8, // Cybersecurity
            9, 10, // Networking
            11, 12, // Artificial Intelligence
            15, 16, // Data Science
            17, 18, // Cloud Computing
            19, 20, // Robotics
            21, 22, // Computer Vision
            23, 24 // Quantum Computing
        ];
        $data = [];

        foreach ($instructorIds as $index => $instructorId) {
            // Evenly distribute field_ids and educational_background_ids
            $field_id = $fieldIds[$index % count($fieldIds)];
            $educational_background_id = $eduBackgroundIds[$index % count($eduBackgroundIds)];
            
            $data[] = [
                'instructor_id' => $instructorId,
                'field_id' => $field_id,
                'edu_background_id' => $educational_background_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('instructor_educational_background')->insert($data);
    }
}
