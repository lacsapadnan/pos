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
        Schema::create('sell_retur_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_retur_id')->references('id')->on('sell_returs')->onDelete('cascade');
            $table->foreignId('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreignId('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->integer('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_retur_details');
    }
};
