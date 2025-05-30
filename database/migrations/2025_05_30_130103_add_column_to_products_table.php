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
            $table->integer('capital_dus')->nullable();
            $table->integer('capital_pak')->nullable();
            $table->integer('capital_eceran')->nullable();
            $table->integer('price_sell_dus_out_of_town')->nullable();
            $table->integer('price_sell_pak_out_of_town')->nullable();
            $table->integer('price_sell_eceran_out_of_town')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['capital_dus', 'capital_pak', 'capital_eceran', 'price_sell_dus_out_of_town', 'price_sell_pak_out_of_town', 'price_sell_eceran_out_of_town']);
        });
    }
};
