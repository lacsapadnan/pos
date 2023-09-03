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
        Schema::create('treasury_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_warehouse')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreignId('input_cashier')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('output_cashier')->references('id')->on('users')->onDelete('cascade');
            $table->string('from_treasury');
            $table->string('to_treasury');
            $table->string('amount');
            $table->text('description')->nullable();
            $table->date('input_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_mutations');
    }
};
