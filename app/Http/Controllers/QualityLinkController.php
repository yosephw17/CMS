<?php

namespace App\Http\Controllers;

use App\Models\QualityLink;
use App\Models\AuditSession;
use App\Models\Instructor;
use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Notifications\InstructorCourseAuditNotification;


class QualityLinkController extends Controller
{
    /**
     * Display a listing of quality links
     */
    public function index()
    {
        $links = QualityLink::with(['auditSession', 'instructor', 'semester', 'academicYear'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($links);
    }

    /**
     * Generate and store a new quality link
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_session_id' => 'required|exists:audit_sessions,id',
            'instructor_ids' => 'required|array',  // Changed to accept array of instructor IDs
            'instructor_ids.*' => 'required|exists:instructors,id',
            'semester_id' => 'required|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $createdLinks = [];
        $failedInstructors = [];

        foreach ($validated['instructor_ids'] as $instructorId) {
            try {
                DB::beginTransaction();

                $instructor = Instructor::findOrFail($instructorId);

                // Generate unique hash
                $linkData = [
                    'audit_session_id' => $validated['audit_session_id'],
                    'instructor_id' => $instructorId,
                    'semester_id' => $validated['semester_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'hash' => Str::random(40),
                    'is_used' => false,
                ];
                $frontendUrl = env('FRONTEND_URL');


                $link = QualityLink::create($linkData);
                $url = "{$frontendUrl}/#/quality-assurance-form/{$link->hash}";

                // Send notification to instructor
                $instructor->notify(new InstructorCourseAuditNotification(
                    $instructor->name,
                    $url
                ));

                $createdLinks[] = [
                    'instructor_id' => $instructorId,
                    'link' => $link,
                    'url' => $url,
                ];

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failedInstructors[] = [
                    'instructor_id' => $instructorId,
                    'error' => $e->getMessage()
                ];
                \Log::error("Failed to create quality link for instructor {$instructorId}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Quality links processed',
            'data' => [
                'successful_creations' => $createdLinks,
                'failed_creations' => $failedInstructors,
            ]
        ], 201);
    }
    /**
     * Display the specified quality link
     */
    public function show(QualityLink $qualityLink)
    {
        return response()->json($qualityLink->load([
            'auditSession',
            'instructor',
            'semester',
            'academicYear'
        ]));
    }

    /**
     * Update a quality link (mark as used/expired)
     */
    public function update(Request $request, QualityLink $qualityLink)
    {
        $validated = $request->validate([
            'is_used' => 'sometimes|boolean',
            'expires_at' => 'sometimes|date|after:now'
        ]);

        $qualityLink->update($validated);

        return response()->json([
            'message' => 'Link updated successfully',
            'link' => $qualityLink
        ]);
    }

    /**
     * Delete a quality link
     */
    public function destroy(QualityLink $qualityLink)
    {
        $qualityLink->delete();

        return response()->json([
            'message' => 'Link deleted successfully'
        ]);
    }

    /**
     * Validate a link by hash (for form submission)
     */
    public function validateLink($hash)
    {
        $link = QualityLink::where('hash', $hash)
                ->where('is_used', false)
                ->when(request('check_expiry'), function($q) {
                    $q->where('expires_at', '>', now());
                })
                ->firstOrFail();

        return response()->json([
            'valid' => true,
            'link' => $link->load(['auditSession', 'semester', 'academicYear'])
        ]);
    }

    /**
     * Get links by instructor
     */
    public function byInstructor($instructorId)
    {
        $links = QualityLink::where('instructor_id', $instructorId)
                ->with(['auditSession', 'semester', 'academicYear'])
                ->get();

        return response()->json($links);
    }
}