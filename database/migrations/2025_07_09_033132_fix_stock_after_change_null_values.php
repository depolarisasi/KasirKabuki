<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing stock logs dengan null stock_after_change
        $stockLogs = DB::table('stock_logs')
            ->whereNull('stock_after_change')
            ->orWhere('stock_after_change', '')
            ->orderBy('product_id')
            ->orderBy('created_at')
            ->get();

        if ($stockLogs->count() > 0) {
            echo "Fixing {$stockLogs->count()} stock logs with null stock_after_change...\n";
            
            $currentStocks = [];
            
            foreach ($stockLogs as $log) {
                // Initialize stock untuk product jika belum ada
                if (!isset($currentStocks[$log->product_id])) {
                    $currentStocks[$log->product_id] = 0;
                }
                
                // Calculate stock change berdasarkan type lama
                $quantityChange = 0;
                switch ($log->type) {
                    case 'in':
                    case 'initial_stock':
                        $quantityChange = $log->quantity ?? $log->quantity_change ?? 0;
                        break;
                    case 'out':
                    case 'sale':
                        $quantityChange = -($log->quantity ?? $log->quantity_change ?? 0);
                        break;
                    case 'adjustment':
                        $quantityChange = $log->quantity_change ?? 0;
                        break;
                }
                
                // Update current stock
                $currentStocks[$log->product_id] += $quantityChange;
                
                // Update the log dengan calculated stock_after_change
                DB::table('stock_logs')
                    ->where('id', $log->id)
                    ->update([
                        'stock_after_change' => $currentStocks[$log->product_id],
                        'quantity_change' => $quantityChange, // Ensure quantity_change is correct
                        'updated_at' => now()
                    ]);
                
                echo "Fixed log ID {$log->id} for product {$log->product_id}: stock = {$currentStocks[$log->product_id]}\n";
            }
            
            echo "Stock logs fixed successfully!\n";
        } else {
            echo "No stock logs need fixing.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this data fix
        echo "Cannot reverse stock_after_change calculation fix.\n";
    }
};
