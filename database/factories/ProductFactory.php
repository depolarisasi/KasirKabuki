<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Sate Ayam', 'Sate Kambing', 'Sate Bebek', 'Nasi Gudeg',
                'Es Teh Manis', 'Es Jeruk', 'Kopi Tubruk', 'Air Mineral'
            ]),
            'price' => $this->faker->numberBetween(5000, 25000),
            'category_id' => Category::factory(),
        ];
    }
} 