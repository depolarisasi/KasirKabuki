<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Discount;
use App\Models\Product;

class SampleDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first 3 products
        $products = Product::take(3)->get();
        
        if ($products->count() >= 3) {
            // Product discount for dine_in
            Discount::create([
                'name' => 'Diskon Sate Kambing 15% - Makan di Tempat',
                'type' => 'product',
                'value_type' => 'percentage',
                'value' => 15,
                'product_id' => $products[0]->id,
                'order_type' => 'dine_in',
                'is_active' => true
            ]);
            
            // Product discount for take_away
            Discount::create([
                'name' => 'Diskon Nasi Gudeg 10% - Bawa Pulang',
                'type' => 'product',
                'value_type' => 'percentage',
                'value' => 10,
                'product_id' => $products[1]->id,
                'order_type' => 'take_away',
                'is_active' => true
            ]);
            
            // Product discount for online
            Discount::create([
                'name' => 'Diskon Online 20% - Produk Ketiga',
                'type' => 'product',
                'value_type' => 'percentage',
                'value' => 20,
                'product_id' => $products[2]->id,
                'order_type' => 'online',
                'is_active' => true
            ]);
            
            // Transaction discount for all order types
            Discount::create([
                'name' => 'Diskon Transaksi Rp 5.000 - Semua Jenis',
                'type' => 'transaction',
                'value_type' => 'fixed',
                'value' => 5000,
                'product_id' => null,
                'order_type' => null, // applies to all order types
                'is_active' => true
            ]);
            
            $this->command->info('Sample discounts created successfully:');
            $this->command->info('1. ' . $products[0]->name . ' - 15% discount for dine_in');
            $this->command->info('2. ' . $products[1]->name . ' - 10% discount for take_away');
            $this->command->info('3. ' . $products[2]->name . ' - 20% discount for online');
            $this->command->info('4. Transaction discount Rp 5,000 for all order types');
        } else {
            $this->command->error('Not enough products found. Please seed products first.');
        }
    }
}
