<?php

namespace App\Http\Controllers;

use App\Models\EvaluationLink;  // ← THIS IS THE CRITICAL IMPORT
use App\Models\EvaluationResponse;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\StudentEvaluationNotification;
use Illuminate\Support\Facades\Notification; // Add this line
use Illuminate\Support\Facades\DB;  // <-- Add this line
use App\Models\Evaluator;
use Illuminate\Support\Str;



class EvaluationLinkController extends Controller
{
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instructor_id' => 'required|exists:instructors,id',
            'evaluator_ids' => 'required|array',
            'evaluator_ids.*' => 'required|exists:evaluators,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $createdLinks = [];
        $failedEvaluators = [];

        foreach ($request->evaluator_ids as $evaluatorId) {
            try {
                DB::beginTransaction();

                // Get evaluator details
                $evaluator = Evaluator::findOrFail($evaluatorId);

                // Create evaluation link
                $link = EvaluationLink::create([
                    'instructor_id' => $request->instructor_id,
                    'evaluator_id' => $evaluator->id,
                    'academic_year_id' => $request->academic_year_id,
                    'semester_id' => $request->semester_id,
                    'hash' => Str::random(60),
                    'is_used' => false,
                    // Removed type since it's already in evaluator table
                ]);

                $evaluationUrl = url("/evaluate/{$link->hash}");

                $createdLinks[] = [
                    'evaluator_id' => $evaluator->id,
                    'evaluator_type' => $evaluator->type, // Get type from evaluator
                    'evaluation_url' => $evaluationUrl,
                ];

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failedEvaluators[] = [
                    'evaluator_id' => $evaluatorId,
                    'error' => $e->getMessage()
                ];
                \Log::error("Failed to create evaluation for evaluator {$evaluatorId}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Evaluation links processed',
            'data' => [
                'successful_creations' => $createdLinks,
                'failed_creations' => $failedEvaluators,
            ]
        ], 201);
    }

public function groupedEvaluationLinks()
{
    $evaluations = EvaluationLink::query()
        ->select([
            'academic_year_id',
            'semester_id',
            'instructor_id',
            DB::raw('COUNT(*) as total_links'),
            DB::raw('SUM(is_used) as completed_evaluations')
        ])
        ->with(['academicYear', 'semester', 'instructor'])
        ->groupBy('academic_year_id', 'semester_id', 'instructor_id')
        ->get()
        ->map(function ($item) {
            return (object)[
                'academic_year_id' => $item->academicYear->id,
                'academic_year_name' => $item->academicYear->name,
                'semester_id' => $item->semester->id,
                'semester' => $item->semester->name,
                'instructor_id' => $item->instructor->id,
                'instructor' => $item->instructor->name,
                'total' => $item->total_links,
                'completed' => $item->completed_evaluations,
                'completion_rate' => round(($item->completed_evaluations / $item->total_links) * 100, 2)
            ];
        });

    return response()->json($evaluations);
}

public function getLink()
{
    $evaluations = evaluationLink::query()
        ->with(['instructor', 'academicYear', 'semester','evaluator'])
        ->get();


    return response()->json($evaluations);
}
}