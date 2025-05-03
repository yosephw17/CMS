<?php

// database/seeders/EvaluatorSeeder.php
namespace Database\Seeders;
use App\Models\Evaluator;
use Illuminate\Database\Seeder;

class EvaluatorSeeder extends Seeder
{
    public function run()
    {
        $evaluators = [
            [
                'email' => 'student1@university.edu',
                'name' => 'Ali Ahmed',
                'type' => 'student'
            ],
            [
                'email' => 'professor1@university.edu',
                'name' => 'Dr. Sarah Khan',
                'type' => 'instructor'
            ],
            [
                'email' => 'head.cs@university.edu',
                'name' => 'Prof. Jamal Rahman',
                'type' => 'dean'
            ],
            [
                'email' => 'student2@university.edu',
                'name' => 'Fatima Mahmoud',
                'type' => 'student'
            ],
            [
                'email' => 'dean.engineering@university.edu',
                'name' => 'Dr. Omar Farooq',
                'type' => 'dean' // or 'dean' if you prefer
            ]
        ];

        foreach ($evaluators as $evaluator) {
            Evaluator::create($evaluator);
        }
    }
}
