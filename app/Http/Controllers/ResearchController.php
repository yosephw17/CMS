<?php

namespace App\Http\Controllers;

use App\Models\Research;
use App\Models\Field;
use App\Models\Instructor;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
    // Display a listing of the researches
    public function index()
    {
        return Research::with('field', 'instructor')->get();
    }
    
    // Show the form for creating a new research
    public function create()
    {
        $fields = Field::all();
        $instructors = Instructor::all();
        return response()->json(compact('fields', 'instructors'));
    }

    // Store a newly created research in storage
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'field_id' => 'required|exists:fields,id',
            'instructor_id' => 'required|exists:instructors,id',
            'link' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $research = Research::create($request->all());

        return response()->json($research, 201);
    }

    // Display the specified research
    public function show($id)
    {
        $research = Research::with('field', 'instructor')->findOrFail($id);
        return response()->json($research);
    }

    // Show the form for editing the specified research
    public function edit($id)
    {
        $research = Research::with('field', 'instructor')->findOrFail($id);
        $fields = Field::all();
        $instructors = Instructor::all();
        return response()->json(compact('research', 'fields', 'instructors'));
    }

    // Update the specified research in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'field_id' => 'required|exists:fields,id',
            'instructor_id' => 'required|exists:instructors,id',
            'link' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $research = Research::findOrFail($id);
        $research->update($request->all());

        return response()->json($research);
    }

    // Remove the specified research from storage
    public function destroy($id)
    {
        $research = Research::findOrFail($id);
        $research->delete();

        return response()->json(null, 204);
    }
}
