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

        // Define Ethiopian names in Latin script
        $ethiopianFirstNames = [
            'Abebe', 'Alemu', 'Bereket', 'Dawit', 'Elias', 'Fitsum', 'Girma', 'Habtamu', 'Kebede', 'Lemma', 
            'Mekonnen', 'Nigatu', 'Selam', 'Tadesse', 'Worku', 'Yohannes', 'Zerihun', 'Alem', 'Birtukan', 'Etenesh', 
            'Fasika', 'Genet', 'Hana', 'Kidan', 'Liya', 'Marta', 'Netsanet', 'Rahel', 'Sara', 'Tigist', 'Yodit', 'Zewditu'
        ];

        $ethiopianLastNames = [
            'Tesfaye', 'Demissie', 'Gebre', 'Kassa', 'Mulugeta', 'Assefa', 'Tadesse', 'Girma', 'Hailu', 'Lemma', 
            'Mekonnen', 'Nigatu', 'Selam', 'Worku', 'Yohannes', 'Zerihun', 'Alemayehu', 'Bekele', 'Desta', 'Fekadu', 
            'Gebremichael', 'Haileselassie', 'Kebede', 'Mengistu', 'Negash', 'Tekle', 'Woldemariam', 'Zewdie'
        ];

        for ($i = 0; $i < 7; $i++) {
            // Randomly select an Ethiopian first name and last name
            $firstName = $ethiopianFirstNames[array_rand($ethiopianFirstNames)];
            $lastName = $ethiopianLastNames[array_rand($ethiopianLastNames)];

            // Create a new student with Ethiopian names
            Student::create([
                'full_name' => $firstName . ' ' . $lastName, // Combine first and last name
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