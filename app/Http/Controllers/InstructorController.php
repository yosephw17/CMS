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
    

    public function show(Instructor $instructor)
    {
        return $instructor->load('role');
    }

    public function update(Request $request, Instructor $instructor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:instructors,email,{$instructor->id}",
            'phone' => 'nullable|string',
            'role_id' => 'required|exists:instructor_roles,id',
        ]);

        $instructor->update($request->all());
        return response()->json($instructor);
    }

    public function destroy(Instructor $instructor)
    {
        $instructor->delete();
        return response()->json(null, 204);
    }
}
