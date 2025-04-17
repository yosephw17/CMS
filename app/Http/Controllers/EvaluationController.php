<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluationLink;          // ← Missing import
use App\Models\EvaluationCategory;     // ← Missing import
use App\Models\EvaluationResponse;     // ← Missing import

class EvaluationController extends Controller
{
    public function getForm($hash)
{
    $link = EvaluationLink::with('instructor')
           ->where('hash', $hash)
           ->where('is_used', false)
           ->firstOrFail();

    return response()->json([
        'instructor' => $link->instructor,
        'categories' => EvaluationCategory::with(['questions' => function($q) {
            $q->orderBy('order');
        }])->orderBy('order')->get()
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
}