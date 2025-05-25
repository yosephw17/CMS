<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // Ensure to use a hashed password
        ]); // Assign the 'admin' , if using Spatie roles and permissions package



        // You can create additional users as needed
        User::create([
            'name' => 'Secretary User',
            'email' => 'secretary@example.com',
            'password' => Hash::make('password123'),
        ]); // Assign the 'secretary' role if needed

        User::create([
            'name' => 'Tamrat',
            'email' => 'tamrat@example.com',
            'password' => Hash::make('password123'),
        ]);

    }
}
