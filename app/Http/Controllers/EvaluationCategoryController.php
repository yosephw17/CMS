<?php

namespace App\Http\Controllers;
use App\Models\EvaluationCategory;
use App\Models\Instructor;


use Illuminate\Http\Request;

class EvaluationCategoryController extends Controller
{
    public function getCategoriesWithQuestions(Request $request)
    {
        // Get instructor ID from query parameter if it exists
        $instructorId = $request->query('instructor-id');
        $targetRole = 'regular_instructor'; // default value

        // If instructor ID is provided, determine the target role
        if ($instructorId) {
            $instructor = Instructor::with('role')->find($instructorId);

            if ($instructor && $instructor->role && $instructor->role->name === 'lab_assistance') {
                $targetRole = 'lab_assistant';
            }
        }

        $categories = EvaluationCategory::with(['questions' => function($query) use ($targetRole) {
            $query->where('target_role', $targetRole)
                  ->orderBy('order', 'asc');
        }])
        ->orderBy('order', 'asc')
        ->get()
        ->filter(function($category) {
            return $category->questions->count() > 0;
        })
        ->values();

        return response()->json($categories);
    }
}
