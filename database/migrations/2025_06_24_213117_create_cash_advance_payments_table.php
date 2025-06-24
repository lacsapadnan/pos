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
        Schema::create('cash_advance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained('cash_advances')->onDelete('cascade');
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->integer('installment_number'); // Which installment this payment is for
            $table->decimal('amount', 15, 2);
            $table->date('payment_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['cash_advance_id', 'installment_number']);
            $table->index(['status', 'due_date']);

            // Ensure unique installment numbers per cash advance
            $table->unique(['cash_advance_id', 'installment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advance_payments');
    }
};
