<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
 {
         $semesters = Semester::all();

         return response()->json([
             'semesters' => $semesters,
         ]);
     }
       public function show($id){
        $semester = Semester::findOrFail($id);
        return response()->json($semester);

    }

}
