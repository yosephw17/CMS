<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear; // Import the AcademicYear model

class AcademicYearController extends Controller
{
    //

    function index()
    {
        // Fetch all academic years
        $academicYears = AcademicYear::get();
        return response()->json([
            'message' => 'Academic Year Controller',
            'data' => $academicYears // Include the fetched data in the response
        ]);
    }

}