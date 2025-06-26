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
        // Create salary settings table for master data
        Schema::create('salary_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->decimal('daily_salary', 10, 2)->default(0); // Base daily salary rate
            $table->decimal('monthly_salary', 10, 2)->default(0); // Base monthly salary rate
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate settings for same employee
            $table->unique(['employee_id']);
        });

        // Create salary payments table for payment records
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreignId('salary_setting_id')->references('id')->on('salary_settings')->onDelete('cascade');
            $table->date('period_start'); // Start of salary period
            $table->date('period_end'); // End of salary period
            $table->decimal('daily_salary', 10, 2)->default(0); // Daily salary rate at time of payment
            $table->decimal('monthly_salary', 10, 2)->default(0); // Monthly salary rate at time of payment
            $table->integer('total_work_days')->default(0); // Total working days in period
            $table->integer('present_days')->default(0); // Days employee was present
            $table->decimal('total_work_hours', 8, 2)->default(0); // Total hours worked
            $table->decimal('gross_salary', 10, 2)->default(0); // Calculated gross salary
            $table->decimal('cash_advance_deduction', 10, 2)->default(0); // Cash advance deductions
            $table->decimal('other_deductions', 10, 2)->default(0); // Other deductions
            $table->decimal('net_salary', 10, 2)->default(0); // Final salary after deductions
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('calculated_by')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate salary records for same employee and period
            $table->unique(['employee_id', 'period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('salary_settings');
    }
};
