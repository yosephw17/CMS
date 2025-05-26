<?php

namespace App\Http\Controllers;

use App\Models\LoadDistribution;
use App\Models\LoadDistributionResult;
use App\Models\Instructor;
use App\Models\Semester;
use App\Models\Year;
use App\Models\Course;
use App\Models\Department;
use App\Models\Assignment;
use App\Models\Result;
use App\Services\LoadCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoadDistributionController extends Controller
{
    protected $loadCalculationService;

    public function __construct(LoadCalculationService $loadCalculationService)
    {
        $this->loadCalculationService = $loadCalculationService;
    }

    public function index(Request $request)
    {
        $query = LoadDistribution::with(['instructor', 'instructor.role', 'year', 'semester', 'department', 'results', 'results.course']);
        if ($request->has('year') && $request->has('semester_id') && $request->has('department_id')) {
            $query->where('year', $request->year)
                  ->where('semester_id', $request->semester_id)
                  ->where('department_id', $request->department_id);
        }
        Log::info("message", $query);
        return $query->get();
    }

    public function list(Request $request)
    {
        $distributions = LoadDistribution::with(['year', 'semester', 'department'])
            ->select('year', 'semester_id', 'department_id')
            ->groupBy('year', 'semester_id', 'department_id')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'semester_id' => $item->semester_id,
                    'semester_name' => $item->semester->name,
                    'department_id' => $item->department_id,
                    'department_name' => $item->department->name,
                    'group_id' => "{$item->year_id}-{$item->semester_id}-{$item->department_id}",
                ];
            });

        return response()->json($distributions);
    }

