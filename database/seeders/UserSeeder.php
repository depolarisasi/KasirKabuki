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
        // Create Roles first (check if exists) - Original 3-tier system
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $stafRole = Role::firstOrCreate(['name' => 'staf']);
        $investorRole = Role::firstOrCreate(['name' => 'investor']);

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@kasirkabuki.com'],
            [
                'name' => 'Admin KasirKabuki',
                'email' => 'admin@kasirkabuki.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'pin' => '1234',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign Spatie Permission role to admin
        $admin->assignRole($adminRole);

        // Create Kasir User (using staf role - consistent with original 3-tier system)
        $kasir = User::updateOrCreate(
            ['email' => 'kasir@kasirkabuki.com'],
            [
                'name' => 'Kasir KasirKabuki',
                'email' => 'kasir@kasirkabuki.com',
                'password' => Hash::make('kasir123'),
                'role' => 'staf', // Use 'staf' role instead of 'kasir'
                'pin' => '5678',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign Spatie Permission role to kasir (as staf)
        $kasir->assignRole($stafRole);
    }
}
