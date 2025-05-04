<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('buildings')->insert([
            ['name' => 'Main Building', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Annex Building', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outpatient Wing', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
