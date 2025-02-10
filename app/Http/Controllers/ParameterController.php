<?php

namespace App\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Http\Request;

class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all parameters from the database
    $parameters = Parameter::all();  // Assuming Parameter is the model name
    return response()->json($parameters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'points' => 'required|numeric',
    ]);

    // Create a new parameter
    $parameter = Parameter::create($validated);

    return response()->json($parameter, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parameter = Parameter::findOrFail($id);

    return response()->json($parameter);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|numeric',
        ]);
    
        // Find the parameter by ID
        $parameter = Parameter::findOrFail($id);
    
        // Update the parameter
        $parameter->update($validated);
    
        return response()->json($parameter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $parameter = Parameter::findOrFail($id);

        // Delete the parameter
        $parameter->delete();
    
        return response()->json(['message' => 'Parameter deleted successfully']);
    }
}
