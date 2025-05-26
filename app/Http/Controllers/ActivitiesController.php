<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivitiesController extends Controller
{
   
    public function index()
    {
        $activities = Activity::with('user')->get();
        return response()->json($activities);
    }

    public function show($id)
    {
        $activity = Activity::with('user')->findOrFail($id);
        return response()->json($activity);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $activity = Activity::create([
            'name' => $validated['name'],
            'user_id' => Auth::id(),
        ]);

        return response()->json($activity->load('user'), 201);
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $activity->update([
            'name' => $validated['name'],
        ]);

        return response()->json($activity->load('user'));
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();
        return response()->json(null, 204);
    }
}
