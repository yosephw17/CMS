<?php

namespace Database\Seeders;

use App\Models\QualityQuestion;
use Illuminate\Database\Seeder;

class QualityQuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'question_text' => 'Total Number of Chapters in the Course',
                'input_type' => 'number',
                'options' => null
            ],
            [
                'question_text' => 'Total Number of Chapters Covered',
                'input_type' => 'number',
                'options' => null
            ],
            [
                'question_text' => 'Total number of assessments planned in the course',
                'input_type' => 'number',
                'options' => null
            ],
            [
                'question_text' => 'Total number of assessments delivered',
                'input_type' => 'number',
                'options' => null
            ],
            [
                'question_text' => 'Total number of feedback given to students',
                'input_type' => 'number',
                'options' => null
            ],
            [
                'question_text' => 'In which batch did you deliver the above course?',
                'input_type' => 'dropdown',
                'options' => json_encode([
                    '3rd Computer Section A',
                    '3rd Computer Section B',
                    '3rd Electrical Section A',
                    '3rd Electrical Section B',
                    '4th Power',
                    '4th Computer',
                    '4th Control',
                    '4th Communication',
                    '5th Power',
                    '5th Computer',
                    '5th Communication'
                ])
            ],
            [
                'question_text' => 'Write any feedback or recommendations used to improve the course for the future.',
                'input_type' => 'textarea',
                'options' => null
            ]
        ];

        foreach ($questions as $question) {
            QualityQuestion::create($question);
        }
    }
}