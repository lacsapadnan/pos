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
        Schema::table('product_reports', function (Blueprint $table) {
            // Change qty from string to decimal
            $table->decimal('qty', 10, 2)->change();

            // Change price from string to decimal
            $table->decimal('price', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reports', function (Blueprint $table) {
            // Revert qty back to string
            $table->string('qty')->change();

            // Revert price back to string
            $table->string('price')->change();
        });
    }
};
