<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluationLink;          // ← Missing import
use App\Models\EvaluationCategory;     // ← Missing import
use App\Models\EvaluationResponse;
use App\Models\AcademicYear;
use App\Models\Semester;



     // ← Missing import

class EvaluationController extends Controller
{
    public function getForm($hash)
    {
        $link = EvaluationLink::with(['instructor.role', 'evaluator'])
               ->where('hash', $hash)
               ->first();

        // Case 1: Link doesn't exist
        if (!$link) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid evaluation link'
            ], 404);
        }

        // Case 2: Link has been used
        if ($link->is_used) {
            return response()->json([
                'status' => 'used',
                'message' => 'You already submitted this evaluation',
                'details' => [
                    'instructor' => $link->instructor->name,
                    'evaluator' => $link->evaluator->name,
                    'evaluator_type' => $link->evaluator->type,
                    'submitted_at' => $link->updated_at->format('Y-m-d H:i:s')
                ]
            ], 200);
        }

        // Determine instructor role and evaluator type
        $isLabAssistant = $link->instructor->role->name === 'lab_assistance';
        $evaluatorType = $link->evaluator->type;

        // Build form response
        $formData = [
            'status' => 'active',
            'data' => [
                'instructor' => [
                    'id' => $link->instructor->id,
                    'name' => $link->instructor->name,
                    'role' => $link->instructor->role->name,
                    'department' => $link->instructor->department->name
                ],
                'evaluator' => [
                    'id' => $link->evaluator->id,
                    'name' => $link->evaluator->name,
                    'type' => $evaluatorType
                ],
                'categories' => EvaluationCategory::with(['questions' => function($q) use ($evaluatorType, $isLabAssistant) {
                    $q->orderBy('order');

                    // Filter by evaluator type only (remove 'general' from the whereIn clause)
                    switch ($evaluatorType) {
                        case 'student':
                            $q->where('type', 'student');
                            break;
                        case 'instructor':
                            $q->where('type', 'instructor');
                            break;
                        case 'dean':
                            $q->where('type', 'dean');
                            break;
                    }

                    // Filter by instructor role
                    $q->where('target_role', $isLabAssistant ? 'lab_assistant' : 'regular_instructor');
                }])->orderBy('order')->get()
                ->filter(function($category) {
                    return $category->questions->count() > 0; // Only include categories with questions
                })
                ->values() // Reset array keys after filtering
            ]
        ];

        // Set form type and instructions based on both role and evaluator
        if ($evaluatorType === 'student') {
            $formData['data']['form_type'] = $isLabAssistant
                ? 'lab_assistant_student_evaluation'
                : 'regular_student_evaluation';
            $formData['data']['instructions'] = $isLabAssistant
                ? 'Please evaluate your lab assistant'
                : 'Please evaluate your instructor';
        }
        elseif ($evaluatorType === 'instructor') {
            $formData['data']['form_type'] = $isLabAssistant
                ? 'lab_assistant_peer_review'
                : 'regular_peer_review';
            $formData['data']['instructions'] = $isLabAssistant
                ? 'Please provide feedback about your lab assistant colleague'
                : 'Please provide feedback about your instructor colleague';
        }
        elseif ($evaluatorType === 'dean') {
            $formData['data']['form_type'] = 'dean_evaluation';
            $formData['data']['instructions'] = 'Administrative evaluation';
            $formData['data']['confidential'] = true;
        }

        return response()->json($formData);
    }

    public function submit(Request $request, $hash)
    {
        $link = EvaluationLink::where('hash', $hash)
               ->where('is_used', false)
               ->firstOrFail();

        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:evaluation_questions,id',
            'responses.*.rating' => 'required|integer|min:0|max:5'
        ]);

        // Optimized mass insertion
        $responses = collect($validated['responses'])->map(function ($response) use ($link) {
            return [
                'link_id' => $link->id,
                'question_id' => $response['question_id'],
                'rating' => $response['rating'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        EvaluationResponse::insert($responses->toArray());
        $link->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation submitted successfully'
        ]);
    }

    public function getGroupResponses(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'instructor_id' => 'required|exists:instructors,id'
        ]);

        // Get all evaluation data with relationships
        $student_links = EvaluationLink::with([
                'responses.question.category', // Load question with its category
                'academicYear',
                'semester',
                'instructor',
                'evaluator' // Load evaluator relationship
            ])
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('instructor_id', $request->instructor_id)
            ->whereHas('responses.question', function($q) {
                $q->where('type', 'student'); // Only questions marked for students
            })
            ->get();

        if ($student_links->isEmpty()) {
            return response()->json([
                'message' => 'No evaluation responses found',
                'data' => []
            ], 404);
        }

        $colleague_links = EvaluationLink::with([
                'responses.question.category', // Load question with its category
                'academicYear',
                'semester',
                'instructor',
                'evaluator' // Load evaluator relationship
            ])
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('instructor_id', $request->instructor_id)
            ->whereHas('responses.question', function($q) {
                $q->where('type', 'instructor'); // Only questions marked for colleagues
            })
            ->get();

            $dean_links = EvaluationLink::with([
                'responses.question.category', // Load question with its category
                'academicYear',
                'semester',
                'instructor',
                'evaluator' // Load evaluator relationship
            ])
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('instructor_id', $request->instructor_id)
            ->whereHas('responses.question', function($q) {
                $q->where('type', 'dean'); // Only questions marked for dean
            })
            ->get();

        // Format responses by student then by category
        $studentResponses = $student_links->map(function($link) {
            return [
                'student' => [
                    'name' => $link->evaluator->name, // Now using evaluator instead of student_name
                    'email' => $link->evaluator->email // Now using evaluator instead of student_email
                ],
                'categories' => $link->responses
                    ->filter(fn($response) => $response->question->type === 'student') // Additional filter
                    ->groupBy('question.category.name')
                    ->map(function($responses, $categoryName) {
                        return [
                            'category' => $categoryName,
                            'answers' => $responses->map(function($response) {
                                return [
                                    'question_id' => $response->question_id,
                                    'question' => $response->question->question,
                                    'rating' => $response->rating,
                                    'answered_at' => $response->created_at
                                ];
                            })
                        ];
                    })->values()
            ];
        });

        $colleagueResponses = $colleague_links->map(function($link) {
            return [
                'colleague' => [
                    'name' => $link->evaluator->name,
                    'email' => $link->evaluator->email
                ],
                'categories' => $link->responses
                    ->filter(fn($response) => $response->question->type === 'instructor') // Additional filter
                    ->groupBy('question.category.name')
                    ->map(function($responses, $categoryName) {
                        return [
                            'category' => $categoryName,
                            'answers' => $responses->map(function($response) {
                                return [
                                    'question_id' => $response->question_id,
                                    'question' => $response->question->question,
                                    'rating' => $response->rating,
                                    'answered_at' => $response->created_at
                                ];
                            })
                        ];
                    })->values()
            ];
        });

        $deanResponses = $dean_links->map(function($link) {
            return [
                'dean' => [
                    'name' => $link->instructor->name,
                    'email' => $link->instructor->email
                ],
                'categories' => $link->responses
                    ->filter(fn($response) => $response->question->type === 'dean') // Additional filter
                    ->groupBy('question.category.name')
                    ->map(function($responses, $categoryName) {
                        return [
                            'category' => $categoryName,
                            'answers' => $responses->map(function($response) {
                                return [
                                    'question_id' => $response->question_id,
                                    'question' => $response->question->question,
                                    'rating' => $response->rating,
                                    'answered_at' => $response->created_at
                                ];
                            })
                        ];
                    })->values()
            ];
        });

        // Calculate statistics (only for student-type questions)
        $allRatings = $student_links->flatMap(function($link) {
            return $link->responses->filter(fn($r) => $r->question->type === 'student')->pluck('rating');
        });

        $academicYear = AcademicYear::find($validated['academic_year_id']);
        $semester = Semester::find($validated['semester_id']);

        return response()->json([
            'meta' => [
                'academic_year' => $academicYear,
                'semester' => $semester,
                'instructor' => $student_links->first()->instructor->name,
                'total_students' => $student_links->count(),
            'total_responses' => $allRatings->count(),
                'average_rating' => $allRatings->avg(),
            ],
            'data' => $studentResponses,
            'colleague_responses' => [
                'responses' => $colleagueResponses,
                'total_colleagues' => $colleague_links->count(),
            ],
            'dean_responses' => [
                'responses' => $deanResponses,
                'total_deans' => $dean_links->count(),
            ],
        ]);

    }
}