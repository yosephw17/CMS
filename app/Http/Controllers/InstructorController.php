<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function index()
    {
        return Instructor::with('role')->get();
    }

    public function store(Request $request)
    {
        // Set default role_id if not provided
        

        $roleId = $request->role_id ?: 1; // Default to role_id 1 if not provided
        $request->validate([
            'name' => 'required|string|max:255',
            'is_available'=>'required|boolean',
            'email' => 'required|email|unique:instructors,email',
            'phone' => 'nullable|string',
        ]);
    
        // Add the role_id to the request data if it's not sent
        $data = $request->all();
      
        $data['role_id'] = $roleId;
    
        // Create the instructor with the data
        $instructor = Instructor::create($data);
        return response()->json($instructor, 201);
        // Return the newly created instructor as a JSON response
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
