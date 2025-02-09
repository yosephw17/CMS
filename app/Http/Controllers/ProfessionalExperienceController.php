<?php

namespace App\Http\Controllers;

use App\Models\ProfessionalExperience;
use Illuminate\Http\Request;

class ProfessionalExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $experiences = ProfessionalExperience::with('field')->get();
        return ProfessionalExperience::with('field')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'field_id' => 'required|exists:fields,id',
        ]);

        $experience = ProfessionalExperience::create($request->all());
        return response()->json(['message' => 'Professional Experience created successfully', 'data' => $experience]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ProfessionalExperience $professionalExperience
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ProfessionalExperience $professionalExperience)
    {
        return response()->json($professionalExperience->load('field'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ProfessionalExperience $professionalExperience
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, ProfessionalExperience $professionalExperience)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'field_id' => 'required|exists:fields,id',
        ]);

        $professionalExperience->update($request->all());
        return response()->json(['message' => 'Professional Experience updated successfully', 'data' => $professionalExperience]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ProfessionalExperience $professionalExperience
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ProfessionalExperience $professionalExperience)
    {
        $professionalExperience->delete();
        return response()->json(['message' => 'Professional Experience deleted successfully']);
    }
}
