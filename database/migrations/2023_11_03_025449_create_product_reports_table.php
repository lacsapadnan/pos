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
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignid('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreignid('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreignid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('customer_id')->nullable()->unsigned();
            $table->bigInteger('supplier_id')->nullable()->unsigned();
            $table->string('unit');
            $table->string('unit_type');
            $table->string('qty');
            $table->string('price');
            $table->string('type');
            $table->string('for');
            $table->text('description');

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};
