<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Check if the SuperAdmin already exists
        $existingAdmin = User::where('email', 'administrator@snailtheme.com')->first();

        if (!$existingAdmin) {
            User::create([
                'name' => 'Super Administrator',
                'email' => 'administrator@snailtheme.com',
                'email_verified_at' => now(),
                'password' => Hash::make('P@$$w0R6'),
                'role' => 'SuperAdmin',
            ]);

            $this->command->info('SuperAdmin user created successfully.');
        } else {
            $this->command->info('SuperAdmin user already exists.');
        }
    }
}