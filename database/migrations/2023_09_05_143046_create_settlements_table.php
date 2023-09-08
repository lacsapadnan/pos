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
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mutation_id')->references('id')->on('treasury_mutations')->onDelete('cascade');
            $table->foreignId('cashier_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('total_recieved')->nullable();
            $table->string('outstanding')->nullable();
            $table->string('to_treasury');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
