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
        Schema::create('sell_returs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_id')->references('id')->on('sells')->onDelete('cascade');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->date('retur_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_returs');
    }
};
