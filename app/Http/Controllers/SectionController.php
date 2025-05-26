<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    /**
     * Display a listing of the sections.
     */
    public function index(Request $request)
    {
        $query = Section::query();
        
        // Filter by year_id if provided
        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }
        
        // Include stream relationship if requested
        if ($request->has('include')) {
            if ($request->include === 'stream') {
                $query->with('stream');
            }
        }
        
        $sections = $query->get();
    
        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    /**
     * Store a newly created section in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'year_id' => 'required|exists:years,id',
            'department_id' => 'required|exists:departments,id',
            'number_of_students' => 'required|integer|min:0',
            'stream_id' => 'nullable|exists:streams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $section = Section::create([
                'name' => $request->name,
                'year_id' => $request->year_id,
                'department_id' => $request->department_id,
                'number_of_students' => $request->number_of_students,
                'stream_id' => $request->stream_id
            ]);

            // Load relationships if needed
            if ($request->has('include')) {
                if ($request->include === 'stream') {
                    $section->load('stream');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Section created successfully.',
                'data' => $section,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Section creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create section',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified section.
     */
    public function show(Section $section)
    {
        return response()->json([
            'success' => true,
            'data' => $section->load('stream'),
        ]);
    }

    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, Section $section)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'year_id' => 'required|exists:years,id',
            'department_id' => 'required|exists:departments,id',
            'number_of_students' => 'required|integer|min:0',
            'stream_id' => 'nullable|exists:streams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $section->update([
                'name' => $request->name,
                'year_id' => $request->year_id,
                'department_id' => $request->department_id,
                'number_of_students' => $request->number_of_students,
                'stream_id' => $request->stream_id
            ]);

            // Load relationships if needed
            if ($request->has('include')) {
                if ($request->include === 'stream') {
                    $section->load('stream');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully.',
                'data' => $section,
            ]);

        } catch (\Exception $e) {
            Log::error('Section update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update section',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Section $section)
    {
        try {
            $section->delete();

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Section deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete section',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}