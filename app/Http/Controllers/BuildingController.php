<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{

 public function index()
    {   
        $buildings = Building::all();
        return response()->json($buildings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
           
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $building = Building::create($request->all());
        return response()->json($building, 201);
    }

    public function show($id)
    {
        $building = Building::findOrFail($id);
        return response()->json($building);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
         
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $building = Building::findOrFail($id);
        $building->update($request->all());
        return response()->json($building);
    }

    public function destroy($id)
    {
        $building = Building::findOrFail($id);
        $building->delete();
        return response()->json(null, 204);
    }
}
