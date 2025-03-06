<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
class ResultController extends Controller
{
    public function index()
    {
        $results = Result::where('is_assigned',1)->get();
        return response()->json($results);
    }
}
