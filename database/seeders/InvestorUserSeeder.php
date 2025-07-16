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
        // Create Investor User
        $investor = User::updateOrCreate(
            ['email' => 'investor@kasirkabuki.com'],
            [
                'name' => 'Investor KasirKabuki',
                'email' => 'investor@kasirkabuki.com',
                'password' => Hash::make('investor123'),
                'role' => 'investor',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign investor role to user
        if (!$investor->hasRole('investor')) {
            $investor->assignRole('investor');
        }

        echo "✅ Investor user seeded successfully!\n";
        echo "Email: investor@kasirkabuki.com\n";
    }
}
