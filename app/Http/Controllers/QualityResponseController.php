<?php

namespace App\Http\Controllers;

use App\Models\QualityLink;
use App\Models\QualityResponse;
use App\Models\QualityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class QualityResponseController extends Controller
{
    /**
     * Submit quality assessment responses for a given link hash.
     *
     * @param Request $request
     * @param string $hash
     * @return JsonResponse
     */
    public function submit(Request $request, $hash): JsonResponse
    {
        try {
            // Fetch the quality link with instructor, ensuring it's unused
            $link = QualityLink::with('instructor')
                ->where('hash', $hash)
                ->where('is_used', false)
                ->firstOrFail();

            // Validate the request
            $validated = $request->validate([
                'responses' => 'required|array|min:1',
                'responses.*.question_id' => 'required|exists:quality_questions,id',
                'responses.*.answer' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $index = explode('.', $attribute)[1];
                        $questionId = $request->input("responses.{$index}.question_id");
                        $question = QualityQuestion::find($questionId);

                        if (!$question) {
                            $fail("The question ID {$questionId} is invalid.");
                            return;
                        }

                        // Validate answer based on question type
                        if ($question->type === 'numeric' && !is_numeric($value) && !is_null($value)) {
                            $fail("The {$attribute} must be a numeric value for question ID {$questionId}.");
                        } elseif ($question->type === 'text' && !is_string($value) && !is_null($value)) {
                            $fail("The {$attribute} must be a string for question ID {$questionId}.");
                        }
                    },
                ],
            ]);

            // Log the validated data for debugging
            Log::debug('Validated Responses:', $validated['responses']);

            // Process responses within a transaction
            DB::transaction(function () use ($link, $validated) {
                // Prepare responses for insertion
                $responses = array_map(function ($response) use ($link) {
                    // Convert answer to string, handling arrays and other types
                    $answer = $this->formatAnswer($response['answer']);

                    return [
                        'quality_link_id' => $link->id,
                        'question_id' => $response['question_id'],
                        'answer' => $answer,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $validated['responses']);

                // Insert responses
                QualityResponse::insert($responses);

                // Mark the link as used
                $link->update(['is_used' => true]);
            });

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Assessment submitted successfully',
                'instructor' => $link->instructor->name,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or used link',
            ], 404);
        } catch (\Exception $e) {
            // Log the unexpected error
            Log::error('Error submitting assessment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the assessment',
            ], 500);
        }
    }

    /**
     * Format the answer to ensure it can be stored as a string.
     *
     * @param mixed $answer
     * @return string
     */
    private function formatAnswer($answer): string
    {
        if (is_array($answer)) {
            return json_encode($answer); // Serialize arrays to JSON
        } elseif (is_null($answer)) {
            return ''; // Convert null to empty string
        } elseif (is_numeric($answer)) {
            return (string) $answer; // Ensure numbers are strings
        }

        return $answer; // Return strings as-is
    }

    /**
     * Get quality assessment form data for a given link hash.
     *
     * @param string $hash
     * @return JsonResponse
     */
    public function getForm($hash)
    {
        $link = QualityLink::with(['instructor', 'auditSession', 'semester', 'academicYear', 'evaluator'])
            ->where('hash', $hash)
            ->first();

        // Case 1: Link doesn't exist
        if (!$link) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid quality assessment link'
            ], 404);
        }

        // Case 2: Link has been used
        if ($link->is_used) {
            return response()->json([
                'status' => 'used',
                'message' => 'This quality assessment has already been submitted',
                'details' => [
                    'instructor' => $link->instructor->name,
                    'submitted_at' => $link->updated_at->diffForHumans(),
                    'session' => $link->auditSession->name,
                    'academic_year' => $link->academicYear->name,
                    'semester' => $link->semester->name
                ]
            ], 200);
        }

        // Case 3: Valid unused link
        return response()->json([
            'status' => 'active',
            'data' => [
                'instructor' => $link->instructor->only(['id', 'name', 'email']),
                'session' => $link->auditSession->only(['id', 'name']),
                'academic_year' => $link->academicYear->only(['id', 'name']),
                'semester' => $link->semester->only(['id', 'name']),
                'evaluator' => $link->evaluator ? $link->evaluator->only(['id', 'name', 'email']) : null,
                'is_self_evaluation' => $link->is_self_evaluation,
                'questions' => QualityQuestion::where('audience', $link->is_self_evaluation ? 'instructor' : 'student')
                    ->orderBy('id')
                    ->get()
                    ->map(function ($question) {
                        $options = null;

                        if (in_array($question->input_type, ['dropdown', 'checkbox'])) {
                            try {
                                $options = is_string($question->options)
                                    ? json_decode($question->options, true)
                                    : (is_array($question->options) ? $question->options : []);
                            } catch (\Exception $e) {
                                $options = [];
                                \Log::error("Failed to decode options for question {$question->id}: " . $e->getMessage());
                            }
                        }

                        return [
                            'id' => $question->id,
                            'text' => $question->question_text,
                            'type' => $question->input_type,
                            'options' => $options
                        ];
                    })
            ]
        ]);
    }

    /**
     * Get all quality responses grouped by instructor, academic year, semester, and department.
     *
     * @return JsonResponse
     */
    public function getAllResponses(): JsonResponse
    {
        try {
            $responses = QualityResponse::with([
                'qualityLink.instructor:id,name,email',
                'qualityLink.evaluator:id,name,email,section',
                'qualityLink.auditSession:id,name',
                'qualityLink.semester:id,name',
                'qualityLink.academicYear:id,name',
                'qualityLink.department:id,name',
                'question:id,question_text,input_type',
            ])
            ->whereHas('qualityLink', function ($q) {
                $q->where('is_used', true);
            })
            ->get();

            // Log raw responses for debugging
            Log::debug('Raw quality responses for getAllResponses', [
                'count' => $responses->count(),
                'data' => $responses->map(function ($response) {
                    $department = $response->qualityLink?->department;
                    return [
                        'response_id' => $response->id,
                        'quality_link_id' => $response->quality_link_id,
                        'instructor_id' => $response->qualityLink?->instructor_id,
                        'instructor' => $response->qualityLink?->instructor ? [
                            'id' => $response->qualityLink->instructor->id,
                            'name' => $response->qualityLink->instructor->name,
                            'email' => $response->qualityLink->instructor->email
                        ] : null,
                        'academic_year_id' => $response->qualityLink?->academic_year_id,
                        'academic_year' => $response->qualityLink?->academicYear ? [
                            'id' => $response->qualityLink->academicYear->id,
                            'name' => $response->qualityLink->academicYear->name
                        ] : null,
                        'semester_id' => $response->qualityLink?->semester_id,
                        'semester' => $response->qualityLink?->semester ? [
                            'id' => $response->qualityLink->semester->id,
                            'name' => $response->qualityLink->semester->name,
                        ] : null,
                        'department_id' => $response->qualityLink?->department_id,
                        'department' => is_object($department) ? [
                            'id' => $department->id,
                            'name' => $department->name
                        ] : null,
                        'question_id' => $response->question_id,
                        'answer' => $response->getRawOriginal('answer'),
                        'is_self_evaluation' => $response->qualityLink?->is_self_evaluation,
                        'is_used' => $response->qualityLink?->is_used
                    ];
                })->toArray()
            ]);

            $groupedResponses = $responses
                ->groupBy(function ($response) {
                    if (!$response->qualityLink || !$response->qualityLink->instructor || !$response->qualityLink->academicYear || !$response->qualityLink->semester) {
                        Log::warning('Invalid grouping data for response', [
                            'response_id' => $response->id,
                            'quality_link_id' => $response->quality_link_id,
                            'instructor_type' => gettype($response->qualityLink?->instructor),
                            'academic_year_type' => gettype($response->qualityLink?->academicYear),
                            'semester_type' => gettype($response->qualityLink?->semester)
                        ]);
                        return 'invalid';
                    }
                    // Create a composite key for grouping by instructor, academic year, semester
                    return implode(':', [
                        $response->qualityLink->instructor->id,
                        $response->qualityLink->academicYear->name,
                        $response->qualityLink->semester->name,
                    ]);
                })
                ->map(function ($group, $key) {
                    if ($key === 'invalid') {
                        return null; // Skip invalid groups
                    }

                    // Extract instructor, academic year, semester from the first response
                    $first = $group->first();
                    $instructor = [
                        'id' => (string)$first->qualityLink->instructor->id,
                        'name' => $first->qualityLink->instructor->name,
                        'email' => $first->qualityLink->instructor->email
                    ];
                    $academicYear = $first->qualityLink->academicYear->name;
                    $semester = $first->qualityLink->semester->name;

                    // Group responses by quality_link_id to maintain individual submissions
                    $submissions = $group->groupBy('quality_link_id')->map(function ($submissionGroup, $linkId) {
                        $firstSubmission = $submissionGroup->first();

                        // Validate suspicious answers
                        if ($firstSubmission->question->input_type === 'text' && is_numeric($firstSubmission->getRawOriginal('answer'))) {
                            Log::warning('Suspicious text answer', [
                                'response_id' => $firstSubmission->id,
                                'question_id' => $firstSubmission->question_id,
                                'answer' => $firstSubmission->getRawOriginal('answer')
                            ]);
                        }

                        // Validate section-answer mismatch for question ID 18
                        if ($firstSubmission->question_id === 18 && $firstSubmission->getRawOriginal('answer') && $firstSubmission->qualityLink->section) {
                            $rawAnswer = $firstSubmission->getRawOriginal('answer');
                            $answerSection = is_array($rawAnswer) ? $rawAnswer[0] : $rawAnswer;
                            if ($answerSection !== $firstSubmission->qualityLink->section) {
                                Log::warning('Section-answer mismatch', [
                                    'response_id' => $firstSubmission->id,
                                    'question_id' => $firstSubmission->question_id,
                                    'answer_section' => $answerSection,
                                    'quality_link_section' => $firstSubmission->qualityLink->section
                                ]);
                            }
                        }

                        return [
                            'id' => $linkId,
                            'evaluator' => $firstSubmission->qualityLink->is_self_evaluation
                                ? null
                                : ($firstSubmission->qualityLink->evaluator
                                    ? [
                                        'id' => (string)$firstSubmission->qualityLink->evaluator->id,
                                        'name' => $firstSubmission->qualityLink->evaluator->name,
                                        'email' => $firstSubmission->qualityLink->evaluator->email,
                                        'section' => $firstSubmission->qualityLink->evaluator->section
                                    ]
                                    : null),
                            'is_self_evaluation' => $firstSubmission->qualityLink->is_self_evaluation,
                            'audit_session' => $firstSubmission->qualityLink->auditSession->name,
                            'submitted_at' => $firstSubmission->created_at->toIso8601String(),
                            'responses' => $submissionGroup->map(function ($response) {
                                // Get the raw answer from database without any processing
                                $rawAnswer = $response->getRawOriginal('answer');

                                // Format based on question type
                                $formattedAnswer = match($response->question->input_type) {
                                    'dropdown' => is_array($rawAnswer) ? json_encode($rawAnswer) : $rawAnswer,
                                    'textarea' => $rawAnswer ?? '', // Ensure empty string instead of null
                                    default => (string)$rawAnswer
                                };

                                return [
                                    'question' => $response->question->question_text,
                                    'type' => $response->question->input_type,
                                    'answer' => $formattedAnswer
                                ];
                            })->values()
                        ];
                    })->values();

                    return [
                        'instructor' => $instructor,
                        'academic_year' => $academicYear,
                        'semester' => $semester,
                        'submissions' => $submissions
                    ];
                })
                ->filter() // Remove null entries from invalid groups
                ->values();

            // Log grouped responses
            Log::info('Grouped quality responses for getAllResponses', [
                'count' => $groupedResponses->count(),
                'structure' => $groupedResponses->map(function ($group) {
                    return [
                        'instructor' => $group['instructor']['name'],
                        'academic_year' => $group['academic_year'],
                        'semester' => $group['semester'],
                        'submission_count' => count($group['submissions'])
                    ];
                })->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $groupedResponses
            ]);

        } catch (\Exception $e) {
            Log::error('Quality Response Error in getAllResponses: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch responses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quality responses grouped by section for self-evaluations.
     *
     * @return JsonResponse
     */
    public function getGroupedResponses(Request $request)
    {
        // Fetch quality responses with related data using eager loading
        $responses = QualityResponse::with([
            'qualityLink.instructor',
            'qualityLink.course',
            'qualityLink.semester',
            'qualityLink.academicYear',
            'qualityLink.department',
            'qualityLink.auditSession',
            'question'
        ])->get();

        // Group responses by academic_year, semester, audit_session, department, and section
        $groupedData = $responses->groupBy(function ($response) {
            $qualityLink = $response->qualityLink;
            return $qualityLink->academicYear->name . '|' .
                   $qualityLink->semester->name . '|' .
                   $qualityLink->auditSession->name . '|' .
                   $qualityLink->department->name . '|' .
                   $qualityLink->section;
        })->map(function ($group) {
            // Group by instructor within each group
            $instructors = $group->groupBy('qualityLink.instructor_id')->map(function ($instructorResponses) {
                $firstResponse = $instructorResponses->first();
                $qualityLink = $firstResponse->qualityLink;
                $instructor = $qualityLink->instructor;
                $course = $qualityLink->course;

                // Organize responses by question
                $responses = $instructorResponses->mapWithKeys(function ($response) {
                    return [$response->question->question_text => $response->answer];
                })->toArray();

                // Calculate percentages
                $chaptersTotal = $responses['Total Number of Chapters in the Course'] ?? 0;
                $chaptersCovered = $responses['Total Number of Chapters Covered'] ?? 0;
                $assessmentsTotal = $responses['Total number of assessments in the course'] ?? 0;
                $assessmentsDelivered = $responses['Total number of assessments delivered'] ?? 0;
                $feedbackGiven = $responses['Total number of feedback given back to students'] ?? 0;

                // Avoid division by zero
                $chapterCompletion = $chaptersTotal > 0 ? round(($chaptersCovered / $chaptersTotal) * 100, 2) : 0;
                $assessmentDelivery = $assessmentsTotal > 0 ? round(($assessmentsDelivered / $assessmentsTotal) * 100, 2) : 0;
                $feedbackPercentage = $assessmentsTotal > 0 ? round(($feedbackGiven / $assessmentsTotal) * 100, 2) : 0;

                // Add calculated percentages to responses
                $responses['Chapter Completion (%)'] = $chapterCompletion;
                $responses['Assessment Delivery (%)'] = $assessmentDelivery;
                $responses['Feedback Percentage (%)'] = $feedbackPercentage;

                return [
                    'instructor' => [
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                        'email' => $instructor->email,
                    ],
                    'course' => [
                        'id' => $course->id,
                        'name' => $course->name,
                    ],
                    'responses' => $responses,
                ];
            })->values();

            // Extract group keys
            $keys = explode('|', $group->first()->qualityLink->academicYear->name . '|' .
                            $group->first()->qualityLink->semester->name . '|' .
                            $group->first()->qualityLink->auditSession->name . '|' .
                            $group->first()->qualityLink->department->name . '|' .
                            $group->first()->qualityLink->section);

            return [
                'academic_year' => $keys[0],
                'semester' => $keys[1],
                'audit_session' => $keys[2],
                'department' => $keys[3],
                'section' => $keys[4],
                'instructors' => $instructors,
            ];
        })->values();

        return response()->json($groupedData);
    }

}