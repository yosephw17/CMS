<?php

namespace App\Http\Controllers;

use App\Models\Evaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluatorController extends Controller
{
    /**
     * Get all evaluators
     */
    public function index(): JsonResponse
    {
        $evaluators = Evaluator::all();
        return response()->json([
            'success' => true,
            'data' => $evaluators
        ]);
    }

    /**
     * Create new evaluator
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:evaluators,email',
            'type' => 'required|string|max:255'
        ]);

        $evaluator = Evaluator::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Evaluator created successfully',
            'data' => $evaluator
        ], 201);
    }

    /**
     * Get single evaluator
     */
    public function show(Evaluator $evaluator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $evaluator
        ]);
    }

    /**
     * Update evaluator
     */
    public function update(Request $request, Evaluator $evaluator): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:evaluators,email,'.$evaluator->id,
            'type' => 'sometimes|string|max:255'
        ]);

        $evaluator->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Evaluator updated successfully',
            'data' => $evaluator
        ]);
    }

    /**
     * Delete evaluator
     */
    public function destroy(Evaluator $evaluator): JsonResponse
    {
        $evaluator->delete();
        return response()->json([
            'success' => true,
            'message' => 'Evaluator deleted successfully'
        ]);
    }
}