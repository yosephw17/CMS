<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\QualityAssuranceEvaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QualityAssuranceEvaluatorController extends Controller
{
    /**
     * Validation rules
     */
    private function validationRules($forUpdate = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('quality_assurance_evaluators')
                    ->where(function ($query) use ($forUpdate) {
                        return $query->where('instructor_id', request('instructor_id'))
                                    ->where('semester_id', request('semester_id'))
                                    ->where('academic_year_id', request('academic_year_id'))
                                    ->where('audit_session_id', request('audit_session_id'))
                                    ->where('section', request('section'));
                    })
                    ->ignore($forUpdate ? request()->route('quality_assurance_evaluator') : null)
            ],
            'instructor_id' => 'required|exists:instructors,id',
            'semester_id' => 'required|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'audit_session_id' => 'required|exists:audit_sessions,id',
            'section' => 'required|string|max:255',
        ];

        if ($forUpdate) {
            foreach ($rules as $field => $rule) {
                if (!is_array($rule)) {
                    $rules[$field] = 'sometimes|'.$rule;
                }
            }
        }

        return $rules;
    }

    /**
     * Display a listing of evaluators
     */
    public function index(): JsonResponse
    {
        $evaluators = QualityAssuranceEvaluator::with(['instructor','academicYear','semester','auditSession'])->get();
        return response()->json($evaluators);
    }

    /**
     * Store a newly created evaluator
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $evaluator = QualityAssuranceEvaluator::create($validator->validated());
            DB::commit();

            return response()->json([
                'message' => 'Evaluator created successfully',
                'data' => $evaluator
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create evaluator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified evaluator
     */
    public function show(QualityAssuranceEvaluator $qualityAssuranceEvaluator): JsonResponse
    {
        return response()->json($qualityAssuranceEvaluator);
    }

    /**
     * Update the specified evaluator
     */
    public function update(Request $request, QualityAssuranceEvaluator $qualityAssuranceEvaluator): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules(true));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $qualityAssuranceEvaluator->update($validator->validated());
            DB::commit();

            return response()->json([
                'message' => 'Evaluator updated successfully',
                'data' => $qualityAssuranceEvaluator
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update evaluator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified evaluator
     */
    public function destroy(QualityAssuranceEvaluator $qualityAssuranceEvaluator): JsonResponse
    {
        DB::beginTransaction();

        try {
            $qualityAssuranceEvaluator->delete();
            DB::commit();

            return response()->json([
                'message' => 'Evaluator deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete evaluator',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}