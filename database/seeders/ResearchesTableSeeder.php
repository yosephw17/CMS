<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ResearchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Define some sample research titles related to Computer Engineering
        $researchTitles = [
            'Advanced Algorithms for Machine Learning',
            'Quantum Computing: Challenges and Opportunities',
            'Cybersecurity in IoT Devices',
            'Design and Implementation of Embedded Systems',
            'Blockchain Technology for Secure Transactions',
            'Artificial Intelligence in Autonomous Vehicles',
            'Cloud Computing: Scalability and Performance',
            'Human-Computer Interaction in Virtual Reality',
            'Data Science for Predictive Analytics',
            '5G Networks: Architecture and Applications',
            'Robotics in Industrial Automation',
            'Computer Vision for Medical Imaging',
            'Natural Language Processing for Chatbots',
            'VLSI Design for Low-Power Devices',
            'Signal Processing for Wireless Communication',
            'Bioinformatics: Genomic Data Analysis',
            'Augmented Reality in Education',
            'Edge Computing for Real-Time Applications',
            'Autonomous Systems: Drones and Self-Driving Cars',
            'Game Development: AI and Graphics',
        ];

        // Insert sample research entries
        for ($i = 0; $i < 20; $i++) {
            DB::table('researches')->insert([
                'title' => $researchTitles[$i],
                'field_id' => $faker->numberBetween(1, 11), // Field IDs from 1 to 11
                'instructor_id' => $faker->numberBetween(2, 21), // Instructor IDs from 2 to 21
                'link' => $faker->url, // Random URL
                'description' => $faker->paragraph, // Random description
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}