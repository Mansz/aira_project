<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'phone' => '+6281234567890',
            'whatsapp' => '+6281234567890',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create a user with Google authentication
        User::create([
            'name' => 'Google User',
            'email' => 'google@test.com',
            'phone' => '+6281234567891',
            'whatsapp' => '+6281234567891',
            'google_id' => 'google_123456789',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create an inactive user
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@test.com',
            'phone' => '+6281234567892',
            'whatsapp' => '+6281234567892',
            'password' => Hash::make('password'),
            'is_active' => false,
            'email_verified_at' => now(),
        ]);

        // Create 10 random users using factory
        User::factory(10)->create();

        // Create 5 users with Google authentication
        User::factory(5)->withGoogle()->create();

        // Create 3 users with avatars
        User::factory(3)->withAvatar()->create();
    }
}
