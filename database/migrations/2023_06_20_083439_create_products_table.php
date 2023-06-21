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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100)->nullable()->default('text')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('unit_dus')->unsigned()->nullable();
            $table->bigInteger('unit_pak')->unsigned()->nullable();
            $table->bigInteger('unit_eceran')->unsigned()->nullable();
            $table->string('barcode_dus', 100)->nullable();
            $table->string('barcode_pak', 100)->nullable();
            $table->string('barcode_eceran', 100)->nullable();
            $table->integer('dus_to_eceran')->nullable();
            $table->integer('pak_to_eceran')->nullable();
            $table->integer('price_dus')->nullable();
            $table->integer('price_pak')->nullable();
            $table->integer('price_eceran')->nullable();
            $table->integer('sales_price')->nullable();
            $table->integer('lastest_price_eceran')->nullable();
            $table->text('hadiah')->nullable();
            $table->timestamps();

            $table->foreign('unit_dus')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('unit_pak')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('unit_eceran')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
