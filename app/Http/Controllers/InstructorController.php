<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class InstructorController extends Controller
{
        public function index()
        {
            return Instructor::with('role','educationalBackgrounds','professionalExperiences')->get();
        }

   public function store(Request $request)
{
    // Set default role_id if not provided
    $roleId = $request->role_id ?: 1; // Default to role_id 1 if not provided

    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'is_available' => 'required|boolean',
        'email' => 'required|email|unique:instructors,email',
        'phone' => 'nullable|string',
        'is_studying' => 'nullable|boolean',
        'is_approved' => 'nullable|boolean',
        'pro_exp_ids' => 'nullable|array', // Array of professional experience IDs
        'pro_exp_ids.*' => 'nullable|integer|exists:professional_experiences,id', // Validate each ID
        'edu_backgrounds' => 'nullable|array', // Array of educational backgrounds
        'edu_backgrounds.*.edu_background_id' => 'nullable|integer|exists:educational_backgrounds,id', // Validate each educational background ID
        'edu_backgrounds.*.field_id' => 'nullable|integer|exists:fields,id', // Validate each field ID
    ]);

    // Add the role_id to the request data if it's not sent
    $data = $request->all();
    $data['role_id'] = $roleId;

    // Remove unnecessary fields from the data
    unset($data['pro_exp_ids']);
    unset($data['edu_backgrounds']);

    // Create the instructor with the data
    $instructor = Instructor::create($data);

    // Attach professional experiences
    if ($request->has('pro_exp_ids')) {
        foreach ($request->pro_exp_ids as $proExpId) {
            DB::table('instructor_professional_experience')->insert([
                'instructor_id' => $instructor->id,
                'pro_exp_id' => $proExpId,
            ]);
        }
    }

    // Attach educational backgrounds and fields
    if ($request->has('edu_backgrounds')) {
        foreach ($request->edu_backgrounds as $eduBackground) {
            DB::table('instructor_educational_background')->insert([
                'instructor_id' => $instructor->id,
                'edu_background_id' => $eduBackground['edu_background_id'],
                'field_id' => $eduBackground['field_id'],
            ]);
        }
    }

    // Return the newly created instructor as a JSON response
    return response()->json($instructor, 201);
}
    

public function show($id)
{
    $instructor = Instructor::with('role', 'educationalBackgrounds', 'professionalExperiences', 'researches')
    ->findOrFail($id);
    return response()->json($instructor);
}


    public function update(Request $request, Instructor $instructor)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:instructors,email,{$instructor->id}",
            'phone' => 'nullable|string',
            'is_available' => 'required|boolean',
            'is_studying' => 'nullable|boolean',
            'is_approved' => 'nullable|boolean',
            'role_id' => 'required|exists:instructor_roles,id',
            'pro_exp_ids' => 'nullable|array', // Array of professional experience IDs
            'pro_exp_ids.*' => 'nullable|integer|exists:professional_experiences,id', // Validate each ID
            'edu_backgrounds' => 'nullable|array', // Array of educational backgrounds
            'edu_backgrounds.*.edu_background_id' => 'nullable|integer|exists:educational_backgrounds,id', // Validate each educational background ID
            'edu_backgrounds.*.field_id' => 'nullable|integer|exists:fields,id', // Validate each field ID
        ]);
    
        // Update the instructor data
        $instructor->update($request->except('pro_exp_ids', 'edu_backgrounds'));
    
        // Update the professional experiences
        if ($request->has('pro_exp_ids')) {
            // First, delete old professional experiences
            DB::table('instructor_professional_experience')->where('instructor_id', $instructor->id)->delete();
    
            // Attach new professional experiences
            foreach ($request->pro_exp_ids as $proExpId) {
                DB::table('instructor_professional_experience')->insert([
                    'instructor_id' => $instructor->id,
                    'pro_exp_id' => $proExpId,
                ]);
            }
        }
    
        // Update the educational backgrounds
        if ($request->has('edu_backgrounds')) {
            // First, delete old educational backgrounds
            DB::table('instructor_educational_background')->where('instructor_id', $instructor->id)->delete();
    
            // Attach new educational backgrounds and fields
            foreach ($request->edu_backgrounds as $eduBackground) {
                DB::table('instructor_educational_background')->insert([
                    'instructor_id' => $instructor->id,
                    'edu_background_id' => $eduBackground['edu_background_id'],
                    'field_id' => $eduBackground['field_id'],
                ]);
            }
        }
    
        // Return the updated instructor as a JSON response
        return response()->json($instructor);
    }
    
    public function destroy(Instructor $instructor)
    {
        $instructor->educationalBackgrounds()->detach();
        $instructor->professionalExperiences()->detach();
    
        $instructor->delete();
    
        return response()->json(['message' => 'Instructor deleted successfully'], 204);
    }
}
