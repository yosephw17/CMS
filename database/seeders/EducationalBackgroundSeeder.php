<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducationalBackgroundSeeder extends Seeder
{
    public function run()
    {
        DB::table('educational_backgrounds')->insert([
            [
                'name' => 'Ph.D. in Computer Engineering',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Computer Engineering',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Masters\'s in Software Engineering',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Ph.D. in Cybersecurity',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Cybersecurity',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'name' => 'Ph.D. in Networking',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Networking',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        
            [
                'name' => 'Ph.D. in Artificial Intelligence',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Artificial Intelligence',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
   
            [
                'name' => 'Ph.D. in Software Engineering',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Software Engineering',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
       
            [
                'name' => 'Ph.D. in Data Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Data Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
    
            [
                'name' => 'Ph.D. in Cloud Computing',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Cloud Computing',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
       
            [
                'name' => 'Ph.D. in Robotics',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Robotics',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
      
            [
                'name' => 'Ph.D. in Computer Vision',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Computer Vision',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
       
            [
                'name' => 'Ph.D. in Quantum Computing',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Master\'s in Quantum Computing',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
     
        ]);
    }
}
