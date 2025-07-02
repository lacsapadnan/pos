<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to sells table
        Schema::table('sells', function (Blueprint $table) {
            $table->index(['created_at', 'status', 'warehouse_id', 'cashier_id'], 'idx_sells_income_statement');
        });

        // Add indexes to sell_details table
        Schema::table('sell_details', function (Blueprint $table) {
            $table->index(['sell_id', 'product_id'], 'idx_sell_details_income_statement');
        });

        // Add indexes to kas table for operating expenses
        Schema::table('kas', function (Blueprint $table) {
            $table->index(['created_at', 'type', 'warehouse_id'], 'idx_kas_income_statement');
        });

        // Add indexes to product_reports table for COGS
        Schema::table('product_reports', function (Blueprint $table) {
            $table->index(['created_at', 'warehouse_id', 'product_id'], 'idx_product_reports_income_statement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sells', function (Blueprint $table) {
            $table->dropIndex('idx_sells_income_statement');
        });

        Schema::table('sell_details', function (Blueprint $table) {
            $table->dropIndex('idx_sell_details_income_statement');
        });

        Schema::table('kas', function (Blueprint $table) {
            $table->dropIndex('idx_kas_income_statement');
        });

        Schema::table('product_reports', function (Blueprint $table) {
            $table->dropIndex('idx_product_reports_income_statement');
        });
    }
};
