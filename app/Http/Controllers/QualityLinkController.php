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
use App\Notifications\EvaluatorQualityAssessmentNotification;
use App\Models\QualityAssuranceEvaluator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class QualityLinkController extends Controller
{
    /**
     * Display a listing of quality links
     */
    public function index()
    {
        $links = QualityLink::with(['auditSession', 'instructor', 'semester', 'academicYear', 'evaluator', 'department'])
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
            'instructor_ids' => 'required|array|min:1',
            'instructor_ids.*' => 'required|exists:instructors,id',
            'semester_id' => 'required|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'section' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $createdLinks = [];
        $failedInstructors = [];
        $frontendUrl = env('FRONTEND_URL', 'http://localhost');

        Log::info('Processing quality links', [
            'input' => $validated,
            'course_id' => $validated['course_id'] ?? 'null',
            'request_all' => $request->all()
        ]);

        foreach ($validated['instructor_ids'] as $instructorId) {
            try {
                DB::beginTransaction();

                $instructor = Instructor::findOrFail($instructorId);
                Log::info('Creating self-evaluation link', [
                    'instructor_id' => $instructorId,
                    'instructor_name' => $instructor->name,
                    'department_id' => $validated['department_id'] ?? 'null',
                    'course_id' => $validated['course_id'] ?? 'null',
                ]);

                $instructorLink = QualityLink::create([
                    'audit_session_id' => $validated['audit_session_id'],
                    'instructor_id' => $instructorId,
                    'semester_id' => $validated['semester_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'is_used' => false,
                    'evaluator_id' => null,
                    'courses_id' => $validated['course_id'],
                    'is_self_evaluation' => true,
                    'section' => $validated['section'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                ]);

                $instructorUrl = "{$frontendUrl}/quality-assurance-form/{$instructorLink->hash}";
                Log::info('Self-evaluation link created', [
                    'instructor_id' => $instructorId,
                    'url' => $instructorUrl,
                    'department_id' => $validated['department_id'] ?? 'null',
                    'courses_id' => $instructorLink->courses_id ?? 'null',
                ]);

                try {
                    $instructor->notify(new InstructorCourseAuditNotification(
                        $instructor->name,
                        $instructorUrl
                    ));
                } catch (\Exception $e) {
                    Log::warning('Failed to send instructor notification', [
                        'instructor_id' => $instructorId,
                        'error' => $e->getMessage(),
                    ]);
                }

                $evaluators = QualityAssuranceEvaluator::where([
                    'instructor_id' => $instructorId,
                    'semester_id' => $validated['semester_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'audit_session_id' => $validated['audit_session_id'],
                ])->get();

                if ($evaluators->isEmpty()) {
                    Log::warning('No evaluators found', [
                        'instructor_id' => $instructorId,
                        'department_id' => $validated['department_id'] ?? 'null',
                        'course_id' => $validated['course_id'] ?? 'null',
                    ]);
                }

                $evaluatorLinks = [];

                foreach ($evaluators as $evaluator) {
                    $evaluatorLink = QualityLink::create([
                        'audit_session_id' => $validated['audit_session_id'],
                        'instructor_id' => $instructorId,
                        'semester_id' => $validated['semester_id'],
                        'academic_year_id' => $validated['academic_year_id'],
                        'is_used' => false,
                        'courses_id' => $validated['course_id'],
                        'evaluator_id' => $evaluator->id,
                        'is_self_evaluation' => false,
                        'section' => $validated['section'] ?? null,
                        'department_id' => $validated['department_id'] ?? null,
                    ]);

                    $evaluatorUrl = "{$frontendUrl}/quality-assurance-form/{$evaluatorLink->hash}";
                    Log::info('Evaluator link created', [
                        'instructor_id' => $instructorId,
                        'evaluator_id' => $evaluator->id,
                        'url' => $evaluatorUrl,
                    ]);

                    $evaluatorLinks[] = [
                        'evaluator_id' => $evaluator->id,
                        'name' => $evaluator->name,
                        'email' => $evaluator->email,
                        'url' => $evaluatorUrl,
                    ];

                    try {
                        Notification::route('mail', $evaluator->email)
                            ->notify(new EvaluatorQualityAssessmentNotification(
                                $evaluator->name,
                                $instructor->name,
                                $evaluatorUrl
                            ));
                    } catch (\Exception $e) {
                        Log::warning('Failed to send evaluator notification', [
                            'evaluator_id' => $evaluator->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $createdLinks[] = [
                    'instructor_id' => $instructorId,
                    'instructor_name' => $instructor->name,
                    'self_evaluation_url' => $instructorUrl,
                    'evaluators' => $evaluatorLinks,
                    'section' => $validated['section'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'course_id' => $validated['course_id'] ?? null,
                ];

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failedInstructors[] = [
                    'instructor_id' => $instructorId,
                    'error' => $e->getMessage(),
                ];
                Log::error('Failed to process instructor', [
                    'instructor_id' => $instructorId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => empty($failedInstructors),
            'message' => empty($failedInstructors) ? 'All links created successfully' : 'Some links failed to create',
            'data' => $createdLinks,
            'errors' => $failedInstructors,
        ], empty($failedInstructors) ? 201 : 207);
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