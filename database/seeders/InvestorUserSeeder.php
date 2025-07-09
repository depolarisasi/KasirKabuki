<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InvestorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Test Investor User (check if exists)
        $investor = User::firstOrCreate(
            ['email' => 'investor@satebraga.com'],
            [
                'name' => 'Test Investor',
                'password' => Hash::make('investor123'),
                'role' => 'investor',
                'is_active' => true,
                'pin' => '123456',
                'email_verified_at' => now(),
            ]
        );
        
        // Assign investor role (if not already assigned)
        if (!$investor->hasRole('investor')) {
            $investor->assignRole('investor');
        }

        echo "Test Investor User Created:\n";
        echo "Email: investor@satebraga.com\n";
        echo "Password: investor123\n";
        echo "PIN: 123456\n";
        echo "Role: " . $investor->roles->pluck('name')->implode(', ') . "\n";
    }
}
