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
        Schema::table('sell_cart_drafts', function (Blueprint $table) {
            $table->foreignId('sell_id')->nullable()->constrained('sells')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sell_cart_drafts', function (Blueprint $table) {
            $table->dropForeign(['sell_id']);
            $table->dropColumn('sell_id');
        });
    }
};
