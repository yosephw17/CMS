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



class EvaluationLinkController extends Controller
{
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instructor_id' => 'required|exists:instructors,id',
            'students' => 'required|array',
            'students.*.name' => 'required|string',
            'students.*.email' => 'required|email',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $createdLinks = [];
        $failedStudents = [];

        foreach ($request->students as $student) {
            try {
                $link = EvaluationLink::create([
                    'instructor_id' => $request->instructor_id,
                    'academic_year_id' => $request->academic_year_id,
                    'student_name' => $student['name'],
                    'student_email' => $student['email'],
                    'semester_id' => $request->semester_id,
                    'hash' => \Illuminate\Support\Str::random(60),
                ]);

                $evaluationUrl = url("/evaluate/{$link->hash}");
                $createdLinks[] = [
                    'name' => $student['name'],
                    'email' => $student['email'],
                    'evaluation_url' => $evaluationUrl,
                ];

                // Uncomment to send emails
                // Notification::route('mail', $student['email'])
                //     ->notify(new StudentEvaluationNotification(
                //         $student['name'],
                //         $evaluationUrl,
                //         $link->expires_at
                //     ));
            } catch (\Exception $e) {
                $failedStudents[] = [
                    'name' => $student['name'],
                    'email' => $student['email'],
                    'error' => $e->getMessage()
                ];
                \Log::error("Failed to create evaluation for {$student['email']}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Evaluation links processed',
            'data' => [
                'successful_creations' => $createdLinks,
                'failed_creations' => $failedStudents,
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
        ->with(['instructor'])
        ->get();


    return response()->json($evaluations);
}
}