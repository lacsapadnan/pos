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
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('break_start')->nullable()->after('check_out')->change();
            $table->time('break_end')->nullable()->after('break_start')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('break_start')->nullable()->after('check_out')->change();
            $table->timestamp('break_end')->nullable()->after('break_start')->change();
        });
    }
};
