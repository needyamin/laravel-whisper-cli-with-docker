<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@whisper.com',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        // Create regular admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super admin created: admin@whisper.com / admin123');
        $this->command->info('Admin created: admin@example.com / admin123');
    }
}