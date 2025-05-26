<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuestInstructor;
use Illuminate\Support\Facades\Log;

class GuestInstructorController extends Controller
{    
    
    public function index()
    {
        $guests = GuestInstructor::all();
        return response()->json($guests);

    }

    public function show($id)
    {
        Log::info("message",['id',$id]);
        $guestInstructor = GuestInstructor::findOrFail($id, ['id', 'name']);
        return response()->json($guestInstructor);

    }
    public function store(Request $request)
    {
        Log::info("message", $request->all());
       $validated= $request->validate([
            'name' => 'required|string',
            'course_id'=>'required|integer',
            'schedule_id'=>'required|integer',
        ]);
      $guest = GuestInstructor::create($validated);

        return response()->json([
            'message' => 'Schedule created successfully.',
            'data' => $guest
        ], 201);
    }
}
