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


    public function getForm($hash)
    {
        $link = QualityLink::with(['instructor', 'auditSession', 'semester', 'academicYear'])
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
                'questions' => QualityQuestion::orderBy('id')->get()->map(function ($question) {
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




    public function getAllResponses()
{
    $responses = QualityResponse::with([
            'qualityLink.instructor:id,name,email',
            'qualityLink.auditSession:id,name',
            'qualityLink.semester:id,name',
            'qualityLink.academicYear:id,name',
            'question:id,question_text,input_type'
        ])
        ->whereHas('qualityLink', function($q) {
            $q->where('is_used', true);
        })
        ->get()
        ->groupBy('quality_link_id')
        ->map(function ($group, $linkId) {
            $first = $group->first();

            return [
                'id' => $linkId,
                'instructor' => [
                    'id' => (string)$first->qualityLink->instructor->id,
                    'name' => $first->qualityLink->instructor->name,
                    'email' => $first->qualityLink->instructor->email
                ],
                'academic_year' => $first->qualityLink->academicYear->name,
                'semester' => $first->qualityLink->semester->name,
                'audit_session' => $first->qualityLink->auditSession->name,
                'submitted_at' => $first->created_at->toIso8601String(),
                'responses' => $group->map(function ($response) {
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
        })
        ->values();

    return response()->json($responses);
}


}

