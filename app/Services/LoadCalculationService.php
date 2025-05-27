<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

class LoadCalculationService
{
    public function calculateELH($data)
    {
        $lectureHours = $data['lecture_hours'] ?? 0;
        $labHours = $data['lab_hours'] ?? 0;
        $tutorialHours = $data['tutorial_hours'] ?? 0;
        $lectureSections = $data['lecture_sections'] ?? 0;
        $labSections = $data['lab_sections'] ?? 0;
        $tutorialSections = $data['tutorial_sections'] ?? 0;
        $studentsCount = $data['students_count'] ?? 0;

        $elh = 0;

        if ($data['assignment_type'] === 'lecture') {
            $elh += $lectureHours * $lectureSections;
            $elh += ($tutorialHours * $tutorialSections) * (2/3);
            
            if ($studentsCount > 50) {
                $elh *= 1.1;
            }
        } elseif ($data['assig~nment_type'] === 'lab') {
            $elh += ($labHours * $labSections) * (2/3);
        } elseif ($data['assignment_type'] === 'lab_assistant') {
            $elh += 1 ;
        }
Log::info("message",['lf',$elh]);
        return round($elh, 2);
    }
}