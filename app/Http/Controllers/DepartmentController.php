<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all departments with related fields
        $departments = Department::all();

        return response()->json([
            'success' => true,
            'message' => 'Departments.',
            'data' => $departments,
        ]);
    }

    /**
     * Store a newly created department in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
      
        ]);

        // Create the department
        $department = Department::create($request->all());


        return response()->json([
            'success' => true,
            'message' => 'Department created successfully.',
            'data' => $department,
        ]);
    }

    /**
     * Display the specified department.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Department $department)
    {
        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    /**
     * Update the specified department in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Department $department)
    {
       $validated= $request->validate([
            'name' => 'required|string|max:255',
       
        ]);

        // Update the department
        $department->update($validated);



        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully.',
            'data' => $department,
        ]);
    }

    /**
     * Remove the specified department from storage.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Department $department)
    {
        // Detach fields before deleting
        $department->fields()->detach();
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully.',
        ]);
    }
}
