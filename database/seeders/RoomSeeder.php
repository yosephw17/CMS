<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Building;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first building or create one
        $building = Building::first() ?? Building::create(['name' => 'Main Building']);

        $rooms = [
            // Normal rooms (lecture halls, tutorial rooms)
            ['name' => 'NDC-6', 'type' => 'lecture', 'capacity' => 50],
            ['name' => 'NDC-7', 'type' => 'lecture', 'capacity' => 50],
            ['name' => 'NDC-8', 'type' => 'lecture', 'capacity' => 50],
            ['name' => 'NDA-4', 'type' => 'lecture', 'capacity' => 50],
            ['name' => 'GLR-203', 'type' => 'lecture', 'capacity' => 50],
            ['name' => 'GLR-206', 'type' => 'lecture', 'capacity' => 50],
            ['name' => 'GLR-303', 'type' => 'lecture', 'capacity' => 50],

            // Labs
            ['name' => 'Electronics lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Interfacing lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Machine lab-1', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Power system lab-1', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Power system lab-2', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Fundamental Lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Elec. Workshop', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Hardware Lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Software Lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'DSP lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Antenna Lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Comm. system lab', 'type' => 'lab', 'capacity' => 35],
            ['name' => 'Process control lab', 'type' => 'lab', 'capacity' => 35],
        ];

        foreach ($rooms as $room) {
            DB::table('rooms')->insert([
                'name' => $room['name'],
                'type' => $room['type'],
                'capacity' => $room['capacity'],
                'building_id' => $building->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
