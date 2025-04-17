<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfessionalExperiencesTableSeeder extends Seeder
{
    /**x
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Assuming the "Computer Engineering" field has an ID of 1 in the `fields` table
        $fieldId = DB::table('fields')
            ->where('name', 'Computer Engineering')
            ->value('id');

        // Insert sample professional experiences
        DB::table('professional_experiences')->insert([
            [
                'name' => 'Software Development Intern',
                'description' => 'Worked on developing web applications using Laravel and Vue.js.',
                'field_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Systems Engineer',
                'description' => 'Designed and implemented scalable systems for enterprise clients.',
                'field_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Data Analyst',
                'description' => 'Analyzed large datasets to provide actionable insights for business decisions.',
                'field_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Network Administrator',
                'description' => 'Managed and maintained the company\'s network infrastructure.',
                'field_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cybersecurity Specialist',
                'description' => 'Implemented security measures to protect the organization from cyber threats.',
                'field_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}