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
        Schema::table('employees', function (Blueprint $table) {
            // Rename email column to nickname
            $table->renameColumn('email', 'nickname');

            // Add ktp column
            $table->string('ktp', 100)->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Rename nickname back to email
            $table->renameColumn('nickname', 'email');

            // Drop ktp column
            $table->dropColumn('ktp');
        });
    }
};
