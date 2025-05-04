<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
class RoomController extends Controller
{
        public function index()
        {
            // Retrieve all courses with related fields
            $rooms = Room::all();
            return response()->json($rooms);
        }

}
