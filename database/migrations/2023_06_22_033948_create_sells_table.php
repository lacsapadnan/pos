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
        Schema::create('sells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->string('order_number');
            $table->string('subtotal');
            $table->string('grand_total');
            $table->string('pay');
            $table->string('change');
            $table->date('transaction_date');
            $table->enum('status', ['drafted', 'success'])->default('drafted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sells');
    }
};
