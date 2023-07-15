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
        Schema::create('kas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kas_income_item_id')->nullable()->unsigned();
            $table->bigInteger('kas_expense_item_id')->nullable()->unsigned();
            $table->date('date');
            $table->string('invoice');
            $table->string('type');
            $table->string('amount');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('kas_income_item_id')->references('id')->on('kas_income_items')->onDelete('cascade');
            $table->foreign('kas_expense_item_id')->references('id')->on('kas_expense_items')->onDelete('cascade');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
