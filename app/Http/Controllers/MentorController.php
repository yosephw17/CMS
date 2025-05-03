<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Notifications\MentorAssignmentNotification;
use Illuminate\Support\Facades\Log;


class MentorController extends Controller
{
    public function store(Request $request)
    {
        set_time_limit(2107000);

        try {
            // Step 1: Fetch all instructors with their assigned students
            $instructors = Instructor::with('students')->get();
            Log::info('Fetched instructors with their students:', ['instructors' => $instructors]);

            if ($instructors->isEmpty()) {
                return response()->json(['message' => 'No instructors found.'], 404);
            }

            // Step 2: Loop through each instructor and send an email
            foreach ($instructors as $instructor) {
                // // Check if the instructor has assigned students
                // if ($instructor->students->isNotEmpty()) {
                //     // Send a single email to the instructor with the list of their assigned students
                //     $instructor->notify(new MentorAssignmentNotification($instructor, $instructor->students));
                // }

                if ($instructor->id == 8) {
                    if ($instructor->students->isNotEmpty()) {
                        $instructor->notify(new MentorAssignmentNotification($instructor, $instructor->students));
                        // Optional: add logging or output for testing
                        \Log::info("Test email sent to instructor ID: 8");
                        // Or if running from command line:
                        echo "Test email sent to instructor ID: 8\n";
                    }
                    continue; // Skip other instructors during this test
                }

                // Original code for other instructors (will be skipped during this test)
                if ($instructor->students->isNotEmpty()) {
                    $instructor->notify(new MentorAssignmentNotification($instructor, $instructor->students));
                }
            }

            // Step 3: Log the action or perform additional tasks
            Log::info('Emails sent to all instructors with their assigned students.');

            return response()->json([
                'message' => 'Notifications sent successfully to all instructors!',
                'total_instructors_notified' => $instructors->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send notifications:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to send notifications.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
