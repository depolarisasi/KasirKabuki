<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles first (check if exists)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $stafRole = Role::firstOrCreate(['name' => 'staf']);

        // Create Admin User (check if exists)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@satebraga.com'],
            [
                'name' => 'Admin Sate Braga',
                'password' => Hash::make('admin123'),
                'role' => 'admin', // Keep for backward compatibility
                'email_verified_at' => now(),
            ]
        );
        
        // Assign admin role to admin user (if not already assigned)
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Create Staff User (check if exists)
        $stafUser = User::firstOrCreate(
            ['email' => 'kasir@satebraga.com'],
            [
                'name' => 'Kasir Sate Braga',
                'password' => Hash::make('kasir123'),
                'role' => 'staf', // Keep for backward compatibility
                'email_verified_at' => now(),
            ]
        );
        
        // Assign staf role to staff user (if not already assigned)
        if (!$stafUser->hasRole('staf')) {
            $stafUser->assignRole('staf');
        }
    }
}
