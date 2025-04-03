<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsTableSeeder extends Seeder
{
    public function run()
    {
        $departments = [

            ['name' => 'Electrical Engineering'],
            ['name' => 'Computer Engineering'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
