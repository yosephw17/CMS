<?php

namespace App\Http\Controllers;
use App\Models\EvaluationCategory;


use Illuminate\Http\Request;

class EvaluationCategoryController extends Controller
{
    public function getCategoriesWithQuestions()
{
    $categories = EvaluationCategory::with(['questions' => function($query) {
        $query->orderBy('order', 'asc');
    }])
    ->orderBy('order', 'asc')
    ->get();

    return response()->json(
        $categories
    );
}
}
