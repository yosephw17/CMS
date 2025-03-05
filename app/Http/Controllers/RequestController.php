<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Notifications\RequestNotification;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    public function store(Request $request)
    {
        set_time_limit(2107000);

        try {
            $instructor = Instructor::find(657); // Test with a single instructor
            if ($instructor) {
                $instructor->notify(new RequestNotification($instructor));
                return response()->json(['message' => 'Notification sent successfully!'], 200);
            } else {
                return response()->json(['message' => 'Instructor not found.'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to send notification.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
