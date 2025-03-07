<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstructorProfessionalExperienceSeeder extends Seeder
{
    public function run()
    {
        $instructorIds = range(2, 21);
        $professionalExperienceIds = [1, 2, 3, 4, 5];
        $data = [];

        foreach ($instructorIds as $index => $instructorId) {
            // Evenly distribute professional_experience_ids
            $professional_experience_id = $professionalExperienceIds[$index % count($professionalExperienceIds)];
            
            $data[] = [
                'instructor_id' => $instructorId,
                'pro_exp_id' => $professional_experience_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('instructor_professional_experience')->insert($data);
    }
}
