<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditSession; // Make sure to import your AuditSession model

class AuditSessionController extends Controller
{
    /**
     * Retrieve all audit sessions
     */
    public function index()
    {
        $auditSessions = AuditSession::all();
        return response()->json([
            'success' => true,
            'data' => $auditSessions
        ]);
    }

    /**
     * Retrieve a specific audit session
     */
    public function show($id)
    {
        $auditSession = AuditSession::find($id);

        if (!$auditSession) {
            return response()->json([
                'success' => false,
                'message' => 'Audit session not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $auditSession
        ]);
    }

    /**
     * Retrieve audit sessions with pagination
     */
    public function paginated(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Default to 10 items per page
        $auditSessions = AuditSession::paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $auditSessions
        ]);
    }

    /**
     * Search/filter audit sessions
     */
    public function search(Request $request)
    {
        $query = AuditSession::query();

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $auditSessions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $auditSessions
        ]);
    }
}