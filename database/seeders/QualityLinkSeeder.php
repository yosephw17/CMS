<?php

namespace Database\Seeders;

use App\Models\QualityLink;
use Illuminate\Database\Seeder;

class QualityLinkSeeder extends Seeder
{
    public function run()
    {

        // OR manual example:
        QualityLink::create([
            'audit_session_id' => 1, // Before Mid Exam
            'instructor_id' => 2, // Example instructor
            'semester_id' => 1, // Example semester
            'academic_year_id' => 1, // Example academic year
            // 'hash' will auto-generate
            'is_used' => false
        ]);
    }
}