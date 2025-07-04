<?php

namespace Database\Factories;

use App\Models\TransactionItem;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $productPrice = $this->faker->numberBetween(5000, 25000);
        $subtotal = $quantity * $productPrice;
        $discountAmount = 0;
        $total = $subtotal - $discountAmount;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->randomElement([
                'Sate Ayam', 'Sate Kambing', 'Sate Bebek', 'Nasi Gudeg'
            ]),
            'product_price' => $productPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total' => $total,
        ];
    }
}
