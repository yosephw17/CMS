<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use Illuminate\Support\Facades\Log;

class SectionController extends Controller
{
 /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $sections = Section::all();
    
        return response()->json([
            'sections' => $sections,
        ]);
    }
    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            
        ]);
Log::info("message", $request->all());
        $section = Section::create(['name' => $request->name,
    'year_id'=>$request->yearId]);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully.',
            'data' => $section,
        ]);
    }

    /**
     * Display the specified role.
     */
    public function show(Section $section)
    {
        return response()->json([
            'success' => true,
            'data' => $section,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Section  updated successfully.',
            'data' => $section,
        ]);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully.',
        ]);
    }}
