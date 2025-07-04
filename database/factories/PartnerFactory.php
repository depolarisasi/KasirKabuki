<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'GoFood', 'GrabFood', 'ShopeeFood', 'Traveloka Eats'
            ]),
            'commission_rate' => $this->faker->randomFloat(2, 5, 25), // 5% to 25%
        ];
    }
} 