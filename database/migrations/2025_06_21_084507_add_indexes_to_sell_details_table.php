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
        Schema::table('sell_details', function (Blueprint $table) {
            // Add composite index for sell_id and product_id (used in joins)
            $table->index(['sell_id', 'product_id'], 'sell_details_sell_product_index');

            // Add index for product_id (used in grouping)
            $table->index('product_id', 'sell_details_product_index');
        });

        Schema::table('sells', function (Blueprint $table) {
            // Add composite index for warehouse_id and status (used in filtering)
            $table->index(['warehouse_id', 'status'], 'sells_warehouse_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sell_details', function (Blueprint $table) {
            $table->dropIndex('sell_details_sell_product_index');
            $table->dropIndex('sell_details_product_index');
        });

        Schema::table('sells', function (Blueprint $table) {
            $table->dropIndex('sells_warehouse_status_index');
        });
    }
};
