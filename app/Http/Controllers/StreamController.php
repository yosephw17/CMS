<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StreamController extends Controller
{
    public function index(Request $request)
    {
        $query = Stream::with(['department', 'year', 'semester']);
        if ($request->query('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }
        $streams = $query->get();
        return response()->json($streams);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'year_id' => 'required|exists:years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $stream = Stream::create($validated);
        Log::info('Stream created', $validated);
        return response()->json($stream, 201);
    }

    public function update(Request $request, $id)
    {
        $stream = Stream::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'year_id' => 'required|exists:years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $stream->update($validated);
        Log::info('Stream updated', $validated);
        return response()->json($stream);
    }

    public function destroy($id)
    {
        $stream = Stream::findOrFail($id);
        $stream->delete();
        Log::info('Stream deleted', ['id' => $id]);
        return response()->json(null, 204);
    }
}
?>