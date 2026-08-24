<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('advance_transactions')) {
            Schema::create('advance_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_advance_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                $table->date('transaction_date');
                // advance|recovery|adjustment|reversal — immutable, append-only.
                // This table is the sole source of truth for outstanding balances.
                $table->string('type');
                $table->decimal('amount', 12, 2);
                $table->string('reference')->nullable();
                $table->decimal('balance_after', 12, 2);

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();

                $table->index('employee_advance_id');
                $table->index(['user_id', 'transaction_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_transactions');
    }
};
