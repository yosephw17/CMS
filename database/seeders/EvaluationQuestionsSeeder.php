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
                    'The course is conducted regularly and on time',
                    'All reference materials are well organized'
                ]
            ],
            // ... other categories
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