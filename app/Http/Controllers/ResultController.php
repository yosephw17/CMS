<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
class ResultController extends Controller
{
    public function index()
    {
        $results = Result::all();
        return response()->json($results);
    }
}
