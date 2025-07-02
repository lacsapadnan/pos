<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any existing 'on_break' status to 'checked_in'
        DB::table('attendances')
            ->where('status', 'on_break')
            ->update(['status' => 'checked_in']);

        // Modify the enum to remove 'on_break'
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('checked_in', 'checked_out') NOT NULL DEFAULT 'checked_in'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the 'on_break' option to the enum
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('checked_in', 'checked_out', 'on_break') NOT NULL DEFAULT 'checked_in'");
    }
};
