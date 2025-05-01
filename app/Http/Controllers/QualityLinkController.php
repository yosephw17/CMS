<?php

namespace App\Http\Controllers;

use App\Models\QualityLink;
use App\Models\AuditSession;
use App\Models\User;
use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'instructor_id' => 'required|exists:instructors,id',
            'semester_id' => 'required|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Generate unique hash
        $validated['hash'] = Str::random(40);
        $validated['is_used'] = false;

        $link = QualityLink::create($validated);
        $url = url("/quality-form/{$link->hash}");


        return response()->json([
            'message' => 'Quality link generated successfully',
            'link' => $link,
            'url' => $url,
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