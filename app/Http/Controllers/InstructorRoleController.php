<?php

namespace App\Http\Controllers;

use App\Models\InstructorRole;
use Illuminate\Http\Request;

class InstructorRoleController extends Controller
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:instructor_roles,name',
            'load' => 'required|integer|',
        ]);

        $role = InstructorRole::create([
            'name' => $request->name,
            'load' => $request->load,
        ]);

        return response()->json([
            'message' => 'Instructor role created successfully',
            'role' => $role,
        ], 201);
    }

     // Get all instructor roles
     public function index()
     {
         $roles = InstructorRole::all();
 
         return response()->json([
             'roles' => $roles,
         ]);
     }

     public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|unique:instructor_roles,name,' . $id, // Allow the same name for the current record
        'load' => 'required|integer|min:0',
    ]);

    $role = InstructorRole::findOrFail($id); // Find the role by ID or throw a 404 error
    $role->update([
        'name' => $request->name,
        'load' => $request->load,
    ]);

    return response()->json([
        'message' => 'Instructor role updated successfully',
        'role' => $role,
    ]);
}

public function destroy(string $id)
    {
        $role = InstructorRole::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

}