public function create(Request $request)
{
    $request->validate([
        'year' => 'required|string',
    ]);

    $existing = LoadDistribution::where([
        'year' => $request->year,
        'semester_id' => $request->semester_id,
        'department_id' => $request->department_id,
    ])->first();

    if ($existing) {
        return response()->json(['message' => 'Load distribution already exists for this year, semester, and department'], 422);
    }

    return DB::transaction(function () use ($request) {
        // Create the LoadDistribution record
        $distribution = LoadDistribution::create([
            'year' => $request->year,
            'semester_id' => $request->semester_id,
            'department_id' => $request->department_id,
        ]);

        // Find matching assignments
        $assignments = Assignment::where('year', $request->year)
            ->where('semester_id', $request->semester_id)
            ->where('department_id', $request->department_id)
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No matching assignments found'], 404);
        }

        // Fetch results where is_assigned = true
        $results = Result::whereIn('assignment_id', $assignments->pluck('id'))
            ->where('is_assigned', true)
            ->with(['course', 'instructor', 'instructor.role', 'stream'])
            ->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No assigned results found for these assignments'], 404);
        }

        // Create LoadDistributionResult records
        $createdResults = [];
        foreach ($results as $result) {
            $resultData = [
                'instructor_id' => $result->instructor_id,
                'course_id' => $result->course_id,
                'section' => $result->stream ? "Stream: {$result->stream->name}" : 'Default',
                'students_count' => 30, // Adjust based on your logic
                'assignment_type' => $result->type,
                'lecture_hours' => $result->type === 'lecture' ? ($result->course->lecture_cp ?? 3) : 0,
                'lab_hours' => ($result->type === 'lab' || $result->type === 'lab_assistant') ? ($result->course->lab_cp ?? 2) : 0,
                'tutorial_hours' => $result->type === 'lecture' ? ($result->course->tut_cp ?? 2) : 0,
                'lecture_sections' => $result->type === 'lecture' ? 1 : 0,
                'lab_sections' => ($result->type === 'lab' || $result->type === 'lab_assistant') ? 1 : 0,
                'tutorial_sections' => $result->type === 'lecture' ? 1 : 0,
            ];
            Log::info("message", $resultData);

            // Calculate base ELH from service
            $elh = $this->loadCalculationService->calculateELH($resultData);

            // Apply studying level multiplier here (not in service)
            $studyingLevel = strtolower($result->instructor->studying ?? '');
            if ($studyingLevel === 'phd') {
                $elh *= 2;
            } elseif ($studyingLevel === 'masters') {
                $elh *= 1.5;
            }

            // Get instructor's standard load based on role
            $standardLoad = $result->instructor->role->load ?? 0;
            
            // Calculate total ELH (after applying studying multiplier)
            $totalElh = $elh;
            
            // Calculate over/under load
            $overUnderLoad = $totalElh - $standardLoad;
            
            // Calculate payment (only pay for over load)
            $amountPaid = $overUnderLoad > 0 ? $overUnderLoad * 1000 : 0;

            $resultRecord = LoadDistributionResult::create([
                'load_distribution_id' => $distribution->id,
                'instructor_id' => $resultData['instructor_id'],
                'course_id' => $resultData['course_id'],
                'section' => $resultData['section'],
                'students_count' => $resultData['students_count'],
                'assignment_type' => $resultData['assignment_type'],
                'lecture_hours' => $resultData['lecture_hours'],
                'lab_hours' => $resultData['lab_hours'],
                'tutorial_hours' => $resultData['tutorial_hours'],
                'lecture_sections' => $resultData['lecture_sections'],
                'lab_sections' => $resultData['lab_sections'],
                'tutorial_sections' => $resultData['tutorial_sections'],
                'elh' => $elh,
                'total_load' => $totalElh,
                'over_under_load' => $overUnderLoad,
                'amount_paid' => $amountPaid,
                'expected_load' => $standardLoad, // Add this line to store expected load
            ]);

            $createdResults[] = $resultRecord;
        }

        return response()->json([
            'load_distribution' => $distribution,
            'results' => $createdResults,
        ], 201);
    });
}
 public function getResults(Request $request)
    {
        $request->validate([
            'year' => 'required|string',
            'semester_id' => 'required|exists:semesters,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $distribution = LoadDistribution::where([
            'year' => $request->year,
            'semester_id' => $request->semester_id,
            'department_id' => $request->department_id,
        ])->first();

        if (!$distribution) {
            return response()->json(['message' => 'Load distribution not found'], 404);
        }

        $results = LoadDistributionResult::where('load_distribution_id', $distribution->id)
            ->with([
              'instructor' => function ($query) {
    $query->select('id', 'name', 'role_id')->with('role:id,name,load');
},

                'course' => function ($query) {
                    $query->select('id', 'name', 'course_code','cp');
                },
            ])
            ->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No results found for this distribution'], 404);
        }

        return response()->json($results);
    }
    public function generate(Request $request)
    {
        $request->validate([
            'year_id' => 'required|exists:years,id',
            'semester_id' => 'required|exists:semesters,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $year = Year::findOrFail($request->year_id);
        $distributions = LoadDistribution::where([
            'year_id' => $request->year_id,
            'semester_id' => $request->semester_id,
            'department_id' => $request->department_id,
        ])->with('instructor', 'instructor.role')->get();

        if ($distributions->isEmpty()) {
            return response()->json(['message' => 'No load distributions found for this year, semester, and department'], 404);
        }

        $assignments = Assignment::where('year', $year->name)
            ->where('semester_id', $request->semester_id)
            ->where('department_id', $request->department_id)
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No matching assignments found'], 404);
        }

        $createdResults = DB::transaction(function () use ($distributions, $assignments, $request) {
            $created = [];
            foreach ($distributions as $distribution) {
                $distribution->results()->delete(); // Clear existing results

                $results = Result::whereIn('assignment_id', $assignments->pluck('id'))
                    ->where('instructor_id', $distribution->instructor_id)
                    ->where('is_assigned', true)
                    ->with(['course', 'stream'])
                    ->get();

                $totalElh = 0;
                foreach ($results as $result) {
                    $resultData = [
                        'course_id' => $result->course_id,
                        'section' => $result->stream ? "Stream: {$result->stream->name}" : 'Default',
                        'students_count' => 30,
                        'assignment_type' => $result->type,
                        'lecture_hours' => $result->type === 'lecture' ? ($result->course->credit_points ?? 3) : 0,
                        'lab_hours' => $result->type === 'lab' ? ($result->course->credit_points ?? 2) : 0,
                        'tutorial_hours' => $result->type === 'lecture' ? 1 : 0,
                        'lecture_sections' => $result->type === 'lecture' ? 1 : 0,
                        'lab_sections' => $result->type === 'lab' ? 1 : 0,
                        'tutorial_sections' => $result->type === 'lecture' ? 1 : 0,
                    ];

                    $elh = $this->loadCalculationService->calculateELH($resultData);
                    $totalElh += $elh;

                    $resultRecord = LoadDistributionResult::create([
                        'load_distribution_id' => $distribution->id,
                        'course_id' => $resultData['course_id'],
                        'section' => $resultData['section'],
                        'students_count' => $resultData['students_count'],
                        'assignment_type' => $resultData['assignment_type'],
                        'lecture_hours' => $resultData['lecture_hours'],
                        'lab_hours' => $resultData['lab_hours'],
                        'tutorial_hours' => $resultData['tutorial_hours'],
                        'lecture_sections' => $resultData['lecture_sections'],
                        'lab_sections' => $resultData['lab_sections'],
                        'tutorial_sections' => $resultData['tutorial_sections'],
                        'elh' => $elh,
                        'total_load' => $totalElh,
                        'over_under_load' => $totalElh - $distribution->instructor->role->load,
                        'amount_paid' => $totalElh > $distribution->instructor->role->load ? ($totalElh - $distribution->instructor->role->load) * 1000 : 0,
                    ]);

                    $created[] = $resultRecord;
                }
            }
            return $created;
        });

        return response()->json($createdResults);
    }
}