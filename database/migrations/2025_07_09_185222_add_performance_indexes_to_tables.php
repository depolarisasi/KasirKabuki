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
        // Function to safely add index if it doesn't exist
        $addIndexIfNotExists = function($table, $columns, $indexName = null) {
            if (!$indexName) {
                $indexName = $table . '_' . implode('_', (array)$columns) . '_index';
            }
            
            $indexExists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            
            if (empty($indexExists)) {
                if (is_array($columns)) {
                    DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} (" . implode(',', array_map(function($col) { return "`{$col}`"; }, $columns)) . ")");
                } else {
                    DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} (`{$columns}`)");
                }
            }
        };

        // Add indexes to expenses table for category and date filtering
        $addIndexIfNotExists('expenses', ['category', 'date'], 'expenses_category_date_idx');
        $addIndexIfNotExists('expenses', 'date', 'expenses_date_idx');
        $addIndexIfNotExists('expenses', 'amount', 'expenses_amount_idx');

        // Add indexes to discounts table for order type filtering
        $addIndexIfNotExists('discounts', ['order_type', 'is_active'], 'discounts_order_type_active_idx');
        $addIndexIfNotExists('discounts', ['product_id', 'is_active'], 'discounts_product_active_idx');

        // Add indexes to product_partner_prices table for efficient lookups
        $addIndexIfNotExists('product_partner_prices', ['product_id', 'partner_id'], 'product_partner_lookup_idx');
        $addIndexIfNotExists('product_partner_prices', 'partner_id', 'partner_prices_partner_idx');

        // Add indexes to transactions table for reporting performance
        $addIndexIfNotExists('transactions', ['created_at', 'order_type'], 'transactions_date_order_type_idx');
        $addIndexIfNotExists('transactions', 'transaction_code', 'transactions_code_idx');
        $addIndexIfNotExists('transactions', ['user_id', 'created_at'], 'transactions_user_date_idx');

        // Add indexes to transaction_items for efficient joins
        $addIndexIfNotExists('transaction_items', ['transaction_id', 'product_id'], 'transaction_items_lookup_idx');
        $addIndexIfNotExists('transaction_items', 'product_id', 'transaction_items_product_idx');

        // Add indexes to stock_logs for stock tracking performance
        $addIndexIfNotExists('stock_logs', ['product_id', 'created_at'], 'stock_logs_product_date_idx');
        $addIndexIfNotExists('stock_logs', ['type', 'created_at'], 'stock_logs_type_date_idx');

        // Add indexes to products table for search performance (use deleted_at instead of is_active)
        $addIndexIfNotExists('products', ['category_id', 'deleted_at'], 'products_category_deleted_idx');
        $addIndexIfNotExists('products', 'name', 'products_name_idx');
        $addIndexIfNotExists('products', ['price', 'deleted_at'], 'products_price_deleted_idx');

        // Add indexes to categories table
        $addIndexIfNotExists('categories', 'name', 'categories_name_idx');

        // Add indexes to users table for authentication performance (has is_active)
        $addIndexIfNotExists('users', ['email', 'is_active'], 'users_email_active_idx');
        $addIndexIfNotExists('users', ['is_active', 'created_at'], 'users_active_date_idx');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Function to safely drop index if it exists
        $dropIndexIfExists = function($table, $indexName) {
            $indexExists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            
            if (!empty($indexExists)) {
                DB::statement("ALTER TABLE {$table} DROP INDEX {$indexName}");
            }
        };

        // Drop indexes from expenses table
        $dropIndexIfExists('expenses', 'expenses_category_date_idx');
        $dropIndexIfExists('expenses', 'expenses_date_idx');
        $dropIndexIfExists('expenses', 'expenses_amount_idx');

        // Drop indexes from discounts table
        $dropIndexIfExists('discounts', 'discounts_order_type_active_idx');
        $dropIndexIfExists('discounts', 'discounts_product_active_idx');

        // Drop indexes from product_partner_prices table
        $dropIndexIfExists('product_partner_prices', 'product_partner_lookup_idx');
        $dropIndexIfExists('product_partner_prices', 'partner_prices_partner_idx');

        // Drop indexes from transactions table
        $dropIndexIfExists('transactions', 'transactions_date_order_type_idx');
        $dropIndexIfExists('transactions', 'transactions_code_idx');
        $dropIndexIfExists('transactions', 'transactions_user_date_idx');

        // Drop indexes from transaction_items table
        $dropIndexIfExists('transaction_items', 'transaction_items_lookup_idx');
        $dropIndexIfExists('transaction_items', 'transaction_items_product_idx');

        // Drop indexes from stock_logs table
        $dropIndexIfExists('stock_logs', 'stock_logs_product_date_idx');
        $dropIndexIfExists('stock_logs', 'stock_logs_type_date_idx');

        // Drop indexes from products table
        $dropIndexIfExists('products', 'products_category_deleted_idx');
        $dropIndexIfExists('products', 'products_name_idx');
        $dropIndexIfExists('products', 'products_price_deleted_idx');

        // Drop indexes from categories table
        $dropIndexIfExists('categories', 'categories_name_idx');

        // Drop indexes from users table
        $dropIndexIfExists('users', 'users_email_active_idx');
        $dropIndexIfExists('users', 'users_active_date_idx');
    }
};
