<?php

namespace App\Http\Controllers;

use App\Models\EducationalBackground;
use Illuminate\Http\Request;

class EducationalBackgroundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $backgrounds = EducationalBackground::all();
        return response()->json($backgrounds, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255', // Only name is required
        ]);

        // Create a new educational background
        $background = EducationalBackground::create([
            'name' => $request->name,
        ]);

        // Return the created background as a JSON response
        return response()->json($background, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the educational background by ID
        $background = EducationalBackground::find($id);

        // If the background doesn't exist, return a 404 error
        if (!$background) {
            return response()->json(['message' => 'Educational background not found'], 404);
        }

        // Return the background as a JSON response
        return response()->json($background, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255', // Only name is required
        ]);

        // Find the educational background by ID
        $background = EducationalBackground::find($id);

        // If the background doesn't exist, return a 404 error
        if (!$background) {
            return response()->json(['message' => 'Educational background not found'], 404);
        }

        // Update the background with the new data
        $background->name = $request->name;
        $background->save();

        // Return the updated background as a JSON response
        return response()->json($background, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the educational background by ID
        $background = EducationalBackground::find($id);

        // If the background doesn't exist, return a 404 error
        if (!$background) {
            return response()->json(['message' => 'Educational background not found'], 404);
        }

        // Delete the background
        $background->delete();

        // Return a success message
        return response()->json(['message' => 'Educational background deleted successfully'], 200);
    }
}