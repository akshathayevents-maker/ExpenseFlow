<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_payments')) {
            Schema::create('expense_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('expense_request_id')->constrained()->cascadeOnDelete();
                $table->enum('payment_mode', ['cash', 'upi', 'bank_transfer', 'wallet']);
                $table->decimal('amount', 12, 2);
                $table->string('transaction_reference')->nullable();
                $table->text('payment_notes')->nullable();
                $table->foreignId('paid_by')->constrained('users')->restrictOnDelete();
                $table->timestamp('paid_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};
