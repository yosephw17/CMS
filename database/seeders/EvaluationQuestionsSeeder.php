<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationCategory;
use App\Models\EvaluationQuestion;

class EvaluationQuestionsSeeder extends Seeder
{
    public function run()
    {
        // Common categories for regular instructors (students and peer instructors)
        $commonCategories = [
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

        // Lab Assistant specific categories
        $labAssistantCategories = [
            [
                'name' => 'Laboratory Organization and Readiness',
                'questions' => [
                    'The Laboratory kits and softwares are well prepared',
                    'The Laboratory is conducted regularly and in time',
                    'All Laboratory manual and exercises are prepared and well organized'
                ]
            ],
            [
                'name' => 'Laboratory Assistance Performance',
                'questions' => [
                    'The Laboratory assistant is ready for the laboratory sessions',
                    'The Laboratory assistant shows solid knowledge of the content',
                    'The Laboratory assistant assists students on every step of the Laboratory session',
                    'The Laboratory assistant can explain the content of the laboratory clearly',
                    'The Laboratory assistant relates the content to practical experience and examples'
                ]
            ],
            [
                'name' => 'Student Relation',
                'questions' => [
                    'The Laboratory assistant gives clear feedback on performance of students',
                    'The assessment of student work is transparent and justified',
                    'The Laboratory assistant provides assistance for students',
                    'The Laboratory assistant appreciates students ideas',
                    'The Laboratory assistant responds appropriately to student complaints'
                ]
            ]
        ];

        // Special categories for dean evaluations
        $deanCategories = [
            [
                'name' => 'Departmental Leadership',
                'questions' => [
                    'The instructor contributes effectively to departmental goals',
                    'Shows strong leadership in curriculum development',
                    'Actively participates in departmental meetings and committees',
                    'Provides mentorship to junior faculty members'
                ]
            ],
            [
                'name' => 'Administrative Performance',
                'questions' => [
                    'Completes administrative duties in a timely manner',
                    'Maintains accurate and up-to-date course documentation',
                    'Adheres to university policies and procedures',
                    'Contributes to accreditation and quality assurance processes'
                ]
            ],
            [
                'name' => 'Professional Development',
                'questions' => [
                    'Demonstrates commitment to continuous professional improvement',
                    'Participates in relevant training and workshops',
                    'Contributes to academic research and publications',
                    'Engages with professional communities in their field'
                ]
            ]
        ];

        // Create regular instructor categories
        foreach ($commonCategories as $catIndex => $cat) {
            $category = EvaluationCategory::create([
                'name' => $cat['name'],
                'order' => $catIndex + 1
            ]);

            // Student questions for regular instructors
            foreach ($cat['questions'] as $i => $question) {
                EvaluationQuestion::create([
                    'category_id' => $category->id,
                    'question' => $question,
                    'order' => $i + 1,
                    'type' => EvaluationQuestion::TYPE_STUDENT,
                    'target_role' => EvaluationQuestion::TARGET_REGULAR_INSTRUCTOR
                ]);
            }

            // Peer questions for regular instructors
            foreach ($cat['questions'] as $i => $question) {
                EvaluationQuestion::create([
                    'category_id' => $category->id,
                    'question' => $question,
                    'order' => $i + 1,
                    'type' => EvaluationQuestion::TYPE_INSTRUCTOR,
                    'target_role' => EvaluationQuestion::TARGET_REGULAR_INSTRUCTOR
                ]);
            }
        }

        // Create lab assistant categories
        $labAssistantStartOrder = count($commonCategories) + 1;
        foreach ($labAssistantCategories as $catIndex => $cat) {
            $category = EvaluationCategory::create([
                'name' => $cat['name'],
                'order' => $labAssistantStartOrder + $catIndex
            ]);

            // Student questions for lab assistants
            foreach ($cat['questions'] as $i => $question) {
                EvaluationQuestion::create([
                    'category_id' => $category->id,
                    'question' => $question,
                    'order' => $i + 1,
                    'type' => EvaluationQuestion::TYPE_STUDENT,
                    'target_role' => EvaluationQuestion::TARGET_LAB_ASSISTANT
                ]);
            }

            // Peer questions for lab assistants
            foreach ($cat['questions'] as $i => $question) {
                EvaluationQuestion::create([
                    'category_id' => $category->id,
                    'question' => $question,
                    'order' => $i + 1,
                    'type' => EvaluationQuestion::TYPE_INSTRUCTOR,
                    'target_role' => EvaluationQuestion::TARGET_LAB_ASSISTANT
                ]);
            }
        }

        // Create dean categories
        $deanStartOrder = count($commonCategories) + count($labAssistantCategories) + 1;
        foreach ($deanCategories as $catIndex => $cat) {
            $category = EvaluationCategory::create([
                'name' => $cat['name'],
                'order' => $deanStartOrder + $catIndex
            ]);

            foreach ($cat['questions'] as $i => $question) {
                EvaluationQuestion::create([
                    'category_id' => $category->id,
                    'question' => $question,
                    'order' => $i + 1,
                    'type' => EvaluationQuestion::TYPE_DEAN,
                    'target_role' => EvaluationQuestion::TARGET_REGULAR_INSTRUCTOR // Or whichever is appropriate
                ]);
            }
        }

        // Create general questions that apply to all evaluator types and roles
        $generalCategory = EvaluationCategory::create([
            'name' => 'General Evaluation',
            'order' => 0
        ]);

        $generalQuestions = [
            'Overall satisfaction with the instructor\'s performance',
            'The instructor maintains a professional demeanor',
            'The instructor demonstrates respect for all students/colleagues',
            'Would you recommend this instructor to others?'
        ];

        foreach ($generalQuestions as $i => $question) {
            // For regular instructors
            EvaluationQuestion::create([
                'category_id' => $generalCategory->id,
                'question' => $question,
                'order' => $i + 1,
                'type' => EvaluationQuestion::TYPE_GENERAL,
                'target_role' => EvaluationQuestion::TARGET_REGULAR_INSTRUCTOR
            ]);

            // For lab assistants
            EvaluationQuestion::create([
                'category_id' => $generalCategory->id,
                'question' => $question,
                'order' => $i + 1,
                'type' => EvaluationQuestion::TYPE_GENERAL,
                'target_role' => EvaluationQuestion::TARGET_LAB_ASSISTANT
            ]);
        }
    }
}