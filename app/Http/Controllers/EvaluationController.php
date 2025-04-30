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
    $link = EvaluationLink::with('instructor')
           ->where('hash', $hash)
           ->first();

    // Case 1: Link doesn't exist
    if (!$link) {
        return response()->json([
            'status' => 'invalid',
            'message' => 'Invalid evaluation link',
            'ui' => [
                'icon' => 'error',
                'color' => 'red-500',
                'action' => null
            ]
        ], 404);
    }

    // Case 2: Link has been used (is_used = true)
    if ($link->is_used) {
        return response()->json([
            'status' => 'used',
            'message' => 'You already submitted this evaluation',
            'details' => [
                'instructor' => $link->instructor->name,
                'submitted_at' => $link->updated_at->diffForHumans()
            ],
            'ui' => [
                'icon' => 'check-circle',
                'color' => 'green-500',
                'action' => [
                    'text' => 'View Receipt',
                    'url' => "#" // Replace with actual route
                ]
            ]
        ], 200);
    }

    // Case 3: Valid unused link (is_used = false)
    return response()->json([
        'status' => 'active',
        'data' => [
            'instructor' => $link->instructor,
            'student' => $link->student_name,
            'categories' => EvaluationCategory::with(['questions' => function($q) {
                $q->orderBy('order');
            }])->orderBy('order')->get()
        ],
        'ui' => [
            'icon' => 'edit',
            'color' => 'blue-500',
            'action' => [
                'text' => 'Begin Evaluation',
                'url' => "#" // Replace with actual route
            ]
        ]
    ]);
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
            'academic_year_id' => 'required|exists:academic_years,id',  // Changed from exists validation
            'semester_id' => 'required|exists:semesters,id',      // Changed from exists validation
            'instructor_id' => 'required|exists:instructors,id'
        ]);

        // Get all evaluation data with relationships
        $links = EvaluationLink::with([
                'responses.question',
                'academicYear',
                'semester',
                'instructor'
            ])
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('instructor_id', $request->instructor_id)
            ->whereHas('responses') // This now uses the corrected relationship
            ->get();

        if ($links->isEmpty()) {
            return response()->json([
                'message' => 'No evaluation responses found',
                'data' => []
            ], 404);
        }

        // Format responses by student then by category
        $studentResponses = $links->map(function($link) {
            return [
                'student' => [
                    'name' => $link->student_name,
                    'email' => $link->student_email
                ],
                'categories' => $link->responses
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

        // Calculate statistics
        $allRatings = $links->flatMap->responses->pluck('rating');
        $academicYear = AcademicYear::where('id', $validated['academic_year_id'])->first();
        $semester = Semester::where('id', $validated['semester_id'])->first();
        // Returns either the first matching model or null
        //
         return response()->json([
            'meta' => [
                'academic_year' => $academicYear,
                'semester' => $semester,
                'instructor' => $links->first()->instructor->name,
                'total_students' => $links->count(),
                'total_responses' => $allRatings->count(),
                'average_rating' => $allRatings->avg(),
                'rating_distribution' => [
                    '5' => $allRatings->filter(fn($r) => $r == 5)->count(),
                    '4' => $allRatings->filter(fn($r) => $r == 4)->count(),
                    '3' => $allRatings->filter(fn($r) => $r == 3)->count(),
                    '2' => $allRatings->filter(fn($r) => $r == 2)->count(),
                    '1' => $allRatings->filter(fn($r) => $r == 1)->count(),
                    '0' => $allRatings->filter(fn($r) => $r == 0)->count()
                ]
            ],
            'data' => $studentResponses
        ]);
    }
}