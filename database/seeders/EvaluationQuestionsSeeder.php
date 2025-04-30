<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationCategory;
use App\Models\EvaluationQuestion;

class EvaluationQuestionsSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Course Organization',
                'questions' => [
                    'The course covers all contents as listed in course guidebook',
                    'The course is conducted regularly and in time',
                    'All reference materials and exercises are prepared and well organized'
                ]
            ],
            [
                'name' => 'Lecturer\'s Performance',
                'questions' => [
                    'The lecturer prepared well for class',
                    'The lecturer shows solid knowledge of the content',
                    'The lectures show solid knowledge of the content',
                    'The lecture can explain the content clearly',
                    'The lecture relates the content to practical experience and examples'
                ]
            ],
            [
                'name' => 'Student Relation',
                'questions' => [
                    'The lecturer gives clear feedback on the performance of students',
                    'The assessment of student work is transparent and justified',
                    'The lecturer provides regular consultation hours',
                    'The lecturer appreciates student opinions and ideas',
                    'The lecturer responds appropriately to student complaints'
                ]
            ]
        ];

        foreach ($categories as $cat) {
            $category = EvaluationCategory::create([
                'name' => $cat['name'],
                'order' => 0 // You can set proper order here
            ]);

            foreach ($cat['questions'] as $i => $question) {
                EvaluationQuestion::create([
                    'category_id' => $category->id,
                    'question' => $question,
                    'order' => $i + 1
                ]);
            }
        }
    }
}