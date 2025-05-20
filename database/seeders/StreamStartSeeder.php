<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StreamStart;

class StreamStartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StreamStart::create([
            'department_id' => 2, // Electrical Engineering
            'year_id' => 4,
            'semester_id' => 2,
        ]);    }
}
