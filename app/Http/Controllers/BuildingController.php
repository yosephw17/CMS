<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BuildingController extends Controller
{

    public function index()
    {
        // You can return dummy data or fetch from a model
        return response()->json([
            ['id' => 1, 'name' => 'NDA'],
            ['id' => 2, 'name' => 'NDB'],
            ['id' => 3, 'name' => 'NDC'],
            ['id' => 4, 'name' => 'GLR'],
        ]);
    }

}
