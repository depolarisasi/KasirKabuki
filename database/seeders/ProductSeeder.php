<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $makananCategory = Category::where('name', 'Makanan')->first();
        $minumanCategory = Category::where('name', 'Minuman')->first();
        $lainnyaCategory = Category::where('name', 'Lain-lain')->first();

        // Makanan Products
        $makananProducts = [
            ['name' => 'Ayam Bakar', 'price' => 25000, 'category_id' => $makananCategory->id],
            ['name' => 'Ayam Goreng', 'price' => 22000, 'category_id' => $makananCategory->id],
            ['name' => 'Rendang Daging', 'price' => 28000, 'category_id' => $makananCategory->id],
            ['name' => 'Nasi Putih', 'price' => 5000, 'category_id' => $makananCategory->id],
            ['name' => 'Lontong', 'price' => 7000, 'category_id' => $makananCategory->id],
            ['name' => 'Ketupat', 'price' => 7000, 'category_id' => $makananCategory->id],
            ['name' => 'Gado-gado', 'price' => 15000, 'category_id' => $makananCategory->id],
            ['name' => 'Krupuk', 'price' => 3000, 'category_id' => $makananCategory->id],
        ];

        // Minuman Products
        $minumanProducts = [
            ['name' => 'Es Teh Manis', 'price' => 5000, 'category_id' => $minumanCategory->id],
            ['name' => 'Es Jeruk', 'price' => 8000, 'category_id' => $minumanCategory->id],
            ['name' => 'Teh Panas', 'price' => 4000, 'category_id' => $minumanCategory->id],
            ['name' => 'Kopi Hitam', 'price' => 6000, 'category_id' => $minumanCategory->id],
            ['name' => 'Air Mineral', 'price' => 3000, 'category_id' => $minumanCategory->id],
            ['name' => 'Es Kelapa Muda', 'price' => 12000, 'category_id' => $minumanCategory->id],
        ];

        // Lain-lain Products
        $lainnyaProducts = [
            ['name' => 'Sambal Extra', 'price' => 2000, 'category_id' => $lainnyaCategory->id],
            ['name' => 'Acar', 'price' => 3000, 'category_id' => $lainnyaCategory->id],
            ['name' => 'Lalap', 'price' => 5000, 'category_id' => $lainnyaCategory->id],
        ];

        // Create all products
        foreach ($makananProducts as $product) {
            Product::create($product);
        }

        foreach ($minumanProducts as $product) {
            Product::create($product);
        }

        foreach ($lainnyaProducts as $product) {
            Product::create($product);
        }
    }
}
