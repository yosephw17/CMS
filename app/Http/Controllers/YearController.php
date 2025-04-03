<?php

namespace App\Http\Controllers;

use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{

 
    public function index()
    {
        $years = Year::with('sections')->get();

        return response()->json([
            'success' => true,
            'message' => 'Years',
            'data' => $years,
        ]);
    }
}
