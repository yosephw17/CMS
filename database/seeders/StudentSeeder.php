<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 7; $i++) {
            // Create a new student with 'assigned_mentor_id' set to null
            Student::create([
                'full_name' => $faker->name,
                'sex' => $faker->randomElement(['Male', 'Female']),
                'phone_number' => $faker->unique()->phoneNumber,
                'department' => $faker->word,
                'hosting_company' => $faker->company(),
                'location' => $faker->city(),
                'assigned_mentor_id' => null,  // Set mentor ID to null
            ]);
        }
    }
}
