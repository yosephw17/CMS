<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityQuestion;

class QualityQuestionsStudentSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'question_text' => 'What Is the Name of This Course?',
                'input_type' => 'text',
                'options' => null,
                'audience' => 'student'
            ],
            [
                'question_text' => 'How Many Chapters Were Covered in This Course?',
                'input_type' => 'number',
                'options' => null,
                'audience' => 'student'
            ],
            [
                'question_text' => 'How Many Assessments Were Conducted in This Course?',
                'input_type' => 'number',
                'options' => null,
                'audience' => 'student'
            ],
            [
                'question_text' => 'How Clear Was the Explanation of the Course Material?',
                'input_type' => 'dropdown',
                'options' => json_encode(['Very Clear', 'Clear', 'Somewhat Clear', 'Unclear', 'Very Unclear']),
                'audience' => 'student'

            ],
            [
                'question_text' => 'How Engaging Was the Course Delivery?',
                'input_type' => 'dropdown',
                'options' => json_encode(['Very Engaging', 'Engaging', 'Neutral', 'Somewhat Engaging', 'Not Engaging']),
                'audience' => 'student'
            ],
            [
                'question_text' => 'How Effective Were the Assessments in Supporting Learning?',
                'input_type' => 'dropdown',
                'options' => json_encode(['Very Effective', 'Effective', 'Neutral', 'Somewhat Effective', 'Not Effective']),
                'audience' => 'student'
            ],
            [
                'question_text' => 'How Timely Was the Feedback Provided to Students?',
                'input_type' => 'dropdown',
                'options' => json_encode(['Very Timely', 'Timely', 'Neutral', 'Delayed', 'Very Delayed']),
                'audience' => 'student'
            ],
            [
                'question_text' => 'In Which Batch Was This Course Taken or Delivered?',
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
                ]),
                'audience' => 'student'
            ],
            [
                'question_text' => 'Please Provide Any Feedback or Suggestions for Improving This Course.',
                'input_type' => 'textarea',
                'options' => null,
                'audience' => 'student'
            ]
        ];





        foreach ($questions as $question) {
            QualityQuestion::create($question);
        }
    }
}