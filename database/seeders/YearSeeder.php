<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = ['1', '2', '3', '4', '5'];

        // Fetch all department IDs
        $departments = DB::table('departments')->pluck('id');

        foreach ($departments as $departmentId) {
            foreach ($years as $year) {
                DB::table('years')->insert([
                    'name' => $year,
                    'department_id' => $departmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
