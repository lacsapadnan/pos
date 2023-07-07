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
        Schema::create('send_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_warehouse')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse')->references('id')->on('warehouses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_stocks');
    }
};
