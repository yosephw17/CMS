<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Year;

class YearsTableSeeder extends Seeder
{
    public function run()
    {
        $years = [
            ['name' => '2nd', 'department_id' => 1],
            ['name' => '2nd', 'department_id' => 2],
            ['name' => '3rd', 'department_id' => 1],
            ['name' => '3rd', 'department_id' => 2],
            ['name' => '4th', 'department_id' => 1],
            ['name' => '4th', 'department_id' => 2],
            ['name' => '5th', 'department_id' => 1],
            ['name' => '5th', 'department_id' => 2],
        ];

        foreach ($years as $year) {
            Year::create($year);
        }
    }
}
