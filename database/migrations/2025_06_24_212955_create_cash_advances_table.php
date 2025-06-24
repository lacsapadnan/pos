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
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('advance_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->date('advance_date');
            $table->enum('type', ['direct', 'installment'])->default('direct');
            $table->integer('installment_count')->nullable(); // For installment type
            $table->decimal('installment_amount', 15, 2)->nullable(); // Amount per installment
            $table->decimal('paid_amount', 15, 2)->default(0); // Amount already paid back
            $table->decimal('remaining_amount', 15, 2); // Remaining amount to be paid
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['employee_id', 'status']);
            $table->index(['warehouse_id', 'advance_date']);
            $table->index('advance_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};
