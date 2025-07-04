<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(10000, 100000);
        $totalDiscount = $this->faker->numberBetween(0, $subtotal * 0.2);
        $partnerCommission = $this->faker->numberBetween(0, $subtotal * 0.1);
        $finalTotal = $subtotal - $totalDiscount - $partnerCommission;

        return [
            'transaction_code' => 'TRX-' . date('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'user_id' => User::factory(),
            'order_type' => $this->faker->randomElement(['dine_in', 'take_away', 'online']),
            'partner_id' => null,
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'partner_commission' => $partnerCommission,
            'final_total' => $finalTotal,
            'payment_method' => $this->faker->randomElement(['cash', 'qris']),
            'status' => $this->faker->randomElement(['pending', 'completed']),
            'discount_details' => [],
            'notes' => $this->faker->optional()->sentence(),
            'completed_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function withPartner(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'partner_id' => Partner::factory(),
                'order_type' => 'online',
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => now(),
            ];
        });
    }
} 