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
            $table->integer('capital_dus')->default(0);
            $table->integer('capital_pak')->default(0);
            $table->integer('capital_eceran')->default(0);
            $table->integer('price_sell_dus_out_of_town')->default(0);
            $table->integer('price_sell_pak_out_of_town')->default(0);
            $table->integer('price_sell_eceran_out_of_town')->default(0);
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
