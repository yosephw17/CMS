<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fields = Field::all();
        return Field::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fields.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Field::create($request->all());

        return redirect()->route('fields.index')->with('success', 'Field created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Field $field)
    {
        return view('fields.show', compact('field'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Field $field)
    {
        return view('fields.edit', compact('field'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Field $field)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $field->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully.',
            'data' => $field,
        ]);    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Field $field)
    {
        $field->delete();

        return redirect()->route('fields.index')->with('success', 'Field deleted successfully.');
    }
}
