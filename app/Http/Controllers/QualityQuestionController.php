<?php

namespace App\Http\Controllers;

use App\Models\QualityQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QualityQuestionController extends Controller
{
    /**
     * Display a listing of questions.
     */
    public function index()
    {
        $questions = QualityQuestion::orderBy('id')->get();
        return response()->json($questions);
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'input_type' => ['required', Rule::in(['text','number', 'dropdown', 'textarea', 'checkbox'])],
            'options' => 'nullable|array' // Required if input_type is dropdown/checkbox
        ]);

        // Convert options array to JSON if present
        if (isset($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        $question = QualityQuestion::create($validated);

        return response()->json([
            'message' => 'Question created successfully',
            'question' => $question
        ], 201);
    }

    /**
     * Display the specified question.
     */
    public function show(QualityQuestion $qualityQuestion)
    {
        return response()->json($qualityQuestion);
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, QualityQuestion $qualityQuestion)
    {
        $validated = $request->validate([
            'question_text' => 'sometimes|string|max:1000',
            'input_type' => ['sometimes', Rule::in(['number', 'dropdown', 'textarea', 'checkbox'])],
            'options' => 'nullable|array',
        ]);

        $qualityQuestion->update($validated);

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $qualityQuestion->fresh()
        ]);
    }


    /**
     * Remove the specified question.
     */
    public function destroy(QualityQuestion $qualityQuestion)
    {
        $qualityQuestion->delete();
        return response()->json([
            'message' => 'Question deleted successfully'
        ]);
    }

    /**
     * Get questions by type (optional helper method)
     */
    public function byType($type)
    {
        $questions = QualityQuestion::where('input_type', $type)->get();
        return response()->json($questions);
    }
}