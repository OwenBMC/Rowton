<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndStaffSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User & associated Staff record
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'user_type' => 'staff',
            'email_verified_at' => now(),
        ]);

        Staff::create([
            'user_id' => $adminUser->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '1234567890',
        ]);

        // 2. Create Shared Volunteer User Account (used for shared login)
        User::create([
            'name' => 'Shared Volunteer Account',
            'email' => 'volunteer@mail.com',
            'password' => Hash::make('password'),
            'user_type' => 'volunteer_shared',
            'email_verified_at' => now(),
        ]);

        // 3. Create Volunteer: Hilary McEvoy
        Volunteer::create([
            'first_name' => 'Hilary',
            'last_name' => 'McEvoy',
            'pin' => '1234', // optional pin if you're using quick pin switching
            'is_active' => true,
        ]);
    }
}