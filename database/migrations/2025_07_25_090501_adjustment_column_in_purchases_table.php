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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('treasury_id')->nullable()->change();
            $table->string('payment_method', 100)->nullable();
            $table->string('cash')->nullable();
            $table->string('transfer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('treasury_id')->nullable()->change();
            $table->dropColumn('payment_method');
            $table->dropColumn('cash');
            $table->dropColumn('transfer');
        });
    }
};
