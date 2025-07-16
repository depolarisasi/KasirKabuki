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
                'Ayam Bakar', 'Ayam Goreng', 'Rendang Daging', 'Nasi Gudeg',
                'Gado-gado', 'Bakso', 'Soto Ayam', 'Nasi Goreng',
                'Mie Ayam', 'Capcay', 'Pecel Lele', 'Ikan Bakar'
            ]),
            'price' => $this->faker->numberBetween(5000, 25000),
            'category_id' => Category::factory(),
        ];
    }
} 