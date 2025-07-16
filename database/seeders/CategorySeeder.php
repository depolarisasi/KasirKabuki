<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Kategori untuk semua jenis makanan dan lauk pauk restoran',
            ],
            [
                'name' => 'Minuman',
                'description' => 'Kategori untuk semua jenis minuman panas dan dingin',
            ],
            [
                'name' => 'Lain-lain',
                'description' => 'Kategori untuk produk tambahan dan aksesoris',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
