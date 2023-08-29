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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('price_sell_dus')->nullable()->default(0);
            $table->integer('price_sell_pak')->nullable()->default(0);
            $table->integer('price_sell_eceran')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_sell_dus', 'price_sell_pak', 'price_sell_eceran']);
        });
    }
};
