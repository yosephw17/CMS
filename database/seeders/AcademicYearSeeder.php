<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the table first if needed

        // Create academic years
        AcademicYear::create([
            'name' => '2022/23',
        ]);

        AcademicYear::create([
            'name' => '2023/24',
        ]);

        // Optional: Create future years
        AcademicYear::create([
            'name' => '2024/25',
        ]);

        AcademicYear::create([
            'name' => '2025/26',
        ]);
    }
}