<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if the admin user already exists
        if (User::where('email', 'admin@gmail.com')->exists()) {
            $this->command->info('Admin user already exists. Skipping seeding.');
            return;
        }

        // Create the admin user if not exists
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'is_super_admin'=>1,
            'password' => Hash::make('Admin@123'), // Secure password hashing
        ]);

        $this->command->info('Admin user created successfully.');
    }
}
