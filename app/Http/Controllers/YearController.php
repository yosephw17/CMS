<?php

namespace App\Http\Controllers;

use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{

 
    public function index(Request $request)
    {
        $query = Year::with(['sections', 'departments']);
        
        // Directly filter by department_id if provided
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
    
        $years = $query->get();
    
        return response()->json([
            'success' => true,
            'message' => $request->filled('department_id') 
                ? 'Years filtered by department' 
                : 'All years',
            'data' => $years,
        ]);
    }
}
