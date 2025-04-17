<?php

namespace App\Http\Controllers;

use App\Models\EvaluationLink;  // ← THIS IS THE CRITICAL IMPORT
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluationLinkController extends Controller
{
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instructor_id' => 'required|exists:instructors,id',
            'student_email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $link = EvaluationLink::create([
            'instructor_id' => $request->instructor_id,
            'student_email' => $request->student_email,
'hash' => \Illuminate\Support\Str::random(60),
        ]);

        return response()->json([
            'message' => 'Evaluation link created successfully',
            'data' => [
                'evaluation_url' => url("/evaluate/{$link->hash}"),
                'expires_in' => '7 days'
            ]
        ], 201);
    }
}