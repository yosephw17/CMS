<?php
namespace App\Http\Controllers;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Choice;
use App\Models\SignedToken;
use Illuminate\Http\Request;

use App\Notifications\RequestNotification;
class RequestController extends Controller
{

    
    public function store(Request $request)
{
    
    set_time_limit(2107000); // Long execution time (if necessary)

    try {
        $instructor = Instructor::find(8); // Replace with dynamic ID if needed
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found.'], 404);
        }

        Log::info("token: " . $instructor);

        // Generate random token
        $token = Str::random(40);
        $expiresAt = now()->addHours(24);

        // Store token in database
        SignedToken::updateOrCreate(
            ['instructor_id' => $instructor->id], // Ensure each instructor has only one token
            ['token' => $token, 'expires_at' => $expiresAt]
        );

        // Generate signed URL with token
        $signedUrl = URL::temporarySignedRoute(
            'choose-courses',
            $expiresAt,
            ['instructor' => $instructor->id, 'token' => $token]
        );

        // Send email notification with the signed URL
        $instructor->notify(new RequestNotification($signedUrl)); // Pass the signed URL here

        return response()->json(['message' => 'Notification sent successfully!'], 200);
    } catch (\Exception $e) {
        Log::error('Failed to send notification:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['message' => 'Failed to send notification.', 'error' => $e->getMessage()], 500);
    }
    
}

public function showChooseCoursesPage($instructorId, Request $request)
{
    // Get token from URL
    $token = $request->query('token');

    // Retrieve the token record
    $signedToken = SignedToken::where('instructor_id', $instructorId)
        ->where('token', $token)
        ->where('expires_at', '>', now()) // Ensure token is not expired
        ->first();

    if (!$signedToken) {
        abort(403, 'Unauthorized, expired, or already used token.');
    }

    // Delete the token after successful validation (so it can't be reused)
    $signedToken->delete();

    // Fetch instructor and courses
    $instructor = Instructor::findOrFail($instructorId);
    $courses = Course::all();
    $assignment = Assignment::latest()->first();

    return view('choose-courses', compact('instructor', 'courses', 'assignment'));
}

public function storeChoices(Request $request)
{
    Log::info('storeChoices', $request->all());
    $request->validate([
        'instructor_id' => 'required|exists:instructors,id',
        'assignment_id' => 'required|exists:assignments,id',
        'choices' => 'required|array|min:3',
        'choices.*' => 'nullable|exists:courses,id'
    ]);

    foreach ($request->choices as $rank => $course_id) {
        if ($course_id) {
            Choice::create([
                'instructor_id' => $request->instructor_id,
                'assignment_id' => $request->assignment_id,
                'course_id' => $course_id,
                'rank' => $rank
            ]);
        }
    }
    return redirect()->route('success.page')->with('success', 'Thank you for your response! Your course choices have been successfully submitted.');
}
    
}
